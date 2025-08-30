<?php
namespace CMS;

use CMS\Enums\Events;

const
	/** Amount of seconds between login attempts */
	SECONDS=5,

	/** Minimum password length */
	MIN_PASSWORD_LENGTH=10,

	/** Default group for newly created users */
	USER_GROUP=3,

	/** Maximum amount of users per 1 authorization */
	MAX_USERS=25,

	/** The number of months after which a session is considered stale and can be terminated */
	MONTHS_TO_STALE_SESSION=3,

	/** Maximum avatar size in bytes, 1 MiB currently */
	MAX_AVATAR_SIZE=1048576;

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

/** Storing avatar from Telegram to server with convertation to WEBP
 * @param int $id ID of user
 * @param string $url contents of photo_url parameter from Telegram
 * @return string salt on success, empty string on error
 * @throws \Throwable */
function StoreAvatar(int$id,string$url):string
{
	if(!\function_exists('curl_init') or \preg_match('#\.(png|jpg)$#',$url,$m)==0)
		return'';

	$curl=\curl_init($url);
	$tmp=\tempnam(\sys_get_temp_dir(),'avatar-');
	$fh=\fopen($tmp,'w');

	\curl_setopt_array($curl,[
		\CURLOPT_TIMEOUT=>10,
		\CURLOPT_WRITEFUNCTION=>fn($ch,$bytes)=>fwrite($fh,$bytes),
	]);

	\curl_exec($curl);

	$info=\curl_getinfo($curl);
	$errno=\curl_errno($curl);

	\fclose($fh);
	\curl_close($curl);

	if($errno>0 or $info['http_code']!=200 or $info['size_download']<5120)
		return'';

	$im=$m[1]=='png' ? imagecreatefrompng($tmp) : imagecreatefromjpeg($tmp);

	#If no image or image size less than 25px
	if($im===false or imagesy($im)<25 or imagesx($im)<25)
		return'';

	$salt=AvatarSalt();
	\imagewebp($im,STATIC_PATH."avatars/{$id}-{$salt}.webp");
	\imagedestroy($im);

	return$salt;
}

/** Session start */
function Session(string$name='sign-up'):void
{
	\session_start([
		'name'=>$name,
		'use_cookies'=>true,
		'use_only_cookies'=>true,
		'cookie_path'=>\Eleanor\SITEDIR,
		'cookie_httponly'=>true,
		'cookie_secure'=>($_SERVER['HTTPS'] ?? '')=='on'
	]);
}

/** Signing in (register, enroll)
 * @param Classes\Uri $Uri
 * @return string|array */
