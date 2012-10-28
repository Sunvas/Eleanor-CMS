<?php
return array(
	#For index.php
	'loading'=>'Loading...',
	'to_top'=>'Up',
	'login'=>'Login:',
	'pass'=>'Password:',
	'enter'=>'Enter',
	'hello'=>'Welcome, %s!',
	'adminka'=>'Admin-panel',
	'exit'=>'Logout',
	'register'=>'Register',
	'lostpass'=>'Forgot your password?',
	'msjump'=>'-Jump to-',

	#For Confirm
	'no'=>'No',
	'yes'=>'Yes',

	#For Denied
	'site_close_text'=>'The site is temporarily unavailable! There are interesting works',

	#For EditDelete
	'delete'=>'Remove',
	'edit'=>'Edit',

	#For LangChecks
	'for_all_langs'=>'For all languages',

	#For Rating
	'average_mark'=>'Average rating: %s; Votes: %s',

	#For Pages
	'pages'=>'Pages:',
	'goto_page'=>'Go to page',

	#For Message
	'warning'=>'Warning',
	'error'=>'Mistake',
	'errors'=>'Mistaks',
	'info'=>'Information',

	#For Captcha
	'captcha'=>'Click to show more digits',

	#For BlockWhoOnline
	'users'=>function($n){ return$n.($n>1 ? ' users:' : ' user:'); },
	'minutes_ago'=>function($n){ return$n.($n>1 ? ' minutes ago' : ' minute ago'); },
	'bots'=>function($n){ return$n.' search '.($n>1 ? 'bots:' : 'bot:'); },
	'guests'=>function($n){ return$n.($n>1 ? ' guests' : ' guest'); },
	'alls'=>'Full list',

	#For BlockArchive
	'year-'=>'Year forward',
	'year+'=>'Year backward',
	'mon'=>'Mon',
	'tue'=>'Tue',
	'wed'=>'Web',
	'thu'=>'Thu',
	'fri'=>'Fri',
	'sat'=>'Sat',
	'sun'=>'Sun',
	'_cnt'=>function($n){return$n.' news';},
	'total'=>function($n){return'Totaly '.$n.' news';},
	'no_per'=>'No news in this period',
);