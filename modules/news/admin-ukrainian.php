<?php
return array(
	#Для admin/index.php
	'forallt'=>'-для всіх-',
	'list'=>'Новини',
	'tags_list'=>'Теги',
	'language'=>'Мова',
	'tname'=>'Ім\'я тега',
	'delc'=>'Підтвердження видалення',
	'addingt'=>'Додавання тегу',
	'editingt'=>'Редагування тега',
	'empty_tag'=>'Назва тега не може бути порожнім!',
	'adding'=>'Додавання новини',
	'editing'=>'Редагування новини',
	'EMPTY_TITLE'=>function($l){return'Заголовок не може бути порожнім'.($l ? ' (для '.$l.')' : '');},
	'EMPTY_TEXT'=>function($l){return'Текст не може бути порожнім'.($l ? ' (для '.$l.')' : '');},
);