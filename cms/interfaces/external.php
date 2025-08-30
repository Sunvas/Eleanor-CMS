<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Внешняя авторизация пользователей */
interface External
{
	/** Функция должна вернуть ID пользователей из системной таблицы users, авторизованность которых подтверждена альтернативным способом.
	 * Эта функция должна при необходимости вставлять пользователей в системную таблицу users */
	function Get():array;

	/** Выход пользователя под своей учётной записью
	 * @param int $id ID пользователя из системной таблицы users
	 * @param bool $temp Флаг временной аутентификации (не стоит галочка "запомнить меня") */
	function SignIn(int$id,bool$temp=false):void;

	/** Выход пользователя из учетной записи
	 * @param int|array $ids один или несколько ID пользователей, которые вышли */
	function SignOut(int|array$ids):void;
}

return External::class;