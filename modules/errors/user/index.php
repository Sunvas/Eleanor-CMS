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
Eleanor::$Template->queue[]=$mc['usertpl'];

$id=0;
if(isset($Eleanor->module['code']))
	$uri=$Eleanor->module['code'];
else
{
	if($Eleanor->Url->is_static)
		$_GET+=$Eleanor->Url->Parse(array('code'));

	$uri=isset($_GET['code']) ? $_GET['code'] : false;
	if(isset($_GET['id']))
		$id=(int)$_GET['id'];
}

$R=Eleanor::$Db->Query('SELECT `id`,`http_code`,`image`,`mail`,`log`,`title`,`text`,`meta_title`,`meta_descr` FROM `'.P.'errors` INNER JOIN `'.P.'errors_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND '.($id ? '`id`='.$id : '`uri`='.Eleanor::$Db->Escape($uri)).' LIMIT 1');
if(!$a=$R->fetch_assoc())
	return GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path);

if($a['meta_title'])
	$title=$a['meta_title'];
else
	$title[]=$a['title'];
$Eleanor->module['description']=$a['meta_descr'];

$isu=Eleanor::$Login->IsUser();
$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
$errors=array();
$sent=false;
$values=array('text'=>'');
if($isu)
	$values['name']='';

if($a['mail'] and $_SERVER['REQUEST_METHOD']=='POST')
{	$Eleanor->Editor_result->ownbb=false;	$values['text']=$Eleanor->Editor_result->GetHtml('text',false,false);
	if($values['text']=='')
		$errors[]='EMPTY_TEXT';
	if($isu)
	{		$user=Eleanor::$Login->GetUserValue(array('id','full_name','name'),false);
		$name=$user['name'];	}
	else
	{		$values['name']=isset($_POST['name']) ? (string)$_POST['name'] : '';
		if($values['name']!=='')
			$name=$values['name'];
		else
			$errors[]='EMPTY_NAME';
	}

	$cach=$Eleanor->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
	$Eleanor->Captcha->Destroy();
	if(!$cach)
		$errors[]='WRONT_CAPTCHA';
	if(!$errors)
	{		$l=include$Eleanor->module['path'].'letters-'.LANGUAGE.'.php';
		$repl=array(
			'site'=>Eleanor::$vars['site_name'],
			'name'=>GlobalsWrapper::Filter($name),
			'fullname'=>$isu ? $user['full_name'] : '',
			'userlink'=>$isu ? PROTOCOL.Eleanor::$domain.Eleanor::$site_path.Eleanor::$Login->UserLink($user['name'],$user['id']) : ээ,
			'text'=>$values['text'],
			'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
			'linkerror'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.Url::$curpage,
			'from'=>$back,
		);
		Email::Simple(
			$a['mail'],
			Eleanor::ExecBBLogic($l['error_t'],$repl),
			Eleanor::ExecBBLogic($l['error'],$repl)
		);
		unset($info['text']);
		$sent=true;
	}
}
if($a['log'] and $back and strpos($back,PROTOCOL.Eleanor::$domain.Eleanor::$site_path)===0 and !$errors and !$sent)
{	$R=Eleanor::$Db->Query('SELECT `title` FROM `'.P.'errors` INNER JOIN `'.P.'errors_l` USING(`id`) WHERE `language` IN (\'\',\''.LANGUAGE.'\') AND `id`='.$a['id'].' LIMIT 1');
	if($my=$R->fetch_assoc())
	{
		$E=new EE($my['title'],EE::USER,array('code'=>$a['http_code'],'back'=>$back));
		$E->Log();
	}
}

$a['text']=OwnBB::Parse($a['text']);
if($a['mail'])
	$Eleanor->Editor->ownbb=false;
$s=Eleanor::$Template->ShowError($a,$sent,$values,$errors,$back,$Eleanor->Captcha->disabled ? false : $Eleanor->Captcha->GetCode());

if($a['http_code'])
{
	Start('index',$a['http_code']);
	header('Retry-After: 0');
}
else
	Start();

echo$s;