function SignIn(Uri$Uri,int&$code):array|string
{
	$R=CMS::$Db->Execute(<<<SQL
SELECT COUNT(`a11n_id`) `total` FROM `a11n_userspace` WHERE `a11n_id`=? 
SQL ,[CMS::$a11n]);
	$total=(int)$R->fetch_column();

	#AJAX request
	if(CMS::$json)
	{
		if($total>=MAX_USERS)
			return['ok'=>false,'error'=>'USERS_LIMIT','max'=>MAX_USERS];

		#Via telegram
		if(\is_array($_POST['telegram'] ?? 0))
		{
			#Bot's key is missed
			if(!CMS::$config['system']['bot_key'])
				return['ok'=>false];

			$ok=\Eleanor\Classes\Telegram::CheckAuth($_POST['telegram'],CMS::$config['system']['bot_key']);

			if(!$ok)
				return['ok'=>false,'error'=>'INCORRECT'];

			$telegram=$_POST['telegram'];

			$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `avatar`, `telegram_username`
FROM `users`
WHERE `telegram_id`={$telegram['id']}
LIMIT 1
SQL );
			if($user=$R->fetch_assoc())
			{
				$id=(int)$user['id'];
				$upd=[];

				if(CMS::$A->current==$id)
					return['ok'=>false,'error'=>'ALREADY'];

				if($telegram['username'] and $user['telegram_username']!=$telegram['username'])
					$upd['telegram_username']=$telegram['username'];

				if(!$user['avatar'] and $telegram['photo_url'])
				{
					$salt=StoreAvatar($id,$telegram['photo_url']);

					if($salt)
						$upd['avatar']=$salt;
				}

				if($upd)
					CMS::$Db->Update('users',$upd,'`id`='.$user['id']);

				//2FA should be injected somewhere here
				CMS::$A->SignIn($id,extra:['way'=>'telegram']);

				Events::UserSignedIn->Trigger([
					'id'=>$id,
					'way'=>'telegram',
					'where'=>'userspace',
					'ip'=>CMS::$ip ? $_SERVER['REMOTE_ADDR'] : null,
					'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
				]);

				return['ok'=>true,'id'=>$id];
			}

			Session();
			$_SESSION=['telegram'=>$telegram];

			return['ok'=>true,'sign_up'=>$Uri('sign-up')];
		}

		#Sign in by username and password
		if(!IsS($_POST['username'] ?? 0,$_POST['password'] ?? 0,$_POST['captcha'] ?? 0) or !isset($_POST['temp']))
			return['ok'=>false,'error'=>'INSUFFICIENT'];

		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `password_hash`, TIMESTAMPDIFF(SECOND,`last_login_attempt`,NOW()) `seconds`
FROM `users`
WHERE `name`=?
LIMIT 1
SQL ,[$_POST['username']]);
		if(!$user=$R->fetch_assoc())
			return['ok'=>false,'error'=>'NOT_FOUND'];

		$id=(int)$user['id'];

		if(CMS::$A->current==$id)
			return['ok'=>false,'error'=>'ALREADY'];

		CMS::$Db->Update('users',['last_login_attempt'=>fn()=>'NOW()'],'`id`='.$user['id']);

		#Too ofter and no captcha
		if($user['seconds']<SECONDS and !\CMS\Classes\hCaptcha::Check('captcha'))
			return['ok'=>false,'error'=>'W8','seconds'=>SECONDS,'remain'=>SECONDS-$user['seconds']];

		$empty=$user['password_hash']==='';

		if($empty or \password_verify($_POST['password'],$user['password_hash']))
		{
			#Поддержим актуальность пароля
			if($empty or \password_needs_rehash($user['password_hash'],\PASSWORD_DEFAULT))
				CMS::$Db->Update('users',['password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT)],'`id`='.$user['id']);

			//2FA should be injected somewhere here
			CMS::$A->SignIn($id,(bool)$_POST['temp'],['way'=>'username']);

			Events::UserSignedIn->Trigger([
				'id'=>$id,
				'way'=>'username',
				'where'=>'userspace',
				'ip'=>CMS::$ip ? $_SERVER['REMOTE_ADDR'] : null,
				'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
			]);

			return['ok'=>true,'id'=>$id];
		}

		return['ok'=>false,'error'=>'WRONG_PASSWORD'];
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
SELECT `name` FROM `users` WHERE `name`=? LIMIT 1
SQL ,[$name]);

	return $R->num_rows==0;
}

