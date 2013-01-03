<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class TplUserAccount
{	/*
		Шаблон страницы вывода всех групп пользователей
		$groups - массив всех групп. Формат: id=>array(), ключи внутреннего массива:
			title - название группы
			descr - описание группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
	*/
	public static function AcGroups($groups)
	{

	}

	/*
		Шаблон страницы пользователей онлайн
		$items - массив сессий пользователей онлай
			type - тип пользовательской сессии: guest - гостя, user - пользователя, bot - поискового бота
			user_id - идентификатор пользователя для пользовательской сессии
			enter - время входа
			ip_guest - IP гостя для гостевой сессии
			ip_user - IP пользователя для пользовательской сессии
			browser - USER AGENT устройства пользователя
			location - местоположение пользователя
			botname - имя бота для сессии поискового бота
			_group - группа пользователя для пользовательской сессии
			name - имя пользователя для пользовательской сессии
			full_name - полное имя пользователя для пользовательской сессии
		$groups - массив всех групп. Формат: id=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
		$cnt - количество сессий всего
		$pp - сессий на страницу
		$page - номер текущей страницы
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function AcUsersOnline($items,$groups,$cnt,$pp,$page,$links)
	{

	}

	/*
		Шаблон основной страница аккаунта.
		Пустое место зарезервирована под нужды проектов, реализуемых на системе
		$sessions - открытые сессии пользователя, формат: ключ=>массив, ключи внутреннего массива:
			0 - TIMESTAMP истечения активности
			1 - IP адрес
			2 - USER AGENT браузера
			_candel - флаг возможности удаления сессии
	*/
	public static function AcMain($sessions)
	{

	}

	/*
		Шаблон страницы формы входа пользователя
		$values - массив значений полей:
			name - имя пользователя
			password - пароль пользователя
		$back - URL возврата
		$errors - массив ошибок
		$captcha - капча при входе
		$links - массив ссылок, ключи:
			login - ссылка на скрипт, обрабатывающий данные из формы входа
	*/
	public static function AcLogin($values,$back,$errors,$captcha,$links)
	{

	}

	/*
		Шаблон страница регистрации пользователя
		$values - массив значений полей, ключи:
			_external - массив, доступен только при регистрации с использованием внешнего сервиса, ключи:
				nickname - имя пользователя из внешего сервиса
			name - имя пользователя
			full_name - полное имя
			email - e-mail пользователя
			password - пароль
			password - повторение пароля
		$captcha - капча
		$errors - массив ошибок. Формат int=>code, либо code=>error, где int - целое число не имеющее никакого отношение к ошибке, возможные code:
			PASSWORD_MISMATCH - пароли не совпадают
			PASS_TOO_SHORT - пароль слишком короткий
			EMPTY_EMAIL - пусто e-mail адрес
			EMAIL_EXISTS - e-mail уже занят другим пользователем
			EMAIL_BLOCKED - e-mail заблокирован
			NAME_TOO_LONG - имя слишком длинное
			EMPTY_NAME - пустое имя
			NAME_EXISTS - имя уже занято другим пользователем
			NAME_BLOCKED - имя заблокировано
			WRONG_CAPTCHA - неправильный защитный код
	*/
	public static function AcRegister($values,$captcha,$errors)
	{

	}

	/*
		Шаблон страницы успешного завершения регистрации
	*/
	public static function AcSuccessReg()
	{

	}

	/*
		Шаблон страницы завершения регистрации: ожидание активации учетной записи.
		$byadmin - флаг активации учетной записи администратором
	*/
	public static function AcWaitActivate($byadmin)
	{

	}

	/*
		Шаблон страницы первого шага восстановления пароля: форма
		$values - массив значений полей, ключи:
			name - имя пользователя
			email - e-mail пользователя
		$captcha - капча, либо false
		$errors - массив ошибок
	*/
	public static function AcRemindPass($values,$captcha,$errors)
	{

	}

	/*
		Страница второго шага восстановления пароля: для проложения необходимо перейти по ссылке, отправленной на мыло
	*/
	public static function AcRemindPassStep2()
	{

	}

	/*
		Шаблон страницы третьего (опциального) шага: ввода нового пароля после того, как пользователь перешел по ссылке с письма
		$values - массив значений полей, ключи:
			password - пароль
			password2 - повторение пароля
		$captcha - капча
		$errors - массив ошибок. Формат int=>code, либо code=>error, где int - целое число не имеющее никакого отношение к ошибке, возможные code:
			PASSWORD_MISMATCH - пароли не совпадают
			PASS_TOO_SHORT - пароль слишком короткий
			WRONG_CAPTCHA - неправильный защитный код
	*/
	public static function AcRemindPassStep3($values,$captcha,$errors=array())
	{

	}

	/*
		Шаблон страницы четвертого (завершающего) шага смены пароля пользователю: вывод информации об успешной операции
		$passsent - флаг когда новый пароля выслан на e-mail и для получения этого пароля, необходио проверить e-mail
		$user - массив данных пользователя, ключи:
			name - имя пользователя (не безопасный HTML)
			full_name - полное имя пользователя
			email - e-mail пользователя
	*/
	public static function AcRemindPassSent($passsent,$user)
	{

	}

	/*
		Шаблон страницы с результатом активации учетной записи
		$success - флаг успешной активации
	*/
	public static function AcActivate($success)
	{

	}

	/*
		Шаблон страницы с формой повторной актиации
		$sent - флаг успешной повторной активации
		$captcha - капча
		$errors - массив ошибок
		$hours - при включенном флаге $sent опредяет количество часов, оставшихся для активации
	*/
	public static function AcReactivation($sent,$captcha,$errors,$hours)
	{

	}

	/*
		Шаблон страницы изменения электронной почты
		$values - масив значений полей, ключи:
			email - электронная почта
		$captcha - капча
		$errors - массив ошибок
	*/
	public static function AcEmailChange($values,$captcha,$errors)
	{

	}

	/*
		Шаблон страницы шага 1 и 2 изменения электронной почты.
		Первый шаг - ожидание перехода по ссылке, отправленной на старый e-mail.
		Второй шаг - ожидание перехода по ссылке, отправленной на новый (введенный) e-mail.
		$step - идентификатор шага: 1 или 2.
	*/
	public static function AcEmailChangeSteps12($step)
	{

	}

	/*
		Шаблон страницы успешного завершения изменения e-mail адреса
	*/
	public static function AcEmailChangeSuccess()
	{

	}

	/*
		Шаблон страницы изменения пароля
	*/
	public static function AcNewPass($success,$errors,$values)
	{

	}

	/*
		Шаблон, формирующий шапку меню. Меню необходимо брать из статического метода Menu() классов, которые находятся в массиве $GLOBALS['Eleanor']->module['handlers'],
		ключи которого являются названиями обработчиков, а значения - именами классов, которые реализуют данные обработчики.
		$section - секция модуля. Это может быть user или guest
		$ih - индекс обработчика активного пункта меню
		$im - индекс пункта меню
	*/
	protected static function Menu($section='',$ih='',$im='')
	{

	}

	public static function AcOptions($controls,$values,$avatar,$errors,$saved)
	{

	}

	/*
		Страница просмотра пользователя. Данные пользователя нужно брать из массива $GLOBALS['Eleanor']->module['user'], ключи:
			id - идентификатор пользователя
			full_name - полное имя пользователя
			name - логин пользователя (не безопасный HTML)
			register - дата регистрации
			last_visit - дата последнего визита
			language - язык пользовател
			timezone - часовой пояс
			+все поля из таблицы users_extra
		$groups - группы пользователя, формат: id=>array(), ключи внутреннего массива:
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
			title - название группы
			_a - ссылка на просмотр информации о группе
			_main - флаг основной группы
	*/
	public static function AcUserInfo($groups)
	{

	}

	/*
		Элемент шаблона: загрузка галерей
		$galleries - массив галерей, каждый элемент массива - массив с ключами:
			n - имя галереи
			i - путь к картинке относительно корня сайта
			d - описание галереи
	*/
	public static function Galleries($galleries)
	{

	}

	/*
		Элемен шаблона: загрузка аватаров
		$avatar - массив аватаров, каждый элемент массива - массив с ключами:
			p - путь к файлу, относительно корня сайта, с закрывающим слешем
			f - имя файла
	*/
	public static function Avatars($avatars)
	{

	}

	#Loginza
	/*
		Страница просмотра внешних авторизаций, при интеграции с сервисом loginza.ru
		$items - массив всех внешних авторизаций, каждый элемент - массив с ключами:
			identity - ссылка на пользователя внешнего сервиса
			provider - название провайдера внешней авторизации
		$added - данные добавленной внешней авторизации, массив с ключами:
			identity - ссылка на пользователя внешнего сервиса
			provider - название провайдера внешней авторизации
		$error - ошибка, если пустая - значит ошибок нет
	*/
	public static function Loginza($items,$added,$error)
	{

	}

	/*
		Ошибка аутентификации при помощи сервиса loginza.
		$loginza - данные, полученные с сервиса
	*/
	public static function LoginzaError($loginza)
	{

	}
}