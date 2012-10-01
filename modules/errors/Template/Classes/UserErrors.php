<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон по умолчанию для пользователей системного модуля страниц ошибок
	Рекомендуется скопировать этот файл в templates/[шаблон пользовательской части]/Classes/[имя этого файла] и там уже начинать править.
	В случае если такой файл уже существует - правьте его.
*/
class TPLUserErrors
{	/*
		Вывод страницы ошибки
		$a - параметры ошибки, массив с ключами:
			id - идентификатор ошибки в БД
			http_code - HTTP код ошибки
			image - логотип ошибки
			mail - e-mail, куда необходимо присылать сообщение от пользователей
			log - флаг логирования ошибки
			title - название страницы ошибки
			text - текст с пояснением ошибки
			meta_title - заголовок окна
			meta_descr - meta description
		$info - информация об отправке сообщения, массив с ключами:
			sent - флаг отправленности сообщения
			error - ошибка отправки, если ошибка пустая - значит ее нету
			text - текст сообщения
			back - URI возврата
			name - имя гостя
		$captcha - captcha при отправке письма
	*/
	public static function ShowError($a,$info,$captcha)
	{
	}
}