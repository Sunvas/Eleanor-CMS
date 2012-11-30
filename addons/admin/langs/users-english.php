<?php
return array(
	#For /addons/admin/modules/users.php
	'personal'=>'Personal',
	'gender'=>'Gender',
	'nogender'=>'Unknown',
	'male'=>'Male',
	'female'=>'Female',
	'bio'=>'Biography',
	'interests'=>'Interests',
	'location'=>'Location',
	'site'=>'Site',
	'site_'=>'Enter a website address, beginning with http://',
	'signature'=>'Signature',
	'connect'=>'Communications',
	'vk'=>'VK',
	'vk_'=>'Please enter only your id, or name',
	'twitter_'=>'Please enter nickname only',
	'theme'=>'Template',
	'by_default'=>'Default',
	'editor'=>'Editor',
	'lettertitle'=>'Email subject',
	'letterdescr'=>'Email text',
	'letter4new'=>'Email to create a new user',
	'descr4new'=>'{site} - site name<br />
{name} - user name<br />
{login} - user login<br />
{pass} - user password<br />
{link} - link to your site',
	'letter4name'=>'Email to changing user name',
	'descr4name'=>'{site} - site name<br />
{name} - user login<br />
{oldlogin} - old user login<br />
{newlogin} - new user login<br />
{link} - link to your site',
	'letter4pass'=>'Email when the password changed',
	'letters'=>'Letters formats',
	'whoonline'=>'Who online',
	'delc'=>'Confirm delete',
	'adding'=>'Creating user',
	'editing'=>'Editing user',
	'list'=>'Users list',

	#Errors
	'NAME_TOO_LONG'=>function($l,$e){ return'User name length should not exceed '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
	'PASS_TOO_SHORT'=>function($l,$e){ return'The password should be at least '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
);