<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Number of seconds between login attempts */
const SECONDS=5;

use Eleanor\Assign,
	Eleanor\Classes\Output,

	CMS\Enums\Events,
	CMS\Classes\Uri4AdminPanel,
	CMS\Interfaces\AdminPanel;

use const Eleanor\SITEDIR;

/** Script start time, used to display service information at the bottom of the page. */
\define('CMS\STARTED',\hrtime(true));

require __DIR__.'/cms/core.php';

/** Error page
 * @param int $code Error code 4XX
 * @param string $allow405 'Allow' header value for 405 error */
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

CMS::$A=new Authorization('a11n_adminpanel',0,require CMS.'external.php');

# Access to admin panel is allowed only for users with root or team roles
while(CMS::$A->current and !array_intersect(['root','team'],CMS::$P->roles))
	CMS::$A->SignOut();

# Arguments for the Template constructor (template source)
if(!CMS::$json and CMS::$T instanceof Assign)
	CMS::$T->args[0]=CMS.'admin-panel';

# Sign in & sign out mechanics work for JSON requests with empty query string only
elseif(!$_SERVER['QUERY_STRING'])
{
	# Sign out
	if(CMS::$delete)
	{
		CMS::$A->SignOut();
		JSON([
			'ok'=>true
		]);
	}

	# PHP 8.6: migrate to pipe operator
	# Sign in
	if(!\array_all([$_POST['username'] ?? 0,$_POST['password'] ?? 0,$_POST['captcha'] ?? 0],fn($t)=>\is_string($t)))
		JSON([
			'ok'=>false,
			'error'=>'INSUFFICIENT'
		]);

	$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `password_hash`, TIMESTAMPDIFF(SECOND,`last_login_attempt`,NOW()) `seconds`
FROM `users`
WHERE `name`=?
LIMIT 1
SQL ,[$_POST['username']]);

	if(!$user=SingleFetch($R))
		JSON([
			'ok'=>false,
			'error'=>'NOT_FOUND'
		]);

	$id=(int)$user['id'];

	if(CMS::$A->current==$id)
		JSON([
			'ok'=>false,
			'error'=>'ALREADY'
		]);

	CMS::$Db->Update('users',['last_login_attempt'=>fn()=>'NOW()'],'`id`='.$user['id']);

	# Too often and no captcha
	if($user['seconds']<SECONDS and !\CMS\Classes\hCaptcha::Check('captcha'))
		JSON([
			'ok'=>false,
			'error'=>'W8',
			'seconds'=>SECONDS,
			'remain'=>SECONDS-$user['seconds']
		]);

	$empty=$user['password_hash']==='';

	if($empty or \password_verify($_POST['password'],$user['password_hash']))
	{
		# Keep password hash up to date
		if($empty or \password_needs_rehash($user['password_hash'],\PASSWORD_DEFAULT))
			CMS::$Db->Update('users',['password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT)],'`id`='.$user['id']);

		# Check rights
		$P=Permissions($id);
		if(!\array_intersect(['root','team'],$P->roles))
			JSON([
				'ok'=>false,
				'error'=>'ACCESS_DENIED'
			]);

		# 2FA should be checked here
		CMS::$A->SignIn($id);

		Events::UserSignedIn->Trigger([
			'id'=>$id,
			'way'=>'username',
			'where'=>'admin-panel',
			'ip'=>CMS::$ip,
			'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
		]);

		JSON([
			'ok'=>true,
			'id'=>$id
		]);
	}

	JSON([
		'ok'=>false,
		'error'=>'WRONG_PASSWORD'
	]);
}

# Localization
L10n::$code=L10N;

if(L10NS)
{
	$l10n=CMS::$A->current ? GetUserData('l10n') : '';

	if(\in_array($l10n,L10NS))
		L10n::$code=$l10n;
	elseif(L10N!==$l10n)
	{
		# https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Accept-Language - HAL is already sorted
		$hal=isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? \explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) : [];
		$hal=\array_map(fn($item)=>\substr(\trim($item),0,2),$hal);

		$l10n=\array_find($hal,fn($item)=>$item===L10N || \in_array($item,L10NS,true));

		if($l10n)
			L10n::$code=$l10n;
	}
}

if(CMS::$A->current)
{
	# Admin panel file can be renamed, so update its name in the system cache
	$fn=\basename(__FILE__);
	if($fn!==CMS::$Cache->Get('admin-panel',true))
		CMS::$Cache->Put('admin-panel',$fn,0,true);

	Uri4AdminPanel::$base=SITEDIR.$fn;

	# Arguments for the Template constructor (default variables)
	if(!CMS::$json and CMS::$T instanceof Assign)
		CMS::$T->args[1]['links']['home']=Uri4AdminPanel::$base;

	# Redirect to the main unit
	if(!\is_string($_GET['u'] ?? 0))
		Redirect(Uri4AdminPanel::$base.'?u=main');

	$unit=$_GET['u'];
	$CMS=new CMS;# Global object to carry shared data inside cms

	if(\preg_match('#[^a-z\d\-_.]#i',$unit)==0 and \is_file($f=CMS."units/$unit.php"))
	{
		/** @var AdminPanel $U */
		$U=require$f;
		$CMS->$unit=$U;

		if($U instanceof AdminPanel)
			$U->AdminPanel(new Uri4AdminPanel(u:$unit));
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