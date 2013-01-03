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

class OwnBbCode_csel extends OwnBbCode
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
		$p=$p ? Strings::ParseParams($p) : array();
		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		if(isset($p['color']) and preg_match('%^[#a-z0-9\-]+$%i',$p['color'])==0)
			unset($p['color']);
		if(isset($p['background']) and preg_match('%^[#a-z0-9\-]+$%i',$p['background'])==0)
			unset($p['background']);
		$p+=array(
			'color'=>'red',
			'background'=>'lightgray',
		);
		return'<span style="color:'.$p['color'].';background-color:'.$p['background'].'">'.$c.'</span>';
	}
}