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
if(!defined('CMS'))die;
global$Eleanor,$title;
$lang=Eleanor::$Language->Load('addons/admin/langs/database-*.php','db');
Eleanor::$Template->queue[]='Database';

$Eleanor->module['links']=array(
	'br'=>$Eleanor->Url->Prefix(),
	'rn'=>$Eleanor->Url->Construct(array('do'=>'recovernames')),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'recovernames':
			$title[]=$lang['recovernames'];
			$R=Eleanor::$Db->Query('SELECT COUNT(`name`) FROM `'.P.'tasks` WHERE `name`=\'recovernames\'');
			list($cnt)=$R->fetch_row();

			$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
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

			$items=array();
			if($cnt>0)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`options`,`lastrun`,`status`,`data` FROM `'.P.'tasks` WHERE `name`=\'recovernames\' ORDER BY `lastrun` DESC LIMIT '.$offset.','.$pp);
				while($a=$R->fetch_assoc())
				{
					$a['data']=(array)unserialize($a['data']);
					$a['options']=(array)unserialize($a['options']);

					$a['_aswap']=empty($a['options']['total']) ? false : $Eleanor->Url->Construct(array('swap'=>$a['id']));
					$a['_aedit']=$a['status'] ? false : $Eleanor->Url->Construct(array('edit'=>$a['id']));
					$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));

					$items[$a['id']]=array_slice($a,1);
				}
			}
			$links=array(
				'first_page'=>$Eleanor->Url->Construct(array('do'=>'recovernames')),
				'pages'=>function($n){ return$GLOBALS['Eleanor']->Url->Construct(array('do'=>'recovernames','page'=>$n)); },
				'pp'=>function($n){ return$GLOBALS['Eleanor']->Url->Construct(array('do'=>'recovernames','new-pp'=>$n)); },
			);
			$c=Eleanor::$Template->ShowList($items,$cnt,$page,$pp,$links);
			Start();
			echo$c;
		break;
		case'add':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				Save(0);
			else
				AddEdit(0);
		break;
		default:
			BackupAndRecovery();
	}
elseif(isset($_GET['swap']))
{
	$id=(int)$_GET['swap'];
	if(Eleanor::$our_query)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`options`,`status` FROM `'.P.'tasks` WHERE `id`='.$id.' AND `name`=\'recovernames\' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			if($a['status'])
				$a=array('status'=>0);
			else
			{
				$do=date_offset_get(date_create());
				$a['options']=unserialize($a['options']);
				$a=array(
					'free'=>1,
					'!nextrun'=>'FROM_UNIXTIME('.Tasks::CalcNextRun(array(),$do).')',
					'maxrun'=>1,
					'alreadyrun'=>0,
					'status'=>$a['options']['total']>0,
					'run_year'=>'*',
					'run_month'=>'*',
					'run_day'=>'*',
					'run_hour'=>'*',
					'run_minute'=>'*',
					'run_second'=>'*',
					'do'=>$do,
					'data'=>serialize(array('total'=>0,'updated'=>0,'done'=>false)),
				);
			}
			Eleanor::$Db->Update(P.'tasks',$a,'`id`='.$id.' AND `name`=\'recovernames\' LIMIT 1');
			if($a['status'])
				Tasks::UpdateNextRun();
		}
	}
	GoAway();
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		Save($id);
	else
		AddEdit($id);
}
elseif(isset($_GET['delete']))
{
	if(Eleanor::$our_query)
		Eleanor::$Db->Delete(P.'tasks','`name`=\'recovernames\' AND `id`='.(int)$_GET['delete'].' LIMIT 1');
	return GoAway();
}
else
	BackupAndRecovery();

