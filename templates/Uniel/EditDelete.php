<?php
/*
	Элемент шаблона. Вывод 2х кнопок для редактирования и удаления чего-либо

	@var отображаемый текст
	@var error|warning|info определяет тип иконки. По умолчанию тип warning
*/
if(!defined('CMS'))die;
if(isset($v_0))
	echo'<a href="'.$v_0.'"><img src="templates/Audora/images/edit.png" /></a>';
if(isset($v_1))
	echo'<a href="'.$v_1.'"><img src="templates/Audora/images/delete.png" /></a>';