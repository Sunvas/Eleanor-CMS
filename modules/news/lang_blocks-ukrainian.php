<?php
return array(
	#Архів
	'year-'=>'Рік назад',
	'year+'=>'Рік вперед',
	'mon'=>'Пн',
	'tue'=>'Вт',
	'wed'=>'Ср',
	'thu'=>'Чт',
	'fri'=>'Пт',
	'sat'=>'Сб',
	'sun'=>'Нд',
	'_cnt'=>function($n){return$n.Ukrainian::Plural($n,array(' новина',' новини',' новин'));},
	'total'=>function($n){return'Всього - '.$n.Ukrainian::Plural($n,array(' новина',' новини',' новин'));},
	'no_per'=>'Новин за цей період немає',
);