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
if(isset($GLOBALS['Eleanor']->module['path']) and is_file($f=$GLOBALS['Eleanor']->module['path'].'/block_archive.php'))
	return include$f;
return include Eleanor::$root.'modules/news/block_archive.php';