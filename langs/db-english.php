<?php
return array(
	#For Db class #ToDo! где именно?
	'connect'=>function($p){return'Can\'t connect to database '.$p['db'].($p['no'] ? ': <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #<b>'.$p['no'].'</b>)' : '.');},
	'query'=>function($p){return'Execution of SQL query failed: <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #'.$p['no'].')';},
);