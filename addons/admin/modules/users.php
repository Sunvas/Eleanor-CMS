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
$lang=Eleanor::$Language->Load('addons/admin/langs/users-*.php','users');
$langg=Eleanor::$Language->Load('addons/admin/langs/groups-*.php',false);
Eleanor::$Template->queue[]='Users';

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
	'online'=>$Eleanor->Url->Construct(array('do'=>'online')),
	'letters'=>$Eleanor->Url->Construct(array('do'=>'letters')),
	'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
);

$Eleanor->us_post=false;
$Eleanor->us=array(
	$lang['personal'],
	'gender'=>array(
		'title'=>$lang['gender'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'options'=>array(-1=>$lang['nogender'],$lang['female'],$lang['male']),
			'extra'=>array(
				'tabindex'=>15,
			),
		),
	),
	'bio'=>array(
		'title'=>$lang['bio'],
		'descr'=>'',
		'type'=>'text',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>16,
			),
		),
	),
	'interests'=>array(
		'title'=>$lang['interests'],
		'descr'=>'',
		'type'=>'text',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>17,
			),
		),
	),
	'location'=>array(
		'title'=>$lang['location'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>18,
			),
		),
	),
	'site'=>array(
		'title'=>$lang['site'],
		'descr'=>$lang['site_'],
		'type'=>'input',
		'save'=>function($a,$Obj)
		{
			if($a['value'] and !Strings::CheckUrl($a['value']))
				$Obj->errors[]='SITE_ERROR';
			else
				return$a['value'];
		},
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'type'=>'url',
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>19,
			),
		),
	),
	'signature'=>array(
		'title'=>$lang['signature'],
		'descr'=>'',
		'type'=>'editor',
		'bypost'=>&$Eleanor->us_post,
		'extra'=>array(
			'no'=>array('tabindex'=>20)
		)
	),
	$lang['connect'],
	'jabber'=>array(
		'title'=>'Jabber',
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>21,
			),
		),
	),
	'skype'=>array(
		'title'=>'Skype',
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>22,
			),
		),
	),
	'icq'=>array(
		'title'=>'ICQ',
		'descr'=>'',
		'type'=>'input',
		'save'=>function($a,$Obj)
		{
			$v=preg_replace('#[^0-9\s,]+#','',$a['value']);
			if($v and !isset($v[4]))
				$Obj->errors[]='SHORT_ICQ';
			else
				return$v;
		},
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>23,
			),
		),
	),
	'vk'=>array(
		'title'=>$lang['vk'],
		'descr'=>$lang['vk_'],
		'type'=>'input',
		'save'=>'SaveVK',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>24,
			),
		),
	),
	'facebook'=>array(
		'title'=>'Facebook',
		'descr'=>'',
		'type'=>'input',
		'save'=>'SaveVK',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>25,
			),
		),
	),
	'twitter'=>array(
		'title'=>'Twitter',
		'descr'=>$lang['twitter_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>26,
			),
		),
	),
	Eleanor::$Language['main']['options'],
	'theme'=>array(
		'title'=>$lang['theme'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'callback'=>function($value) use ($lang)
			{
				$templates=Eleanor::Option($lang['by_default'],'',in_array('',$value['value']));
				if(Eleanor::$vars['templates'] and is_array(Eleanor::$vars['templates']))
					foreach(Eleanor::$vars['templates'] as &$v)
					{
						$f=Eleanor::$root.'templates/'.$v.'.php';
						if(!file_exists($f))
							continue;
						$a=include($f);
						$name=(is_array($a) and isset($a['name'])) ? $a['name'] : $v;
						$templates.=Eleanor::Option($name,$v,in_array($v,$value['value']));
					}
				return$templates;
			},
			'extra'=>array(
				'tabindex'=>27,
			),
		),
	),
	'editor'=>array(
		'title'=>$lang['editor'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'callback'=>function($value) use ($lang)
			{
				return array(''=>$lang['by_default'])+$GLOBALS['Eleanor']->Editor->editors;
			},
			'extra'=>array(
				'tabindex'=>28,
			),
		),
	),
);

