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
global$Eleanor;
if(!isset($op) or !is_array($op))
	$op=array();
try
{
	return (string)Eleanor::$Template->BlockTagCloud($op,null);
}
catch(EE$E)
{
	return'Template BlockTagCloud does not exists.';
}