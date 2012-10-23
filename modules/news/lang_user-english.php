<?php
return array(
	#For user/index.php
	'n'=>'News',
	'my'=>'My news',
	'deleted'=>'Successfully removed',
	'delc'=>'Confirm delete',
	'search_limit'=>function($s1,$s2){ return'You can use the search again in '.$s1.' second'.($s1>1 ? 's' : '').'! Wait '.$s2.' second'.($s2>1 ? 's' : '').'.'; },
	'sym_limit'=>function($ch){ return'The search query must be at least '.$ch.' character'.($ch>1 ? 's' : '').'!'; },
	'notofind'=>'Not specified search criteria',
	'categs'=>'Categories',
	'tags'=>'Tags',
	'search'=>'Search',
	'from'=>'News from category &quot;%s&quot;',
	'for'=>'News for %s',
	'wt'=>'News with tag &quot;%s&quot;',

	#For user/addedit.php
	'adding'=>'Adding news',
	'editing'=>'Aditing news',
	'FLOOD_LIMIT'=>'You can add news again in %s seconds! Wait %s seconds.',
	'ERROR_END_DATE'=>'Field &quot;Available until&quot; is filled incorrectly',
	'ERROR_END_DATE_IN_PAST'=>'Date &quot;Available until&quot; must be in future',
	'EMPTY_TITLE'=>function($l){return'Title can not be empty'.($l ? ' (for '.$l.')' : '');},
	'EMPTY_TEXT'=>function($l){return'Text can not be empty'.($l ? ' (for '.$l.')' : '');},
	'waitmod'=>'Waiting for moderation',
	'nssadded'=>'News successfully added',
	'nssedit'=>'News edited successfully',
	'FILL_AUTHOR'=>'Please introduce',
);