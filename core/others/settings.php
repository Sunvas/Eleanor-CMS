<?php
/*
	Copyright В© Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
$full=dirname(__file__).'/settings/full.php';
if(extension_loaded('ionCube Loader') and is_file($full))
	include$full;
else
	include dirname(__file__).'/settings/simple.php';