<?php
return array(
	#For /core/others/comments_ajax.php
	'flood_limit'=>function($n){return 'The site enabled flood control! Please, wait '.$n.($n>1 ? ' seconds' : ' second');},
	'WRONG_CAPTCHA'=>'Security code is entered with error.',
);