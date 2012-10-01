<?php
return array(
	#Для ajax/user/index.php
	'error_email'=>'Введено неприпустимий e-mail!',
	'email_in_use'=>'Цей e-mail вже використовується іншим користувачем!',

	#Для ajax/user/register.php
	'name_too_long'=>function($n)
	{		return'Довжина ника перевищує припустиму межу в '.$n.Ukrainian::Plural($n,array(' символ',' символи',' символів'));
	},
	'error_name'=>'Введено неприпустимий нік',
	'name_in_use'=>'Цей нік вже використовується іншим користувачем!',

	#Для шаблона
	'noavatars'=>'Доступних аватарів немає',
	'cancel'=>'Скасувати',
	'togals'=>'До галерей',
);