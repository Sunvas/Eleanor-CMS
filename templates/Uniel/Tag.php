<?php
/*
	Элемент шаблона: оформление тегов.

	@var array(
		_a - ссылка на материалы с этим тегом
		cnt - количество материалов с этим тегом
		name - название тега
	)
*/
if(!defined('CMS'))die;
if(!isset($cnt))
	$size='';
elseif($cnt<10)
	$size=' style="font-size:10px" class="smallest"';
elseif($cnt>=10 and $cnt<50)
	$size=' style="font-size:14px" class="small"';
elseif($cnt>=50 and $cnt<200)
	$size=' style="font-size:18px" class="medium"';
elseif($cnt>=200 and $cnt<500)
	$size=' style="font-size:22px" class="large"';
elseif($cnt>=500)
	$size=' style="font-size:26px" class="largest"';
echo'<a href="'.$_a.'"'.$size.' rel="tag">'.$name.'</a>';