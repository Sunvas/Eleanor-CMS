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
$menu=include Eleanor::$root.'addons/menus/multiline.php';
try
{
	return$menu ? Eleanor::$Template->BlockMenuTree($menu,null) : false;
}
catch(EE$E)
{
	return'Template BlockMenuTree does not exists.';
}