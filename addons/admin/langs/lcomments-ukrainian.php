<?php
return array(
	#Для /addons/admin/modules/lcomments.php
	'delc'=>'Підтвердження видалення',
	'list'=>'Список коментарів',
	'editing'=>'Редагування коментаря',

	#Для шаблону
	'news'=>function($n){return$n.Russian::Plural($n,array(' новий коментар',' нових коментаря',' нових коментарів'));},
	'deleting'=>'Ви дійсно хочете видалити коментар &quot;%s&quot;?',
	'filter'=>'Фільтр',
	'module'=>'Модуль',
	'date'=>'Дата',
	'author'=>'Автор',
	'published'=>'Опубліковано в',
	'text'=>'Текст',
	'cnf'=>'Коментарі не знайдені',
	'cpp'=>'Коментарів на сторінку: %s',
	'blocked'=>'Заблокувано',
	'status'=>'Статус',
);