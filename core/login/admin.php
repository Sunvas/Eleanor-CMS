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

if(!class_exists('LoginBase',false))
	include dirname(__file__).'/base.php';

class LoginAdmin extends LoginBase implements LoginClass
{	const
		MAX_SESSIONS=1,#Максимальное число сессий
		UNIQUE='admin';

	protected static
		$Instance;

	public function Login(array$data,array$extra=array())
	{
		if(!isset($data['name'],$data['password']))
			throw new EE('EMPTY_DATA',EE::UNIT);
		$this->AuthByName($data['name'],$data['password'],$extra);
		if(!$this->CheckPermission())
		{
			$this->Logout();
			throw new EE('ACCESS_DENIED',EE::UNIT);
		}

		#:-)
		if(extension_loaded('ionCube Loader'))
			new Settings;

		$data+=array('rememberme'=>true);
		Eleanor::SetCookie(self::UNIQUE,base64_encode((isset($this->user['login_key']) ? $this->user['login_key'] : '').'|'.$this->user['id']),$data['rememberme'] ? false : 0,true);
		$this->login=true;
	}

	public function IsUser($hard=false)
	{		if(isset($this->login) and !$hard)
			return$this->login;

		if(!$cookie=Eleanor::GetCookie(self::UNIQUE))
			return$this->login=false;

		list($k,$id)=explode('|',base64_decode($cookie),2);

		if(!$k or !$id or !$this->AuthByKey($id,$k))
			return$this->login=false;

		if(!$this->CheckPermission())
		{			$this->Logout();
			return$this->login=false;
		}
		return$this->login=true;
	}

	public function UserLink($username,$uid=0)
	{
		if(!$this->IsUser())
			return false;
		return$uid ? Eleanor::$services['admin']['file'].'?'.Eleanor::getInstance()->Url->Construct(array('section'=>'management','module'=>'users','edit'=>$uid),false,false,false) : '';
	}

	public function ApplyCheck(){}

	private function CheckPermission()
	{
		return in_array(1,Eleanor::GetPermission('access_cp',$this));
	}
}