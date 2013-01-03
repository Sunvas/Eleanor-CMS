<?php
return array(
	#For /addons/admin/modules/blocks.php
	'lab'=>'List of all blocks',
	'bpos'=>'Blocks positions',
	'ipages'=>'Pages identification',
	'delc'=>'Delete confirmation',
	'bydef'=>'By default',
	'editing'=>'Editing block',
	'adding'=>'Adding block',
	'empty_title'=>function($l){return'The name of the block does not filled'.($l ? ' (для '.$l.')' : '');},
	'editingi'=>'Editing page identifier',
	'addingi'=>'Adding page identifier',
	'notitle'=>function($l){return'You did not enter the title of page identifier'.($l ? ' (для '.$l.')' : '');},
	'errcode'=>'In the code of identification made a mistake: %s',
);