/** Sign Up for new user
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function SignUp(Uri$Uri,int&$code):array|string
{
	Session();

	#Check availability of username
	if(CMS::$json and is_string($_GET['check_name'] ?? 0))
		return[
			'ok'=>CheckName($_GET['check_name']),
		];

	#No Telegram credentials have been provided
	if(!isset($_SESSION['telegram']))
	{
		$code=400;
		$error=CMS::$config['system']['bot_key'] ? 'MISSED_TELEGRAM' : 'NO_TELEGRAM';

		#AJAX request
		if(CMS::$json)
			return[
				'ok'=>false,
				'error'=>$error
			];

		return(CMS::$T)('SignUpError',$error);
	}

	$telegram=$_SESSION['telegram'];

	#AJAX request
	if(CMS::$json)
	{
		if(!IsS($_POST['name'] ?? 0,$_POST['display_name'] ?? 0,$_POST['password'] ?? 0))
			return[
				'ok'=>false,
				'error'=>'INSUFFICIENT'
			];

		if(!CheckName($_POST['name']))
			return[
				'ok'=>false,
				'error'=>'NAME_EXISTS'
			];

		if(strlen($_POST['password'])<MIN_PASSWORD_LENGTH)
			return[
				'ok'=>false,
				'error'=>'LOW_PASSWORD_LENGTH'
			];

		$group=USER_GROUP;
		$id=CMS::$Db->Insert('users',[
			'name'=>$_POST['name'],
			'display_name'=>$_POST['display_name'],
			'groups'=>"[{$group}]",
			'telegram_id'=>$telegram['id'],
			'telegram_username'=>$telegram['username'],
			'password_hash'=>\password_hash($_POST['password'],\PASSWORD_DEFAULT),
		]);

		if(!$id)
			return[
				'ok'=>false,
				'error'=>'SOMETHING_WENT_WRONG'
			];

		$avatar=StoreAvatar($id,$telegram['photo_url']);

		if($avatar)
			CMS::$Db->Update('users',['avatar'=>$avatar],'`id`='.$id);

		#Signing in to the site
		CMS::$A->SignIn($id,true,['way'=>'sign-up']);

		Events::UserCreated->Trigger(['id'=>$id]);

		return[
			'ok'=>true,
		];
	}

	#Resistration finished
	$R=CMS::$Db->Query(<<<SQL
SELECT `telegram_id` FROM `users` WHERE `telegram_id`={$telegram['id']}
LIMIT 1
SQL );
	if($R->num_rows>0)
		return(CMS::$T)('SignUpError','EXISTS');

	return(CMS::$T)('SignUp',MIN_PASSWORD_LENGTH,$telegram);
}

/** User's sign out
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function SignOut(Uri$Uri,int&$code):array|string
{
	if(CMS::$A->current)
		CMS::$A->SignOut();
	else
		$code=401;

	#AJAX request
	if(CMS::$json)
		return[
			'ok'=>true,
		];

	return(CMS::$T)('SignOut');
}

/** Settings of the user
 * @param Uri $Uri
 * @param int $code
 * @return array|string */
function Settings(Uri$Uri,int&$code):array|string
{
	if(!CMS::$A->current)
		Halt(401);

	#AJAX request
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		$id=CMS::$A->current;
		$update=[];

		#Storing string data
		foreach(['display_name','info','timezone',...(L10NS ? ['l10n'] : [])] as $k)
			if(\is_string($_POST[$k] ?? 0))
				$update[$k]=$_POST[$k];

		#Saving avatar
		if(\is_uploaded_file($_FILES['avatar']['tmp_name'] ?? '') and $_FILES['avatar']['size']<=MAX_AVATAR_SIZE)
		{
			$old=GetUsers('avatar');
			$img=\imagecreatefromwebp($_FILES['avatar']['tmp_name']);

			if($img!==false)
			{
				if($old)
					\Eleanor\Classes\Files::Delete(STATIC_PATH."avatars/{$id}-{$old}.webp");

				$salt=AvatarSalt();

				if(\imagewebp($img,STATIC_PATH."avatars/{$id}-{$salt}.webp"))
					$update['avatar']=$salt;

				\imagedestroy($img);
			}
		}

		return[
			'ok'=>$update and CMS::$Db->Update('users',$update,'`id`='.$id)>0
		];
	}

	$settings=GetUsers(['display_name','avatar','info','timezone',...(L10NS ? ['l10n'] : [])]);
	$timezones=array_merge(
		timezone_identifiers_list(\DateTimeZone::ASIA),
		timezone_identifiers_list(\DateTimeZone::EUROPE),
	);

	return(CMS::$T)('Settings',$settings,$timezones);
}

/** Change password of user's account
 * @param Uri $Uri
 * @param int $code
 * @return string|array */
