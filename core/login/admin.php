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

if(!class_exists('LoginBase',false))
	include __dir__.'/base.php';

class LoginAdmin extends LoginBase implements LoginClass
{
	const
		MAX_SESSIONS=1,#Максимальное число сессий
		UNIQUE='admin';

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
		if(!static::CheckPermission())
		{
			static::Logout();
			throw new EE('ACCESS_DENIED',EE::UNIT);
		}

		#:-)
		if(extension_loaded('ionCube Loader'))
			new Settings;

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

		if(!static::CheckPermission())
		{
			static::Logout();
			return static::$login=false;
		}
		return static::$login=true;
	}

	/**
	 * Формирование ссылки на учётную запись пользователя
	 *
	 * @param string|array $name Имя пользователя
	 * @param string|array $id ID пользователя
	 * @return string|array|FALSE
	 */
	public static function UserLink($username,$uid=0)
	{
		if(!static::IsUser())
			return false;
		return$uid ? Eleanor::$services['admin']['file'].'?'.Eleanor::getInstance()->Url->Construct(array('section'=>'management','module'=>'users','edit'=>$uid),false) : '';
	}

	/**
	 * Применение логина, как главного в системе: подстройка системы под пользователя, настройка часового пояса, проверка забаненности и т.п.
	 */
	public static function ApplyCheck(){}

	/**
	 * Проверка наличия у пользователя права входить в панель администратора
	 */
	private static function CheckPermission()
	{
		return in_array(1,Eleanor::GetPermission('access_cp',__class__));
	}
}