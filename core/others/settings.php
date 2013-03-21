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
$full=__dir__.'/settings/full.php';
if(extension_loaded('ionCube Loader') and is_file($full))
	include$full;
else
	include __dir__.'/settings/simple.php';