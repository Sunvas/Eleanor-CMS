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
$n=isset($GLOBALS['Eleanor']->module['section']) ? $GLOBALS['Eleanor']->module['section'] : 'def';
$cache=Eleanor::$Cache->Get('categories_'.$n.'_'.Language::$main);

if($cache===false and isset($GLOBALS['Eleanor']->Categories))
{
	$Fbc=function($a,$c='<ul>') use (&$Fbc)
	{
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$n=-1;
		$nonp=true;
		foreach($a as &$v)
		{
			++$n;
			$nl=strlen($v['parents']);
			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif($nonp)
				{
					$c.=$Fbc(array_slice($a,$n));
					$nonp=false;
				}
				continue;
			}
			if($n>0)
				$c.='</li>';
			$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($v['id']),true,'/').'">'.$v['title'].'</a>';
			$nonp=true;
		}
		return$c.'</li></ul>';
	};
	$mn=isset($GLOBALS['Eleanor']->module['name']) ? $GLOBALS['Eleanor']->module['name'] : '';

	$cache=$Fbc($GLOBALS['Eleanor']->Categories->dump,'');
	Eleanor::$Cache->Put('categories_'.$n.'_'.Language::$main,$cache);
}

try
{
	return$cache ? Eleanor::$Template->BlockCategories($cache,null) : false;
}
catch(EE$E)
{
	return'Template BlockCategories does not exists.';
}