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

class OwnBbCode_html extends OwnBbCode
{
	public static function PreDisplay($t,$p,$c,$cu)
	{		if(strpos($p,'noparse')!==false)
			return'['.$t.']'.htmlspecialchars($c,ELENT,CHARSET,false).'[/'.$t.']';
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		return$c;
	}
}