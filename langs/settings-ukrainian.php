<?php
return array(
	#Для /core/others/settings/simple.php & /core/others/settings/full.php
	'setting_og'=>'Установка значень налаштувань групи',
	'reset_g_con'=>'Скидання налаштувань групи',
	's_phrase_len'=>'Довжина пошукової фрази повинна бути більше двох символів!',
	'ops_not_found'=>'По запиту &quot;%s&quot; налаштування не знайдені',
	'cnt_seaop'=>'Пошук налаштувань (знайдено %s)',
	'f_not_load'=>'Файл не завантажений',
	'import_result'=>function($gd,$od,$ag,$ug,$ao,$uo)
	{
		return rtrim(($gd>0 ? $gd.Ukrainian::Plural($gd,array(' групу',' групи',' груп')).' видалено, ' : '')
			.($od>0 ? $od.Ukrainian::Plural($od,array(' налаштування',' налаштування',' налаштувань')).' видалено, ' : '')
			.($ag>0 ? $ag.Ukrainian::Plural($ag,array(' групу',' групи',' груп')).' дадано, ' : '')
			.($ug>0 ? $ug.Ukrainian::Plural($ug,array(' групу',' групи',' груп')).' оновлено, ' : '')
			.($ao>0 ? $ao.Ukrainian::Plural($ao,array(' налаштування',' налаштування',' налаштувань')).' дадано, ' : '')
			.($uo>0 ? $uo.Ukrainian::Plural($uo,array(' налаштування',' налаштування',' налаштувань')).' оновлено' : ''),', ');
	},
	'error_in_code'=>'Помилка в коді',
	'op_errors'=>'Допущені помилки',
	'grlist'=>'Список груп',
	'nooptions'=>'Налаштування відсутні',
	'options'=>'Нашалтування',
	'ops_without_g'=>'Налаштування без груп',
	'import'=>'Імпорт налаштувань',
	'export'=>'Експорт налаштувань',
	'incorrect_s_file'=>'Невірна структура файлу! (%s)',
	'im_nogrname'=>'В однієї з груп відсутнє ім\'я!',
	'im_noopname'=>'В однієї з настройок відсутнє ім\'я!',

	#Для /core/others/settings/full.php
	'delc'=>'Підтвердження видалення',
	'empty_gt'=>function($l=''){ return'Назва групи не заповнена'.($l ? ' (для '.$l.')' : ''); },
	'adding_g'=>'Додавання групи налаштувань',
	'editing_g'=>'Редагування групи налаштувань',
	'adding_opt'=>'Додавання налаштування',
	'editing_opt'=>'Редагування налаштування',
	'empty_ot'=>function($l=''){ return'Не заповнена назва налаштування'.($l ? ' (для '.$l.')' : ''); },
);