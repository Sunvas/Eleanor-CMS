<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class LoginBase extends BaseClass implements LoginClass
{	const
		MAX_SESSIONS=10,#Максимальное число сессий
		UNIQUE='user',
		INTEGRATION=true;

	public
		$user=array(),
		$Plugin;
	protected
		$login;

	protected static
		$Instance;

	public function __get($n)
	{
		if($n=='Permissions')
		{
			if(!class_exists($n,false))
				include Eleanor::$root.'core/permissions.php';
			return$this->$n=new Permissions($this);
		}
		return parent::__get($n);
	}

	public static function getInstance()
	{		if(!isset(static::$Instance))
			static::$Instance=new static;
		return static::$Instance;	}

	protected function __construct()
	{
		if(static::INTEGRATION and is_file(Eleanor::$root.'core/login/integration.php'))
		{
			require Eleanor::$root.'core/login/integration.php';
			$this->Plugin=new Integration;
		}
	}

	public function Login(array$data,array$addon=array())
	{		if(!isset($data['name'],$data['password']))
			throw new EE('EMPTY_DATA',EE::INFO);
		$this->AuthByName($data['name'],$data['password'],$addon);

		$data+=array('rememberme'=>true);
		Eleanor::SetCookie(static::UNIQUE,base64_encode((isset($this->user['login_key']) ? $this->user['login_key'] : '').'|'.$this->user['id']),$data['rememberme'] ? false : 0,true);
		if(static::INTEGRATION and method_exists($this->Plugin,'Login'))
			$this->Plugin->Login($this->user);
		$this->login=true;
	}

	public function IsUser($hard=false)
	{
		if(isset($this->login) and !$hard)
			return$this->login;

		if(!$cookie=Eleanor::GetCookie(self::UNIQUE))
			return$this->login=false;

		list($k,$id)=explode('|',base64_decode($cookie),2);

		if(!$k or !$id or !$this->AuthByKey($id,$k))
			return$this->login=false;

		if($hard)
			unset($this->Permissions);

		if(self::INTEGRATION and method_exists($this->Plugin,'IsUser'))
			$this->Plugin->IsUser($this->user);
		return$this->login=true;
	}

	public function Logout($alls=false)
	{		$this->login=false;
		Eleanor::SetCookie(static::UNIQUE,false,365,true);
		if(isset($this->user['id']))
		{
			if(self::INTEGRATION and method_exists($this->Plugin,'LogOut'))
				$this->Plugin->LogOut($this->user);
			$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$this->user['id'].' LIMIT 1');
			if($a=$R->fetch_assoc())
			{
				$lks=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
				$cl=get_class($this);

				if($alls)
					unset($lks[$cl]);
				else
					unset($lks[$cl][$this->user['login_key']]);

				Eleanor::$Db->Update(P.'users_site',array('login_keys'=>$lks ? serialize($lks) : ''),'`id`='.$this->user['id'].' LIMIT 1');
				Eleanor::$Db->Delete(P.'sessions','`ip_guest`=\'\' AND `user_id`='.$this->user['id'].' AND `service`=\''.Eleanor::$service.'\'');
			}
		}
		$this->user=array();
	}

	public function UserLink($name,$id=0)
	{static$ma;
		$El=Eleanor::getInstance();
		if(!$ma)
		{			$ma=array_keys($El->modules['sections'],'user');
			if(!$ma)
				return false;
			$ma=reset($ma);		}		$a=array('module'=>$ma);
		if($name and $id)
			$a['user']=html_entity_decode($name);
		elseif($id)
			$a[]=array('userid'=>$id);
		else
			return false;
		return$El->Url->special.$El->Url->Construct($a,false,'');
	}

	public function AuthByName($name,$pass,array$addon=array())
	{		$addon+=array('ismd'=>false,'captcha'=>false);
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`pass_salt`,`pass_hash`,`ban_date`,`ban_explain`,`u`.`language`,`u`.`timezone`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`failed_logins`,`s`.`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.USERS_TABLE.'` `u` LEFT JOIN `'.P.'users_extra` USING(`id`) LEFT JOIN `'.P.'users_site` `s` USING(`id`) WHERE `u`.`name`='.Eleanor::$Db->Escape($name).' LIMIT 1');
			if(!$user=$R->fetch_assoc())
				throw new EE('NOT_FOUND',EE::INFO);
			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{				UserManager::Sync($user['id']);
				$R=Eleanor::$Db->Query('SELECT `forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`failed_logins`,`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_extra` INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
				$user+=$R->fetch_assoc();			}
		}
		else
		{			Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`pass_salt`,`pass_hash`,`register`,`last_visit`,`ban_date`,`ban_explain`,`language`,`timezone` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($name).' AND `temp`=0 LIMIT 1');
			if(!$user=Eleanor::$UsersDb->fetch_assoc())
				throw new EE('NOT_FOUND',EE::INFO);
			UserManager::Sync(array($user['id']=>array('full_name'=>$user['full_name'],'name'=>$user['name'],'register'=>$user['register'],'language'=>$user['language'])));
			$R=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`email`,`groups`,`groups_overload`,`failed_logins`,`login_keys`,`ip`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
			$user+=$R->fetch_assoc();		}
		$t=time();
		if(Eleanor::$vars['antibrute'])
		{
			$fls=$user['failed_logins'] ? unserialize($user['failed_logins']) : array();
			$acnt=(int)Eleanor::$vars['antibrute_cnt'];
			$atime=(int)Eleanor::$vars['antibrute_time'];
			if($fls)
			{
				usort($fls,function($a,$b)
				{
					$a=(int)$a[0];
					$b=(int)$b[0];
					if($a==$b)
						return 0;
					return$a>$b ? -1 : 1;
				});
				if(isset($fls[$acnt-1]) and (Eleanor::$vars['antibrute']==1 or !$addon['captcha']) and strtotime($user['last_visit'])<$fls[$acnt-1][0])
				{					$lt=$t-$fls[$acnt-1][0];
					if($lt<$atime)
					{
						if(Eleanor::$vars['antibrute']==2)
						{
							Eleanor::SetCookie('Captcha_'.get_class($this),$fls[$acnt-1][0]+$atime,($atime-$lt).'s');
							throw new EE('CAPTCHA',EE::INFO,array('remain'=>$atime-$lt));
						}
						throw new EE('TEMPORARILY_BLOCKED',EE::INFO,array('remain'=>$atime-$lt));
					}
				}
			}
		}
		if($user['pass_hash']===UserManager::PassHash($user['pass_salt'],$pass,$addon['ismd']))
			$this->SetUser($user);
		else
		{			if(Eleanor::$vars['antibrute'])
			{
				if(count($fls)>$acnt)
					array_splice($fls,$acnt);
				array_unshift($fls,array($t,Eleanor::$service,isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',Eleanor::$ip));
				Eleanor::$Db->Update(P.'users_site',array('failed_logins'=>serialize($fls)),'`id`='.$user['id'].' LIMIT 1');
				if(isset($fls[$acnt-1]))
				{
					$lt=$t-$fls[$acnt-1][0];
					if($lt<$atime and strtotime($user['last_visit'])<$fls[$acnt-1][0])
						if(Eleanor::$vars['antibrute']==1)
							throw new EE('TEMPORARILY_BLOCKED',EE::INFO,array('remain'=>$atime-$lt));
						else
						{
							Eleanor::SetCookie('Captcha_'.get_class($this),$fls[$acnt-1][0]+$atime,($atime-$lt).'s');
							throw new EE('WRONG_PASSWORD',EE::INFO,array('captcha'=>true,'remain'=>$atime-$lt));
						}
				}
			}
			throw new EE('WRONG_PASSWORD',EE::INFO);
		}
	}

	public function AuthByKey($id,$k)
	{		if(Eleanor::$Db===Eleanor::$UsersDb)
		{			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`ban_date`,`ban_explain`,`u`.`language`,`staticip`,`u`.`timezone`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`s`.`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.USERS_TABLE.'` `u` LEFT JOIN `'.P.'users_extra` USING(`id`) LEFT JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			if(!$user=$R->fetch_assoc())
				return false;
			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{
				UserManager::Sync($user['id']);
				$R=Eleanor::$Db->Query('SELECT `forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_extra` INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
				$user+=$R->fetch_assoc();
			}
		}
		else
		{			$R2=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`last_visit`,`ban_date`,`ban_explain`,`language`,`timezone`,`staticip` FROM `'.USERS_TABLE.'` WHERE `id`='.(int)$id.' AND `temp`=0 LIMIT 1');
			if(!$user=$R2->fetch_assoc())
				return false;
			UserManager::Sync(array($user['id']=>array('full_name'=>$user['full_name'],'name'=>$user['name'],'register'=>$user['register'],'language'=>$user['language'])));
			$R3=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			$user+=$R3->fetch_assoc();
		}
		$lks=$user['login_keys'] ? (array)unserialize($user['login_keys']) : array();
		$user['groups_overload']=$user['groups_overload'] ? unserialize($user['groups_overload']) : array();
		$user['groups']=$user['groups'] ? explode(',,',trim($user['groups'],',')) : array();
		$cl=get_class($this);
		if(!isset($lks[$cl][$k]) or $user['staticip'] and $lks[$cl][$k][1]!=Eleanor::$ip)
			return false;
		$t=time();
		if($lks[$cl][$k][0]-Eleanor::$vars['time_online'][$cl]<$t or strtotime($user['last_visit'])<mktime(0,0,0))
		{			$lks[$cl][$k][0]=Eleanor::$vars['time_online'][$cl]+$t;
			Eleanor::$Db->Update(P.'users_site',array('login_keys'=>serialize($lks),'!last_visit'=>'NOW()','ip'=>Eleanor::$ip),'`id`='.$user['id'].' LIMIT 1');
			Eleanor::$UsersDb->Update(USERS_TABLE,array('!last_visit'=>'NOW()'),'`id`='.$user['id'].' LIMIT 1');
		}
		unset($user['login_keys'],$user['ip']);
		$user['login_key']=$k;
		$this->user=$user;
		return true;
	}

	#Жесткая авторизация. Мы указываем только ID пользователя и авторизумся! Для external_auth прежде всего.
	public function Auth($id,$data=array())
	{
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`ban_date`,`ban_explain`,`u`.`language`,`staticip`,`u`.`timezone`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`s`.`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.USERS_TABLE.'` `u` LEFT JOIN `'.P.'users_extra` USING(`id`) LEFT JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			if(!$user=$R->fetch_assoc())
				return false;
			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{
				UserManager::Sync($user['id']);
				$R=Eleanor::$Db->Query('SELECT `forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_extra` INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
				$user+=$R->fetch_assoc();
			}
		}
		else
		{
			Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`last_visit`,`ban_date`,`ban_explain`,`language`,`timezone`,`staticip` FROM `'.USERS_TABLE.'` WHERE `id`='.(int)$id.' AND `temp`=0 LIMIT 1');
			if(!$user=Eleanor::$UsersDb->fetch_assoc())
				return false;
			UserManager::Sync(array($user['id']=>array('full_name'=>$user['full_name'],'register'=>$user['register'],'name'=>$user['name'],'language'=>$user['language'])));
			$R=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			$user+=$R->fetch_assoc();
		}
		$this->SetUser($user);
		$data+=array('rememberme'=>true);
		Eleanor::SetCookie(static::UNIQUE,base64_encode((isset($this->user['login_key']) ? $this->user['login_key'] : '').'|'.$this->user['id']),$data['rememberme'] ? false : 0,true);
		if(static::INTEGRATION and method_exists($this->Plugin,'Login'))
			$this->Plugin->Auth($this->user);
		return true;
	}

	protected function SetUser($user)
	{		$lks=$user['login_keys'] ? unserialize($user['login_keys']) : array();
		$user['login_key']=md5(uniqid($user['id']));
		$user['groups_overload']=$user['groups_overload'] ? unserialize($user['groups_overload']) : array();
		$user['groups']=$user['groups'] ? explode(',,',trim($user['groups'],',')) : array();
		$cl=get_class($this);
		$lks[$cl][$user['login_key']]=array(Eleanor::$vars['time_online'][$cl]+time(),Eleanor::$ip,getenv('HTTP_USER_AGENT'));
		if(count($lks[$cl])>static::MAX_SESSIONS)
			array_splice($lks[$cl],0,static::MAX_SESSIONS);
		Eleanor::$Db->Update(P.'users_site',array('login_keys'=>serialize($lks),'ip'=>Eleanor::$ip,'!last_visit'=>'NOW()'),'`id`='.(int)$user['id'].' LIMIT 1');
		Eleanor::$UsersDb->Update(USERS_TABLE,array('!last_visit'=>'NOW()'),'`id`='.(int)$user['id'].' LIMIT 1');
		Eleanor::$Db->Delete(P.'sessions','`ip_guest`=\''.Eleanor::$ip.'\'');
		unset($user['failed_logins'],$user['pass_salt'],$user['pass_hash'],$user['login_keys']);
		if(Eleanor::$vars['antibrute']==2)
			Eleanor::SetCookie('Captcha_'.get_class($this),false);
		$this->user=$user;	}

	public function ApplyCheck()
	{
		if($this->user['ban_date'] and 0<strtotime($this->user['ban_date'])-time())
			throw new EE($this->user['ban_explain'],EE::BAN,array('date'=>$this->user['ban_date']));
	}

	public function GetUserValue($param,$safe=true,$query=true)
	{		if(!$isa=is_array($param))
			$param=(array)$param;
		$pnew=$res=array();
		foreach($param as &$v)
			if(array_key_exists($v,$this->user))
				$res[$v]=$safe ? FilterArrays::Filter($this->user[$v]) : $this->user[$v];
			else
				$pnew[]=$v;
		if($pnew and $query and isset($this->user['id']))
		{
			$R=Eleanor::$Db->Query('SELECT `'.join('`,`',$pnew).'` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.(int)$this->user['id'].' LIMIT 1');
			if($a=$R->fetch_assoc())
			{
				$this->user+=$a;
				$res+=$this->GetUserValue($pnew,$safe,false);
			}
		}
		return$isa ? $res : reset($res);
	}
}