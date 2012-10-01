<?php
/*
	Ёлемент шаблона: отображение капчи.

	@var array(
		name - им€ капчи
		w - ширина капчи
		h - высота капчи
		src - ссылка на изображение капчи
		s - содержимое контрола капчи
	)
*/
if(!defined('CMS'))die;
echo'<img id="'.$name.'" src="'.$src.'" style="cursor:pointer;" width="'.$w.'" height="'.$h.'" alt="" title="'.Eleanor::$Language['tpl']['captcha'].'" onclick="this.a;if(!this.a)this.a=this.src;this.src=this.a+\'&amp;new=\'+Math.random()" />'.Eleanor::Control($name,'hidden',$s);