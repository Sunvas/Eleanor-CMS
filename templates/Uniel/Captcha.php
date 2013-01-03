<?php
/*
	Элемент шаблона: отображение капчи.

	@var array(
		name - имя капчи
		w - ширина капчи
		h - высота капчи
		src - ссылка на изображение капчи
		s - содержимое контрола капчи
	)
*/
if(!defined('CMS'))die;
echo'<img id="'.$name.'" src="'.$src.'" style="cursor:pointer;" width="'.$w.'" height="'.$h.'" alt="" title="'.Eleanor::$Language['tpl']['captcha'].'" onclick="this.a;if(!this.a)this.a=this.src;this.src=this.a+\'&amp;new=\'+Math.random()" />'.Eleanor::Input($name,$s,array('type'=>'hidden'));