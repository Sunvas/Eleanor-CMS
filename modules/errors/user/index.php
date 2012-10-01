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
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_user-*.php','merror');
Eleanor::$Template->queue[]=$Eleanor->module['config']['usertpl'];

$id=false;
$canlog=isset($Eleanor->module['code']);
$uri=$canlog ? $Eleanor->module['code'] : false;
if(!$uri)
{
	if($Eleanor->Url->is_static)
	{		$Eleanor->Url->GetEnding(array($Eleanor->Url->ending,$Eleanor->Url->delimiter),true);
		$_GET+=$Eleanor->Url->Parse(array('code'));
	}

	$uri=isset($_GET['code']) ? $_GET['code'] : '';
	if(isset($_GET['id']))
		$id=(int)$_GET['id'];
}
if(!$uri and !$id)
	return ErrorExitPage();

$R=Eleanor::$Db->Query('SELECT `id`,`http_code`,`image`,`mail`,`log`,`title`,`text`,`meta_title`,`meta_descr` FROM `'.P.'errors` INNER JOIN `'.P.'errors_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND '.($id ? '`id`='.$id : '`uri`='.Eleanor::$Db->Escape($uri)).' LIMIT 1');
if(!$a=$R->fetch_assoc())
	return GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path);

if($a['meta_title'])
	$title=$a['meta_title'];
else
	$title[]=$a['title'];
$Eleanor->module['description']=$a['meta_descr'];

$info=array(
	'sent'=>false,
	'error'=>'',
	'text'=>'',
	'back'=>getenv('HTTP_REFERER'),
	'name'=>'',
);

if($a['mail'] and $_SERVER['REQUEST_METHOD']=='POST')
	do
	{		$info['back']=isset($_POST['back']) ? $_POST['back'] : '';		$info['text']=$Eleanor->Editor_result->GetHtml('text');
		if($user=Eleanor::$Login->GetUserValue(array('id','full_name','name'),false))
			$info['name']=htmlspecialchars($user['name'],ELENT,CHARSET);
		else
			$info['name']=isset($_POST['name']) ? (string)Eleanor::$POST['name'] : $lang['guest'];
		$cach=$Eleanor->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
		$Eleanor->Captcha->Destroy();
		if(!$cach)
		{			$info['error']=$lang['error_captcha'];
			break;		}
		if(!$info['text'])
		{
			$info['error']=$lang['empty_text'];
			break;
		}

		$l=include $Eleanor->module['path'].'letters-'.LANGUAGE.'.php';
		$repl=array(
			'site'=>Eleanor::$vars['site_name'],
			'name'=>$info['name'],
			'fullname'=>$user ? $user['full_name'] : '',
			'userlink'=>$user ? PROTOCOL.Eleanor::$domain.Eleanor::$site_path.Eleanor::$Login->UserLink($user['name'],$user['id']) : false,
			'text'=>$info['text'],
			'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
			'linkerror'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.($Eleanor->Url->is_static ? $_SERVER['QUERY_STRING'] : ltrim($_SERVER['REQUEST_URI'],'/')),
			'from'=>$info['back'],
		);
		Eleanor::Mail(
			$a['mail'],
			Eleanor::ExecBBLogic($l['error_t'],$repl),
			Eleanor::ExecBBLogic($l['error'],$repl)
		);
		unset($info['text']);
		$info['sent']=true;
	}while(false);
if(!$info['error'] and !$info['sent'])
{
	$R=Eleanor::$Db->Query('SELECT `title` FROM `'.P.'errors` INNER JOIN `'.P.'errors_l` USING(`id`) WHERE `language` IN (\'\',\''.LANGUAGE.'\') AND `id`='.$a['id'].' LIMIT 1');
	if(!$my=$R->fetch_assoc())
		return GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path);

	if($canlog and $a['log'] and $info['back'])
	{
		$E=new EE('',EE::INFO);
		$E->LogIt(EE::$vars['log_site_errors'],$my['title']);
	}
}

$s=Eleanor::$Template->ShowError($a,$info,$Eleanor->Captcha->disabled ? $Eleanor->Captcha->GetCode() : false);
$a['text']=OwnBB::Parse($a['text']);
if($a['http_code'])
{
	Start('index',$a['http_code']);
	header('Retry-After: 0');
}
else
	Start();

echo$s;

function ErrorExitPage()
{global$Eleanor;	#Тут возможно нужно сделать содержание :)
	if(!empty($Eleanor->module['general']))
		return;
	return GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path);
}