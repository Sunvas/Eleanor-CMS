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
$res=Eleanor::$Cache->Get('mainpage');
if($res===false)
{	$res=array();
	$modules=Modules::GetCache();
	$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files` FROM `'.P.'modules` INNER JOIN `'.P.'mainpage` USING(`id`) WHERE `active`=1 ORDER BY `pos` ASC');
	while($a=$R->fetch_assoc())
	{		if(!in_array($a['id'],$modules['ids']))
			continue;		if(!$a['multiservice'])
		{
			$files=unserialize($a['files']);
			$a['file']=isset($files[Eleanor::$service]) ? $files[Eleanor::$service] : false;
		}
		if(!$a['file'])
			continue;
		$a['title_l']=(array)unserialize($a['title_l']);
		$a['sections']=(array)unserialize($a['sections']);
		$res[$a['id']]=array_slice($a,1);
	}
	Eleanor::$Cache->Put('mainpage',$res);
}
global$Eleanor,$title;
$Eleanor->started=true;
ob_start();
foreach($res as $k=>&$v)
{	foreach($v['sections'] as $sk=>&$sv)
		if(Eleanor::$vars['multilang'] and isset($sv[Language::$main]))
			$sv=reset($sv[Language::$main]);
		else
			$sv=isset($sv[LANGUAGE]) ? reset($sv[LANGUAGE]) : reset($sv['']);
	$v['title_l']=Eleanor::FilterLangValues($v['title_l']);
	$Eleanor->module=array(
		'general'=>true,
		'name'=>reset($v['sections']),
		'title'=>$v['title_l'],
		'path'=>Eleanor::FormatPath($v['path']).DIRECTORY_SEPARATOR,
		'id'=>$k,
		'sections'=>$v['sections'],
		'section'=>key($v['sections']),
	);
	$nop=Eleanor::$vars['prefix_free_module']==$k;
	$Eleanor->Url->SetPrefix(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$nop ? false : $Eleanor->module['name']) : array('module'=>$nop ? false : $Eleanor->module['name']));
	Modules::Load($Eleanor->module['path'],$v['multiservice'],$v['file'] ? $v['file'] : 'index');
}
$c=ob_get_contents();
ob_end_clean();
$Eleanor->started=false;
$title=false;
unset($Eleanor->module['description']);
Start();
echo$c;