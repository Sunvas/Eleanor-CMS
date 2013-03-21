<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон по умолчанию для пользователей модуля "обратная связь"
	Рекомендуется скопировать этот файл в templates/[шаблон пользовательской части]/Classes/[имя этого файла] и там уже начинать править.
	В случае если такой файл уже существует - правьте его.
*/
class TplUserContacts
{
	/*
		Основная страница обратной связи

		$canupload - флаг возможности загрузки файла
		$info - информация по обратной связи, заполняемая в админке
		$whom - массив выбора получателя письма. Формат id=>имя получателя
		$values - массив значений формы, ключи:
			subject - тема сообщения
			message - текст сообщения
			whom - идентификатор получателя
			sess - идентификатор сессии
		$bypost - флаг загрузки содержимого из POST запроса
		$errors - массив ошибок
		$isu - флаг пользователя (не гостя)
		$captcha - captcha при отправке сообщения
	*/
	public static function Contacts($canupload,$info,$whom,$values,$bypost,$errors,$isu,$captcha)
	{

	}

	/*
		Страница с информацией о том, что сообщение успешно отправлено
	*/
	public static function Sent()
	{

	}
}