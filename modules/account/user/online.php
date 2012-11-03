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
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_user-*.php',$Eleanor->module['config']['n']);
Eleanor::$Template->queue[]=$Eleanor->module['config']['usertpl'];

$title[]=$lang['who_online'];
if($Eleanor->Url->is_static)
	$_GET+=$Eleanor->Url->Parse();
$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;

$where='WHERE `s`.`service`=\''.Eleanor::$service.'\' AND `s`.`expire`>\''.date('Y-m-d H:i:s').'\'';
$R=Eleanor::$Db->Query('SELECT COUNT(`expire`) FROM `'.P.'sessions` `s`'.$where);
list($cnt)=$R->fetch_row();
if($page<=0)
	$page=1;
$pp=30;

$offset=abs(($page-1)*$pp);
if($cnt and $offset>=$cnt)
	$offset=max(0,$cnt-$pp);

$groups=$items=array();
$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`user_id`,`s`.`enter`,`s`.`ip_guest`,`s`.`ip_user`,`s`.`browser`,`s`.`location`,`s`.`name` `botname`,`us`.`groups`,`us`.`name`,`us`.`full_name` FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id` '.$where.' ORDER BY `s`.`expire` DESC LIMIT '.$offset.','.$pp);
while($a=$R->fetch_assoc())
{
	if($a['type']=='user' and $a['name'])
	{
		$a['_group']=(int)ltrim($a['groups'],',');
		$groups[]=$a['_group'];
	}
	$items[]=$a;
}

if($groups)
{
	$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
	$groups=array();
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
		$groups[$a['id']]=array_slice($a,1)+array('_href'=>$Eleanor->Url->Construct(array('edit'=>$a['id'])));
	}
}
$links=array(
	'first_page'=>$Eleanor->Url->Prefix(),
	'pages'=>function($n){ return$GLOBALS['Eleanor']->Url->Construct(array(array('page'=>$n))); },
);
$c=Eleanor::$Template->AcUsersOnline($items,$groups,$cnt,$pp,$page,$links);
Start();
echo$c;