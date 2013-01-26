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
$Eleanor->module['config']=$mc=include($Eleanor->module['path'].'config.php');
Eleanor::$Template->queue[]=$mc['admintpl'];

$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php',$mc['n']);
Eleanor::LoadOptions($mc['opts'],false);

$Eleanor->Categories=new Categories_manager;
$Eleanor->Categories->table=$mc['c'];
$Eleanor->Categories->ondelete='DelCategories';

$Eleanor->sc_post=false;
$Eleanor->sc=array(
	'language'=>Eleanor::$vars['multilang'] ? array(
		'title'=>$lang['language'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'callback'=>function($a) use ($lang)
			{
				$sel=Eleanor::Option($lang['forallt'],'',in_array('',$a['value']));
				foreach(Eleanor::$langs as $k=>&$v)
					$sel.=Eleanor::Option($v['name'],$k,in_array($k,$a['value']));
				return$sel;
			},
			'extra'=>array(
				'tabindex'=>1
			),
		),
	) : null,
	'name'=>array(
		'title'=>$lang['tname'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>2
			),
		),
	),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'tags':
			$title[]=$lang['tags_list'];
			$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$items=$where=$langs=array();
			$qs=array('do'=>'tags');
			if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
			{
				if($_SERVER['REQUEST_METHOD']=='POST')
					$page=1;
				$qs['']['fi']=array();
				if(isset($_REQUEST['fi']['name'],$_REQUEST['fi']['namet']) and $_REQUEST['fi']['name']!='')
				{
					$name=Eleanor::$Db->Escape((string)$_REQUEST['fi']['name'],false);
					switch($_REQUEST['fi']['namet'])
					{
						case'b':
							$name=' LIKE \''.$name.'%\'';
						break;
						case'm':
							$name=' LIKE \'%'.$name.'%\'';
						break;
						case'e':
							$name=' LIKE \'%'.$name.'\'';
						break;
						default:
							$name='=\''.$name.'\'';
					}
					$qs['']['fi']['name']=$_REQUEST['fi']['name'];
					$qs['']['fi']['namet']=$_REQUEST['fi']['namet'];
					$where[]='`name`'.$name;
				}
				if(Eleanor::$vars['multilang'] and isset($_REQUEST['fi']['language']))
				{
					$qs['']['fi']['language']=(array)$_REQUEST['fi']['language'];
					$where[]='`language`'.Eleanor::$Db->In($qs['']['fi']['language']);
				}
				if(isset($_REQUEST['fi']['cntf']) or isset($_REQUEST['fi']['cntt']))
				{
					$f=isset($_REQUEST['fi']['cntf']) ? (int)$_REQUEST['fi']['cntf'] : 0;
					$t=isset($_REQUEST['fi']['cntt']) ? (int)$_REQUEST['fi']['cntt'] : 0;
					$where[]='`cnt` BETWEEN '.min($f,$t).' AND '.max($f,$t);
					if($f>0 or $t==0)
						$qs['']['fi']['cntf']=$f;
					if($t>0)
						$qs['']['fi']['cntt']=$t;
				}
			}

			$where=$where ? ' WHERE '.join(' AND ',$where) : '';
			if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']))
				switch($_POST['op'])
				{
					case'k':
						$in=Eleanor::$Db->In($_POST['mass']);
						Eleanor::$Db->Delete($mc['tt'],'`id`'.$in);
						Eleanor::$Db->Delete($mc['rt'],'`tag`'.$in);
				}

			$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$mc['tt'].'`'.$where);
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
			if(!in_array($sort,array('id','name','cnt')))
				$sort='';
			$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? $_GET['so'] : 'asc';
			if($so!='asc')
				$so='desc';
			if($sort and ($sort!='cnt' or $so!='desc'))
				$qs+=array('sort'=>$sort,'so'=>$so);
			else
				$sort='cnt';
			$qs+=array('sort'=>false,'so'=>false);

			if($cnt>0)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`language`,`name`,`cnt` FROM `'.$mc['tt'].'`'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
				while($a=$R->fetch_assoc())
				{
					$a['_aedit']=$Eleanor->Url->Construct(array('editt'=>$a['id']));
					$a['_adel']=$Eleanor->Url->Construct(array('deletet'=>$a['id']));

					$items[$a['id']]=array_slice($a,1);
				}
			}
			$links=array(
				'sort_name'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'name','so'=>$qs['sort']=='name' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_cnt'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'cnt','so'=>$qs['sort']=='cnt' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
				'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
				'first_page'=>$Eleanor->Url->Construct($qs),
				'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
			);
			SetData();
			$c=Eleanor::$Template->TagsList($items,$cnt,$pp,$qs,$page,$links);
			Start();
			echo$c;
		break;
		case'addt':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				SaveTag(0);
			else
				AddEditTag(0);
		break;
		case'add':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				Save(0);
			else
				AddEdit(0);
		break;
		case'categories':
			$Eleanor->Url->SetPrefix(array('do'=>'categories'),true);
			$c=$Eleanor->Categories->Show();
			if($c)
			{
				SetData();
				$c=Eleanor::$Template->Categories($c);
				Start();
				echo$c;
			}
		break;
		case'options':
			$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
			$c=$Eleanor->Settings->GetInterface('group',$mc['opts']);
			if($c)
			{
				SetData();
				$c=Eleanor::$Template->Options($c);
				Start();
				echo$c;
			}
		break;
		case'draft':
			$t=isset($_POST['_draft']) ? (string)$_POST['_draft'] : '';
			if(preg_match('#^([nt])(\d+)$#',$t,$m)>0)
			{
				unset($_POST['_draft'],$_POST['back']);
				Eleanor::$Db->Replace(P.'drafts',array('key'=>$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$t,'value'=>serialize($_POST)));
			}
			Eleanor::$content_type='text/plain';
			Start('');
			echo'ok';
		break;
		//case'addf':
		//	$title='Дополнительные поля';
		//	#ToDo!
		//	Start();
		//	echo 'В разработке...';
		//break;
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
	$R=Eleanor::$Db->Query('SELECT `id`,`title`,`tags`,`voting` FROM `'.$mc['t'].'` LEFT JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		if($a['voting'])
			$Eleanor->VotingManager->Delete($a['voting']);
		Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$mc['n'].DIRECTORY_SEPARATOR.$id);
		$R=Eleanor::$Db->Query('SELECT `tag`,COUNT(`id`) `cnt` FROM `'.$mc['rt'].'` WHERE `id`='.$id.' GROUP BY `tag`');
		$tids=array();
		while($a=$R->fetch_assoc())
		{
			$tids[]=$a['tag'];
			Eleanor::$Db->Update($mc['tt'],array('!cnt'=>'GREATEST(0,`cnt`-'.$a['cnt'].')'),'`id`='.$a['tag'].' AND `cnt`>0');
		}

		if($a['tags'])
			$tids=array_merge($tids,explode(',,',trim($a['tags'],',')));

		if($tids)
			Eleanor::$Db->Delete($mc['tt'],'`cnt`=0 AND `id`'.Eleanor::$Db->In(array_unique($tids)));
		Eleanor::$Db->Delete(P.'comments','`module`='.$Eleanor->module['id'].' AND `contid`='.$id);
		Eleanor::$Db->Delete($mc['t'],'`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($mc['tl'],'`id`='.$id);
		Eleanor::$Db->Delete($mc['rt'],'`id`='.$id);
		Eleanor::$Db->Delete(P.'drafts','`key`=\''.$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$id.'\' LIMIT 1');
		Eleanor::$Cache->Lib->DeleteByTag($mc['n']);
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	SetData();
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;
}
elseif(isset($_GET['swap']))
{
	$id=(int)$_GET['swap'];
	if(Eleanor::$our_query)
	{
		$R=Eleanor::$Db->Query('SELECT `pinned`,`enddate`,`status`,`tags` FROM `'.$mc['t'].'` WHERE `id`='.$id.' LIMIT 1');
		if(list($pin,$ed,$st,$tags)=$R->fetch_row() and ((int)$ed==0 or strtotime($ed)>time()))
		{
			$tags=$tags ? explode(',,',trim($tags,',')) : false;
			if($st<=0)
			{
				if($pin)
					Eleanor::$Db->Update($mc['t'],array('!status'=>'IF(`date`>`pinned` AND `date`<=NOW(),1,2)'),'`id`='.$id.' LIMIT 1');
				else
					Eleanor::$Db->Update($mc['t'],array('status'=>1),'`id`='.$id.' LIMIT 1');
				if($tags)
				{
					Eleanor::$Db->Insert($mc['rt'],array('id'=>array_fill(0,count($tags),$id),'tag'=>$tags));
					Eleanor::$Db->Update($mc['tt'],array('!cnt'=>'`cnt`+1'),'`id`'.Eleanor::$Db->In($tags));
				}
			}
			else
			{
				Eleanor::$Db->Update($mc['t'],array('status'=>0),'`id`='.$id.' LIMIT 1');
				RemoveTags($id);
			}
		}
		$R=Eleanor::$Db->Query('UPDATE `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) SET `lstatus`=`status` WHERE `id`='.$id);
		Eleanor::$Cache->Lib->DeleteByTag($mc['n']);
	}
	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#it'.$id : true);
}
elseif(isset($_GET['editt']))
{
	$id=(int)$_GET['editt'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		SaveTag($id);
	else
		AddEditTag($id);
}
elseif(isset($_GET['deletet']))
{
	$id=(int)$_GET['deletet'];
	$R=Eleanor::$Db->Query('SELECT `name` FROM `'.$mc['tt'].'` WHERE `id`='.$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete($mc['tt'],'`id`='.$id);
		Eleanor::$Db->Delete($mc['rt'],'`tag`='.$id);
		Eleanor::$Db->Delete(P.'drafts','`key`=\''.$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-t'.$id.'\' LIMIT 1');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	SetData();
	$c=Eleanor::$Template->DeleteTag($a,$back);
	Start();
	echo$c;
}
else
	ShowList();

function AddEditTag($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.$Eleanor->module['config']['tt'].'` WHERE id='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			foreach($a as $k=>&$v)
				if(isset($Eleanor->sc[$k]))
					$Eleanor->sc[$k]['value']=$v;
		}
		$title[]=$lang['editingt'];
	}
	else
		$title[]=$lang['addingt'];

	$hasdraft=false;
	if(!$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-t'.$id.'\' LIMIT 1');
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
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$values=$Eleanor->Controls->DisplayControls($Eleanor->sc);
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('deletet'=>$id,'noback'=>1)) : false,
		'nodraft'=>$hasdraft ? $Eleanor->Url->Construct(array('do'=>$id ? false : 'addt','editt'=>$id ? $id : false,'nodraft'=>1)): false,
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);
	SetData();
	$c=Eleanor::$Template->AddEditTag($id,$Eleanor->sc,$values,$errors,$back,$hasdraft,$links);
	Start();
	echo$c;
}

