<?php
/*
	Элемент шаблона. Вывод 2х кнопок для редактирования и удаления чего-либо

	@var ссылка на редактирование
	@var ссылка на удаление
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];
if(isset($v_0))
	echo'<a href="'.$v_0.'" title="'.$ltpl['edit'].'"><img src="templates/Audora/images/edit.png" /></a>';
if(isset($v_1))
	echo'<a href="'.$v_1.'" title="'.$ltpl['delete'].'"><img src="templates/Audora/images/delete.png" /></a>';