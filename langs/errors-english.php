<?php
return array(
	#For all functions Error() of services to starting system
	'happened'=>'An error occurred',
	'you_are_banned'=>'You are banned!',
	'banlock'=>function($date,$reason){ return'Date of end of ban: '.($date ? Eleanor::$Language->Date($date) : 'unknown').'.<br />Reason: '.($reason ? $reason : 'unknown'); },
);