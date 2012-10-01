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
$config=include($Eleanor->module['path'].'config.php');
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_user-*.php','contacts');
Eleanor::$Template->queue[]='UserContacts';

if($_SERVER['REQUEST_METHOD']=='POST')
{	$Eleanor->Editor->ownbb=$Eleanor->Editor->smiles=$Eleanor->Editor->antilink=false;
	$values=array(
		'subject'=>isset($_POST['subject']) ? trim((string)Eleanor::$POST['subject']) : '',
		'message'=>$Eleanor->Editor_result->GetHtml('message'),
		'whom'=>isset($_POST['whom']) ? (int)$_POST['whom'] : 0,
		'sess'=>isset($_POST['sess']) ? (string)$_POST['sess'] : false,
	);
	$whom=Eleanor::FilterLangValues($config['whom']);
	$errors=array();
	do
	{		#Защита от F5
		if($values['sess'])
			Eleanor::StartSession($values['sess']);
		if(empty($_SESSION['can']))
			break;		if(!$whom)
			break;

		$whom=array_keys($whom);
		if(!isset($whom[$values['whom']]))
			$errors[]='WRONG_RESPONDER';

		if(!$values['subject'])
			$errors[]='EMPTY_SUBJECT';

		if(!isset($values['message'][7]))
			$errors[]='SHORT_MESSAGE';

		$canupload=Eleanor::$Permissions->MaxUpload();
		if($canupload===true)
			$canupload=5*1024*1024;#5 Mb жесткий предел

		$files=array();
		if($canupload and isset($_FILES['file']) and is_uploaded_file($_FILES['file']['tmp_name']))
			if($canupload!==true and $_FILES['file']['size']>$canupload)				$errors['FILE_TOO_BIG']=sprintf($lang['FILE_TOO_BIG'],Files::BytesToSize($canupload),Files::BytesToSize($_FILES['file']['size']));
			else
				$files=array($_FILES['file']['name']=>file_get_contents($_FILES['file']['tmp_name']));

		$cach=$Eleanor->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
		$Eleanor->Captcha->Destroy();
		if(!$cach)
			$errors[]='WRONG_CAPTCHA';

		if($errors)
			break;

		$subject=Eleanor::FilterLangValues($config['subject']);
		Eleanor::Mail($whom[$values['whom']],Eleanor::ExecBBLogic($subject,array('s'=>$values['subject'])),$values['message'],array('files'=>$files));
		$_SESSION['can']=false;

		$title[]=$lang['st'];
		$s=Eleanor::$Template->Sent();
		Start();
		echo$s;
		return;
	}while(false);
	Contacts($config,$errors);
}
else
	Contacts($config);

function Contacts($config,$errors=array())
{global$Eleanor,$title;
	$bypost=false;	if($errors)
	{		$bypost=true;		if($errors===true)
			$errors=array();
		$values=array(
			'subject'=>isset($_POST['subject']) ? (string)$_POST['subject'] : '',
			'message'=>isset($_POST['message']) ? (string)$_POST['message'] : '',
			'whom'=>isset($_POST['whom']) ? (int)$_POST['whom'] : 0,
			'sess'=>isset($_POST['sess']) ? (string)$_POST['sess'] : '',
		);
	}
	else
		$values=array(
			'subject'=>'',
			'message'=>'',
			'whom'=>0,
			'sess'=>'',
		);

	$title[]=$Eleanor->module['title'];
	$canupload=Eleanor::$Permissions->MaxUpload();
	$info=Eleanor::FilterLangValues($config['info']);
	$whom=Eleanor::FilterLangValues($config['whom']);
	$whom=$whom ? array_values($whom) : false;
	$Eleanor->Editor->ownbb=$Eleanor->Editor->smiles=false;

	if($canupload===true)
		$canupload=5*1024*1024;#5 Mb жесткий предел

	Eleanor::StartSession($values['sess']);
	$_SESSION['can']=true;
	$values['sess']=session_id();

	$s=Eleanor::$Template->Contacts($canupload,OwnBB::Parse($info),$whom,$values,$bypost,$errors,$Eleanor->Captcha->disabled ? $Eleanor->Captcha->GetCode() : false);
	Start();
	echo$s;}