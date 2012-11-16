<?php
/*
	Элемент шаблона. Отображает языковые галочки для выбора языка(ов) материала

	@var флаг отмеченности чекбокса галочкой "Для всех языков"
	@var массив для определения отмеченности чекбоксов (галочками) для языков. По умолчанию язык считается включенным array('lang1'=>true,'lang2'=>false)
	@var имя переменной для JavaScript объекта управления галочками
	@var tabindex
*/
if(!defined('CMS'))die;
$GLOBALS['jscripts'][]='js/multilang.js';
$one=isset($v_0) ? $v_0 : false;
$langs=isset($v_1) ? (array)$v_1 : array();
$name=isset($v_2) ? $v_2 : false;
$ti=isset($v_3) ? array('tabindex'=>$v_3) : array();

$mchecks=array();
foreach(Eleanor::$langs as $k=>&$v)
	$mchecks[]='<label>'.Eleanor::Check('_langs[]',in_array($k,$langs),$ti+array('value'=>$k)).' '.$v['name'].'</label>';
echo'<label>',
	Eleanor::Check('_onelang',$one,$ti),
	' <b>'.Eleanor::$Language['tpl']['for_all_langs'],
	'</b></label><div style="display:',
	($one ? 'none' : 'block'),
	'">',
	join('<br />',$mchecks),
	'</div><script type="text/javascript">/*<![CDATA[*/',
	($name ? 'var '.$name.';' : ''),
	'$(function(){',
	($name ? $name.'=' : ''),
	'new MultilangChecks({mainlang:"',
	Language::$main,
	'"});});//]]></script>';