$Eleanor->gp=array(
	$langg['global_r'],
	'access_cp'=>array(
		'title'=>$langg['aa'],
		'descr'=>'',
		'type'=>'check',
		'bypost'=>&$Eleanor->us_post,
		'options'=>array(
			'extra'=>array('onclick'=>'if(this.checked) return confirm(\''.$langg['are_you_sure'].'\')'),
		),
	),
	'banned'=>array(
		'title'=>$langg['ban'],
		'descr'=>$langg['ban_'],
		'bypost'=>&$Eleanor->us_post,
		'type'=>'check',
	),
	'captcha'=>array(
		'title'=>$langg['captcha'],
		'descr'=>$langg['captcha_'],
		'bypost'=>&$Eleanor->us_post,
		'type'=>'check',
	),
	'moderate'=>array(
		'title'=>$langg['moderate'],
		'descr'=>$langg['moderate_'],
		'bypost'=>&$Eleanor->us_post,
		'type'=>'check',
	),
	'sh_cls'=>array(
		'title'=>$langg['cls'],
		'descr'=>'',
		'bypost'=>&$Eleanor->us_post,
		'type'=>'check',
	),
	$langg['limits'],
	'flood_limit'=>array(
		'title'=>$langg['flood_limit'],
		'descr'=>$langg['flood_limit_'],
		'bypost'=>&$Eleanor->us_post,
		'save'=>'IntSave',
		'type'=>'input',
	),
	'search_limit'=>array(
		'title'=>$langg['search_limit'],
		'descr'=>$langg['search_limit_'],
		'bypost'=>&$Eleanor->us_post,
		'save'=>'IntSave',
		'type'=>'input',
	),
	'max_upload'=>array(
		'title'=>$langg['max_size'],
		'descr'=>$langg['max_size_'],
		'bypost'=>&$Eleanor->us_post,
		'type'=>'input',
		'default'=>0,
	),
);

