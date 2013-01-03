<?php
return array(
	#Для /addons/admin/modules/modules.php
	'list'=>'Модулі',
	'delc'=>'Підтвердження видалення',
	'adding'=>'Додавання модуля',
	'editing'=>'Редагування модуля',
	'empty_title'=>function($l){ return'Назва модуля не може бути порожнею'.($l ? ' (для '.$l.')' : ''); },
	'sec_exists'=>function($s){ return'Модуль з '.Ukrainian::Plural(count($s),array('розділом','розділами','розділами')).' &quot;'.join('&quot;, &quot;',$s).'&quot; вже існує'; },
);