<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/
if(!defined('CMS'))die;
global$Eleanor,$title;
$lang=Eleanor::$Language->Load('addons/admin/langs/tasks-*.php','tasks');
Eleanor::$Template->queue[]='Tasks';

$tasks=array();
$R=Eleanor::$Db->Query('SELECT `task` FROM `'.P.'tasks`');
while($a=$R->fetch_assoc())
	$tasks[]=$a['task'];

$realtasks=glob(Eleanor::$root.'core/tasks/*.php');
foreach($realtasks as $k=>&$v)
{
	$v=basename($v);
	if(strpos($v,'special_')===0)
		unset($realtasks[$k]);
}
$hastasks=count(array_diff($realtasks,$tasks))>0;

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$hastasks ? $Eleanor->Url->Construct(array('do'=>'add')) : false,
);

$Eleanor->ta_post=false;
$Eleanor->ta=array(
	'task'=>array(
		'title'=>$lang['handler'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->ta_post,
		'options'=>array(
			'callback'=>function($co) use ($realtasks,$tasks)
			{global$Eleanor;
				$ret=Eleanor::Option('&mdash;','',in_array('',$co['value']),array(),2);
				$a=array_diff($realtasks,array_diff($tasks,$co['value']));
				foreach($a as &$v)
					$ret.=Eleanor::Option($v,false,in_array($v,$co['value']));
				return$ret;
			},
		),
	),
	'title_l'=>array(
		'title'=>$lang['name'],
		'descr'=>'',
		'type'=>'input',
		'load'=>function($a)
		{
			$ret=$a['value'] ? unserialize($a['value']) : array();
			return array('value'=>$a['multilang'] ? $ret : Eleanor::FilterLangValues($ret));
		},
		'save'=>function($a)
		{
			return$a['multilang'] ? serialize($a['value']) : serialize(array(''=>$a['value']));
		},
		'multilang'=>Eleanor::$vars['multilang'],#Не ствим true потому что TitleLoad сам обрабатывает значения в нужно виде :)
		'bypost'=>&$Eleanor->ta_post,
		'options'=>array(
			'htmlsafe'=>true,#Только для текстовых данных
		),
	),
	'run_year'=>array(
		'title'=>$lang['runyear'],
		'descr'=>$lang['runyear_'],
		'type'=>'input',
		'default'=>'*',
		'bypost'=>&$Eleanor->ta_post,
	),
	'run_month'=>array(
		'title'=>$lang['runmonth'],
		'descr'=>$lang['runmonth_'],
		'type'=>'input',
		'default'=>'*',
		'bypost'=>&$Eleanor->ta_post,
	),
	'run_day'=>array(
		'title'=>$lang['runday'],
		'descr'=>$lang['runday_'],
		'type'=>'input',
		'default'=>'*',
		'bypost'=>&$Eleanor->ta_post,
	),
	'run_hour'=>array(
		'title'=>$lang['runhour'],
		'descr'=>$lang['runhour_'],
		'type'=>'input',
		'default'=>'1',
		'bypost'=>&$Eleanor->ta_post,
	),
	'run_minute'=>array(
		'title'=>$lang['runminute'],
		'descr'=>$lang['runminute_'],
		'type'=>'input',
		'default'=>'*',
		'bypost'=>&$Eleanor->ta_post,
	),
	'run_second'=>array(
		'title'=>$lang['runsecond'],
		'descr'=>$lang['runsecond_'],
		'type'=>'input',
		'default'=>'*',
		'bypost'=>&$Eleanor->ta_post,
	),
	'maxrun'=>array(
		'title'=>$lang['maxrun'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->ta_post,
	),
	'alreadyrun'=>array(
		'title'=>$lang['alreadyrun'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->ta_post,
	),
	'ondone'=>array(
		'title'=>$lang['ondone'],
		'descr'=>$lang['ondone_'],
		'type'=>'select',
		'bypost'=>&$Eleanor->ta_post,
		'default'=>'deactivate',
		'options'=>array(
			'options'=>array(
				'deactivate'=>$lang['deactivate'],
				'delete'=>$lang['delete'],
			),
		),
	),
	'status'=>array(
		'title'=>$lang['status'],
		'descr'=>'',
		'name'=>'status',
		'type'=>'check',
		'bypost'=>&$Eleanor->ta_post,
	),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'add':
			if($hastasks)
			{
				if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
					Save(0);
				else
					AddEdit(0);
				break;
			}
		default:
			ShowList();
	}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		Save($id);
	else
		AddEdit($id);
}
elseif(isset($_GET['swap']))
{
	if(Eleanor::$our_query)
		Eleanor::$Db->Update(P.'tasks',array('!status'=>'NOT `status`'),'`id`='.(int)$_GET['swap'].' LIMIT 1');
	Tasks::UpdateNextRun();
	GoAway();
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `title_l` `title` FROM `'.P.'tasks` WHERE `id`='.$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'tasks','`id`='.$id.' LIMIT 1');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$s=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$s;
}
else
	ShowList();

function ShowList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['tasks']['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$items=$where=$qs=array();
	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		#filters... ?
	}

	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'tasks`');
	list($cnt)=$R->fetch_row();

	if($page<=0)
		$page=1;
	if(isset($_GET['new-pp']) and 4<$pp=(int)$_GET['new-pp'])
		Eleanor::SetCookie('per-page',$pp);
	else
		$pp=abs((int)Eleanor::GetCookie('per-page'));
	if($pp<5 or $pp>500)
		$pp=50;
	$offset=abs(($page-1)*$pp);
	if($cnt and $offset>=$cnt)
		$offset=max(0,$cnt-$pp);
	$sort=isset($_GET['sort']) ? (string)$_GET['sort'] : '';
	$sot=$sort=='title';
	if(!in_array($sort,array('id','task','free','nextrun','status')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'desc';
	if($so!='desc')
		$so='asc';
	if($sort and ($sort!='id' or $so!='asc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	$R=Eleanor::$Db->Query('SELECT `id`,`task`,`title_l` `title`,`free`,`nextrun`,`lastrun`,`run_year`,`run_month`,`run_day`,`run_hour`,`run_minute`,`run_second`,`status` FROM `'.P.'tasks` LIMIT '.$offset.', '.$pp);
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : array();

		$a['_aswap']=$Eleanor->Url->Construct(array('swap'=>$a['id']));
		$a['_aedit']=strpos($a['task'],'special_')===0 ? false : $Eleanor->Url->Construct(array('edit'=>$a['id']));
		$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));

		$items[$a['id']]=array_slice($a,1);
	}

	$links=array(
		'sort_nextrun'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'nextrun','so'=>$qs['sort']=='nextrun' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_status'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'status','so'=>$qs['sort']=='status' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_task'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'task','so'=>$qs['sort']=='task' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_free'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'free','so'=>$qs['sort']=='free' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$s=Eleanor::$Template->ShowList($items,$cnt,$page,$pp,$qs,$links);
	Start();
	echo$s;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['tasks'];
	$values=array();
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'tasks` WHERE `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc() or strpos($a['task'],'special_')===0)
				return GoAway(true);
			foreach($a as $k=>&$v)
				if(isset($Eleanor->ta[$k]))
					$values[$k]['value']=$v;
		}
		$title[]=$lang['editing'];
	}
	else
		$title[]=$lang['adding'];

	if($errors)
	{
		if($errors===true)
			$errors=array();
		$Eleanor->ta_post=true;
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$values=$Eleanor->Controls->DisplayControls($Eleanor->ta,$values)+$values;
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	$c=Eleanor::$Template->AddEdit($id,$Eleanor->ta,$values,$errors,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$C=new Controls;
	$C->throw=false;
	try
	{
		$values=$C->SaveControls($Eleanor->ta);
	}
	catch(EE$E)
	{
		return AddEdit($id,$E->getMessage());
	}
	$errors=$C->errors;

	$values['do']=date_offset_get(date_create());
	$nr=Tasks::CalcNextRun(array(
		'year'=>$values['run_year'],
		'month'=>$values['run_month'],
		'day'=>$values['run_day'],
		'hour'=>$values['run_hour'],
		'minute'=>$values['run_minute'],
		'second'=>$values['run_second'],
	),$values['do']);
	if($nr===false)
		$errors[]='NO_NEXT_RUN';

	if($errors)
		return AddEdit($id,$errors);

	$values['!nextrun']='FROM_UNIXTIME('.$nr.')';
	if(!$values['task'])
		$values['status']=0;

	if($id)
		Eleanor::$Db->Update(P.'tasks',$values,'`id`='.$id.' LIMIT 1');
	else
		Eleanor::$Db->Insert(P.'tasks',$values);
	Tasks::UpdateNextRun();
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}