<?php
return array(
	#For Classes/Comments.php
	'vc'=>'Visitors comments',
	'nc'=>'No comments so far no one wrote.',
	'anc'=>'Replies to this comment while no one wrote.',
	'lnp'=>'Load new comments',
	'addc'=>'Add new comment',
	'yn'=>'Your name',
	'yc'=>'Comment',
	'needch'=>'Your comment will be publicly available only after validation.',
	'captcha'=>'Security code',
	'captcha_'=>'type the characters you see',
	'cite'=>'Quote %s',
	'stmodwait'=>'This comment pending verification',
	'stblocked'=>'This comment is blocked',
	'answer'=>'Reply',
	'qquote'=>'Quick quote',
	'withsel'=>'-With marked-',
	'doact'=>'Activate',
	'toblock'=>'Block',
	'tomod'=>'To moderation',
	'save'=>'Save',
	'cancel'=>'Cancel',
	'answers'=>function($n){return $n.($n>1 ? ' answers' : ' answer');},
	'added_after'=>function($y,$m,$d,$h,$i,$s)
	{
		return rtrim('Added after '.($y>0 ? $y.($y>1 ? ' years' : ' year') : '')
			.($m>0 ? $m.($m>1 ? ' months' : ' month') : '')
			.($d>0 ? $d.($d>1 ? ' days' : ' day') : '')
			.($h>0 ? $h.($h>1 ? ' hours' : ' hour') : '')
			.($i>0 ? $i.($i>1 ? ' minutes' : ' minite') : '')
			.($s>0 ? $s.($s>1 ? ' seconds' : ' second') : ''));
	},
);