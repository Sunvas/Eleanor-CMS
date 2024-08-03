<?php
return array(
	#Для admin/index.php
	'from'=>'Тексты для замены',
	'from_'=>'Можно указать несколько текстов через запятую',
	'to'=>'Текст ссылки',
	'to_'=>'HTML разрешен! Если не заполнить, текстом ссылки станет исходный текст.',
	'reg'=>'Регулярное выражение?',
	'reg_'=>'Регулярное должна возвратить 2 группы замены. Замена будет проходить по шаблону \1&lt;a&gt;\2&lt;/a&gt;',
	'rege'=>'Регулярное выражение введено с ошибкой!',
	'url'=>'Адрес ссылки',
	'url_'=>'&lt;a href=',
	'eval_url'=>'PHP код ссылки',
	'eval_url_'=>'Для динамической генерации ссылки. Обязательно должно содержать ключевое слово return. Например: return$Eleanor->Url->Construct(array())',
	'params'=>'Дополнительные параметры ссылки',
	'params_'=>'Например: onclick="alert()"',
	'date_from'=>'Начало',
	'date_till'=>'Окончание',
	'activate'=>'Активировать',
	'list'=>'Список ссылок',
	'adding'=>'Добавление ссылки',
	'editing'=>'Редактирование ссылки',
	'EMPTY_FROM'=>function($l){return'Тексты для замены не заданы'.($l ? ' (для '.$l.')' : '');},
	'EMPTY_LINK'=>function($l){return'Ни текст для замены, ни ссылка замены не заданы'.($l ? ' (для '.$l.')' : '');},
);