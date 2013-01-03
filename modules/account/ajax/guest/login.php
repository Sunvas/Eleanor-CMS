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
class AccountLogin
{
	public static function Handler($master)
	{		$errors=array();
		$captcha=Eleanor::$vars['antibrute']==2 && (isset($_POST['check']) || ($ct=Eleanor::GetCookie('Captcha_'.get_class(Eleanor::$Login)) and $ct>time()));
		if($captcha)
		{			$pch=isset($_POST['check']);
			$GLOBALS['Eleanor']->Captcha->disabled=false;
			$cach=$GLOBALS['Eleanor']->Captcha->Check($pch ? (string)$_POST['check'] : '');
			$GLOBALS['Eleanor']->Captcha->Destroy();
			if(!$cach)
				return Error(array($pch ? 'WRONG_CAPTCHA' : 'ENTER_CAPTCHA'));
		}
		if(isset($_POST['login']))
			try
			{
				Eleanor::$Login->Login((array)$_POST['login'],array('captcha'=>$captcha));
				Result(true);
			}
			catch(EE$E)
			{
				$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
				$error=$E->getMessage();
				switch($error)
				{
					case'TEMPORARILY_BLOCKED':
						$errors['TEMPORARILY_BLOCKED']=sprintf($lang['TEMPORARILY_BLOCKED'],round($E->extra['remain']/60));
					break;
					case'CAPTCHA':
						$errors[]='ENTER_CAPTCHA';
					break;
					default:
						$errors[]=$error;
				}
				return Error($errors);
			}
		Error();
	}
}