<?php
return array(
	#Для /admin.php
	'TEMPORARILY_BLOCKED'=>function($l,$m){ return 'В связи с частым вводом неправильного пароля, аккаунт пользователя <b>'.$l.'</b> был заблокирован!<br />Повторите попытку через '.$m.Russian::Plural($m,array(' минут.',' минуты.',' минуты.')); },
	'enter_to'=>'Вход в панель администратора',
);