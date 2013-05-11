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
$lang=Eleanor::$Language->Load('addons/admin/langs/spam-*.php','spam');
Eleanor::$Template->queue[]='Spam';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
	'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'add':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				Save(0);
			else
				AddEdit(0);
		break;
		case'options':
			$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
			$c=$Eleanor->Settings->GetInterface('group','mailer');
			if($c)
			{
				$c=Eleanor::$Template->Options($c);
				Start();
				echo$c;
			}
		break;
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
elseif(isset($_GET['change'],$_GET['newstatus']))
{
	$id=(int)$_GET['change'];
	if(!Eleanor::$our_query)
		return GoAway();
	$R=Eleanor::$Db->Query('SELECT `total`,`per_run`,`taskid`,`status` FROM `'.P.'spam` WHERE `id`='.$id.' LIMIT 1');
	if($a=$R->fetch_assoc())
	{
		$spam=$task=array();
		switch((string)$_GET['newstatus'])
		{
			case'runned':
				if(in_array($a['status'],array('','stopped','finished')))
				{
					$do=date_offset_get(date_create());
					$task=array(
						'free'=>1,
						'locked'=>0,
						'!nextrun'=>'FROM_UNIXTIME('.Tasks::CalcNextRun(array(),$do).')',
						'maxrun'=>1,
						'alreadyrun'=>0,
						'status'=>1,
						'run_year'=>'*',
						'run_month'=>'*',
						'run_day'=>'*',
						'run_hour'=>'*',
						'run_minute'=>'*',
						'run_second'=>'*',
						'do'=>$do,
						'data'=>serialize(array('lastid'=>0)),
					);
					$spam=array(
						'status'=>'runned',
						'sent'=>0,
						'!statusdate'=>'NOW()',
					);
				}
				elseif($a['status']=='paused')
				{
					$spam=array('status'=>'runned','!statusdate'=>'NOW()');
					$task=array('status'=>1);
				}
			break;
			case'paused':
				if($a['status']=='runned')
				{
					$spam=array('status'=>'paused','!statusdate'=>'NOW()');
					$task=array('status'=>0);
				}
			break;
			case'stopped':
			default:
				$task=array(
					'free'=>1,
					'locked'=>0,
					'!nextrun'=>'FROM_UNIXTIME(0)',
					'maxrun'=>0,
					'alreadyrun'=>0,
					'status'=>0,
					'data'=>serialize(array('lastid'=>0)),
				);
				$spam=array(
					'status'=>'stopped',
					'!statusdate'=>'NOW()',
				);
		}
		if($task)
		{
			Eleanor::$Db->Update(P.'tasks',$task,'`id`='.$a['taskid'].' AND `name`=\'spam\' LIMIT 1');
			if($task['status'])
				Tasks::UpdateNextRun();
		}
		if($spam)
			Eleanor::$Db->Update(P.'spam',$spam,'`id`='.$id.' LIMIT 1');
	}
	return GoAway();
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `innertitle`,`taskid` FROM `'.P.'spam` LEFT JOIN `'.P.'spam_l` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Files::Delete(Eleanor::$root.Eleanor::$uploads.'/spam/'.$id);
		Eleanor::$Db->Delete(P.'spam','`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Delete(P.'spam_l','`id`='.$id);
		Eleanor::$Db->Delete(P.'tasks','`id`='.$a['taskid'].' AND `name`=\'spam\'');
		Tasks::UpdateNextRun();
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$s=Eleanor::$Template->Delete($lang,$back);
	Start();
	echo$s;
}
else
	ShowList();

function ShowList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['spam']['list'];
	$items=$qs=array();
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'spam`');
	list($cnt)=$R->fetch_row();
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		#filters... ?
	}

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
	if(!in_array($sort,array('id','innertitle','status')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'desc';
	if($so!='desc')
		$so='asc';
	if($sort and ($sort!='id' or $so!='asc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']))
		switch($_POST['op'])
		{
			case'k':
				$tids=$ids=array();
				$R=Eleanor::$Db->Query('SELECT `id`,`taskid` FROM `'.P.'spam` WHERE `id`'.Eleanor::$Db->In($_POST['mass']));
				while($a=$R->fetch_assoc())
				{
					$ids[]=$a['id'];
					$tids[]=$a['taskid'];
				}
				$ids_=Eleanor::$Db->In($ids);
				Eleanor::$Db->Delete(P.'spam','`id`'.$ids_);
				Eleanor::$Db->Delete(P.'spam_l','`id`'.$ids_);
				Eleanor::$Db->Delete(P.'tasks','`id`'.Eleanor::$Db->In($tids).' AND `name`=\'spam\'');
				foreach($ids as &$v)
					Files::Delete(Eleanor::$root.Eleanor::$uploads.'/spam/'.$v);
		}

	if($cnt>0)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`innertitle` `title`,`sent`,`total`,`status`,`statusdate` FROM `'.P.'spam` INNER JOIN `'.P.'spam_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.','.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			switch($a['status'])
			{
				case'runned':
					$a['_astop']=$Eleanor->Url->Construct(array('change'=>$a['id'],'newstatus'=>'stopped'));
					$a['_apause']=$Eleanor->Url->Construct(array('change'=>$a['id'],'newstatus'=>'paused'));
				break;
				case'paused':
					$a['_astop']=$Eleanor->Url->Construct(array('change'=>$a['id'],'newstatus'=>'stopped'));
				default:
					$a['_arun']=$Eleanor->Url->Construct(array('change'=>$a['id'],'newstatus'=>'runned'));
			}

			$items[]=$a;
		}
	}

	$links=array(
		'sort_innertitle'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'innertitle','so'=>$qs['sort']=='innertitle' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_status'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'status','so'=>$qs['sort']=='status' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->ShowList($items,$cnt,$pp,$page,$qs,$links);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['spam'];
	$values=array();
	$runned=false;
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`per_run`,`finame`,`finamet`,`figroup`,`figroupt`,`fiip`,`firegisterb`,`firegistera`,`filastvisitb`,`filastvisita`,`figender`,`fiemail`,`fiids`,`deleteondone`,`status` FROM `'.P.'spam` WHERE id='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway(true);
			$values['figroup']=$values['figroup'] ? explode(',',trim($values['figroup'],',')) : array();
			$values['innertitle']=$values['title']=$values['text']=array();
			if($values['status']=='runned')
				$runned=true;

			$R=Eleanor::$Db->Query('SELECT `language`,`innertitle`,`title`,`text` FROM `'.P.'spam_l` WHERE `id`='.$id);
			while($temp=$R->fetch_assoc())
				if(!Eleanor::$vars['multilang'] and (!$temp['language'] or $temp['language']==Language::$main))
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk]=$tv;
					if(!$temp['language'])
						break;
				}
				elseif(!$temp['language'] and Eleanor::$vars['multilang'])
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk][Language::$main]=$tv;
					$values['_onelang']=true;
					break;
				}
				elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$temp['language']]))
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk][$temp['language']]=$tv;

			if(Eleanor::$vars['multilang'])
			{
				if(!isset($values['_onelang']))
					$values['_onelang']=false;
				$values['_langs']=isset($values['title']['value']) ? array_keys($values['title']['value']) : array();
			}
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['adding'];
		$values=array(
			'per_run'=>25,
			'finame'=>'',
			'finamet'=>'b',
			'figroup'=>array(),
			'figroupt'=>'and',
			'fiip'=>'',
			'firegisterb'=>'',
			'firegistera'=>'',
			'filastvisitb'=>'',
			'filastvisita'=>'',
			'figender'=>-2,
			'fiemail'=>'',
			'fiids'=>'',
			'deleteondone'=>false,
			'status'=>'stopped',
		);
		$values['innertitle']=$values['title']=$values['text']=Eleanor::$vars['multilang'] ? array_combine(array_keys(Eleanor::$langs),array_fill(0,count(Eleanor::$langs),'')) : '';
		if(Eleanor::$vars['multilang'])
		{
			$values['_onelang']=true;
			$values['_langs']=array_keys(Eleanor::$langs);
		}
	}

	if($errors and !$runned)
	{
		$bypost=true;
		$values['per_run']=isset($_POST['per_run']) ? (int)$_POST['per_run'] : 25;
		$values['finame']=isset($_POST['finame']) ? (string)$_POST['finame'] : '';
		$values['finamet']=isset($_POST['finamet']) ? (string)$_POST['finamet'] : '';
		$values['figroup']=isset($_POST['figroup']) ? (array)$_POST['figroup'] : array();
		$values['figroupt']=isset($_POST['figroupt']) ? (string)$_POST['figroupt'] : 'and';
		$values['fiip']=isset($_POST['fiip']) ? (string)$_POST['fiip'] : '';
		$values['firegisterb']=isset($_POST['firegisterb']) ? (string)$_POST['firegisterb'] : '';
		$values['firegistera']=isset($_POST['firegistera']) ? (string)$_POST['firegistera'] : '';
		$values['filastvisitb']=isset($_POST['filastvisitb']) ? (string)$_POST['filastvisitb'] : '';
		$values['filastvisita']=isset($_POST['filastvisita']) ? (string)$_POST['filastvisita'] : '';
		$values['figender']=isset($_POST['figender']) ? (int)$_POST['figender'] : -2;
		$values['fiemail']=isset($_POST['fiemail']) ? (string)$_POST['fiemail'] : '';
		$values['fiids']=isset($_POST['fiids']) ? (string)$_POST['fiids'] : '';
		$values['deleteondone']=isset($_POST['deleteondone']);
		$values['status']=isset($_POST['status']) ? (string)$_POST['status'] : '';

		if(Eleanor::$vars['multilang'])
		{
			$values['_onelang']=isset($_POST['_onelang']);
			$values['_langs']=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array(Language::$main);
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$values['innertitle'][$k]=isset($_POST['innertitle'][$k]) ? (string)$_POST['innertitle'][$k] : '';
				$values['title'][$k]=isset($_POST['title'][$k]) ? (string)$_POST['title'][$k] : '';
				$values['text'][$k]=isset($_POST['text'][$k]) ? $Eleanor->Editor_result->GetHtml((string)$_POST['text'][$k],true) : '';
			}
		}
		else
		{
			$values['innertitle']=isset($_POST['innertitle']) ? (string)$_POST['innertitle'] : '';
			$values['title']=isset($_POST['title']) ? (string)$_POST['title'] : '';
			$values['text']=isset($_POST['text']) ? (string)$_POST['text'] : '';
		}
	}
	else
		$bypost=false;

	$uploader=$Eleanor->Uploader->Show($id ? 'spam/'.$id : false);
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->AddEdit($id,$values,$runned,$uploader,$links,$errors,$bypost,$back);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$filter=true;
	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `total`,`per_run`,`taskid`,`status` FROM `'.P.'spam` WHERE id='.$id.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway(true);
		$filter=$a['status']!='runned';
	}
	$lang=Eleanor::$Language['spam'];

	$values=$errors=array();
	if($filter)
	{
		$figr=isset($_POST['figroup']) ? (array)$_POST['figroup'] : array();
		$values+=array(
			'finame'=>isset($_POST['finame']) ? (string)$_POST['finame'] : '',
			'finamet'=>isset($_POST['finamet']) ? (string)$_POST['finamet'] : 'b',
			'figroup'=>$figr ? ','.join(',',$figr).',' : '',
			'figroupt'=>isset($_POST['figroupt']) ? (string)$_POST['figroupt'] : 'and',
			'fiip'=>isset($_POST['fiip']) ? $_POST['fiip'] : '',
			'firegisterb'=>isset($_POST['firegisterb']) ? (string)$_POST['firegisterb'] : '',
			'firegistera'=>isset($_POST['firegistera']) ? (string)$_POST['firegistera'] : '',
			'filastvisitb'=>isset($_POST['filastvisitb']) ? (string)$_POST['filastvisitb'] : '',
			'filastvisita'=>isset($_POST['filastvisita']) ? (string)$_POST['filastvisita'] : '',
			'figender'=>isset($_POST['figender']) ? (int)$_POST['figender'] : -2,
			'fiemail'=>isset($_POST['fiemail']) ? (string)$_POST['fiemail'] : '',
			'fiids'=>isset($_POST['fiids']) ? (string)$_POST['fiids'] : '',
		);
	}
	$values+=array(
		'per_run'=>isset($_POST['per_run']) ? (int)$_POST['per_run'] : 25,
		'deleteondone'=>isset($_POST['deleteondone']),
		'status'=>isset($_POST['status']) ? (string)$_POST['status'] : 'stopped',
	);

	if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
	{
		$langs=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array();
		$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
		if(!$langs)
			$langs=array(Language::$main);
	}
	else
		$langs=array('');

	$Eleanor->Editor->smiles=false;

	if(Eleanor::$vars['multilang'])
	{
		$lvalues=array(
			'innertitle'=>array(),
			'title'=>array(),
			'text'=>array(),
		);
		foreach($langs as $l)
		{
			$lng=$l ? $l : Language::$main;
			$lvalues['innertitle'][$l]=isset($_POST['innertitle'][$lng]) ? (string)Eleanor::$POST['innertitle'][$lng] : array();
			$lvalues['title'][$l]=isset($_POST['title'][$lng]) ? (string)Eleanor::$POST['title'][$lng] : array();
			$lvalues['text'][$l]=isset($_POST['text'][$lng]) ? $Eleanor->Editor_result->GetHtml((string)$_POST['text'][$lng],true) : '';
		}
	}
	else
		$lvalues=array(
			'innertitle'=>array(''=>$_POST['innertitle'] ? (string)Eleanor::$POST['innertitle'] : ''),
			'title'=>array(''=>$_POST['title'] ? (string)Eleanor::$POST['title'] : ''),
			'text'=>array(''=>$Eleanor->Editor_result->GetHtml('text')),
		);

	$ml=in_array('',$langs) ? Language::$main : '';
	foreach(array('innertitle') as $field)
		foreach($lvalues[$field] as $k=>&$v)#Не ставить &$v, иначе в месте 1 (см ниже) после >In($langs), значение получается в пастрофах (Eleanor::$Language['spam']['english']=="'english'"
			if($v=='')
			{
				$er=strtoupper('empty_'.$field.($k ? '_'.$k : ''));
				$errors[$er]=$lang['empty_'.$field]($k);
			}

	if($errors)
		return AddEdit($id,$errors);

	$do=date_offset_get(date_create());
	if($id)
	{
		$task=array('options'=>serialize(array('id'=>$id)),'ondone'=>$values['deleteondone'] ? 'delete' : 'deactivate');
		switch($values['status'])
		{
			case'runned':
				if(in_array($a['status'],array('','stopped','finished')))
				{
					$task+=array(
						'free'=>1,
						'locked'=>0,
						'!nextrun'=>'FROM_UNIXTIME('.Tasks::CalcNextRun(array(),$do).')',
						'maxrun'=>1,
						'alreadyrun'=>0,
						'status'=>1,
						'run_year'=>'*',
						'run_month'=>'*',
						'run_day'=>'*',
						'run_hour'=>'*',
						'run_minute'=>'*',
						'run_second'=>'*',
						'do'=>$do,
						'data'=>serialize(array('lastid'=>0)),
					);
					$values+=array(
						'sent'=>0,
						'!statusdate'=>'NOW()',
					);
				}
				elseif($a['status']=='paused')
				{
					$values+=array('!statusdate'=>'NOW()');
					$task+=array('status'=>1);
				}
			break;
			case'paused':
				if($a['status']=='runned')
				{
					$values+=array('!statusdate'=>'NOW()');
					$task+=array('status'=>0);
				}
			break;
			case'stopped':
			default:
				$task+=array(
					'free'=>1,
					'locked'=>0,
					'!nextrun'=>'FROM_UNIXTIME(0)',
					'maxrun'=>0,
					'alreadyrun'=>0,
					'status'=>0,
					'data'=>serialize(array('lastid'=>0)),
				);
				$values+=array('!statusdate'=>'NOW()');
		}
		Eleanor::$Db->Replace(
			P.'tasks',
			$task+array(
				'id'=>$a['taskid'],
				'task'=>'special_spam.php',
				'title_l'=>serialize(array(''=>'Spam')),
				'name'=>'spam',
				'options'=>serialize(array('id'=>$id)),
				'free'=>1,
				'locked'=>0,
				'!nextrun'=>'FROM_UNIXTIME('.Tasks::CalcNextRun(array(),$do).')',
				'ondone'=>$values['deleteondone'] ? 'delete' : 'deactivate',
				'maxrun'=>1,
				'alreadyrun'=>0,
				'status'=>$values['status']=='runned',
				'run_year'=>'*',
				'run_month'=>'*',
				'run_day'=>'*',
				'run_hour'=>'*',
				'run_minute'=>'*',
				'run_second'=>'*',
				'do'=>$do,
				'data'=>serialize(array('lastid'=>0)),
			)
		);

		Eleanor::$Db->Delete(P.'spam_l','`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		$replace=array();
		foreach($langs as &$v)
			$replace[]=array(
				'id'=>$id,
				'language'=>$v,
				'innertitle'=>isset($lvalues['innertitle'][$v]) ? $lvalues['innertitle'][$v] : '',
				'title'=>isset($lvalues['title'][$v]) ? $lvalues['title'][$v] : '',
				'text'=>isset($lvalues['text'][$v]) ? $lvalues['text'][$v] : '',
			);
		Eleanor::$Db->Replace(P.'spam_l',$replace);
	}
	else
	{
		$values+=array('!statusdate'=>'NOW()');
		Eleanor::$Db->Transaction();#Все ради аплоадера
		$id=Eleanor::$Db->Insert(P.'spam',$values);
		try
		{
			$ft=$Eleanor->Uploader->MoveFiles('spam/'.$id);
		}
		catch(EE$E)
		{
			Eleanor::$Db->Rollback();
			return AddEdit($id,array('ERROR'=>$E->getMessage()));
		}

		$ldb=array('id'=>array(),'language'=>array(),'innertitle'=>array(),'title'=>array(),'text'=>array());
		foreach($langs as &$v)
		{
			$ldb['id'][]=$id;
			$ldb['language'][]=$v;
			$ldb['innertitle'][]=isset($lvalues['innertitle'][$v]) ? $lvalues['innertitle'][$v] : '';
			$ldb['title'][]=isset($lvalues['title'][$v]) ? $lvalues['title'][$v] : '';
			$ldb['text'][]=isset($lvalues['text'][$v]) ? str_replace($ft['from'],$ft['to'],$lvalues['text'][$v]) : '';
		}
		Eleanor::$Db->Insert(P.'spam_l',$ldb);

		$task=array(
			'task'=>'special_spam.php',
			'title_l'=>serialize(array(''=>'Spam')),
			'name'=>'spam',
			'options'=>serialize(array('id'=>$id)),
			'free'=>1,
			'locked'=>0,
			'!nextrun'=>'FROM_UNIXTIME('.Tasks::CalcNextRun(array(),$do).')',
			'ondone'=>$values['deleteondone'] ? 'delete' : 'deactivate',
			'maxrun'=>1,
			'alreadyrun'=>0,
			'status'=>$values['status']=='runned',
			'run_year'=>'*',
			'run_month'=>'*',
			'run_day'=>'*',
			'run_hour'=>'*',
			'run_minute'=>'*',
			'run_second'=>'*',
			'do'=>$do,
			'data'=>serialize(array('lastid'=>0)),
		);
		$tid=Eleanor::$Db->Insert(P.'tasks',$task);
		$values=array('taskid'=>$tid);
		Eleanor::$Db->Commit();
	}
	Eleanor::$Db->Update(P.'spam',$values,'`id`='.$id.' LIMIT 1');
	Tasks::UpdateNextRun();
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}