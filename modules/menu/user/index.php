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
global$Eleanor,$title;
$Eleanor->module['config']=$mc=include$Eleanor->module['path'].'config.php';
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'user-*.php',$mc['n']);
Eleanor::$Template->queue[]=$mc['usertpl'];

$menu=Eleanor::$Cache->Get('menu_map_'.Language::$main);
if($menu===false)
{
	$maxlen=0;
	$menu=$to1sort=$to2sort=$db=$excl=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`title`,`url`,`eval_url`,`params`,`parents`,`pos`,`in_map`,`status` FROM `'.P.'menu` LEFT JOIN `'.P.'menu_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') ORDER BY `parents` ASC, `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		foreach($excl as $v)
			if(strpos($a['parents'],$v)===0)
				continue;
		if(!$a['in_map'] or !$a['status'])
		{
			$excl[]=$a['parents'].$a['id'].',';
			continue;
		}

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
			$a['url']=$f($Eleanor);
		}

		if($a['parents'])
		{
			$cnt=substr_count($a['parents'],',');
			$to1sort[$a['id']]=$cnt;
			$maxlen=max($cnt,$maxlen);
		}
		$db[$a['id']]=array_slice($a,1);
		$to2sort[$a['id']]=$a['pos'];
	}
	asort($to1sort,SORT_NUMERIC);

	foreach($to1sort as $k=>&$v)
		if($db[$k]['parents'] and preg_match('#(\d+),$#',$db[$k]['parents'],$p)>0)
			if(isset($to2sort[$p[1]]))
				$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
			else
				unset($to1sort[$k],$db[$k],$to2sort[$k]);

	foreach($to2sort as $k=>&$v)
		$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

	natsort($to2sort);
	foreach($to2sort as $k=>&$v)
		$menu[$k]=$db[$k];

	Eleanor::$Cache->Put('menu_map_'.Language::$main,$menu);
}

if(!class_exists('ApiMenu',false))
	include Eleanor::$root.'modules/menu/api.php';
$title[]=$lang['menu'];
$c=Eleanor::$Template->GeneralMenu($menu);
Start();
echo$c;