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
{
	const
		MAX_SESSIONS=10,#Максимальное число сессий
		UNIQUE='user';#Для упрощения наследования этого класса создана эта константа. Наследуем класс, меняем константу: вуаля! и у нас новый класс логина системы

	protected static
		$user=array(),
		$login,
		$Plugin;

	/**
	 * Аутентификация по определенным входящим параметрам, например, по логину и паролю
	 *
	 * @param array $data Массив с данными
	 * @throws EE
	 */
	public static function Login(array$data,array$extra=array())
	{
		if(!isset($data['name'],$data['password']))
			throw new EE('EMPTY_DATA',EE::UNIT);
		static::AuthByName($data['name'],$data['password'],$extra);

		$data+=array('rememberme'=>true);
		Eleanor::SetCookie(static::UNIQUE,base64_encode((isset(static::$user['login_key']) ? static::$user['login_key'] : '').'|'.static::$user['id']),$data['rememberme'] ? false : 0,true);
		static::$login=true;
	}

	/**
	 * Авторизация пользователя: проверка является ли пользователь пользователем
	 *
	 * @param bool $hard Метод кэширует результат, для сброса кэша передайте true
	 * @return bool
	 */
	public static function IsUser($hard=false)
	{
		if(isset(static::$login) and !$hard)
			return static::$login;

		if(!$cookie=Eleanor::GetCookie(static::UNIQUE))
			return static::$login=false;

		list($k,$id)=explode('|',base64_decode($cookie),2);

		if(!$k or !$id or !static::AuthByKey($id,$k))
			return static::$login=false;

		return static::$login=true;
	}

	/**
	 * Выход пользователя из учетной записи
	 */
	public static function Logout($alls=false)
	{
		static::$login=false;
		Eleanor::SetCookie(static::UNIQUE,false,365,true);
		if(isset(static::$user['id']))
		{
			$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.static::$user['id'].' LIMIT 1');
			if($a=$R->fetch_assoc())
			{
				$lks=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
				$cl=get_called_class();

				if($alls)
					unset($lks[$cl]);
				else
					unset($lks[$cl][ static::$user['login_key'] ]);

				Eleanor::$Db->Update(P.'users_site',array('login_keys'=>$lks ? serialize($lks) : ''),'`id`='.static::$user['id'].' LIMIT 1');
				Eleanor::$Db->Delete(P.'sessions','`ip_guest`=\'\' AND `user_id`='.static::$user['id'].' AND `service`=\''.Eleanor::$service.'\'');
			}
		}
		static::$user=array();
	}

	protected static
		$ma;

	/**
	 * Формирование ссылки на учётную запись пользователя
	 *
	 * @param string|array $name Имя пользователя
	 * @param string|array $id ID пользователя
	 * @return string|array|FALSE
	 */
	public static function UserLink($name,$id=0)
	{
		#ToDo! Input array
		$El=Eleanor::getInstance();
		if(!static::$ma)
		{
			static::$ma=array_keys($El->modules['sections'],'user');
			if(!static::$ma)
				return false;
			static::$ma=reset(static::$ma);
		}
		$a=array('module'=>static::$ma);
		if($name and $id)
			$a['user']=html_entity_decode($name);
		elseif($id)
			$a[]=array('userid'=>$id);
		else
			return false;
		return$El->Url->special.$El->Url->Construct($a,false,'');
	}

	/**
	 * Аутентификация пользователя его имени и паролю
	 *
	 * @param string $name Имя пользователя
	 * @param string $pass Пароль пользователя
	 * @param array $extra Дополнительные параметры аутентификации, возможные ключи массива:
	 * ismd Признак того, что пароль уже преобразован в md5 (на стороне клиента для защиты от снифферов)
	 * captcha Признак того, что пользователь корректно ввел капчу (защита от подбора пароля)
	 */
	public static function AuthByName($name,$pass,array$extra=array())
	{
		$extra+=array('ismd'=>false,'captcha'=>false);
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`pass_salt`,`pass_hash`,`banned_until`,`ban_explain`,`u`.`language`,`u`.`timezone`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`failed_logins`,`s`.`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.USERS_TABLE.'` `u` LEFT JOIN `'.P.'users_extra` USING(`id`) LEFT JOIN `'.P.'users_site` `s` USING(`id`) WHERE `u`.`name`='.Eleanor::$Db->Escape($name).' LIMIT 1');
			if(!$user=$R->fetch_assoc())
				throw new EE('NOT_FOUND',EE::UNIT);
			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{
				UserManager::Sync($user['id']);
				$R=Eleanor::$Db->Query('SELECT `forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`failed_logins`,`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_extra` INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
				$user+=$R->fetch_assoc();
			}
		}
		else
		{
			Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`pass_salt`,`pass_hash`,`register`,`last_visit`,`banned_until`,`ban_explain`,`language`,`timezone` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($name).' AND `temp`=0 LIMIT 1');
			if(!$user=Eleanor::$UsersDb->fetch_assoc())
				throw new EE('NOT_FOUND',EE::UNIT);
			UserManager::Sync(array($user['id']=>array('full_name'=>$user['full_name'],'name'=>$user['name'],'register'=>$user['register'],'language'=>$user['language'])));
			$R=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`email`,`groups`,`groups_overload`,`failed_logins`,`login_keys`,`ip`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
			$user+=$R->fetch_assoc();
		}
		$t=time();
		if(Eleanor::$vars['antibrute'])
		{
			$fls=$user['failed_logins'] ? unserialize($user['failed_logins']) : array();
			$acnt=(int)Eleanor::$vars['antibrute_cnt'];
			$atime=(int)Eleanor::$vars['antibrute_time'];
			if($fls)
			{
				usort($fls,function($a,$b){
					$a=(int)$a[0];
					$b=(int)$b[0];
					if($a==$b)
						return 0;
					return$a>$b ? -1 : 1;
				});
				if(isset($fls[$acnt-1]) and (Eleanor::$vars['antibrute']==1 or !$extra['captcha']) and strtotime($user['last_visit'])<$fls[$acnt-1][0])
				{
					$lt=$t-$fls[$acnt-1][0];
					if($lt<$atime)
					{
						if(Eleanor::$vars['antibrute']==2)
						{
							Eleanor::SetCookie('Captcha_'.get_called_class(),$fls[$acnt-1][0]+$atime,($atime-$lt).'s');
							throw new EE('CAPTCHA',EE::UNIT,array('remain'=>$atime-$lt));
						}
						throw new EE('TEMPORARILY_BLOCKED',EE::UNIT,array('remain'=>$atime-$lt));
					}
				}
			}
		}
		if($user['pass_hash']===UserManager::PassHash($user['pass_salt'],$pass,$extra['ismd']))
			static::SetUser($user);
		else
		{
			if(Eleanor::$vars['antibrute'])
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
							throw new EE('TEMPORARILY_BLOCKED',EE::UNIT,array('remain'=>$atime-$lt));
						else
						{
							Eleanor::SetCookie('Captcha_'.get_called_class(),$fls[$acnt-1][0]+$atime,($atime-$lt).'s');
							throw new EE('WRONG_PASSWORD',EE::UNIT,array('captcha'=>true,'remain'=>$atime-$lt));
						}
				}
			}
			throw new EE('WRONG_PASSWORD',EE::UNIT);
		}
	}

	/**
	 * Авторизация пользователя по ключу
	 *
	 * @param int $id ID пользователя
	 * @param string $k Ключ пользователя
	 */
	public static function AuthByKey($id,$k)
	{
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`banned_until`,`ban_explain`,`u`.`language`,`staticip`,`u`.`timezone`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`s`.`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.USERS_TABLE.'` `u` LEFT JOIN `'.P.'users_extra` USING(`id`) LEFT JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
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
		{
			$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`last_visit`,`banned_until`,`ban_explain`,`language`,`timezone`,`staticip` FROM `'.USERS_TABLE.'` WHERE `id`='.(int)$id.' AND `temp`=0 LIMIT 1');
			if(!$user=$R->fetch_assoc())
				return false;
			UserManager::Sync(array($user['id']=>array('full_name'=>$user['full_name'],'name'=>$user['name'],'register'=>$user['register'],'language'=>$user['language'])));
			$R=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			$user+=$R->fetch_assoc();
		}
		$lks=$user['login_keys'] ? (array)unserialize($user['login_keys']) : array();
		$user['groups_overload']=$user['groups_overload'] ? unserialize($user['groups_overload']) : array();
		$user['groups']=$user['groups'] ? explode(',,',trim($user['groups'],',')) : array();
		$cl=get_called_class();
		if(!isset($lks[$cl][$k]) or $user['staticip'] and $lks[$cl][$k][1]!=Eleanor::$ip)
			return false;
		$t=time();
		if($lks[$cl][$k][0]-Eleanor::$vars['time_online'][$cl]<$t or strtotime($user['last_visit'])<mktime(0,0,0))
		{
			$lks[$cl][$k][0]=Eleanor::$vars['time_online'][$cl]+$t;
			Eleanor::$Db->Update(P.'users_site',array('login_keys'=>serialize($lks),'!last_visit'=>'NOW()','ip'=>Eleanor::$ip),'`id`='.$user['id'].' LIMIT 1');
			Eleanor::$UsersDb->Update(USERS_TABLE,array('!last_visit'=>'NOW()'),'`id`='.$user['id'].' LIMIT 1');
		}
		unset($user['login_keys'],$user['ip']);
		$user['login_key']=$k;
		static::$user=$user;
		return true;
	}

	/**
	 * Аутентификация только по ID пользователя. Прежде всего для External
	 *
	 * @param int $id ID пользователя
	 * @throws EE
	 */
	public static function Auth($id,$data=array())
	{
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`banned_until`,`ban_explain`,`u`.`language`,`staticip`,`u`.`timezone`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`s`.`last_visit`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.USERS_TABLE.'` `u` LEFT JOIN `'.P.'users_extra` USING(`id`) LEFT JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
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
			Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`last_visit`,`banned_until`,`ban_explain`,`language`,`timezone`,`staticip` FROM `'.USERS_TABLE.'` WHERE `id`='.(int)$id.' AND `temp`=0 LIMIT 1');
			if(!$user=Eleanor::$UsersDb->fetch_assoc())
				return false;
			UserManager::Sync(array($user['id']=>array('full_name'=>$user['full_name'],'register'=>$user['register'],'name'=>$user['name'],'language'=>$user['language'])));
			$R=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`theme`,`avatar_location`,`avatar_type`,`editor` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			$user+=$R->fetch_assoc();
		}
		static::SetUser($user);
		$data+=array('rememberme'=>true);
		Eleanor::SetCookie(static::UNIQUE,base64_encode((isset(static::$user['login_key']) ? static::$user['login_key'] : '').'|'.static::$user['id']),$data['rememberme'] ? false : 0,true);
		return true;
	}

	/**
	 * Метод, вызываемый после успешной авторизации и аутентификации пользователя: обрабатывает данные и заносит их в таблицу
	 *
	 * @param array $user Данные пользователя
	 */
	protected static function SetUser(array$user)
	{
		$lks=$user['login_keys'] ? unserialize($user['login_keys']) : array();
		$user['login_key']=md5(uniqid($user['id']));
		$user['groups_overload']=$user['groups_overload'] ? unserialize($user['groups_overload']) : array();
		$user['groups']=$user['groups'] ? explode(',,',trim($user['groups'],',')) : array();
		$cl=get_called_class();
		$lks[$cl][$user['login_key']]=array(Eleanor::$vars['time_online'][$cl]+time(),Eleanor::$ip,getenv('HTTP_USER_AGENT'));
		if(count($lks[$cl])>static::MAX_SESSIONS)
			array_splice($lks[$cl],0,static::MAX_SESSIONS);
		Eleanor::$Db->Update(P.'users_site',array('login_keys'=>serialize($lks),'ip'=>Eleanor::$ip,'!last_visit'=>'NOW()'),'`id`='.(int)$user['id'].' LIMIT 1');
		Eleanor::$UsersDb->Update(USERS_TABLE,array('!last_visit'=>'NOW()'),'`id`='.(int)$user['id'].' LIMIT 1');
		Eleanor::$Db->Delete(P.'sessions','`ip_guest`=\''.Eleanor::$ip.'\'');
		unset($user['failed_logins'],$user['pass_salt'],$user['pass_hash'],$user['login_keys']);
		if(Eleanor::$vars['antibrute']==2)
			Eleanor::SetCookie('Captcha_'.get_called_class(),false);
		static::$user=$user;
	}

	/**
	 * Применение логина, как главного в системе: подстройка системы под пользователя, настройка часового пояса, проверка забаненности и т.п.
	 *
	 * @throws EE
	 */
	public static function ApplyCheck()
	{
		if(static::$user['banned_until'] and 0<strtotime(static::$user['banned_until'])-time())
			throw new EE(static::$user['ban_explain'],EE::USER,array('ban'=>'user','banned_until'=>static::$user['banned_until']));
	}

	/**
	 * Получение значения пользовательского параметра
	 *
	 * @param array|string $key Один или несколько параметров, значения которых нужно получить
	 * @return array|string В зависимости от типа переданной переменной
	 */
	public static function GetUserValue($param,$safe=true,$query=true)
	{
		if(!$isa=is_array($param))
			$param=(array)$param;
		$pnew=$res=array();
		foreach($param as &$v)
			if(array_key_exists($v,static::$user))
				$res[$v]=$safe ? GlobalsWrapper::Filter(static::$user[$v]) : static::$user[$v];
			else
				$pnew[]=$v;
		if($pnew and $query and isset(static::$user['id']))
		{
			$R=Eleanor::$Db->Query('SELECT `'.join('`,`',$pnew).'` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING(`id`) WHERE `id`='.(int)static::$user['id'].' LIMIT 1');
			if($a=$R->fetch_assoc())
			{
				static::$user+=$a;
				$res+=static::GetUserValue($pnew,$safe,false);
			}
		}
		return$isa ? $res : reset($res);
	}

	/**
	 * Установка значения пользовательского параметра. Метод не должен обновлять данны пользователя в БД! Только на время работы скрипта
	 *
	 * @param array|string $key Имя параметра, либо массив в виде $key=>$value
	 * @param mixed $value Значения
	 */
	public static function SetUserValue($key,$value=null)
	{
		if(is_array($key))
			static::$user=$key+static::$user;
		else
			static::$user[$key]=$value;
	}
}