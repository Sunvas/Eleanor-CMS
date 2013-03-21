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
require Eleanor::$root.'addons/admin/info.php';
if(!$m=isset($_GET['module']) ? (string)$_GET['module'] : false)
	$m=isset($_POST['module']) ? (string)$_POST['module'] : false;
$error='';
if($m and isset($info[$m]))
{
	$Eleanor->module=array(
		'name'=>$m,
		'title'=>$info[$m]['title'],
		'descr'=>$info[$m]['descr'],
		'image'=>$info[$m]['image'] ? $info[$m]['image'] : 'default-*.png',
		'path'=>Eleanor::$root.(isset($info[$m]['path']) ? $info[$m]['path'] : 'addons/admin/modules/'),
		'file'=>isset($info[$m]['services'][Eleanor::$service]) ? $info[$m]['services'][Eleanor::$service] : false,
	)+$Eleanor->module;
	$Eleanor->Url->SetPrefix(array('module'=>$m),true);
	$title[]=$info[$m]['title'];
	return Modules::Load($Eleanor->module['path'],false,$Eleanor->module['file']);
}

Eleanor::$Template->queue[]='Management';
$general=$extra=$titles=array();
foreach($info as $name=>&$t)
	$titles[$name]=$t['title'];
asort($titles,SORT_STRING);
foreach($titles as $name=>&$q)
{
	$a=$info[$name];
	if(!isset($a['services'][Eleanor::$service]) or !empty($a['hidden']))
		continue;
	$a['_a']=$Eleanor->Url->Construct(array('module'=>$name));
	if(empty($a['main']))
		$extra[]=$a;
	else
		$general[]=$a;
}
$c=Eleanor::$Template->ManagCover($general,$extra,$error);
Start();
echo$c;