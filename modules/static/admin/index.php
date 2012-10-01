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
$Eleanor->module['config']=include($Eleanor->module['path'].'config.php');
Eleanor::$Template->queue[]=$Eleanor->module['config']['admintpl'];

$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_admin-*.php',$Eleanor->module['config']['n']);
Eleanor::LoadOptions($Eleanor->module['config']['opts'],false);

$Eleanor->module['links']=array(
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
	'parent_add'=>false,
	'list'=>$Eleanor->Url->Prefix(),
	'files'=>$Eleanor->Url->Construct(array('do'=>'files')),
	'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
);

$Eleanor->sc_post=false;
$Eleanor->sc=array(
	'parents'=>array(
		'title'=>$lang['parent'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->sc_post,
		'load'=>function($a)
		{
			$a['value']=rtrim($a['value'],',');
			if(false!==$p=strrpos($a['value'],','))
				$a['value']=substr($a['value'],$p+1);
			return$a;
		},
		'save'=>function($a)
		{global$Eleanor;
			$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.(int)$a['value'].' LIMIT 1');
			if($a=$R->fetch_assoc())
				return$a['parents'] ? $a['parents'].$a['id'].',' : $a['id'].',';
			return'';
		},
		'options'=>array(
			'exclude'=>0,
			'callback'=>function($a)
			{global$Eleanor;
				$sel=Eleanor::Option('&mdash;',0,in_array('',$a['value']),array(),2);
				if(!class_exists($Eleanor->module['config']['api'],false))
					include $Eleanor->module['path'].'api.php';
				$Plug=new$Eleanor->module['config']['api']($Eleanor->module['config']);
				$items=$Plug->GetOrderedList();
				foreach($items as $k=>&$v)
				{
					if($k==$a['options']['exclude'] or strpos(','.$v['parents'],','.$a['options']['exclude'].',')!==false)
						continue;
					$sel.=Eleanor::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')).'›&nbsp;' : '').$v['title'],$k,in_array($k,$a['value']),array(),2);
				}
				return$sel;
			},
			'addon'=>array(
				'tabindex'=>1
			),
		),
	),
	'title'=>array(
		'title'=>$lang['name'],
		'descr'=>'',
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'load'=>function($a)
		{
			$r=array();
			if($a['multilang'])
				foreach(Eleanor::$langs as $k=>&$v)
				{
					$r['options'][$k]=$a['options'];
					$r['options'][$k]['addon']['id']='title-'.$k;
				}
			else
			{
				$r['options']=$a['options'];
				$r['options']['addon']['id']='title';
			}
			return$r;
		},
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
			'addon'=>array(
				'tabindex'=>2
			),
		),
	),
	'text'=>array(
		'title'=>$lang['content'],
		'descr'=>'',
		'type'=>'editor',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
			'4alt'=>'title',
		),
		'addon'=>array(
			'no'=>array('tabindex'=>3)
		),
	),
	'uri'=>array(
		'title'=>'URI',
		'descr'=>'',
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'load'=>function($a)
		{
			$r=array();
			if($a['multilang'])
				foreach(Eleanor::$langs as $k=>&$v)
				{
					$r['options'][$k]=$a['options'];
					$r['options'][$k]['addon']['onfocus']='if(!$(this).val())$(this).val($(\'#title-'.$k.'\').val())';
				}
			else
			{
				$r['options']=$a['options'];
				$r['options']['addon']['onfocus']='if(!$(this).val())$(this).val($(\'#title\').val())';
			}
			return$r;
		},
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
			'addon'=>array(
				'tabindex'=>4,
			),
		),
	),
	'pos'=>array(
		'title'=>$lang['pos'],
		'descr'=>$lang['pos_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'htmlsafe'=>true,
			'addon'=>array(
				'tabindex'=>5,
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
			'addon'=>array(
				'tabindex'=>6,
			),
		),
	),
	'meta_title'=>array(
		'title'=>'Window title',
		'descr'=>'',
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
	'meta_descr'=>array(
		'title'=>'Meta description',
		'descr'=>'',
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
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
		case'files':
			$title[]=$lang['fp'];
			$Up=new Uploader($Eleanor->module['path'].'DIRECT');
			$Up->watermark=false;
			$Up->buttons_top=array(
				'create_file'=>true,
				'create_folder'=>true,
				'update'=>true,
			);
			$Up->buttons_item=array(
				'edit'=>true,
				'file_rename'=>true,
				'file_delete'=>true,
				'folder_rename'=>true,
				'folder_open'=>false,
				'folder_delete'=>true,
			);
			$c=Eleanor::$Template->Files($Up->Show(''));
			Start();
			echo$c;
		break;
		case'options':
			$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
			$c=$Eleanor->Settings->GetInterface('group',$Eleanor->module['config']['opts']);
			if($c)
			{
				$c=Eleanor::$Template->Options($c);
				Start();
				echo$c;
			}
		break;
		case'resort':
			$p='';
			if(isset($_GET['id']))
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.(int)$_GET['id'].' LIMIT 1');
				if(list($id,$p)=$R->fetch_row())
					$p.=$id.',';
			}
			Resort($p);
			GoAway();
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
	$R=Eleanor::$Db->Query('SELECT `title`,`parents`,`pos` FROM `'.$Eleanor->module['config']['t'].'` LEFT JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
	if(!Eleanor::$our_query or !$a=$R->fetch_assoc())
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$id);
		$ids=array($id);
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$Eleanor->module['config']['t'].'` WHERE `parents` LIKE \''.$a['parents'].$id.',%\'');
		while($temp=$R->fetch_assoc())
		{
			$ids[]=$temp['id'];
			Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$temp['id']);
		}
		$ids=Eleanor::$Db->In($ids);
		Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`'.$ids);
		Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`'.$ids);
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`-1'),'`pos`>'.$a['pos'].' AND `parents`=\''.$a['parents'].'\'');
		Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
		Eleanor::$Cache->Lib->CleanByTag($Eleanor->module['config']['n']);
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$s=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$s;
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
elseif(isset($_GET['up']))
{
	$id=(int)$_GET['up'];
	$R=Eleanor::$Db->Query('SELECT `parents`,`pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.' LIMIT 1');
	if($R->num_rows==0 or !Eleanor::$our_query)
		return GoAway();
	list($parents,$posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`parents`),`pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `pos`=(SELECT MAX(`pos`) FROM `'.$Eleanor->module['config']['t'].'` WHERE `pos`<'.$posit.' AND `parents`=\''.$parents.'\') AND `parents`=\''.$parents.'\'');
	list($cnt,$np)=$R->fetch_row();
	if($cnt>0)
	{
		if($cnt>1 or $np+1!=$posit)
		{
			Resort($parents);
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`+1'),'`pos`='.--$posit.' AND `parents`=\''.$parents.'\' LIMIT 1');
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`-1'),'`id`='.$id.' AND `parents`=\''.$parents.'\' LIMIT 1');
	}
	GoAway(false,301,'it'.$id);
}
elseif(isset($_GET['down']))
{
	$id=(int)$_GET['down'];
	$R=Eleanor::$Db->Query('SELECT `parents`,`pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.' LIMIT 1');
	if($R->num_rows==0 or !Eleanor::$our_query)
		return GoAway();
	list($parents,$posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`parents`),`pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `pos`=(SELECT MIN(`pos`) FROM `'.$Eleanor->module['config']['t'].'` WHERE `pos`>'.$posit.' AND `parents`=\''.$parents.'\') AND `parents`=\''.$parents.'\'');
	list($cnt,$np)=$R->fetch_row();
	if($cnt>0)
	{
		if($cnt>1 or $np-1!=$posit)
		{
			Resort($parents);
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`-1'),'`pos`='.++$posit.' AND `parents`=\''.$parents.'\' LIMIT 1');
		Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`+1'),'`id`='.$id.' AND `parents`=\''.$parents.'\' LIMIT 1');
	}
	GoAway(false,301,'it'.$id);
}
else
	ShowList();

function ShowList()
{global$Eleanor,$title;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$navi=$where=$qs=array();
	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
		{
			$qs['']['fi']['title']=$_REQUEST['fi']['title'];
			$where[]='`title` LIKE \'%'.Eleanor::$Db->Escape($qs['']['fi']['title'],false).'%\'';
		}
	}

	if(isset($_REQUEST['parent']) and 0<$qs['parent']=(int)$_REQUEST['parent'])
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$qs['parent'].' LIMIT 1');
		list($parents)=$R->fetch_row();
		$parents.=$qs['parent'];
		$where[]='`parents`='.Eleanor::$Db->Escape($parents.',');
		$temp=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$Eleanor->module['config']['t'].'` `s` INNER JOIN `'.$Eleanor->module['config']['tl'].'` `l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id` IN ('.$parents.')');
		while($a=$R->fetch_assoc())
			$temp[$a['id']]=$a['title'];
		$navi[0]=array('title'=>$lang['list'],'_a'=>$Eleanor->Url->Prefix());
		foreach(explode(',',$parents) as $v)
			if(isset($temp[$v]))
				$navi[$v]=array('title'=>$temp[$v],'_a'=>$v==$qs['parent'] ? false : $Eleanor->Url->Construct(array('parent'=>$v)));
		$Eleanor->module['parent_add']=$Eleanor->Url->Construct(array('do'=>'add','parent'=>$qs['parent']));
	}
	else
		$where[]='`parents`=\'\'';
	$where[]='`language` IN (\'\',\''.Language::$main.'\')';
	$where=' WHERE '.join(' AND ',$where);
	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']) and is_array($_POST['mass']))
		switch($_POST['op'])
		{
			case'k':
				$ids=array();
				$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`'.Eleanor::$Db->In($_POST['mass']));
				while($a=$R->fetch_assoc())
				{
					$ids[]=$a['id'];
					$R2=Eleanor::$Db->Query('SELECT `id` FROM `'.$Eleanor->module['config']['t'].'` WHERE `parents` LIKE \''.$a['parents'].$a['id'].',%\'');
					while($temp=$R2->fetch_assoc())
						$ids[]=$temp['id'];
				}
				$ids_=Eleanor::$Db->In($ids);
				Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`'.$ids_);
				Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`'.$ids_);
				foreach($ids as &$v)
					Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$v);
			break;
			case'a':
				Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>1),'`id`'.Eleanor::$Db->In($_POST['mass']));
			break;
			case'd':
				Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>0),'`id`'.Eleanor::$Db->In($_POST['mass']));
			break;
			case's':
				Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!status'=>'NOT `status`'),'`id`'.Eleanor::$Db->In($_POST['mass']));
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
	if(!in_array($sort,array('id','title','status','pos')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? $_GET['so'] : 'asc';
	if($so!='desc')
		$so='asc';
	if($sort and ($sort!='pos' or $so!='asc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='pos';
	$qs+=array('sort'=>false,'so'=>false);

	$items=$subitems=array();
	if($cnt>0)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`parents`,`pos`,`status`,`title` FROM `'.$Eleanor->module['config']['t'].'` `s` INNER JOIN `'.$Eleanor->module['config']['tl'].'` `l` USING(`id`)'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			$a['_aparent']=$Eleanor->Url->Construct(array('parent'=>$a['id']));
			$a['_aswap']=$Eleanor->Url->Construct(array('swap'=>$a['id']));
			$a['_aup']=$a['pos']>1 ? $Eleanor->Url->Construct(array('up'=>$a['id'])) : false;
			$a['_adown']=$a['pos']<$cnt ? $Eleanor->Url->Construct(array('down'=>$a['id'])) : false;
			$a['_aaddp']=$Eleanor->Url->Construct(array('do'=>'add','parent'=>$a['id']));

			$subitems[]=$a['parents'].$a['id'].',';
			$items[$a['id']]=array_slice($a,2);
		}
	}

	if($subitems)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`parents`,`title` FROM `'.$Eleanor->module['config']['t'].'` `s` INNER JOIN `'.$Eleanor->module['config']['tl'].'` `l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `parents`'.Eleanor::$Db->In($subitems).' ORDER BY `pos` ASC');
		$subitems=array();
		while($a=$R->fetch_assoc())
		{
			$a['parents']=rtrim($a['parents'],',');
			$subitems[substr(strrchr($a['parents'],','),1)][$a['id']]=$a['title'];
		}
		foreach($subitems as &$v)
		{			asort($v,SORT_STRING);
			foreach($v as $kk=>&$vv)
				$vv=array(
					'title'=>$vv,
					'_aedit'=>$Eleanor->Url->Construct(array('edit'=>$kk)),
				);
		}
	}
	$links=array(
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_title'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'title','so'=>$qs['sort']=='title' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_pos'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'pos','so'=>$qs['sort']=='pos' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_status'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'status','so'=>$qs['sort']=='status' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page)),
	);
	$c=Eleanor::$Template->ShowList($items,$subitems,$navi,$cnt,$pp,$qs,$page,$links);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	$values=array('_onelang'=>true,'parents'=>array('value'=>isset($_GET['parent']) ? (int)$_GET['parent'] : 0));
	if($id)
	{
		$Eleanor->sc['parents']['options']['exclude']=$id;
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.$Eleanor->module['config']['t'].'` WHERE id='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			foreach($a as $k=>&$v)
				if(isset($Eleanor->sc[$k]))
					$values[$k]['value']=$v;
			$R=Eleanor::$Db->Query('SELECT `language`,`uri`,`title`,`text`,`meta_title`,`meta_descr` FROM `'.$Eleanor->module['config']['tl'].'` WHERE `id`='.$id);
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
				$values['_onelang']=(!is_array($values['title']) or count($values['title']['value'])==1 and isset($values['title']['value'][LANGUAGE]));
				foreach(Eleanor::$langs as $k=>&$v)
					if(!isset($values['title']['value'][$k]))
						$values['title']['value'][$k]=$values['text']['value'][$k]=$values['uri']['value'][$k]=$values['meta_title']['value'][$k]=$values['meta_descr']['value'][$k]='';
			}
		}
		$title[]=$lang['editing'];
	}
	else
		$title[]=$lang['adding'];

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
		if(!is_array($errors))
			$errors=array();
		$values['_onelang']=isset($_POST['_onelang']);
		$Eleanor->sc_post=true;
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$u=$Eleanor->Uploader->Show($id ? $Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$id : false);
	$values=$Eleanor->Controls->DisplayControls($Eleanor->sc,$values)+$values;
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
		'nodraft'=>$hasdraft ? $Eleanor->Url->Construct(array('do'=>$id ? false : 'add','edit'=>$id ? $id : false,'nodraft'=>1)) : false,
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);
	$c=Eleanor::$Template->AddEdit($id,$Eleanor->sc,$values,$errors,$back,$u,$hasdraft,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
	{
		$langs=(empty($_POST['lang']) or !is_array($_POST['lang'])) ? array() : $_POST['lang'];
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
			'title'=>$values['title'],
			'text'=>$values['text'],
			'uri'=>$values['uri'],
			'meta_title'=>$values['meta_title'],
			'meta_descr'=>$values['meta_descr'],
		);
	else
		$lvalues=array(
			'title'=>array(''=>$values['title']),
			'text'=>array(''=>$values['text']),
			'uri'=>array(''=>$values['uri']),
			'meta_title'=>array(''=>$values['meta_title']),
			'meta_descr'=>array(''=>$values['meta_descr']),
		);
	unset($values['uri'],$values['text'],$values['title'],$values['meta_title'],$values['meta_descr']);

	$ml=in_array('',$langs) ? Language::$main : '';
	foreach(array('title','text') as $field)
		foreach($lvalues[$field] as $k=>&$v)
			if($v=='')
			{				$er=strtoupper('empty_'.$field.($k ? '_'.$k : ''));
				$errors[$er]=$lang['empty_'.$field]($k);
			}

	if($errors)
		return AddEdit($id,$errors);

	foreach($lvalues['uri'] as $k=>&$v)
	{
		if($v=='')
			$v=htmlspecialchars_decode($lvalues['title'][$k],ELENT);
		$v=$Eleanor->Url->Filter($v,$k);
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `uri`='.Eleanor::$Db->Escape($v).' AND `language`'.($k ? 'IN(\'\',\''.$k.'\')' : '=\'\'').($id ? ' AND `id`!='.$id : '').' LIMIT 1');
		if($R->num_rows>0)
			$v='';
	}

	Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `parents`,`pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.' LIMIT 1');
		if(!list($parents,$pos)=$R->fetch_row())
			return GoAway();

		$values['pos']=(int)$values['pos'];
		if($values['pos']<=0)
			$values['pos']=1;
		if($pos!=$values['pos'])
		{
			Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`-1'),'`pos`>'.$pos.' AND `parents`=\''.$parents.'\'');
			Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos'].' AND `parents`=\''.$values['parents'].'\'');
		}
		if($parents!=$values['parents'])
			Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!parents'=>'REPLACE(`parents`,\''.$parents.'\',\''.$values['parents'].'\')'),'`parents` LIKE \''.$parents.$id.',%\'');
		Eleanor::$Db->Update($Eleanor->module['config']['t'],$values,'id='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		$values=array();
		foreach($langs as &$v)
			$values[]=array(
				'id'=>$id,
				'language'=>$v,
				'uri'=>isset($lvalues['uri'][$v]) ? $lvalues['uri'][$v] : '',
				'title'=>isset($lvalues['title'][$v]) ? $lvalues['title'][$v] : '',
				'text'=>isset($lvalues['text'][$v]) ? $lvalues['text'][$v] : '',
				'meta_title'=>isset($lvalues['meta_title'][$v]) ? $lvalues['meta_title'][$v] : '',
				'meta_descr'=>isset($lvalues['meta_descr'][$v]) ? $lvalues['meta_descr'][$v] : '',
				'last_mod'=>date('Y-m-d H:i:s'),
			);
		Eleanor::$Db->Replace($Eleanor->module['config']['tl'],$values);
	}
	else
	{
		Eleanor::$Db->Transaction();#Все ради аплоадера
		if($values['pos']=='')
		{
			$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.$Eleanor->module['config']['t'].'` WHERE `parents`=\''.$values['parents'].'\'');
			list($pos)=$R->fetch_row();
			$values['pos']=$pos===null ? 1 : $pos+1;
		}
		else
		{
			if($values['pos']<=0)
				$values['pos']=1;
			Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos'].' AND `parents`=\''.$values['parents'].'\'');
		}
		$id=Eleanor::$Db->Insert($Eleanor->module['config']['t'],$values);
		try
		{
			$ft=$Eleanor->Uploader->MoveFiles($Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$id);
		}
		catch(EE $E)
		{
			Eleanor::$Db->Rollback();
			return AddEdit($id,$E->getMessage());
		}
		$values=array('id'=>array(),'language'=>array(),'uri'=>array(),'title'=>array(),'text'=>array(),'meta_title'=>array(),'meta_descr'=>array());
		foreach($langs as &$v)
		{
			$values['id'][]=$id;
			$values['last_mod'][]=date('Y-m-d H:i:s');
			$values['language'][]=$v;
			$values['uri'][]=isset($lvalues['uri'][$v]) ? $lvalues['uri'][$v] : '';
			$values['title'][]=isset($lvalues['title'][$v]) ? str_replace($ft['from'],$ft['to'],$lvalues['title'][$v]) : '';
			$values['text'][]=isset($lvalues['text'][$v]) ? str_replace($ft['from'],$ft['to'],$lvalues['text'][$v]) : '';
			$values['meta_title'][]=isset($lvalues['meta_title'][$v]) ? $lvalues['meta_title'][$v] : '';
			$values['meta_descr'][]=isset($lvalues['meta_descr'][$v]) ? $lvalues['meta_descr'][$v] : '';
		}
		Eleanor::$Db->Insert($Eleanor->module['config']['tl'],$values);
		Eleanor::$Db->Commit();
	}
	Eleanor::$Cache->Lib->CleanByTag($Eleanor->module['config']['n']);
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}

function Resort($p='')
{global$Eleanor;
	$R=Eleanor::$Db->Query('SELECT `id`,`pos` FROM `'.$Eleanor->module['config']['t'].'` WHERE `parents`=\''.$p.'\' ORDER BY `pos` ASC');
	$cnt=1;
	while($a=$R->fetch_assoc())
	{
		if($a['pos']!=$cnt)
			Eleanor::$Db->Update($Eleanor->module['config']['t'],array('pos'=>$cnt),'`id`='.$a['id'].' LIMIT 1');
		++$cnt;
	}
}