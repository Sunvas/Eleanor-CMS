<?php
return array(
	#Для ajax/user/index.php
	'error_email'=>'Введено неприпустимий e-mail!',
	'email_in_use'=>'Цей e-mail вже використовується іншим користувачем!',

	#Для ajax/user/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'Довжина імені користувача не повинна перевищувати '.$l.Ukrainian::Plural($l,array(' символ',' символи',' символів')).' символів. Ви ввели '.$e.Ukrainian::Plural($e,array(' символ',' символи',' символів')).' символів.'; },
	'error_name'=>'Введено неприпустимий нік',
	'name_in_use'=>'Цей нік вже використовується іншим користувачем!',
);