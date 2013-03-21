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
if($_SERVER['REQUEST_METHOD']=='POST')
{
	Eleanor::$Db->Delete(P.'qmenu','`type`=\''.Eleanor::$service.'\' AND `uid`='.Eleanor::$Login->GetUserValue('id'));
	$mids=isset($_POST['mid']) ? (array)$_POST['mid'] : array();
	$lids=isset($_POST['lid']) ? (array)$_POST['lid'] : array();
	$cnt=count($mids);
	if($cnt>0)
	{
		if(count($lids)<$cnt)
			$lids=array_pad($lids,$cnt,0);
		$uids=array_fill(0,$cnt,Eleanor::$Login->GetUserValue('id'));
		$types=array_fill(0,$cnt,Eleanor::$service);
		$poses=range(0,$cnt-1);
		Eleanor::$Db->Insert(P.'qmenu',array('type'=>$types,'uid'=>$uids,'pos'=>$poses,'mid'=>$mids,'lid'=>$lids));
	}
	Eleanor::$Cache->Delete(Eleanor::$service.'_qmenu'.Eleanor::$Login->GetUserValue('id').Language::$main);
	Eleanor::$Cache->Put(Eleanor::$service.'_qbmenu'.Eleanor::$Login->GetUserValue('id'),isset($_POST['bigicons']));
}

$to_sort=$modules=$titles=array();

$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`title_l`,`path`,`image`,`api` FROM `'.P.'modules` WHERE `api`!=\'\'');
while($a=$R->fetch_assoc())
{
	$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
	$titles[$a['id']]=$a['title_l'];
	$to_sort[$a['id']]=array(
		'api'=>Eleanor::FormatPath($a['api'],$a['path']),
		'image'=>str_replace('*','small',$a['image']),
		'sections'=>$a['sections'],
	);
}

asort($titles,SORT_STRING);
foreach($titles as $k=>&$v)
{
	$class='Api'.basename(dirname($to_sort[$k]['api']));
	do
	{
		if(class_exists($class,false))
			break;
		if(is_file($to_sort[$k]['api']))
		{
			include($to_sort[$k]['api']);
			if(class_exists($class,false))
				break;
		}
		continue 2;
	}while(false);

	if(method_exists($class,'QuickMenu'))
	{
		$Plug=new$class;
		$menu=$Plug->QuickMenu(Eleanor::$service,$to_sort[$k]);
		if($menu)
			$modules[$k]=array(
				'menu'=>$menu,
				'image'=>$to_sort[$k]['image'] ? $to_sort[$k]['image'] : 'default.png',
				'title'=>$v,
			);
		else
			unset($modules[$k]);
	}
}
unset($Plug,$to_sort,$titles);

$n=1;
$R=Eleanor::$Db->Query('SELECT `mid`,`lid` FROM `'.P.'qmenu` WHERE `type`=\'admin\' AND `uid`='.Eleanor::$Login->GetUserValue('id').' ORDER BY `pos` ASC');
while($a=$R->fetch_assoc())
	if(isset($modules[$a['mid']]['menu'][$a['lid']]))
		$modules[$a['mid']]['menu'][$a['lid']]['_act']=$n++;

$GLOBALS['title'][]='Admin menu editor';
$s=Eleanor::$Template->Qmenu(array(
	'big'=>Eleanor::$Cache->Get(Eleanor::$service.'_qbmenu'.Eleanor::$Login->GetUserValue('id'),true),
	'modules'=>$modules,
));
Start('');
echo$s;