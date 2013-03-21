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
do
{
	if(!isset($_GET['f']))
		break;
	$rp=Eleanor::$root.'templates'.DIRECTORY_SEPARATOR;
	$path=realpath($rp.Files::Windows(trim($_GET['f'],'/\\')));
	if(!$path or strncmp($path,$rp,strlen($rp))!=0 or !is_file($path))
		break;
	return Files::OutputStream(array('file'=>$path));
}while(false);
GoAway();