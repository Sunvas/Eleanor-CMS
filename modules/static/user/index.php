<?php
/*
	Copyright © Eleanor CMS, developed by Alexander Sunvas*, interface created by Rumin Sergey.
	For details, visit the web site http://eleanor-cms.ru, emails send to support@eleanor-cms.ru .
	*Pseudonym
*/
if(!defined('CMS'))die;
global$Eleanor,$title;
$Eleanor->module['config']=$mc=include$Eleanor->module['path'].'config.php';
$l=Eleanor::$Language->Load($Eleanor->module['path'].'user-*.php',$mc['n']);
Eleanor::$Template->queue[]=$mc['usertpl'];
Eleanor::LoadOptions($mc['opts'],false);

$Lst=Eleanor::LoadListTemplate('headfoot');
$u='?'.Url::Query(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']));
$GLOBALS['head']['rss']=$Lst->link(array(
	'rel'=>'alternate',
	'type'=>'application/rss+xml',
	'href'=>Eleanor::$services['rss']['file'].$u,
	'title'=>$l['rss'],
));

if($Eleanor->module['sections']['static']!=$Eleanor->module['name'])
	$Eleanor->Url->SetPrefix(array('lang'=>Language::$main==LANGUAGE ? false : Eleanor::$langs[Language::$main]['uri'],'module'=>Eleanor::$vars['prefix_free_module']==$Eleanor->module['id'] ? false : $Eleanor->module['sections']['static']));

if(!class_exists($mc['api'],false))
	include$Eleanor->module['path'].'api.php';
$Eleanor->Plug=new$mc['api']($mc);
if(!empty($Eleanor->module['general']))
{
	if(Eleanor::$vars[$mc['pv'].'general'])
		ShowGeneral();
	else
		Substance();
}
else
{
	$trace=array();
	if($Eleanor->Url->is_static)
	{
		$Eleanor->Url->GetEnding(array($Eleanor->Url->ending,$Eleanor->Url->delimiter),true);
		$_GET+=$Eleanor->Url->Parse();
		if(isset($_GET['']))
			$trace=(array)$_GET[''];
	}
	elseif(isset($_GET['uri']))
		$trace=array((string)$_GET['uri']);
	$id=isset($_GET['id']) ? (int)$_GET['id'] : false;
	if($trace or $id)
	{
		$data=array();
		$uid=Eleanor::$Login->GetUserValue('id');
		if($id>0)
		{
			$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$mc['t'].'` WHERE `status`=1 AND `id`='.$id.' LIMIT 1');
			if(!list($parents)=$R->fetch_row())
				return ExitPage();
			$parents.=$id.',';
		}
		if($trace)
		{
			$a=false;
			if(!$id)
			{
				$f=preg_replace('#([^a-z0-9'.constant(Language::$main.'::ALPHABET').'\.\-_/]|\.\.)+#i','',join('/',$trace));#Обезопасим от возможного выхода из каталога и проверку других файлов.
				$a=glob($Eleanor->module['path'].'DIRECT/'.$f.'.php',GLOB_BRACE);
			}
			if($a)
			{
				$data=GetLocalFile($a[0],$uid);
				if(Eleanor::$caching)
				{
					Eleanor::$last_mod=$data['last_mod'];
					$etag=Eleanor::$etag;
					Eleanor::$etag=$data['etag'];
					if(Eleanor::$modified and $data['last_mod'] and $data['last_mod']<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
						return Start();
					Eleanor::$modified=false;
				}
				$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct(array('uri'=>$f));
				$id=$f;
			}
			else
			{
				$uri=$parents='';
				$requrl=reset($trace);
				$R=Eleanor::$Db->Query('SELECT `id`,`parents`,`uri` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `uri`'.Eleanor::$Db->In($trace).' AND `status`=1 ORDER BY `parents` ASC');
				while($a=$R->fetch_assoc())
					if($parents==$a['parents'])
					{
						$id=(int)$a['id'];
						if(mb_strtolower($requrl)==mb_strtolower($a['uri']))
							$requrl=true;
						$uri=$a['uri'];
						$parents.=$a['id'].',';
					}
				if(mb_strtolower(end($trace))!=mb_strtolower($uri) or $requrl!==true)
					return ExitPage();
			}
		}
		if(!$id and !$data)
			return ExitPage();
		if($id and !$data)
		{
			$R=Eleanor::$Db->Query('SELECT `title`,`text`,`parents`,`meta_title`,`meta_descr`,`last_mod` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `status`=1 AND `id`='.$id.' LIMIT 1');
			if(!$data=$R->fetch_assoc())
				return ExitPage();
			if(Eleanor::$caching)
			{
				Eleanor::$last_mod=strtotime($data['last_mod']);
				$etag=Eleanor::$etag;
				Eleanor::$etag=md5($uid.'-'.$mc['n'].$id);
				if(Eleanor::$modified and Eleanor::$last_mod and Eleanor::$last_mod<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
					return Start();
				Eleanor::$modified=false;
			}

			$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($Eleanor->Url->furl ? $Eleanor->Plug->GetUri($id) : array('id'=>$id));
			OwnBB::$opts['alt']=$data['title'];
			$data['text']=OwnBB::Parse($data['text']);
			if(!$pr=$Eleanor->Url->Prefix())
				$pr=$Eleanor->Url->Construct(array('module'=>$Eleanor->module['name']),true,false);
			$data['navi'][]=array($l['substance'],$pr);
			if($data['parents'])
			{
				$in=explode(',',$data['parents']);
				$tmp=array();
				$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `status`=1 AND `id`'.Eleanor::$Db->In($in));
				while($a=$R->fetch_assoc())
					$tmp[$a['id']]=$a['title'];
				foreach($in as &$v)
					if(isset($tmp[$v]))
						$data['navi'][]=array($tmp[$v],$Eleanor->Url->Construct($Eleanor->Url->furl ? $Eleanor->Plug->GetUri($v) : array('id'=>$v)));
			}
			$data['navi'][]=array($data['title'],false);

			$data['seealso']=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `status`=1 AND `parents`=\''.$parents.'\' ORDER BY `pos` ASC');
			while($a=$R->fetch_assoc())
				$data['seealso'][]=array($a['title'],$Eleanor->Url->Construct($Eleanor->Url->furl ? $Eleanor->Plug->GetUri($a['id']) : array('id'=>$a['id'])));
		}
		if($data['meta_title'])
			$title=$data['meta_title'];
		else
			$title[]=$data['title'];
		$Eleanor->module['description']=$data['meta_descr'] ? $data['meta_descr'] : Strings::CutStr(strip_tags(str_replace("\n",' ',$data['text'])),250);

		#Поддержка соцсетей:
		$Lst=Eleanor::LoadListTemplate('headfoot')
			->og('title',$data['title'])
			->og('uri',$Eleanor->origurl)
			->og('locale',Eleanor::$langs[Language::$main]['d'])
			->og('site_name',Eleanor::$vars['site_name'])
			->og('description',$Eleanor->module['description']);
		if(preg_match('#<img.+?src="([^"]+)"[^>]*>#',$data['text'],$m)>0)
			$Lst->og('image',strpos($m[1],'://')===false ? PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$m[1] : $m[1]);
		$GLOBALS['head']['og']=(string)$Lst;

		$s=Eleanor::$Template->StaticShow($id,$data);
		Start();
		echo$s;
	}
	else
		Substance();
}

function ShowGeneral()
{global$Eleanor;
	$mc=$Eleanor->module['config'];
	if(!Eleanor::$vars[$mc['pv'].'general'])
		return Substance();
	$ids=explode(',',Eleanor::$vars[$mc['pv'].'general']);
	$res=$temp=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`title`,`text` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id`'.Eleanor::$Db->In($ids).' AND `status`=1 AND `language`IN(\'\',\''.Language::$main.'\')');
	while($a=$R->fetch_assoc())
	{
		$a['text']=OwnBB::Parse($a['text']);
		$temp[$a['id']]=$a;
	}
	foreach($ids as &$v)
		if(isset($temp[$v]))
			$res[]=$temp[$v];
	unset($temp,$ids);
	$s=Eleanor::$Template->StaticGeneral($res);
	$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Prefix(!$Eleanor->Url->furl);
	Start();
	echo$s;
}

function Substance()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language[$Eleanor->module['config']['n']]['substance'];
	$ol=$Eleanor->Plug->GetOrderedList();
	foreach($ol as $k=>&$v)
		$v['_a']=$Eleanor->Url->Construct($Eleanor->Url->furl ? $Eleanor->Plug->GetUri($k) : array('id'=>$k));
	$s=Eleanor::$Template->StaticSubstance($ol);
	$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Prefix(!$Eleanor->Url->furl);
	Start();
	echo$s;
}

function GetLocalFile($f,$uid)
{global$Eleanor;
	ob_start();
	$data=include$f;
	$text=ob_get_contents();
	ob_end_clean();
	if(!is_array($data))
		$data=array();
	$data+=array(
		'text'=>$text,
		'title'=>'',
		'navi'=>array(),
		'seealso'=>array(),
		'last_mod'=>filemtime($f),
		'etag'=>md5($uid.'-'.$Eleanor->module['config']['n'].$f),
		'meta_title'=>false,
		'meta_descr'=>false,
	);
	return$data;
}