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
$start=microtime();
define('CMS',true);

require dirname(__file__).'/core/core.php';
$Eleanor=Eleanor::getInstance();
Eleanor::$service='admin';#ID сервиса
Eleanor::LoadOptions(array('site','users-on-site'));
Eleanor::LoadService();
Eleanor::$Language->queue['main']='langs/admin-*.php';

if(Eleanor::$vars['multilang'])
{
	if(isset($_GET['language']) and is_string($_GET['language']) and isset(Eleanor::$langs[$_GET['language']]))
	{
		if(Eleanor::$Login->IsUser())
			UserManager::Update(array('language'=>$_GET['language']));
		else
			Eleanor::SetCookie(Eleanor::$service.'_lang',$_GET['language']);
		return GoAway(html_entity_decode(LangNewUrl(getenv('HTTP_REFERER'),$_GET['language'])));
	}
	if(!Eleanor::$Login->IsUser() and $l=Eleanor::GetCookie(Eleanor::$service.'_lang') and isset(Eleanor::$langs[$l]) and $l!=LANGUAGE)
	{
		Language::$main=$l;
		Eleanor::$Language->Change($l);
	}


	foreach(Eleanor::$lvars as $k=>&$v)
		Eleanor::$vars[$k]=Eleanor::FilterLangValues($v);
}
else
	Eleanor::$lvars=array();

#Три предустановленные переменные
$title=$head=$jscripts=array();
$Eleanor->started=$Eleanor->error=false;
Eleanor::InitTemplate(Eleanor::$services[Eleanor::$service]['theme']);

if(Eleanor::$Login->IsUser())
{	$section=isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
	if(!$section and isset($_GET['logout']))
	{
		Eleanor::$Login->LogOut();
		GoAway(Eleanor::$filename);
	}
	$l=Eleanor::$Language['main'];
	$title[]=$l['adminka'];
	switch($section)
	{
		case'options':
			$Eleanor->module=array(
				'stitle'=>$l['options'],
				'name'=>'options',
				'image'=>'setting-*.png',
			);
			$Eleanor->Url->SetPrefix(array('section'=>'options'));
			$title[]=$l['options'];
			Modules::Load(Eleanor::$root.'addons/admin/modules/',false,'section_options.php');
		break;
		case'management':
			$Eleanor->module=array(
				'stitle'=>$l['management'],
				'name'=>'management',
			);
			$Eleanor->Url->SetPrefix(array('section'=>'management'));
			$title[]=$l['management'];
			Modules::Load(Eleanor::$root.'addons/admin/modules/',false,'section_management.php');
		break;
		case'modules':
			$Eleanor->module=array(
				'stitle'=>$l['modules'],
				'name'=>'modules',
				'image'=>'modules-*.png',
			);
			$Eleanor->Url->SetPrefix(array('section'=>'modules'));
			$title[]=$l['modules'];
			Modules::Load(Eleanor::$root.'addons/admin/modules/',false,'section_modules.php');
		break;
		default:
			$Eleanor->module=array(
				'stitle'=>$l['main page'],
				'name'=>'general',
				'general'=>true,
			);
			$Eleanor->Url->SetPrefix(array('section'=>'general'));
			$title[]=$l['main page'];
			Modules::Load(Eleanor::$root.'addons/admin/modules/',false,'section_general.php');
	}
}
else
{	$l=Eleanor::$Language->Load('langs/admin_enter-*.php','enter');
	$login=isset($_POST['login']['name']) ? $_POST['login']['name'] : '';
	$password=isset($_POST['login']['password']) ? $_POST['login']['password'] : '';
	$error='';
	$captcha=Eleanor::$vars['antibrute']==2 && (isset($_POST['check']) || $ct=Eleanor::GetCookie('Captcha_'.get_class(Eleanor::$Login)) && $ct>time());

	if($captcha)
	{		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$Eleanor->Captcha->disabled=false;
			if(isset($_POST['check']))
			{
				$cach=$Eleanor->Captcha->Check((string)$_POST['check']);
				$Eleanor->Captcha->Destroy();
				if(!$cach)
					$error=$l['error_captcha'];
			}
			else
				$error=$l['CAPTCHA'];
		}
		$Eleanor->Captcha->disabled=false;
		$Eleanor->Captcha->Destroy();
		$captcha=$Eleanor->Captcha->GetCode();
	}

	if(!$error and isset($_POST['login']))
		try
		{
			Eleanor::$Login->Login((array)$_POST['login'],array('captcha'=>$captcha));
			return GoAway(Eleanor::$filename.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
		}
		catch(EE$E)
		{
			switch($error=$E->getMessage())
			{
				case'TEMPORARILY_BLOCKED':
					$error=sprintf($l['TEMPORARILY_BLOCKED'],$login,round($E->extra['remain']/60));
				break;
				case'CAPTCHA':
					$captcha=true;
					$error=$l['CAPTCHA'];
				break;
				case'WRONG_PASSWORD':
					$password='';
				default:
					if(isset($l[$error]))
						$error=$l[$error];
			}
		}
	$title=$l['enter_to'];
	Eleanor::$Template->default+=array('error'=>$error,'login'=>$login,'password'=>$password,'captcha'=>$captcha);
	Start('Enter');
}

