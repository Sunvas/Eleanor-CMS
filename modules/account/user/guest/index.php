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
class AccountIndex
{
	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Prefix(),
		);
	}

	public static function Content($master=true,$errors=array())
	{
		$back=$master && isset($_POST['back']) ? (string)$_POST['back'] : false;
		if(!$back and isset($_GET['back']))
			$back=(string)$_GET['back'];
		if(!$back)
			$back=getenv('HTTP_REFERER');

		$values=array(
			'name'=>$master && isset($_POST['login']['name']) ? (string)$_POST['login']['name'] : '',
			'password'=>$master && isset($_POST['login']['password']) && !in_array('WRONG_PASSWORD',$errors) ? (string)$_POST['login']['password'] : '',
		);
		$captcha=in_array('CAPTCHA',$errors) ? true : (Eleanor::$vars['antibrute']==2 and $ct=Eleanor::GetCookie('Captcha_'.get_class(Eleanor::$Login)) and $ct>time());

		if($master)
			$GLOBALS['title'][]=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['cabinet'];

		if($captcha)
		{
			$GLOBALS['Eleanor']->Captcha->disabled=false;
			$captcha=$GLOBALS['Eleanor']->Captcha->GetCode();
		}
		$links=array(
			'login'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'login'),true,''),
		);
		return Eleanor::$Template->AcLogin($values,$back,$errors,$captcha,$links);
	}
}