<?php
return array(
	#For admin/index.php
	'from'=>'Words to replace',
	'from_'=>'You can specify multiple words separated by commas',
	'to'=>'Link text',
	'to_'=>'HTML is allowed! If you do not fill in the link text will be the original word.',
	'reg'=>'Regular expression?',
	'reg_'=>'Regular expression must return 3 groups to replate. ����� ���� ��������� �� ������� \1&lt;a&gt;\2&lt;/a&gt;',
	'rege'=>'Regular expression was entered with error!',
	'url'=>'Link address',
	'url_'=>'&lt;a href=',
	'eval_url'=>'PHP code reference',
	'eval_url_'=>'For the dynamic generation of links. Must contain the keyword return. For example: return$Eleanor->Url->Construct(array())',
	'params'=>'Extra options links',
	'params_'=>'For example: onclick="alert()"',
	'date_from'=>'Starting',
	'date_till'=>'Ending',
	'activate'=>'Activate',
	'list'=>'Words list',
	'adding'=>'Adding word',
	'editing'=>'Editing word',
	'EMPTY_FROM'=>function($l){return'Replacement word is not defined'.($l ? ' (for '.$l.')' : '');},
	'EMPTY_LINK'=>function($l){return'Neither the text of a link or address not specified'.($l ? ' (for '.$l.')' : '');},

	#For template
	'add'=>'Add the word',
	'not_found'=>'Words not found',
	'to_pages'=>'Per page: %s',
);