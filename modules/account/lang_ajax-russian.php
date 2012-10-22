<?php
return array(
	#Для ajax/user/index.php
	'error_email'=>'Введен недопустимый e-mail!',
	'email_in_use'=>'Этот e-mail уже используется другим пользователем!',

	#Для ajax/user/register.php
	'name_too_long'=>function($n)
	{
		return'Длина ника превышает допустимый предел в '.$n.Russian::Plural($n,array(' символ',' символа',' символов'));
	},
	'error_name'=>'Введен недопустимый ник',
	'name_in_use'=>'Этот ник уже используется другим пользователем!',
);