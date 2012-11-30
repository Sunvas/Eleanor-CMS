<?php
return array(
	#For Classes/VotingManager.php
	'rvoters'=>function($n)
	{
		return$n.($n==1 ? ' user' : ' users').' really voted.';
	},
	'norv'=>'For this option no one voted.',
	'va'=>'Answer',
	'questions'=>'Questions',
	'addq'=>'Add question',
	'delq'=>'Delete question',
	'votes'=>'Voted',
);