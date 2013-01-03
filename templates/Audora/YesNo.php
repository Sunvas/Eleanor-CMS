<?php
/*
	Элемент шаблона. Формирует визуальное представление понятий "да" и "нет" (включено или выключено).

	@var флаг "да" или "нет"
*/
if(!defined('CMS'))die;
$yes=!empty($v_0);
$t=$yes ? Eleanor::$Language['tpl']['yes'] : Eleanor::$Language['tpl']['no'];
return'<img src="'.($yes ? Eleanor::$Template->default['theme'].'images/active.png' : Eleanor::$Template->default['theme'].'images/inactive.png').'" alt="" title="'.$t.'" />';