<?php
return array(
	#For admin/index.php
	'forallt'=>'-for all-',
	'list'=>'News',
	'tags_list'=>'Tags',
	'language'=>'Language',
	'tname'=>'Tag name',
	'delc'=>'Confirm delete',
	'addingt'=>'Adding tag',
	'editingt'=>'Editing tag',
	'empty_tag'=>'Tag name can not be empty!',
	'adding'=>'Adding news',
	'editing'=>'Editing news',
	'EMPTY_TITLE'=>function($l){return'Title can not be empty'.($l ? ' (for '.$l.')' : '');},
	'EMPTY_TEXT'=>function($l){return'The text can not be empty'.($l ? ' (for '.$l.')' : '');},
);