<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.su, http://eleanor-cms.ru, http://eleanor-cms.com, http://eleanor-cms.net
	E-mail: support@eleanor-cms.su, support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
$start=microtime();
define('CMS',true);
require dirname(__file__).'/core/core.php';

$Eleanor=Eleanor::getInstance();
Eleanor::$service='user';#ID сервиса
Eleanor::$Language->queue['main']='langs/user-*.php';
Eleanor::LoadOptions(array('site','users-on-site'));
Eleanor::LoadService();
$Eleanor->Url->furl=Eleanor::$vars['furl'];
$Eleanor->Url->delimiter=Eleanor::$vars['url_static_delimiter'];
$Eleanor->Url->defis=Eleanor::$vars['url_static_defis'];
$Eleanor->Url->ending=Eleanor::$vars['url_static_ending'];

if(isset($_GET['newtpl']) and Eleanor::$vars['templates'] and $themes=unserialize(Eleanor::$vars['templates']) and in_array($_GET['newtpl'],$themes))
{
	if(Eleanor::$Login->IsUser())
		UserManager::Update(array('theme'=>$_GET['newtpl']));
	else
		Eleanor::SetCookie('theme',$_GET['newtpl']);
	return GoAway();
}
$Eleanor->Url->special=$Eleanor->Url->furl ? '' : Eleanor::$filename.'?';
$Eleanor->started=$Eleanor->error=false;

$e=$Eleanor->Url->is_static ? $Eleanor->Url->GetEnding(array($Eleanor->Url->delimiter,$Eleanor->Url->ending),false) : '';
$m=false;
if(Eleanor::$vars['multilang'])
{
	$isu=Eleanor::$Login->IsUser();
	if(isset($_GET['language']) and is_string($_GET['language']) and isset(Eleanor::$langs[$_GET['language']]))
	{
		if($isu)
			UserManager::Update(array('language'=>$_GET['language']));
		else
			Eleanor::SetCookie(Eleanor::$service.'_lang',$_GET['language']);
		return GoAway(html_entity_decode(LangNewUrl(getenv('HTTP_REFERER'),$_GET['language'])));
	}
	if(!$isu and $l=Eleanor::GetCookie(Eleanor::$service.'_lang') and isset(Eleanor::$langs[$l]) and $l!=LANGUAGE)
	{
		Language::$main=$l;
		Eleanor::$Language->Change($l);
	}
	#Попробуем определить основной язык пользователя
	elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{
		$la=array();
		foreach(explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) as $v)
		{
			$v=trim($v);
			if(strpos($v,';q=')===false)
			{
				$la=array($v=>1);
				break;
			}
			else
				$la[substr($v,0,strpos($v,';'))]=substr(strrchr($v,'='),1);
		}
		arsort($la,SORT_NUMERIC);
		$la=substr(key($la),0,2);
		foreach(Eleanor::$langs as $k=>&$v)
			if(substr($k,0,2)==$la and $k!=LANGUAGE)
			{
				Language::$main=$k;
				Eleanor::$Language->Change($k);
				break;
			}
	}
	$lang=$Eleanor->Url->ParseToValue('lang');
	$chl=false;#Change language
	foreach(Eleanor::$langs as $k=>&$v)
		if($v['uri']==$lang)
		{
			$chl=$k;
			break;
		}
	if(!$chl)
	{
		$m=$lang;
		$chl=LANGUAGE;
	}

	if(Language::$main!=$chl)
	{
		$ref=getenv('HTTP_REFERER');
		if($ref and strpos($ref,PROTOCOL.Eleanor::$punycode.Eleanor::$site_path)===0)
		{
			if($isu)
				UserManager::Update(array('language'=>$chl));
			else
				Eleanor::SetCookie(Eleanor::$service.'_lang',$chl);
			Language::$main=$chl;
			Eleanor::$Language->Change($chl);
		}
		else
		{
			$newl=Language::$main;
			Language::$main=$chl;
			return GoAway(html_entity_decode(LangNewUrl(PROTOCOL.Eleanor::$punycode.$_SERVER['REQUEST_URI'],$newl)));
		}
	}

	if(LANGUAGE!=Language::$main)
		$Eleanor->Url->special.=$Eleanor->Url->Construct(array('lang'=>Eleanor::$langs[Language::$main]['uri']),false,false).$Eleanor->Url->GetDel();

	foreach(Eleanor::$lvars as $k=>&$v)
		Eleanor::$vars[$k]=Eleanor::FilterLangValues($v);
}
else
	Eleanor::$lvars=array();
