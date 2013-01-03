<?php
/*
	Шаблон блока "Вертикальное многоуровневое меню"

	@var строка меню, без начального <ul>, представляет собой последовательность <li><a...>...</a><ul><li>...</li></ul></li></ul>
*/
if(!defined('CMS'))die;
$GLOBALS['jscripts'][]='js/menu_multilevel.js';
$u=uniqid();
echo'<nav><ul id="',$u,'" class="blockmenu">',$v_0,'</ul></nav><script type="text/javascript">//<![CDATA[
$(function(){
	var li=$("#',$u,'").MultiLevelMenu({type:"col"}).children("li"),
		h=li.outerHeight();
	li.end().height(h*li.size());
});//]]></script>';