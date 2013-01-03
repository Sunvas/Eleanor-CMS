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

final class Permissions extends BaseClass
{
	private
		$lc,#Login class

		$moderate,#Флаг модерации публикаций
		$is_admin,#Флаг админа
		$max_upload,#Максимальный размер загруженного файла
		$hcaptcha,#Флаг скрытия капчи
		$sh_cls,#Флага показа закрытого сайта
		$banned,#Флаг бана группы
		$fl,#Flood limit
		$sl;#Search limit

	/**
	 * Конструктор разрешений, для каждого логина определяются свои разрешения
	 *
	 * @param string $lc название класса логина, за которым закрплен текущий объект разрешений
	 */
	public function __construct($lo)
	{
		$this->lc=$lo;
	}

	/**
	 * Проверка разрешен ли пользователю вход в панель администратора
	 */
	public function IsAdmin()
	{
		if(!isset($this->is_admin))
		{
			$v=Eleanor::GetPermission('access_cp',$this->lc);
			$this->is_admin=in_array(1,$v);
		}
		return$this->is_admin;
	}

	/**
	 * Определение максимального размера загружаемого файла
	 *
	 * @return bool|int true - нет ограничений, false - нельзя загружать файлы, (int) - числов в байтах
	 */
	public function MaxUpload()
	{
		if(!isset($this->max_upload))
		{
			$v=Eleanor::GetPermission('max_upload',$this->lc);
			if(in_array(1,$v))
				return$this->max_upload=true;
			sort($v,SORT_NUMERIC);
			$bytes=(int)end($v);
			return$this->max_upload=$bytes<1 ? false : $bytes*1024;
		}
		return$this->max_upload;
	}

	/**
	 * Проверка забанен ли пользователь
	 */
	public function IsBanned()
	{
		if($this->IsAdmin())
			return false;
		if(!isset($this->banned))
		{
			$v=Eleanor::GetPermission('banned',$this->lc);
			$this->banned=in_array(1,$v);
		}
		return$this->banned;
	}

	/**
	 * Проверка возможности отключения капчи для пользователя
	 */
	public function HideCaptcha()
	{
		if(!isset($this->hcaptcha))
		{
			$v=Eleanor::GetPermission('captcha',$this->lc);
			$this->hcaptcha=in_array(0,$v);
		}
		return$this->hcaptcha;
	}

	/**
	 * Проверка наличия возпожности просматривать закрытый сайт
	 */
	public function ShowClosedSite()
	{
		if(!isset($this->sh_cls))
		{
			$v=Eleanor::GetPermission('sh_cls',$this->lc);
			$this->sh_cls=in_array(1,$v);
		}
		return$this->sh_cls;
	}

	/**
	 * Определение минимального промежутка времени в секундах между публикацией 2х материалов (новостей, комментариев и т.п.)
	 */
	public function FloodLimit()
	{
		if(!isset($this->fl))
		{
			$v=Eleanor::GetPermission('flood_limit',$this->lc);
			$this->fl=min($v);
		}
		return$this->fl;
	}


	/**
	 * Определение минимального промежутка времени в секундах между 2мя поисковыми запросами
	 */
	public function SearchLimit()
	{
		if(!isset($this->sl))
		{
			$v=Eleanor::GetPermission('search_limit',$this->lc);
			$this->sl=min($v);
		}
		return$this->sl;
	}

	/**
	 * Проверка наличия возможности публикации материалов без их премодерации
	 */
	public function Moderate()
	{
		if(!isset($this->moderate))
		{
			$v=Eleanor::GetPermission('moderate',$this->lc);
			$this->moderate=in_array(1,$v);
		}
		return$this->moderate;
	}
}