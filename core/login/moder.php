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
class LoginModer extends BaseClass implements LoginClass
{	protected static
		$Instance;

	public static function getInstance()
	{		if(!isset(self::$Instance))
			self::$Instance=new self;
		return self::$Instance;	}

	public function __get($n)
	{		if($n=='Permissions')
		{			if(!class_exists($n,false))
				include Eleanor::$root.'core/permissions.php';
			return $this->$n=new Permissions($this);		}
		return parent::__get($n);
	}

	protected function __construct(){}

	public function Login(array $b)
	{		return false;
	}

	public function IsUser($a=false)
	{
		return false;
	}

	public function Auth($id){}

	public function ApplyCheck()
	{
		return false;
	}

	public function Logout()
	{		return false;
	}

	public function UserLink($a,$b=0)
	{		return false;
	}

	public function GetUserValue($a,$b=true)
	{		return false;
	}
}