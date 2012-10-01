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

#Класс для обеспечения интеграции системы со сторонними скриптами
class Integration
{	/*
		Функция создания пользователя. Должна возвратить ID, который будет записан в таблице пользователей системы в поле forum_id
		$data - данные пользователя, которого добавляют
		$raw - "сырые" данные, полученые в месте, где пользователь создается
	*/
	public static function Add($data,$raw)
	{	}

	/*
		Функция обновления пользователей
		$data - данные, которые необходимо обновлить
		$raw - "сырые" данные, полученые в месте, где пользователь создается
		$ids - иды пользователей (массив или ИД)
	*/
	public static function Update($data,$raw,$ids)
	{	}

	/*
		Функция удаление пользователей
		$ids - иды пользователей (массив или ИД)
	*/
	public static function Delete($ids)
	{	}
}