#Предопределенные функции.
function Start($tpl='index',$code=200)
{global$Eleanor,$jscripts,$head,$title,$tcover,$thead;
	if($Eleanor->started)
		return;
	if($code==200)
		Eleanor::AddSession();
	Eleanor::HookOutPut('Finish',$code);
	$Eleanor->started=true;
	if(!$tpl)
		return$tcover='';

	$ms=include Eleanor::$root.'addons/config_multisite.php';
	if($ms)
	{
		$hms=array(
			'msisuser'=>Eleanor::$Login->IsUser(),
			'msservice'=>Eleanor::$service,
		);
		foreach($ms as $sn=>&$sd)
			$hms['mssites'][$sn]=array(
				'address'=>$sd['address'],
				'title'=>Eleanor::FilterLangValues($sd['title']),
				'secret'=>(bool)$sd['secret'],
			);
		$Eleanor->multisite=true;
	}
	else
	{
		$hms=array();
		$Eleanor->multisite=false;
	}
	$tcover=(string)Eleanor::$Template->$tpl();

	$Lst=Eleanor::LoadListTemplate('headfoot')
		->metahttp('text/html; charset='.DISPLAY_CHARSET)
		#Нафига следующая строка? При переносе сайта нельзя сменить домен!
		#->base(PROTOCOL.Eleanor::$domain.Eleanor::$site_path)
		->title(is_array($title) ? join(' &raquo; ',$title) : $title)
		->meta('robots','noindex, nofollow');

	array_unshift($jscripts,'js/jquery.min.js','js/core.js','js/lang-'.Language::$main.'.js');
	$jscripts=array_unique($jscripts);
	foreach($jscripts as &$v)
		$Lst->script($v);
	$thead=$Lst.Eleanor::JsVars(array(
		'c_domain'=>Eleanor::$vars['cookie_domain'],
		'c_prefix'=>Eleanor::$vars['cookie_prefix'],
		'c_time'=>Eleanor::$vars['cookie_save_time'],
		'ajax_file'=>Eleanor::$services['ajax']['file'],
		'site_path'=>Eleanor::$site_path,
		'language'=>Language::$main,
		'!head'=>$head ? '["'.join('","',array_keys($head)).'"]' : '[]',
	)+$hms,true,false,'CORE.').join($head);
}

