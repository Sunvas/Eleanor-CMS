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
global$Eleanor;
$GLOBALS['title'][]=$Eleanor->module['title'];

#Часть для поисковика google.com см: http://www.google.com/cse/?hl=ru
$g=Eleanor::$Template->GoogleSearch(include $Eleanor->module['path'].'config.php');
Start();
echo$g;