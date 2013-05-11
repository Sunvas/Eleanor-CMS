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
$lang=Eleanor::$Language->Load('addons/admin/langs/themes-*.php','te');
Eleanor::$Template->queue[]='Themes';

global$Eleanor,$title;

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'info'=>false,
	'files'=>false,
	'config'=>false,
);

if(isset($_GET['info']))
{
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['info']);
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	if(!is_file($f))
		return GoAway();
	$info=(array)include$f;
	DoNavigation($theme,$info);

	$name=isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme;
	$license=isset($info['license']) ? Eleanor::FilterLangValues((array)$info['license']) : false;
	$info=isset($info['info']) ? Eleanor::FilterLangValues((array)$info['info']) : false;

	$title[]=$name;
	$c=Eleanor::$Template->Info($name,$info,$license);
	Start();
	echo$c;
}
elseif(isset($_GET['files']))
{
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['files']);
	if(!is_dir(Eleanor::$root.'templates/'.$theme.'/'))
		return GoAway();
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	$info=is_file($f) ? (array)include$f : array();
	DoNavigation($theme,$info);
	$name=isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme;
	$title[]=sprintf($lang['files_tpl'],$name);
	$Up=new Uploader(Eleanor::$root.'templates'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR);
	$Up->watermark=false;
	$Up->buttons_top=array(
		'create_file'=>true,
		'create_folder'=>true,
		'update'=>true,
	);
	$Up->buttons_item=array(
		'edit'=>true,
		'file_rename'=>true,
		'file_delete'=>true,
		'folder_rename'=>true,
		'folder_open'=>false,
		'folder_delete'=>true,
	);
	$c=Eleanor::$Template->Files($Up->Show('','tpl',$name),$name);
	Start();
	echo$c;
}
elseif(isset($_GET['config']))
{
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
	{
		$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['config']);
		$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
		if(!is_file($f))
			return GoAway();
		$info=include$f;
		if(!isset($info['options']) or !is_array($info['options']))
			return GoAway();
		$C=new Controls;
		$C->throw=false;
		try
		{
			$r=$C->SaveControls($info['options']);
		}
		catch(EE$E)
		{
			return ConfigTemplate($theme,array('ERROR'=>$E->getMessage()));
		}
		$errors=$C->errors;
		if(file_put_contents(Eleanor::$root.'templates/'.$theme.'.config.php','<?php return '.var_export($r,true).';')===false)
			$errors['SAVE']='Saving error';
		if($errors)
			return ConfigTemplate($theme,$errors);
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	else
		ConfigTemplate((string)$_GET['config']);
}
elseif(isset($_GET['settpl'],$_GET['to']))
{
	$name=(string)$_GET['to'];
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['settpl']);
	if(!is_dir(Eleanor::$root.'templates/'.$theme) or !isset(Eleanor::$services[$name]))
		return GoAway();
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	$info=is_file($f) ? (array)include$f : array();

	if(!empty($info['service']) and !in_array($name,$info['service']))
		return GoAway();
	DoNavigation($theme,$info);
	$nolic=empty($info['license']);
	if(Eleanor::$our_query and ($nolic or $_SERVER['REQUEST_METHOD']=='POST'))
	{
		if($nolic or isset($_POST['submit']))
		{
			Eleanor::$Db->Update(P.'services',array('theme'=>$theme),'`name`='.Eleanor::$Db->Escape($name).' LIMIT 1');
			Eleanor::$Cache->Lib->DeleteByTag('');
		}
		elseif(isset($_POST['refuse']))
		{
			foreach(Eleanor::$services as &$v)
				if($v['theme']==$theme)
					return GoAway(empty($_POST['back']) ? true : $_POST['back']);
			Files::Delete(Eleanor::$root.'templates/'.$theme);
		}
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	elseif(!$nolic)
	{
		$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
		$info=is_file($f) ? (array)include$f : array();
		$title[]=$lang['agreement'];
		if(isset($_GET['noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
		$c=Eleanor::$Template->License(isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme,$back,Eleanor::FilterLangValues((array)$info['license']));
		Start();
		echo$c;
	}
}
elseif(isset($_GET['delete']))
{
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['delete']);
	if(!is_dir(Eleanor::$root.'templates/'.$theme))
		return GoAway();

	foreach(Eleanor::$services as &$v)
		if($v['theme']==$theme)
			return GoAway();
	if(Eleanor::$our_query and isset($_POST['ok']))
	{
		Files::Delete(Eleanor::$root.'templates/'.$theme);
		Files::Delete(Eleanor::$root.'templates/'.$theme.'.config.php');
		Files::Delete(Eleanor::$root.'templates/'.$theme.'.init.php');
		Files::Delete(Eleanor::$root.'templates/'.$theme.'.settings.php');
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	else
	{
		$title[]=$lang['delc'];
		if(isset($_GET['noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
		$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
		$info=is_file($f) ? (array)include$f : array();
		DoNavigation($theme,$info);
		$c=Eleanor::$Template->Delete(sprintf($lang['deleting'],isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme),$back);
		Start();
		echo$c;
	}
}
else
{
	$title[]=Eleanor::$Language['te']['list'];
	$a=glob(Eleanor::$root.'templates/*',GLOB_ONLYDIR);
	$tpls=array();
	foreach($a as &$v)
	{
		$theme=basename($v);
		$info=is_file($v.'.settings.php') ? (array)include$v.'.settings.php' : array();

		$tpl=array(
			'image'=>is_file($v.'.png') ? 'templates/'.$theme.'.png' : false,
			'setto'=>array(),
			'used'=>array(),
		);
		if(!isset($info['service']) or !is_array($info['service']))
			$info['service']=array();
		foreach(Eleanor::$services as $k=>&$vs)
			if($vs['theme'] and $vs['theme']==$theme)
				$tpl['used'][]=$k;
			elseif(in_array($k,$info['service']))
				$tpl['setto'][$k]=$Eleanor->Url->Construct(array('settpl'=>$theme,'to'=>$k));
			else
				continue;

		$tpls[$theme]=$tpl+array(
			'creation'=>isset($info['creation']) ? $info['creation'] : false,
			'author'=>isset($info['author']) ? $info['author'] : false,
			'title'=>isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']) : false,
			'_aopts'=>isset($info['options']) ? $Eleanor->Url->Construct(array('config'=>$theme)) : false,
			'_ainfo'=>isset($info['info']) ? $Eleanor->Url->Construct(array('info'=>$theme)) : false,
			'_afiles'=>$Eleanor->Url->Construct(array('files'=>$theme)),
			'_adel'=>$tpl['used'] ? false : $Eleanor->Url->Construct(array('delete'=>$theme)),
		);
	}
	$c=Eleanor::$Template->TemplatesGeneral($tpls);
	Start();
	echo$c;
}

function DoNavigation($name,$info)
{global$Eleanor;
	$Eleanor->module['links']=array(
		'info'=>array(
			'link'=>$Eleanor->Url->Construct(array('info'=>$name)),
			'name'=>isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']) : $name,
		),
		'files'=>$Eleanor->Url->Construct(array('files'=>$name)),
		'config'=>isset($info['options']) ? $Eleanor->Url->Construct(array('config'=>$name)) : false,
	)+$Eleanor->module['links'];
}

function ConfigTemplate($theme,$errors=array())
{global$Eleanor,$title;
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',$theme);
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	if(!is_file($f))
		return GoAway();
	$info=(array)include$f;
	if(!isset($info['options']) or !is_array($info['options']))
		return GoAway();
	DoNavigation($theme,$info);
	$values=is_file(Eleanor::$root.'templates/'.$theme.'.config.php') ? (array)include Eleanor::$root.'templates/'.$theme.'.config.php' : array();

	foreach($values as &$v)
		$v=array('value'=>$v);
	if($errors)
	{
		if($errors===true)
			$error=array();
		foreach($info['options'] as &$v)
			if(is_array($v))
				$v['bypost']=true;
	}

	$title[]=sprintf(Eleanor::$Language['te']['config_tpl'],isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme);
	$values=$Eleanor->Controls->DisplayControls($info['options'],$values);
	$c=Eleanor::$Template->Config($info['options'],$values,$errors);
	Start();
	echo$c;
}