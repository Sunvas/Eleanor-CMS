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

class OwnBbCode_quote extends OwnBbCode
{
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
		if(strpos($p,'noparse')!==false)
			return'['.$t.']'.$c.'[/'.$t.']';
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		return Eleanor::$Template->Quote($c);
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
		$c=preg_replace("#^(\r?\n?<br />\r?\n?)+#i",'<br />',$c);
		$c=preg_replace("#(\r?\n?<br />\r?\n?)+$#i",'<br />',$c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}