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
if(!defined('CMS') or !function_exists('BeAs'))die;

$type=isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$service=isset($_REQUEST['service']) ? (string)$_REQUEST['service'] : '';
if($service)
	BeAs($service);
switch($type)
{
	case'check':#Проверка стороннего сайта на логин в текущем сайте
		Eleanor::$maxage=1000;
		Eleanor::$last_mod=time();
		$r=Eleanor::$Login->IsUser() ? Eleanor::JsVars(array('title'=>Eleanor::$vars['site_name'],)+Eleanor::$Login->GetUserValue(array('id','name'),false),false,true) : 'false';
		$c=isset($_GET['c']) ? (string)$_GET['c'] : '';
		Start();
		header('Access-Control-Allow-Origin: *');
		echo'try{'.$c.'('.$r.')}catch(e){}';
	break;
	case'getlogin':#Получение логина с текущего сайта для возможности логина на стороннем
		if(Eleanor::$Login->IsUser())
		{
			Eleanor::LoadOptions('multisite');
			$r=Eleanor::$Login->GetUserValue(array('id','name'),false);
			$r=array(
				'uid'=>$r['id'],
				'name'=>$r['name'],
			);
			if(isset($_REQUEST['secret']))
			{
				$t=time()+Eleanor::$vars['multisite_ttl'];
				$r['signature']=$t.'-'.md5($t.'-'.$r['uid'].$service.Eleanor::$ip.$r['name'].getenv('HTTP_USER_AGENT').Eleanor::$vars['multisite_secret']);
			}
			else
			{
				$r['signature']=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT'));
				$r['id']=Eleanor::$Db->Insert(P.'multisite_jump',array('type'=>'out','!expire'=>'NOW() + INTERVAL 2 MINUTE')+$r);
			}

			$r=Eleanor::JsVars($r,false,true);
		}
		else
			$r='false';
		$c=isset($_GET['c']) ? (string)$_GET['c'] : '';
		Start();
		header('Access-Control-Allow-Origin: *');
		echo'try{'.$c.'('.$r.')}catch(e){}';
	break;
	case'login':#Логин со стороннего сайта на текущий
		if(Eleanor::$Login->IsUser())
			return Result(true);
		$sn=isset($_REQUEST['sn']) ? (string)$_REQUEST['sn'] : '';
		$sign=isset($_REQUEST['signature']) ? (string)$_REQUEST['signature'] : '';
		$ms=include Eleanor::$root.'addons/config_multisite.php';
		if(!isset($ms[$sn]))
			return Error();
		$d=$ms[$sn];

		if($d['secret'])
		{
			$a=array(
				'uid'=>isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0,
				'name'=>isset($_REQUEST['name']) ? (string)$_REQUEST['name'] : '',
			);
			list($t,$sign)=explode('-',$sign,2);
			if($t<time() or $sign!=md5($t.'-'.$a['uid'].$service.Eleanor::$ip.$a['name'].getenv('HTTP_USER_AGENT').$d['secret']))
				return Error();
		}
		else
		{
			if($sign!=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT')))
				return Error();
			if(isset($d['db']))
				try
				{
					$Db=new Db($d);
					$Db->SyncTimeZone();
				}
				catch(EE$E)
				{
					return Error($E->getMessage());
				}
			else
				$Db=Eleanor::$Db;
			$id=isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
			$Db->Query('SELECT `uid`,`name` FROM `'.$d['prefix'].'multisite_jump` WHERE `id`='.$id.' AND `type`=\'out\' AND `expire`>NOW() AND `signature`='.Eleanor::$Db->Escape($sign).' LIMIT 1');
			if(!$a=$Db->fetch_assoc())
				return Error();
			$Db->Delete($d['prefix'].'multisite_jump','`id`='.$id.' LIMIT 1');
		}
		if(!$d['sync'])
		{
			Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($a['name']).' LIMIT 1');
			if(!list($a['uid'])=Eleanor::$UsersDb->fetch_row())
				return Error();
		}
		Eleanor::$Login->Auth($a['uid']);
		Result(true);
	break;
	case'prejump':#Подготовка к прыжку с текущего на сторонний
		if(Eleanor::$Login->IsUser())
		{
			Eleanor::LoadOptions('multisite');
			$sn=isset($_REQUEST['sn']) ? (string)$_REQUEST['sn'] : '';
			$ms=include Eleanor::$root.'addons/config_multisite.php';
			if(!isset($ms[$sn]))
				return Error();
			$d=$ms[$sn];
			$data=Eleanor::$Login->GetUserValue(array('id','name'),false);
			$data=array(
				'uid'=>$d['sync'] ? $data['id'] : 0,
				'name'=>$data['name'],
				'address'=>$d['address'],
			);

			if($d['secret'])
			{
				$t=time()+Eleanor::$vars['multisite_ttl'];
				$data['signature']=$t.'-'.md5($t.'-'.$data['uid'].$service.Eleanor::$ip.$data['name'].getenv('HTTP_USER_AGENT').$d['secret']);
				$data['secret']=true;
			}
			else
			{
				if(isset($d['db']))
					try
					{
						$Db=new Db($d);
						$Db->SyncTimeZone();
					}
					catch(EE$E)
					{
						return Error($E->getMessage());
					}
				else
					$Db=Eleanor::$Db;

				$data['signature']=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT'));
				$data['id']=$Db->Insert($d['prefix'].'multisite_jump',array('type'=>'in','!expire'=>'NOW() + INTERVAL 2 MINUTE')+$data);
			}
			Result($data);
		}
		else
			Error();
	break;
	case'jump':#Прыжок со стороннего сайта на текущий
		if(Eleanor::$Login->IsUser())
			return GoAway(true);
		$sp=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
		$sign=isset($_REQUEST['signature']) ? (string)$_REQUEST['signature'] : '';
		if(isset($_REQUEST['secret']))
		{
			Eleanor::LoadOptions('multisite');
			$a=array(
				'uid'=>isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0,
				'name'=>isset($_REQUEST['name']) ? (string)$_REQUEST['name'] : '',
			);
			list($t,$sign)=explode('-',$sign,2);
			if($t<time() or $sign!=md5($t.'-'.$a['uid'].$service.Eleanor::$ip.$a['name'].getenv('HTTP_USER_AGENT').Eleanor::$vars['multisite_secret']))
				return GoAway($sp);
		}
		else
		{
			if($sign!=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT')))
				return GoAway($sp);
			$id=isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
			$R=Eleanor::$Db->Query('SELECT `uid`,`name` FROM `'.P.'multisite_jump` WHERE `id`='.$id.' AND `type`=\'in\' AND `expire`>NOW() AND `signature`='.Eleanor::$Db->Escape($sign).' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway($sp);
			Eleanor::$Db->Delete(P.'multisite_jump','`id`='.$id.' LIMIT 1');
		}
		if($a['uid']==0)
		{
			$R2=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($a['name']).' LIMIT 1');
			if(!list($a['uid'])=$R2->fetch_row())
				return GoAway($sp);
		}
		Eleanor::$Login->Auth($a['uid']);
		GoAway(true);
}