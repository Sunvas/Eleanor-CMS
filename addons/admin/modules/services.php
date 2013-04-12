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
$lang=Eleanor::$Language->Load('addons/admin/langs/services-*.php','ser');
Eleanor::$Template->queue[]='Services';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
);

$Eleanor->sp_post=false;
$Eleanor->sp=array(
	'name'=>array(
		'title'=>$lang['name'],
		'descr'=>'',
		'type'=>'input',
		'imp'=>true,
		'_proptected'=>true,
		'load'=>function($a)
		{
			if($a['_protected'])
				$a['options']['extra']['disabled']='disabled';
			return$a;
		},
		'save'=>function($a)
		{
			if(!$a['_protected'])
				return$a['value'];
		},
		'bypost'=>&$Eleanor->sp_post,
		'options'=>array(
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'file'=>array(
		'title'=>$lang['file'],
		'descr'=>'',
		'type'=>'input',
		'imp'=>true,
		'bypost'=>&$Eleanor->sp_post,
		'options'=>array(
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'login'=>array(
		'title'=>$lang['login'],
		'descr'=>'',
		'type'=>'select',
		'imp'=>true,
		'bypost'=>&$Eleanor->sp_post,
		'options'=>array(
			'callback'=>function($a)
			{
				$ret='';
				$Dir=dir(Eleanor::$root.'core/login');
				while($entry=$Dir->read())
				{
					if(is_file(Eleanor::$root.'core/login/'.$entry))
						$ret.=Eleanor::Option($entry=basename($entry,'.php'),false,in_array($entry,$a['value']));
				}
				$Dir->close();
				return$ret;
			},
			'htmlsafe'=>false,#Только для текстовых данных
		),
	),
	'protected'=>array(
		'title'=>$lang['prot'],
		'descr'=>'',
		'type'=>'check',
		'imp'=>false,
		'load'=>function($a)
		{
			if($a['_name'])
				$a['options']['extra']['disabled']='disabled';
			return$a;
		},
		'save'=>function($a)
		{
			if(!$a['_name'])
				return$a['value'];
		},
		'bypost'=>&$Eleanor->sp_post,
	),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'add':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				Save(false);
			else
				AddEdit(false);
		break;
		default:
			ServicesList();
	}
elseif(isset($_GET['edit']))
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		Save((string)$_GET['edit']);
	else
		AddEdit((string)$_GET['edit']);
elseif(isset($_GET['delete']))
{
	$name=(string)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `name`,`file` FROM `'.P.'services` WHERE `name`='.Eleanor::$Db->Escape($name,true).' AND `protected`=0 LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'services','`name`='.Eleanor::$Db->Escape($name,true).' LIMIT 1');
		Eleanor::$Cache->Obsolete('system-services');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$s=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$s;
}
else
	ServicesList();

function ServicesList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['ser']['list'];

	$items=array();
	$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'services` ORDER BY `name` ASC');
	while($a=$R->fetch_assoc())
	{
		$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['name']));
		$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['name']));
		$items[]=$a;
	}

	$c=Eleanor::$Template->Services($items);
	Start();
	echo$c;
}

function AddEdit($name,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['ser'];
	$values=array_combine(array_keys($Eleanor->sp),array_fill(0,count($Eleanor->sp),array('_name'=>$name,'_protected'=>false)));
	if($name)
	{
		$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'services` WHERE `name`='.Eleanor::$Db->Escape($name,true).' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway();
		foreach($a as $k=>&$v)
			if(isset($Eleanor->sp[$k]))
			{
				if(!$errors)
					$values[$k]['value']=$v;
				if(isset($values[$k]['_protected']))
					$values[$k]['_protected']=$a['protected'];
			}
		$title[]=$lang['editing'];
	}
	else
		$title[]=$lang['adding'];

	if($errors)
	{
		if($error===true)
			$error=array();
		$Eleanor->sp_post=true;
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>!$name || $values['protected']['value'] ? false : $Eleanor->Url->Construct(array('delete'=>$name,'noback'=>1)),
	);
	$values=$Eleanor->Controls->DisplayControls($Eleanor->sp,$values)+$values;
	$c=Eleanor::$Template->AddEdit($name,$Eleanor->sp,$values,$errors,$back,$links);
	Start();
	echo$c;
}

function Save($name)
{global$Eleanor;
	$lang=Eleanor::$Language['ser'];
	if($name)
	{
		$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'services` WHERE `name`='.Eleanor::$Db->Escape($name,true).' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway();
		foreach($Eleanor->sp as &$v)
		{
			$v['_name']=$name;
			$v['_protected']=$a['protected'];
		}
	}
	else
		foreach($Eleanor->sp as &$v)
		{
			$v['_name']=$name;
			$v['_protected']=false;
		}

	try
	{
		$values=$Eleanor->Controls->SaveControls($Eleanor->sp);
	}
	catch(EE$E)
	{
		return AddEdit($name,array('ERROR'=>$E->getMessage()));
	}
	$values['login']=preg_replace('#[^a-z0-9\-_]+#i','',$values['login']);

	if(!$values['login'])
		$values['login']='no';
	if(!is_file(Eleanor::$root.'core/login/'.$values['login'].'.php'))
		return AddEdit($id,array('NO_LOGIN'));

	if(isset($values['name']))
		$values['name']=trim($values['name']);
	if($name)
	{
		if($a['protected'])
		{
			unset($values['protected'],$values['name']);
			if($a['protected']==1)
				unset($values['login']);
		}
		elseif(!$values['name'])
			return AddEdit($name,array('EMPTY_TITLE'));
		elseif($values['name']!=$name)
		{
			$R=Eleanor::$Db->Query('SELECT `name` FROM `'.P.'services` WHERE `name`='.Eleanor::$Db->Escape($values['name'],true).' LIMIT 1');
			if($R->num_rows!=0)
				return AddEdit($id,array('EXISTS'));
		}
		Eleanor::$Db->Update(P.'services',$values,'`name`='.Eleanor::$Db->Escape($name,true).' LIMIT 1');
	}
	else
	{
		if(!$values['name'])
			return AddEdit($name,array('EMPTY_TITLE'));
		$R=Eleanor::$Db->Query('SELECT `name` FROM `'.P.'services` WHERE `name`='.Eleanor::$Db->Escape($values['name'],true).' LIMIT 1');
		if($R->num_rows!=0)
			return AddEdit($id,array('EXISTS'));
		Eleanor::$Db->Insert(P.'services',$values);
	}
	Eleanor::$Cache->Obsolete('system-services');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}