<?php
return array(
	#For admin/index.php
	'list'=>'List of static pages',
	'fp'=>'File pages',
	'parent'=>'Parent',
	'name'=>'Title',
	'content'=>'Content',
	'pos'=>'Position',
	'pos_'=>'Leave blank to append',
	'activate'=>'Activate',
	'adding'=>'Adding static page',
	'editing'=>'Editing a static page',
	'empty_title'=>function($l){return'Title not given'.($l ? ' (for '.$l.')' : '');},
	'empty_text'=>function($l){return'Content not given'.($l ? ' (for '.$l.')' : '');},
);