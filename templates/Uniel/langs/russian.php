<?php
return array(
	'no'=>'нет',
	'yes'=>'да',
	'all'=>'все',
	'ok'=>'ОК',
	'update'=>'Обновить',
	'delete'=>'Удалить',
	'edit'=>'Править',
	'category'=>'Категория',
	'loading'=>'Загрузка. Пожалуйста, подождите...',
	'to_top'=>'Вверх',
	'login'=>'Логин:',
	'pass'=>'Пароль:',
	'enter'=>'Войти',
	'search'=>'Поиск',
	'cancel'=>'Отмена',
	'tags'=>'Теги',
	'site_close_text'=>'Сайт временно недоступен! Ведутся интересные работы',
	'hello'=>'Добро пожаловать, ',
	'adminka'=>'Админ-панель',
	'exit'=>'Выход',
	'register'=>'Регистрация',
	'lostpass'=>'Забыли пароль?',
	'users'=>function($n)
	{
		return$n.Russian::Plural($n,array(' пользователь:',' пользователя:',' пользователей:'));
	},
	'minutes_ago'=>function($n)
	{
		return$n.Russian::Plural($n,array(' минуту назад',' минуты назад',' минут назад'));
	},
	'bots'=>function($n)
	{
		return$n.Russian::Plural($n,array(' поисковый бот:',' поисковых бота:',' поисковых ботов:'));
	},
	'guests'=>function($n)
	{
		return$n.Russian::Plural($n,array(' гость',' гостя',' гостей'));
	},
	'alls'=>'Полный список',
	'back'=>'Вернуться',
	'captcha'=>'Кликните, чтобы показать другие цифры',
	'warning'=>'Предупреждение',
	'error'=>'Ошибка',
	'errors'=>'Ошибки',
	'info'=>'Информация!',
	'pages'=>'Страницы:',
	'goto_page'=>'Перейти на страницу',
	'average_mark'=>'Средняя оценка: %s; Проголосовало: %s',
	'for_all_langs'=>'Для всех языков',
	'msjump'=>'-Перейти-',
);