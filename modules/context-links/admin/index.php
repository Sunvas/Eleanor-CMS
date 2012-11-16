<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym. See paramss/copyrights/info.txt for more information.
*/
if(!defined('CMS'))die;
global$Eleanor;

$Eleanor->module['config']=include($Eleanor->module['path'].'config.php');
Eleanor::$Template->queue[]=$Eleanor->module['config']['admintpl'];
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_admin-*.php',$Eleanor->module['config']['n']);

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
);
$Eleanor->sc_post=false;
$Eleanor->sc=array(
	'from'=>array(
		'title'=>$lang['from'],
		'descr'=>$lang['from_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>1
			),
		),
	),
	'to'=>array(
		'title'=>$lang['to'],
		'descr'=>$lang['to_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>3
			),
		),
	),
	'regexp'=>array(
		'title'=>$lang['reg'],
		'descr'=>$lang['reg_'],
		'type'=>'check',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'save'=>function($a,$Obj,$controls) use ($lang)
		{
			if($a['multilang'])
			{
				foreach($a['value'] as $k=>&$v)
					if($v and $controls['from'][$k]!='')
					{						Eleanor::$nolog=true;
						preg_replace($controls['from'][$k],'','text');
						Eleanor::$nolog=false;
						if(preg_last_error()!=PREG_NO_ERROR)
							$Obj->errors['REG_ERROR']=$lang['rege'];
					}
				return$a['value'];
			}
			if($a['value'] and $controls['from']!='')
			{				Eleanor::$nolog=true;
				preg_replace($controls['from'],'','text');
				Eleanor::$nolog=false;
				if(preg_last_error()!=PREG_NO_ERROR)
					$Obj->errors['REG_ERROR']=$lang['rege'];
			}
			return$a['value'];
		},
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>2
			),
		),
	),
	'url'=>array(
		'title'=>$lang['url'],
		'descr'=>$lang['url_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>4,
			),
		),
	),
	'eval_url'=>array(
		'title'=>$lang['eval_url'],
		'descr'=>$lang['eval_url_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>5,
			),
		),
	),
	'params'=>array(
		'title'=>$lang['params'],
		'descr'=>$lang['params_'],
		'save'=>function($a)
		{
			if($a['multilang'])
			{
				foreach($a['value'] as &$v)
					$v=$v ? ' '.trim($v) : '';
				return$a['value'];
			}
			return$a['value'] ? ' '.trim($a['value']) : '';
		},
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>6,
			),
		),
	),
	'date_from'=>array(
		'title'=>$lang['date_from'],
		'descr'=>'',
		'type'=>'date',
		'load'=>'LoadDate',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'time'=>true,
			'extra'=>array(
				'tabindex'=>7,
			),
		),
	),
	'date_till'=>array(
		'title'=>$lang['date_till'],
		'descr'=>'',
		'type'=>'date',
		'load'=>'LoadDate',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'time'=>true,
			'extra'=>array(
				'tabindex'=>8,
			),
		),
	),
	'status'=>array(
		'title'=>$lang['activate'],
		'descr'=>'',
		'default'=>true,
		'type'=>'check',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'extra'=>array(
				'tabindex'=>9,
			),
		),
	),
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
		case'draft':
			$id=isset($_POST['_draft']) ? (int)$_POST['_draft'] : 0;
			unset($_POST['_draft'],$_POST['back']);
			Eleanor::$Db->Replace(P.'drafts',array('key'=>$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id,'value'=>serialize($_POST)));
			Eleanor::$content_type='text/plain';
			Start('');
			echo'ok';
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
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	if(!Eleanor::$our_query)
		return GoAway();
	Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`='.$id.' LIMIT 1');
	Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`='.$id);
	Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
	Eleanor::$Cache->Lib->CleanByTag($Eleanor->module['config']['n']);
	GoAway();
}
elseif(isset($_GET['swap']))
{
	$id=(int)$_GET['swap'];
	if(Eleanor::$our_query)
	{
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!status'=>'NOT `status`'),'`id`='.$id.' LIMIT 1');
		Eleanor::$Cache->Lib->CleanByTag($Eleanor->module['config']['n']);
	}
	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#it'.$id : true);
}
else
	ShowList();

