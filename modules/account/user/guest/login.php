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
	public static function Menu()
	{		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Prefix(),
		);
	}

	public static function Content($master)
	{		if($master)
		{			$errors=array();
			$captcha=Eleanor::$vars['antibrute']==2 && (isset($_POST['check']) || ($ct=Eleanor::GetCookie('Captcha_'.get_class(Eleanor::$Login)) and $ct>time()));
			if($captcha and $_SERVER['REQUEST_METHOD']=='POST')
			{				$pch=isset($_POST['check']);
				$GLOBALS['Eleanor']->Captcha->disabled=false;
				$cach=$GLOBALS['Eleanor']->Captcha->Check($pch ? (string)$_POST['check'] : '');
				$GLOBALS['Eleanor']->Captcha->Destroy();
				if(!$cach)
					return class_exists('AccountIndex',false) ? AccountIndex::Content(true,array($pch ? 'WRONG_CAPTCHA' : 'ENTER_CAPTCHA')) : null;
			}
			if(isset($_POST['login']))
				try
				{
					Eleanor::$Login->Login((array)$_POST['login'],array('captcha'=>$captcha));
					$back=isset($_POST['back']) ? $_POST['back'] : false;
					if(Eleanor::$Login->GetUserValue('groups')==array(GROUP_WAIT))
						$back=false;
					return GoAway($back);
				}
				catch(EE$E)
				{
					if(class_exists('AccountIndex',false))
					{						$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
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
						return AccountIndex::Content(true,$errors);
					}
					throw new EE($E->getMessage(),EE::ENV,array(),$E);
				}
		}
		return class_exists('AccountIndex',false) ? AccountIndex::Content(true) : null;
	}
}