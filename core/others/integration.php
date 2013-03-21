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

#Класс обеспечения интеграции системы со сторонними скриптами
class Integration
{
	/**
	 * Создание пользователя. Должна возвратить ID, который будет записан в таблице пользователей системы в поле forum_id
	 *
	 * @param array $data Очищенные и приведенные к системному виду данные добавляемого пользователя
	 * @param array $raw "Сырые" данные (обычно в том виде, в котором они переданы методу Usermanager::Add
	 */
	public static function Add($data,$raw)
	{

	}

	/**
	 * Обновление пользователей
	 *
	 * @param array $data Очищенные и приведенные к системному виду данные добавляемого пользователя
	 * @param array $raw "Сырые" данные (обычно в том виде, в котором они переданы методу Usermanager::Add
	 * @param array|int $ids Идентификатор(ы) обновляемого пользователя
	 */
	public static function Update($data,$raw,$ids)
	{

	}

	/**
	 * Удаление пользователей
	 *
	 * @param array|int $ids Идентификатор(ы) удаляемого пользователя
	 */
	public static function Delete($ids)
	{

	}
}