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
			return Eleanor::$Template->HiddenText($l['hidden']);
		}
		catch(EE$E)
		{			return'['.$l['hidden'].']';		}
	}
}