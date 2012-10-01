<?php
return array(
	#For /addons/admin/modules/modules.php
	'delc'=>'Confirm delete',
	'adding'=>'Adding module',
	'editing'=>'Editing module',
	'empty_title'=>function($l){return'Module name can not be empty'.($l ? ' (for '.$l.')' : '');},
	'sec_exists'=>function($s){return'Module with section'.(count($s)>0 ? 's' : '').' &quot;'.join('&quot;, &quot;',$s).'&quot; already exists';},

	#For template
	'NOSERVICES'=>'You have not selected any service, which will be available this module',
	'WRONG_PATH'=>'Wrong path',
	'add'=>'Add module',
	'list'=>'Modules',
	'module'=>'Module',
	'services'=>'Services',
	'groups'=>'Groups',
	'm_folder'=>'Module folder',
	'access_in_s'=>'Available in the services',
	'multi'=>'Multiservice?',
	'multi_'=>'Search for the module will be carried in [catalog module]/[service name]/[file name].php',
	'files'=>'Files',
	'filename'=>'Filename',
	'img'=>'Logo',
	'img_'=>'Relative to the directory images/modules/. Please use the * character instead of the tire size (big, small...). news-*.png',
	'prot'=>'Protected modules?',
	'prot_'=>'This option can be set only once - during the installation. So marked particularly important modules that can not be deleted, and editing is limited.',
	'files_'=>'If the module is not multiservice, each service will use the specified file',
	'sections'=>'Module sections',
	'descr_'=>'HTML tags disabled',
	'updown'=>'Move',
	'deleting'=>'Do you really want to delete a module &quot;%s&quot;?',
);