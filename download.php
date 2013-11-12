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
require __dir__.'/core/core.php';
$Eleanor=Eleanor::getInstance();
Eleanor::LoadOptions(array('site','users-on-site','files'));
Eleanor::$service='download';#ID сервиса
Eleanor::InitService();
$Eleanor->started=false;

if(Eleanor::$vars['site_closed'] and !Eleanor::$Permissions->ShowClosedSite() and !Eleanor::LoadLogin(Eleanor::$services['admin']['login'])->IsUser())
	return ExitPage();

ApplyLang();

if(Eleanor::$Permissions->IsBanned())
	throw new EE(Eleanor::$Login->GetUserValue('ban_explain'),EE::USER,array('ban'=>'group'));

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
elseif(isset($_GET['captcha']))
	$Eleanor->Captcha->GetImage(isset($_GET['imageid']) ? (string)$_GET['imageid'] : 0,(string)$_GET['captcha']);
elseif(isset($_GET['download']))
{
	$f=Url::Decode($_GET['download']);
	$f=Eleanor::FormatPath(Eleanor::$uploads.DIRECTORY_SEPARATOR.$f);

	if(Eleanor::$vars['download_antileech'] and isset($_SERVER['HTTP_REFERER']))
	{
		$ref_host=substr($_SERVER['HTTP_REFERER'],strpos($_SERVER['HTTP_REFERER'],'//')+2);
		$ref_host=substr($ref_host,0,strpos($ref_host,'/'));
		$ref_host=preg_replace('#^www\.#i','',$ref_host);
		$check[]=preg_replace('#^www\.#i','',Eleanor::$domain);
		$check[]=preg_replace('#^www\.#i','',Eleanor::$punycode);
		$check[]=Eleanor::$vars['site_domain'];
		if(!in_array($ref_host,$check))
			return ExitPage();
	}
	if(Eleanor::$vars['download_no_session'])
	{
		$sip=Eleanor::$Db->Escape(Eleanor::$ip);
		$R=Eleanor::$Db->Query('SELECT `enter` FROM `'.P.'sessions` WHERE `expire`>\''.date('Y-m-d H:i:s').'\' AND (`ip_guest`='.$sip.' OR `ip_user`='.$sip.') LIMIT 1');
		if($R->num_rows==0)
			return ExitPage();
	}

	$direct=preg_match('#\.(gif|png|jpe?g|bmp)$#i',$f)>0;
	$f=Files::Windows($f);
	if(!is_file($f))
		return ExitPage();
	if(Eleanor::$caching)
	{
		Eleanor::$last_mod=filemtime($f);
		$etag=Eleanor::$etag;
		Eleanor::$etag=md5($f.filesize($f));
		if(Eleanor::$modified and Eleanor::$last_mod and Eleanor::$last_mod<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
			return Eleanor::HookOutPut();
		Eleanor::$modified=false;
	}
	else
		Eleanor::$etag=false;
	if(!$direct)
		Eleanor::AddSession();
	Eleanor::$gzip=false;#Сжимать файлы - не прерогатива системы.
	Eleanor::HookOutPut(false,200,false);
	Files::OutPutStream(array('file'=>$f,'save'=>!$direct,'etag'=>Eleanor::$etag));
}
elseif(isset($_REQUEST['direct']) and is_string($_REQUEST['direct']) and is_file($f=Eleanor::$root.'addons/direct/'.preg_replace('#[^a-z0-9]+#i','',$_REQUEST['direct']).'.php'))
	include $f;
else
	return ExitPage();

#Предопределенные функции.
/**
 * Перенаправление на другую страницу
 * @param mixed $info true - на префикс модуля, false - на предыдущую страницу, string - на адрес, array - компиляция адреса
 * @param int $code Код редиректа 301 или 302
 * @param string $hash Хеш редиректа
 */
function GoAway($info=false,$code=301,$hash='')
{global$Eleanor;
	$ref=getenv('HTTP_REFERER');
	$current=PROTOCOL.Eleanor::$punycode.$_SERVER['REQUEST_URI'];
	if(!$ref or $ref==$current or $info)
	{
		if(is_bool($info))
			$info=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.($info ? $Eleanor->Url->Prefix() : '');
		elseif(is_array($info))
			$info=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($info);
		else
		{
			$d=parse_url($info);
			if(isset($d['host'],$d['scheme']))
			{
				if(preg_match('#^[a-z0-9\-\.]+$#',$d['host'])==0)
					$info=preg_replace('#^'.$d['scheme'].'://'.preg_quote($d['host']).'#',$d['scheme'].'://'.Punycode::Domain($d['host']),$info);
			}
			elseif(strpos($info,'/')!==0)
				$info=Eleanor::$site_path.$info;
		}

		if($info==$current)
			return ExitPage(404);
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
	Eleanor::HookOutPut();
	$Eleanor->started=true;
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
		Eleanor::HookOutPut(false,isset($extra['httpcode']) ? (int)$extra['httpcode'] : 503,$e);
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
	GoAway($Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>'errors','code'=>$code),false,''),$r);
}

function ApplyLang($gl=false)
{
	if(Eleanor::$vars['multilang'])
	{
		if(!Eleanor::$Login->IsUser() and ($gl or $gl=Eleanor::GetCookie('lang')) and isset(Eleanor::$langs[$gl]) and $gl!=LANGUAGE)
		{
			Language::$main=$gl;
			Eleanor::$Language->Change($gl);
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

	$queue=isset(Eleanor::$Template->queue) ? Eleanor::$Template->queue : array();
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
		{
			$pref=isset($Eleanor->module['id']) && $Eleanor->module['id']==Eleanor::$vars['prefix_free_module'] ? array() : array('module'=>$Eleanor->module['name']);
			$Eleanor->Url->SetPrefix(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'])+$pref : $pref);
		}

		$theme=Eleanor::$Login->IsUser() ? Eleanor::$Login->GetUserValue('theme') : Eleanor::GetCookie('theme');
		if(!Eleanor::$vars['templates'] or !in_array($theme,Eleanor::$vars['templates']))
			$theme=false;
		Eleanor::InitTemplate($theme ? $theme : Eleanor::$services['user']['theme']);
	}
	else
		Eleanor::InitTemplate(Eleanor::$services[$n]['theme']);
	Eleanor::$Template->queue+=$queue;
}