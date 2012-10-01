<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон по умолчанию для админки модуля обратной связи
	Рекомендуется скопировать этот файл в templates/[шаблон админки]/Classes/[имя этого файла] и там уже начинать править.
	В случае если такой файл уже существует - правьте его.
*/
class TplAdminContacts
{	/*
		Страница редактирование параметров обратной связи
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$error - ошибка, если ошибка пустая - значит ее нету
	*/
	public static function Contacts($controls,$values,$error)
	{

	}

	/*
		Элемент шаблона. Таблица ввода электронных адресов получателей обратной связи
		$n - имя-префикс всех контролов
		$emails - массив получателей формат email=>имя
	*/
	public static function LoadWhom($n,$emails)
	{

	}
}