function LoadDate($a)
{
	return array('value'=>(int)$a['value']>0 ? $a['value'] : '');
}

function ShowList()
{global$Eleanor,$title;
	$title=Eleanor::$Language[$Eleanor->module['config']['n']]['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$where=$qs=array();
	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		if(isset($_REQUEST['fi']['from']) and $_REQUEST['fi']['from']!='')
		{
			$qs['']['fi']['from']=$_REQUEST['fi']['from'];
			$where[]='`from` LIKE \'%'.Eleanor::$Db->Escape($qs['']['fi']['from'],false).'%\'';
		}
	}

	$where[]='`language` IN (\'\',\''.Language::$main.'\')';
	$where=' WHERE '.join(' AND ',$where);
	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']))
	{		$in=Eleanor::$Db->In($_POST['mass']);
		switch($_POST['op'])
		{
			case'k':
				Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`'.$in);
				Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`'.$in);
			break;
			case'a':
				Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>1),'`id`'.$in);
			break;
			case'd':
				Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>0),'`id`'.$in);
			break;
			case's':
				Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!status'=>'NOT `status`'),'`id`'.$in);
		}
	}
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`)'.$where);
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
	$sort=isset($_GET['sort']) ? $_GET['sort'] : '';
	if(!in_array($sort,array('id','from','to','status','date_from','date_till')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? $_GET['so'] : 'asc';
	if($so!='asc')
		$so='desc';
	if($sort and ($sort!='id' or $so!='asc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	$items=array();
	if($cnt>0)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`date_from`,`date_till`,`status`,`from`,`to` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`)'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			$a['_aswap']=$Eleanor->Url->Construct(array('swap'=>$a['id']));

			$items[$a['id']]=array_slice($a,1);
		}
	}

	$links=array(
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_from'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'from','so'=>$qs['sort']=='from' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_to'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'to','so'=>$qs['sort']=='to' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_date_from'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'date_from','so'=>$qs['sort']=='date_from' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_date_till'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'date_till','so'=>$qs['sort']=='date_till' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_status'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'status','so'=>$qs['sort']=='status' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->ShowList($items,$cnt,$pp,$qs,$page,$links);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	$values=array();
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.$Eleanor->module['config']['t'].'` WHERE id='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			foreach($a as $k=>&$v)
				if(isset($Eleanor->sc[$k]))
					$values[$k]['value']=$v;
			$R=Eleanor::$Db->Query('SELECT `language`,`from`,`regexp`,`to`,`url`,`eval_url`,`params` FROM `'.$Eleanor->module['config']['tl'].'` WHERE `id`='.$id);
			while($temp=$R->fetch_assoc())
				if(!Eleanor::$vars['multilang'] and (!$temp['language'] or $temp['language']==Language::$main))
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk]['value']=$tv;
					if(!$temp['language'])
						break;
				}
				elseif(!$temp['language'] and Eleanor::$vars['multilang'])
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk]['value'][Language::$main]=$tv;
					$values['_onelang']=true;
					break;
				}
				elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$temp['language']]))
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk]['value'][$temp['language']]=$tv;
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
		if(Eleanor::$vars['multilang'])
		{
			$values['_onelang']=true;
			$values['_langs']=array_keys(Eleanor::$langs);
		}
	}

	$hasdraft=false;
	if(!$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
		if($draft=$R->fetch_row() and $draft[0])
		{
			$hasdraft=true;
			$_POST+=(array)unserialize($draft[0]);
			$errors=true;
		}
	}

	if($errors)
	{
		if($errors===true)
			$errors=array();
		$Eleanor->sc_post=true;
		if(Eleanor::$vars['multilang'])
		{
			$values['_onelang']=isset($_POST['_onelang']);
			$values['_langs']=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array(Language::$main);
		}
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$values=$Eleanor->Controls->DisplayControls($Eleanor->sc,$values)+$values;
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
		'nodraft'=>$hasdraft ? $Eleanor->Url->Construct(array('do'=>$id ? false : 'add','edit'=>$id ? $id : false,'nodraft'=>1)) : false,
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);
	$c=Eleanor::$Template->AddEdit($id,$Eleanor->sc,$values,$errors,$back,$hasdraft,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
	{
		$langs=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array();
		$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
		if(!$langs)
			$langs=array(Language::$main);
	}
	else
		$langs=array('');

	$C=new Controls;
	$C->langs=$langs;
	$C->throw=false;
	try
	{
		$values=$C->SaveControls($Eleanor->sc);
	}
	catch(EE$E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}
	$errors=$C->errors;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];

	if(Eleanor::$vars['multilang'])
		$lvalues=array(
			'from'=>$values['from'],
			'regexp'=>$values['regexp'],
			'to'=>$values['to'],
			'url'=>$values['url'],
			'eval_url'=>$values['eval_url'],
			'params'=>$values['params'],
		);
	else
		$lvalues=array(
			'from'=>array(''=>$values['from']),
			'regexp'=>array(''=>$values['regexp']),
			'to'=>array(''=>$values['to']),
			'url'=>array(''=>$values['url']),
			'eval_url'=>array(''=>$values['eval_url']),
			'params'=>array(''=>$values['params']),
		);
	unset($values['to'],$values['from'],$values['regexp'],$values['url'],$values['eval_url'],$values['params']);

	$ml=in_array('',$langs) ? Language::$main : '';
	foreach($lvalues['eval_url'] as $k=>&$v)
	{		$er=$k ? '_'.strtoupper($k) : '';
		if($v)
		{
			ob_start();
			if(create_function('$Eleanor',$v)===false)
			{
				$err=ob_get_contents();
				ob_end_clean();
				$Eleanor->e_g_l=error_get_last();
				$errors['ERROR_EVAL_URL'.$er]=$err;
			}
			ob_end_clean();
		}

		if(in_array($k,$langs) or $ml==$k)
		{
			if(!$lvalues['from'][$k])
				$errors['EMPTY_FROM'.$er]=$lang['EMPTY_FROM']($k);

			if(!$v and !$lvalues['url'][$k])
				$errors['EMPTY_LINK'.$er]=$lang['EMPTY_LINK']($k);
		}
	}

	if($errors)
		return AddEdit($id,$errors);

	Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
	if($id)
	{
		Eleanor::$Db->Update($Eleanor->module['config']['t'],$values,'id='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		$values=array();
		foreach($langs as &$v)
			$values[]=array(
				'id'=>$id,
				'language'=>$v,
				'from'=>isset($lvalues['from'][$v]) ? $lvalues['from'][$v] : '',
				'regexp'=>isset($lvalues['regexp'][$v]) ? $lvalues['regexp'][$v] : '',
				'to'=>isset($lvalues['to'][$v]) ? $lvalues['to'][$v] : '',
				'url'=>isset($lvalues['url'][$v]) ? $lvalues['url'][$v] : '',
				'eval_url'=>isset($lvalues['eval_url'][$v]) ? $lvalues['eval_url'][$v] : '',
				'params'=>isset($lvalues['params'][$v]) ? $lvalues['params'][$v] : '',
			);
		Eleanor::$Db->Replace($Eleanor->module['config']['tl'],$values);
	}
	else
	{
		$id=Eleanor::$Db->Insert($Eleanor->module['config']['t'],$values);
		$values=array('id'=>array(),'language'=>array(),'from'=>array(),'to'=>array(),'url'=>array(),'eval_url'=>array(),'params'=>array());
		foreach($langs as &$v)
		{
			$values['id'][]=$id;
			$values['language'][]=$v;
			$values['from'][]=isset($lvalues['from'][$v]) ? $lvalues['from'][$v] : '';
			$values['regexp'][]=isset($lvalues['regexp'][$v]) ? $lvalues['regexp'][$v] : '';
			$values['to'][]=isset($lvalues['to'][$v]) ? $lvalues['to'][$v] : '';
			$values['url'][]=isset($lvalues['url'][$v]) ? $lvalues['url'][$v] : '';
			$values['eval_url'][]=isset($lvalues['eval_url'][$v]) ? $lvalues['eval_url'][$v] : '';
			$values['params'][]=isset($lvalues['params'][$v]) ? $lvalues['params'][$v] : '';
		}
		Eleanor::$Db->Insert($Eleanor->module['config']['tl'],$values);
	}
	Eleanor::$Cache->Lib->CleanByTag($Eleanor->module['config']['n']);
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}