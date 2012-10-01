<?php
return array(
	'no'=>'no',
	'yes'=>'yes',
	'all'=>'all',
	'ok'=>'OK',
	'update'=>'Refresh',
	'delete'=>'Remove',
	'edit'=>'Edit',
	'category'=>'Category',
	'loading'=>'Loading...',
	'to_top'=>'Up',
	'login'=>'Login:',
	'pass'=>'Password:',
	'enter'=>'Enter',
	'search'=>'Search',
	'cancel'=>'Cancel',
	'tags'=>'Tags',
	'site_close_text'=>'The site is temporarily unavailable! There are interesting works',
	'hello'=>'Welcome, ',
	'adminka'=>'Admin-panel',
	'exit'=>'Logout',
	'register'=>'Register',
	'lostpass'=>'Forgot your password?',
	'users'=>function($n)
	{
		return$n.($n>1 ? ' users:' : ' user:');
	},
	'minutes_ago'=>function($n)
	{
		return$n.($n>1 ? ' minutes ago' : ' minute ago');
	},
	'bots'=>function($n)
	{
		return$n.' search '.($n>1 ? 'bots:' : 'bot:');
	},
	'guests'=>function($n)
	{
		return$n.($n>1 ? ' guests' : ' guest');
	},
	'alls'=>'Full list',
	'back'=>'Back',
	'captcha'=>'Click to show more digits',
	'warning'=>'Warning',
	'error'=>'Mistake',
	'errors'=>'Mistaks',
	'info'=>'Information',
	'pages'=>'Pages:',
	'goto_page'=>'Go to page',
	'average_mark'=>'Average rating: %s; Votes: %s',
	'for_all_langs'=>'For all languages',
	'msjump'=>'-Jump to-',
);