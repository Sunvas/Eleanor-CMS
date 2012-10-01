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
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_admin-*.php','mp');
Eleanor::$Template->queue[]='AdminMainpage';

$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'modules` WHERE `id` NOT IN (SELECT `id` FROM `'.P.'mainpage`) AND `id`!='.$Eleanor->module['id']);
list($Eleanor->module['cnt'])=$R->fetch_row();

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->module['cnt']>0 ? $Eleanor->Url->Construct(array('do'=>'add')) : false,
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'add':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				Save(0);
			else
				AddEdit(0);
		break;
		case'resort':
			Resort(true);
		break;
		default:
			ShowList();
	}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		Save($id);
	else
		AddEdit($id);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	if(Eleanor::$our_query)
	{
		$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
		if($R->num_rows>0)
		{
			list($p)=$R->fetch_row();
			Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`-1'),'`pos`>'.$p.' LIMIT 1');
			Eleanor::$Db->Delete(P.'mainpage','`id`='.$id.' LIMIT 1');
			Eleanor::$Cache->Obsolete('mainpage');
		}
	}
	GoAway(true);
}
elseif(isset($_GET['up']))
{
	$id=(int)$_GET['up'];
	if(!Eleanor::$our_query)
		return GoAway();
	$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
	if($R->num_rows==0)
		return GoAway();
	list($posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`pos`),`pos` FROM `'.P.'mainpage` WHERE `pos`=(SELECT MAX(`pos`) FROM `'.P.'mainpage` WHERE `pos`<'.$posit.')');
	list($cnt,$np)=$R->fetch_row();
	if($cnt>0)
	{
		if($cnt>1 or $np+1!=$posit)
		{
			Resort();
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`+1'),'`pos`='.--$posit.' LIMIT 1');
		Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`-1'),'`id`='.$id.' LIMIT 1');
	}
	Eleanor::$Cache->Obsolete('mainpage');
	GoAway(false,301,'it'.$id);
}
elseif(isset($_GET['down']))
{
	$id=(int)$_GET['down'];
	if(!Eleanor::$our_query)
		return GoAway();
	$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
	if($R->num_rows==0)
		return GoAway();
	list($posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`pos`),`pos` FROM `'.P.'mainpage` WHERE `pos`=(SELECT MIN(`pos`) FROM `'.P.'mainpage` WHERE `pos`>'.$posit.')');
	list($cnt,$np)=$R->fetch_row();
	if($cnt>0)
	{
		if($cnt>1 or $np-1!=$posit)
		{
			Resort();
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`-1'),'`pos`='.++$posit.' LIMIT 1');
		Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`+1'),'`id`='.$id.' LIMIT 1');
	}
	Eleanor::$Cache->Obsolete('mainpage');
	GoAway(false,301,'it'.$id);
}
else
	ShowList();

function ShowList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['mp']['listmp'];
	$items=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`services`,`title_l` `title`,`descr_l` `descr`,`protected`,`path`,`image`,`active`,`pos` FROM `'.P.'modules` INNER JOIN `'.P.'mainpage` USING(`id`) ORDER BY `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
		$a['descr']=$a['descr'] ? Eleanor::FilterLangValues((array)unserialize($a['descr'])) : '';
		$a['services']=$a['services'] ? explode(',',trim($a['services'],',')) : array();

		$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
		$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
		$a['_aup']=$a['pos']>1 ? $Eleanor->Url->Construct(array('up'=>$a['id'])) : false;
		$a['_adown']=$a['pos']<$R->num_rows ? $Eleanor->Url->Construct(array('down'=>$a['id'])) : false;

		$items[$a['id']]=array_slice($a,1);
	}
	$c=Eleanor::$Template->ShowList($items);
	Start();
	echo$c;
}

function AddEdit($id,$error='')
{global$Eleanor,$title;
	$lang=Eleanor::$Language['mp'];
	if($id)
	{
		if(!$error)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway();
		}
		$title=$lang['editing'];
	}
	else
	{
		if($Eleanor->module['cnt']==0)
			return GoAway();
		$title[]=$lang['adding'];
		$values=array(
			'id'=>0,
			'pos'=>'',
		);
	}
	if($error)
	{		if($error===true)
			$error='';
		$bypost=true;
		$values['id']=isset($_POST['id']) ? (int)$_POST['id'] : 0;
		$values['pos']=isset($_POST['pos']) ? $_POST['pos'] : '';
	}
	else
		$bypost=false;

	$modules=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'modules` WHERE `id` NOT IN (SELECT `id` FROM `'.P.'mainpage` WHERE `id`!='.$values['id'].') AND `id`!='.$Eleanor->module['id']);
	while($a=$R->fetch_assoc())
		$modules[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues(unserialize($a['title_l'])) : '';
	asort($modules,SORT_STRING);

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->AddEdit($id,$values,$modules,$error,$back,$bypost);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$values=array(
		'id'=>isset($_POST['id']) ? (int)$_POST['id'] : 0,
		'pos'=>isset($_POST['pos']) ? $_POST['pos'] : '',
	);
	if(!$id and $Eleanor->module['cnt']==0 or $Eleanor->module['id']==$values['id'])
		return GoAway();

	if(!$id or $id!=$values['id'])
	{
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'mainpage` WHERE `id`='.$values['id'].' LIMIT 1');
		if($R->num_rows>0)
			return AddEdit($id,Eleanor::$Language['mp']['mexists']);
	}

	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'mainpage` WHERE `id`='.$id.' LIMIT 1');
		if(!list($pos)=$R->fetch_row())
			return GoAway();

		$values['pos']=(int)$values['pos'];
		if($values['pos']<=0)
			$values['pos']=1;
		if($pos!=$values['pos'])
		{
			Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`-1'),'`pos`>'.$pos);
			Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos']);
		}
		Eleanor::$Db->Update(P.'mainpage',$values,'id='.$id.' LIMIT 1');
	}
	else
	{
		if($values['pos']=='')
		{
			$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.P.'mainpage`');
			list($pos)=$R->fetch_row();
			$values['pos']=$pos===null ? 1 : $pos+1;
		}
		else
		{
			if($values['pos']<=0)
				$values['pos']=1;
			Eleanor::$Db->Update(P.'mainpage',array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos']);
		}
		Eleanor::$Db->Insert(P.'mainpage',$values);
	}
	Eleanor::$Cache->Obsolete('mainpage');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}

function Resort()
{
	$R=Eleanor::$Db->Query('SELECT `id`,`pos` FROM `'.P.'mainpage` ORDER BY `pos` ASC');
	$cnt=1;
	while($a=$R->fetch_assoc())
	{
		if($a['pos']!=$cnt)
			Eleanor::$Db->Update(P.'mainpage',array('pos'=>$cnt),'`id`='.$a['id'].' LIMIT 1');
		++$cnt;
	}
	Eleanor::$Cache->Obsolete('mainpage');
}