Eleanor::LoadOptions('user-profile',false);
$Eleanor->avatar=array(
	'type'=>'uploadimage',
	'name'=>'a',
	'default'=>'',
	'bypost'=>&$Eleanor->us_post,
	'options'=>array(
		'types'=>array('png','jpeg','jpg','bmp','gif'),
		'path'=>Eleanor::$uploads.'/avatars',
		'max_size'=>Eleanor::$vars['avatar_bytes'],
		'max_image_size'=>Eleanor::$vars['avatar_size'],
		'filename'=>function($a)
		{
			return isset($a['id']) ? 'av-'.$a['id'].strrchr($a['filename'],'.') : $a['filename'];
		},
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
		case'options':
			$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
			$c=$Eleanor->Settings->GetInterface('group','users-on-site');
			if($c)
			{
				$c=Eleanor::$Template->Options($c);
				Start();
				echo$c;
			}
		break;
		case'letters':
			$post=false;
			$controls=array(
				$lang['letter4new'],
				'new_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['descr4new'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'new'=>array(
					'title'=>$lang['letterdescr'],
					'descr'=>$lang['descr4new'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				$lang['letter4name'],
				'name_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['descr4name'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'name'=>array(
					'title'=>$lang['letterdescr'],
					'descr'=>$lang['descr4name'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				$lang['letter4pass'],
				'pass_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['descr4name'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'pass'=>array(
					'title'=>$lang['letterdescr'],
					'descr'=>$lang['descr4name'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
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
				$post=true;
				$letter=$Eleanor->Controls->SaveControls($controls);
				if(Eleanor::$vars['multilang'])
					foreach($multilang as &$lng)
					{
						$tosave=array();
						foreach($letter as $k=>&$v)
							$tosave[$k]=$controls[$k]['multilang'] ? Eleanor::FilterLangValues($v,$lng) : $v;
						$file=Eleanor::$root.'addons/admin/letters/users-'.$lng.'.php';
						file_put_contents($file,'<?php return '.var_export($tosave,true).';');
					}
				else
				{
					$file=Eleanor::$root.'addons/admin/letters/users-'.LANGUAGE.'.php';
					file_put_contents($file,'<?php return '.var_export($letter,true).';');
				}
			}
			else
				foreach($multilang as &$lng)
				{
					$letters=array();
					$file=Eleanor::$root.'addons/admin/letters/users-'.$lng.'.php';
					$letter=file_exists($file) ? (array)include $file : array();
					$letter+=array(
						'new_t'=>'',
						'new'=>'',
						'name_t'=>'',
						'name'=>'',
						'pass_t'=>'',
						'pass'=>'',
					);
					if(Eleanor::$vars['multilang'])
						foreach($letter as $k=>$v)
							$values[$k]['value'][$lng]=$v;
					else
						foreach($letter as $k=>$v)
							$values[$k]['value']=$v;
				}
			$values=$Eleanor->Controls->DisplayControls($controls,$values)+$values;
			$title[]=$lang['letters'];
			$c=Eleanor::$Template->Letters($controls,$values);
			Start();
			echo$c;
		break;
		case'userlist':
			$st=Eleanor::$Db===Eleanor::$UsersDb;
			$table=$st ? USERS_TABLE : P.'users_site';
			if(isset($_POST['name']))
			{
				Eleanor::StartSession();
				$_SESSION['query']=$query=' WHERE `name` LIKE \'%'.Eleanor::$Db->Escape((string)$_POST['name'],false).'%\'';
				$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$table.'`'.$query);
				list($_SESSION['total'])=$R->fetch_row();
				$total=$_SESSION['total'];
				$qs=array('search'=>session_id());
			}
			elseif(isset($_GET['search']))
			{
				Eleanor::StartSession((string)$_GET['search']);
				if(isset($_SESSION['total']))
					$total=$_SESSION['total'];
				if(isset($_SESSION['query']))
					$query=$_SESSION['query'];
			}
			if(!isset($total))
			{
				$query=' ORDER BY `name` ASC';
				$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$table.'`');
				list($total)=$R->fetch_row();
				$qs=array();
			}

			$pp=30;
			$Eleanor->Url->SetPrefix(array('do'=>'userlist'),true);
			$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
			if($page<=0)
				$page=1;
			$offset=abs(($page-1)*$pp);
			$groups=$users=array();
			if($st)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`name` FROM `'.$table.'`'.$query.' LIMIT '.$offset.', '.$pp);
				while($a=$R->fetch_assoc())
					$users[$a['id']]=array_slice($a,1);
			}
			if(!$st or $users)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`groups`,`name` FROM `'.P.'users_site`'.($st ? ' WHERE `id`'.Eleanor::$Db->In(array_keys($users)) : $query));
				while($a=$R->fetch_assoc())
				{
					$a['_group']=$a['groups'] ? (int)trim($a['groups'],',') : 0;
					if($a['_group'])
						$groups[]=$a['_group'];

					$a['_a']=Eleanor::$Login->UserLink($a['name'],$a['id']);
					if($st)
						$users[$a['id']]+=$a;
					else
						$users[$a['id']]=array_slice($a,1);
				}
			}
			if($groups)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
				$groups=array();
				while($a=$R->fetch_assoc())
				{
					$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
					$groups[$a['id']]=array_slice($a,1);
				}
			}
			$values=array(
				'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
			);
			$links=array(
				'first_page'=>$Eleanor->Url->Construct($qs),
				'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
			);
			$c=Eleanor::$Template->FindUsers($users,$groups,$total,$pp,$page,$values,$links);
			Start('');
			echo$c;
		break;
		case'online':
			$title[]=$lang['whoonline'];
			$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$d=date('Y-m-d H:i:s');
			$where=array('expire'=>'`s`.`expire`>\''.$d.'\'');
			$qs=array('do'=>'online');
			if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
			{
				if($_SERVER['REQUEST_METHOD']=='POST')
					$page=1;
				$qs['']['fi']=array();
				if(isset($_REQUEST['fi']['online']))
				{
					$qs['']['fi']['online']=(int)$_REQUEST['fi']['online'];
					if($qs['']['fi']['online']==1)
						unset($where['expire']);
					else
						$where['expire']='`s`.`expire`<\''.$d.'\'';
				}
			}
			$where=$where ? ' WHERE '.join(' AND ',$where) : '';
			$R=Eleanor::$Db->Query('SELECT COUNT(`expire`) FROM `'.P.'sessions` `s`'.$where);
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
			if(!in_array($sort,array('enter','ip','location')))
				$sort='';
			$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'desc';
			if($so!='asc')
				$so='desc';
			if($sort and ($sort!='enter' or $so!='desc'))
			{
				$qs+=array('sort'=>$sort,'so'=>$so);
				switch($sort)
				{
					case'enter':
						$sort='`s`.`expire`';
					break;
					case'ip':
						$sort='`s`.`ip_guest` '.$so.', `ip_user` ';
				}
			}
			else
				$sort='`s`.`expire`';
			$qs+=array('sort'=>false,'so'=>false);

			$myuid=Eleanor::$Login->GetUserValue('id');
			$groups=$items=array();
			if($cnt>0)
			{
				$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`user_id`,`s`.`enter`,`s`.`expire`,`s`.`expire`>NOW() `_online`,`s`.`ip_guest`,`s`.`ip_user`,`s`.`service`,`s`.`browser`,`s`.`location`,`s`.`name` `botname`,`us`.`groups`,`us`.`name`,`us`.`full_name` FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id` '.$where.' ORDER BY '.$sort.' '.$so.' LIMIT '.$offset.','.$pp);
				while($a=$R->fetch_assoc())
				{
					if($a['type']=='user')
						if($a['name'])
						{
							$a['_group']=(int)ltrim($a['groups'],',');
							$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['user_id']));
							$a['_adel']=$myuid==$a['user_id'] ? false : $Eleanor->Url->Construct(array('delete'=>$a['user_id']));

							$groups[]=$a['_group'];
						}
						else
							$a['type']='guest';
					$items[]=$a;
				}
			}

			if($groups)
			{
				$R2=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
				$groups=array();
				while($a=$R2->fetch_assoc())
				{
					$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';

					$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));

					$groups[$a['id']]=array_slice($a,1);
				}
			}
			$links=array(
				'sort_ip'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'ip','so'=>$qs['sort']=='ip' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_enter'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'enter','so'=>$qs['sort']=='enter' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_location'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'location','so'=>$qs['sort']=='location' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
				'first_page'=>$Eleanor->Url->Construct($qs),
				'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
			);
			$c=Eleanor::$Template->UsersOnline($items,$groups,$cnt,$pp,$qs,$page,$links);
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
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$UsersDb->Query('SELECT `name`,`full_name` FROM `'.USERS_TABLE.'` WHERE `id`='.(int)$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query or $id==Eleanor::$Login->GetUserValue('id'))
		return GoAway();
	if(isset($_POST['ok']))
	{
		UserManager::Delete($id);
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$a['name']=htmlspecialchars($a['name'],ELENT,CHARSET);
	$title[]=$lang['delc'];
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

function IntSave($a)
{
	return abs((int)$a['value']);
}

function SaveVK($a)
{
	return preg_replace('#[^a-z0-9_\.-]+/#','',$a['value']);
}

function ShowList()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['users']['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$groups=$items=$tmp=$where=$qs=array();
	if(!empty($_REQUEST['fi']))
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
			$where[]='`u`.`name`'.$name;
		}
		if(isset($_REQUEST['fi']['sname'],$_REQUEST['fi']['snamet']) and $_REQUEST['fi']['sname']!='')
		{
			$name=Eleanor::$Db->Escape((string)$_REQUEST['fi']['sname'],false);
			switch($_REQUEST['fi']['snamet'])
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
			$qs['']['fi']['sname']=$_REQUEST['fi']['sname'];
			$qs['']['fi']['snamet']=$_REQUEST['fi']['snamet'];
			$where[]='`u`.`full_name`'.$name;
		}
		if(!empty($_REQUEST['fi']['id']))
		{
			$ints=explode(',',Tasks::FillInt($_REQUEST['fi']['id']));
			$qs['']['fi']['id']=(string)$_REQUEST['fi']['id'];
			$where[]='`id`'.Eleanor::$Db->In($ints);
		}
		if(!empty($_REQUEST['fi']['group']))
		{
			$qs['']['fi']['group']=(int)$_REQUEST['fi']['group'];
			$where[]='`groups` LIKE \','.$qs['']['fi']['group'].',\'';
		}
		if(!empty($_REQUEST['fi']['lvfrom']) and 0<$t=strtotime($_REQUEST['fi']['lvfrom']))
		{
			$qs['']['fi']['lvfrom']=$_REQUEST['fi']['lvfrom'];
			$where[]='`u`.`last_visit`>=\''.date('Y-m-d H:i:s',$t).'\'';
		}
		if(!empty($_REQUEST['fi']['lvto']) and 0<$t=strtotime($_REQUEST['fi']['lvto']))
		{
			$qs['']['fi']['lvto']=$_REQUEST['fi']['lvto'];
			$where[]='`u`.`last_visit`<=\''.date('Y-m-d H:i:s',$t).'\'';
		}
		if(!empty($_REQUEST['fi']['regfrom']) and 0<$t=strtotime($_REQUEST['fi']['regfrom']))
		{
			$qs['']['fi']['regfrom']=$_REQUEST['fi']['regfrom'];
			$where[]='`u`.`register`>=\''.date('Y-m-d H:i:s',$t).'\'';
		}
		if(!empty($_REQUEST['fi']['regto']) and 0<$t=strtotime($_REQUEST['fi']['regto']))
		{
			$qs['']['fi']['regto']=$_REQUEST['fi']['regto'];
			$where[]='`u`.`register`<=\''.date('Y-m-d H:i:s',$t).'\'';
		}
		if(!empty($_REQUEST['fi']['ip']))
		{
			$qs['']['fi']['ip']=$_REQUEST['fi']['ip'];
			$ip=Eleanor::$Db->Escape($_REQUEST['fi']['ip'],false);
			$where[]='`ip` LIKE \''.str_replace('*','%',$ip).'\'';
		}
		if(!empty($_REQUEST['fi']['email']))
		{
			$qs['']['fi']['email']=$_REQUEST['fi']['email'];
			$email=Eleanor::$Db->Escape($_REQUEST['fi']['email'],false);
			$where[]='`email` LIKE \''.str_replace('*','%',$email).'\'';
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';
	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']) and is_array($_POST['mass']))
		switch($_POST['op'])
		{
			case'd':
				$myid=Eleanor::$Login->GetUserValue('id');
				if(false!==$p=array_search($myid,$_POST['mass']))
					unset($_POST['mass'][$p]);
				UserManager::Delete($_POST['mass']);
		}

	if(Eleanor::$Db===Eleanor::$UsersDb)
	{
		$table=USERS_TABLE;
		$where=' INNER JOIN `'.P.'users_site` USING(`id`)'.$where;
	}
	else
		$table=P.'users_site';
	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$table.'` `u` INNER JOIN `'.P.'users_extra` USING(`id`)'.$where);
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
	if(!in_array($sort,array('id','name','email','groups','full_name','last_visit')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'desc';
	if($so!='asc')
		$so='desc';
	if($sort and ($sort!='id' or $so!='desc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	if($cnt>0)
	{
		$myuid=Eleanor::$Login->GetUserValue('id');
		$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`email`,`groups`,`ip`,`u`.`last_visit` FROM `'.$table.'` `u` INNER JOIN `'.P.'users_extra` USING(`id`)'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['groups']=$a['groups'] ? explode(',,',trim($a['groups'],',')) : array();
			if($a['groups'])
				$groups=array_merge($groups,$a['groups']);

			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$myuid==$a['id'] ? false : $Eleanor->Url->Construct(array('delete'=>$a['id']));

			$items[]=$a;
		}
	}

	if($groups)
	{
		$pref=$Eleanor->Url->file.'?section=management&amp;module=groups&amp;';
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
		$groups=array();
		while($a=$R->fetch_assoc())
		{
			$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
			$a['_aedit']=$pref.$Eleanor->Url->Construct(array('edit'=>$a['id']),false);
			$groups[$a['id']]=array_slice($a,1);
		}
	}

	$links=array(
		'sort_name'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'name','so'=>$qs['sort']=='name' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_email'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'email','so'=>$qs['sort']=='email' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_group'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'groups','so'=>$qs['sort']=='groups' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_visit'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'last_visit','so'=>$qs['sort']=='last_visit' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_ip'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'ip','so'=>$qs['sort']=='ip' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->ShowList($items,$groups,$cnt,$pp,$qs,$page,$links);
	Start();
	echo$c;
}

function AddEdit($id,$error='')
{global$Eleanor,$title;
	$uid=Eleanor::$Login->GetUserValue('id');
	$overload=$values=array();
	$lang=Eleanor::$Language['users'];
	if($id)
	{
		$R=Eleanor::$UsersDb->Query('SELECT * FROM `'.USERS_TABLE.'` WHERE `id`='.$id.' LIMIT 1');
		if(!$values=$R->fetch_assoc())
			return GoAway(true);
		$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'users_site` WHERE `id`='.$id.' LIMIT 1');
		$values+=$R->fetch_assoc();
		$values+=array(
			'_slnew'=>false,
			'_slname'=>true,
			'_slpass'=>true,
			'_cleanfla'=>false,
			'_overskip'=>array(),
			'_externalauth'=>array(),
			'_sessions'=>array(),
			'pass'=>'',
			'pass2'=>'',
		);
		$values['failed_logins']=$values['failed_logins'] ? (array)unserialize($values['failed_logins']) : array();

		$R=Eleanor::$Db->Query('SELECT `provider`,`provider_uid`,`identity` FROM `'.P.'users_external_auth` WHERE `id`='.$id);
		while($a=$R->fetch_assoc())
			$values['_externalauth'][]=$a;

		$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$id.' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			$cl=get_class(Eleanor::$Login);
			$lk=Eleanor::$Login->GetUserValue('login_key');
			$values['_sessions']=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
			foreach($values['_sessions'] as $cl=>&$sess)
				foreach($sess as $k=>&$v)
					$v['_candel']=$uid!=$id || $k!=$lk;
		}

		if(!$error)
		{
			$values['groups']=$values['groups'] ? explode(',,',trim($values['groups'],',')) : array();
			if($values['groups'])
			{
				$values['_group']=reset($values['groups']);
				$k=key($values['groups']);
				unset($values['groups'][$k]);
			}
			else
				$values['_group']=GROUP_USER;

			$values['groups_overload']=$values['groups_overload'] ? (array)unserialize($values['groups_overload']) : array();
			if(!isset($values['groups_overload']['value']) or !is_array($values['groups_overload']['value']))
				$values['groups_overload']['value']=array();
			foreach($Eleanor->gp as &$gpv)
				foreach($values['groups_overload']['value'] as $k=>&$v)
					if(isset($gpv[$k]))
					{
						$overload[$k]['value']=$v;
						unset($values['groups_overload']['value'][$k]);
						continue;
					}
			if(!isset($values['groups_overload']['method']) or !is_array($values['groups_overload']['method']))
				$values['groups_overload']['method']=array();
			$values['_overskip']=$values['groups_overload']['method'];
			unset($values['groups_overload']);

			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'users_extra` WHERE `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			if($a['avatar_type']=='local' and $a['avatar_location'])
				$a['avatar_location']='images/avatars/'.$a['avatar_location'];
			foreach($a as $k=>&$v)
				if(isset($Eleanor->us[$k]))
					$values[$k]['value']=$v;
				else
					$values[$k]=$v;
			$values['_aupload']=$a['avatar_type']!='local';
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['adding'];
		$values=array(
			'full_name'=>'',
			'name'=>'',
			'email'=>'',
			'_group'=>GROUP_USER,
			'groups'=>array(),
			'language'=>'',
			'banned_until'=>'',
			'ban_explain'=>'',
			'staticip'=>false,
			'last_visit'=>'',
			'_slnew'=>true,
			'_slname'=>true,
			'_slpass'=>true,
			'_cleanfla'=>false,
			'_overskip'=>array(),
			'_aupload'=>false,
			'_sessions'=>array(),
			'pass'=>'',
			'pass2'=>'',
			'timezone'=>'',
			'failed_logins'=>array(),
			'avatar_location'=>false,
		);
		foreach($Eleanor->gp as $k=>&$v)
			if(is_array($v))
				$values['_overskip'][]=$k;
		$values['register']=date('Y-m-d H:i:s');
	}

	if($error)
	{
		if($error===true)
			$error='';
		$Eleanor->us_post=$bypost=true;
		$values['full_name']=isset($_POST['full_name']) ? (string)$_POST['full_name'] : '';
		$values['name']=isset($_POST['name']) ? (string)$_POST['name'] : '';
		$values['email']=isset($_POST['email']) ? (string)$_POST['email'] : '';
		$values['_group']=isset($_POST['_group']) ? (int)$_POST['_group'] : '';
		$values['groups']=isset($_POST['groups']) ? (array)$_POST['groups'] : array();
		$values['banned_until']=isset($_POST['banned_until']) ? (string)$_POST['banned_until'] : '';
		$values['ban_explain']=isset($_POST['ban_explain']) ? (string)$_POST['ban_explain'] : '';
		$values['language']=isset($_POST['language']) ? (string)$_POST['language'] : '';
		$values['timezone']=isset($_POST['timezone']) ? (string)$_POST['timezone'] : '';
		$values['staticip']=isset($_POST['staticip']);
		$values['_slnew']=isset($_POST['_slnew']);
		$values['_slname']=isset($_POST['_slname']);
		$values['_slpass']=isset($_POST['_slpass']);
		$values['_cleanfla']=isset($_POST['_cleanfla']);
		$values['_overskip']=isset($_POST['_overskip']) ? (array)$_POST['_overskip'] : array();
		$values['_atype']=isset($_POST['_atype']) ? $_POST['_atype']=='upload' : false;
		$values['pass']=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
		$values['pass2']=isset($_POST['pass2']) ? (string)$_POST['pass2'] : '';
		$values['_aupload']=isset($_POST['_atype']) && $_POST['_atype']=='upload';
		$values['avatar_location']=isset($_POST['avatar_location']) ? (string)$_POST['avatar_location'] : '';
	}
	else
	{
		$al=$values['avatar_location'] ? ($values['_aupload'] && strpos($values['avatar_location'],'://')===false ? Eleanor::$uploads.'/avatars/' : '').$values['avatar_location'] : '';
		if($values['_aupload'])
		{
			$Eleanor->avatar['value']=$al;
			$values['avatar_location']='';
		}
		else
			$values['avatar_location']=$al;
		$bypost=false;
	}

	$Eleanor->Controls->arrname=array('avatar');
	$upavatar=$Eleanor->Controls->DisplayControl($Eleanor->avatar);

	$Eleanor->Controls->arrname=array('extra');
	$extra=$Eleanor->Controls->DisplayControls($Eleanor->us,$values);

	$Eleanor->Controls->arrname=array('overload');
	$overload=$Eleanor->Controls->DisplayControls($Eleanor->gp,$values);

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>$id && $id!=$uid ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	$c=Eleanor::$Template->AddEditUser($id,$values,$Eleanor->gp,$overload,$upavatar,$Eleanor->us,$extra,$bypost,$error,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$lang=Eleanor::$Language['users'];
	$C=new Controls;
	$C->throw=false;
	$C->arrname=array('extra');
	try
	{
		$values=$C->SaveControls($Eleanor->us);
	}
	catch(EE$E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}

	$C->arrname=array('overload');
	try
	{
		$overload=$C->SaveControls($Eleanor->gp);
	}
	catch(EE $E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}
	$errors=$C->errors;

	$values+=array(
		'full_name'=>isset($_POST['full_name']) ? (string)Eleanor::$POST['full_name'] : '',
		'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
		'email'=>empty($_POST['email']) ? null : (string)$_POST['email'],
		'groups'=>isset($_POST['groups']) ? (array)$_POST['groups'] : array(),
		'banned_until'=>isset($_POST['banned_until']) ? (string)$_POST['banned_until'] : '',
		'ban_explain'=>$Eleanor->Editor_result->GetHtml('ban_explain'),
		'language'=>isset($_POST['language']) ? (string)$_POST['language'] : '',
		'staticip'=>isset($_POST['staticip']),
		'timezone'=>isset($_POST['timezone']) ? (string)$_POST['timezone'] : '',
	);

	if($values['banned_until'] and false===strtotime($values['banned_until']))
		$errors[]='ERROR_BANDATE';
	if(!$values['banned_until'])
		$values['banned_until']=null;

	$extra=array(
		'_group'=>isset($_POST['_group']) ? (int)$_POST['_group'] : 0,
		'_slnew'=>isset($_POST['_slnew']),
		'_slname'=>isset($_POST['_slname']),
		'_slpass'=>isset($_POST['_slpass']),
		'_cleanfla'=>isset($_POST['_cleanfla']),
		'_overskip'=>isset($_POST['_overskip']) ? (array)$_POST['_overskip'] : array(),
		'_atype'=>isset($_POST['_atype']) ? (string)$_POST['_atype'] : false,
		'pass'=>isset($_POST['pass']) ? (string)$_POST['pass'] : '',
		'pass2'=>isset($_POST['pass2']) ? (string)$_POST['pass2'] : '',
		'avatar'=>isset($_POST['avatar_location']) ? (string)$_POST['avatar_location'] : '',
	);

	if($extra['pass'] and $extra['pass']!=$extra['pass2'])
		$errors[]='PASSWORD_MISMATCH';

	if($k=array_keys($values['groups'],$extra['_group']))
		foreach($k as &$v)
			unset($values['groups'][$v]);
	array_unshift($values['groups'],$extra['_group']);
	if($extra['_cleanfla'])
		$values['failed_logins']='';

	$C->arrname=array('avatar');
	if($id)
	{
		$Eleanor->avatar['id']=$id;
		$R=Eleanor::$Db->Query('SELECT `avatar_location`,`avatar_type` FROM `'.P.'users_extra` WHERE `id`='.$id.' LIMIT 1');
		$oldavatar=$R->fetch_assoc();
	}

	if($extra['_atype']=='upload')
		try
		{
			$avatar=$C->SaveControl($Eleanor->avatar+array('value'=>isset($oldavatar) && $oldavatar['avatar_type']=='upload' && $oldavatar['avatar_location'] ? Eleanor::$uploads.'/avatars/'.$oldavatar['avatar_location'] : ''));
		}
		catch(EE$E)
		{
			return AddEdit($id,array('ERROR'=>$E->getMessage()));
		}
	else
		$avatar=$extra['avatar'];

	if($extra['_atype']=='upload' and $avatar)
		$atype=strpos($avatar,'://')===false ? 'upload' : 'url';
	else
		$atype=$avatar ? 'local' : '';

	if(($atype=='upload' or $atype=='local') and $avatar and !is_file(Eleanor::$root.$avatar))
		$errors[]='AVATAR_NOT_EXISTS';

	if($atype=='local' and $avatar)
		$avatar=preg_replace('#^images/avatars/#','',$avatar);

	foreach($extra['_overskip'] as $k=>&$v)
		if($v=='inherit' and isset($overload[$k]))
			unset($overload[$k],$extra['_overskip'][$k]);

	$values['groups_overload']=$overload ? serialize(array('method'=>$extra['_overskip'],'value'=>$overload)) : '';

	$letterlang=$values['language'] ? $values['language'] : Language::$main;

	if($errors)
		return AddEdit($id,$errors);

	if($id)
	{
		$R=Eleanor::$UsersDb->Query('SELECT `full_name`,`name` FROM `'.USERS_TABLE.'` WHERE `id`='.$id.' LIMIT 1');
		if(!$old=$R->fetch_assoc())
			return GoAway();

		$isf=is_file($f=Eleanor::$root.'addons/admin/letters/users-'.$letterlang.'.php');
		$cansend=$values['email'] && ($extra['_slname'] or $extra['_slpass']) && $isf && ($l=include($f)) && is_array($l);

		if($extra['pass'])
			$values['_password']=$extra['pass'];
		try
		{
			UserManager::Update($values,$id);
			if($cansend and $old['name']!=$values['name'] and $extra['_slname'] and isset($l['name_t'],$l['name']))
			{
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'],
					'newlogin'=>htmlspecialchars($values['name'],ELENT,CHARSET),
					'oldlogin'=>$old['name'],
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				Email::Simple(
					$values['email'],
					Eleanor::ExecBBLogic($l['name_t'],$repl),
					Eleanor::ExecBBLogic($l['name'],$repl)
				);
			}
			if($cansend and $extra['pass'] and $extra['_slpass'] and isset($l['pass_t'],$l['pass']))
			{
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'],
					'login'=>htmlspecialchars($values['name'],ELENT,CHARSET),
					'pass'=>$extra['pass'],
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				Email::Simple(
					$values['email'],
					Eleanor::ExecBBLogic($l['pass_t'],$repl),
					Eleanor::ExecBBLogic($l['pass'],$repl)
				);
			}
		}
		catch(EE$E)
		{
			$mess=$E->getMessage();
			$errors=array();
			switch($mess)
			{
				case'NAME_TOO_LONG':
					$errors['NAME_TOO_LONG']=$lang['NAME_TOO_LONG']($E->extra['max'],$E->extra['you']);
				break;
				case'PASS_TOO_SHORT':
					$errors['PASS_TOO_SHORT']=$lang['PASS_TOO_SHORT']($E->extra['min'],$E->extra['you']);
				break;
				default:
					$errors[]=$mess;
			}
			return AddEdit($id,$errors);
		}

		if($atype=='upload')
			$avatar=basename($avatar);
		if($oldavatar['avatar_location']!=$avatar or $oldavatar['avatar_type']!=$atype)
		{
			if($oldavatar['avatar_type']=='upload' and $oldavatar['avatar_location'] and $oldavatar['avatar_location']!=$avatar)
				Files::Delete(Eleanor::$root.Eleanor::$uploads.'/avatars/'.$oldavatar['avatar_location']);
			UserManager::Update(array('avatar_location'=>$avatar,'avatar_type'=>$atype),$id);
		}
	}
	else
	{
		if($values['full_name']=='')
			$values['full_name']=htmlspecialchars($values['full_name'],ELENT,CHARSET,true);
		if(!$extra['pass'])
		{
			Eleanor::LoadOptions('user-profile',false);
			$extra['pass']=uniqid();
			$extra['pass']=strlen($extra['pass'])>=Eleanor::$vars['min_pass_length'] ? substr($extra['pass'],0,Eleanor::$vars['min_pass_length']>7 ? Eleanor::$vars['min_pass_length'] : 7) : str_pad($extra['pass'],Eleanor::$vars['min_pass_length'],uniqid(),STR_PAD_RIGHT);
		}
		try
		{
			$newid=UserManager::Add($values+array('_password'=>$extra['pass']));
		}
		catch(EE_SQL$E)
		{
			$E->Log();
			return AddEdit($id,array($E->getMessage()));
		}
		catch(EE$E)
		{
			$mess=$E->getMessage();
			$errors=array();
			switch($mess)
			{
				case'NAME_TOO_LONG':
					$errors['NAME_TOO_LONG']=$lang['NAME_TOO_LONG']($E->extra['max'],$E->extra['you']);
				break;
				case'PASS_TOO_SHORT':
					$errors['PASS_TOO_SHORT']=$lang['PASS_TOO_SHORT']($E->extra['min'],$E->extra['you']);
				break;
				default:
					$errors[]=$mess;
			}
			return AddEdit($id,$errors);
		}
		if($avatar)
		{
			if($atype=='upload')
			{
				rename(Eleanor::$root.$avatar,Eleanor::$root.Eleanor::$uploads.'/avatars/'.($newa='av-'.$newid.strrchr($avatar,'.')));
				$avatar=$newa;
			}
			UserManager::Update(array('avatar_location'=>$avatar,'avatar_type'=>$atype),$newid);
		}

		if($values['email'] and $extra['_slnew'])
			do
			{
				if(!is_file($f=Eleanor::$root.'addons/admin/letters/users-'.$letterlang.'.php'))
					break;
				$l=include($f);
				if(!is_array($l) or !isset($l['new_t'],$l['new']))
					break;
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'],
					'login'=>htmlspecialchars($values['name'],ELENT,CHARSET),
					'pass'=>$extra['pass'],
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				try
				{
					Email::Simple(
						$values['email'],
						Eleanor::ExecBBLogic($l['new_t'],$repl),
						Eleanor::ExecBBLogic($l['new'],$repl)
					);
				}
				catch(EE$E){}
			}while(false);
	}
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}