function ChangePassword(Uri$Uri,int&$code):array|string
{
	if(!CMS::$A->current)
		Halt(401);

	$R=CMS::$Db->Execute(<<<SQL
SELECT `way` FROM `a11n_userspace` WHERE `a11n_id`=? AND `user_id`=? LIMIT 1
SQL ,[CMS::$a11n,CMS::$A->current]);
	$way=$R->fetch_column();
	$old_required=$way!='telegram' or GetUsers('password_hash')==='';

	#AJAX request
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		if(!IsS($_POST['new'] ?? 0,$_POST['old'] ?? ($old_required ? 0 : '')))
			return['ok'=>false,'error'=>'INSUFFICIENT'];

		#Checking the old password when it needed
		if($old_required)
		{
			$R=CMS::$Db->Execute(<<<SQL
SELECT `password_hash` FROM `users` WHERE `id`=? LIMIT 1
SQL ,[CMS::$A->current]);
			$hash=$R->fetch_column();

			if($hash!=='' and !\password_verify($_POST['old'],$hash))
				return['ok'=>false,'error'=>'INCORRECT'];
		}

		if(strlen($_POST['new'])<MIN_PASSWORD_LENGTH)
			return[
				'ok'=>false,
				'error'=>'LOW_PASSWORD_LENGTH'
			];

		$amount=CMS::$Db->Update('users',[
			'password_hash'=>\password_hash($_POST['new'],\PASSWORD_DEFAULT)
		],'`id`='.CMS::$A->current);

		return[
			'ok'=>$amount>0
		];
	}

	return(CMS::$T)('ChangePassword',MIN_PASSWORD_LENGTH,$old_required);
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
SELECT `created` FROM `a11n_userspace` WHERE `a11n_id`=? AND `user_id`=? LIMIT 1
SQL ,[CMS::$a11n,CMS::$A->current]);
	$current=$R->fetch_column();

	#AJAX request
	if(CMS::$json)
	{
		if(CMS::$delete and isset($_GET['id']))
		{
			$a11n=(int)$_GET['id'];

			$R=CMS::$Db->Execute(<<<SQL
SELECT `a`.`id`
FROM `a11n_userspace` `u`
INNER JOIN `a11n` `a` ON `a`.`id`=`u`.`a11n_id`
WHERE `a11n_id`=? AND `u`.`user_id`=? AND (`u`.`created`>? OR `a`.`used`<NOW() - INTERVAL ? MONTH)
LIMIT 1
SQL ,[$a11n,CMS::$A->current,$current,MONTHS_TO_STALE_SESSION]);

			$amount=$R->num_rows>0
				? CMS::$Db->Delete('a11n_userspace','`a11n_id`=? AND `user_id`=?',[$a11n,CMS::$A->current])
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
FROM `a11n_userspace` `u`
INNER JOIN `a11n` `a` ON `a`.`id`=`u`.`a11n_id`
WHERE `u`.`user_id`=? AND `u`.`way`!='dashboard'
SQL ,[$current,MONTHS_TO_STALE_SESSION,CMS::$A->current]);
	while($a=$R->fetch_assoc())
	{
		$a['ip']=$a['ip'] ? \inet_ntop($a['ip']) : '';
		$a['a11n_id']=(int)$a['a11n_id'];
		$a['terminatable']=(bool)$a['terminatable'];

		$items[]=$a;
	}

	return(CMS::$T)('Sessions',$items,MONTHS_TO_STALE_SESSION);
}

#Parsing uri
[$slug]=SlugUri($uri);

if(!CMS::$json)
{
	#Loading template of the unit
	CMS::$T->queue[]=require ROOT."userspace/unit-{$this->name}/object.php";

	#Checking URI correctness via canonical urls
	Canonical($Uri,$slug);
}

#Making links to alternative l10n versions of page
Alternate(fn($code,$Uri)=>$Uri([$this->slug,$slug]));

#Linkts for user inside unit
if(CMS::$A->current)
{
	CMS::$T->default['links']['sign-in']=(string)$Uri;

	foreach(['settings','sessions','change-password'] as $link)
		CMS::$T->default['links'][$link]=$Uri($link);
}

return match($slug ?? ''){
	'settings'=>Settings($Uri,$code),
	'sessions'=>Sessions($Uri,$code),
	'change-password'=>ChangePassword($Uri,$code),
	'sign-out'=>SignOut($Uri,$code),
	'sign-up'=>SignUp($Uri,$code),
	''=>SignIn($Uri,$code),
	default=>Halt()
};