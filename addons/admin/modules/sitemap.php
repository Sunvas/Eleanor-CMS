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
$lang=Eleanor::$Language->Load('addons/admin/langs/sitemap-*.php','sitemap');
Eleanor::$Template->queue[]='Sitemap';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
	'er'=>$Eleanor->Url->Construct(array('do'=>'editrobots')),
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
		case'editrobots':
			$title[]=$lang['editingr'];
			$f=Eleanor::$root.'robots.txt';
			$saved=false;
			if(isset($_POST['text']) and Eleanor::$our_query)
			{
				file_put_contents($f,(string)$_POST['text']);
				$saved=true;
			}
			$text=is_file($f) ? file_get_contents($f) : '';
			$c=Eleanor::$Template->EditRobots($text,$saved);
			Start();
			echo$c;
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
elseif(isset($_GET['swap']))
{
	$id=(int)$_GET['swap'];
	if(!Eleanor::$our_query)
		return GoAway();
	$R=Eleanor::$Db->Query('SELECT `taskid`,`status` FROM `'.P.'sitemaps` WHERE `id`='.$id.' LIMIT 1');
	if($a=$R->fetch_assoc())
	{
		$ns=$a['status']==0;
		Eleanor::$Db->Update(P.'sitemaps',array('status'=>$ns),'`id`='.$id.' LIMIT 1');
		if($ns)
			Eleanor::$Db->Update(P.'tasks',array('status'=>$ns),'`id`='.$a['taskid'].' AND `name`=\'sitemap\' LIMIT 1');
		else
			Eleanor::$Db->Update(P.'tasks',array('status'=>$ns,'free'=>1,'locked'=>0),'`id`='.$a['taskid'].' AND `name`=\'sitemap\' LIMIT 1');
	}
	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#it'.$id : true);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `title_l` `title`,`taskid`,`file`,`compress` FROM `'.P.'sitemaps` WHERE `id`='.$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	$a['file'].=$a['compress'] ? '.gz' : '.xml';
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'sitemaps','`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Delete(P.'tasks','`id`='.$a['taskid'].' AND `name`=\'sitemap\'');
		Files::Delete(Eleanor::FormatPath($a['file']));
		Tasks::UpdateNextRun();
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
	$title[]=Eleanor::$Language['sitemap']['list'];
	$items=$modules=$tosort=$where=$qs=array();
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		if(isset($_REQUEST['fi']['file']) and $_REQUEST['fi']['file']!=='')
		{
			$qs['']['fi']['file']=$_REQUEST['fi']['file'];
			$where[]='`s`.`file` LIKE \'%'.Eleanor::$Db->Escape($qs['']['fi']['file'],false).'%\'';
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'sitemaps` `s`'.$where);
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
	if(!in_array($sort,array('id','file','status')))
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
				$R=Eleanor::$Db->Query('SELECT `id`,`taskid`,`file`,`compress` FROM `'.P.'sitemaps` WHERE `id`'.Eleanor::$Db->In($_POST['mass']));
				while($a=$R->fetch_assoc())
				{
					$ids[]=$a['id'];
					$tids[]=$a['taskid'];
					$a['file'].=$a['compress'] ? '.gz' : '.xml';
					Files::Delete(Eleanor::FormatPath($a['file']));
				}
				$ids_=Eleanor::$Db->In($ids);
				Eleanor::$Db->Delete(P.'sitemaps','`id`'.$ids_);
				Eleanor::$Db->Delete(P.'tasks','`id`'.Eleanor::$Db->In($tids).' AND `name`=\'sitemap\'');
		}

	if($cnt>0)
	{
		$R=Eleanor::$Db->Query('SELECT `s`.`id`,`s`.`title_l` `title`,`s`.`modules`,`s`.`taskid`,`s`.`total`,`s`.`already`,`s`.`file`,`s`.`compress`,`s`.`status`,`t`.`lastrun`,`t`.`nextrun`,`t`.`free` FROM `'.P.'sitemaps` `s` LEFT JOIN `'.P.'tasks` `t` ON `t`.`id`=`s`.`taskid`'.$where.' ORDER BY `s`.`'.$sort.'` '.$so.' LIMIT '.$offset.','.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['modules']=$a['modules'] ? explode(',,',trim($a['modules'],',')) : array();
			if($a['modules'])
				$modules=array_merge($modules,$a['modules']);
			if(!$a['modules'] or $a['lastrun']===null)
			{
				Eleanor::$Db->Update(P.'sitemaps',array('status'=>0),'`id`='.$a['id'].' LIMIT 1');
				Eleanor::$Db->Update(P.'tasks',array('status'=>0),'`id`='.$a['taskid'].' LIMIT 1');
				$a['status']=0;
			}
			$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
			$a['file'].=$a['compress'] ? '.gz' : '.xml';
			if($sot)
				$tosort[]=$a['title'];
			if($a['free'] and time()>=strtotime($a['nextrun']))
				$a['free']=false;
			if($a['free']===null)
				$a['free']=true;

			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			$a['_aswap']=$Eleanor->Url->Construct(array('swap'=>$a['id']));

			$items[$a['id']]=array_slice($a,1);
		}
	}
	if($sot and $tosort)
	{
		asort($tosort,SORT_STRING);
		$newit=array();
		foreach($tosort as $k=>&$v)
			$newit[]=$items[$k];
		$items=$newit;
	}
	if($modules)
	{
		$modules=array_unique($modules);
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'modules` WHERE `id`'.Eleanor::$Db->In($modules));
		$modules=array();
		while($a=$R->fetch_assoc())
			$modules[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
	}

	$links=array(
		'sort_status'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'status','so'=>$qs['sort']=='status' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_file'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'file','so'=>$qs['sort']=='file' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->ShowList($items,$cnt,$modules,$page,$pp,$qs,$links);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['sitemap'];
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT `title_l`,`modules`,`taskid`,`file`,`compress`,`fulllink`,`sendservice`,`per_time`,`status` FROM `'.P.'sitemaps` WHERE id='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway(true);
			$values['title_l']=$values['title_l'] ? (array)unserialize($values['title_l']) : array();
			$values['modules']=$values['modules'] ? explode(',,',trim($values['modules'],',')) : array();
			$values['sendservice']=$values['sendservice'] ? explode(',,',trim($values['sendservice'],',')) : array();
			$values['_recreate']=$values['_runnow']=false;
			$R=Eleanor::$Db->Query('SELECT `options`,`run_year`,`run_month`,`run_day`,`run_hour`,`run_minute`,`run_second` FROM `'.P.'tasks` WHERE id='.(int)$values['taskid'].' LIMIT 1');
			if($R->num_rows>0)
			{
				$values+=$R->fetch_assoc();
				$values['options']=$values['options'] ? (array)unserialize($values['options']) : array();
			}
			else
				$values+=array(
					'options'=>array(),
					'run_year'=>'*',
					'run_month'=>'*',
					'run_day'=>'*',
					'run_hour'=>0,
					'run_minute'=>0,
					'run_second'=>0,
				);
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$values=array(
			'title_l'=>array(''=>''),
			'modules'=>array(),
			'file'=>'',
			'compress'=>true,
			'fulllink'=>true,
			'status'=>true,
			'per_time'=>1000,
			'sendservice'=>true,

			'options'=>array(),
			'run_year'=>'*',
			'run_month'=>'*',
			'run_day'=>'*',
			'run_hour'=>0,
			'run_minute'=>0,
			'run_second'=>0,
			'_runnow'=>true,
		);
		$title[]=$lang['adding'];
	}
	$bypost=false;
	if($errors)
	{
		$bypost=true;
		if($errors===true)
			$errors=array();
		$values['modules']=isset($_POST['modules']) ? (array)$_POST['modules'] : array();
		$values['file']=isset($_POST['file']) ? (string)$_POST['file'] : '';
		$values['compress']=isset($_POST['compress']);
		$values['fulllink']=isset($_POST['fulllink']);
		$values['status']=isset($_POST['status']);
		$values['per_time']=isset($_POST['per_time']) ? (int)$_POST['per_time'] : '';
		$values['sendservice']=isset($_POST['sendservice']) ? (array)$_POST['sendservice'] : array();

		$values['run_year']=isset($_POST['run_year']) ? (string)$_POST['run_year'] : '';
		$values['run_month']=isset($_POST['run_month']) ? (string)$_POST['run_month'] : '';
		$values['run_day']=isset($_POST['run_day']) ? (string)$_POST['run_day'] : '';
		$values['run_hour']=isset($_POST['run_hour']) ? (string)$_POST['run_hour'] : '';
		$values['run_minute']=isset($_POST['run_minute']) ? (string)$_POST['run_minute'] : '';
		$values['run_second']=isset($_POST['run_second']) ? (string)$_POST['run_second'] : '';
		$values['_recreate']=isset($_POST['_recreate']);
		$values['_runnow']=isset($_POST['_runnow']);

		if(Eleanor::$vars['multilang'])
			foreach(Eleanor::$langs as $k=>&$v)
				$values['title_l'][$k]=isset($_POST['title_l'][$k]) ? $_POST['title_l'][$k] : '';
		else
			$values['title_l']=array(''=>isset($_POST['title_l']) ? $_POST['title_l'] : '');
	}
	$modules=$settings=array();
	$C=new Controls;
	$R=Eleanor::$Db->Query('SELECT `id`,`title_l`,`descr_l`,`path`,`api` FROM `'.P.'modules` WHERE `api`!=\'\'');
	while($a=$R->fetch_assoc())
	{
		$api=Eleanor::FormatPath($a['api'],$a['path']);
		$class='Api'.basename(dirname($api));
		do
		{
			if(class_exists($class,false))
				break;
			if(is_file($api))
			{
				include$api;
				if(class_exists($class,false))
					break;
			}
			continue 2;
		}while(false);
		if(!method_exists($class,'SitemapGenerate'))
			continue;
		$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
		$modules[$a['id']]=$a['title_l'];
		if(method_exists($class,'SitemapConfigure') and in_array($a['id'],$values['modules']))
		{
			$a['descr_l']=$a['descr_l'] ? Eleanor::FilterLangValues((array)unserialize($a['descr_l'])) : '';

			$Api=new$class;
			$conf=$Api->SitemapConfigure($bypost);
			$C->arrname=array('module'.$a['id']);

			$sett=$ovalues=array();
			if(isset($values['options']['m'][$a['id']]))
				foreach($values['options']['m'][$a['id']] as $k=>&$v)
					$ovalues[$k]=array('value'=>$v);

			$error=false;
			try
			{
				$sett=$C->DisplayControls($conf,$ovalues);
			}
			catch(EE$E)
			{
				$error=$E->getMessage();
			}

			$settings[]=array(
				'id'=>$a['id'],
				't'=>$a['title_l'],
				'd'=>$a['descr_l'],
				'c'=>$conf,
				's'=>$sett,
				'e'=>$error,
			);
		}
	}
	unset($Api);
	asort($modules,SORT_STRING);
	$values['_recreate']=isset($_POST['_recreate']);
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	$c=Eleanor::$Template->AddEdit($id,$values,$modules,$settings,$errors,$bypost,$links,$back);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$lang=Eleanor::$Language['sitemap'];
	$values=array(
		'modules'=>isset($_POST['modules']) ? (array)$_POST['modules'] : false,
		'file'=>isset($_POST['file']) ? (string)$_POST['file'] : '',
		'compress'=>isset($_POST['compress']),
		'fulllink'=>isset($_POST['fulllink']),
		'sendservice'=>isset($_POST['sendservice']) ? ','.join(',,',(array)$_POST['sendservice']).',' : '',
		'status'=>isset($_POST['status']),
		'per_time'=>isset($_POST['per_time']) ? (int)$_POST['per_time'] : 1000,
	);
	if($values['per_time']<10)
		$values['per_time']=10;

	$errors=array();
	if($values['file']=='')
		$errors[]='NOFILE';
	if(!is_writeable(Eleanor::$root))
		$errors['UNABLE_CREATE_FILE']=sprintf($lang['unacr'],$values['file'].($values['compress'] ? '.xml.gz' : '.xml'));

	if(Eleanor::$vars['multilang'])
		$values['title_l']=isset($_POST['title_l']) ? (array)Eleanor::$POST['title_l'] : array();
	else
		$values['title_l']=isset($_POST['title_l']) ? array(''=>(string)Eleanor::$POST['title_l']) : array();
	$values['title_l']=serialize($values['title_l']);

	$do=date_offset_get(date_create());
	$tvalues=array(
		'task'=>'special_sitemap.php',
		'title_l'=>$values['title_l'],
		'name'=>'sitemap',
		'ondone'=>'deactivate',
		'status'=>$values['status'],
		'run_year'=>isset($_POST['run_year']) ? (string)$_POST['run_year'] : '',
		'run_month'=>isset($_POST['run_month']) ? (string)$_POST['run_month'] : '',
		'run_day'=>isset($_POST['run_day']) ? (string)$_POST['run_day'] : '',
		'run_hour'=>isset($_POST['run_hour']) ? (string)$_POST['run_hour'] : '',
		'run_minute'=>isset($_POST['run_minute']) ? (string)$_POST['run_minute'] : '',
		'run_second'=>isset($_POST['run_second']) ? (string)$_POST['run_second'] : '',
		'do'=>$do,
	);
	$nr=isset($_POST['_runnow']) ? time() : Tasks::CalcNextRun(array(
		'year'=>$tvalues['run_year'],
		'month'=>$tvalues['run_month'],
		'day'=>$tvalues['run_day'],
		'hour'=>$tvalues['run_hour'],
		'minute'=>$tvalues['run_minute'],
		'second'=>$tvalues['run_second'],
	),$do);

	if($nr===false)
		$errors[]='NO_NEXT_RUN';
	$tvalues['!nextrun']='FROM_UNIXTIME('.(int)$nr.')';
	if(!$values['modules'])
		$errors[]='NOMODULES';

	if(isset($_POST['_recreate']))
	{
		$tvalues['data']='';
		$values['total']=$values['already']=0;
		$f=Eleanor::$root.$values['file'].'.xml';
		Files::Delete($f);
		if($values['compress'])
			Files::Delete($f.'.gz');
	}

	$R=Eleanor::$Db->Query('SELECT `id`,`path`,`api` FROM `'.P.'modules` WHERE `api`!=\'\' AND `id`'.Eleanor::$Db->In($values['modules']));
	$options=$values['modules']=array();
	while($a=$R->fetch_assoc())
	{
		$api=Eleanor::FormatPath($a['api'],$a['path']);
		$class='Api'.basename(dirname($api));
		do
		{
			if(class_exists($class,false))
				break;
			if(is_file($api))
			{
				include$api;
				if(class_exists($class,false))
					break;
			}
			continue 2;
		}while(false);
		if(!method_exists($class,'SitemapGenerate'))
			continue;
		$values['modules'][]=$a['id'];
		if(method_exists($class,'SitemapConfigure'))
		{
			$Api=new$class;
			$conf=$Api->SitemapConfigure($p=false);
			$Eleanor->Controls->arrname=array('module'.$a['id']);
			try
			{
				$options['m'][$a['id']]=$Eleanor->Controls->SaveControls($conf);
			}
			catch(EE$E)
			{
				return AddEdit($id,array('ERROR'=>$E->getMessage()));
			}
		}
	}
	if($values['modules'])
		$values['modules']=','.join(',,',$values['modules']).',';
	else
		$errors[]='NOMODULES';

	if($errors)
		return AddEdit($id,array_unique($errors));

	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `taskid` FROM `'.P.'sitemaps` WHERE id='.$id.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway();
		$options['id']=$id;
		$tvalues['options']=serialize($options);
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'tasks` WHERE `id`='.$a['taskid'].' LIMIT 1');
		if($R->num_rows>0)
			Eleanor::$Db->Update(P.'tasks',$tvalues,'`id`='.$a['taskid'].' LIMIT 1');
		else
			$values['taskid']=Eleanor::$Db->Insert(P.'tasks',$tvalues+array('free'=>1,'locked'=>0,));
		Eleanor::$Db->Update(P.'sitemaps',$values,'`id`='.$id.' LIMIT 1');
	}
	else
	{
		$values['taskid']=Eleanor::$Db->Insert(P.'tasks',$tvalues+array('free'=>1,'locked'=>0,));
		$options['id']=Eleanor::$Db->Insert(P.'sitemaps',$values);
		$options=serialize($options);
		Eleanor::$Db->Update(P.'tasks',array('options'=>$options),'`id`='.$values['taskid'].' LIMIT 1');
	}
	Tasks::UpdateNextRun();
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}