<?php
return array(
	'no'=>'ні',
	'yes'=>'так',
	'all'=>'всі',
	'ok'=>'ОК',
	'update'=>'Оновити',
	'delete'=>'Видалити',
	'edit'=>'Редагувати',
	'category'=>'Категорія',
	'loading'=>'Завантаження. Зачекайте будь ласка...',
	'to_top'=>'Вгору',
	'login'=>'Логін:',
	'pass'=>'Пароль:',
	'enter'=>'Увійти',
	'search'=>'Пошук',
	'cancel'=>'Скасувати',
	'tags'=>'Теги',
	'site_close_text'=>'Сайт тимчасово недоступний! Проводяться цікаві роботи',
	'hello'=>'Ласкаво просимо, ',
	'adminka'=>'Адмін-панель',
	'exit'=>'Вихід',
	'register'=>'Реєстрація',
	'lostpass'=>'Забули пароль?',
	'users'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' користувач:',' користувача:',' користувачів:'));
	},
	'minutes_ago'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' хвилину тому:',' хвилини тому',' хвилин тому'));
	},
	'bots'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' пошуковий бот',' пошукових бота',' пошукових ботів'));
	},
	'guests'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' гість',' гостя',' гостей'));
	},
	'alls'=>'Повний список',
	'back'=>'Назад',
	'captcha'=>'Нажміть, щоб показати інші цифри',
	'warning'=>'Попередження',
	'error'=>'Помилка',
	'errorи'=>'Помилки',
	'info'=>'Інформація!',
	'pages'=>'Сторінки:',
	'goto_page'=>'Перейти на сторінку',
	'average_mark'=>'Средня оцінка: %s; Проголосувало: %s',
	'for_all_langs'=>'Для всіх мов',
	'msjump'=>'-Перейти-',
);