<?php
return array(
	'callback'=>function()
	{
		$r=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'groups`');
		while($a=$R->fetch_assoc())
			$r[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
		asort($r,SORT_STRING);
		return$r;
	}
);