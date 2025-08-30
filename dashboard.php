<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Amount of seconds between login attempts */
const SECONDS=5;

use Eleanor\Classes\L10n,
	Eleanor\Classes\Output,

	CMS\Enums\Events,
	CMS\Classes\UriDashboard,
	CMS\Interfaces\Dashboard;

use const Eleanor\SITEDIR;

/** @const Время старта системы, используется для отображения служебной информации внизу страницы */
\define('CMS\STARTED',\hrtime(true));

require __DIR__.'/cms/core.php';

/** Страница с ошибкой
 * @param int $code Код ошибки
 * @param string $allow405 Значение заголовка allow для ошибки 405 */
function Halt(int$code=404,string$allow405='DELETE'):never
{
	if(CMS::$json)
	{
		$output=\json_encode([
			'ok'=>false,
			'code'=>$code,
		],JSON);
		Output::SendHeaders(Output::JSON, $code);
	}
	else
	{
		$output=(CMS::$T)('error',code:$code);

		Output::SendHeaders(Output::HTML,$code);
	}

	if($code===405 and $allow405)
		\header('Allow: '.$allow405);

	die($output);
}

CMS::$A=new Authorization('a11n_dashboard',0,require ROOT.'external.php');

#Access to dashboard should have admins and team members
while(CMS::$A->current and !array_intersect(['admin','team'],CMS::$P->roles))
	CMS::$A->SignOut();

if(!CMS::$json)
	CMS::$T->queue[]=ROOT.'dashboard';

//Sign in & sign out
elseif(!$_SERVER['QUERY_STRING'])
{
	//Sign out
	if(CMS::$delete)
	{
		CMS::$A->SignOut();
		JSON(['ok'=>true]);
	}

	//Sign in
	if(!IsS($_POST['username'] ?? 0,$_POST['password'] ?? 0,$_POST['captcha'] ?? 0))
		JSON(['ok'=>false,'error'=>'INSUFFICIENT']);

	$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `password_hash`, TIMESTAMPDIFF(SECOND,`last_login_attempt`,NOW()) `seconds`
FROM `users`
WHERE `name`=?
LIMIT 1
SQL ,[$_POST['username']]);
	if(!$user=$R->fetch_assoc())
		JSON(['ok'=>false,'error'=>'NOT_FOUND']);

	$id=(int)$user['id'];

	if(CMS::$A->current==$id)
		JSON(['ok'=>false,'error'=>'ALREADY']);

	CMS::$Db->Update('users',['last_login_attempt'=>fn()=>'NOW()'],'`id`='.$user['id']);

	#Too ofter and no captcha
	if($user['seconds']<SECONDS and !\CMS\Classes\hCaptcha::Check('captcha'))
		JSON(['ok'=>false,'error'=>'W8','seconds'=>SECONDS,'remain'=>SECONDS-$user['seconds']]);

	$empty=$user['password_hash']==='';

	if($empty or \password_verify($_POST['password'],$user['password_hash']))
	{
		#Поддержим актуальность пароля
		if($empty or \password_needs_rehash($user['password_hash'],\PASSWORD_DEFAULT))
			CMS::$Db->Update('users',['password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT)],'`id`='.$user['id']);

		#Check rights
		$P=Permissions($id);
		if(!array_intersect(['admin','team'],$P->roles))
			JSON(['ok'=>false,'error'=>'ACCESS_DENIED']);

		//2FA should be injected somewhere here
		CMS::$A->SignIn($id);

		Events::UserSignedIn->Trigger([
			'id'=>$id,
			'way'=>'username',
			'where'=>'dashboard',
			'ip'=>CMS::$ip ? $_SERVER['REMOTE_ADDR'] : null,
			'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
		]);

		JSON(['ok'=>true,'id'=>$id]);
	}

	JSON(['ok'=>false,'error'=>'WRONG_PASSWORD']);
}

#Localization
L10n::$code=L10N;

if(L10NS)
{
	$l10n=CMS::$A->current ? GetUsers('l10n') : '';

	if(\in_array($l10n,L10NS))
		L10n::$code=$l10n;
	elseif(L10N!==$l10n)
	{
		//https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Accept-Language - HAL is already sorted
		$hal=isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? \explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) : [];
		$hal=\array_map(fn($item)=>\substr(\trim($item),0,2),$hal);

		$l10n=\array_find($hal,fn($item)=>$item===L10N || \in_array($item,L10NS,true));

		if($l10n)
			L10n::$code=$l10n;
	}
}

if(CMS::$A->current)
{
	#Dashboard file could be easily renamed, so lets update its name in the system cache
	$fn=\basename(__FILE__);
	if($fn!==CMS::$Cache->Get('dashboard',true))
		CMS::$Cache->Put('dashboard',$fn,0,true);

	UriDashboard::$base=SITEDIR.$fn;

	CMS::$T->default['links']=[
		'dashboard'=>UriDashboard::$base
	];

	#Redirect to the main unit
	if(!\is_string($_GET['u'] ?? 0))
		Redirect(UriDashboard::$base.'?u=main');

	$unit=$_GET['u'];
	$Shared=new CMS;#Global object to carry shared data inside cms

	if(\preg_match('#[^a-z\d\-_.]#i',$unit)==0 and \is_file($f=ROOT."units/{$unit}.php"))
	{
		/** @var Dashboard $U */
		$U=require$f;
		$Shared->$unit=$U;

		if($U instanceof Dashboard)
			$U->Dashboard(new UriDashboard(u:$unit));
	}

	if(CMS::$json)
		JSON(['ok'=>false],404);
	else
		Halt();
}
else
{
	$out=(CMS::$T)('SignIn',hcaptcha:CMS::$config['system']['captcha'] ? CMS::$config['system']['hcaptcha'] : '');

	HTML($out,401);
}