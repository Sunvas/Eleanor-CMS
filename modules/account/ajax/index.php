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
global$Eleanor;
BeAs('user');
$Eleanor->module['config']=include($Eleanor->module['path'].'config.php');
Eleanor::$Language->Load($Eleanor->module['path'].'lang_ajax-*.php',$Eleanor->module['config']['n']);
Eleanor::$Template->queue[]=$Eleanor->module['config']['usertpl'];

function GetHandlers($type)
{
	$h=array();
	if($files=glob(dirname(__file__).DIRECTORY_SEPARATOR.$type.'/*.php',GLOB_MARK))
		foreach($files as &$f)
			if(substr($f,-1)!=DIRECTORY_SEPARATOR)
			{
				$fn=substr(basename($f),0,-4);
				$c='Account'.$fn;
				if(!class_exists($c,false))
					include$f;
				if(!class_exists($c,false))
					continue;
				$h[$fn]=$c;
			}
	return$h;
}

$userid=isset($_POST['userid']) ? (int)$_POST['userid'] : 0;
$do=isset($_POST['do']) ? preg_replace('#[^a-z0-9\-_]+#','',(string)$_POST['do']) : false;

if($userid or $Eleanor->module['section']=='user')
{
	$handlers=GetHandlers('view');

	if(Eleanor::$Db===Eleanor::$UsersDb)
		$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`groups`,`u`.`register`,`u`.`last_visit`,`u`.`language`,`e`.* FROM `'.USERS_TABLE.'` `u` INNER JOIN `'.P.'users_extra` `e` USING (`id`) INNER JOIN `'.P.'users_site` USING(`id`) WHERE `id`='.$userid.' LIMIT 1');
	else
	{
		$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`language` FROM `'.USERS_TABLE.'` WHERE `id`='.$userid.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return Error();
		UserManager::Sync(array($a['id']=>array_slice($a,1)));
		$R=Eleanor::$Db->Query('SELECT `id`,`full_name`,`name`,`groups`,`register`,`last_visit`,`language`,`e`.* FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` `e` USING (`id`) WHERE `id`='.$userid.' LIMIT 1');
	}

	if(!$Eleanor->module['user']=$R->fetch_assoc())
		return Error();
}
elseif(Eleanor::$Login->IsUser())
	$handlers=GetHandlers('user');
else
	$handlers=GetHandlers('guest');

$Eleanor->module['handlers']=$handlers;

if($do)
	if(isset($handlers[$do]) and method_exists($handlers[$do],'Handler'))
		$handlers[$do]::Handler();
	else
		return Error();
elseif(class_exists('AccountIndex',false))
	AccountIndex::Handler();
else
	return Error();