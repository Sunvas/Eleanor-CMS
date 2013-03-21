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

class OwnBbCode_nobb extends OwnBbCode
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
		if(strpos($p,'noparse')===false)
			return$c;
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		return'['.$t.']'.$c.'[/'.$t.']';
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
		$Ed=new Editor_Result;
		$c=$Ed->SafeHtml($c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}