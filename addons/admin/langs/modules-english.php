<?php
return array(
	#For /addons/admin/modules/modules.php
	'list'=>'Modules',
	'delc'=>'Confirm delete',
	'adding'=>'Adding module',
	'editing'=>'Editing module',
	'empty_title'=>function($l){ return'Module name can not be empty'.($l ? ' (for '.$l.')' : ''); },
	'sec_exists'=>function($s){ return'Module with section'.(count($s)>0 ? 's' : '').' &quot;'.join('&quot;, &quot;',$s).'&quot; already exists'; },
);