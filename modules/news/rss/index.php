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
global$Eleanor;
$Eleanor->module['config']=include($Eleanor->module['path'].'config.php');
Eleanor::LoadOptions($Eleanor->module['config']['opts']);
$Eleanor->Categories->Init($Eleanor->module['config']['c']);

$items=array();
$lastmod=0;

if(isset($_GET['nid']))
{	$R=Eleanor::$Db->Query('SELECT `id`,`announcement`,`cats`,UNIX_TIMESTAMP(IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`)) `date`,`show_sokr`,`show_detail`,`uri`,`title`,`text`,`last_mod` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `status`=1 AND `id`='.(int)$_GET['nid'].' LIMIT 1');
	if($a=$R->fetch_assoc() and ($a['text'] or $a['show_detail']))
	{		$a['text']=($a['show_sokr'] ? OwnBB::Parse($a['announcement']) : '').OwnBB::Parse($a['text']);
		$lastmod=strtotime($a['last_mod']);
		$items[$a['id']]=array_slice($a,2);
	}}
else
{	$page=empty($_GET['page']) ? 1 : (int)$_GET['page'];
	if($page<=0)
		$page=1;

	if(!empty($_GET['pp']))
	{
		$pp=(int)$_GET['pp'];
		if($pp>Eleanor::$vars['publ_rss_per_page'])
			$pp=Eleanor::$vars['publ_rss_per_page'];
		elseif($pp<1)
			$pp=1;
	}
	else
		$pp=Eleanor::$vars['publ_rss_per_page'];

	$where=$lwhere='';
	if(isset($_GET['c'],$Eleanor->Categories->dump[$c=(int)$_GET['c']]))
	{
		if(Eleanor::$vars['publ_catsubcat'])
		{			$cs=array($c);
			$p=$Eleanor->Categories->dump[$c]['parents'].$c.',';
			foreach($Eleanor->Categories->dump as $k=>&$v)
				if(strpos($v['parents'],$p)===0)
					$cs[]=$k;
			sort($cs,SORT_NUMERIC);
			$c=count($cs)>1 ? 'REGEXP(\',('.join('|',$cs).'),\')' : 'LIKE(\'%,'.$c.',%\')';
		}
		else
			$c='LIKE(\'%,'.$c.',%\')';
		$where.=' AND `cats` '.$c;
		$lwhere.=' AND `lcats` '.$c;
	}

	$R=Eleanor::$Db->Query('SELECT COUNT(`status`) `cnt` FROM `'.$Eleanor->module['config']['t'].'` WHERE `status`=1'.$where);
	list($cnt)=$R->fetch_row();

	$offset=abs(($page-1)*$pp);
	if($cnt and $offset>=$cnt)
		$offset=max(0,$cnt-$pp);

	$R=Eleanor::$Db->Query('SELECT `id`,`announcement`,`cats`,UNIX_TIMESTAMP(IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`)) `date`,`show_detail`,`uri`,`title`,`text`,`last_mod` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `lstatus`=1 AND `ldate`<=\''.date('Y-m-d H:i:s').'\''.$lwhere.' ORDER BY `ldate` DESC LIMIT '.$offset.', '.$pp);
	while($a=$R->fetch_assoc())
	{		$lastmod=max($lastmod,strtotime($a['last_mod']));
		if($a['text'] or $a['show_detail'])
			$a['text']=OwnBB::Parse($a['announcement']);
		else
			$a['uri']=false;
		$items[$a['id']]=array_slice($a,2);
	}
}

if(Eleanor::$caching)
{
	Eleanor::$last_mod=$lastmod;
	$etag=Eleanor::$etag;
	Eleanor::$etag=md5($Eleanor->module['config']['n'].join(',',array_keys($items)));
	if(Eleanor::$modified and Eleanor::$last_mod and Eleanor::$last_mod<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
		return Start();
	else
		Eleanor::$modified=false;
}

BeAs('user');
Start(array(
	'lastBuildDate'=>$lastmod,
));

foreach($items as $k=>&$v)
{	$cu=$cats=array();
	if($v['uri']===false)
		$u=false;
	else
	{		if($v['cats'])
		{			$cid=explode(',,',trim($v['cats'],','));
			foreach($cid as &$cv)
				if(isset($Eleanor->Categories->dump[$cv]))
					$cats[]=$Eleanor->Categories->dump[$cv]['title'];
			$cid=reset($cid);
			if($Eleanor->Url->furl and isset($Eleanor->Categories->dump[$cid]))
				$cu=$Eleanor->Categories->GetUri($cid);
		}
		$u=$Eleanor->Url->Construct($cu+array('u'=>array($v['uri'],'nid'=>$k)));
	}
	echo Rss(array(
		'title'=>$v['title'],#Заголовок сообщения
		'link'=>$u,#URL сообщения
		'description'=>$v['text'],#Краткий обзор сообщения
		'guid'=>$u,#Строка, уникальным образом идентифицирующая сообщение.
		'category'=>$cats,#Включает сообщение в одну или более категорий.
		'comments'=>$u ? $u.'#comments' : false,
		'pubDate'=>(int)$v['date'],
	));
}