function BackupAndRecovery()
{global$title;
	$title[]=Eleanor::$Language['db']['backup&recovery'];
	Eleanor::StartSession();
	$_SESSION['EleanorCMS4sypex']=array(
		'c'=>Eleanor::$root.'config_general.php',
		'b'=>Eleanor::$root.'addons'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR,
		'bu'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'addons/backups/',
		'e'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->file.'?section=management',
		'l'=>substr(Language::$main,0,2),
	);
	$c=Eleanor::$Template->Sypex();
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$title;
	$lang=Eleanor::$Language['db'];
	$runned=false;
	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`options`,`ondone`,`status` FROM `'.P.'tasks` WHERE `id`='.$id.' AND `name`=\'recovernames\' LIMIT 1');
		if(!$a=$R->fetch_assoc() or !$values=unserialize($a['options']) or !isset($values['tables'],$values['ids'],$values['names']))
			return GoAway();
		$values['status']=$a['status'];
		$values['delete']=$a['ondone']=='delete';
		$values['tables']=array_keys($values['tables']);
		if($a['status'])
			$runned=true;

		$title[]=$lang['editing'];
	}
	else
	{
		$values=array(
			'tables'=>array(),
			'ids'=>array('author_id'),
			'names'=>array('author'),
			'per_load'=>100,
			'status'=>true,
			'delete'=>true,
		);
		$title[]=$lang['adding'];
	}

	if($errors)
	{
		if($errors===true)
			$errors=array();
		$values['tables']=isset($_POST['tables']) ? (array)$_POST['tables'] : array();
		$values['ids']=isset($_POST['ids']) ? (array)$_POST['ids'] : array();
		$values['names']=isset($_POST['names']) ? (array)$_POST['names'] : array();
		$values['status']=isset($_POST['status']);
		$values['delete']=isset($_POST['delete']);
		$values['per_load']=isset($_POST['per_load']) ? (int)$_POST['per_load'] : 100;
	}

	$tables=array();
	$R=Eleanor::$Db->Query('SHOW TABLES FROM `'.Eleanor::$Db->db.'`');
	while(list($t)=$R->fetch_row())
		$tables[]=$t;

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id)) : false,
	);
	$c=Eleanor::$Template->AddEdit($id,$tables,$values,$runned,$errors,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{
	$lang=Eleanor::$Language['db'];
	$tables=isset($_POST['tables']) ? (array)$_POST['tables'] : array();
	if(!$tables)
		return AddEdit($id,array('NO_TABLES'));
	$ids=isset($_POST['ids']) ? (array)$_POST['ids'] : array();
	$names=isset($_POST['names']) ? (array)$_POST['names'] : array();
	$opts=$errors=array();
	$total=0;
	foreach($tables as &$t)
	{
		$opts[$t]=array();
		foreach($ids as $k=>&$idv)
			if(isset($names[$k]) and $idv!='' and $t!='' and $names[$k]!='')
			{
				$fidv=Eleanor::$Db->Escape($idv,false);
				$ft=Eleanor::$Db->Escape($t,false);
				try
				{
					$R=Eleanor::$Db->Query('SELECT COUNT(`'.$fidv.'`) FROM (SELECT `'.$fidv.'`, COUNT(`'.$fidv.'`) `cnt` FROM `'.$ft.'` WHERE `'.$fidv.'`!=0 GROUP BY `'.$fidv.'`) `t`');
					list($cnt)=$R->fetch_row();
					$opts[$t][$idv]=$cnt;
				}
				catch(EE_SQL$E)
				{
					$errors[$t.$k]=sprintf($lang['errort'],$t,$idv,$names[$k],$E->getMessage());
				}
			}
		$total+=array_sum($opts[$t]);
	}
	if($errors)
		return AddEdit($id,$errors);
	$per_load=isset($_POST['per_load']) ? (int)$_POST['per_load'] : 100;
	if($per_load<1)
		$per_load=1;
	$do=date_offset_get(date_create());
	$values=array(
		'task'=>'special_recovernames.php',
		'title_l'=>serialize(array(''=>'Recover Names')),
		'name'=>'recovernames',
		'options'=>serialize(array('tables'=>$opts,'ids'=>$ids,'names'=>$names,'total'=>$total,'per_load'=>$per_load)),
		'free'=>1,
		'!nextrun'=>'FROM_UNIXTIME('.Tasks::CalcNextRun(array(),$do).')',
		'ondone'=>isset($_POST['delete']) ? 'delete' : 'deactivate',
		'maxrun'=>1,
		'alreadyrun'=>0,
		'status'=>isset($_POST['status']),
		'run_year'=>'*',
		'run_month'=>'*',
		'run_day'=>'*',
		'run_hour'=>'*',
		'run_minute'=>'*',
		'run_second'=>'*',
		'do'=>$do,
		'data'=>serialize(array('total'=>0,'updated'=>0,'done'=>false)),
	);
	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `status` FROM `'.P.'tasks` WHERE `id`='.$id.' AND `name`=\'recovernames\' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway();
		if($a['status'])
			return AddEdit($id,array('RUNNED'));
		Eleanor::$Db->Update(P.'tasks',$values,'`id`='.$id.' AND `name`=\'recovernames\' LIMIT 1');
	}
	else
		Eleanor::$Db->Insert(P.'tasks',$values);
	Tasks::UpdateNextRun();
	GoAway(empty($_POST['back']) ? array('do'=>'recovernames') : $_POST['back']);
}