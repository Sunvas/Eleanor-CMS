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

require __dir__.'/core/core.php';
$Eleanor=Eleanor::getInstance();
Eleanor::$service='admin';#ID сервиса
Eleanor::LoadOptions(array('site','users-on-site'));
Eleanor::InitService();
Eleanor::$Language->queue['main']='langs/admin-*.php';

if(Eleanor::$vars['multilang'])
{
	if(isset($_GET['language']) and is_string($_GET['language']) and isset(Eleanor::$langs[$_GET['language']]))
	{
		if(Eleanor::$Login->IsUser())
			UserManager::Update(array('language'=>$_GET['language']));
		else
			Eleanor::SetCookie('lang',$_GET['language']);
		return GoAway(html_entity_decode(LangNewUrl(getenv('HTTP_REFERER'),$_GET['language'])));
	}
	if(!Eleanor::$Login->IsUser() and $l=Eleanor::GetCookie('lang') and isset(Eleanor::$langs[$l]) and $l!=LANGUAGE)
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
{
	$section=isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
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
{
	$l=Eleanor::$Language->Load('langs/admin_enter-*.php','enter');
	$login=isset($_POST['login']['name']) ? (string)$_POST['login']['name'] : '';
	$password=isset($_POST['login']['password']) ? (string)$_POST['login']['password'] : '';
	$errors=array();
	$captcha=Eleanor::$vars['antibrute']==2 && (isset($_POST['check']) || $ct=Eleanor::GetCookie('Captcha_'.get_class(Eleanor::$Login)) && $ct>time());

	if($captcha)
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$Eleanor->Captcha->disabled=false;
			if(isset($_POST['check']))
			{
				$cach=$Eleanor->Captcha->Check((string)$_POST['check']);
				$Eleanor->Captcha->Destroy();
				if(!$cach)
					$errors[]='WRONG_CAPTCHA';
			}
			else
				$errors[]='CAPTCHA';
		}
		$Eleanor->Captcha->disabled=false;
		$Eleanor->Captcha->Destroy();
		$captcha=$Eleanor->Captcha->GetCode();
	}

	if(!$errors and isset($_POST['login']))
		try
		{
			Eleanor::$Login->Login((array)$_POST['login'],array('captcha'=>$captcha));
			return GoAway(Eleanor::$filename.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
		}
		catch(EE$E)
		{
			$error=$E->getMessage();
			switch($error)
			{
				case'TEMPORARILY_BLOCKED':
					$errors['TEMPORARILY_BLOCKED']=$l['TEMPORARILY_BLOCKED'](htmlspecialchars($login,ELENT,CHARSET),round($E->extra['remain']/60));
				break;
				case'CAPTCHA':
					$captcha=true;
					$errors[]='CAPTCHA';
				break;
				case'WRONG_PASSWORD':
					$password='';
				default:
					$errors[]=$error;
			}
		}
	$title[]=$l['enter_to'];
	Start(array('Enter',array('errors'=>$errors,'login'=>$login,'password'=>$password,'captcha'=>$captcha)));
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
	$tcover=(string)(is_array($tpl) ? call_user_func_array(array(Eleanor::$Template,$tpl[0]),array_slice($tpl,1)) : Eleanor::$Template->$tpl());

	try
	{
		$Lst=Eleanor::LoadListTemplate('headfoot')
			->metahttp('text/html; charset='.DISPLAY_CHARSET)
			->base(PROTOCOL.getenv('HTTP_HOST').Eleanor::$site_path)
			->title(is_array($title) ? join(' &laquo; ',array_reverse($title)) : $title)
			->meta('robots','noindex, nofollow');

		array_unshift($jscripts,'js/jquery.min.js','js/core.js','js/lang-'.Language::$main.'.js');
		$jscripts=array_unique($jscripts);
		foreach($jscripts as &$v)
			$Lst->script($v);
	}
	catch(EE$E)
	{
		$Lst='<!-- '.$E->getMessage().' -->';
	}

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

		if($info==$current and $_SERVER['REQUEST_METHOD']=='GET')
			die('.');
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
		ob_start();ob_start();ob_start();#Странный глюк PHP... Достаточно сделать Parse error в index.php темы (или Template index was not found!) и Core::FinishOutPut будет получать пустое значение
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
	#Определим отправную точку, куда мы в любом случае отправим клиента
	$base=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;

	#Если запрос пришел с чужого домена - считаем такой запрос некорректным
	if(strpos($url,'://')!==false and strpos($url,$base)!==0)
		$url='';
	$url=preg_replace('#^'.preg_quote($base,'#').'('.preg_quote(Eleanor::$filename,'#').'\??)?#i','',$url);

	if($url=='')
		return$base.$Eleanor->Url->Construct(array(),true);

	$special=Eleanor::$filename.'?';
	parse_str($url,$q);
	if(!isset($q['section'],$q['module']) or $q['section']!='modules')
		return$base.$special.$url;

	$modules=Modules::GetCache();
	if(!isset($modules['ids'][$q['module']]))
		return$base.$special.$url;

	$mid=(int)$modules['ids'][$q['module']];
	$s=$modules['sections'][$q['module']];

	$R=Eleanor::$Db->Query('SELECT `sections`,`path`,`api` FROM `'.P.'modules` WHERE `id`='.$mid.' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return$base.$special.$url;

	$path=Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR;
	if(!$a['api'] or !is_file($path.$a['api']))
		return$base.$special.$url;

	$sections=unserialize($a['sections']);
	foreach($sections as &$v)
		if(Eleanor::$vars['multilang'] and isset($v[$l]))
			$v=reset($v[$l]);
		else
			$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
	$modules=Modules::GetCache(false,$l);
	$m=array_keys($modules['ids'],$mid);
	$q['module']=reset($m);

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
		return$base.$r;
	return$base.$special.$url;
}