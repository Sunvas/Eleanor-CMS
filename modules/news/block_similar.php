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
if(empty($Eleanor->module['ptags']) or empty($Eleanor->module['pid']))
	return'';

$mc=$Eleanor->module['config'];
$near=$reads=array();
$R=Eleanor::$Db->Query('SELECT `p`.`id`,`p`.`reads` FROM `'.$mc['rt'].'` `rt` INNER JOIN `'.$mc['t'].'` `p` ON `rt`.`id`=`p`.`id` WHERE `rt`.`tag`'.Eleanor::$Db->In($Eleanor->module['ptags']));
while($a=$R->fetch_row())
	if($Eleanor->module['pid']!=$a[0])
	{
		$near[$a[0]]=isset($near[$a[0]]) ? $near[$a[0]]+1 : 1;
		$reads[$a[0]]=$a[1];
	}

if(!$near)
	return'';

foreach($near as $k=>&$v)
	$v.='-'.$reads[$k];

unset($v,$reads);
natsort($near);

$near=array_keys(array_reverse($near,true));
if(isset($near[5]))
	$near=array_slice($near,0,5);

$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`lcats`,`title` FROM `'.$mc['tl'].'` WHERE `id`'.Eleanor::$Db->In($near).' AND `lstatus`=1');
if($R->num_rows>0)
{
	echo'<ul>';
	while($a=$R->fetch_assoc())
	{
		$a['lcats']=$a['lcats'] ? (int)ltrim($a['lcats'],',') : false;
		$u=array('u'=>array($a['uri'],'id'=>$a['id']));
		if($a['lcats'] and $Eleanor->Url->furl)
		{
			$cu=$Eleanor->Categories->GetUri($a['lcats']);
			if($cu)
				$u=$cu+$u;
		}
		echo'<li><a href="'.$Eleanor->Url->Construct($u).'">'.$a['title'].'</a></li>';
	}
	echo'</ul>';
}