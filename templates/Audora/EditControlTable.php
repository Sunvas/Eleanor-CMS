<?php
/*
	Шаблон таблицы правки/создания контрола.

	@var массив настроек контрола. Ключи:
		preview - предпросмотр контрола
		type - select с выбором типа контрола
		settings - массив с настройками контрола. Формат array('name'=>array())
		td - массив с описаниями контрола. Формат array('name'=>array(название, описание))
	@var флаг того, что текущий запрос это AJAX
	@var текст ошибки
	@var флаг того, что нужно возвратить только превьюшку
	@var подгруппа контрола (подгруппа - это объединение свойств похожих контролов)
	@var тип контрола (edit,text,select...)
*/
if(!defined('CMS'))die;
$a=&$v_0;
$ajax=&$v_1;
$error=&$v_2;
$onlyprev=&$v_3;
$sgroup=&$v_4;
$type=&$v_5;

$lang=Eleanor::$Language->Load($theme.'langs/controls-*.php',false);
$prev=$error ? Eleanor::$Template->Message($error,'error') : ($a['preview'] ? Eleanor::$Template->LangEdit($a['preview'],null) : $a['preview']);
if($onlyprev)
	return$prev;
$c='';

if(!$ajax)
	$c.='<table class="tabstyle tabform" id="edit-control-table"><tr><td class="label">'.$lang['control_type'].'</td><td>'.$a['type'].' '.Eleanor::Button(Eleanor::$Language['tpl']['update'],'button',array('onclick'=>'EC.ChangeType(true)')).'</td></tr>';

$c.='<tr style="border:3px solid black" class="temp"><td class="label">'.$lang['preview'].'<br /><span class="small">'.$lang['preview_'].'</span></td><td id="edit-control-preview">'.$prev.'</td></tr>';

foreach($a['settings'] as $k=>&$v)
	if($v)
		$c.='<tr class="temp"><td class="label">'.$a['td'][$k][0].':<br /><span class="small">'.$a['td'][$k][1].'</span></td><td>'.Eleanor::$Template->LangEdit($v,null).'</td></tr>';

if(!$ajax)
{	$GLOBALS['jscripts'][]='js/edit_control.js';
	if(!empty($_SESSION['controls']['controls_name']))
	{
		$pn=reset($_SESSION['controls']['controls_name']);
		$a=array_slice($_SESSION['controls']['controls_name'],1);
		foreach($a as &$v)
			$pn.='['.$v.']';
		$pn.='['.$k.']';
	}
	else
		$pn='controls';
	if(!empty($_SESSION['controls']['settings_name']))
	{
		$sn=reset($_SESSION['settings']['controls_name']);
		$a=array_slice($_SESSION['settings']['controls_name'],1);
		foreach($a as &$v)
			$sn.='['.$v.']';
		$sn.='['.$k.']';
	}
	else
		$sn='settings';

	$c.='</table><script type="text/javascript">//<![CDATA[
EC.sett_group={'.$type.':"'.$sgroup.'"};
EC.type="'.$type.'";
EC.session="'.session_id().'";
EC.service="'.Eleanor::$service.'";
EC.pref_prev="'.$pn.'[preview]";
EC.pref_sett="'.$sn.'";//]]></script>';
}

if($ajax)
	$c.='<script type="text/javascript">//<![CDATA[
EC.sett_group.'.$type.'="'.$sgroup.'";
$("#edit-control-table span.labinfo").poshytip({
	className:"tooltip",
	offsetX:-7,
	offsetY:16,
	allowTipHover:false
});//]]></script>';
echo$c;