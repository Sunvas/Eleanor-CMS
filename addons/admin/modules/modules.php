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
$lang=Eleanor::$Language->Load('addons/admin/langs/modules-*.php','modules');
Eleanor::$Template->queue[]='Modules';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
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
	$R=Eleanor::$Db->Query('SELECT `title_l` `title` FROM `'.P.'modules` WHERE `id`='.$id.' AND `protected`=0 LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway();
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'modules','`id`='.$id.' AND `protected`=0');
		Eleanor::$Cache->Obsolete('mainpage');
		Eleanor::$Cache->Lib->DeleteByTag('modules');
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
elseif(isset($_GET['swap']))
{
	$id=(int)$_GET['swap'];
	if(Eleanor::$our_query)
		Eleanor::$Db->Update(P.'modules',array('!active'=>'NOT `active`'),'`id`='.$id.' AND `protected`=0 LIMIT 1');
	$back=getenv('HTTP_REFERER');
	Eleanor::$Cache->Obsolete('mainpage');#Для модуля "главная страница"
	GoAway($back ? $back.'#it'.$id : true);
}
else
	ShowList();

function ShowList()
{global$Eleanor,$title;
	$lang=Eleanor::$Language['modules'];
	$tosort=$items=$temp=$groups=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`services`,`title_l` `title`,`descr_l` `descr`,`protected`,`path`,`image`,`active` FROM `'.P.'modules` ORDER BY `protected` ASC');
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
		$a['descr']=$a['descr'] ? Eleanor::FilterLangValues((array)unserialize($a['descr'])) : '';
		$a['services']=$a['services'] ? explode(',,',trim($a['services'],',')) : array();

		$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
		if($a['protected'])
			$a['_adel']=$a['_aswap']=false;
		else
		{
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			$a['_aswap']=$Eleanor->Url->Construct(array('swap'=>$a['id']));
		}

		$temp[$a['id']]=array_slice($a,1);
		$tosort[$a['id']]=$a['title'];
	}
	asort($tosort,SORT_STRING);
	foreach($tosort as $k=>&$v)
		$items[]=$temp[$k];
	unset($tosort,$temp);


	$title[]=$lang['list'];
	$c=Eleanor::$Template->ShowList($items);
	Start();
	echo$c;
}