function SaveTag($id)
{global$Eleanor;
	try
	{
		$values=$Eleanor->Controls->SaveControls($Eleanor->sc);
	}
	catch(EE$E)
	{
		return AddEditTag($id,array('ERROR'=>$E->getMessage()));
	}

	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	if($values['name']=='')
		return AddEditTag($id,$lang['empty_tag']);
	$l=isset($values['language']) ? $values['language'] : '';
	$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$Eleanor->module['config']['tt'].'` WHERE `name`=\''.Eleanor::$Db->Escape($values['name'],false).'\''.($l && isset(Eleanor::$langs[$l]) ? ' AND `language` IN (\'\',\''.$l.'\')' : '').($id ? ' AND `id`!='.$id : '').' LIMIT 1');
	if($R->num_rows>0)
		return AddEditTag($id,array('TAG_EXISTS'));

	Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-t'.$id.'\' LIMIT 1');
	if($id)
		Eleanor::$Db->Update($Eleanor->module['config']['tt'],$values,'id='.$id.' LIMIT 1');
	else
		Eleanor::$Db->Insert($Eleanor->module['config']['tt'],$values);
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}

function ShowList()
{global$Eleanor,$title;
	$title=Eleanor::$Language[$Eleanor->module['config']['n']]['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$qs=$items=array();
	$where='';
	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		if(isset($_REQUEST['fi']['title'],$_REQUEST['fi']['titlet']) and $_REQUEST['fi']['title']!='')
		{
			$t=Eleanor::$Db->Escape((string)$_REQUEST['fi']['title'],false);
			switch($_REQUEST['fi']['titlet'])
			{
				case'b':
					$t=' LIKE \''.$t.'%\'';
				break;
				case'm':
					$t=' LIKE \'%'.$t.'%\'';
				break;
				case'e':
					$t=' LIKE \'%'.$t.'\'';
				break;
				default:
					$t='=\''.$t.'\'';
			}
			$qs['']['fi']['title']=$_REQUEST['fi']['title'];
			$qs['']['fi']['titlet']=$_REQUEST['fi']['titlet'];
			$where.=' AND `title`'.$t;
		}
		if(!empty($_REQUEST['fi']['category']))
			if($_REQUEST['fi']['category']=='no')
			{
				$qs['']['fi']['category']='';
				$where.=' AND `cats`=\'\'';
			}
			else
			{
				$qs['']['fi']['category']=(int)$_REQUEST['fi']['category'];
				$where.=' AND `cats` LIKE \'%,'.$qs['']['fi']['category'].',%\'';
			}
		if(isset($_REQUEST['fi']['status']) and $_REQUEST['fi']['status']!='-')
		{
			$qs['']['fi']['status']=(int)$_REQUEST['fi']['status'];
			$where.=$qs['']['fi']['status']==1 ? ' AND `status`IN(1,2)' : ' AND `status`='.$qs['']['fi']['status'];
		}
	}

	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']))
		do
		{
			$in=Eleanor::$Db->In($_POST['mass']);
			switch($_POST['op'])
			{
				case'd':
					Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>0),'`id`'.$in);
					RemoveTags($_POST['mass']);
				break;
				case'a':
					Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>1),'`id`'.$in.' AND `pinned`=\'0000-00-00 00:00:00\'');
					Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!status'=>'IF(`date`>`pinned` AND `date`<=NOW(),1,2)'),'`id`'.$in.' AND `pinned`!=\'0000-00-00 00:00:00\'');
					$R=Eleanor::$Db->Query('SELECT `id`,`tags` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`'.$in);
					while($a=$R->fetch_assoc())
						if($a['tags'])
						{
							$a['tags']=explode(',,',trim($a['tags'],','));
							Eleanor::$Db->Insert($Eleanor->module['config']['rt'],array('id'=>array_fill(0,count($a['tags']),$a['id']),'tag'=>$a['tags']));
							Eleanor::$Db->Update($Eleanor->module['config']['tt'],array('!cnt'=>'`cnt`+1'),'`id`'.Eleanor::$Db->In($a['tags']));
						}
				break;
				case'm':
					Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>-1),'`id`'.$in);
				break;
				case'k':
					$vots=array();
					$R=Eleanor::$Db->Query('SELECT `voting` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`'.Eleanor::$Db->In($_POST['mass']).' AND `voting`>0');
					while($a=$R->fetch_assoc())
						$vots[]=$a['voting'];
					if($vots)
						$Eleanor->VotingManager->Delete($vots);
					foreach($_POST['mass'] as &$v)
						Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.(int)$v);
					RemoveTags($_POST['mass']);
					Eleanor::$Db->Delete(P.'comments','`module`='.$Eleanor->module['id'].' AND `contid`'.Eleanor::$Db->In($_POST['mass']));
					Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`'.$in);
					Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`'.$in);
					Eleanor::$Cache->Lib->DeleteByTag($Eleanor->module['config']['n']);
				break 2;
			}
			$R2=Eleanor::$Db->Query('UPDATE `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) SET `lstatus`=`status` WHERE `id`'.Eleanor::$Db->In($_POST['mass']));
			Eleanor::$Cache->Lib->DeleteByTag($Eleanor->module['config']['n']);
		}while(false);

	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')'.$where);
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
	if(!in_array($sort,array('id','title','date','author','status')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? $_GET['so'] : 'desc';
	if($so!='asc')
		$so='desc';
	if($sort and ($sort!='id' or $so!='asc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='date';
	$qs+=array('sort'=>false,'so'=>false);

	if($cnt>0)
	{
		$t=time();
		$R=Eleanor::$Db->Query('SELECT `id`,`cats`,`date`,`enddate`,`pinned`,`date`>`pinned` `_pinned`,`author`,`author_id`,`status`,`title` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['cats']=$a['cats'] ? array_unique(explode(',,',trim($a['cats'],','))) : array();
			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			$a['_aswap']=(int)$a['enddate'] && strtotime($a['enddate'])<=$t ? false : $Eleanor->Url->Construct(array('swap'=>$a['id']));

			if((int)$a['pinned'] && $a['_pinned'])
				$a['date']=$a['pinned'];
			unset($a['pinned']);

			$items[$a['id']]=array_slice($a,1);
		}
	}
	$Eleanor->Categories->Init($Eleanor->module['config']['c']);
	$links=array(
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_title'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'title','so'=>$qs['sort']=='title' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_date'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'date','so'=>$qs['sort']=='date' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_author'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'author','so'=>$qs['sort']=='author' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);

	SetData();
	$c=Eleanor::$Template->ShowList($items,$Eleanor->Categories->dump,$cnt,$pp,$qs,$page,$links);
	Start();
	echo$c;
}

function RemoveTags($ids)
{global$Eleanor;
	$in=Eleanor::$Db->In($ids);
	$R=Eleanor::$Db->Query('SELECT `tag`,COUNT(`id`) `cnt` FROM `'.$Eleanor->module['config']['rt'].'` WHERE `id`'.$in.' GROUP BY `tag`');
	while($a=$R->fetch_assoc())
		Eleanor::$Db->Update($Eleanor->module['config']['tt'],array('!cnt'=>'GREATEST(0,`cnt`-'.$a['cnt'].')'),'`id`='.$a['tag'].' AND `cnt`>0');
	Eleanor::$Db->Delete($Eleanor->module['config']['rt'],'`id`'.$in);
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	if($id)
	{
		$title[]=$lang['editing'];
		$R=Eleanor::$Db->Query('SELECT '.($errors ? '`voting`' : '*,`date`>`pinned` `_pin`').' FROM `'.$Eleanor->module['config']['t'].'` WHERE id='.$id.' LIMIT 1');
		if(!$values=$R->fetch_assoc())
			return GoAway(true);

		if(!$errors)
		{
			$values['_ping']=$values['status']!=1;
			$values['cats']=$values['cats'] ? explode(',,',trim($values['cats'],',')) : array();
			$values['tags']=$values['tags'] ? explode(',,',trim($values['tags'],',')) : array();

			#Служебное
			$values['_maincat']=reset($values['cats']);
			if($values['status']==2)
				$values['status']=1;
			if($values['_pin'] and (int)$values['pinned'])
				list($values['pinned'],$values['date'])=array($values['date'],$values['pinned']);

			$values['uri']=$values['title']=$values['announcement']=$values['text']=$values['meta_title']=$values['meta_descr']=array();
			$R=Eleanor::$Db->Query('SELECT `language`,`uri`,`title`,`announcement`,`text`,`meta_title`,`meta_descr` FROM `'.$Eleanor->module['config']['tl'].'` WHERE `id`='.$id);
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
				$values['_langs']=array_keys($values['title']);
			}

			if($values['tags'])
			{
				$R=Eleanor::$Db->Query('SELECT `language`,`name` FROM `'.$Eleanor->module['config']['tt'].'` WHERE `id`'.Eleanor::$Db->In($values['tags']));
				$values['tags']=array();
				while($a=$R->fetch_assoc())
					$values['tags'][(Eleanor::$vars['multilang'] and !$values['_onelang']) ? $a['language'] : ''][]=$a['name'];
				foreach($values['tags'] as &$v)
				{
					#Удаление одинаковых тегов
					foreach($v as $kt=>&$vt)
						foreach($v as $kkt=>&$vvt)
						{
							if($kkt>=$kt)
								break;
							if(mb_strtolower($vt)==mb_strtolower($vvt))
								unset($v[$kt]);
						}
					$v=join(', ',$v);
				}
			}
			else
				$values['tags']=array(''=>'');
		}
	}
	else
	{
		$dv=Eleanor::$vars['multilang'] ? array(''=>'') : '';
		$values=array(
			'cats'=>array(),
			'date'=>date('Y-m-d H:i:s'),
			'pinned'=>'',
			'enddate'=>'',
			'author'=>Eleanor::$Login->GetUserValue('name',false),
			'author_id'=>Eleanor::$Login->GetUserValue('id'),
			'show_detail'=>true,
			'show_sokr'=>false,
			'reads'=>0,
			'tags'=>array(''=>''),
			'status'=>1,
			'voting'=>false,
			#Языковые
			'title'=>$dv,
			'announcement'=>$dv,
			'text'=>$dv,
			'uri'=>$dv,
			'meta_title'=>$dv,
			'meta_descr'=>$dv,
			#Специальные
			'_maincat'=>0,
		);
		if(Eleanor::$vars['multilang'])
		{
			$values['_onelang']=true;
			$values['_langs']=array_keys(Eleanor::$langs);
		}
		$title[]=$lang['adding'];
	}

	$hasdraft=false;
	if(!$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$id.'\' LIMIT 1');
		if($draft=$R->fetch_row() and $draft[0])
		{
			$hasdraft=true;
			$_POST+=(array)unserialize($draft[0]);
			$errors=true;
		}
	}

	if($errors)
	{
		$bypost=true;
		if($errors===true)
			$errors=array();
		$values['tags']=isset($_POST['tags']) ? (array)$_POST['tags'] : array();
		if(Eleanor::$vars['multilang'])
		{
			$values['meta_title']=isset($_POST['meta_title']) ? (array)$_POST['meta_title'] : array();
			$values['meta_descr']=isset($_POST['meta_descr']) ? (array)$_POST['meta_descr'] : array();
			$values['title']=isset($_POST['title']) ? (array)$_POST['title'] : array();
			$values['announcement']=isset($_POST['announcement']) ? (array)$_POST['announcement'] : array();
			$values['text']=isset($_POST['text']) ? (array)$_POST['text'] : array();
			$values['uri']=isset($_POST['uri']) ? (array)$_POST['uri'] : array();
			$values['_onelang']=isset($_POST['_onelang']);
			$values['_langs']=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array(Language::$main);
		}
		else
		{
			$values['meta_title']=isset($_POST['meta_title']) ? (string)$_POST['meta_title'] : '';
			$values['meta_descr']=isset($_POST['meta_descr']) ? (string)$_POST['meta_descr'] : '';
			$values['title']=isset($_POST['title']) ? (string)$_POST['title'] : '';
			$values['announcement']=isset($_POST['announcement']) ? (string)$_POST['announcement'] : '';
			$values['text']=isset($_POST['text']) ? (string)$_POST['text'] : '';
			$values['uri']=isset($_POST['uri']) ? (string)$_POST['uri'] : '';
		}
		$values['_maincat']=isset($_POST['_maincat']) ? (int)$_POST['_maincat'] : 0;
		$values['cats']=isset($_POST['cats']) ? (array)$_POST['cats'] : array();
		$values['date']=isset($_POST['date']) ? (string)$_POST['date'] : date('Y-m-d H:i:s');
		$values['enddate']=isset($_POST['enddate']) ? (string)$_POST['enddate'] : '';
		$values['pinned']=isset($_POST['pinned']) ? (string)$_POST['pinned'] : '';
		$values['author']=isset($_POST['author']) ? (string)$_POST['author'] : '';
		$values['author_id']=isset($_POST['author_id']) ? (string)$_POST['author_id'] : '';
		$values['reads']=isset($_POST['reads']) ? (int)$_POST['reads'] : '';
		$values['status']=isset($_POST['status']) ? (int)$_POST['status'] : '';
		$values['show_detail']=isset($_POST['show_detail']);
		$values['show_sokr']=isset($_POST['show_sokr']);
		$values['_ping']=isset($_POST['_ping']) && $values['status']==1;
		$Eleanor->VotingManager->bypost=true;
	}
	else
		$bypost=false;

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$Eleanor->Categories->Init($Eleanor->module['config']['c']);
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
		'nodraft'=>$Eleanor->Url->Construct(array('do'=>$id ? false : 'add','edit'=>$id ? $id : false,'nodraft'=>1)),
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);

	SetData();
	$c=Eleanor::$Template->AddEdit($id,$values,$errors,$Eleanor->Uploader->Show($id ? $Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$id : false),$Eleanor->VotingManager->AddEdit($values['voting']),$bypost,$hasdraft,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	$errors=array();
	#Перечень переменных, которые дублируются для языковой таблицы ниже
	$maincat=isset($_POST['_maincat']) ? (int)$_POST['_maincat'] : false;
	$cats=isset($_POST['cats']) ? array_unique((array)$_POST['cats']) : array();
	if($cats and count($cats)>1)
	{
		sort($cats,SORT_NUMERIC);
		if($maincat!=$cats[0])
			array_unshift($cats,$maincat ? $maincat : reset($cats));
	}
	$cats=$cats ? ','.join(',,',$cats).',' : '';

	if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
	{
		$langs=empty($_POST['_langs']) || !is_array($_POST['_langs']) ? array() : $_POST['_langs'];
		$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
		if(!$langs)
			$langs=array(Language::$main);
	}
	else
		$langs=array('');

	$values=array(
		'cats'=>$cats,
		'date'=>isset($_POST['date']) ? (string)$_POST['date'] : false,
		'enddate'=>isset($_POST['enddate']) ? (string)$_POST['enddate'] : '',
		'pinned'=>isset($_POST['pinned']) ? (string)$_POST['pinned'] : '0000-00-00 00:00:00',
		'author'=>isset($_POST['author']) ? (string)$_POST['author'] : '',
		'author_id'=>isset($_POST['author_id']) ? (string)$_POST['author_id'] : 0,
		'reads'=>isset($_POST['reads']) ? (int)$_POST['reads'] : 0,
		'status'=>isset($_POST['status']) ? (int)$_POST['status'] : 1,
		'show_detail'=>isset($_POST['show_detail']),
		'show_sokr'=>isset($_POST['show_sokr']),
		'tags'=>'',
	);

	if(!$values['date'])
	{
		$values['date']=date('Y-m-d H:i:s');
		$da=time();
	}
	elseif($da=strtotime($values['date']) and $da>time())
		$values['status']=2;

	if($values['enddate'])
	{
		$values['enddate']=strtotime($values['enddate']);
		if(!$values['enddate'])
			$errors[]='ERROR_END_DATE';
		elseif($values['status'] and $values['enddate']<=time())
			$errors[]='ERROR_END_DATE_IN_PAST';
		$values['enddate']=date('Y-m-d H:i:s',$values['enddate']);
	}

	if($values['author'] and $values['author_id']>0)
	{
		$R=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`=\''.Eleanor::$Db->Escape($values['author'],false).'\' LIMIT 1');
		if($R->num_rows>0)
			list($values['author_id'])=$R->fetch_row();
		else
			$values['author_id']=0;
	}
	elseif($values['author']!='')
		$values['author']=htmlspecialchars($values['author'],ELENT,CHARSET,false);

	if($da and $values['pinned'] and $pin=strtotime($values['pinned']))
	{
		if($pin<$da)
			$errors[]='ERROR_PINNED';
		elseif($values['status']>=1)
			list($values['date'],$values['pinned'])=array($values['pinned'],$values['date']);
	}
	else
		$values['pinned']='0000-00-00 00:00:00';

	if(Eleanor::$vars['multilang'])
	{
		$lvalues=array(
			'title'=>array(),
			'announcement'=>array(),
			'text'=>array(),
			'uri'=>array(),
			'meta_title'=>array(),
			'meta_descr'=>array(),
			'tags'=>array(),
		);
		foreach($langs as $l)
		{
			$lng=$l ? $l : Language::$main;
			$lvalues['tags'][$l]=isset($_POST['tags'],$_POST['tags'][$lng]) && is_array($_POST['tags']) ? (string)Eleanor::$POST['tags'][$lng] : '';
			$Eleanor->Editor_result->imgalt=$lvalues['title'][$l]=(isset($_POST['title'],$_POST['title'][$lng]) and is_array($_POST['title'])) ? (string)Eleanor::$POST['title'][$lng] : '';
			$lvalues['announcement'][$l]=isset($_POST['announcement'],$_POST['announcement'][$lng]) && is_array($_POST['announcement']) ? $Eleanor->Editor_result->GetHtml((string)$_POST['announcement'][$lng],true) : '';
			$lvalues['text'][$l]=isset($_POST['text'],$_POST['text'][$lng]) && is_array($_POST['text']) ? $Eleanor->Editor_result->GetHtml((string)$_POST['text'][$lng],true) : '';
			$lvalues['uri'][$l]=isset($_POST['uri'],$_POST['uri'][$lng]) && is_array($_POST['uri']) ? (string)$_POST['uri'][$lng] : '';
			$lvalues['meta_title'][$l]=isset($_POST['meta_title'],$_POST['meta_title'][$lng]) && is_array($_POST['meta_title']) ? (string)Eleanor::$POST['meta_title'][$lng] : '';
			$lvalues['meta_descr'][$l]=isset($_POST['meta_descr'],$_POST['meta_descr'][$lng]) && is_array($_POST['meta_descr']) ? (string)Eleanor::$POST['meta_descr'][$lng] : '';
		}
	}
	else
	{
		$Eleanor->Editor_result->imgalt=isset($_POST['title']) ? (string)Eleanor::$POST['title'] : '';
		$lvalues=array(
			'title'=>array(''=>$Eleanor->Editor_result->imgalt),
			'announcement'=>array(''=>$Eleanor->Editor_result->GetHtml('announcement')),
			'text'=>array(''=>$Eleanor->Editor_result->GetHtml('text')),
			'tags'=>array(''=>isset($_POST['tags']) ? (string)Eleanor::$POST['tags'] : ''),
			'uri'=>array(''=>isset($_POST['uri']) ? (string)$_POST['uri'] : ''),
			'meta_title'=>array(''=>isset($_POST['meta_title']) ? (string)Eleanor::$POST['meta_title'] : ''),
			'meta_descr'=>array(''=>isset($_POST['meta_descr']) ? (string)Eleanor::$POST['meta_descr'] : ''),
		);
	}

	$ml=in_array('',$langs) ? Language::$main : '';
	$emp=array('title'=>array(),'announcement'=>array(),'text'=>array());
	foreach($emp as $kf=>&$field)
		foreach($lvalues[$kf] as $k=>&$v)
			$field[$k]=$v=='' && (in_array($k,$langs) or $ml==$k);

	foreach($emp['title'] as $k=>&$v)
		if($v)
		{
			$er='EMPTY_TITLE'.strtoupper($k ? '_'.$k : '');
			$errors[$er]=$lang['EMPTY_TITLE']($k);
		}

	foreach($emp['announcement'] as $k=>&$v)
		if($v and $emp['text'][$k])
		{
			$er='EMPTY_TEXT'.strtoupper($k ? '_'.$k : '');
			$errors[$er]=$lang['EMPTY_TEXT']($k);
		}

	foreach($lvalues['uri'] as $k=>&$v)
	{
		if($v=='')
			$v=htmlspecialchars_decode($lvalues['title'][$k],ELENT);
		$v=$Eleanor->Url->Filter($v,$k);
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `uri`='.Eleanor::$Db->Escape($v).' AND `language`'.($k ? 'IN(\'\',\''.$k.'\')' : '=\'\'').($id ? ' AND `id`!='.$id : '').' LIMIT 1');
		if($R->num_rows>0)
			$v='';
	}

	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `voting` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.' LIMIT 1');
		list($voting)=$R->fetch_row();
	}
	else
		$voting=false;

	$Eleanor->VotingManager->langs=Eleanor::$vars['multilang'] ? $langs : array();
	$values['voting']=$Eleanor->VotingManager->Save($voting);
	if(is_array($values['voting']))
		$errors+=$values['voting'];

	if($errors)
		return AddEdit($id,$errors);

	$addt=$values['tags']=array();
	foreach($lvalues['tags'] as $lng=>&$tags)
	{
		$tags=$tags ? explode(',',$tags) : array();
		foreach($tags as $kt=>&$vt)
		{
			$vt=trim($vt);
			if(!$vt)
			{
				unset($tags[$kt]);
				continue;
			}
			foreach($tags as $kkt=>$vvt)
			{
				if($kkt>=$kt)
					break;
				if(mb_strtolower($vt)==mb_strtolower($vvt))
					unset($tags[$kt]);
			}
		}
		unset($vt);#Не просто так
		if(!$tags)
			continue;
		$toins=$utags=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`name` FROM `'.$Eleanor->module['config']['tt'].'` WHERE'.($lng && isset(Eleanor::$langs[$lng]) ? '`language` IN (\'\',\''.$lng.'\') AND' : '').' `name`'.Eleanor::$Db->In($tags));
		while($a=$R->fetch_assoc())
			$utags[$a['id']]=$a['name'];

		foreach($tags as &$tv)
		{
			$n=true;
			foreach($utags as &$utv)
				if(mb_strtolower($tv)==mb_strtolower($utv))
				{
					$n=false;
					break;
				}
			if($n)
				$toins[]=$tv;
		}

		if($toins)
		{
			$n=Eleanor::$Db->Insert($Eleanor->module['config']['tt'],array('language'=>array_fill(0,count($toins),$lng),'name'=>$toins));
			if($n>0)
				foreach($toins as &$v)
					$utags[$n++]=$v;
		}
		$tags=array_keys($utags);
		$values['tags']=array_merge($values['tags'],$tags);
		$values['tags']=array_unique($values['tags']);
		sort($values['tags'],SORT_NUMERIC);
	}
	unset($tags,$utags);
	$date=$values['date'];
	$status=$values['status'];
	$ping=false;
	Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$id.'\' LIMIT 1');
	if($id)
	{
		$t=array();
		if($status==1)
		{
			$ping=isset($_POST['_ping']);
			$R=Eleanor::$Db->Query('SELECT `tag` FROM `'.$Eleanor->module['config']['rt'].'` WHERE `id`='.$id);
			while($a=$R->fetch_assoc())
				$t[]=$a['tag'];
			$addt=array_diff($values['tags'],$t);
			$delt=array_diff($t,$values['tags']);

			if($delt)
			{
				$delt=Eleanor::$Db->In($delt);
				Eleanor::$Db->Delete($Eleanor->module['config']['rt'],'`id`='.$id.' AND `tag`'.$delt);
				Eleanor::$Db->Update($Eleanor->module['config']['tt'],array('!cnt'=>'`cnt`-1'),'`id`'.$delt.' AND `cnt`>0');
			}
		}
		else
			RemoveTags($id);
		$values['tags']=$values['tags'] ? ','.join(',,',$values['tags']).',' : '';

		Eleanor::$Db->Update($Eleanor->module['config']['t'],$values,'id='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		$values=array();
		foreach($langs as &$v)
			$values[]=array(
				'id'=>$id,
				'language'=>$v,
				'uri'=>$lvalues['uri'][$v],
				'title'=>$lvalues['title'][$v],
				'announcement'=>$lvalues['announcement'][$v],
				'text'=>$lvalues['text'][$v],
				'meta_title'=>$lvalues['meta_title'][$v],
				'meta_descr'=>$lvalues['meta_descr'][$v],
				'last_mod'=>date('Y-m-d H:i:s'),
				'lstatus'=>$status,
				'ldate'=>$date,
				'lcats'=>$cats,
			);
		Eleanor::$Db->Replace($Eleanor->module['config']['tl'],$values);
	}
	else
	{
		if($status==1)
		{
			$addt=$values['tags'];
			$ping=true;
		}
		$values['tags']=$values['tags'] ? ','.join(',,',$values['tags']).',' : '';
		Eleanor::$Db->Transaction();#Все ради аплоадера
		$id=Eleanor::$Db->Insert($Eleanor->module['config']['t'],$values);
		try
		{
			$ft=$Eleanor->Uploader->MoveFiles($Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$id);
		}
		catch(EE$E)
		{
			Eleanor::$Db->Rollback();
			return AddEdit(false,array('UPLOAD_ERROR'=>$E->getMessage()));
		}
		$values=array('id'=>array(),'language'=>array(),'uri'=>array(),'title'=>array(),'announcement'=>array(),'text'=>array(),'meta_title'=>array(),'meta_descr'=>array());
		foreach($langs as &$v)
		{
			$values['id'][]=$id;
			$values['last_mod'][]=date('Y-m-d H:i:s');
			$values['lstatus'][]=$status;
			$values['ldate'][]=$date;
			$values['lcats'][]=$cats;
			$values['language'][]=$v;
			$values['uri'][]=$lvalues['uri'][$v];
			$values['title'][]=str_replace($ft['from'],$ft['to'],$lvalues['title'][$v]);
			$values['announcement'][]=str_replace($ft['from'],$ft['to'],$lvalues['announcement'][$v]);
			$values['text'][]=str_replace($ft['from'],$ft['to'],$lvalues['text'][$v]);
			$values['meta_title'][]=$lvalues['meta_title'][$v];
			$values['meta_descr'][]=$lvalues['meta_descr'][$v];
		}
		Eleanor::$Db->Insert($Eleanor->module['config']['tl'],$values);
		Eleanor::$Db->Commit();
	}
	if($addt)
	{
		Eleanor::$Db->Insert($Eleanor->module['config']['rt'],array('id'=>array_fill(0,count($addt),$id),'tag'=>array_values($addt)));
		Eleanor::$Db->Update($Eleanor->module['config']['tt'],array('!cnt'=>'`cnt`+1'),'`id`'.Eleanor::$Db->In($addt));
	}
	Eleanor::$Cache->Obsolete($Eleanor->module['config']['n'].'_nextrun');
	Eleanor::$Cache->Lib->DeleteByTag($Eleanor->module['config']['n']);
	if($ping and Eleanor::$vars['publ_ping'])
	{
		$Eleanor->Url->furl=Eleanor::$vars['furl'];
		$Eleanor->Url->file=Eleanor::$services['user']['file'];
		$sd=PROTOCOL.Eleanor::$domain.Eleanor::$site_path;
		Ping::Add(array('id'=>$Eleanor->module['config']['n'],'changes'=>$sd.$Eleanor->Url->Prefix(),'rss'=>$sd.Eleanor::$services['rss']['file'].'?'.Url::Query(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']))));
		$Eleanor->Url->furl=false;
		$Eleanor->Url->file=Eleanor::$filename;
	}
	GoAway(true/*empty($_POST['back']) ? true : $_POST['back']*/);
}

function DelCategories($ids)
{global$Eleanor;
	#К сожалению в MySQL не предусмотрена функция замены по регулярному выражению. Расставляем грабли.
	foreach($ids as &$v)
	{
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!cats'=>'REPLACE(`cats`,\','.$v.',\',\'\')'));
		Eleanor::$Db->Update($Eleanor->module['config']['tl'],array('!lcats'=>'REPLACE(`lcats`,\','.$v.',\',\'\')'));
	}
	Eleanor::$Cache->Lib->DeleteByTag($Eleanor->module['config']['n']);
}

function SetData()
{global$Eleanor;
	$R=Eleanor::$Db->Query('SELECT COUNT(`status`) FROM `'.$Eleanor->module['config']['t'].'` WHERE `status`=-1');
	list($cnt)=$R->fetch_row();
	$Eleanor->module['links']=array(
		'list'=>$Eleanor->Url->Prefix(),
		'newlist'=>$cnt>0
			? array(
				#'act'=>isset($_GET['fi'],$_GET['fi']['status']) and $_GET['fi']['status']==-1,
				'link'=>$Eleanor->Url->Construct(array(''=>array('fi'=>array('status'=>-1)))),
				'cnt'=>$cnt,
			)
			: false,
		'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
		'tags'=>$Eleanor->Url->Construct(array('do'=>'tags')),
		'addt'=>$Eleanor->Url->Construct(array('do'=>'addt')),
		'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
		'categories'=>$Eleanor->Url->Construct(array('do'=>'categories')),
		//'addf'=>$Eleanor->Url->Construct(array('do'=>'addf'),
	);
}