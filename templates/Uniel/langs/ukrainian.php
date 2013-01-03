<?php
return array(
	#Для index.php
	'loading'=>'Завантаження. Зачекайте будь ласка...',
	'to_top'=>'Вгору',
	'login'=>'Логін:',
	'pass'=>'Пароль:',
	'enter'=>'Увійти',
	'hello'=>'Ласкаво просимо, %s!',
	'adminka'=>'Адмін-панель',
	'exit'=>'Вихід',
	'register'=>'Реєстрація',
	'lostpass'=>'Забули пароль?',
	'msjump'=>'-Перейти-',

	#Для Confirm.php
	'no'=>'Ні',
	'yes'=>'Так',

	#Для Denied.php	'site_close_text'=>'Сайт тимчасово недоступний! Проводяться цікаві роботи',

	#Для EditDelete.php
	'delete'=>'Видалити',
	'edit'=>'Редагувати',

	#Для LangChecks.php
	'for_all_langs'=>'Для всіх мов',

	#Для Rating.php
	'average_mark'=>'Средня оцінка: %s; Проголосувало: %s',

	#Для Pages.php
	'pages'=>'Сторінки:',
	'goto_page'=>'Перейти на сторінку',

	#Для Message.php
	'warning'=>'Попередження',
	'error'=>'Помилка',
	'errorи'=>'Помилки',
	'info'=>'Інформація',

	#Для Captcha.php
	'captcha'=>'Нажміть, щоб показати інші цифри',

	#Для BlockWhoOnline.php
	'users'=>function($n){ return$n.Ukrainian::Plural($n,array(' користувач:',' користувача:',' користувачів:')); },
	'minutes_ago'=>function($n){ return$n.Ukrainian::Plural($n,array(' хвилину тому:',' хвилини тому',' хвилин тому')); },
	'bots'=>function($n){ return$n.Ukrainian::Plural($n,array(' пошуковий бот',' пошукових бота',' пошукових ботів')); },
	'guests'=>function($n){ return$n.Ukrainian::Plural($n,array(' гість',' гостя',' гостей')); },
	'alls'=>'Повний список',

	#Для BlockArchive.php
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

	#Для Editor.php
	'smiles'=>'Смайли',
);