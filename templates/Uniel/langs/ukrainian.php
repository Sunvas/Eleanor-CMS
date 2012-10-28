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

	#Для Confirm
	'no'=>'Ні',
	'yes'=>'Так',

	#Для Denied	'site_close_text'=>'Сайт тимчасово недоступний! Проводяться цікаві роботи',

	#Для EditDelete
	'delete'=>'Видалити',
	'edit'=>'Редагувати',

	#Для LangChecks
	'for_all_langs'=>'Для всіх мов',

	#Для Rating
	'average_mark'=>'Средня оцінка: %s; Проголосувало: %s',

	#Для Pages
	'pages'=>'Сторінки:',
	'goto_page'=>'Перейти на сторінку',

	#Для Message
	'warning'=>'Попередження',
	'error'=>'Помилка',
	'errorи'=>'Помилки',
	'info'=>'Інформація',

	#Для Captcha
	'captcha'=>'Нажміть, щоб показати інші цифри',

	#Для BlockWhoOnline
	'users'=>function($n){ return$n.Ukrainian::Plural($n,array(' користувач:',' користувача:',' користувачів:')); },
	'minutes_ago'=>function($n){ return$n.Ukrainian::Plural($n,array(' хвилину тому:',' хвилини тому',' хвилин тому')); },
	'bots'=>function($n){ return$n.Ukrainian::Plural($n,array(' пошуковий бот',' пошукових бота',' пошукових ботів')); },
	'guests'=>function($n){ return$n.Ukrainian::Plural($n,array(' гість',' гостя',' гостей')); },
	'alls'=>'Повний список',

	#Для BlockArchive
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
);