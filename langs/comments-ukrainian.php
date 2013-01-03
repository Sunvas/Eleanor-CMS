<?php
return array(
	#Для /core/others/comments_ajax.php
	'flood_limit'=>function($n){return 'На сайті включений флуд контроль! Pачекайте ще '.$n.Ukrainian::Plural($n,array(' секунду.',' секунди',' секунд'));},
	'WRONG_CAPTCHA'=>'Захисний код введений з помилкою',
);