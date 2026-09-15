<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use CMS\Enums\Events;
use Eleanor\Classes\TOTP;

const
	/** Minimum interval between login attempts when CAPTCHA is unavailable, in seconds. */
	AUTH_RETRY_INTERVAL=7,

	/** Maximum avatar size in bytes, 1 MiB currently */
	MAX_AVATAR_SIZE=1048576,

	/** Maximum number of users per 1 authorization */
	MAX_USERS=25,

	/** Minimum password length */
	MIN_PASSWORD_LENGTH=10,

	/** The number of months after which a session is considered stale and can be terminated */
	MONTHS_TO_STALE_SESSION=3,

	/** Number of recovery codes generated for a user. */
	RECOVERY_CODE_COUNT=10,

	/** Period after successful account recovery during which sensitive account data can be changed without additional
	 * verification, in seconds. */
	RECOVERY_GRACE_PERIOD=3600,

	/** Default group for newly created users */
	USER_GROUP=3;

/** Main page of the site
 * @var Classes\Uri $Uri CMS uri
 * @var object $this This unit
 * @var ?string $uri Subpage name
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */


/** Generating salt for avatar */
function AvatarSalt():string
{
	return \base_convert(\random_int(1,60466175),10,36);
}

/** Signing in
 * @param Classes\Uri $Uri
 * @return string|array */
