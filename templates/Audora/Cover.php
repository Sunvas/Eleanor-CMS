<?php
/*
	"Обертка" для контента модулей в админке. Включает в себя вывод подзаголовка и ошибки

	@var конент модуля
	@var текст ошибки
	@var тип ошибки warning,error,info
*/
if(!defined('CMS'))die;
echo Eleanor::$Template->Title(is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title']);
if(!empty($v_1))
	echo Eleanor::$Template->Message($v_1,isset($v_2) ? $v_2 : null);
if($v_0)
	echo Eleanor::$Template->OpenTable(),$v_0,Eleanor::$Template->CloseTable();
