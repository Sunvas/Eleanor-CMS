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
$c='';
foreach(Eleanor::$vars['templates'] as &$v)
{	$f=Eleanor::$root.'/templates/'.$v.'.php';
	if(!file_exists($f))
		continue;
	$a=include$f;
	$name=$a && is_array($a) && isset($a['name']) ? $a['name'] : $v;
	$c.=Eleanor::Option($name,$v,basename(Eleanor::$Template->default['theme'])==$v);
}
return$c ? '<div style="text-align:center">'.Eleanor::Select('themesel',$c,array('onchange'=>'window.location=\'index.php?newtpl=\'+$(this).val()')).'</div>' : '';