function SignIn(Uri$Uri,int&$code):array|string
{
	if(CMS::$a11n)
	{
		$R=CMS::$Db->Execute(<<<SQL
SELECT COUNT(`a11n_id`) `total` FROM `a11n_userarea` WHERE `a11n_id`=? 
SQL ,[CMS::$a11n]);
		$total=(int)SingleFetch($R,true);
	}
	else
		$total=0;

	# AJAX request
	if(CMS::$json)
	{
		if($total>=MAX_USERS)
			return[
				'ok'=>false,
				'error'=>'USERS_LIMIT',
				'max'=>MAX_USERS
			];

		# PHP 8.6: migrate to pipe operator
		# Sign in by username and password
		if(!\array_all([$_POST['username'] ?? 0,$_POST['password'] ?? 0],fn($t)=>\is_string($t)) or !isset($_POST['temp']))
			return[
				'ok'=>false,
				'error'=>'INSUFFICIENT'
			];

		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `password_hash`, TIMESTAMPDIFF(SECOND,`last_login_attempt`,NOW()) `seconds`
FROM `users`
WHERE `name`=?
SQL ,[$_POST['username']]);

		if(!$user=SingleFetch($R))
			return[
				'ok'=>false,
				'error'=>'NOT_FOUND'
			];

		$id=(int)$user['id'];

		if(CMS::$A->current==$id)
			return[
				'ok'=>false,
				'error'=>'ALREADY'
			];

		CMS::$Db->Update('users',['last_login_attempt'=>fn()=>'NOW()'],'`id`='.$user['id']);

		# Too often and no captcha
		if($user['seconds']!==null and $user['seconds']<AUTH_RETRY_INTERVAL and !\CMS\Classes\hCaptcha::Check('captcha'))
			return[
				'ok'=>false,
				'error'=>'W8',
				'seconds'=>AUTH_RETRY_INTERVAL,
				'remain'=>AUTH_RETRY_INTERVAL-$user['seconds']
			];

		$empty=$user['password_hash']==='';

		if($empty or \password_verify($_POST['password'],$user['password_hash']))
		{
			# Keep password hash up to date
			if($empty or \password_needs_rehash($user['password_hash'],\PASSWORD_DEFAULT))
				CMS::$Db->Update('users',['password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT)],'`id`='.$user['id']);

			# FA should be injected somewhere here
			CMS::$A->SignIn($id,(bool)$_POST['temp'],['way'=>'sign-in']);

			Events::UserSignedIn->Trigger([
				'id'=>$id,
				'way'=>'sign-in',
				'where'=>'user-area',
				'ip'=>CMS::$ip ? $_SERVER['REMOTE_ADDR'] : null,
				'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
			]);

			return[
				'ok'=>true,
				'id'=>$id
			];
		}

		return[
			'ok'=>false,
			'error'=>'WRONG_PASSWORD'
		];
	}

	if($total>=MAX_USERS)
	{
		$code=403;
		return(CMS::$T)('SignInError','USERS_LIMIT',MAX_USERS);
	}

	return(CMS::$T)('SignIn');
}

/** Checking availability of username
 * @param string $name Name to be checked
 * @return bool */
function CheckName(string$name):bool
{
	if($name==='')
		return false;

	$R=CMS::$Db->Execute(<<<SQL
SELECT `name` FROM `users` WHERE `name`=?
SQL ,[$name]);

	return $R->num_rows==0;
}

/** Sign Up for new user
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function SignUp(Uri$Uri,int&$code):array|string
{
	# Check availability of username
	if(CMS::$json and \is_string($_GET['check_name'] ?? 0))
		return[
			'ok'=>CheckName($_GET['check_name']),
		];

	if(CMS::$json)
	{
		# PHP 8.6: migrate to pipe operator
		if(!\array_all([$_POST['name'] ?? 0,$_POST['display_name'] ?? 0,$_POST['password'] ?? 0],fn($t)=>\is_string($t)))
			return[
				'ok'=>false,
				'error'=>'INSUFFICIENT'
			];

		if(!CheckName($_POST['name']))
			return[
				'ok'=>false,
				'error'=>'NAME_EXISTS'
			];

		if(\strlen($_POST['password'])<MIN_PASSWORD_LENGTH)
			return[
				'ok'=>false,
				'error'=>'LOW_PASSWORD_LENGTH'
			];

		if(!\CMS\Classes\hCaptcha::Check('captcha'))
			return[
				'ok'=>false,
				'error'=>'CAPTCHA',
			];

		$group=USER_GROUP;
		$id=CMS::$Db->Insert('users',[
			'name'=>$_POST['name'],
			'display_name'=>$_POST['display_name'],
			'groups'=>"[$group]",
			'password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT),
		]);

		if(!$id)
			return[
				'ok'=>false,
				'error'=>'SOMETHING_WENT_WRONG'
			];

		# Signing in to the site
		CMS::$A->SignIn($id,true,['way'=>'sign-up']);

		Events::UserCreated->Trigger(['id'=>$id]);

		return[
			'ok'=>true,
			'redirect'=>(string)$Uri
		];
	}

	return(CMS::$T)('SignUp',MIN_PASSWORD_LENGTH);
}

/** User's sign out
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function SignOut(Uri$Uri,int&$code):array|string
{
	if(CMS::$A->current)
		CMS::$A->SignOut(isset($_GET['@']) ? (int)$_GET['@'] : null);
	else
		$code=401;

	# AJAX request
	if(CMS::$json)
		return[
			'ok'=>true,
		];

	return(CMS::$T)('SignOut');
}

function Overview(Uri$Uri,int&$code):array|string
{
	if(!CMS::$A->current)
		Redirect($Uri('sign-in'),302);

	# AJAX request
	if(CMS::$json)
		return match($_GET['zone'] ?? ''){
			'totp'=>Totp(),
			'recovery-codes'=>RecoveryCodes(),
			'change-password'=>ChangePassword(),
			default=>['ok'=>false],
		};

	# Fetching user data
	$id=CMS::$A->current;
	$R=CMS::$Db->Query(<<<SQL
SELECT `name`, `groups`, `password_changed_at`, `totp_secret` IS NOT NULL `totp_enabled`, `totp_changed_at`, IFNULL(JSON_LENGTH(`recovery_codes`),0) `available_recovery_codes`, `recovery_codes_created_at`, `recovery_codes_last_used_at`, `created`, `last_login_attempt`
FROM `users`
WHERE `id`=$id
SQL);
	$user=SingleFetch($R);

	# Data normalization
	$user['groups']=\json_decode($user['groups'],true);
	$user['totp_enabled']=(bool)$user['totp_enabled'];
	$user['available_recovery_codes']=(int)$user['available_recovery_codes'];

	# Fetching user groups
	if($user['groups'])
	{
		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `title` FROM `groups` WHERE `id` IN (?)
SQL ,[join(',',$user['groups'])]);
		$user['groups']=$R->fetch_all(\MYSQLI_ASSOC);
		$R->free();
	}

	return(CMS::$T)('Overview',$user,MIN_PASSWORD_LENGTH,RecoveryGraceRemaining());
}

/** Get the remaining account recovery grace period, in seconds.
 * @return int Remaining time, or 0 if the grace period has expired.
 * @throws \Throwable */
function RecoveryGraceRemaining():int
{
	$R=CMS::$Db->Execute(<<<'SQL'
SELECT TIMESTAMPDIFF(SECOND, `created`, NOW())
FROM `a11n_userarea`
WHERE `a11n_id`=? AND `user_id`=? AND `way`='recovery' AND `created`>NOW() - INTERVAL ? SECOND
SQL ,[CMS::$a11n,CMS::$A->current,RECOVERY_GRACE_PERIOD]);
	$grace=SingleFetch($R,true);

	return $grace===false ? 0 : RECOVERY_GRACE_PERIOD-$grace;
}

/** Change the current user's password.
 * @return array Operation result.
 * @throws \Throwable */
function ChangePassword():array
{
	if(!CMS::$post)
		return[
			'ok'=>false
		];

	# PHP 8.6: migrate to pipe operator
	if(!\array_all([$_POST['password'] ?? 0,$_POST['verification'] ?? 0],fn($t)=>\is_string($t)))
		return[
			'ok'=>false,
			'error'=>'INSUFFICIENT'
		];

	if(\strlen($_POST['password'])<MIN_PASSWORD_LENGTH)
		return[
			'ok'=>false,
			'error'=>'LOW_PASSWORD_LENGTH'
		];

	$R=CMS::$Db->Execute(<<<SQL
SELECT `password_hash`, `totp_secret`, `totp_digits`, `recovery_codes`
FROM `users`
WHERE `id`=?
SQL ,[CMS::$A->current]);
	$user=SingleFetch($R);

	if(\password_verify($_POST['password'],$user['password_hash']))
		return[
			'ok'=>false,
			'error'=>'CURRENT_PASSWORD_USED'
		];

	include CMS.'recovery-codes.php';

	if(RecoveryGraceRemaining())
		$way='';
	elseif($user['password_hash']==='' or \password_verify($_POST['verification'],$user['password_hash']))
		$way='password';
	elseif($user['totp_secret']!==null and TOTP::Verify($user['totp_secret'],$_POST['verification'],(int)$user['totp_digits']))
		$way='totp';
	elseif($user['recovery_codes'] and VerifyRecoveryCode($_POST['verification'],$user['recovery_codes']))
		$way='recovery-code';
	else
		return[
			'ok'=>false,
			'error'=>'UNVERIFIED'
		];

	$amount=CMS::$Db->Update('users',[
		'password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT)
	],'`id`='.CMS::$A->current);

	return[
		'ok'=>$amount>0,
		'way'=>$way,
	];
}

/** Manage TOTP configuration for the current user.
 * @return array Operation result.
 * @throws \Throwable */
function Totp():array
{
	# Deleting TOTP
	if(CMS::$patch)
	{
		$test=\file_get_contents('php://input');
		$way=Verification($test);

		if(\is_array($way))
			return $way;

		$amount=CMS::$Db->Update('users',['totp_secret'=>null],'`id`='.CMS::$A->current);

		return[
			'ok'=>$amount>0,
			'way'=>$way,
		];
	}

	if(!CMS::$post)
		return[
			'ok'=>false
		];

	# Request otpauth:// URI
	# PHP 8.6: migrate to pipe operator
	if(\array_all([$_POST['issuer'] ?? 0,$_POST['digits'] ?? 0],fn($t)=>\is_string($t)))
	{
		$secret=\random_bytes(32);
		$digits=(int)$_POST['digits'];
		$name=GetUserData('name');

		# PHP 8.6: migrate to clamp function
		if($digits<6 or $digits>8)
			$digits=6;

		return[
			'ok'=>true,
			'uri'=>Totp::Uri($_POST['issuer'],$name,$secret,$digits),
			'secret'=>\bin2hex($secret),
		];
	}

	# PHP 8.6: migrate to pipe operator
	if(!\array_all([$_POST['secret'] ?? 0,$_POST['digits'] ?? 0,$_POST['code'] ?? 0,$_POST['verification'] ?? 0],fn($t)=>\is_string($t))
	or !ctype_xdigit($_POST['secret']) or \strlen($_POST['secret'])!=64 or !\is_numeric($_POST['digits']))
		return[
			'ok'=>false,
			'error'=>'INSUFFICIENT'
		];

	$secret=\hex2bin($_POST['secret']);
	$digits=(int)$_POST['digits'];

	# PHP 8.6: migrate to clamp function
	if($digits<6 or $digits>8)
		$digits=6;

	if(!Totp::Verify($secret,$_POST['code'],$digits))
		return[
			'ok'=>false,
			'error'=>'INCORRECT_CODE'
		];

	$way=Verification($_POST['verification']);

	if(\is_array($way))
		return $way;

	$amount=CMS::$Db->Update('users',[
		'totp_secret'=>$secret,
		'totp_digits'=>$digits,
	],'`id`='.CMS::$A->current);

	return[
		'ok'=>$amount>0,
		'way'=>$way,
	];
}

/** Manage recovery codes for the current user.
 * @return array Operation result.
 * @throws \Throwable */
function RecoveryCodes():array
{
	# Checking if recovery code is valid
	if(CMS::$put)
	{
		$R=CMS::$Db->Execute(<<<SQL
SELECT `recovery_codes` FROM `users` WHERE `id`=?
SQL ,[CMS::$A->current]);

		$hashes=SingleFetch($R,true);

		if($hashes)
			$hashes=\json_decode($hashes,true);

		# PHP 8.6: migrate to pipe operator
		$code=\file_get_contents('php://input');
		$code=\preg_replace('#[^\da-z]+#i','',$code)
				|> \strtoupper(...);

		return[
			'ok'=>\is_array($hashes) && \array_any($hashes,fn($hash)=>\password_verify($code,$hash)),
		];
	}

	# Storing new recovery codes
	if(CMS::$post)
	{
		if(!\array_all([$_POST['hash'] ?? 0,$_POST['session_id'] ?? 0,$_POST['verification'] ?? 0],fn($t)=>\is_string($t)))
			return[
				'ok'=>false,
				'error'=>'INSUFFICIENT'
			];

		\session_id($_POST['session_id']);
		\session_start([
			'use_cookies'=>false,
		]);

		if(!\is_array($_SESSION['recovery_codes'] ?? 0))
			return[
				'ok'=>false,
				'error'=>'BROKEN'
			];

		$hash=\hash('sha3-256',\join('',$_SESSION['recovery_codes']));

		if(!\hash_equals($hash,$_POST['hash']))
			return[
				'ok'=>false,
				'error'=>'BROKEN'
			];

		$way=Verification($_POST['verification']);

		if(\is_array($way))
			return $way;

		$amount=CMS::$Db->Update('users',[
			'recovery_codes'=>\json_encode($_SESSION['recovery_codes'],JSON),
		],'`id`='.CMS::$A->current);

		unset($_SESSION['recovery_codes']);

		return[
			'ok'=>$amount>0,
			'way'=>$way,
		];
	}

	include CMS.'recovery-codes.php';
	$codes=$hashes=[];

	for($i=0;$i<RECOVERY_CODE_COUNT;$i++)
	{
		$code=RecoveryCode();

		$codes[]=$code;
		$hashes[]=\password_hash($code,\PASSWORD_DEFAULT);
	}

	\session_start([
		'use_cookies'=>false,
	]);

	$hash=\hash('sha3-256',join('',$hashes));

	$_SESSION['recovery_codes']=$hashes;

	return[
		'ok'=>true,
		'codes'=>$codes,
		'hash'=>$hash,
		'session_id'=>\session_id(),
	];
}

/** Verify the current user's credential.
 * @param string $test Password, TOTP, or recovery code.
 * @return array|string Verification method, or error result.
 * @throws \Throwable */
function Verification(string$test):array|string
{
	if(RecoveryGraceRemaining())
		return '';

	$R=CMS::$Db->Execute(<<<SQL
SELECT `password_hash`, `totp_secret`, `totp_digits`, `recovery_codes`
FROM `users`
WHERE `id`=?
SQL ,[CMS::$A->current]);
	$user=SingleFetch($R);

	if($user['password_hash']==='' or \password_verify($test,$user['password_hash']))
		return 'password';

	if($user['totp_secret']!==null and TOTP::Verify($user['totp_secret'],$test,(int)$user['totp_digits']))
		return 'totp';

	include CMS.'recovery-codes.php';

	if($user['recovery_codes'] and VerifyRecoveryCode($test,$user['recovery_codes']))
		return 'recovery-code';

	return[
		'ok'=>false,
		'error'=>'UNVERIFIED'
	];
}

/** Settings of the user
 * @param Uri $Uri
 * @param int $code
 * @return array|string */
function Settings(Uri$Uri,int&$code):array|string
{
	if(!CMS::$A->current)
		Halt(401);

	# AJAX request
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		$id=CMS::$A->current;
		$update=[];

		# Storing string data
		foreach(['display_name','info','timezone',...(L10NS ? ['l10n'] : [])] as $k)
			if(\is_string($_POST[$k] ?? 0))
				$update[$k]=$_POST[$k];

		# Saving avatar
		if(\is_uploaded_file($_FILES['avatar']['tmp_name'] ?? '') and $_FILES['avatar']['size']<=MAX_AVATAR_SIZE)
		{
			$old=GetUserData('avatar');
			$img=\imagecreatefromwebp($_FILES['avatar']['tmp_name']);

			if($img!==false)
			{
				if($old)
					\Eleanor\Classes\Files::Delete(STATIC_PATH."avatars/$id-$old.webp");

				$salt=AvatarSalt();

				if(\imagewebp($img,STATIC_PATH."avatars/$id-$salt.webp"))
					$update['avatar']=$salt;
			}
		}

		return[
			'ok'=>$update and CMS::$Db->Update('users',$update,'`id`='.$id)>0
		];
	}

	$settings=GetUserData(['display_name','avatar','info','timezone',...(L10NS ? ['l10n'] : [])]);
	$timezones=array_merge(
		timezone_identifiers_list(\DateTimeZone::ASIA),
		timezone_identifiers_list(\DateTimeZone::EUROPE),
	);

	return(CMS::$T)('Settings',$settings,$timezones);
}

