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
$op+=array(
	'width'=>150,#Ширина
	'height'=>150,#Высота
	'color'=>'fbf9f8',#Цвет текста
	'color2'=>'00ff00',
	'hicolor'=>'ff0000',
	'bgcolor'=>'fbf9f8',#Цвет фона
	'trans'=>true,#Прозрачность
	'speed'=>100,#Скорость движения
	'distr'=>'true',#Равномерное распределение
);

if($tags=isset($Eleanor->module['tags']) ? $Eleanor->module['tags'] : false)
{	$Tpl=Eleanor::$Template;#Костыль по другому не работает!
	foreach($tags as &$v)
		$v=$Tpl('Tag',$v);	if($Eleanor->Url->furl)
	{		$tags=join($tags);
		return'<div id="tag-cloud" style="text-align:center">'.$tags.'</div><script type="text/javascript">/*<![CDATA[*/CORE.AddScript("js/swfobject.js",function(){swfobject.embedSWF("addons/flash/tagcloud.swf?r="+Math.random(),"tag-cloud","'.$op['width'].'","'.$op['height'].'", "9.0.0",null,{tcolor:"0x'.$op['color'].'",tcolor2:"0x'.$op['color2'].'",hicolor2:"0x'.$op['hicolor'].'",tspeed:"'.$op['speed'].'",distr:"'.$op['distr'].'",mode:"tags",tagcloud:"<tags>'.str_replace(array('a href="','%','?','&amp;','&','"'),array('a href="'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,'%25','%3F','%26','%26','\\"'),$tags).'</tags>"},{},{'.($op['trans'] ? 'wmode:"transparent",' : '').'allowscriptaccess:"always",bgcolor:"#'.$op['bgcolor'].'"})})//]]></script>';
	}
	return'<div style="text-align:center">'.implode(' ',$tags).'</div>';
}