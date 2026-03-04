<?php
namespace Eleanor\Classes\L10n;

return[
	'title'=>'Список статических страниц',
	'create'=>'Создать страницу',
	'filter'=>'Фильтр',
	'by-id'=>'ID:',
	'do-filter'=>'Фильтровать',
	'caption'=>'Заголовок страницы',
	'location'=>'Адрес страницы',
	'status'=>'Статус',
	'modified'=>'Изменено',
	'delete'=>'Удалить',
	'nothing-found'=>'Страницы не найдены',
	'yes'=>'Да',
	'no'=>'Нет',

	'say-total'=>fn($n)=>"Всего <b>{$n}</b> ".Ru::Plural($n,['страница','страницы','страниц']),
];