/** List of user sessions
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function Sessions(Uri$Uri,int&$code):array|string
{
	if(!CMS::$A->current)
		Halt(401);

	$R=CMS::$Db->Execute(<<<SQL
SELECT `created`
FROM `a11n_userarea`
WHERE `a11n_id`=? AND `user_id`=?
SQL ,[CMS::$a11n,CMS::$A->current]);
	$current=SingleFetch($R,true);

	# AJAX request
	if(CMS::$json)
	{
		if(CMS::$delete and isset($_GET['id']))
		{
			$a11n=(int)$_GET['id'];

			$R=CMS::$Db->Execute(<<<SQL
SELECT `a`.`id`
FROM `a11n_userarea` `u`
INNER JOIN `a11n` `a` ON `a`.`id`=`u`.`a11n_id`
WHERE `a11n_id`=? AND `u`.`user_id`=? AND (`u`.`created`>? OR `a`.`used`<NOW() - INTERVAL ? MONTH)
SQL ,[$a11n,CMS::$A->current,$current,MONTHS_TO_STALE_SESSION]);

			$amount=$R->num_rows>0
				? CMS::$Db->Delete('a11n_userarea','`a11n_id`=? AND `user_id`=?',[$a11n,CMS::$A->current])
				: 0;

			return[
				'ok'=>$amount>0
			];
		}

		return[
			'ok'=>false
		];
	}

	$items=[];
	$R=CMS::$Db->Execute(<<<SQL
SELECT `u`.`a11n_id`, `u`.`created`, `u`.`way`, `a`.`used`, `a`.`ip`, `a`.`ua`,
	IF(`u`.`created`>? OR `a`.`used`<NOW() - INTERVAL ? MONTH,1,0) `terminatable`
FROM `a11n_userarea` `u`
INNER JOIN `a11n` `a` ON `a`.`id`=`u`.`a11n_id`
WHERE `u`.`user_id`=? AND `u`.`way`!='admin-panel'
SQL ,[$current,MONTHS_TO_STALE_SESSION,CMS::$A->current]);
	foreach($R as $a)
	{
		$a['ip']=$a['ip'] ? \inet_ntop($a['ip']) : '';
		$a['a11n_id']=(int)$a['a11n_id'];
		$a['terminatable']=(bool)$a['terminatable'];

		$items[]=$a;
	}
	$R->free();

	return(CMS::$T)('Sessions',$items,MONTHS_TO_STALE_SESSION);
}

/** List of user login attempts
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function SignInHistory(Uri$Uri,int&$code):array|string
{
	if(!CMS::$A->current)
		Halt(401);

	//ToDo!
	die;
}

# Parsing uri
[$slug,$uri]=SlugTail($uri);

if(!CMS::$json)
{
	# Loading template of the unit
	CMS::$T[]=CMS."user-area/unit-$this->name/object.php";

	# Checking URI correctness via canonical urls
	Canonical($Uri,$slug);

	# Making links to alternative l10n versions of page
	Alternate(fn($code,$Uri)=>$Uri([$this->slug,$slug]));
}

# Links for user inside unit
if(!CMS::$json)
{
	if(CMS::$A->current)
	{
		CMS::$T['links']['overview']=(string)$Uri;

		foreach(['sign-up','sign-in','sign-out', 'settings','sessions','sign-in-history'] as $link)
			CMS::$T['links'][$link]=$Uri($link);
	}
	else
		foreach(['sign-up','sign-in'] as $link)
			CMS::$T['links'][$link]=$Uri($link);
}

return match($slug ?? ''){
	'sign-up'=>SignUp($Uri,$code),
	'sign-in'=>SignIn($Uri,$code),
	'sign-out'=>SignOut($Uri,$code),

	'settings'=>Settings($Uri,$code),
	'sessions'=>Sessions($Uri,$code),
	'sign-in-history'=>SignInHistory($Uri,$code),

	''=>Overview($Uri,$code),
	default=>Halt()
};