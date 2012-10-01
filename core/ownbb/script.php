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

class OwnBbCode_script extends OwnBbCode
{
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
		if(isset($p['src']))
			return '<script type="text/javascript" src="'.$p['src'].'"></script>';
		return '<script type="text/javascript">/*<![CDATA[*/'.$c.'//]]></script>';
	}

	public static function PreSave($t,$p,$c,$cu)
	{
		return parent::PreSave($t,isset($p['src']) ? array('src'=>$p['src']) : array(),$c,$cu);
	}
}