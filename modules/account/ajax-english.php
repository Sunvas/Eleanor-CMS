<?php
return array(
	#For ajax/user/index.php
	'error_email'=>'Entered an invalid e-mail!',
	'email_in_use'=>'This e-mail is already in use by another user!',

	#For ajax/user/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'User name length should not exceed '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
	'error_name'=>'Entered an invalid nickname',
	'name_in_use'=>'This nickname is already used by another user!',
);