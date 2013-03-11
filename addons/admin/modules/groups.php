<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/
if(!defined('CMS'))die;
global$Eleanor,$title;
$lang=Eleanor::$Language->Load('addons/admin/langs/groups-*.php','g');
Eleanor::$Template->queue[]='Groups';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
);

$Eleanor->gp_post=false;
$Eleanor->gp=array(
	$lang['base_o'],
	'title_l'=>array(
		'title'=>$lang['g_name'],
		'descr'=>'',
		'noinherit'=>true,
		'field'=>'title',
		'type'=>'input',
		'load'=>'LLoad',
		'save'=>'LSave',
		'multilang'=>Eleanor::$vars['multilang'],#Не ствим true потому что LLoad сам обрабатывает значения в нужно виде :)
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'htmlsafe'=>true,#Только для текстовых данных
		),
	),
	'html_pref'=>array(
		'title'=>$lang['html_pref'],
		'descr'=>$lang['html_pref_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'html_end'=>array(
		'title'=>$lang['html_suf'],
		'descr'=>$lang['html_suf_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'descr_l'=>array(
		'title'=>$lang['descr'],
		'descr'=>'',
		'type'=>'editor',
		'bypost'=>&$Eleanor->gp_post,
		'load'=>'LLoad',
		'save'=>'LSave',
	),
	$lang['global_r'],
	'access_cp'=>array(
		'title'=>$lang['aa'],
		'descr'=>'',
		'type'=>'check',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'extra'=>array('onclick'=>'if(this.checked) return confirm(\''.$lang['are_you_sure'].'\')'),
		),
	),
	'banned'=>array(
		'title'=>$lang['ban'],
		'descr'=>$lang['ban_'],
		'bypost'=>&$Eleanor->gp_post,
		'type'=>'check',
	),
	'captcha'=>array(
		'title'=>$lang['captcha'],
		'descr'=>$lang['captcha_'],
		'bypost'=>&$Eleanor->gp_post,
		'type'=>'check',
	),
	'moderate'=>array(
		'title'=>$lang['moderate'],
		'descr'=>$lang['moderate_'],
		'bypost'=>&$Eleanor->gp_post,
		'type'=>'check',
	),
	'sh_cls'=>array(
		'title'=>$lang['cls'],
		'descr'=>'',
		'bypost'=>&$Eleanor->gp_post,
		'type'=>'check',
	),
	$lang['limits'],
	'flood_limit'=>array(
		'title'=>$lang['flood_limit'],
		'descr'=>$lang['flood_limit_'],
		'bypost'=>&$Eleanor->gp_post,
		'save'=>'IntSave',
		'type'=>'input',
	),
	'search_limit'=>array(
		'title'=>$lang['search_limit'],
		'descr'=>$lang['search_limit_'],
		'bypost'=>&$Eleanor->gp_post,
		'save'=>'IntSave',
		'type'=>'input',
	),
	'max_upload'=>array(
		'title'=>$lang['max_size'],
		'descr'=>$lang['max_size_'],
		'bypost'=>&$Eleanor->gp_post,
		'type'=>'input',
		'default'=>0,
	),
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
	$R=Eleanor::$Db->Query('SELECT `title_l` `title`,`html_pref`,`html_end`,`parents` FROM `'.P.'groups` WHERE `id`='.$id.' AND `protected`=0');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'groups','`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Delete(P.'groups','`parents` LIKE \''.$a['parents'].$id.',%\' LIMIT 1');
		Eleanor::$Cache->Lib->DeleteByTag('groups');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;
}
else
	ShowList();

function IntSave($a)
{
	return abs((int)$a['value']);
}

function LLoad($a)
{
	$ret=$a['value'] ? (array)unserialize($a['value']) : array();
	return array('value'=>$a['multilang'] ? $ret : Eleanor::FilterLangValues($ret));
}

function LSave($a,$Obj)
{
	$v=(array)$a['value'];
	if(isset($a['field']))
	{
		$lang=Eleanor::$Language['g'];
		foreach($v as $k=>&$vv)
			if($vv=='')
			{
				$er=$a['multilang'] ? '_'.strtoupper($k) : '';
				$Obj->errors['EMPTY_TITLE'.$er]=$lang['notitle']($k);
			}
	}
	return$a['multilang'] ? serialize($v) : serialize(array(''=>$a['value']));
}

