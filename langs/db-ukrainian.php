<?php
return array(
	#Для класу Db #ToDo! где именно?
	'connect'=>function($p){return'Неможливо підключитися до бази даних '.$p['db'].($p['no'] ? ': <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #<b>'.$p['no'].'</b>)' : '.');},
	'query'=>function($p){return'SQL запит виконався невдало: <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #'.$p['no'].')';},
);