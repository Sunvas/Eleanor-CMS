<?php
return array(
	#Для всех функций Error() сервисов запуска системы
	'happened'=>'Произошла ошибка',
	'you_are_banned'=>'Вы забанены!',
	'banlock'=>function($date,$reason){ return'Дата разблокировки: '.($date ? Eleanor::$Language->Date($date) : 'неизвестна').'.<br />Причина: '.($reason ? $reason : 'неизвестна'); }
);