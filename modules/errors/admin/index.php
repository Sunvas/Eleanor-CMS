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

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
	'letters'=>$Eleanor->Url->Construct(array('do'=>'letters')),
);

$Eleanor->sc_post=false;
$Eleanor->sc=array(
	'title'=>array(
		'title'=>$lang['name'],
		'descr'=>'',
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
	'uri'=>array(
		'title'=>'URI',
		'descr'=>'',
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
	'text'=>array(
		'title'=>$lang['descr'],
		'descr'=>'',
		'type'=>'editor',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>true,
			'4alt'=>'title',
		),
	),
	'image'=>array(
		'title'=>$lang['image'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'addon'=>array(
				'id'=>'image',
			),
			'callback'=>function($a)
			{
				$imgs=Eleanor::Option('&mdash;','',!$a['value'],array(),2);
				$hdir=opendir(Eleanor::$root.'images/errors');
				while($entry=readdir($hdir))
					if(preg_match('#\.(gif|png|jpe?g|bmp)#i',$entry)>0)
						$imgs.=Eleanor::Option($entry,false,in_array($entry,$a['value']));
				closedir($hdir);
				return$imgs;
			},
		),
	),
	0=>array(
		'title'=>$lang['preview'],
		'descr'=>'',
		'type'=>'',
		'options'=>array(
			'content'=>Eleanor::$Template->ImagePreview(),
		),
	),
	'http_code'=>array(
		'title'=>$lang['http_status'],
		'descr'=>$lang['http_status_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'load'=>function($a)
		{
			if(!$a['value'])
				$a['value']='';
			return$a;
		},
		'options'=>array(
			'addon'=>array(
				'maxlenght'=>3,
			),
		),
	),
	'mail'=>array(
		'title'=>'E-mail',
		'descr'=>$lang['mail_'],
		'type'=>'edit',
		'bypost'=>&$Eleanor->sc_post,
		'save'=>function($a,$Obj)
		{
			if($a['value'] and !Strings::CheckEmail($a['value']))
				$Obj->errors[]='INCORRECT_EMAIL';
			else
				return$a['value'];
		},
		'options'=>array(
			'htmlsafe'=>false,
		),
	),
	'log'=>array(
		'title'=>$lang['log'],
		'descr'=>$lang['log_'],
		'type'=>'check',
		'bypost'=>&$Eleanor->sc_post,
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
		case'letters':
			$post=false;
			$controls=array(
				$lang['letter_error'],
				'error_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['letter_error_'],
					'type'=>'edit',
					'multilang'=>true,
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'error'=>array(
					'title'=>$lang['letterdescr'],
					'descr'=>$lang['letter_error_'],
					'type'=>'editor',
					'multilang'=>true,
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
			);

			$values=array();
			$multilang=Eleanor::$vars['multilang'] ? array_keys(Eleanor::$langs) : array(Language::$main);
			if($_SERVER['REQUEST_METHOD']=='POST')
			{
				$letter=$Eleanor->Controls->SaveControls($controls);
				if(Eleanor::$vars['multilang'])
					foreach($multilang as &$lng)
					{
						$tosave=array();
						foreach($letter as $k=>&$v)
							$tosave[$k]=isset($v[$lng]) ? $v[$lng] : '';
						$file=$Eleanor->module['path'].'letters-'.$lng.'.php';
						file_put_contents($file,'<?php return '.var_export($tosave,true).';');
					}
				else
				{
					$file=$Eleanor->module['path'].'letters-'.Language::$main.'.php';
					file_put_contents($file,'<?php return '.var_export($letter,true));
				}
			}
			else
				foreach($multilang as &$lng)
				{
					$file=$Eleanor->module['path'].'letters-'.$lng.'.php';
					$letter=file_exists($file) ? (array)include $file : array();
					$letter+=array(
						'error_t'=>'',
						'error'=>'',
					);
					foreach($letter as $k=>&$v)
						$values[$k]['value'][$lng]=$v;
				}
			$values=$Eleanor->Controls->DisplayControls($controls,$values)+$values;
			$title[]=$lang['letters'];
			$c=Eleanor::$Template->Letters($controls,$values,'');
			Start();
			echo$c;
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
	$R=Eleanor::$Db->Query('SELECT `title` FROM `'.$Eleanor->module['config']['t'].'` LEFT JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$id);
		Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`='.$id);
		Eleanor::$Db->Delete(P.'drafts','`key`=\''.$Eleanor->module['config']['n'].'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;
}
else
	ShowList();

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
		if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
		{
			$qs['']['fi']['title']=$_REQUEST['fi']['title'];
			$where[]='`title` LIKE \'%'.Eleanor::$Db->Escape($qs['']['fi']['title'],false).'%\'';
		}
		if(!empty($_REQUEST['fi']['email']))
		{
			$qs['']['fi']['email']=$_REQUEST['fi']['meail'];
			$where[]='`mail` LIKE \'%'.Eleanor::$Db->Escape($qs['']['fi']['email'],false).'%\'';
		}
	}

	$where[]='`language` IN (\'\',\''.Language::$main.'\')';
	$where=' WHERE '.join(' AND ',$where);
	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']) and is_array($_POST['mass']))
		switch($_POST['op'])
		{
			case'k':
				$ids_=Eleanor::$Db->In($_POST['mass']);
				Eleanor::$Db->Delete($Eleanor->module['config']['t'],'`id`'.$ids_);
				Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`'.$ids_);
				foreach($_POST['mass'] as &$v)
					Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$Eleanor->module['config']['n'].DIRECTORY_SEPARATOR.$v);
		}
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$Eleanor->module['config']['t'].'` `s` INNER JOIN `'.$Eleanor->module['config']['tl'].'` `l` USING(`id`)'.$where);
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
	if(!in_array($sort,array('id','title','mail','log')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? $_GET['so'] : 'desc';
	if($so!='asc')
		$so='desc';
	if($sort and ($sort!='id' or $so!='desc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	$items=array();
	if($cnt>0)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`mail`,`log`,`uri`,`title` FROM `'.$Eleanor->module['config']['t'].'` `s` INNER JOIN `'.$Eleanor->module['config']['tl'].'` `l` USING(`id`)'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));

			$items[$a['id']]=array_slice($a,1);
		}
	}

	$links=array(
		'sort_title'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'title','so'=>$qs['sort']=='title' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_mail'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'mail','so'=>$qs['sort']=='mail' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_log'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'log','so'=>$qs['sort']=='log' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page)),
	);
	$c=Eleanor::$Template->ShowList($items,$cnt,$pp,$qs,$page,$links);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];
	$values=array('_onelang'=>true);
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
		if($errors===true)
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
	catch(EE $E)
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
		foreach($lvalues[$field] as $k=>&$v)#Не ставить &$v, иначе в месте 1 (см ниже) после >In($langs), значение получается в пастрофах ($lang['english']=="'english'"
			if($v=='')
			{
				$er=strtoupper('empty_'.$field.($k ? '_'.$k : ''));
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
		Eleanor::$Db->Update($Eleanor->module['config']['t'],$values,'id='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($Eleanor->module['config']['tl'],'`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		$values=array();
		foreach($langs as &$v)
			$values[]=array(
				'id'=>$id,
				'language'=>$v,
				'uri'=>isset($lvalues['uri'][$lng]) ? $lvalues['uri'][$lng] : '',
				'title'=>isset($lvalues['title'][$lng]) ? $lvalues['title'][$lng] : '',
				'text'=>isset($lvalues['text'][$lng]) ? $lvalues['text'][$lng] : '',
				'meta_title'=>isset($lvalues['meta_title'][$lng]) ? $lvalues['meta_title'][$lng] : '',
				'meta_descr'=>isset($lvalues['meta_descr'][$lng]) ? $lvalues['meta_descr'][$lng] : '',
				'last_mod'=>date('Y-m-d H:i:s'),
			);
		Eleanor::$Db->Replace($Eleanor->module['config']['tl'],$values);
	}
	else
	{
		Eleanor::$Db->Transaction();#Все ради аплоадера
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
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}