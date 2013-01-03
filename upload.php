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
define('CMS',true);
require dirname(__file__).'/core/core.php';
$Eleanor=Eleanor::getInstance();
Eleanor::$service='upload';#ID сервиса
Eleanor::InitService();
Eleanor::$Language->queue['main'][]='langs/main-*.php';
Eleanor::LoadOptions('site');
ApplyLang();

if(Eleanor::$Permissions->IsBanned())
	throw new EE(Eleanor::$Login->GetUserValue('ban_explain'),EE::USER,array('ban'=>'group'));

#Флеш любит все переводить в UTF-8
if(isset($_REQUEST['flashdecode']) and CHARSET!='utf-8')
{
	$d=is_array($_REQUEST['flashdecode']) ? $_REQUEST['flashdecode'] : explode(',',(string)$_REQUEST['flashdecode']);
	foreach($d as &$v)
		if(isset($_REQUEST[$v]) and is_string($_REQUEST[$v]))
			$_REQUEST[$v]=mb_convert_encoding($_REQUEST[$v],CHARSET,'utf-8');
}

$Eleanor->started=false;

$m=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;
if($m)
{
	$Eleanor->modules=Modules::GetCache();
	if(!isset($Eleanor->modules['ids'][$m]))
		return ExitPage();
	$R=Eleanor::$Db->Query('SELECT `id`,`services`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files`,`image` FROM `'.P.'modules` WHERE `id`='.(int)$Eleanor->modules['ids'][$m].' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return ExitPage(404);
	if(!$a['multiservice'])
	{
		$files=unserialize($a['files']);
		$a['file']=isset($files[Eleanor::$service]) ? $files[Eleanor::$service] : false;
	}
	if(!$a['file'])
		return ExitPage();
	$a['sections']=unserialize($a['sections']);
	foreach($a['sections'] as $k=>&$v)
		if(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
			$v=reset($v[Language::$main]);
		else
			$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
	$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues(unserialize($a['title_l'])) : '';
	$Eleanor->module=array(
		'name'=>$m,
		'section'=>isset($Eleanor->modules['sections'][$m]) ? $Eleanor->modules['sections'][$m] : '',
		'title'=>$a['title_l'],
		'image'=>$a['image'],
		'path'=>Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR,
		'id'=>$a['id'],
		'sections'=>$a['sections'],
	);
	Modules::Load($Eleanor->module['path'],$a['multiservice'],$a['file'] ? $a['file'] : 'index.php');
}
elseif(isset($_POST['direct']) and is_file($f=Eleanor::$root.'addons/direct/'.preg_replace('#[^a-z0-9]+#i','',(string)$_POST['direct']).'.php'))
	include$f;
else
	SomeUpload();

#Предопределенные функции.
function GoAway($info=false,$code=301,$hash='')
{global$Eleanor;
	if(!$ref=getenv('HTTP_REFERER') or $ref==PROTOCOL.Eleanor::$punycode.$_SERVER['REQUEST_URI'] or $info)
	{
		if(is_bool($info))
			$info=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.($info ? $Eleanor->Url->Prefix() : '');
		elseif(is_array($info))
			$info=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($info);
		elseif($d=parse_url($info) and isset($d['host'],$d['scheme']) and preg_match('#^[a-z0-9\-\.]+$#',$d['host'])==0)
			$info=preg_replace('#^'.$d['scheme'].'://'.preg_quote($d['host']).'#',$d['scheme'].'://'.Punycode::Domain($d['host']),$info);
		$ref=$info;
	}
	if($hash)
		$ref=preg_replace('%#.*$%','',$ref).'#'.$hash;
	header('Cache-Control: no-store');
	header('Location: '.rtrim(html_entity_decode($ref),'&?'),true,$code);
	die;
}

function Start()
{global$Eleanor;
	if($Eleanor->started)
		return;
	Eleanor::$content_type='text/plain';
	Eleanor::LoadOptions('users-on-site');
	Eleanor::AddSession();
	Eleanor::HookOutPut();
	$Eleanor->started=true;
}

function Error($e='',$toutf=true)
{global$Eleanor;
	if($toutf)
	{
		Eleanor::$charset='utf-8';
		if(CHARSET!='utf-8')
			$e=mb_convert_encoding($e,'utf-8',CHARSET);
	}

	if(isset($Eleanor))
	{		$le=Eleanor::$Language['errors'];
		if(!$e)
			$e=$le['happened'];
		elseif(isset($le[$e]))
			$e=$le[$e];
		Start();
	}
	echo Eleanor::JsVars(array('error'=>$e),false,true);
}

function Result($data,$toutf=true)
{
	$s='';
	if(is_array($data))
	{
		Eleanor::$content_type='application/json';
		if($toutf and CHARSET!='utf-8')
			array_walk_recursive($data,create_function('&$v','$v=mb_convert_encoding($v,\'utf-8\',CHARSET);'));
		$s=Eleanor::JsVars($data,false,true);
	}
	elseif($toutf)
		$s=mb_convert_encoding($s,'utf-8',CHARSET);
	else
		$s=$data;
	if($toutf)
		Eleanor::$charset='utf-8';
	Start();
	die($s);
}

function ExitPage($code=403,$r=301)
{global$Eleanor;
	BeAs('user');
	$Eleanor->Url->file=Eleanor::$services['user']['file'];
	GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>'errors','code'=>$code),false,true,Eleanor::$vars['furl']),$r);
}

