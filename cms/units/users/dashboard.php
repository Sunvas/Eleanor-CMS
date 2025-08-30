<?php
namespace CMS;

use CMS\Classes\Paginator,
	Eleanor\Classes\L10n;

/** Users unit
 * @var Classes\UriDashboard $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

/** Minimum password length */
const MIN_PASSWORD_LENGTH=10;

/** Checking availability of username
 * @param string $name Name to be checked
 * @param int $id User id who is currently using the name
 * @return bool */
function CheckName(string$name,int$id=0):bool
{
	if($name==='')
		return false;

	$R=CMS::$Db->Execute(<<<SQL
SELECT `name` FROM `users` WHERE `name`=? AND `id`!={$id} LIMIT 1
SQL ,[$name]);

	return $R->num_rows==0;
}

/** Userlist
 * @param bool $is_admin Only admins have right to edit users, site team can only view the list
 * @return array|string */
function Users(Classes\UriDashboard$Uri,bool$is_admin):array|string
{
	if(CMS::$json)
	{
		if(!$is_admin)
			return[
				'ok'=>false
			];

		#User removal
		if(CMS::$delete)
		{
			$id=(int)($_GET['user'] ?? 0);

			if(!$id or $id==CMS::$A->current)
				return[
					'ok'=>false
				];

			#Removing users
			CMS::$Db->Delete('users','`id`='.$id);

			#Removing avatar
			$avatars=\glob(STATIC_PATH."avatars/{$id}-*.webp",\GLOB_NOSORT);

			if($avatars)
				array_walk($avatars,fn($avatar)=>\Eleanor\Classes\Files::Delete($avatar));

			return[
				'ok'=>true
			];
		}

		#User create & update
		if(CMS::$post)
		{
			$id=(int)($_GET['user'] ?? 0);
			$data=[];

			#Storing string data
			foreach(['name','display_name','info','comment','password',...(L10NS ? ['l10n'] : [])] as $k)
				if(\is_string($_POST[$k] ?? 0))
					$data[$k]=$_POST[$k];

			#Name
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

			#Password
			if(isset($data['password']))
			{
				if(strlen($data['password'])<MIN_PASSWORD_LENGTH)
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

			#Updating user
			if($id)
			{
				$amount=CMS::$Db->Update('users',$data,'`id`='.$id);

				return[
					'ok'=>$amount>0
				];
			}

			#Creating user
			$id=CMS::$Db->Insert('users',$data);

			return[
				'ok'=>$id>0,
				'id'=>$id ?: null
			];
		}

		#Sign in into user's account
		if(isset($_GET['sign-in']))
		{
			$id=(int)$_GET['sign-in'];

			try{
				CMS::$Db->Replace('a11n_userspace',['user_id'=>$id,'a11n_id'=>CMS::$a11n,'way'=>'dashboard']);
			}catch(\Throwable){
				return[
					'ok'=>false,
					'error'=>'SOMETHING_WENT_WRONG'
				];
			}

			return[
				'ok'=>true
			];
		}

		#Get user's data
		$id=(int)($_GET['user'] ?? 0);

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

		#Load data for modification
		$R=CMS::$Db->Query(<<<SQL
SELECT `name`, `groups`, `l10n`, `display_name`, `avatar`, `info`, `comment` FROM `users` WHERE `id`={$id}
SQL );
		if(!$user=$R->fetch_assoc())
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

	#Filter by id and name
	$id=(int)($_GET['id'] ?? 0);
	$name=\is_string($_GET['name'] ?? 0) ? trim($_GET['name'],'%') : '';

	if($id>0)
		$where[]='`id`='.$id;

	if($name!='')
	{
		$where[]='`name` LIKE ?';
		$params[]="%{$name}%";
	}

	#Filter by group
	$group=(int)($_GET['group'] ?? 0);

	if($group)
		$where[]="JSON_CONTAINS(`groups`,'{$group}','$')";

	$where=$where ? 'WHERE '.join(' AND ',$where) : '';

	if(isset($_GET['total']))
		$total=(int)$_GET['total'];
	else
	{
		if($params)
			$R=CMS::$Db->Execute(<<<SQL
SELECT COUNT(`name`) FROM `users` {$where}
SQL, $params);
		else
			$R=CMS::$Db->Query(<<<SQL
SELECT COUNT(`id`) FROM `users`
{$where}
SQL);

		$total=(int)$R->fetch_column();
	}

	try{
		[$sort,$order,$limit]=Paginator::SortOrderLimit($total,['id','name'],true,$page,$pp);
	}catch(\OutOfBoundsException){
		$Uri->amp=false;
		Redirect($Uri);
	}

	if($params)
		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `name`, `groups`, IF(`password_hash`='',1,0) `empty_password`, `created`, `activity`, `display_name`, `avatar`, `comment`, `telegram_username`
FROM `users`
{$where}
ORDER BY `{$sort}`{$order}
{$limit}
SQL, $params);
	else
		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `name`, `groups`, IF(`password_hash`='',1,0) `empty_password`, `created`, `activity`, `display_name`, `avatar`, `comment`, `telegram_username`
FROM `users`
{$where}
ORDER BY `{$sort}`{$order}
{$limit}
SQL);

	$users=(function()use($R){
		while($a=$R->fetch_assoc())
		{
			$a['id']=(int)$a['id'];
			$a['groups']=\json_decode($a['groups'],true) ?? [];
			$a['empty_password']=(bool)$a['empty_password'];

			yield$a;
		}
	})();

	$groups=(function(){
		$multi=L10NS!==null;

		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `title` FROM `groups`
SQL);
		while($a=$R->fetch_assoc())
		{
			$a['id']=(int)$a['id'];

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			yield $a;
		}
	})();

	return (CMS::$T)('users',\compact('users','groups','total','sort','pp','is_admin')+['desc'=>(bool)$order]);
}

