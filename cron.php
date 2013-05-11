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
interface Task
{
	/*
		Функция должна вернуть true, если задание выполнено успешно, и false - если нет.
	*/
	public function Run($data);

	/*
		Функция должна возвратить
	*/
	public function GetNextRunInfo();
}

define('CMS',true);
require __dir__.'/core/core.php';
$Eleanor=Eleanor::getInstance();
Eleanor::$service='cron';#ID сервиса
if(0<$t=strpos(Eleanor::$site_path,Eleanor::$filename))
	Eleanor::$site_path=$t==1 ? '' : substr(Eleanor::$site_path,0,$t-1);
Eleanor::InitService();
Eleanor::$Language->queue['main'][]='langs/main-*.php';

ApplyLang(isset($_REQUEST['language']) ? (string)$_REQUEST['language'] : false);

$m=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;
if($m)
{
	$Eleanor->modules=Modules::GetCache();
	if(!isset($Eleanor->modules['ids'][$m]))
		return Start();
	$R=Eleanor::$Db->Query('SELECT `id`,`services`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files`,`image` FROM `'.P.'modules` WHERE `id`='.(int)$Eleanor->modules['ids'][$m].' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return Start();

	if(!$a['multiservice'])
	{
		$files=unserialize($a['files']);
		$a['file']=isset($files[Eleanor::$service]) ? $files[Eleanor::$service] : false;
	}
	if(!$a['file'])
		return Start();
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
{
	if(isset($_GET['id']))
		$R=Eleanor::$Db->Query('SELECT `id`,`task`,`free`,`options`,UNIX_TIMESTAMP(`nextrun`),`ondone`,`maxrun`,`alreadyrun`,`data`,`run_year`,`run_month`,`run_day`,`run_hour`,`run_minute`,`run_second`,`do` FROM `'.P.'tasks` WHERE `id`='.(int)$_GET['id'].' AND `status`=1 AND `locked`=0');
	else
	{
		#В случае, если скрипт запис... Через 2 часа запустим его снова.
		Eleanor::$Db->Update(P.'tasks',array('free'=>1,'locked'=>0),'`status`=1 AND `locked`=1 AND `free`=0 AND `nextrun`<FROM_UNIXTIME('.(time()-7200).')');
		$R=Eleanor::$Db->Query('SELECT `id`,`task`,`free`,`options`,UNIX_TIMESTAMP(`nextrun`) `nextrun`,`ondone`,`maxrun`,`alreadyrun`,`data`,`run_year`,`run_month`,`run_day`,`run_hour`,`run_minute`,`run_second`,`do` FROM `'.P.'tasks` WHERE `status`=1 AND `locked`=0 ORDER BY `free` ASC, `nextrun` ASC');
	}
	if($task=$R->fetch_assoc())
		do
		{
			if($task['free'] and $task['nextrun']>time())
				break;
			$f=Eleanor::$root.'core/tasks/'.$task['task'];
			$class='Task'.basename($task['task'],'.php');
			ob_start();
			register_shutdown_function('FatalCatcher');
			$Eleanor->c_l_e=false;
			if(!class_exists($class,false) and (!file_exists($f) or !include($f)))
			{
				Eleanor::$Db->Update(P.'tasks',array('status'=>0),'`id`='.$task['id'].' LIMIT 1');
				break;
			}

			$T=new $class($task['options'] ? unserialize($task['options']) : array());
			if(!($T instanceof Task))
			{
				Eleanor::$Db->Update(P.'tasks',array('status'=>0),'`id`='.$task['id'].' LIMIT 1');
				break;
			}
			Eleanor::$Db->Update(P.'tasks',array('free'=>0,'locked'=>1,'!lastrun'=>'NOW()'),'`id`='.$task['id'].' LIMIT 1');
			$res=$T->Run($task['data'] ? unserialize($task['data']) : array());
			if($res!==false)
				$res=true;
			$update=array(
				'free'=>$res,
				'locked'=>0,
				'!lastrun'=>'NOW()',
			);
			if($res)
			{
				if($task['maxrun']>0 and $task['maxrun']<=++$task['alreadyrun'])
					switch($task['ondone'])
					{
						case'delete':
							Eleanor::$Db->Delete(P.'tasks','`id`='.$task['id'].' LIMIT 1');
						break 2;
						default:
						case'deactivate':
							$update['status']=0;
					}
				$nr=Tasks::CalcNextRun(array(
					'year'=>$task['run_year'],
					'month'=>$task['run_month'],
					'day'=>$task['run_day'],
					'hour'=>$task['run_hour'],
					'minute'=>$task['run_minute'],
					'second'=>$task['run_second'],
				),$task['do']);
				if(!$nr)
					$update['status']=0;
				$update['!nextrun']='FROM_UNIXTIME('.(int)$nr.')';
			}
			$update['data']=$T->GetNextRunInfo();
			$update['data']=$update['data'] ? serialize($update['data']) : '';
			$update['maxrun']=$task['maxrun'];
			$update['alreadyrun']=$task['alreadyrun'];
			Eleanor::$Db->Update(P.'tasks',$update,'`id`='.$task['id'].' LIMIT 1');
		}while(false);
	Tasks::UpdateNextRun();
	Start();
}

function FatalCatcher()
{global$task;
	$e=error_get_last();
	if($e && $e!=$GLOBALS['Eleanor']->c_l_e && ($e['type'] & (E_ERROR|E_PARSE|E_COMPILE_ERROR|E_CORE_ERROR)))
	{
		$c=ob_get_contents();
		if($c!==false)
			ob_end_clean();
		$update=array('free'=>1,'locked'=>0,'!lastrun'=>'NOW()');
		$nr=Tasks::CalcNextRun(array(
			'year'=>$task['run_year'],
			'month'=>$task['run_month'],
			'day'=>$task['run_day'],
			'hour'=>$task['run_hour'],
			'minute'=>$task['run_minute'],
			'second'=>$task['run_second'],
		),$task['do']);
		if(!$nr)
			$update['status']=0;
		$update['!nextrun']='FROM_UNIXTIME('.(int)$nr.')';
		Eleanor::ErrorHandle($e['type'],ucfirst($e['message']),$e['file'],$e['line']);
		Eleanor::$Db->Update(P.'tasks',$update,'`id`='.$task['id'].' LIMIT 1');
		Tasks::UpdateNextRun();
		Error($c);
		$GLOBALS['Eleanor']->c_l_e=$e;
	}
	elseif(ob_get_contents()!==false)
		ob_end_flush();
}

#Предопределенные функции.
function Start($clean=false)
{
	if($clean and ob_get_contents()!==false)
	{
		ob_end_clean();
		ob_start();
	}
	if(!isset($_GET['noimage']))
	{
		Eleanor::$content_type='image/png';
		Eleanor::HookOutPut();
		echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWP4//8/AwAI/AL+hc2rNAAAAABJRU5ErkJggg==');
	}
	else
		Eleanor::HookOutPut();
}

function GoAway(){die;}
function Error($e){die($e);}

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

#Функция "Будь как", делает сервис другим. Осторожно, этот BeAs не похожа на другие
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
		if(!isset(Eleanor::$vars['furl']))
			Eleanor::LoadOptions('site');
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
	}
}