<?php
return array(
	#Для ajax/user/index.php
	'error_email'=>'Введен недопустимый e-mail!',
	'email_in_use'=>'Этот e-mail уже используется другим пользователем!',

	#Для ajax/user/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'Длина имени пользователя не должна превышать '.$l.Russian::Plural($l,array(' символ',' символа',' символов')).' символов. Вы ввели '.$e.Russian::Plural($e,array(' символ',' символа',' символов')).' символов.'; },
	'error_name'=>'Введен недопустимый ник',
	'name_in_use'=>'Этот ник уже используется другим пользователем!',
);