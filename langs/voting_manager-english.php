<?php
return array(
	#For /core/others/votingmanager.php
	'errorva'=>'An error occurred while receiving data from survey!',
	'EMPTY_VARIANT'=>function($l){return'One of the answers are not filled'.($l ? ' (for '.$l.')' : '');},
	'EMPTY_TITLE'=>function($l){return'Title of question was not filled'.($l ? ' (for '.$l.')' : '');},
	'DATES'=>'Date of completion of the survey can not be earlier than their start date!',
	'addvoting'=>'Add voting',
	'dbegin'=>'Start date',
	'lblank'=>'Leave empty to begin the voting now',
	'dend'=>'Finish date',
	'onlyusers'=>'Available only to users',
	'onlyusers_'=>'Guests can not vote. Users can only vote one time.',
	'againdays'=>'Days before a new vote',
	'againdays_'=>'The number of days after which user can vote again',
	'votes'=>'Votes',
	'question'=>'Question',
	'vv'=>'Possible answers',
	'multiple'=>'Multiple answers',
	'maxa'=>'The maximum number of answers',
);