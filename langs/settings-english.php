<?php
return array(
	#For /core/others/settings/simple.php & /core/others/settings/full.php
	'setting_og'=>'Setting values of group\'s settings',
	'reset_g_con'=>'Reset group\'s options',
	's_phrase_len'=>'Search phrase must be more than two characters in length!',
	'ops_not_found'=>'For query &quot;%s&quot; settings was not found',
	'cnt_seaop'=>'Search settings (found %s)',
	'f_not_load'=>'File not loaded',
	'error_in_code'=>'Error in code',
	'op_errors'=>'There are an errors',
	'grlist'=>'List of setting\'s groups',
	'nooptions'=>'Options none',
	'options'=>'Settings',
	'ops_without_g'=>'Settings without groups',
	'import'=>'Importing settings',
	'export'=>'Export settings',
	'incorrect_s_file'=>'Incorrect file structure! (%s)',
	'im_nogrname'=>'One of the groups is not a name!',
	'im_noopname'=>'One of the settings there is no name!',

	#For /core/others/settings/full.php
	'delc'=>'Confirm delete',
	'empty_gt'=>function($l=''){ return'Name of group is empty'.($l ? ' (for '.$l.')' : ''); },
	'adding_g'=>'Adding a group setting',
	'editing_g'=>'Editing Group settings',
	'adding_opt'=>'Adding settings',
	'editing_opt'=>'Edit settings',
	'empty_ot'=>function($l=''){ return'Not filled in the name of the settings'.($l ? ' (for '.$l.')' : ''); },
);