<?php
return array(
	#For admin/index.php
	'lettertitle'=>'Email subject',
	'letterdescr'=>'Email text',
	'letter_reg'=>'Email registration',
	'letter_reg_fin'=>'Text of the email without activation',
	'letter_reg_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{pass} - user password<br />
{link} - link to your website',
	'letter_reg_act'=>'Text of letter with the activation of an e-mail',
	'letter_reg_act_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{pass} - user password<br />
{link} - link to your website<br />
{confirm} - link to confirm<br />
{hours} - activation period in hours',
	'letter_reg_act_admin'=>'Text of email with the activation of the administrator',
	'letter_act'=>'Email from the account activation',
	'letter_act_success'=>'Email of the successful activation',
	'letter_act_success_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{link} - link to your website',
	'letter_act_refused'=>'Text removal unactivated account',
	'letter_act_refused_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{link} - link to your website<br />
{reason} - reason for removal',
	'letter_passrem'=>'Email password recovery',
	'letter_passrem_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{link} - link to your website<br />
{confirm} - link to confirm',
	'letter_passremfin'=>'A new password (after restoration)',
	'letter_passremfin_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{pass} - user password<br />
{link} - link to your website<br />
{confirm} - link to confirm',
	'letter_newemail'=>'Letter change e-mail',
	'letter_newemail_'=>'{site} - site name<br />
{login} - user login<br />
{name} - user name<br />
{link} - link to your website<br />
{confirm} - link to confirm<br />
{newemail} - new e-mail<br />
{oldemail} - old e-mail',
	'letter_newemail_old'=>'Letter to the old e-mail (step 1)',
	'letter_newemail_new'=>'Письмо на новый e-mail (шаг 2)',
	'letters'=>'Email formats',
	'delc'=>'Confirm delete',
	'inactives'=>'Not activated users',
);