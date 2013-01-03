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
BeAs('admin');
if(!Eleanor::$Login->IsUser())
	return ExitPage();
do
{	if(!isset($_GET['f']))
		break;
	global$Eleanor;
	$rp=$Eleanor->module['path'].'DIRECT'.DIRECTORY_SEPARATOR;
	$path=realpath($rp.Files::Windows(trim((string)$_GET['f'],'/\\')));
	if(!$path or strncmp($path,$rp,strlen($rp))!=0 or !is_file($path))
		break;	return Files::OutputStream(array('file'=>$path));
}while(false);
GoAway();