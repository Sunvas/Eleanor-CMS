<?php
return array(
	#For /admin.php
	'TEMPORARILY_BLOCKED'=>function($l,$m){ return '<b>'.$l.'</b> has been blocked due to frequent incorrect password entering. Try again after '.$m.($m==1 ? ' minute.' : ' minutes'); },
	'enter_to'=>'Login to admin panel',
);