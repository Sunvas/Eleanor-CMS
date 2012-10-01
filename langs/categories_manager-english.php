<?php
return array(
	#For /core/others/categories_manager.php
	'delc'=>'Delete confirm',
	'parent'=>'Parent',
	'name'=>'Name',
	'EMPTY_TITLE'=>function($l=''){return'Category name can not be empty'.($l ? ' (for '.$l.')' : '');},
	'descr'=>'Description',
	'picture'=>'Image',
	'preview'=>'Preview',
	'pos'=>'Position',
	'pos_'=>'Leave blank to append',
	'adding'=>'Adding category',
	'editing'=>'Editing category',

	#For template
	'add'=>'Add category',
	'list'=>'Categories',
	'subitems'=>'Subcategories:',
	'addsubitem'=>'Add subcategory',
	'up'=>'Move up',
	'down'=>'Move down',
	'no'=>'No categories',
	'to_pages'=>'Categories on the page: %s',
	'deleting'=>'Do you really want to delete the category &quot;%s&quot;?',
);