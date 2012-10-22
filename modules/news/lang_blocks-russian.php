<?php
return array(
	#Архив
	'year-'=>'Год назад',
	'year+'=>'Год вперед',
	'mon'=>'Пн',
	'tue'=>'Вт',
	'wed'=>'Ср',
	'thu'=>'Чт',
	'fri'=>'Пт',
	'sat'=>'Сб',
	'sun'=>'Вс',
	'_cnt'=>function($n){return$n.Russian::Plural($n,array(' новость',' новости',' новостей'));},
	'total'=>function($n){return'Всего - '.$n.Russian::Plural($n,array(' новость',' новости',' новостей'));},
	'no_per'=>'Новостей за этот период нет',
);