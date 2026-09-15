<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use CMS\Classes\Paginator;
use Eleanor\Classes\{E, TOTP};

/** Admin of unit "users"
 * @var Classes\Uri4AdminPanel $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

const
	/** Minimum password length */
	MIN_PASSWORD_LENGTH=10,

	/** Number of recovery codes generated for a user. */
	RECOVERY_CODE_COUNT=10;

/** Checking availability of username
 * @param string $name Name to be checked
 * @param int $id User id who is currently using the name
 * @return bool */
function CheckName(string$name,int$id=0):bool
{
	if($name==='')
		return false;

	$R=CMS::$Db->Execute(<<<SQL
SELECT `name` FROM `users` WHERE `name`=? AND `id`!=$id
SQL ,[$name]);

	return $R->num_rows==0;
}

/** Userlist
 * @param bool $is_root Only administrators have right to edit users, site team can only view the list
 * @return array|string */
function Users(Classes\Uri4AdminPanel$Uri,bool$is_root):array|string
{
	if(CMS::$json)
	{
		if(!$is_root)
			return[
				'ok'=>false
			];

		$id=(int)($_GET['user'] ?? 0);

		# User removal
		if(CMS::$delete)
		{
			if(!$id or $id==CMS::$A->current)
				return[
					'ok'=>false
				];

			# Removing users
			CMS::$Db->Delete('users','`id`='.$id);

			# Removing avatar
			$avatars=\glob(STATIC_PATH."avatars/$id-*.webp",\GLOB_NOSORT);

			if($avatars)
				\array_walk($avatars,fn($avatar)=>\Eleanor\Classes\Files::Delete($avatar));

			return[
				'ok'=>true
			];
		}

		$action=$_GET['action'] ?? '';

		if(CMS::$post)
		{
			# Request otpauth:// URI for TOTP qr code
			if($action==='totp')
			{
				# PHP 8.6: migrate to pipe operator
				if(!\array_all([$_POST['issuer'] ?? 0,$_POST['digits'] ?? 0],fn($t)=>\is_string($t)))
					return['ok'=>false];

				$secret=\random_bytes(32);
				$digits=(int)$_POST['digits'];

				try{
					$name=GetUserData('name',$id);
				}
				catch(E){
					return['ok'=>false];
				}

				# PHP 8.6: migrate to clamp function
				if($digits<6 or $digits>8)
					$digits=6;

				return[
					'ok'=>true,
					'uri'=>Totp::Uri($_POST['issuer'],$name,$secret,$digits),
					'secret'=>\bin2hex($secret),
				];
			}

			# User create & update
			$data=[];

			# Storing TOTP
			if(isset($_POST['secret'],$_POST['digits'],$_POST['code']))
			{
				if(!\array_all([$_POST['secret'] ?? 0,$_POST['digits'] ?? 0,$_POST['code'] ?? 0],fn($t)=>\is_string($t))
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

				$data['totp_secret']=$secret;
				$data['totp_digits']=$digits;
			}

			# Storing recovery codes
			if(isset($_POST['recovery_codes'],$_POST['session_id']))
			{
				if(!\array_all([$_POST['recovery_codes'] ?? 0,$_POST['session_id'] ?? 0],fn($t)=>\is_string($t)))
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

				if(!\hash_equals($hash,$_POST['recovery_codes']))
					return[
						'ok'=>false,
						'error'=>'BROKEN'
					];

				$data['recovery_codes']=\json_encode($_SESSION['recovery_codes'],JSON);

				unset($_SESSION['recovery_codes']);
			}

			# Storing string data
			foreach(['name','display_name','info','comment','password',...(L10NS ? ['l10n'] : [])] as $k)
				if(\is_string($_POST[$k] ?? 0))
					$data[$k]=$_POST[$k];

			# Name
			if(isset($data['name']))
			{
				if(!CheckName($data['name'],$id))
					return[
						'ok'=>false,
						'error'=>'NAME_EXISTS'
					];
			}
			elseif(!$id)
				return[
					'ok'=>false,
					'error'=>'NAME_REQUIRED'
				];

			# Password
			if(isset($data['password']))
			{
				if(\strlen($data['password'])<MIN_PASSWORD_LENGTH)
					return[
						'ok'=>false,
						'error'=>'LOW_PASSWORD_LENGTH'
					];

				$data['password_hash']=\password_hash($data['password'],\PASSWORD_DEFAULT);
				unset($data['password']);
			}
			elseif(!$id)
				return[
					'ok'=>false,
					'error'=>'PASSWORD_REQUIRED'
				];

			if(\is_array($_POST['groups'] ?? 0) and \array_is_list($_POST['groups']))
				$data['groups']=\json_encode($_POST['groups'],JSON);
			elseif(!$id)
				return[
					'ok'=>false,
					'error'=>'GROUPS_REQUIRED'
				];

			# Updating user
			if($id)
			{
				$amount=CMS::$Db->Update('users',$data,'`id`='.$id);

				return[
					'ok'=>$amount>0
				];
			}

			# Creating user
			$id=CMS::$Db->Insert('users',$data);

			return[
				'ok'=>$id>0,
				'id'=>$id ?: null
			];
		}

		switch($id ? $action : '')
		{
			# Sign in into user's account
			case'sign-in':
				CMS::$Db->Replace('a11n_userarea',['user_id'=>$id,'a11n_id'=>CMS::$a11n,'way'=>'admin-panel']);

				return[
					'ok'=>true
				];

			# Clear TOTP credentials
			case'delete-totp':
				CMS::$Db->Update('users',['totp_secret'=>null],'`id`='.$id);

				return[
					'ok'=>true
				];

			# Renerating recovery codes
			case 'recovery-codes':
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

		# Get check user's name
		if(\is_string($_GET['check_name'] ?? 0))
		{
			if(CheckName($_GET['check_name'],$id))
				return[
					'ok'=>true
				];

			return[
				'ok'=>false,
				'error'=>'NAME_EXISTS'
			];
		}

		# Load data for modification
		$R=CMS::$Db->Query(<<<SQL
SELECT `name`, `groups`, `l10n`, `display_name`, `avatar`, `info`, `comment` FROM `users` WHERE `id`=$id
SQL );

		if(!$user=SingleFetch($R))
			return[
				'ok'=>false
			];

		$user['groups']=\json_decode($user['groups'],true) ?? [];

		return[
			'ok'=>true,
			'user'=>$user
		];
	}

	$page=$pp=null;
	$where=$params=[];

	# Filter by id and name
	$id=(int)($_GET['id'] ?? 0);
	$name=\is_string($_GET['name'] ?? 0) ? trim($_GET['name'],'%') : '';

	if($id>0)
		$where[]='`id`='.$id;

	if($name!='')
	{
		$where[]='`name` LIKE ?';
		$params[]="%$name%";
	}

	# Filter by group
	$group=(int)($_GET['group'] ?? 0);

	if($group)
		$where[]="JSON_CONTAINS(`groups`,'$group','$')";

	$where=$where ? 'WHERE '.join(' AND ',$where) : '';

	if(isset($_GET['total']))
		$total=(int)$_GET['total'];
	else
	{
		if($params)
			$R=CMS::$Db->Execute(<<<SQL
SELECT COUNT(`name`) FROM `users` $where
SQL, $params);
		else
			$R=CMS::$Db->Query(<<<SQL
SELECT COUNT(`id`) FROM `users`
$where
SQL);

		$total=(int)SingleFetch($R,true);
	}

	try{
		[$sort,$order,$limit]=Paginator::SortOrderLimit($total,['id','name'],true,$page,$pp);
	}catch(\OutOfBoundsException){
		$Uri->amp=false;
		Redirect($Uri);
	}

	# Fields available for root admins only
	$root_only=$is_root
		? ", `totp_changed_at`, IF(`password_hash`='',1,0) `empty_password`, `totp_secret` IS NOT NULL `totp_enabled`, IFNULL(JSON_LENGTH(`recovery_codes`),0) `available_recovery_codes`, IF(JSON_LENGTH(`recovery_codes`)=0,`recovery_codes_last_used_at`,`recovery_codes_created_at`) `recovery_codes_date`"
		: '';

	if($params)
		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `groups`, `created`, `activity`, `display_name`, `avatar`, `comment`$root_only
FROM `users`
$where
ORDER BY `$sort`$order
$limit
SQL, $params);
	else
		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `name`, `groups`, `created`, `activity`, `display_name`, `avatar`, `comment`$root_only
FROM `users`
$where
ORDER BY `$sort`$order
$limit
SQL);

	$items=(function()use($R){
		foreach($R as $a)
		{
			$a['id']=(int)$a['id'];
			$a['available_recovery_codes']=(int)$a['available_recovery_codes'];
			$a['groups']=\json_decode($a['groups'],true) ?? [];
			$a['totp_enabled']=(bool)$a['totp_enabled'];
			$a['empty_password']=(bool)$a['empty_password'];

			yield $a;
		}

		$R->free();
	})();

	$groups=(function(){
		$multi=L10NS!==null;
		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `title` FROM `groups`
SQL);

		foreach($R as $a)
		{
			$a['id']=(int)$a['id'];

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			yield $a;
		}

		$R->free();
	})();

	return (CMS::$T)('users',\compact('items','groups','total','sort','pp','is_root')+['desc'=>(bool)$order]);
}

