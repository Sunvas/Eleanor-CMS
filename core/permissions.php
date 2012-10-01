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
		$lo,#Login object

		$moderate,
		$is_admin,
		$max_upload,
		$hcaptcha,
		$sh_cls,
		$banned,
		$fl,#Flood limit
		$sl;#Search limit

	public function __construct($lo)
	{
		$this->lo=$lo;
	}

	public function IsAdmin()
	{
		if(!isset($this->is_admin))
		{
			$v=Eleanor::GetPermission('access_cp',$this->lo);
			$this->is_admin=in_array(1,$v);
		}
		return$this->is_admin;
	}

	/*
		Функция используется для определения максимального размера загружаемого файла. Возвращает:
		true - нет ограничений
		false - нельзя загружать файлы
		(int) - числов в байтах
	*/
	public function MaxUpload()
	{
		if(!isset($this->max_upload))
		{
			$v=Eleanor::GetPermission('max_upload',$this->lo);
			if(in_array(1,$v))
				return$this->max_upload=true;
			sort($v,SORT_NUMERIC);
			$bytes=(int)end($v);
			return$this->max_upload=$bytes<1 ? false : $bytes*1024;
		}
		return$this->max_upload;
	}

	public function IsBanned()
	{
		if($this->IsAdmin())
			return false;
		if(!isset($this->banned))
		{
			$v=Eleanor::GetPermission('banned',$this->lo);
			$this->banned=in_array(1,$v);
		}
		return$this->banned;
	}

	public function HideCaptcha()
	{
		if(!isset($this->hcaptcha))
		{
			$v=Eleanor::GetPermission('captcha',$this->lo);
			$this->hcaptcha=in_array(0,$v);
		}
		return$this->hcaptcha;
	}

	public function ShowClosedSite()
	{
		if(!isset($this->sh_cls))
		{
			$v=Eleanor::GetPermission('sh_cls',$this->lo);
			$this->sh_cls=in_array(1,$v);
		}
		return$this->sh_cls;
	}

	public function FloodLimit()
	{
		if(!isset($this->fl))
		{
			$v=Eleanor::GetPermission('flood_limit',$this->lo);
			$this->fl=min($v);
		}
		return$this->fl;
	}

	public function SearchLimit()
	{
		if(!isset($this->sl))
		{
			$v=Eleanor::GetPermission('search_limit',$this->lo);
			$this->sl=min($v);
		}
		return$this->sl;
	}

	public function Moderate()
	{
		if(!isset($this->moderate))
		{
			$v=Eleanor::GetPermission('moderate',$this->lo);
			$this->moderate=in_array(1,$v);
		}
		return$this->moderate;
	}
}