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
#Настройки
$mname=array_keys($GLOBALS['Eleanor']->modules['sections'],'news');
$mname=reset($mname);
#$mname=array('russian'=>'новости','ukrainian'=>'новини','english'=>'news',''=>'news');#URL модуля. Может быть строкой

$limit=10;#Количество месяцев за которые брать архив по месяцам
#Конец настроек

if(is_array($mname))
	$mname=Eleanor::FilterLangValues($mname,Language::$main);
$conf=include __dir__.'/config.php';

#Months
$months=Eleanor::$Cache->Get($conf['n'].'_archivemonths');
if($months===false)
{
	$months=array();
	$R=Eleanor::$Db->Query('SELECT EXTRACT(YEAR_MONTH FROM IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`)) `ym`, COUNT(`id`) `cnt` FROM `'.$conf['t'].'` WHERE `status`=1 GROUP BY `ym` ORDER BY `ym` DESC LIMIT '.$limit);
	while($a=$R->fetch_assoc())
	{
		$a['ym']=substr_replace($a['ym'],'-',4,0);
		$months[$a['ym']]=array(
			'cnt'=>$a['cnt'],
			'a'=>$GLOBALS['Eleanor']->Url->Construct(array('module'=>$mname,'do'=>$a['ym']),false,''),
		);
	}
	Eleanor::$Cache->Put($conf['n'].'_archivemonths',$months,3600);
}

#Days
if($cdate=Eleanor::GetCookie($conf['n'].'-archive') and preg_match('#^(\d{4})\D(\d{1,2})$#',$cdate,$ma)>0)
{
	list(,$y,$m)=$ma;
	$y=(int)$y;
	$m=(int)$m;
}
else
{
	$y=idate('Y');
	$m=idate('n');
}

include_once __dir__.'/block_archive_funcs.php';
$days=ArchiveDays($y,$m,$conf,$mname);

try
{
	return Eleanor::$Template->BlockArchive($days,$mname,false,$months);
}
catch(EE$E)
{
	return'BlockArchive is missed';
}