#Три предустановленные переменные
$title=$head=$jscripts=array();

$theme=Eleanor::$Login->IsUser() ? Eleanor::$Login->GetUserValue('theme') : Eleanor::GetCookie('theme');
if(!Eleanor::$vars['templates'] or !in_array($theme,Eleanor::$vars['templates']))
	$theme=false;

Eleanor::InitTemplate($theme ? $theme : Eleanor::$services[Eleanor::$service]['theme']);
$Eleanor->modules=Modules::GetCache();

if(Eleanor::$vars['site_closed'] and !Eleanor::$Permissions->ShowClosedSite() and !Eleanor::LoadLogin(Eleanor::$services['admin']['login'])->IsUser())
{	$s=Eleanor::$Template->Denied();
	Start('');
	header('Retry-After: 7200',true,503);
	die($s);
}
unset(Eleanor::$vars['site_close_mes']);

if(Eleanor::$Permissions->IsBanned())
	throw new EE(Eleanor::$Login->GetUserValue('ban_explain'),EE::BAN);

if('index'.$Eleanor->Url->ending==$Eleanor->Url->string or !$_SERVER['QUERY_STRING'])
{
	$Eleanor->Url->string='';
	return MainPage();
}

if(!$m)
	$m=$Eleanor->Url->ParseToValue('module');

