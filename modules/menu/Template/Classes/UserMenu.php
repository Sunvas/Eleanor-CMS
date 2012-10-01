<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Ўаблон по умолчанию дл€ пользователей модул€ меню.
	–екомендуетс€ скопировать этот файл в templates/[шаблон пользовательской части]/Classes/[им€ этого файла] и там уже начинать править.
	¬ случае если такой файл уже существует - правьте его.
*/
class TplUserMenu
{
	/*
		—траница отображени€ меню сайта
		$a - массив меню сайта, формат id=>array(), ключи внутреннего массива:
			url - ссылка
			title - название пункта меню
			params - параметры ссылки
			parents - идентификаторы всех родителей меню, разделенных зап€тыми (если они, конечно, есть)
			pos - число по которому отсортировано меню в пределах одного родител€ (от меньшего к большему начина€ с 1)
	*/
	public static function GeneralMenu($a)
	{

	}
}