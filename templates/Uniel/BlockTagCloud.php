<?php
/*
	Оформление содержимого блока "тучка тегов"

	@var массив опций
*/
if(!defined('CMS'))die;
$v_0+=array(
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

if(isset($GLOBALS['Eleanor']->module['tags']))
{
	$tags=$GLOBALS['Eleanor']->module['tags'];
	foreach($tags as &$v)
		$v='<a href="'.$v['_a'].'" style="font-size:12px" rel="tag">'.$v['name'].'</a>';
	$tags=join($tags);
	if($GLOBALS['Eleanor']->Url->furl)
		return'<div id="tag-cloud" style="text-align:center">'.$tags.'</div><script type="text/javascript">/*<![CDATA[*/CORE.AddScript("js/swfobject.js",function(){swfobject.embedSWF("addons/flash/tagcloud.swf?r="+Math.random(),"tag-cloud","'.$v_0['width'].'","'.$v_0['height'].'", "9.0.0",null,{tcolor:"0x'.$v_0['color'].'",tcolor2:"0x'.$v_0['color2'].'",hicolor2:"0x'.$v_0['hicolor'].'",tspeed:"'.$v_0['speed'].'",distr:"'.$v_0['distr'].'",mode:"tags",tagcloud:"<tags>'.str_replace(array('a href="','%','?','&amp;','&','"'),array('a href="'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,'%25','%3F','%26','%26','\\"'),$tags).'</tags>"},{},{'.($v_0['trans'] ? 'wmode:"transparent",' : '').'allowscriptaccess:"always",bgcolor:"#'.$v_0['bgcolor'].'"})})//]]></script>';
	return'<div style="text-align:center">'.$tags.'</div>';
}