<?php
return array(
	#Для /core/others/comments_ajax.php
	'flood_limit'=>function($n){return 'На сайте включен флуд контроль! Подождите еще '.$n.Russian::Plural($n,array(' секунду.',' секунды',' секунд'));},
	'WRONG_CAPTCHA'=>'Защитный код введен с ошибкой',
);