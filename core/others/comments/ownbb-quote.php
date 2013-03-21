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

class CommentsQoute extends OwnBbCode
{
	public static
		$findlink;#Callback функция генерации ссылки на цитируемый комментарий

	/**
	 * Обработка информации перед показом на странице
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p) : array();
		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}
		if(!$cu)
			return self::RestrictDisplay($t,$p,$c);
		$id=isset($p['c']) ? (int)$p['c'] : false;

		$fl=static::$findlink;
		return Eleanor::$Template->CommentsQuote(array(
			'date'=>isset($p['date']) ? Eleanor::$Language->Date($p['date'],'fdt') : false,
			'name'=>isset($p['name']) ? $p['name'] : false,
			'id'=>$id,
			'find'=>$id ? $fl($id) : false,
			'text'=>$c,
		));
	}

	/**
	 * Обработка информации перед её сохранением
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreSave($t,$p,$c,$cu)
	{
		$c=preg_replace("#^(\r?\n?<br />\r?\n?)+#i",'',$c);
		$c=preg_replace("#(\r?\n?<br />\r?\n?)+$#i",'',$c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}