<?php
return array(
	'callback'=>function($co)
	{
		$opts=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'modules` WHERE `services` LIKE \'%,user,%\' AND `id`!=3 AND `active`=1');
		while($a=$R->fetch_assoc())
			$opts[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
		return$opts;
	},
);