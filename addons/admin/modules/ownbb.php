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
$lang=Eleanor::$Language->Load('addons/admin/langs/ownbb-*.php','ownbb');
Eleanor::$Template->queue[]='Ownbb';

$Eleanor->ownbbs=array();
$R=Eleanor::$Db->Query('SELECT `handler` FROM `'.P.'ownbb`');
while($a=$R->fetch_assoc())
	$Eleanor->ownbbs[]=$a['handler'];
$Eleanor->realown=glob(Eleanor::$root.'core/ownbb/*.php');
foreach($Eleanor->realown as &$v)
	$v=basename($v);
$isownbb=count(array_diff($Eleanor->realown,$Eleanor->ownbbs))>0;

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$isownbb ? $Eleanor->Url->Construct(array('do'=>'add')) : false,
	'recache'=>$Eleanor->Url->Construct(array('do'=>'recache')),
);

$Eleanor->gp_post=false;
$Eleanor->gp=array(
	$lang['general'],
	'handler'=>array(
		'title'=>$lang['handler'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'callback'=>function($a)
			{global$Eleanor;
				$r='';
				$ad=array_diff($Eleanor->realown,array_diff($Eleanor->ownbbs,$a['value']));
				foreach($ad as &$v)
					$r.=Eleanor::Option($v,false,in_array($v,$a['value']));
				return$r;
			},
		),
	),
	'tags'=>array(
		'title'=>$lang['tags'],
		'descr'=>$lang['tags_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'special'=>array(
		'title'=>$lang['special'],
		'descr'=>$lang['special_'],
		'type'=>'check',
		'bypost'=>&$Eleanor->gp_post,
	),
	'no_parse'=>array(
		'title'=>$lang['no_parse'],
		'descr'=>$lang['no_parse_'],
		'type'=>'check',
		'bypost'=>&$Eleanor->gp_post,
	),
	'sp_tags'=>array(
		'title'=>$lang['sp_tags'],
		'descr'=>$lang['sp_tags_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'htmlsafe'=>true,#Только для текстовых данных
		),
	),
	'active'=>array(
		'title'=>$lang['activate'],
		'descr'=>'',
		'type'=>'check',
		'bypost'=>&$Eleanor->gp_post,
	),
	Eleanor::$Language['main']['options'],
	'pos'=>array(
		'title'=>$lang['pos'],
		'descr'=>$lang['pos_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'gr_use'=>array(
		'title'=>$lang['can_use'],
		'descr'=>$lang['can_use_'],
		'type'=>'items',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'callback'=>'GroupsOptions',
		),
	),
	'gr_see'=>array(
		'title'=>$lang['can_see'],
		'descr'=>$lang['can_see_'],
		'type'=>'items',
		'bypost'=>&$Eleanor->gp_post,
		'options'=>array(
			'callback'=>'GroupsOptions',
		),
	),
	'sb'=>array(
		'title'=>$lang['sb'],
		'descr'=>'',
		'type'=>'check',
		'bypost'=>&$Eleanor->gp_post,
	),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'resort':
			Resort();
			GoAway();
		break;
		case'add':
			if($isownbb)
			{
				if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
					Save(0);
				else
					AddEdit(0);
				break;
			}
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
	$R=Eleanor::$Db->Query('SELECT `tags`,`pos` FROM `'.P.'ownbb` WHERE `id`='.$id.' LIMIT 1');
	if(!Eleanor::$our_query or !$a=$R->fetch_assoc())
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'ownbb','`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Update(P.'ownbb',array('!pos'=>'`pos`-1'),'`pos`>'.$a['pos']);
		Eleanor::$Cache->Obsolete('ownbb');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;
}
elseif(isset($_GET['swap']))
{
	if(Eleanor::$our_query)
		Eleanor::$Db->Update(P.'ownbb',array('!active'=>'NOT `active`'),'`id`='.(int)$_GET['swap'].' LIMIT 1');
	Eleanor::$Cache->Obsolete('ownbb');
	GoAway();
}
elseif(isset($_GET['up']))
{
	$id=(int)$_GET['up'];
	$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'ownbb` WHERE `id`='.$id.' LIMIT 1');
	if(!Eleanor::$our_query or $R->num_rows==0)
		return GoAway();
	list($posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'ownbb` WHERE `pos`=(SELECT MAX(`pos`) FROM `'.P.'ownbb` WHERE `pos`<'.$posit.')');
	list($cnt)=$R->fetch_row();
	if($cnt>0)
	{
		if($cnt>1)
		{
			Resort();
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'ownbb` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update(P.'ownbb',array('!pos'=>'`pos`+1'),'`pos`='.--$posit.' LIMIT 1');
		Eleanor::$Db->Update(P.'ownbb',array('!pos'=>'`pos`-1'),'`id`='.$id.' LIMIT 1');
	}
	Eleanor::$Cache->Obsolete('ownbb');
	GoAway();
}
elseif(isset($_GET['down']))
{
	$id=(int)$_GET['down'];
	$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'ownbb` WHERE `id`='.$id.' LIMIT 1');
	if(!Eleanor::$our_query or $R->num_rows==0)
		return GoAway();
	list($posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'ownbb` WHERE `pos`=(SELECT MIN(`pos`) FROM `'.P.'ownbb` WHERE `pos`>'.$posit.')');
	list($cnt)=$R->fetch_row();
	if($cnt>0)
	{
		list($cnt)=$R->fetch_row();
		if($cnt>1)
		{
			Resort();
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'ownbb` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update(P.'ownbb', array('!pos'=>'`pos`-1'),'`pos`='.++$posit.' LIMIT 1');
		Eleanor::$Db->Update(P.'ownbb', array('!pos'=>'`pos`+1'),'`id`='.$id.' LIMIT 1');
	}
	Eleanor::$Cache->Obsolete('ownbb');
	GoAway();
}
else
	ShowList();

function GroupsOptions()
{
	$items=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'groups`');
	while($a=$R->fetch_assoc())
	{
		$ret=$a['title_l'] ? unserialize($a['title_l']) : array();
		$items[$a['id']]=Eleanor::FilterLangValues($ret);
	}
	asort($items,SORT_STRING);
	return$items;
}

