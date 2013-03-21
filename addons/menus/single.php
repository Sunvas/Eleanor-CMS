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
if(!defined('CMS'))die;
$parent=isset($parent) ? (int)$parent : 0;
$menu=Eleanor::$Cache->Get('menu_single'.Language::$main.$parent);
if($menu===false)
{
	$p='';
	$menu=array();
	if($parent)
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'menu` WHERE `id`='.$parent.' LIMIT 1');
		if(!list($p)=$R->fetch_row())
			return'';
		$p.=$parent.',';
	}
	$R=Eleanor::$Db->Query('SELECT `title`,`url`,`eval_url`,`params` FROM `'.P.'menu` INNER JOIN `'.P.'menu_l` USING(`id`) WHERE `language` IN(\'\',\''.Language::$main.'\') AND `in_map`=1 AND `status`=1 AND `parents`=\''.$p.'\' ORDER BY `parents` ASC, `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		if($a['eval_url'])
		{
			ob_start();
			$f=create_function('$Eleanor',$a['eval_url']);
			if($f===false)
			{
				ob_end_clean();
				continue;
			}
			ob_end_clean();
			$url=$f($GLOBALS['Eleanor']);
		}
		else
			$url=$a['url'];
		$menu[]='<a href="'.$url.'"'.$a['params'].'>'.$a['title'].'</a>';
	}
	Eleanor::$Cache->Put('menu_single'.Language::$main.$parent,$menu);
}
return$menu;