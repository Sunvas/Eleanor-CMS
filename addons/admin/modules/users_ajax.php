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
Eleanor::$Language->Load('addons/admin/langs/users-*.php','users');
$event=isset($_POST['event']) ? (string)$_POST['event'] : '';
switch($event)
{
	case'remove':
		if(isset($_POST['provider'],$_POST['pid']))
		{
			Eleanor::$Db->Delete(P.'users_external_auth','`provider`='.Eleanor::$Db->Escape((string)$_POST['provider']).' AND `provider_uid`='.Eleanor::$Db->Escape((string)$_POST['pid']));
			return Result('');
		}
		Error();
	break;
	case'online':
		$sess=$scnt=$sscnt=array();
		$limit=30;
		$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`user_id`,`s`.`enter`,`s`.`ip_guest`,`s`.`ip_user`,`s`.`service`,`s`.`name` `botname`,`us`.`groups`,`us`.`name` FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id` WHERE `s`.`expire`>\''.date('Y-m-d H:i:s').'\' ORDER BY `s`.`expire` DESC LIMIT '.$limit);
		while($a=$R->fetch_assoc())
		{
			$limit--;
			if($a['type']=='user' and $a['groups'])
			{
				$g=array((int)ltrim($a['groups'],','));
				$a['_gpref']=join(Eleanor::Permissions($g,'html_pref'));
				$a['_gend']=join(Eleanor::Permissions($g,'html_end'));
			}
			else
				$a['_gpref']=$a['_gend']='';

			switch($a['type'])
			{
				case'user':
					if($a['name'])
					{
						$a['_aedit']=$Eleanor->Url->Construct(array('section'=>'management','module'=>'users','id'=>$a['user_id']),false);
						$sess[$a['service']]['users'][]=array_slice($a,1);
						break;
					}
				case'bot':
					if(Eleanor::$vars['bots_enable'] and $a['name'] and !$a['user_id'])
					{
						$sess[$a['service']]['bots'][]=array_slice($a,1);
						break;
					}
				default:
					$sess[$a['service']]['guests'][]=array_slice($a,1);
			}
		}

		if($limit<=0)
		{
			$R=Eleanor::$Db->Query('SELECT `service`, COUNT(`service`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''.date('Y-m-d H:i:s').'\' GROUP BY `service`');
			while($a=$R->fetch_row())
				$scnt[$a[0]]=$a[1];
			$q=array();
			foreach($scnt as $k=>&$v)
				$q[]='(SELECT `type`,`service`, COUNT(`type`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''.date('Y-m-d H:i:s').'\' AND `service`=\''.$k.'\' GROUP BY `type`)';
			if($q)
			{
				$R=Eleanor::$Db->Query(join('UNION ALL',$q));
				while($a=$R->fetch_row())
					$sscnt[$a[1]][$a[0]]=$a[2];
			}
		}

		Eleanor::$Template->queue[]='UsersOnline';
		Result(Eleanor::$Template->BlockOnline($sess,$scnt,$sscnt));
	break;
	case'online_detail':
		$ip=isset($_POST['ip']) ? (string)$_POST['ip'] : '';
		$id=isset($_POST['id']) ? (int)$_POST['id'] : '';
		$service=isset($_POST['service']) ? Eleanor::$Db->Escape((string)$_POST['service'],false) : '';

		$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`enter`,`s`.`ip_guest`,`s`.`ip_user`,`s`.`info`,`s`.`service`,`s`.`browser`,`s`.`location`,`s`.`name` `botname`,`us`.`groups`,`us`.`name` FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id` WHERE `s`.`ip_guest`='.Eleanor::$Db->Escape($ip).' AND `s`.`user_id`='.$id.' AND `s`.`service`=\''.$service.'\' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			if($a['type']=='user' and $a['groups'])
			{
				$g=array((int)ltrim($a['groups'],','));
				$a['_gpref']=join(Eleanor::Permissions($g,'html_pref'));
				$a['_gend']=join(Eleanor::Permissions($g,'html_end'));
			}
			else
				$a['_gend']=$a['_gpref']='';
			$a['info']=$a['info'] ? (array)unserialize($a['info']) : array();
		}

		Eleanor::$Template->queue[]='UsersOnline';
		Result(Eleanor::$Template->SessionDetail($a));
	break;
	case'galleries':
		$galleries=array();
		$gals=glob(Eleanor::$root.'images/avatars/*',GLOB_MARK | GLOB_ONLYDIR);
		foreach($gals as &$v)
		{
			$descr=$name=basename($v);
			$image=false;
			if(is_file($v.'config.ini'))
			{
				$a=parse_ini_file($v.'config.ini',true);
				if(isset($a['title']))
					$descr=Eleanor::FilterLangValues($a['title'],'',$name);
				if(isset($a['options']['cover']) and is_file($v.$a['options']['cover']))
					$image='images/avatars/'.$name.'/'.$a['options']['cover'];
			}
			if(!$image and $temp=glob($v.'*.{jpg,png,jpeg,bmp,gif}',GLOB_BRACE))
				$image='images/avatars/'.$name.'/'.basename($temp[0]);
			if($image)
				$galleries[]=array('n'=>$name,'i'=>$image,'d'=>$descr);
		}
		Eleanor::$Template->queue[]='Users';
		Result(Eleanor::$Template->Galleries($galleries));
	break;
	case'avatars':
		$gallery=isset($_POST['gallery']) ? (string)$_POST['gallery'] : false;
		$files=$gallery ? glob(Eleanor::$root.'images/avatars/'.$gallery.'/*.{jpg,png,jpeg,bmp,gif}',GLOB_BRACE) : false;
		if(!$files)
			return Error();

		foreach($files as &$v)
			$v=array('p'=>'images/avatars/'.$gallery.'/','f'=>basename($v));

		Eleanor::$Template->queue[]='Users';
		Result(Eleanor::$Template->Avatars($files));
	break;
	case'killsession':
		$key=isset($_POST['key']) ? (string)$_POST['key'] : '';
		$uid=isset($_POST['uid']) ? (string)$_POST['uid'] : '';
		$cl=isset($_POST['cl']) ? (string)$_POST['cl'] : '';
		$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$uid.' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			$lks=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
			unset($lks[$cl][$key]);
			if(empty($lks[$cl]))
				unset($lks[$cl]);
			Eleanor::$Db->Update(P.'users_site',array('login_keys'=>$lks ? serialize($lks) : ''),'`id`='.$uid.' LIMIT 1');
		}
		Result(true);
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}