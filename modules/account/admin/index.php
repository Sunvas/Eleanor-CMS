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
$Eleanor->module['config']=$mc=include$Eleanor->module['path'].'config.php';
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php',$mc['n']);
Eleanor::$Template->queue[]=$mc['admintpl'];

$Eleanor->module['links']=array(
	'inactives'=>$Eleanor->Url->Prefix(),
	'letters'=>$Eleanor->Url->Construct(array('do'=>'letters')),
	'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'letters':
			$post=false;
			$controls=array(
				$lang['letter_reg'],
				'reg_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['letter_reg_'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'reg_fin'=>array(
					'title'=>$lang['letter_reg_fin'],
					'descr'=>$lang['letter_reg_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				'reg_act'=>array(
					'title'=>$lang['letter_reg_act'],
					'descr'=>$lang['letter_reg_act_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				'reg_act_admin'=>array(
					'title'=>$lang['letter_reg_act_admin'],
					'descr'=>$lang['letter_reg_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				$lang['letter_act'],
				'act_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['letter_act_success_'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'act_success'=>array(
					'title'=>$lang['letter_act_success'],
					'descr'=>$lang['letter_act_success_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				'act_refused'=>array(
					'title'=>$lang['letter_act_refused'],
					'descr'=>$lang['letter_act_refused_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				$lang['letter_passrem'],
				'passrem_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['letter_passrem_'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'passrem'=>array(
					'title'=>$lang['letterdescr'],
					'descr'=>$lang['letter_passrem_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				$lang['letter_passremfin'],
				'passremfin_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['letter_passremfin_'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'passremfin'=>array(
					'title'=>$lang['letterdescr'],
					'descr'=>$lang['letter_passremfin_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				$lang['letter_newemail'],
				'newemail_t'=>array(
					'title'=>$lang['lettertitle'],
					'descr'=>$lang['letter_newemail_'],
					'type'=>'input',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'htmlsafe'=>true,
					),
				),
				'newemail_old'=>array(
					'title'=>$lang['letter_newemail_old'],
					'descr'=>$lang['letter_newemail_'],
					'type'=>'editor',
					'multilang'=>Eleanor::$vars['multilang'],
					'bypost'=>&$post,
					'options'=>array(
						'checkout'=>false,
						'ownbb'=>false,
						'smiles'=>false,
					),
				),
				'newemail_new'=>array(
					'title'=>$lang['letter_newemail_new'],
					'descr'=>$lang['letter_newemail_'],
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
						$file=$Eleanor->module['path'].'letters-'.$lng.'.php';
						file_put_contents($file,'<?php return '.var_export($tosave,true).';');
					}
				else
				{
					$file=$Eleanor->module['path'].'letters-'.LANGUAGE.'.php';
					file_put_contents($file,'<?php return '.var_export($letter,true).';');
				}
			}
			else
				foreach($multilang as &$lng)
				{
					$letters=array();
					$file=$Eleanor->module['path'].'letters-'.$lng.'.php';
					$letter=file_exists($file) ? (array)include$file : array();
					$letter+=array(
						'reg_t'=>'',
						'reg_fin'=>'',
						'reg_act'=>'',
						'reg_act_admin'=>'',
						'passrem_t'=>'',
						'passrem'=>'',
						'passremfin_t'=>'',
						'passremfin'=>'',
						'newemail_t'=>'',
						'newemail_old'=>'',
						'newemail_new'=>'',
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
		case'options':
			$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
			$c=$Eleanor->Settings->GetInterface('group','user-profile');
			if($c)
			{
				$c=Eleanor::$Template->Options($c);
				Start();
				echo$c;
			}
		break;
		default:
			InactiveUsers();
	}
elseif(isset($_GET['delete']))
{
	$ids=$_GET['delete'];
	$Eleanor->Editor->smiles=$Eleanor->Editor->ownbb=false;
	if(isset($_POST['ids']) and is_array($_POST['ids']) and Eleanor::$our_query)
	{
		$myid=Eleanor::$Login->GetUserValue('id');
		if(false!==$p=array_search($myid,$_POST['ids']))
			unset($_POST['ids'][$p]);
		$l[Language::$main]=include $Eleanor->module['path'].'letters-'.Language::$main.'.php';
		$R=Eleanor::$Db->Query('SELECT `id`,`full_name`,`name`,`email`,`language` FROM `'.P.'users_site` WHERE `id`'.Eleanor::$Db->In($_POST['ids']));
		while($a=$R->fetch_assoc())
		{
			if($a['language'] and !isset($l[$a['language']]) and isset(Eleanor::$langs[$a['language']]))
				$l[$a['language']]=include $Eleanor->module['path'].'letters-'.$a['language'].'.php';
			else
				$a['language']=Language::$main;

			$repl=array(
				'site'=>Eleanor::$vars['site_name'],
				'name'=>$a['full_name'],
				'login'=>htmlspecialchars($a['name'],ELENT,CHARSET),
				'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				'reason'=>$Eleanor->Editor_result->GetHtml('reason'),
			);
			Email::Simple(
				$a['email'],
				Eleanor::ExecBBLogic($l[$a['language']]['act_t'],$repl),
				Eleanor::ExecBBLogic($l[$a['language']]['act_refused'],$repl)
			);
		}
		UserManager::Delete($_POST['ids']);
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	if(!is_array($ids) and strpos($ids,',')!==false)
		$ids=explode(',',$ids);
	$users=array();
	$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`language` FROM `'.USERS_TABLE.'` WHERE `id`'.Eleanor::$UsersDb->In($ids).' ORDER BY `name` ASC');
	while($a=$R->fetch_assoc())
		$users[$a['id']]=array_slice($a,1);
	if(!$users)
		return GoAway();
	$title[]=$lang['delc'];

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$c=Eleanor::$Template->ToDelete($users,$back);
	Start();
	echo$c;
}
else
	InactiveUsers();

function InactiveUsers()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language[ $Eleanor->module['config']['n'] ]['inactives'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$items=$sletters=$where=$qs=array();
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
			$where[]='`u`.`name`'.$name;
		}
		if(!empty($_REQUEST['fi']['id']))
		{
			$ints=explode(',',Tasks::FillInt($_REQUEST['fi']['id']));
			$qs['']['fi']['id']=(int)$_REQUEST['fi']['id'];
			$where['id']='`id`'.Eleanor::$Db->In($ints);
		}
		if(!empty($_REQUEST['fi']['regto']) and 0<$t=strtotime($_REQUEST['fi']['regto']))
		{
			$qs['']['fi']['regto']=$_REQUEST['fi']['regto'];
			$where[]='`u`.`register`<=\''.date('Y-m-d H:i:s',$t).'\'';
		}
		if(!empty($_REQUEST['fi']['regfrom']) and 0<$t=strtotime($_REQUEST['fi']['regfrom']))
		{
			$qs['']['fi']['regfrom']=$_REQUEST['fi']['regfrom'];
			$where[]='`u`.`register`>=\''.date('Y-m-d H:i:s',$t).'\'';
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

	if(!isset($where['id']))
		$where['id']='`id`>0';
	$where[]='`groups` LIKE \'%,'.GROUP_WAIT.',%\'';
	$where='WHERE '.join(' AND ',$where);
	$act=isset($_GET['activate']) ? array($_GET['activate']) : array();
	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']) and is_array($_POST['mass']))
		switch($_POST['op'])
		{
			case's':
				$sletters=array_merge($sletters,$_POST['mass']);
			break;
			case'd':
				return UserManager::Delete($_POST['mass']);
			case'dr':
				return GoAway(array('delete'=>join(',',$_POST['mass'])));
			case'a':
 				$act=array_merge($act,$_POST['mass']);
		}
	if(Eleanor::$Db===Eleanor::$UsersDb)
	{
		$table=USERS_TABLE;
		$where=' INNER JOIN `'.P.'users_site` USING(`id`)'.$where;
	}
	else
		$table=P.'users_site';
	if($sletters)
	{
		$l[Language::$main]=include $Eleanor->module['path'].'letters-'.Language::$main.'.php';
		Eleanor::LoadOptions('user-profile');
		$Eleanor->Url->file=Eleanor::$services['user']['file'];
		$ma[Language::$main]=Modules::GetCache('user',Language::$main);
		$ma[Language::$main]=array_keys($ma[Language::$main],$Eleanor->module['id']);
		$ma[Language::$main]=reset($ma[Language::$main]);
		Eleanor::$Db->Delete(P.'confirmation','`op`=\'regact\' AND `user`'.Eleanor::$Db->In($sletters));
		if(Eleanor::$Db!==Eleanor::$UsersDb)
			UserManager::Sync($sletters);
		$R=Eleanor::$Db->Query('SELECT `id`,`full_name`,`name`,`email`,`language` FROM `'.P.'users_site` WHERE `id`'.Eleanor::$Db->In($sletters));
		$sletters=array();
		while($a=$R->fetch_assoc())
		{
			$sletters[]=$a['id'];
			$actid=Eleanor::$Db->Insert(P.
				'confirmation',
				array(
					'hash'=>$hash=md5(uniqid(microtime())),
					'!date'=>'NOW()',
					'user'=>$a['id'],
					'op'=>'regact',
					'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
					'data'=>serialize(array('newgr'=>array(GROUP_USER))),
				)
			);
			if($a['language'] and !isset($ma[$a['language']]) and isset(Eleanor::$langs[$a['language']]))
			{
				$l[$a['language']]=include $Eleanor->module['path'].'letters-'.$a['language'].'.php';
				$ma[$a['language']]=Modules::GetCache('user',$a['language']);
				$ma[$a['language']]=array_keys($ma[$a['language']],$Eleanor->module['id']);
				$ma[$a['language']]=reset($ma[$a['language']]);
			}
			else
				$a['language']=Language::$main;
			$repl=array(
				'site'=>Eleanor::$vars['site_name'],
				'name'=>$a['full_name'],
				'login'=>htmlspecialchars($a['name'],ELENT,CHARSET),
				'pass'=>false,
				'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
				'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				'confirm'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$Eleanor->Url->Construct(array('module'=>$ma[$a['language']],'do'=>'activate','id'=>$actid,'md'=>$hash),Eleanor::$services['user']['file'].'?',false,false),
			);
			Email::Simple(
				$a['email'],
				Eleanor::ExecBBLogic($l[$a['language']]['reg_t'],$repl),
				Eleanor::ExecBBLogic($l[$a['language']]['reg_act'],$repl)
			);
		}
		$Eleanor->Url->file=Eleanor::$filename;
	}
	elseif($act)
	{
		if(Eleanor::$Db!==Eleanor::$UsersDb)
			UserManager::Sync($act);
		$l[Language::$main]=include $Eleanor->module['path'].'letters-'.Language::$main.'.php';
		$R=Eleanor::$Db->Query('SELECT `id`,`full_name`,`name`,`email`,`language` FROM `'.P.'users_site` WHERE `id`'.Eleanor::$Db->In($act));
		while($a=$R->fetch_assoc())
		{
			UserManager::Update(array('groups'=>GROUP_USER),$a['id']);
			if($a['language'] and !isset($l[$a['language']]) and isset(Eleanor::$langs[$a['language']]))
				$l[$a['language']]=include $Eleanor->module['path'].'letters-'.$a['language'].'.php';
			else
				$a['language']=Language::$main;

			$repl=array(
				'site'=>Eleanor::$vars['site_name'],
				'name'=>$a['full_name'],
				'login'=>htmlspecialchars($a['name'],ELENT,CHARSET),
				'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
			);
			Email::Simple(
				$a['email'],
				Eleanor::ExecBBLogic($l[$a['language']]['act_t'],$repl),
				Eleanor::ExecBBLogic($l[$a['language']]['act_success'],$repl)
			);
		}
	}
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
	if(!in_array($sort,array('id','ip','name','email','group','full_name','last_visit')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'desc';
	if($so!='asc')
		$so='desc';
	if($sort)
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	if($cnt)
	{
		$upref=$Eleanor->Url->file.'?section=management&amp;module=users&amp;';
		$myuid=Eleanor::$Login->GetUserValue('id');
		$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`email`,`ip` FROM `'.$table.'` `u` INNER JOIN `'.P.'users_extra` USING(`id`)'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{
			$a['_aact']=$Eleanor->Url->Construct(array('activate'=>$a['id']));
			$a['_aedit']=$upref.'edit='.$a['id'];
			if($myuid==$a['id'])
				$a['_adel']=$a['_adelr']=false;
			else
			{
				$a['_adel']=$upref.'delete='.$a['id'];
				$a['_adelr']=$Eleanor->Url->Construct(array('delete'=>$a['id']));
			}

			$items[$a['id']]=array_slice($a,1);
		}
	}

	$links=array(
		'sort_name'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'name','so'=>$qs['sort']=='name' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_email'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'email','so'=>$qs['sort']=='email' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_ip'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'ip','so'=>$qs['sort']=='ip' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->InactiveUsers($items,$sletters,$cnt,$pp,$page,$qs,$links);
	Start();
	echo$c;
}