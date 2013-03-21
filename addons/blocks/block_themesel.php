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
$opts=array();
foreach(Eleanor::$vars['templates'] as &$v)
{
	$f=Eleanor::$root.'/templates/'.$v.'.settings.php';
	if(!file_exists($f))
		continue;
	$a=include$f;
	$opts[$v]=$a && is_array($a) && isset($a['name']) ? Eleanor::FilterLangValues((array)$a['name']) : $v;
}
try
{
	return count($opts)>1 ? Eleanor::$Template->BlockThemeSel($opts,null) : false;
}
catch(EE$E)
{
	return'Template BlockThemeSel does not exists.';
}