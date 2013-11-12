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
Eleanor::LoadOptions(array('site','users-on-site'));
Eleanor::$service='ajax';#ID сервиса
Eleanor::InitService();
Eleanor::$Language->queue['main'][]='langs/ajax-*.php';

#Три предустановленные переменные
$head=$jscripts=array();

#Исправляем кодировку
if(CHARSET!='utf-8' and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')
{
	$F=function(&$v){ $v=mb_convert_encoding($v,CHARSET,'utf-8'); };
	array_walk_recursive($_POST,$F);
	array_walk_recursive($_GET,$F);
}

ApplyLang();

#Заплатка. В новой версии все исправить.
Eleanor::$Template=new Template_Mixed;

if(Eleanor::$vars['site_closed'] and !Eleanor::LoadLogin(Eleanor::$services['admin']['login'])->IsUser())
	return Error(Eleanor::$Language['main']['site_closed']);

if(Eleanor::$Permissions->IsBanned())
	throw new EE(Eleanor::$Login->GetUserValue('ban_explain'),EE::USER,array('ban'=>'group'));

#$_REQUEST нельзя использовать, потому что у $_REQUEST нельзя исправить кодировку (см. выше)
$m=isset($_POST['module']) ? (string)$_POST['module'] : (isset($_GET['module']) ? (string)$_GET['module'] : false);
if($m)
{
	$Eleanor->modules=Modules::GetCache();
	if(!isset($Eleanor->modules['ids'][$m]))
		return Error();
	$R=Eleanor::$Db->Query('SELECT `id`,`services`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files`,`image` FROM `'.P.'modules` WHERE `id`='.(int)$Eleanor->modules['ids'][$m].' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return Error();

	if(!$a['multiservice'])
	{
		$files=unserialize($a['files']);
		$a['file']=isset($files[Eleanor::$service]) ? $files[Eleanor::$service] : false;
	}
	if(!$a['file'])
		return Error();
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
elseif(isset($_REQUEST['direct']) and is_file($f=Eleanor::$root.'addons/direct/'.preg_replace('#[^a-z0-9]+#i','',(string)$_REQUEST['direct']).'.php'))
	include$f;
else
	SomeAjax();

#Предопределенные функции.
function Start($code=200)
{static$one=true;
	if($one)
		Eleanor::HookOutPut('',$code);
	$one=false;
}

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

function Error($e='',$extra=array())
{global$Eleanor;
	if(isset($Eleanor))
	{
		$le=Eleanor::$Language['errors'];
		if(!$e)
			$e=$le['happened'];
		elseif(is_string($e) and isset($le[$e]))
			$e=$le[$e];
		Start(isset($extra['httpcode']) ? $extra['httpcode'] : 200);
	}
	echo Eleanor::JsVars(array('error'=>$e),false,true);
}

function Result($d,$alr=false)
{global$jscripts,$head;
	Eleanor::$content_type='application/json';
	Start();
	$jscripts=array_unique($jscripts);
	foreach($jscripts as &$v)
		$v=addcslashes($v,"\n\r\t\"\\");
	echo Eleanor::JsVars(array(
		$alr ? '!data' : 'data'=>$d,
		'!scripts'=>$jscripts ? '["'.join('","',$jscripts).'"]' : '[]',
		'head'=>$head,
	),false,true);
}

#Какие-то странные участки AJAX, которые нельзя привязать к какому-нить модулю. Как предпросмотр в редакторе, например.
function SomeAjax()
{global$Eleanor;
	$type=isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
	try
	{
		switch($type)
		{
			case'preview':
				if(isset($_POST['service']))
					BeAs($_POST['service']);
				$Eleanor->Editor_result->type='bb';
				$Eleanor->Editor_result->ownbb=isset($_POST['ownbb']);
				$Eleanor->Editor_result->smiles=isset($_POST['smiles']);
				Result($Eleanor->Editor_result->GetHtml(isset($_POST['text']) ? (string)$_POST['text'] : '',true,false));
			break;
			case'uploader':
				if(isset($_POST['service']))
					BeAs((string)$_POST['service']);
				$Eleanor->Uploader_Ajax->Process();
			break;
			case'controls':
				$data=array('session'=>isset($_POST['session']) ? (string)$_POST['session'] : '');
				if(isset($_POST['newtype']))
					$data['type']=(string)$_POST['newtype'];
				if(isset($_POST['options']))
					$data['options']=(array)$_POST['options'];
				if(isset($_POST['service']))
					BeAs((string)$_POST['service']);
				Result($Eleanor->Controls_Manager->ConfigureControl($data,true,!empty($_POST['onlyprev'])));
			break;
			case'uploadimage':
				include Eleanor::$root.'core/others/controls.php';
				include Eleanor::$root.'core/controls/uploadimage.php';
				ControlUploadImage::DoAjax();
			break;
			case'uploader':
				if(isset($_POST['service']))
					BeAs((string)$_POST['service']);
			break;
			default:
				Error(Eleanor::$Language['main']['unknown_event']);
		}
	}
	catch(EE$E)
	{
		Error($E->getMessage());
	}
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