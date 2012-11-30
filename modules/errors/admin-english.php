<?php
return array(
	#For admin/index.php
	'name'=>'Title',
	'descr'=>'Description',
	'image'=>'Error logo',
	'preview'=>'Preview',
	'http_status'=>'HTTP status',
	'http_status_'=>'403, 404...',
	'mail_'=>'User can send you a notice. It comes to this e-mail.',
	'log'=>'Logging',
	'log_'=>'When visit this page, creates a log entry in site errors.',
	'letters'=>'Letters formats',
	'lettertitle'=>'Letter subject',
	'letterdescr'=>'Text of letter',
	'letter_error'=>'Letter of error by user',
	'letter_error_'=>'{site} - site name<br />
{name} - user name<br />
{fullname} - full name<br />
{userlink} - link to user<br />
{text} - users text<br />
{link} - link to your website<br />
{linkerror} - error page
{from} - link to error',
	'delc'=>'Confirm delete',
	'list'=>'Error pages',
	'adding'=>'Adding a page error',
	'editing'=>'Editing a page error',
	'empty_title'=>function($l){return'Name not specified'.($l ? ' (for '.$l.')' : '');},
	'empty_text'=>function($l){return'Description not specified'.($l ? ' (for '.$l.')' : '');},
);