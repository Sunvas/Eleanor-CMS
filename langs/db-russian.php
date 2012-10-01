<?php
return array(
	#Для класса Db #ToDo! где именно?
	'connect'=>function($p){return'Невозможно подключиться к базе данных '.$p['db'].($p['no'] ? ': <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #<b>'.$p['no'].'</b>)' : '.');},
	'query'=>function($p){return'SQL запрос выполнился неудачно: <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #'.$p['no'].')';},
);