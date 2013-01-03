<?php
return array(
	#Для /addons/admin/modules/modules.php
	'list'=>'Модули',
	'delc'=>'Подтверждение удаления',
	'adding'=>'Добавление модуля',
	'editing'=>'Редактирование модуля',
	'empty_title'=>function($l){ return'Название модуля не может быть пустым'.($l ? ' (для '.$l.')' : ''); },
	'sec_exists'=>function($s){ return'Модуль с '.Russian::Plural(count($s),array('разделом','разделами','разделами')).' &quot;'.join('&quot;, &quot;',$s).'&quot; уже существует'; },
);