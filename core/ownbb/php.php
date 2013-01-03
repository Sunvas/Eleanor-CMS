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

class OwnBbCode_php extends OwnBbCode
{
	public static
		$input;

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
		{
			ob_start();
			$f=create_function('$params,$input',$c);
			if(!$f)
			{
				$r='['.$t.']'.ob_get_contents().'[/'.$t.']';
				ob_end_clean();
				return $r;
			}
			$c=$f($p,self::$input);
			$c.=ob_get_contents();
			ob_end_clean();
			return $c;
		}
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		return'['.$t.']'.$c.'[/'.$t.']';
	}
}