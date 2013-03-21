<?php
return array(
	#Для всіх функцій Error() сервісів запуску системи
	'happened'=>'Сталася помилка',
	'you_are_banned'=>'Вас заблоковано!',
	'banlock'=>function($date,$reason){ return'Дата розблокування: '.($date ? Eleanor::$Language->Date($date) : 'невідома').'.<br />Причина: '.($reason ? $reason : 'невідома'); }
);