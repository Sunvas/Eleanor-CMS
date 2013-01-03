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
$c=$Eleanor->Settings->GetInterface('full');
$Eleanor->module['title']=Eleanor::$Language['main']['options'];
$Eleanor->module['descr']=end($GLOBALS['title']);
Start();
echo$c;