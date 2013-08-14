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

class OwnBbCode_hide extends OwnBbCode
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
		$p=$p ? Strings::ParseParams($p,'g') : array();
		if(isset($p['g']) ? array_intersect(explode(',',$p['g']),Eleanor::GetUserGroups()) : Eleanor::$Login->IsUser())
			return$c;
		$l=Eleanor::$Language['ownbb'];
		try
		{
			return isset(Eleanor::$Template) ? Eleanor::$Template->HiddenText($l['hidden']) : '['.$l['hidden'].']';
		}
		catch(EE$E)
		{
			return'['.$l['hidden'].']';
		}
	}
}