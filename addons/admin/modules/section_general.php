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
$lang=Eleanor::$Language->Load('addons/admin/langs/general-*.php','general');
Eleanor::$Template->queue[]='General';

$Eleanor->module['links']=array(
	'main'=>$Eleanor->Url->Prefix(),
	'server'=>$Eleanor->Url->Construct(array('do'=>'server')),
	'logs'=>$Eleanor->Url->Construct(array('do'=>'logs')),
	'license'=>$Eleanor->Url->Construct(array('do'=>'license')),
);
switch(isset($_GET['do']) ? (string)$_GET['do'] : '')
{
	case'server':
		$title[]=$lang['server_info'];
		$c=Eleanor::$Template->Server(array(
			'gd_info'=>function_exists('gd_info') ? gd_info() : false,
			'ini_get_v'=>empty($_POST['ini_get']) ? false : ini_get((string)$_POST['ini_get']),
			'ini_get'=>isset($_POST['ini_get']) ? (string)$_POST['ini_get'] : '',
			'os'=>php_uname('s'),
			'pms'=>ini_get('post_max_size'),
			'ums'=>ini_get('upload_max_filesize'),
			'ml'=>ini_get('memory_limit'),
			'met'=>ini_get('max_execution_time'),
			'db'=>str_replace('-nt-max','',Eleanor::$Db->Driver->server_info),
		));
		Start();
		echo$c;
	break;
	case'logs':
		$Eleanor->Url->SetPrefix(array('do'=>'logs'),true);
		if(isset($_GET['view']))
		{
			$f=str_replace(array('..','/','\\'),'',(string)$_GET['view']);
			$f=Eleanor::FormatPath('addons/logs/'.$f);
			$help=$f.'.inc';

			if(is_file($f))
			{
				$bn=basename($f);
				$title[]=isset($lang[$bn]) ? $lang[$bn] : $bn;
				if(in_array($bn,array('errors.log','db_errors.log','request_errors.log')) and is_file($help))
					$help=unserialize(file_get_contents($help));
				$c=Eleanor::$Template->ShowLog(
					is_array($help) ? $help : file_get_contents($f),
					substr($bn,0,-4),
					array(
						'adown'=>$Eleanor->Url->Construct(array('download'=>$bn)),
						'adel'=>$Eleanor->Url->Construct(array('delete'=>$bn)),
					)
				);
				Start();
				echo$c;
				break;
			}
		}
		elseif(isset($_GET['download']))
		{
			$f=str_replace(array('..','/','\\'),'',$_GET['download']);
			if(is_file($f=Eleanor::FormatPath('addons/logs/'.$f)))
			{
				Files::OutputStream(array('file'=>$f));
				break;
			}
		}
		elseif(isset($_GET['delete']))
		{
			$f=str_replace(array('..','/','\\'),'',$_GET['delete']);
			if(is_file($f=Eleanor::FormatPath('addons/logs/'.$f)))
			{
				Files::Delete($f);
				Files::Delete($f.'.inc');
				GoAway(true);
				break;
			}
		}
		$logs=glob(Eleanor::FormatPath('addons/logs/*.log'));
		$title[]=$lang['logs'];
		if($logs)
		{
			Eleanor::LoadOptions('errors');
			$len=strlen(Eleanor::$root);
			foreach($logs as &$v)
			{
				$t=basename($v);
				$v=array(
					'path'=>str_replace('\\','/',substr($v,$len)),
					'size'=>filesize($v),
					'aview'=>$Eleanor->Url->Construct(array('view'=>$t)),
					'adown'=>$Eleanor->Url->Construct(array('download'=>$t)),
					'adel'=>$Eleanor->Url->Construct(array('delete'=>$t)),
					'descr'=>isset($lang[$t]) ? $lang[$t] : $t,
				);
			}
		}
		$size=Files::BytesToSize(Files::GetSize(
			Eleanor::$root.'addons/logs',
			function($f){
				return basename($f)!='.htaccess';
			}
		));
		$c=Eleanor::$Template->Logs($logs,$size);
		Start();
		echo$c;
	break;
	case'license':
		$title[]=$lang['license_'];
		$license=is_file($f=Eleanor::$root.'addons/license/license-'.Language::$main.'.html') ? file_get_contents($f) : file_get_contents(Eleanor::$root.'addons/license/license-'.LANGUAGE.'.html');
		$license=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$license);
		$sanctions=is_file($f=Eleanor::$root.'addons/license/sanctions-'.Language::$main.'.html') ? file_get_contents($f) : file_get_contents(Eleanor::$root.'addons/license/sanctions-'.LANGUAGE.'.html');
		$sanctions=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$sanctions);
		$c=Eleanor::$Template->License($license,$sanctions);
		Start();
		echo$c;
	break;
	default:
		$wd=date('w');
		if($wd==0)
			$wd=7;
		--$wd;

		$nums=$groups=$users=$grs=array();
		$R=Eleanor::$Db->Query('(SELECT COUNT(`id`) FROM `'.P.'comments`) UNION ALL (SELECT COUNT(`id`) FROM `'.P.'comments` WHERE `date`>DATE_SUB(CURDATE(), INTERVAL '.$wd.' DAY))');
		list($nums['c'])=$R->fetch_row();
		list($nums['cw'])=$R->fetch_row();

		$R=Eleanor::$UsersDb->Query('(SELECT COUNT(`id`) FROM `'.USERS_TABLE.'` WHERE `id`>0) UNION ALL (SELECT COUNT(`id`) FROM `'.USERS_TABLE.'` WHERE `register`>DATE_SUB(CURDATE(), INTERVAL '.$wd.' DAY))');
		list($nums['u'])=$R->fetch_row();
		list($nums['uw'])=$R->fetch_row();

		$R=Eleanor::$Db->Query('SELECT TO_DAYS(NOW())-TO_DAYS(`date`) FROM `'.P.'upgrade_hist` ORDER BY `id` ASC LIMIT 1');
		list($nums['sl'])=$R->fetch_row();

		$op=$Eleanor->Url->Prefix();
		$Eleanor->Url->SetPrefix('section=management&amp;module=comments&amp;');
		$ong=true;
		$comments=require Eleanor::$root.'addons/admin/modules/comments.php';
		$Eleanor->Url->SetPrefix($op);

		$pref=$Eleanor->Url->file.'?section=management&amp;module=users&amp;';
		$R=Eleanor::$Db->Query('SELECT `id`,`full_name`,`name`,`email`,`groups`,`ip`,`register`,`last_visit` FROM `'.P.'users_site` WHERE `id`>0 ORDER BY `id` DESC LIMIT 5');
		while($a=$R->fetch_assoc())
		{
			$a['groups']=$a['groups'] ? explode(',,',trim($a['groups'],',')) : array();
			$grs=array_merge($grs,$a['groups']);
			$a['_adel']=$pref.$Eleanor->Url->Construct(array('delete'=>$a['id']),false);
			$a['_aedit']=$pref.$Eleanor->Url->Construct(array('edit'=>$a['id']),false);
			$users[$a['id']]=array_slice($a,1);
		}

		if($grs)
		{
			$pref=$Eleanor->Url->file.'?section=management&amp;module=groups&amp;';
			$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($grs));
			$grs=array();
			while($a=$R->fetch_assoc())
			{
				$groups[$a['id']]=$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
				$a['_aedit']=$pref.$Eleanor->Url->Construct(array('edit'=>$a['id']),false);
				$grs[$a['id']]=array_slice($a,1);
			}
			asort($groups,SORT_STRING);
			foreach($groups as $k=>&$v)
				$v=$grs[$k];
		}

		$mynotes=Eleanor::$Cache->Get('notes_'.Eleanor::$Login->GetUserValue('id'),true);
		$conotes=Eleanor::$Cache->Get('notes',true);

		$ck=false;
		if(isset($_POST['kill_cache']))
		{
			Eleanor::$Cache->Lib->DeleteByTag('');
			$ck=true;
		}
		$c=Eleanor::$Template->General($nums,$comments,$users,$groups,$mynotes ? OwnBB::Parse($mynotes) : '',$conotes ? OwnBB::Parse($conotes) : '',$ck);
		Start();
		echo$c;
}