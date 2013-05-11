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
$lang=Eleanor::$Language->Load('addons/admin/langs/smiles-*.php','smiles');
Eleanor::$Template->queue[]='Smiles';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
	'addgroup'=>$Eleanor->Url->Construct(array('do'=>'addgroup')),
);

$u=uniqid();
$Eleanor->sm_post=false;
$Eleanor->sm=array(
	'emotion'=>array(
		'title'=>$lang['emotion'],
		'descr'=>$lang['emotion_'],
		'type'=>'input',
		'default'=>'',
		'bypost'=>&$Eleanor->sm_post,
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
	'path'=>array(
		'title'=>$lang['path'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->sm_post,
		'default'=>'images/smiles/',
		'load'=>function()
		{
			$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
			$GLOBALS['head'][]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		},
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'id'=>'path-'.$u,
			),
		),
		'append'=>'<script type="text/javascript">//<![CDATA[
			$(function(){
				$("#path-'.$u.'").each(function(){
					var c=(this.value!="images/spacer.png" && this.value.match(/\.(png|jpe?g|gif|bmp)$/gi));
					$("#prev-'.$u.'").attr("src",c ? this.value : "images/spacer.png");
				}).blur(function(){
					var c=(this.value!="images/spacer.png" && this.value.match(/\.(png|jpe?g|gif|bmp)$/gi));
					$("#prev-'.$u.'").attr("src",c ? this.value : "images/spacer.png");
				}).autocomplete({
					serviceUrl:CORE.ajax_file,
					minChars:2,
					delimiter:null,
					onSelect:function(img){$("#prev-'.$u.'").attr("src",img)},
					params:{
						"direct":"admin",
						"file":"autocomplete",
						"filter":"types",
						"types":"jpg,png,gif,bmp,jpeg"
					}
				});
			});//]]></script>'
	),
	'preview'=>array(
		'title'=>$lang['preview'],
		'descr'=>'',
		'type'=>'',
		'options'=>array(
			'content'=>'<img src="images/spacer.png" id="prev-'.$u.'" />',
		),
	),
	'pos'=>array(
		'title'=>$lang['pos'],
		'descr'=>$lang['pos_'],
		'type'=>'input',
		'default'=>'',
		'bypost'=>&$Eleanor->sm_post,
	),
	'status'=>array(
		'title'=>$lang['status'],
		'descr'=>'',
		'default'=>true,
		'type'=>'check',
		'bypost'=>&$Eleanor->sm_post,
	),
	'show'=>array(
		'title'=>$lang['show'],
		'descr'=>'',
		'default'=>true,
		'type'=>'check',
		'bypost'=>&$Eleanor->sm_post,
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
		case'addgroup':
			$title[]=$lang['gadd'];
			$rl=strlen(Eleanor::$root);
			$data=array(
				'added'=>false,
				'smiles'=>array(),
				'folder'=>'images/smiles',
				'error'=>''
			);
			if(!empty($_POST['folder']) and is_string($_POST['folder']))
				do
				{
					$data['folder']=$_POST['folder'];
					$f=Eleanor::FormatPath($_POST['folder']);
					$f=realpath($f).'/';
					if(!is_dir($f) or strpos($f,Eleanor::$root)!==0)
					{
						$data['error']=$lang['fdne'];
						break;
					}
					$already=$emos=array();
					$R=Eleanor::$Db->Query('SELECT `path`,`emotion` FROM `'.P.'smiles`');
					while($a=$R->fetch_assoc())
					{
						$a['emotion']=explode(',,',trim($a['emotion'],','));
						$emos=array_merge($emos,$a['emotion']);
						$already[]=$a['path'];
					}

					if(!empty($_POST['smiles']) and is_array($_POST['smiles']))
					{
						$R=Eleanor::$Db->Query('SELECT MAX(`pos`)+1 FROM `'.P.'smiles`');
						list($pos)=$R->fetch_row();
						$toinsert=$exemo=array();
						$path=substr($f,$rl);
						$path=str_replace(DIRECTORY_SEPARATOR,'/',$path);
						foreach($_POST['smiles'] as $smv)
						{
							if(!isset($smv['f'],$smv['e']))
								continue;

							$sm=basename($smv['f']);
							if(preg_match('#\.(png|gif|jpe?g|bmp)$#',$sm)==0 or !is_file($f.$sm))
								continue;
							$em=$smv['e'] ? explode(',',$smv['e']) : array();
							foreach($em as $k=>&$v)
							{
								$v=trim($v,', ');
								if($v=='')
									unset($em[$k]);
							}
							if(!$em)
								continue;
							$exemo=array_merge($exemo,array_intersect($em,$emos));
							$smv['f']=$path.$sm;
							$data['smiles'][]=$smv+array('ch'=>true);
							$already[]=$smv['f'];
							$toinsert[]=array(
								'path'=>$smv['f'],
								'emotion'=>','.join(',,',$em).',',
								'status'=>1,
								'show'=>isset($smv['s']),
								'pos'=>$pos++,
							);
						}
						if($exemo)
						{
							$data['error']=$lang['emoexists']($exemo);
							break;
						}
						elseif($toinsert)
						{
							Eleanor::$Db->Insert(P.'smiles',$toinsert);
							$data['added']=true;
							Eleanor::$Cache->Obsolete('smiles');
						}
						else
							$data['error']=$lang['smnots'];
					}

					$data['smiles']=glob($f.'*.{png,gif,jpg,jpeg,bmp}',GLOB_BRACE);
					if($data['smiles'])
						foreach($data['smiles'] as $k=>&$v)
						{
							$v=substr($v,$rl);
							$v=str_replace(DIRECTORY_SEPARATOR,'/',$v);
							if(in_array($v,$already))
								unset($data['smiles'][$k]);
							else
							{
								$bn=basename($v);
								$v=array('f'=>$v,'e'=>':'.preg_replace('#\.([a-z]{3,4})$#','',$bn).':','s'=>false,'ch'=>false,'v'=>$bn);
							}
						}
					if(!$data['smiles'] and !$data['added'])
						$data['error']=$lang['smnf'];
				}while(false);
			$c=Eleanor::$Template->AddGroupSmiles($data);
			Start();
			echo$c;
		break;
		case'resort':
			Resort();
			GoAway();
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
	if(!Eleanor::$our_query)
		return GoAway(true);
	Eleanor::$Db->Update(P.'smiles',array('!status'=>'NOT `status`'),'`id`='.(int)$_GET['swap'].' LIMIT 1');
	Eleanor::$Cache->Obsolete('smiles');
	GoAway();
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `path`,`emotion`,`pos` FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'smiles','`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Update(P.'smiles',array('!pos'=>'`pos`-1'),'`pos`>'.$a['pos']);
		Eleanor::$Cache->Obsolete('smiles');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$a['emotion']=str_replace(',,',', ',trim($a['emotion'],','));
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;
}
elseif(isset($_GET['up']))
{
	$id=(int)$_GET['up'];
	$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
	if($R->num_rows==0 or !Eleanor::$our_query)
		return GoAway();
	list($posit)=$R->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'smiles` WHERE `pos`=(SELECT MAX(`pos`) FROM `'.P.'smiles` WHERE `pos`<'.$posit.')');
	list($cnt)=$R->fetch_row();
	if($cnt>0)
	{
		if($cnt>1)
		{
			Resort();
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update(P.'smiles',array('!pos'=>'`pos`+1'),'`pos`='.--$posit.' LIMIT 1');
		Eleanor::$Db->Update(P.'smiles',array('!pos'=>'`pos`-1'),'`id`='.$id.' LIMIT 1');
	}
	Eleanor::$Cache->Obsolete('smiles');
	GoAway();
}
elseif(isset($_GET['down']))
{
	$id=(int)$_GET['down'];
	$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
	if($R->num_rows==0 or !Eleanor::$our_query)
		return GoAway();
	list($posit)=$R4->fetch_row();
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'smiles` WHERE `pos`=(SELECT MIN(`pos`) FROM `'.P.'smiles` WHERE `pos`>'.$posit.')');
	list($cnt)=$R->fetch_row();
	if($cnt>0)
	{
		list($cnt)=$R->fetch_row();
		if($cnt>1)
		{
			Resort();
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
			list($posit)=$R->fetch_row();
		}
		Eleanor::$Db->Update(P.'smiles', array('!pos'=>'`pos`-1'),'`pos`='.++$posit.' LIMIT 1');
		Eleanor::$Db->Update(P.'smiles', array('!pos'=>'`pos`+1'),'`id`='.$id.' LIMIT 1');
	}
	Eleanor::$Cache->Obsolete('smiles');
	GoAway();
}
else
	ShowList();