function Finish($s)
{global$Eleanor,$start,$tcover,$thead;
	if($Eleanor->error)
		return$s;
	$r=array(
		'debug'=>'',
		'page status'=>'',
	);
	$l=Eleanor::$Language['main'];
	if(Eleanor::$vars['show_status']==2 or (Eleanor::$vars['show_status']==1 and Eleanor::$Permissions->IsAdmin()))
		$r['page status']=isset($l['page_status']) ? sprintf(
			$l['page_status'],
			round(array_sum(explode(' ',microtime()))-array_sum(explode(' ',$start)),3),
			Eleanor::$Db->queries,
			round(memory_get_usage()/1048576,3),
			round(memory_get_peak_usage()/1048576,3)
		) : '';

	if(DEBUG and Eleanor::$debug)
	{
		$Lst=Eleanor::LoadListTemplate('headfoot');
		foreach(Eleanor::$debug as $v)
		{
			if(!isset($v['f']))
				$v['f']='?';
			$r['debug'].=$Lst->debug($v['t'],$v['f'].(isset($v['l']) ? '['.$v['l'].']' : ''),$v['e']);
		}
	}
	return$tcover ? Eleanor::ExecBBLogic($tcover,$r+array('head'=>$thead,'module'=>$s)) : $s;
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
	$csh=!headers_sent();
	$l=Eleanor::$Language['errors'];
	if(!empty($extra['lang']) and isset($l[$e]))
		$e=$l[$e];
	if(empty($extra['ban']))
	{
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/error.html',
			array(
				'title'=>$l['happened'],
				'error'=>$e,
				'extra'=>$extra,
			)
		);
		if($csh)
			header('Retry-After: 7200');
	}
	else
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/ban.html',
			array(
				'title'=>$l['you_are_banned'],
				'message'=>$e ? OwnBB::Parse($e) : Eleanor::$vars['blocked_message'],
				'extra'=>$extra,
			)
		);

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

function LangNewUrl($url,$l)
{global$Eleanor;
	$our=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
	if(strpos($url,$our)!==0 or $our==$url or !$url=preg_replace('#^'.preg_quote($our,'#').'('.preg_quote(Eleanor::$filename,'#').'\??)?#i','',$url))
		return$our.$Eleanor->Url->Construct(array(),true);

	$old=$our.Eleanor::$filename.'?'.$url;
	$Eleanor->Url->__construct($url);
	parse_str($Eleanor->Url->string,$q);
	if(!isset($q['section'],$q['module']) or $q['section']!='modules')
		return$old;

	$modules=Modules::GetCache();
	if(isset($modules['ids'][$q['module']]))
	{
		$mid=(int)$modules['ids'][$q['module']];
		$s=$modules['sections'][$q['module']];
	}
	else
		return$old;

	$R=Eleanor::$Db->Query('SELECT `sections`,`path`,`api` FROM `'.P.'modules` WHERE `id`='.$mid.' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return$old;

	$sections=unserialize($a['sections']);
	foreach($sections as &$v)
		if(Eleanor::$vars['multilang'] and isset($v[$l]))
			$v=reset($v[$l]);
		else
			$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
	$modules=Modules::GetCache(false,$l);
	$m=array_keys($modules['ids'],$mid);
	$m=reset($m);

	$Eleanor->Url->SetPrefix(array('section'=>'modules','module'=>$m));
	$p=htmlspecialchars_decode($Eleanor->Url->Prefix(),ELENT);
	unset($q['module'],$q['section'],$q['key']);
	$path=Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR;
	if(!$a['api'] or !is_file($path.$a['api']))
		return$our.$p;
	$c='Api'.basename($a['path']);
	if(!class_exists($c,false))
		include$path.$a['api'];
	$Plug=new$c;
	$Plug->module=array(
		'sections'=>$sections,
		'path'=>$path,
		'name'=>$m,
		'section'=>$s,
		'id'=>$mid,
	);
	if(method_exists($Plug,'LangUrl') and $r=$Plug->LangUrl($q,$l))
		return$our.$r;
	else
		return$our.$p;
}