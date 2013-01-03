<?php
return array(
	#Для user/groups.php
	'groups'=>'Группы пользователей',

	#Для user/online.php
	'who_online'=>'Кто онлайн',

	#Для user/guest/index.php
	'cabinet'=>'Личный кабинет',

	#Для user/guest/lostpass.php
	'reminderpass'=>'Восстановление пароля',
	'wait_pass1'=>'Проверьте e-mail',
	'new_pass'=>'Новый пароль для %s',
	'successful'=>'Успех',

	#Для user/guest/login.php
	'TEMPORARILY_BLOCKED'=>'В связи с частым вводом неправильного пароля, аккаунт заблокирован!<br />Повторите попытку через %s  минут(ы).',

	#Для user/guest/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'Длина имени пользователя не должна превышать '.$l.Russian::Plural($l,array(' символ',' символа',' символов')).' символов. Вы ввели '.$e.Russian::Plural($e,array(' символ',' символа',' символов')).' символов.'; },
	'PASS_TOO_SHORT'=>function($l,$e){ return'Минимальная длина пароля '.$l.Russian::Plural($l,array(' символ',' символа',' символов')).' символов. Вы ввели только '.$e.Russian::Plural($e,array(' символ',' символа',' символов')).' символов.'; },
	'form_reg'=>'Форма регистрации',
	'reg_fin'=>'Регистрация завершена!',
	'wait_act'=>'Ожидание активации',

	#Для user/user/activate.php
	'reactivation'=>'Повторная активация',
	'activate'=>'Активация',

	#Для user/user/changeemail.php
	'changing_email'=>'Изменение e-mail адреса',

	#Для user/user/changepass.php
	'changing_pass'=>'Изменение пароля',

	#Для user/user/externals.php
	'externals'=>'Внешние сервисы',

	#Для user/user/settings.php
	'site'=>'Сайт',
	'site_'=>'Введите адрес сайта, начиная с http://',
	'lang'=>'Язык',
	'theme'=>'Тема оформления',
	'timezone'=>'Часовой пояс',
	'personal'=>'Личное',
	'siteopts'=>'Настройки сайта',
	'by_default'=>'По умолчанию',
	'full_name'=>'Полное имя',
	'editor'=>'Редактор',
	'staticip'=>'Статический IP',
	'staticip_'=>'При каждом входе на сайт, ваша сессия будет привязываться к IP.',
	'gender'=>'Пол',
	'male'=>'Мужчина',
	'female'=>'Женщина',
	'nogender'=>'Не скажу',
	'bio'=>'Биография',
	'interests'=>'Интересы',
	'location'=>'Откуда',
	'location_'=>'Местоположение: страна, город',
	'signature'=>'Подпись',
	'connect'=>'Связь',
	'vk'=>'ВКонтакте',
	'vk_'=>'Пожалуйста, введите только свой id, либо имя',
	'twitter_'=>'Пожалуйста, введите только свой ник',
	'settings'=>'Настройка профиля',
);