<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Minimum interval between login attempts, in seconds. */
const AUTH_RETRY_INTERVAL=5;

use Eleanor\Assign,

	CMS\Classes\Uri4AdminPanel,
	CMS\Interfaces\AdminPanel;

use CMS\Enums\{Events, SignInAttempt};
use Eleanor\Classes\{Output, Totp};
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

	# Sign in
	if(!\is_string($_POST['username'] ?? 0) or !is_string($_POST['password'] ?? 0))
		JSON([
			'ok'=>false,
			'error'=>'INSUFFICIENT'
		]);

	$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `password_hash`, `totp_secret`, `totp_digits`, `totp_used`, `recovery_codes`, TIMESTAMPDIFF(SECOND,`last_login_attempt`,NOW()) `seconds`
FROM `users`
WHERE `name`=?
SQL ,[$_POST['username']]);

	if(!$user=SingleFetch($R))
		JSON([
			'ok'=>false,
			'error'=>'NOT_FOUND'
		]);

	$id=(int)$user['id'];

	if(CMS::$A->current==$id)
		JSON([
			'ok'=>true,
			'error'=>'ALREADY'
		]);

	# Recovery access
	$recovery=\is_string($_POST['totp'] ?? 0) && is_string($_POST['recovery_code'] ?? 0);

	# TOTP available but not provided
	if(!$recovery and $user['totp_secret'] and empty($_POST['totp']))
		JSON([
			'ok'=>false,
			'error'=>'TOTP'
		]);

	CMS::$Db->Update('users',['last_login_attempt'=>fn()=>'NOW()'],'`id`='.$user['id']);

	# Too often and no captcha
	if($user['seconds']!==null and $user['seconds']<AUTH_RETRY_INTERVAL and !\CMS\Classes\hCaptcha::Check('captcha'))
		JSON([
			'ok'=>false,
			'error'=>'W8',
			'seconds'=>AUTH_RETRY_INTERVAL,
			'remain'=>AUTH_RETRY_INTERVAL-$user['seconds']
		]);

	# Check rights
	$P=Permissions($id);
	if(!\array_intersect(['root','team'],$P->roles))
		JSON([
			'ok'=>false,
			'error'=>'ACCESS_DENIED'
		]);

	$totp_used=false;

	if($recovery)
	{
		$counter=0;

		# TOTP verification
		if($user['totp_secret'] and $user['totp_used']!=$_POST['totp'] and Totp::Verify($user['totp_secret'],$_POST['totp'],$user['totp_digits']))
		{
			$counter++;
			$totp_used=true;
		}

		# Password verification
		if(\password_verify($_POST['password'],$user['password_hash']))
			$counter++;

		if($counter===2)
			$recovery=false;

		if($counter===1 and $user['recovery_codes'])
		{
			include CMS.'recovery-codes.php';

			if(VerifyRecoveryCode($_POST['recovery_code'],$user['recovery_codes'],$id))
				$counter++;
			else
				SignInAttempt::WrongRecoveryCode->Log($id);
		}

		if($counter<2)
			JSON([
				'ok'=>false,
				'error'=>'WRONG_CREDENTIALS'
			]);
	}
	else
	{
		# TOTP verification
		if($user['totp_secret'])
		{
			if($user['totp_used']==$_POST['totp'] or !Totp::Verify($user['totp_secret'],$_POST['totp'],$user['totp_digits']))
			{
				SignInAttempt::WrongTOTP->Log($id);

				JSON([
					'ok'=>false,
					'error'=>'WRONG_TOTP'
				]);
			}

			$totp_used=true;
		}

		# Emergency password reset: accept and store a new password if the hash was manually cleared
		$emergency=$_POST['password'] && $user['password_hash']==='';

		# Password verification
		if(!$emergency and !\password_verify($_POST['password'],$user['password_hash']))
		{
			SignInAttempt::WrongPassword->Log($id);

			JSON([
				'ok'=>false,
				'error'=>'WRONG_PASSWORD'
			]);
		}

		# Keep password hash up to date
		if($emergency or \password_needs_rehash($user['password_hash'],\PASSWORD_DEFAULT))
			CMS::$Db->Update('users',['password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT)],'`id`='.$user['id']);
	}

	if($totp_used)
		CMS::$Db->Update('users',['totp_used'=>(int)$_POST['totp']],'`id`='.$user['id']);

	$way=$recovery ? 'recovery' : 'sign-in';

	CMS::$A->SignIn($id,false,['way'=>$way]);

	Events::UserSignedIn->Trigger([
		'id'=>$id,
		'way'=>$way,
		'where'=>'admin-panel',
		'ip'=>CMS::$ip ? $_SERVER['REMOTE_ADDR'] : null,
		'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
	]);

	SignInAttempt::Ok->Log($id);

	JSON([
		'ok'=>true,
		'id'=>$id,
		'recovery'=>$recovery,
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