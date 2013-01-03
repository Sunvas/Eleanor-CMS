<?php
/*
	Элемент шаблона. Отображает мультиязычные контролы в виде табов

	@var array('lang1'=>'content1','lang2'=>'content2',...)
	@var null (для совместимости с шаблониазтором)
*/
if(!defined('CMS'))die;
global$Eleanor;
$a=isset($v_0) ? $v_0 : array();

if(is_array($a))
{
	$GLOBALS['jscripts'][]='js/tabs.js';
	$tflags='';
	$add=uniqid('l');
	foreach($a as $k=>&$v)
	{
		echo'<div id="'.$add.'-'.$k.'" class="langtabcont">'.$v.'</div>';
		$tflags.='<a href="#" data-rel="'.$add.'-'.$k.'" class="'.$k.($k==Language::$main ? ' selected' : '').'" title="'.Eleanor::$langs[$k]['name'].'"><img src="images/lang_flags/'.$k.'.png" alt="'.Eleanor::$langs[$k]['name'].'" /></a>';
	}
	echo'<div id="div-'.$add.'" class="langtabs">'.$tflags.'</div><script type="text/javascript">/*<![CDATA[*/$(function(){$("#div-'.$add.' a").Tabs();})//]]></script>';
}
else
	echo$a;