<?php
return array(
	#For admin/index.php
	'list'=>'Menus',
	'parent'=>'Parent',
	'text'=>'Text links',
	'text_'=>'HTML enabled!',
	'url'=>'Link address',
	'eval_url'=>'PHP code link',
	'eval_url_'=>'For the dynamic generation of links. Must contain the keyword return. For example: return$Eleanor->Url->Construct(array())',
	'params'=>'Extra options links',
	'params_'=>'For example: onclick="alert()"',
	'pos'=>'Position',
	'pos_'=>'Leave blank to append',
	'in_map'=>'Show in main site map',
	'activate'=>'Activate',
	'adding'=>'Adding menu item',
	'editing'=>'Editing menu item',
	'EMPTY_LINK'=>function($l){return'Neither the text of a link or address not specified'.($l ? ' (for '.$l.')' : '');},

	#For template
	'add'=>'Add menu item',
	'not_found'=>'No menu',
	'to_pages'=>'Per page: %s',
	'submenu'=>'Submenu:',
	'addsubmenu'=>'Add a submenu',
);