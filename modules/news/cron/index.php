<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
if(!defined('CMS'))die;
global$Eleanor;
$conf=include($Eleanor->module['path'].'config.php');
if(Eleanor::$Cache->Get($conf['n'].'-runned')===false)
{	Eleanor::$Cache->Put($conf['n'].'-runned',true,100);
	$n=150;
	$ids=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`date`,`pinned`,`tags` FROM `'.$conf['t'].'` WHERE `date`<=\''.date('Y-m-d H:i:s').'\' AND `status`=2 LIMIT '.$n);
	while($a=$R->fetch_assoc())
	{		$n--;		$upd=array(
			'status'=>1,
		);
		if((int)$a['pinned'])
			$upd+=array(
				'date'=>$a['pinned'],
				'pinned'=>$a['date'],
			);
		$ids[]=$a['id'];
		Eleanor::$Db->Update($conf['t'],$upd,'`id`='.$a['id'].' LIMIT 1');

		if($a['tags'])
		{			$a['tags']=explode(',,',trim($a['tags'],','));
			Eleanor::$Db->Insert($conf['rt'],array('id'=>array_fill(0,count($a['tags']),$a['id']),'tag'=>$a['tags']));
			Eleanor::$Db->Update($conf['tt'],array('!cnt'=>'`cnt`+1'),'`id`'.Eleanor::$Db->In($a['tags']));		}
	}
	if($ids)
		Eleanor::$Db->Update($conf['tl'],array('lstatus'=>1),'`id`'.Eleanor::$Db->In($ids));

	if($n>0)
	{
		$ids=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`date`,`pinned` FROM `'.$conf['t'].'` WHERE `enddate`BETWEEN \'0000-00-00 00:00:01\' AND \''.date('Y-m-d H:i:s').'\' AND `status`>0 LIMIT '.$n);
		while($a=$R->fetch_assoc())
		{
			$n--;
			$ids[]=$a['id'];
			$upd=array(
				'status'=>0,
			);
			if((int)$a['pinned'])
				$upd+=array(
					'date'=>$a['pinned'],
					'pinned'=>$a['date'],
				);
			Eleanor::$Db->Update($conf['t'],$upd,'`id`='.$a['id'].' LIMIT 1');
		}
		if($ids)
		{			$in=Eleanor::$Db->In($ids);
			Eleanor::$Db->Update($conf['tl'],array('lstatus'=>0),'`id`'.$in);
			$R=Eleanor::$Db->Query('SELECT `tag`,COUNT(`id`) `cnt` FROM `'.$conf['rt'].'` WHERE `id`'.$in.' GROUP BY `tag`');
			while($a=$R->fetch_assoc())
				Eleanor::$Db->Update($conf['tt'],array('!cnt'=>'GREATEST(0,`cnt`-'.$a['cnt'].')'),'`id`='.$a['tag'].' AND `cnt`>0');
			Eleanor::$Db->Delete($conf['rt'],'`id`'.$in);
		}
	}

	if($n>0)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`pinned` FROM `'.$conf['t'].'` WHERE `status`=1 AND `date`<=\''.date('Y-m-d H:i:s').'\' AND `pinned`!=\'0000-00-00 00:00:00\' LIMIT '.$n);
		while($a=$R->fetch_assoc())
			Eleanor::$Db->Update($conf['t'],array('date'=>$a['pinned'],'pinned'=>'0000-00-00 00:00:00'),'`id`='.$a['id'].' LIMIT 1');

		$R=Eleanor::$Db->Query('SELECT UNIX_TIMESTAMP(`date`) FROM `'.$conf['t'].'` WHERE `status`=1 AND `date`>\''.date('Y-m-d H:i:s').'\' AND `pinned`!=\'0000-00-00 00:00:00\' ORDER BY `date` ASC LIMIT 1');
		list($near)=$R->fetch_row();
		$near=(int)$near;
		$totomor=strtotime('+1 DAY',mktime(0,0,0));
		$near=$near>0 ? min($near,$totomor) : $totomor;

		$R=Eleanor::$Db->Query('SELECT UNIX_TIMESTAMP(`date`) FROM `'.$conf['t'].'` WHERE `status`=2 AND `date`>\''.date('Y-m-d H:i:s').'\' ORDER BY `date` ASC LIMIT 1');
		list($near2)=$R->fetch_row();

		Eleanor::$Cache->Put($conf['n'].'_nextrun',true,($near2>0 ? min($near,$near2) : $near2)-time());
	}
	Eleanor::$Cache->Obsolete($conf['n'].'-runned');
}
Start();