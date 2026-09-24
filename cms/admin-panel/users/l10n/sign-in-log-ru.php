<?php
namespace Eleanor\Classes\L10n;

return[
	'title'=>'Журнал попыток входа',
	'filter'=>'Фильтр',
	'by-user'=>'Пользователь:',
	'do-filter'=>'Фильтровать',
	'user'=>'Пользователь',
	'date'=>'Дата',
	'result'=>'Результат',
	'browser'=>'Браузер',
	'no-records'=>'Записи не найдены',

	'say-total'=>fn($n)=>"Всего <b>$n</b> ".Ru::Plural($n,'попытка входа','попытки входа','попыток входа'),
];