if($m)
{	if(!isset($Eleanor->modules['ids'][$m]))
		return MainPage($m,$e);
	$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files` FROM `'.P.'modules` WHERE `id`='.(int)$Eleanor->modules['ids'][$m].' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return MainPage($m,$e);
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
		'path'=>Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR,
		'id'=>$a['id'],
		'sections'=>$a['sections'],
	);
	$Eleanor->Url->SetPrefix(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']));
	if(!Modules::Load($Eleanor->module['path'],$a['multiservice'],$a['file'] ? $a['file'] : 'index.php'))
	{
		$Eleanor->Url->SetPrefix('');
		return ExitPage();
	}
}
elseif(isset($_REQUEST['direct']) and is_file($f=Eleanor::$root.'addons/direct/'.preg_replace('#[^a-z0-9]+#i','',(string)$_REQUEST['direct']).'.php'))
	include$f;
else
	return MainPage();

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

	if(!$title)
		$t=Eleanor::$vars['site_name'];
	elseif(is_string($title))
		$t=$title;
	else
		$t=(is_array($title) ? join(Eleanor::$vars['site_defis'],$title) : $title).(Eleanor::$vars['site_name'] ? Eleanor::$vars['site_defis'].Eleanor::$vars['site_name'] : '');

	$t=htmlspecialchars($t,ELENT,CHARSET,false);
	if(isset($Eleanor->module['description']))
		$descr=$Eleanor->module['description'];
	elseif(isset($Eleanor->module['general']))
		$descr=Eleanor::$vars['site_description'];
	else
		$descr=false;

	$Lst=Eleanor::LoadListTemplate('headfoot')
		->metahttp('text/html; charset='.DISPLAY_CHARSET)
		->base(PROTOCOL.Eleanor::$domain.Eleanor::$site_path)
		->title($t)
		->meta('generator','Eleanor CMS '.ELEANOR_VERSION);

	if($descr)
		$Lst->meta('description',htmlspecialchars($descr,ELENT,CHARSET,false));

	if(false!==$mn=array_search(6,$Eleanor->modules['ids']))
		$Lst->link(array('rel'=>'search','href'=>$Eleanor->Url->Construct(array('module'=>$mn),false)))
			->link(array(
				'rel'=>'search',
				'type'=>'application/opensearchdescription+xml',
				'title'=>Eleanor::$vars['site_name'],
				'href'=>Eleanor::$services['xml']['file'].'?'.Url::Query(array('module'=>$mn)),
			));

	if(Eleanor::$vars['site_domain']!=Eleanor::$domain and Eleanor::$vars['parked_domains']=='rel' and Eleanor::$vars['site_domain'])
		$Lst->link(array('rel'=>'canonical','href'=>PROTOCOL.preg_replace('#^[a-z0-9\-]+\.[a-z\-]{2,}#i',Eleanor::$vars['site_domain'],$_SERVER['SERVER_NAME']).$_SERVER['REQUEST_URI']));
	#Если модулем задан оригинальный URL страницы, сравним его с полученным
	elseif(isset($Eleanor->origurl))
	{
		$u=isset($Eleanor->module['general']) ? PROTOCOL.Eleanor::$punycode.Eleanor::$site_path : $Eleanor->origurl;
		$ru=PROTOCOL.Eleanor::$punycode.$_SERVER['REQUEST_URI'];
		if(CHARSET!='utf-8')
		{
			$u=mb_convert_encoding($u,CHARSET,'utf-8');
			$ru=mb_convert_encoding($ru,CHARSET,'utf-8');
		}
		if(strcasecmp($u,$ru)!=0)
			$Lst->link(array('rel'=>'canonical','href'=>$u));
	}

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
	if(Eleanor::$vars['show_status']==2 or (Eleanor::$vars['show_status']==1 and Eleanor::$Permissions->IsAdmin()))
		$r['page status']=sprintf(
			Eleanor::$Language['main']['page_status'],
			round(array_sum(explode(' ',microtime()))-array_sum(explode(' ',$start)),3),
			Eleanor::$Db->queries,
			Eleanor::$gzip ? 'GZIP.' : '',
			round(memory_get_usage()/1048576,3),
			round(memory_get_peak_usage()/1048576,3)
		);

	if(DEBUG and Eleanor::$debug)
	{
		$Lst=Eleanor::LoadListTemplate('headfoot');
		foreach(Eleanor::$debug as $v)
		{
			if(!isset($v['f']))
				$v['f']='?';
			$Lst->debug($v['t'],$v['f'].(isset($v['l']) ? '['.$v['l'].']' : ''),$v['e']);
		}
		$r['debug'].=$Lst;
	}
	$s=$tcover ? Eleanor::ExecBBLogic($tcover,$r+array('head'=>$thead,'module'=>$s)) : $s;

	if($parsers=glob(Eleanor::$root.'core/html_parsers/*.php'))
		foreach($parsers as &$f)
		{
			$c='HtmlParser'.substr(basename($f),0,-4);
			if(!class_exists($c,false))
				include$f;
			$s=$c::Parse($s);
		}
	return$s;
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

function Error($e=false,$extra=array())
{global$Eleanor;
	$csh=!headers_sent();
	$le=Eleanor::$Language['errors'];
	if(empty($extra['ban']))
	{		if(isset($extra['date']))			$e=$le['banlock']($extra['date'],$e);
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
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/ban.html',
			array(
				'title'=>$le['you_are_banned'],
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
#Специальные функции для сервиса user

function ExitPage($code=404)
{global$Eleanor;
	#Страница с Error
	$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`title_l`,`image` FROM `'.P.'modules` WHERE `id`=4 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return GoAway(true);
	$a['sections']=unserialize($a['sections']);
	foreach($a['sections'] as &$v)
		if(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
			$v=reset($v[Language::$main]);
		else
			$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
	$m=array_keys($Eleanor->modules['sections'],'errors');
	$m=reset($m);
	$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues(unserialize($a['title_l'])) : '';
	$Eleanor->module=array(
		'name'=>$m,
		'section'=>isset($Eleanor->modules['sections'][$m]) ? $Eleanor->modules['sections'][$m] : '',
		'title'=>$a['title_l'],
		'image'=>$a['image'],
		'path'=>Eleanor::$root.'modules/errors/',
		'id'=>$a['id'],
		'sections'=>$a['sections'],
		'code'=>$code,
	);
	$Eleanor->Url->SetPrefix(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>'errors') : array('module'=>'errors'),false);
	if(!Modules::Load($Eleanor->module['path'],true))
		return GoAway(false);
	die;
}

function MainPage($tm=false,$ending='')
{global$Eleanor;
	do
	{
		if(!$tm)
			break;

		if(!$Eleanor->Url->is_static)
			return ExitPage();

		$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files` FROM `'.P.'modules` WHERE `id`='.(int)Eleanor::$vars['prefix_free_module'].' AND `active`=1 LIMIT 1');
		if(!$a=$R->fetch_assoc())
			break;
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
		$m=reset($a['sections']);
		$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues(unserialize($a['title_l'])) : '';
		$Eleanor->module=array(
			'name'=>$m,
			'section'=>isset($Eleanor->modules['sections'][$m]) ? $Eleanor->modules['sections'][$m] : '',
			'title'=>$a['title_l'],
			'path'=>Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR,
			'id'=>$a['id'],
			'sections'=>$a['sections'],
		);
		if($Eleanor->Url->furl or $Eleanor->Url->is_static)
			$Eleanor->Url->string=$tm.($Eleanor->Url->string ? $Eleanor->Url->delimiter.$Eleanor->Url->string : $ending);

		if(Eleanor::$vars['multilang'] and Language::$main!=LANGUAGE)
			$Eleanor->Url->SetPrefix(array('lang'=>Eleanor::$langs[Language::$main]['uri']));

		if(Modules::Load($Eleanor->module['path'],$a['multiservice'],$a['file'] ? $a['file'] : 'index.php'))
			return;

		return ExitPage();
	}while(false);

	$Eleanor->module=array('general'=>true,'section'=>'mainpage');
	#Тут мы по-умолчанию грузим модуль главной страницы, который настраивается в админке. Но никто не мешает пихануть сюда что-то свое.
	Modules::Load(Eleanor::$root.'modules/mainpage/');
}

