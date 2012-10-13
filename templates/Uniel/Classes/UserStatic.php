<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для пользователей модуля статических страниц.
*/
class TplUserStatic
{
	/*
		Страница отображения статической страницы
		$id - числовой идентификатор статической страницы для страницы из базы данных, строка - для файловых страниц
		$data - данные статической страницы
			title - название статической страницы
			text - текст статической страницы
			navi - хлебные крошки навигации к статической странице. Каждый элемент - массив с ключами:
				0 - текст крошки
				1 - (опциально) ссылка крошки
			seealso - ссылки, полезные для просмотра (смотри еще). Каждый элемент - массив с ключами:
				0 - текст ссылки
				1 - ссылка
	*/
	public static function StaticShow($id,$data)
	{
		$see=$navi='';
		if($data['navi'])
		{
			foreach($data['navi'] as &$v)
				$v=$v[1] ? '<a href="'.$v[1].'">'.$v[0].'</a>' : $v[0];
			$navi.=join(' &raquo; ',$data['navi']).'<hr />';
		}
		if($data['seealso'])
		{
			foreach($data['seealso'] as &$v)
				$v='<a href="'.$v[1].'">'.$v[0].'</a>';
			$see='<hr /><b>'.Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['seealso'].'</b><br /><ul><li>'.join('</li><li>',$data['seealso']).'</li></ul>';
		}
		return Eleanor::$Template->OpenTable()
			.$navi
			.$data['text']
			.$see
			.Eleanor::$Template->CloseTable();
	}

	/*
		Вывод статических страниц на главной (в случае, если модуль статических страниц отображается на главной странице)
		$a - массив статических страниц для вывода на главной. Каждый элемент - массив с ключами:
			title - название статической страницы
			text - текст статической страницы
	*/
	public static function StaticGeneral($a)
	{
		$c='';
		foreach($a as &$v)
			$c.='<h1 style="text-align:center">'.$v['title'].'</h1><br />'.$v['text'].'<br /><br />';
		return$c;
	}

	/*
		Вывод содержания статических страниц (перечень всех страниц).
		$a - массив всех статических страниц, хранимых в базе. Формат: id=>array(), ключи внутреннего массива:
			uri - строка-идентификатор статической страницы
			title - название статической страницы
			parents - идентификаторы всех родителей статической страницы, разделенных запятыми (если они, конечно, есть)
			pos - число по которому отсортированы статические страницы в пределах одного родителя (от меньшего к большему начиная с 1)
	*/
	public static function StaticSubstance($a)
	{
		return Eleanor::$Template->Title(Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['substance'])
		.($a ? Eleanor::$Template->OpenTable().self::SubstanceItems($a).Eleanor::$Template->CloseTable() : '');
	}

	protected static function SubstanceItems($a)
	{
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$c='<ul>';#Content
		$n=-1;
		$nonp=true;#No new page
		foreach($a as $k=>&$v)
		{
			++$n;
			$nl=strlen($v['parents']);
			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif($nonp)
				{
					$c.=self::SubstanceItems(array_slice($a,$n));
					$nonp=false;
				}
				continue;
			}
			if($n>0)
				$c.='</li>';
			$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Plug->GetUrl($k)).'">'.$v['title'].'</a>';
			$nonp=true;
		}
		return$c.'</li></ul>';
	}
}