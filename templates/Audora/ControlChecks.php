<?php
/*
	Внешний вид контрола "массив чекбоксов".

	@var array массив чексбосов, каждый элемент которого - массив с ключами:
		0 - сам чекбоксы
		1 - название
*/
if(!defined('CMS'))die;
$html='';
foreach($v_0 as &$v)
	$html.='<label>'.$v[0].' '.$v[1].'</label><br />';
return$html ? substr($html,0,-6) : '';