function LangNewUrl($url,$l)
{global$Eleanor;
	$our=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
	if(strpos($url,$our)!==0 or $our==$url or !$url=preg_replace('#^'.preg_quote($our,'#').'('.preg_quote(Eleanor::$filename,'#').'\??)?#i','',$url))
		return $l==LANGUAGE ? $our : $our.$Eleanor->Url->Construct(array('lang'=>Eleanor::$langs[$l]['uri']),true,Eleanor::$vars['url_static_delimiter']);

	$Eleanor->Url->__construct($url);

	$lang=LANGUAGE;
	if($Eleanor->Url->is_static)
	{
		$Eleanor->Url->ending=$Eleanor->Url->GetEnding(array(Eleanor::$vars['url_static_delimiter'],Eleanor::$vars['url_static_ending']));
		$Eleanor->Url->furl=true;
		$olds=$Eleanor->Url->string;
		$m=$Eleanor->Url->ParseToValue('module',true);
		foreach(Eleanor::$langs as $k=>&$v)
			if($v['uri']==$m)
			{
				$lang=$k;
				$olds=$Eleanor->Url->string;
				$m=$Eleanor->Url->ParseToValue('module',true);
				if(!$m)
					return LangNewUrl('',$l);
				break;
			}
		$q=true;
	}
	else
	{
		parse_str($Eleanor->Url->string,$q);
		if(isset($q['uri']))
			foreach(Eleanor::$langs as $k=>&$v)
				if($v['uri']==$q['uri'])
					$lang=$k;
		$m=isset($q['module']) ? $q['module'] : 0;
		unset($q['module'],$q['lang']);#Смотри в Static api. Для избежания конфликтов с $Url->prefix
		if(!$m and !$q)
			return LangNewUrl('',$l);
	}
	if($lang!=Language::$main)
		Language::$main=$lang;
	$Eleanor->modules=Modules::GetCache();

	if(isset($Eleanor->modules['ids'][$m]))
	{
		$mid=(int)$Eleanor->modules['ids'][$m];
		$s=$Eleanor->modules['sections'][$m];
	}
	else
	{
		$mid=2;#ID статических страниц
		$s=array_keys($Eleanor->modules['ids'],$mid);
		$s=reset($s);
		$m=false;
		if($Eleanor->Url->is_static)
			$Eleanor->Url->string=$olds;
	}

	$R=Eleanor::$Db->Query('SELECT `sections`,`path`,`api` FROM `'.P.'modules` WHERE `id`='.$mid.' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return LangNewUrl('',$l);

	$path=Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR;
	$a['sections']=unserialize($a['sections']);
	foreach($a['sections'] as &$v)
		if(Eleanor::$vars['multilang'] and isset($v[$l]))
			$v=reset($v[$l]);
		else
			$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
	if($m)
	{
		$Eleanor->modules=Modules::GetCache(false,$l);
		$m=array_keys($Eleanor->modules['ids'],$mid);
		$m=reset($m);
	}
	$Eleanor->Url->SetPrefix($l==LANGUAGE ? array('module'=>$m) : array('lang'=>Eleanor::$langs[$l]['uri'],'module'=>$m));
	$p=htmlspecialchars_decode($Eleanor->Url->Prefix($m && !$Eleanor->Url->string ? true : $Eleanor->Url->delimiter),ELENT);
	if($Eleanor->Url->is_static and $Eleanor->Url->string)
		$p.=$Eleanor->Url->string.$Eleanor->Url->ending;
	if(!$Eleanor->Url->string or !$a['api'] or !is_file($path.$a['api']))
		return$our.$p;
	$c='Api'.basename($a['path']);
	if(!class_exists($c,false))
		include$path.$a['api'];
	$Plug=new$c;
	$Plug->module=array(
		'sections'=>$a['sections'],
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