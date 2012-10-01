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
	public static function PreDisplay($t,$p,$c,$cu)
	{
		if(strpos($p,'noparse')===false)
			return$c;
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		return'['.$t.']'.$c.'[/'.$t.']';
	}

	public static function PreSave($t,$p,$c,$cu)
	{		$Ed=new Editor_Result;
		$c=$Ed->SafeHtml($c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}