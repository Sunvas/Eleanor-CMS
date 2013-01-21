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
$lang=Eleanor::$Language->Load('addons/admin/langs/themes-*.php',false);
$event=isset($_POST['event']) ? $_POST['event'] : '';

switch($event)
{
	case'copy':
		$theme=isset($_POST['theme']) ? (string)$_POST['theme'] : '';
		$newtpl=isset($_POST['newtpl']) ? (string)$_POST['newtpl'] : '';
		if(preg_match('#^[a-z0-9\-_\.]+$#i',$theme)==0 or preg_match('#^[a-z0-9\-_\.]+$#i',$newtpl)==0)
			return Error($lang['incorr_symb']);
		if(file_exists(Eleanor::$root.'templates/'.$newtpl))
			return Error(sprintf($lang['theme_exists'],$newtpl));
		if(!file_exists(Eleanor::$root.'templates/'.$theme))
			return Error(sprintf($lang['no_ish_thm'],$theme));
		$res=Files::Copy(Eleanor::$root.'templates/'.$theme,Eleanor::$root.'templates/'.$newtpl);

		$files=glob(Eleanor::$root.'templates/'.$theme.'.*');
		if($files)
			foreach($files as &$v)
				Files::Copy($v,dirname($v).DIRECTORY_SEPARATOR.preg_replace('#^[^\.]+\.#',$newtpl.'.',basename($v)));

		if($res)
			return Result(true);
		Error();
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}