<?php
return array(
	#Для Classes/VotingManager.php
	'rvoters'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' користувач реально проголосував',' користувача реально проголосували',' користувачів реально проголосували'));
	},
	'norv'=>'За цей варіант ніхто не голосував.',
	'va'=>'Варіант відповіді',
	'questions'=>'Питання',
	'addq'=>'Додати питання',
	'delq'=>'Вилучити питання',
	'votes'=>'Голосів',
);