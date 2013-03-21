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
$Eleanor->module['config']=include($Eleanor->module['path'].'config.php');

switch($Eleanor->module['section'])
{
	case'groups':
		return include dirname(__file__).'/groups.php';
	case'online':
		return include dirname(__file__).'/online.php';
}
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'user-*.php',$Eleanor->module['config']['n']);
Eleanor::$Template->queue[]=$Eleanor->module['config']['usertpl'];

if($Eleanor->Url->is_static)
{
	$str=$Eleanor->Url->GetEnding(array($Eleanor->Url->ending,$Eleanor->Url->delimiter),true);
	$user=$Eleanor->module['section']=='user' ? $Eleanor->Url->ParseToValue('user',true,false) : false;
	$do=$Eleanor->Url->ParseToValue('do',true);
}
else
{
	$user=isset($_GET['user']) ? $_GET['user'] : 0;
	$do=isset($_GET['do']) ? (string)$_GET['do'] : false;
}

$id=isset($_GET['userid']) ? (int)$_GET['userid'] : 0;
if($do)
	$do=preg_replace('#[^a-z0-9\-_]+#','',$do);

function GetHandlers($type)
{
	$h=array();
	if($files=glob(__dir__.DIRECTORY_SEPARATOR.$type.'/*.php',GLOB_MARK))
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

if($user or $id)
{
	$handlers=GetHandlers('view');
	if(Eleanor::$Db===Eleanor::$UsersDb)
	{
		if($id)
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`forum_id`,`groups`,`u`.`register`,`u`.`last_visit`,`u`.`language`,`u`.`timezone`,`e`.* FROM `'.USERS_TABLE.'` `u` INNER JOIN `'.P.'users_extra` `e` USING (`id`) INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE `id`='.$id.' LIMIT 1');
		else
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`forum_id`,`groups`,`u`.`register`,`u`.`last_visit`,`u`.`language`,`u`.`timezone`,`e`.* FROM `'.USERS_TABLE.'` `u` INNER JOIN `'.P.'users_extra` `e` USING (`id`) INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE `u`.`name`='.Eleanor::$Db->Escape($user).' LIMIT 1');
	}
	else
	{
		if($id)
			$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`language`,`timezone` FROM `'.USERS_TABLE.'` WHERE `id`='.$id.' LIMIT 1');
		else
			$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`language`,`timezone` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($user).' LIMIT 1',array($user));
		if(!$a=$R->fetch_assoc())
			return ExitPage();
		UserManager::Sync(array($a['id']=>array_slice($a,1)));
		$R=Eleanor::$Db->Query('SELECT `id`,`forum_id`,`groups`,`full_name`,`name`,`register`,`last_visit`,`language`,`timezone`,`e`.* FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` `e` USING (`id`) WHERE `id`='.$a['id'].' LIMIT 1');
	}

	if(!$Eleanor->module['user']=$R->fetch_assoc())
		return ExitPage();
}
elseif(Eleanor::$Login->IsUser())
	$handlers=GetHandlers('user');
else
	$handlers=GetHandlers('guest');

$Eleanor->module['handlers']=$handlers;

if($do)
	$c=isset($handlers[$do]) && method_exists($handlers[$do],'Content') ? $handlers[$do]::Content(true) : null;
elseif(class_exists('AccountIndex',false))
	$c=AccountIndex::Content(true);

if(!isset($c))
	return ExitPage();

Start();
echo$c;