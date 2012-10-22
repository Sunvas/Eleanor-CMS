<?php
return array(
	#Для index.php
	'loading'=>'Загрузка. Пожалуйста, подождите...',
	'to_top'=>'Вверх',
	'login'=>'Логин:',
	'pass'=>'Пароль:',
	'enter'=>'Войти',
	'hello'=>'Добро пожаловать, %s!',
	'adminka'=>'Админ-панель',
	'exit'=>'Выход',
	'register'=>'Регистрация',
	'lostpass'=>'Забыли пароль?',
	'msjump'=>'-Перейти-',

	#Для Confirm
	'no'=>'Нет',
	'yes'=>'Да',

	#Для Classes/Comments.php :: CommentsEdit
	'cancel'=>'Отмена',

	#Для Denied
	'site_close_text'=>'Сайт временно недоступен! Ведутся интересные работы',

	#Для EditDelete
	'delete'=>'Удалить',
	'edit'=>'Править',

	#Для LangChecks
	'for_all_langs'=>'Для всех языков',

	#Для Rating
	'average_mark'=>'Средняя оценка: %s; Проголосовало: %s',

	#Для Pages
	'pages'=>'Страницы:',
	'goto_page'=>'Перейти на страницу',

	#Для Message
	'warning'=>'Предупреждение',
	'error'=>'Ошибка',
	'errors'=>'Ошибки',
	'info'=>'Информация',

	#Для Captcha
	'captcha'=>'Кликните, чтобы показать другие цифры',

	#Для BlockWhoOnline
	'users'=>function($n){ return$n.Russian::Plural($n,array(' пользователь:',' пользователя:',' пользователей:')); },
	'minutes_ago'=>function($n){ return$n.Russian::Plural($n,array(' минуту назад',' минуты назад',' минут назад')); },
	'bots'=>function($n){ return$n.Russian::Plural($n,array(' поисковый бот:',' поисковых бота:',' поисковых ботов:')); },
	'guests'=>function($n){ return$n.Russian::Plural($n,array(' гость',' гостя',' гостей')); },
	'alls'=>'Полный список',

	#Для BlockArchive
	'year-'=>'Год назад',
	'year+'=>'Год вперед',
	'mon'=>'Пн',
	'tue'=>'Вт',
	'wed'=>'Ср',
	'thu'=>'Чт',
	'fri'=>'Пт',
	'sat'=>'Сб',
	'sun'=>'Вс',
	'_cnt'=>function($n){return$n.Russian::Plural($n,array(' новость',' новости',' новостей'));},
	'total'=>function($n){return'Всего - '.$n.Russian::Plural($n,array(' новость',' новости',' новостей'));},
	'no_per'=>'Новостей за этот период нет',
);