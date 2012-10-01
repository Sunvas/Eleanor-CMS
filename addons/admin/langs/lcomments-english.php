<?php
return array(
	#For /addons/admin/modules/lcomments.php
	'delc'=>'Delete confirmation',
	'list'=>'Comments list',
	'editing'=>'Editing a comment',

	#For template
	'news'=>function($n){return$n.($n==1 ? ' new comment' : ' new comments');},
	'deleting'=>'Are you sure you want to to delete comment &quot;%s&quot;?',
	'filter'=>'Filter',
	'module'=>'Module',
	'date'=>'Date',
	'author'=>'Author',
	'published'=>'Published in',
	'text'=>'Text',
	'cnf'=>'Comments not found',
	'cpp'=>'Comments per page: %s',
	'blocked'=>'Blocked',
	'status'=>'Status',
);