function ShowList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['ownbb']['list'];

	$items=array();
	if($Eleanor->ownbbs)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`pos`,`active`,`handler`,`tags`,`special`,`sp_tags` FROM `'.P.'ownbb` ORDER BY `pos` ASC');
		while($a=$R->fetch_assoc())
		{
			if(in_array($a['handler'],$Eleanor->realown))
				$a['_aact']=$Eleanor->Url->Construct(array('swap'=>$a['id']));
			else
			{
				Eleanor::$Db->Update(P.'ownbb',array('active'=>0),'`id`='.$a['id'].' LIMIT 1');
				$a['_aact']=false;
			}

			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			$a['_aup']=$a['pos']>1 ? $Eleanor->Url->Construct(array('up'=>$a['id'])) : false;
			$a['_adown']=$a['pos']<$R->num_rows ? $Eleanor->Url->Construct(array('down'=>$a['id'])) : false;

			$items[$a['id']]=array_slice($a,1);
		}
	}

	$c=Eleanor::$Template->ShowList($items);
	Start();
	echo$c;
}

function Resort()
{
	$n=0;
	$R=Eleanor::$Db->Query('SELECT `id`,`pos` FROM `'.P.'ownbb` ORDER BY `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		++$n;
		if($a['pos']!=$n)
			Eleanor::$Db->Update(P.'ownbb',array('pos'=>$n),'`id`='.$a['id'].' LIMIT 1');
	}
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['ownbb'];
	$values=array();
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'ownbb` WHERE `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			foreach($a as $k=>$v)
			{
				if(in_array($k,array('gr_use','gr_see')))
					$v=$v ? explode(',',$v) : array();
				$values[$k]['value']=$v;
			}
		}
		$title[]=$lang['editing'];
	}
	else
		$title[]=$lang['adding'];

	if($errors)
	{
		if($errors===true)
			$errors=array();
		$Eleanor->gp_post=true;
	}
	$a=$Eleanor->Controls->DisplayControls($Eleanor->gp,$values);
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id)) : false,
	);
	$c=Eleanor::$Template->AddEdit($id,$Eleanor->gp,$a,$errors,$links,$back);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$C=new Controls;
	$C->throw=false;
	try
	{
		$values=$C->SaveControls($Eleanor->gp);
	}
	catch(EE$E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}
	$errors=$C->errors;
	$lang=Eleanor::$Language['ownbb'];

	if($values['tags'])
	{
		$tags=explode(',',$values['tags']);
		$ab=constant(Language::$main.'::ALPHABET');
		foreach($tags as &$v)
		{
			$v=trim($v);
			if($v=='' or preg_match('#^[a-z0-9\-'.$ab.'_]+$#i',$v)==0)
			{
				$errors[]='ERROR_TAGS';
				break;
			}
		}
		$values['tags']=join(',',$tags);
	}
	else
		$errors[]='EMPTY_TAGS';

	if($values['sp_tags'])
	{
		$tags=explode(',',$values['sp_tags']);
		foreach($tags as &$v)
		{
			$v=trim($v);
			if($v=='' or preg_match('#^[a-z0-9\-'.$ab.'_]+$#i',$v)==0)
			{
				$errors[]='ERROR_STAGS';
				break;
			}
		}
		$values['sp_tags']=join(',',$tags);
	}

	if($errors)
		return AddEdit($id,$errors);

	if(!$values['handler'])
		$values['active']=0;

	$values['gr_use']=$values['gr_use'] ? join(',',$values['gr_use']) : '';
	$values['gr_see']=$values['gr_see'] ? join(',',$values['gr_see']) : '';
	if($id)
	{
		$values['pos']=(int)$values['pos'];
		$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'ownbb` WHERE `id`='.$id);
		list($pos)=$R->fetch_row();
		if($pos!=$values['pos'])
		{
			Eleanor::$Db->Update(P.'ownbb',array('!pos'=>'`pos`-1'),'`pos`>'.$pos);
			Eleanor::$Db->Update(P.'ownbb',array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos']);
		}
		Eleanor::$Db->Update(P.'ownbb',$values,'`id`='.$id.' LIMIT 1');
	}
	else
	{
		if($values['pos']=='')
		{
			$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.P.'ownbb`');
			list($pos)=$R->fetch_row();
			$values['pos']=$pos===null ? 0 : $pos+1;
		}
		else
			Eleanor::$Db->Update(P.'ownbb',array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos']);
		Eleanor::$Db->Insert(P.'ownbb',$values);
	}
	Eleanor::$Cache->Obsolete('ownbb');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}