function ShowList()
{global$Eleanor,$title;
	$lang=Eleanor::$Language['g'];
	$title[]=$lang['list'];
	$parent=isset($_GET['parent']) ? (int)$_GET['parent'] : 0;
	$temp=$items=$titles=$subitems=$navi=array();
	$parents='';
	if($parent>0)
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'groups` WHERE `id`='.$parent.' LIMIT 1');
		list($parents)=$R->fetch_row();
		$parents.=$parent;
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'groups` WHERE `id` IN ('.$parents.')');
		while($a=$R->fetch_assoc())
			$temp[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
		$navi[0]=array('title'=>$lang['list'],'_a'=>$Eleanor->Url->Prefix());
		foreach(explode(',',$parents) as $v)
			if(isset($temp[$v]))
				$navi[$v]=array('title'=>$temp[$v],'_a'=>$v==$parent ? false : $Eleanor->Url->Construct(array('parent'=>$v)));
		$Eleanor->module['links']['add']=$Eleanor->Url->Construct(array('do'=>'add','parent'=>$parent));
		$parents.=',';
	}

	$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end`,`protected`,`access_cp`,`captcha`,`moderate`,`parents` FROM `'.P.'groups` WHERE `parents`=\''.$parents.'\'');
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
		$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
		$a['_adel']=$a['protected'] ? false : $Eleanor->Url->Construct(array('delete'=>$a['id']));
		$a['_aparent']=$Eleanor->Url->Construct(array('parent'=>$a['id']));
		$a['_aaddp']=$Eleanor->Url->Construct(array('do'=>'add','parent'=>$a['id']));

		$titles[$a['id']]=$a['title'];
		$subitems[]=$a['parents'].$a['id'].',';
		$temp[$a['id']]=array_slice($a,1);
	}

	if($subitems)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`parents`,`title_l` FROM `'.P.'groups` WHERE `parents`'.Eleanor::$Db->In($subitems));
		$subitems=array();
		while($a=$R->fetch_assoc())
		{
			$p=ltrim(strrchr(','.rtrim($a['parents'],','),','),',');
			$subitems[$p][$a['id']]=array(
				'title'=>$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '',
				'_aedit'=>$Eleanor->Url->Construct(array('edit'=>$a['id']))
			);
		}
	}

	natsort($titles);
	foreach($titles as $k=>&$v)
		$items[$k]=&$temp[$k];

	$c=Eleanor::$Template->ShowList($items,$subitems,$navi);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$values=array('_parent'=>isset($_GET['parent']) ? (int)$_GET['parent'] : 0);
	$lang=Eleanor::$Language['g'];
	$inherit=array();
	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'groups` WHERE `id`='.$id.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway(true);
		if(!$errors)
		{
			$a['_parent']=ltrim(strrchr(','.rtrim($a['parents'],','),','),',');
			foreach($a as $k=>&$v)
				if(isset($Eleanor->gp[$k]))
					if($v===null)
						$inherit[]=$k;
					else
						$values[$k]['value']=$v;
				else
					$values[$k]=$v;
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['adding'];
		$like=isset($_GET['like']) ? (int)$_GET['like'] : false;
		if(!$errors and $like)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'groups` WHERE `id`='.$like.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			foreach($a as $k=>&$v)
				if(isset($Eleanor->gp[$k]))
					if($v===null)
						$inherit[]=$k;
					else
						$values[$k]['value']=$v;
				else
					$values[$k]=$v;
		}
	}
	if($errors)
	{
		if($errors===true)
			$errors=array();
		$Eleanor->gp_post=true;
		$values['_parent']=isset($_POST['_parent']) ? (int)$_POST['_parent'] : 0;
		$inherit=isset($_POST['inherit']) ? (array)$_POST['inherit'] : array();
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$values=$Eleanor->Controls->DisplayControls($Eleanor->gp,$values)+$values;
	$links=array(
		'delete'=>!$id || $a['protected'] ? false : $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)),
	);

	$c=Eleanor::$Template->AddEdit($id,$Eleanor->gp,$values,$inherit,$errors,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$values=array('parents'=>'');
	$parent=isset($_POST['_parent']) ? (int)$_POST['_parent'] : 0;
	if($parent>0)
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'groups` WHERE `id`='.$parent.' LIMIT 1');
		if(list($p)=$R->fetch_row())
			$values['parents']=$p.$parent.',';
	}

	$gp=$Eleanor->gp;
	$inherit=isset($_POST['inherit']) ? (array)$_POST['inherit'] : array();
	foreach($inherit as &$v)
		if(isset($gp[$v]) and empty($gp[$v]['noinherit']))
		{
			unset($gp[$v]);
			$values[$v]=null;
		}

	try
	{
		$values+=$Eleanor->Controls->SaveControls($gp);
	}
	catch(EE$E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}
	$errors=$Eleanor->Controls->errors;

	if(!$values['access_cp'] and $id)
	{
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'groups` WHERE `access_cp`=1 AND `id`!='.$id.' LIMIT 1');
		if($R->num_rows==0)
			$errors[]='NO_ADMIN';
	}

	if($errors)
		return AddEdit($id,$errors);

	if($id)
		Eleanor::$Db->Update(P.'groups',$values,'`id`='.$id.' LIMIT 1');
	else
		Eleanor::$Db->Insert(P.'groups',$values);
	Eleanor::$Cache->Lib->DeleteByTag('groups');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}