function SomeUpload()
{global$Eleanor;
	$type=isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
	try
	{
		switch($type)
		{
			case'uploader':
				if(!isset($_FILES[Uploader::FILENAME]) or !is_uploaded_file($_FILES[Uploader::FILENAME]['tmp_name']) or !$Eleanor->Uploader_upload->Process())
					ExitPage(403);
			break;
			case'uploadimage':
				include Eleanor::$root.'core/others/controls.php';
				include Eleanor::$root.'core/controls/uploadimage.php';
				ControlUploadImage::DoUpload();
			break;
			default:
				ExitPage();
		}
	}
	catch(EE$E)
	{
		ExitPage();
	}
}

function ApplyLang($gl=false)
{
	if(Eleanor::$vars['multilang'])
	{
		if(!Eleanor::$Login->IsUser() and ($gl or $gl=Eleanor::GetCookie('lang')) and isset(Eleanor::$langs[$gl]) and $gl!=LANGUAGE)
		{
			Language::$main=$gl;
			Eleanor::$Language->Change($l);
		}
		foreach(Eleanor::$lvars as $k=>&$v)
			Eleanor::$vars[$k]=Eleanor::FilterLangValues($v);
	}
	else
		Eleanor::$lvars=array();
}

#Функция "Будь как", делает сервис другим. Полностью :)
function BeAs($n)
{global$Eleanor;
	if(Eleanor::$service==$n or !isset(Eleanor::$services[$n]))
		return;

	Eleanor::$filename=Eleanor::$services[$n]['file'];
	Eleanor::$Language->queue['main'][]='langs/'.$n.'-*.php';

	if(Eleanor::$services[$n]['login']!=Eleanor::$services[Eleanor::$service]['login'])
		Eleanor::ApplyLogin(Eleanor::$services[$n]['login']);

	Eleanor::$service=$n;
	ApplyLang();

	if($n=='user')
	{
		$Eleanor->Url->furl=Eleanor::$vars['furl'];
		$Eleanor->Url->delimiter=Eleanor::$vars['url_static_delimiter'];
		$Eleanor->Url->defis=Eleanor::$vars['url_static_defis'];
		$Eleanor->Url->ending=Eleanor::$vars['url_static_ending'];

		$Eleanor->Url->special=$Eleanor->Url->furl ? '' : Eleanor::$filename.'?';
		if(Language::$main!=LANGUAGE)
			$Eleanor->Url->special.=$Eleanor->Url->Construct(array('lang'=>Eleanor::$langs[Language::$main]['uri']),false,false);
		if(isset($Eleanor->module,$Eleanor->module['name']))
			$Eleanor->Url->SetPrefix(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']));

		$theme=Eleanor::$Login->IsUser() ? Eleanor::$Login->GetUserValue('theme') : Eleanor::GetCookie('theme');
		if(!Eleanor::$vars['templates'] or !in_array($theme,Eleanor::$vars['templates']))
			$theme=false;
		Eleanor::InitTemplate($theme ? $theme : Eleanor::$services['user']['theme']);
	}
	else
		Eleanor::InitTemplate(Eleanor::$services[$n]['theme']);
}