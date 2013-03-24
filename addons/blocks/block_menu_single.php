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
if(isset($CONFIG['parent']))
	$parent=$CONFIG['parent'];
$menu=include Eleanor::$root.'addons/menus/single.php';
try
{
	return$menu ? Eleanor::$Template->BlockMenuSingle($menu,null) : false;
}
catch(EE$E)
{
	return'Template BlockMenuSingle does not exists.';
}