function AddEdit($id,$error='')
{global$Eleanor,$title;
	$lang=Eleanor::$Language['modules'];
	if($id)
	{
		if(!$error)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'modules` WHERE `id`='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway();
			$values['services']=explode(',,',trim($values['services'],','));
			$values['title']=$values['title_l'] ? (array)unserialize($values['title_l']) : array();
			$values['descr']=$values['descr_l'] ? (array)unserialize($values['descr_l']) : array();
			$values['files']=$values['files'] ? (array)unserialize($values['files']) : array();
			$values['sections']=$values['sections'] ? (array)unserialize($values['sections']) : array();
			$values['path'].='/';
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['adding'];
		$values=array(
			'multiservice'=>false,
			'protected'=>false,
			'sections'=>array(),
			'files'=>array(),
			'services'=>array(),
			'title'=>array(''=>''),
			'descr'=>array(''=>''),
			'active'=>true,
			'path'=>'',
			'file'=>'index.php',
			'image'=>'',
			'api'=>'',
		);
	}
	if($error)
	{
		if($error===true)
			$error='';
		if(Eleanor::$vars['multilang'])
		{
			$values['title']=isset($_POST['title']) ? (array)$_POST['title'] : array();
			$values['descr']=isset($_POST['descr']) ? (array)$_POST['descr'] : array();
		}
		else
		{
			$values['title']=isset($_POST['title']) ? array(''=>(string)$_POST['title']) : array(''=>'');
			$values['descr']=isset($_POST['descr']) ? array(''=>(string)$_POST['descr']) : array(''=>'');
		}

		$values['sections']=isset($_POST['sections']) ? (array)$_POST['sections'] : array();
		$values['services']=isset($_POST['services']) ? (array)$_POST['services'] : array();
		$values['files']=isset($_POST['files']) ? (array)$_POST['files'] : array();
		$values['multiservice']=isset($_POST['multiservice']);
		$values['api']=isset($_POST['api']) ? (string)$_POST['api'] : '';
		$values['image']=isset($_POST['image']) ? (string)$_POST['image'] : '';
		$values['path']=isset($_POST['path']) ? (string)$_POST['path'] : '';
		$values['file']=isset($_POST['file']) ? (string)$_POST['file'] : '';
		$values['active']=isset($_POST['active']);
		if($id)
		{
			$R=Eleanor::$Db->Query('SELECT `services`,`protected`,`path`,`file`,`active` FROM `'.P.'modules` WHERE `id`='.$id.' LIMIT 1');
			$a=$R->fetch_assoc();
			if($a['protected'])
			{
				$values=$a+$values;
				$values['services']=explode(',,',trim($values['services'],','));
			}
		}
		else
			$values['protected']=isset($_POST['protected']);
	}
	if(!$values['sections'])
		$values['sections']=array('section'=>array(''=>array('')));

	foreach($values['sections'] as &$v)
		if(Eleanor::$vars['multilang'])
			$v+=array_combine(array_keys(Eleanor::$langs),array_fill(0,count(Eleanor::$langs),array()));

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>!$id || $values['protected'] ? false : $Eleanor->Url->Construct(array('delete'=>$id)),
	);
	$c=Eleanor::$Template->AddEdit($id,$values,$error,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{
	$lang=Eleanor::$Language['modules'];
	$errors=array();
	if(Eleanor::$vars['multilang'])
	{
		$title=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : array();
		$descr=isset($_POST['descr']) ? (array)Eleanor::$POST['descr'] : array();
	}
	else
	{
		$title=isset($_POST['title']) ? array(''=>(string)Eleanor::$POST['title']) : array();
		$descr=isset($_POST['descr']) ? array(''=>(string)Eleanor::$POST['descr']) : array();
	}
	foreach($title as $k=>&$v)
	{
		$v=trim($v);
		if($v=='')
		{
			$er=$k ? '_'.strtoupper($k) : '';
			$errors['EMPTY_TITLE'.$er]=$lang['empty_title']($k);
		}
	}

	$prot=false;
	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `services`,`protected` FROM `'.P.'modules` WHERE `id`='.$id.' LIMIT 1');
		if(!list($services,$prot)=$R->fetch_row())
			return GoAway();
		$services=explode(',,',trim($services,','));
	}

	if(!$prot)
	{
		$multi=isset($_POST['multiservice']);
		$services=isset($_POST['services']) ? (array)$_POST['services'] : array();
		foreach($services as $k=>&$v)
			if(!isset(Eleanor::$services[$v]))
				unset($services[$k]);
		if(!$services)
			$errors[]='NOSERVICES';

		$path=isset($_POST['path']) ? rtrim($_POST['path'],'/\\') : '';
		$file=isset($_POST['file']) ? (string)$_POST['file'] : '';
		$files=isset($_POST['files']) ? (array)$_POST['files'] : array();
		foreach($files as $k=>&$v)
			if(!isset(Eleanor::$services[$k]) or !$v)
				unset($files[$k]);

		if(!$path or $multi and !$file or !$multi and count(array_diff($services,array_keys($files)))>0)
			$errors[]='WRONG_PATH';
	}

	$groups=isset($_POST['groups']) ? (array)$_POST['groups'] : array();
	foreach($groups as $k=>&$v)
	{
		$v=(int)$v;
		if($v==0)
			unset($groups[$k]);
	}
	$sections=isset($_POST['sections']) ? (array)$_POST['sections'] : array();
	foreach($sections as $k=>&$v)
	{
		if(Eleanor::$vars['multilang'])
		{
			if(is_array($v))
				foreach($v as $lng=>&$value)
					if(isset(Eleanor::$langs[$lng]))
					{
						$value=explode(',',$value);
						foreach($value as $tmp=>&$tmpv)
						{
							$tmpv=trim($tmpv);
							if(!$tmpv)
								unset($value[$tmp]);
						}
						if(!$value)
							unset($v[$lng]);
					}
					else
						unset($v[$lng]);
			else
				$v=false;
		}
		elseif($v)
		{
			$v=explode(',',$v);
			foreach($v as $tmp=>&$tmpv)
			{
				$tmpv=trim($tmpv);
				if(!$tmpv)
					unset($v[$tmp]);
			}
			$v=array(''=>$v);
		}
		if(!$v)
			unset($sections[$k]);
	}

	$mc=$secex=array();
	foreach($services as &$service)
	{
		if(Eleanor::$vars['multilang'])
			foreach(Eleanor::$langs as $lng=>&$_)
			{
				$mc[$lng]=Modules::GetCache($service,$lng,true);
				$mc[$lng]=$mc[$lng]['ids'];
			}
		else
		{
			$mc['']=Modules::GetCache($service,false,true);
			$mc['']=$mc['']['ids'];
		}
		foreach($sections as &$section)
			foreach($section as $l=>&$vs)
				foreach($vs as &$v)
					if(isset($mc[$l][$v]) and (!$id or $id!=$mc[$l][$v]))
						$secex[]=$v;
	}
	if($secex)
		$errors[]=$lang['sec_exists'](array_unique($secex));

	if($errors)
		return AddEdit($id,$errors);

	$values=array(
		'sections'=>$sections ? serialize($sections) : '',
		'title_l'=>$title ? serialize($title) : '',
		'descr_l'=>$descr ? serialize($descr) : '',
		'image'=>isset($_POST['image']) ? (string)$_POST['image'] : '',
		'api'=>isset($_POST['api']) ? (string)$_POST['api'] : '',
	);
	if(preg_match('#\.(jpe?g|png|gif|bmp)$#i',$values['image'])==0)
		$values['image']='';
	if(!$prot)
	{
		natsort($services);
		$values+=array(
			'services'=>$services ? ','.implode(',,',$services).',' : '',
			'protected'=>isset($_POST['protected']),
			'path'=>$path,
			'multiservice'=>isset($_POST['multiservice']),
			'file'=>$file,
			'files'=>$files ? serialize($files) : '',
			'active'=>isset($_POST['active']),
		);
	}
	if($id)
		Eleanor::$Db->Update(P.'modules',$values,'`id`='.$id.' LIMIT 1');
	else
		Eleanor::$Db->Insert(P.'modules',$values);
	Eleanor::$Cache->Obsolete('mainpage');
	Eleanor::$Cache->Lib->DeleteByTag('modules');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}