function ShowList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['smiles']['list'];

	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']))
	{
		$in=Eleanor::$Db->In($_POST['mass']);
		switch($_POST['op'])
		{
			case'd':
				Eleanor::$Db->Update(P.'smiles',array('status'=>0),'`id`'.$in);
			break;
			case'a':
				Eleanor::$Db->Update(P.'smiles',array('status'=>1),'`id`'.$in);
			break;
			case'k':
				Eleanor::$Db->Delete(P.'smiles','`id`'.$in);
		}
		Eleanor::$Cache->Obsolete('smiles');
	}
	$qs=$items=array();
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'smiles`');
	list($cnt)=$R->fetch_row();

	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	if($page<=0)
		$page=50;
	if(isset($_GET['new-pp']) and 4<$pp=(int)$_GET['new-pp'])
		Eleanor::SetCookie('per-page',$pp);
	else
		$pp=abs((int)Eleanor::GetCookie('per-page'));
	if($pp<5 or $pp>500)
		$pp=30;
	$offset=abs(($page-1)*$pp);
	if($cnt and $offset>=$cnt)
		$offset=max(0,$cnt-$pp);
	$sort=isset($_GET['sort']) ? (string)$_GET['sort'] : '';
	if(!in_array($sort,array('id','emotion','path','status','pos','show')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'asc';
	if($so!='desc')
		$so='asc';
	if($sort)
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='pos';
	$qs+=array('sort'=>false,'so'=>false);

	if($cnt>0)
	{
		$sasc=$qs['sort']=='pos' && $qs['so']=='asc';
		$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'smiles` ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.','.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['emotion']=str_replace(',,',', ',trim($a['emotion'],','));

			$a['_ok']=is_file(Eleanor::$root.$a['path']);
			$a['_aswap']=$a['_ok'] && $a['status'] ? $Eleanor->Url->Construct(array('swap'=>$a['id'])) : false;
			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			if($sasc)
			{
				$a['_aup']=$a['pos']>0 ? $Eleanor->Url->Construct(array('up'=>$a['id'])) : false;
				$a['_adown']=$a['pos']<$cnt ? $Eleanor->Url->Construct(array('down'=>$a['id'])) : false;
			}
			else
				$a['_aup']=$a['_adown']=false;

			if(!$a['_ok'])
				Eleanor::$Db->Update(P.'smiles',array('status'=>0),'`id`='.$a['id'].' LIMIT 1');

			$items[$a['id']]=array_slice($a,1);
		}
	}

	$links=array(
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_emotion'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'emotion','so'=>$qs['sort']=='emotion' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_path'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'path','so'=>$qs['sort']=='path' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_show'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'show','so'=>$qs['sort']=='show' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_pos'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'pos','so'=>$qs['sort']=='pos' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->SmilesList($items,$cnt,$page,$pp,$qs,$links);
	Start();
	echo$c;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['smiles'];
	$values=array();
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			$a['emotion']=str_replace(',,',', ',trim($a['emotion'],','));
			foreach($a as $k=>&$v)
				if(isset($Eleanor->sm[$k]))
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
		$Eleanor->sm_post=true;
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$values=$Eleanor->Controls->DisplayControls($Eleanor->sm,$values)+$values;
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	$c=Eleanor::$Template->AddEdit($id,$Eleanor->sm,$values,$errors,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$C=new Controls;
	$C->throw=false;
	try
	{
		$values=$C->SaveControls($Eleanor->sm);
	}
	catch(EE$E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}
	$errors=$C->errors;
	$lang=Eleanor::$Language['smiles'];

	if(!is_file(Eleanor::$root.$values['path']))
		$errors[]='NOFILE';

	$values['emotion']=$values['emotion'] ? explode(',',$values['emotion']) : array();
	$exemo=array();
	foreach($values['emotion'] as $k=>&$v)
	{
		$v=trim($v,', ');
		if($v=='')
			unset($values['emotion'][$k]);
		else
		{
			$R=Eleanor::$Db->Query('SELECT `emotion` FROM `'.P.'smiles` WHERE `emotion`LIKE\'%,'.Eleanor::$Db->Escape($v,false).',%\''.($id ? ' AND `id`!='.$id : '').' LIMIT 1');
			if($a=$R->fetch_assoc())
			{
				$a['emotion']=explode(',,',trim($a['emotion'],','));
				$exemo[]=$v;
			}
		}
	}
	if($exemo)
		$errors['EMO_EXISTS']=$lang['emoexists'](array_unique($exemo));

	if(!$values['emotion'])
		$errors[]='NOEMO';

	if($errors)
		return AddEdit($id,$errors);

	$values['emotion']=','.join(',,',$values['emotion']).',';
	if($id)
	{
		$values['pos']=(int)$values['pos'];
		$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'smiles` WHERE `id`='.$id);
		list($pos)=$R->fetch_row();
		if($pos!=$values['pos'])
		{
			Eleanor::$Db->Update(P.'smiles',array('!pos'=>'`pos`-1'),'`pos`>'.$pos);
			Eleanor::$Db->Update(P.'smiles',array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos']);
		}
		Eleanor::$Db->Update(P.'smiles',$values,'`id`='.$id.' LIMIT 1');
	}
	else
	{
		if($values['pos']=='')
		{
			$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.P.'smiles`');
			list($pos)=$R->fetch_row();
			$values['pos']=$pos===null ? 0 : $pos+1;
		}
		else
			Eleanor::$Db->Update(P.'smiles',array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos']);
		Eleanor::$Db->Insert(P.'smiles',$values);
	}
	Eleanor::$Cache->Obsolete('smiles');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}

function Resort()
{
	$n=1;
	$R=Eleanor::$Db->Query('SELECT `id`,`pos` FROM `'.P.'smiles` ORDER BY `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		if($a['pos']!=$n)
			Eleanor::$Db->Update(P.'smiles',array('pos'=>$n),'`id`='.$a['id'].' LIMIT 1');
		++$n;
	}
	Eleanor::$Cache->Obsolete('smiles');
}