/** List of groups of users
 * @param Classes\Uri4AdminPanel $Uri
 * @return array|string */
function Groups(Classes\Uri4AdminPanel $Uri):array|string
{
	if(CMS::$json)
	{
		# Group removal
		if(CMS::$delete)
		{
			$id=(int)($_GET['group'] ?? 0);

			if($id<5)
				return[
					'ok'=>false
				];

			CMS::$Db->Delete('groups','`id`='.$id);

			return[
				'ok'=>true
			];
		}

		# Group create & update
		if(CMS::$post)
		{
			$id=(int)($_GET['group'] ?? 0);
			$data=[];

			# Roles (not for predefined groups)
			if(($id<1 or $id>4) and \is_array($_POST['roles'] ?? 0))
				$data['roles']=join(',',$_POST['roles']);

			# Slow mode (not for root & team groups)
			if(($id<1 or $id>2) and isset($_POST['slow_mode']))
				$data['slow_mode']=(int)$_POST['slow_mode'];

			if(L10NS===null)
			{
				if(\is_string($_POST['title'] ?? 0))
					$data['title']=$_POST['title'];
				elseif(!$id)
					return[
						'ok'=>false,
						'error'=>'TITLE_REQUIRED'
					];
			}
			else
			{
				if(\is_array($_POST['title'] ?? 0))
					$data['title']=json_encode($_POST['title'],JSON);
				elseif(!$id)
					return[
						'ok'=>false,
						'error'=>'TITLE_REQUIRED'
					];

				//Extra space for future keys
			}

			# Updating group
			if($id)
			{
				$amount=CMS::$Db->Update('groups',$data,'`id`='.$id);

				return[
					'ok'=>$amount>0
				];
			}

			# Creating group
			$id=CMS::$Db->Insert('groups',$data);

			return[
				'ok'=>$id>0,
				'id'=>$id ?: null
			];
		}

		# Load data of the group for modification
		$id=(int)($_GET['group'] ?? 0);

		$R=CMS::$Db->Query(<<<SQL
SELECT `roles`, `title`, `slow_mode` FROM `groups` WHERE `id`=$id
SQL );

		if(!$group=SingleFetch($R))
			return[
				'ok'=>false
			];

		$group['roles']=$group['roles'] ? \explode(',',$group['roles']) : [];
		$group['slow_mode']=(int)$group['slow_mode'];

		if(L10NS!==null)
			$group['title']=\json_decode($group['title'],true);

		return[
			'ok'=>true,
			'group'=>$group
		];
	}

	$items=(function(){
		$multi=L10NS!==null;
		$R=CMS::$Db->Query('SELECT * FROM `groups` ORDER BY `id` ASC');

		foreach($R as $a)
		{
			$a['id']=(int)$a['id'];
			$a['slow_mode']=(int)$a['slow_mode'];
			$a['roles']=$a['roles'] ? \explode(',',$a['roles']) : [];

			$a['deletable']=$a['id']>4;

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			yield $a;
		}

		$R->free();
	})();

	$roles=\array_map(fn($item)=>$item->value,Enums\Roles::cases());

	return(CMS::$T)('groups',\compact('items','roles'));
}

function SignInHistory()
{
	//ToDo!
	die('?');
}

# Assigning folder with templates
if(!CMS::$json)
	CMS::$T[]=CMS.'admin-panel/'.$this->name;

$is_root=\in_array('root',CMS::$P->roles);

return match($_GET['zone'] ?? ''){
	'groups'=>$is_root ? Groups($Uri) : Halt(),
	'sign-in-history'=>$is_root ? SignInHistory($Uri,$is_root) : Halt(),
	''=>Users($Uri,$is_root),
	default=>Halt()
};