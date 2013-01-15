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
class LoginModer extends BaseClass implements LoginClass
{
	/**
	 * Аутентификация по определенным входящим параметрам, например, по логину и паролю
	 *
	 * @param array $data Массив с данными
	 * @throws EE
	 */
	public static function Login(array $b)
	{
		return false;
	}

	/**
	 * Авторизация пользователя: проверка является ли пользователь пользователем
	 *
	 * @param bool $hard Метод кэширует результат, для сброса кэша передайте true
	 * @return bool
	 */
	public static function IsUser($a=false)
	{
		return false;
	}

	/**
	 * Аутентификация только по ID пользователя
	 *
	 * @param int $id ID пользователя
	 * @throws EE
	 */
	public static function Auth($id){}

	/**
	 * Применение логина, как главного в системе: подстройка системы под пользователя, настройка часового пояса, проверка забаненности и т.п.
	 */
	public static function ApplyCheck()
	{

	}

	/**
	 * Выход пользователя из учетной записи
	 */
	public static function Logout()
	{
		return false;
	}

	/**
	 * Формирование ссылки на учётную запись пользователя
	 *
	 * @param string|array $name Имя пользователя
	 * @param string|array $id ID пользователя
	 * @return string|array|FALSE
	 */
	public static function UserLink($a,$b=0)
	{
		return false;
	}

	/**
	 * Получение значения пользовательского параметра
	 *
	 * @param array|string $key Один или несколько параметров, значения которых нужно получить
	 * @return array|string В зависимости от типа переданной переменной
	 */
	public static function GetUserValue($name,$id=0)
	{
		return false;
	}

	/**
	 * Установка значения пользовательского параметра. Метод не должен обновлять данны пользователя в БД! Только на время работы скрипта
	 *
	 * @param array|string $key Имя параметра, либо массив в виде $key=>$value
	 * @param mixed $value Значения
	 */
	public static function SetUserValue($key,$value=null)
	{

	}
}