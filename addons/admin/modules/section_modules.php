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
$mid=false;
$m=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;
$modules=Modules::GetCache();
$error='';
if($m and isset($modules['ids'][$m]))
{
	$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`title_l`,`descr_l`,`path`,`multiservice`,`file`,`files`,`image` FROM `'.P.'modules` WHERE `id`='.(int)$modules['ids'][$m].' AND `active`=1 LIMIT 1');
	if($a=$R->fetch_assoc())
	{
		if(!$a['multiservice'])
		{
			$files=unserialize($a['files']);
			$a['file']=isset($files[Eleanor::$service]) ? $files[Eleanor::$service] : false;
		}
		$sections=unserialize($a['sections']);
		foreach($sections as $k=>&$v)
			if(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
				$v=reset($v[Language::$main]);
			else
				$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
		$a['title_l']=unserialize($a['title_l']);
		$a['title_l']=Eleanor::FilterLangValues($a['title_l']);

		$a['descr_l']=unserialize($a['descr_l']);
		$a['descr_l']=Eleanor::FilterLangValues($a['descr_l']);

		$Eleanor->module=array(
			'name'=>$m,
			'section'=>isset($modules['sections'][$m]) ? $modules['sections'][$m] : '',
			'title'=>$a['title_l'],
			'descr'=>$a['descr_l'],
			'image'=>$a['image'],
			'path'=>Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR,
			'id'=>$a['id'],
			'sections'=>$sections,
		)+$Eleanor->module;

		$Eleanor->Url->SetPrefix(array('module'=>$m),true);
		$title[]=$a['title_l'];
		return Modules::Load($Eleanor->module['path'],$a['multiservice'],$a['file']);
	}
}

$titles=$preitems=$items=array();
Eleanor::$Template->queue[]='Management';
$R=Eleanor::$Db->Query('SELECT `sections`,`title_l` `title`,`descr_l` `descr`,`image`,`active` FROM `'.P.'modules` WHERE `services`=\'\' OR `services` LIKE \'%,'.Eleanor::$service.',%\'');
while($a=$R->fetch_assoc())
{
	$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
	$a['descr']=$a['descr'] ? Eleanor::FilterLangValues((array)unserialize($a['descr'])) : '';

	$a['sections']=unserialize($a['sections']);
	foreach($a['sections'] as &$v)
		$v=Eleanor::FilterLangValues($v);
	$a['sections']=reset($a['sections']);
	if(is_array($a['sections']))
		$a['_a']=$Eleanor->Url->Construct(array('module'=>reset($a['sections'])));
	else
		$a['active']=false;
	$titles[]=$a['title'];
	$preitems[]=$a;
}
asort($titles,SORT_STRING);
foreach($titles as $k=>&$v)
	$items[]=$preitems[$k];
$c=Eleanor::$Template->ModulesCover($items,$error);
Start();
echo$c;