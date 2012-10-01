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
{	public static
		$findlink;#Îáúåêò UrlFunc
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
			'find'=>$id && is_object($fl) ? $fl($id) : false,
			'text'=>$c,
		));
	}

	public static function PreSave($t,$p,$c,$cu)
	{		$c=preg_replace("#^(\r?\n?<br />\r?\n?)+#i",'',$c);
		$c=preg_replace("#(\r?\n?<br />\r?\n?)+$#i",'',$c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}