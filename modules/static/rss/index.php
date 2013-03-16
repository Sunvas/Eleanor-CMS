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
$pp_=50;#Количество статических страниц на страницу по умолчанию :)
$mc=include$Eleanor->module['path'].'config.php';
if(!class_exists($mc['api'],false))
	include$Eleanor->module['path'].'api.php';
$Plug=new$mc['api']($mc);

if(!empty($_GET['pp']))
{
	$pp=(int)$_GET['pp'];
	if($pp>$pp_)
		$pp=$pp_;
	elseif($pp<1)
		$pp=1;
}
else
	$pp=$pp_;

$page=empty($_GET['page']) ? 1 : (int)$_GET['page'];
if($page<=0)
	$page=1;
$offset=$pp*($page-1);

$items=$Plug->GetOrderedList();
$items=array_slice($items,$offset,$pp);
$ids=$parents=array();

foreach($items as $k=>&$v)
{
	$ids[]=$k;
	if($v['parents'])
		$parents=array_merge($parents,explode(',',rtrim($v['parents'],',')));
}
if($parents)
{
	$parents=array_unique($parents);
	$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id`'.Eleanor::$Db->In($parents).' AND `status`=1');
	$parents=array();
	while($a=$R->fetch_assoc())
		$parents[$a['id']]=$a['title'];
}

$lastmod=0;
$hasroot=false;
if($ids)
{
	$R=Eleanor::$Db->Query('SELECT `id`,`parents`,`title`,`text`,`last_mod` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id`'.Eleanor::$Db->In($ids).' AND `status`=1');
	$items=array();
	while($a=$R->fetch_assoc())
	{
		if($a['parents'])
			$hasroot=true;
		$lastmod=max($lastmod,strtotime($a['last_mod']));
		$a['text']=OwnBB::Parse($a['text']);
		$items[$a['id']]=array_slice($a,1);
	}
}

if(Eleanor::$caching)
{
	Eleanor::$last_mod=$lastmod;
	$etag=Eleanor::$etag;
	Eleanor::$etag=md5($mc['n']);
	if(Eleanor::$modified and Eleanor::$last_mod and Eleanor::$last_mod<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
		return Start();
	else
		Eleanor::$modified=false;
}

BeAs('user');
$Eleanor->Url->SetPrefix(array('module'=>$Eleanor->module['name']));

Start(array(
	'lastBuildDate'=>$lastmod,
));
foreach($ids as &$v)
	if(isset($items[$v]))
	{
		if($items[$v]['parents'])
		{
			$c=array();
			foreach(explode(',',rtrim($items[$v]['parents'],',')) as $p)
				if(isset($parents[$p]))
					$c[]=$parents[$p];
			$c=join('/',$c);
		}
		else
			$c=$hasroot ? '&#47;' : array();
		$u=$Eleanor->Url->Construct($Eleanor->Url->furl ? $Plug->GetUri($v) : array('id'=>$v));
		echo Rss(array(
			'title'=>$items[$v]['title'],#Заголовок сообщения
			'link'=>$u,#URL сообщения
			'description'=>$items[$v]['text'],#Краткий обзор сообщения
			'guid'=>$u,#Строка, уникальным образом идентифицирующая сообщение.
			'category'=>$c,#Включает сообщение в одну или более категорий. См. ниже.
		));
	}