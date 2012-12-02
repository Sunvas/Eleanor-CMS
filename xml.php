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
Eleanor::LoadOptions('site');
Eleanor::$service='xml';#ID сервиса
Eleanor::LoadService();

if(Eleanor::$vars['site_closed'] and !Eleanor::$Permissions->ShowClosedSite() and !Eleanor::LoadLogin(Eleanor::$services['admin']['login'])->IsUser())
	return ExitPage();

ApplyLang();

if(Eleanor::$Permissions->IsBanned())
	throw new EE(Eleanor::$Login->GetUserValue('ban_explain'),EE::USER,array('ban'=>'group'));

Eleanor::InitTemplate(Eleanor::$services[Eleanor::$service]['theme']);
$Eleanor->error=$Eleanor->started=false;

$m=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;
if($m)
{
	$Eleanor->modules=Modules::GetCache();
	if(!isset($Eleanor->modules['ids'][$m]))
		return ExitPage(404);
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
elseif(isset($_REQUEST['direct']) and is_string($_REQUEST['direct']) and is_file($f=Eleanor::$root.'addons/direct/'.preg_replace('#[^a-z0-9]+#i','',$_REQUEST['direct']).'.php'))
	include $f;
else
	return ExitPage();

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
	Eleanor::AddSession();
	Eleanor::$content_type='text/xml';
	Eleanor::HookOutPut('Finish');
	$Eleanor->started=true;
}

function Finish($s)
{global$Eleanor;
	return$Eleanor->started && !$Eleanor->error ? '<?xml version="1.0" encoding="'.CHARSET.'"?>'.$s : $s;
}

function Error($e=false,$extra=array())
{global$Eleanor;
	$csh=!headers_sent();
	$le=Eleanor::$Language['errors'];
	if(empty($extra['ban']))
	{
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/error.html',
			array(
				'title'=>$le['happened'],
				'error'=>$e,
				'extra'=>$extra,
			)
		);
		if($csh)
			header('Retry-After: 7200');
	}
	else
	{
		if(isset($extra['banned_until']))
			$e=$le['banlock']($extra['banned_until'],$e);
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/ban.html',
			array(
				'title'=>$le['you_are_banned'],
				'message'=>$e ? OwnBB::Parse($e) : Eleanor::$vars['blocked_message'],
				'extra'=>$extra,
			)
		);
	}

	if(isset($Eleanor,$Eleanor->started) and $Eleanor->started)#Ошибка могла вылететь и в момент создания объекта $Eleanor
	{
		$Eleanor->error=true;
		if($csh)
			header('Content-Type: text/html; charset='.Eleanor::$charset,true,isset($extra['httpcode']) ? (int)$extra['httpcode'] : 503);
		while(ob_get_contents()!==false)
			ob_end_clean();
		ob_start();ob_start();#Странный глюк PHP... Достаточно сделать Parse error в index.php темы и Core::FinishOutPut будет получать пустое значение
		echo$e;
	}
	else
	{
		Eleanor::$content_type='text/html';
		Eleanor::HookOutPut(false,isset($adoon['httpcode']) ? (int)$extra['httpcode'] : 503,$e);
		die;
	}
}

function Result($s)
{
	Start();
	die($s);
}

function ExitPage($code=403,$r=301)
{global$Eleanor;
	BeAs('user');
	$Eleanor->Url->file=Eleanor::$services['user']['file'];
	GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>'errors','code'=>$code),false,true,Eleanor::$vars['furl']),$r);
}

function ApplyLang($gl=false)
{
	if(Eleanor::$vars['multilang'])
	{
		if(!Eleanor::$Login->IsUser() and ($gl or $gl=Eleanor::GetCookie('lang')) and isset(Eleanor::$langs[$gl]) and $gl!=LANGUAGE)
		{
			Language::$main=$l;
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

	Eleanor::$service=$n;
	Eleanor::$Language->queue['main'][]='langs/'.$n.'-*.php';
	if(Eleanor::$services[$n]['login']!=Eleanor::$services[Eleanor::$service]['login'])
		Eleanor::ApplyLogin(Eleanor::$services[$n]['login']);
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