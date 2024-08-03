<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym


	Шаблоны по умолчанию для админки модуля аккаунт пользователя
	Рекомендуется скопировать этот файл в templates/[шаблон админки]/Classes/[имя этого файла] и там уже начинать править.
	В случае если такой файл уже существует - правьте его.
*/

class TPLAdminAccount
{
	/*
		Шаблон отображения списка пользователей, ожидающих модерации
		$items - массив пользователей страниц. Формат: ID=>array(), ключи внутреннего массива:
			full_name -
			name - имя пользователя (небезопасный HTML!)
			email - e-mail пользователя
			ip - IP адрес пользователя
			_aact - ссылка на активацию пользователя
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя
			_adelr - ссылка на удаление пользователя с указание причины
		$cnt - количество пользователя, ожидающих модерации страниц всего
		$pp - количество пользователей, ожидающих модерации на страницу
		$page - номер текущей страницы, на которой мы сейчас находимся
		$qs - массив параметров адресной строки для каждого запроса
		$links - перечень необходимых ссылок, массив с ключами:
			sort_name - ссылка на сортировку списка $items по имени пользователя (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_email - ссылка на сортировку списка $items по email (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_ip - ссылка на сортировку списка $items по ip адресу (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внтури которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function InactiveUsers($items,$sletters,$cnt,$pp,$page,$qs,$links)
	{

	}

	/*
		Шаблон страницы удаления пользователей с указанием причины
		$users - массив пользователей id=>имя пользователя
		$back - URI возврата
	*/
	public static function ToDelete($users,$back)
	{

	}

	/*
		Шаблон страницы с редактированием форматов писем
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
	*/
	public static function Letters($controls,$values)
	{

	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{

	}
}