/** List of groups of users
 * @param Classes\UriDashboard $Uri
 * @return array|string */
function Groups(Classes\UriDashboard$Uri):array|string
{
	if(CMS::$json)
	{
		#Group removal
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

		#Group create & update
		if(CMS::$post)
		{
			$id=(int)($_GET['group'] ?? 0);
			$data=[];

			#Roles
			if($id>4 and \is_array($_POST['roles'] ?? 0))
				$data['roles']=join(',',$_POST['roles']);

			#Slow mode
			if($id>2 and isset($_POST['slow_mode']))
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
			}

			#Updating group
			if($id)
			{
				$amount=CMS::$Db->Update('groups',$data,'`id`='.$id);

				return[
					'ok'=>$amount>0
				];
			}

			#Creating group
			$id=CMS::$Db->Insert('groups',$data);

			return[
				'ok'=>$id>0,
				'id'=>$id ?: null
			];
		}

		#Load data of the group for modification
		$id=(int)($_GET['group'] ?? 0);

		$R=CMS::$Db->Query(<<<SQL
SELECT `roles`, `title`, `slow_mode` FROM `groups` WHERE `id`={$id}
SQL );
		if(!$group=$R->fetch_assoc())
			return[
				'ok'=>false
			];

		$group['roles']=$group['roles'] ? \explode(',',$group['roles']) : [];

		if(L10NS!==null)
			$group['title']=\json_decode($group['title'],true);

		return[
			'ok'=>true,
			'group'=>$group
		];
	}

	$items=(function()use($Uri){
		$multi=L10NS!==null;

		$R=CMS::$Db->Query('SELECT * FROM `groups` ORDER BY `id` ASC');
		while($a=$R->fetch_assoc())
		{
			$a['id']=(int)$a['id'];
			$a['slow_mode']=(int)$a['slow_mode'];
			$a['roles']=$a['roles'] ? \explode(',',$a['roles']) : [];

			$a['filter_users']=$Uri(group:$a['id']);
			$a['deletable']=$a['id']>4;

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			yield $a;
		}
	})();

	$roles=\array_map(fn($item)=>$item->value,Enums\Roles::cases());

	return(CMS::$T)('groups',\compact('items','roles'));
}

if(!CMS::$json)
	CMS::$T->queue[]=ROOT.'dashboard/'.$this->name;

$is_admin=\in_array('admin',CMS::$P->roles);

return match($_GET['zone'] ?? ''){
	'groups'=>$is_admin ? Groups($Uri) : Halt(),
	''=>Users($Uri,$is_admin),
	default=>Halt()
};