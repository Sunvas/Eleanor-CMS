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
class AccountChangePass
{
	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'changepass'),true,''),
		);
	}

	public static function Content($master=true)
	{
		$errors=array();
		$success=false;
		$values=array(
			'old'=>isset($_POST['old']) ? (string)$_POST['old'] : false,
			'password'=>isset($_POST['password']) ? (string)$_POST['password'] : false,
			'password2'=>isset($_POST['password2']) ? (string)$_POST['password2'] : false,
		);
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$cach=$GLOBALS['Eleanor']->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
			$GLOBALS['Eleanor']->Captcha->Destroy();
			if(!$cach)
			{
				$errors[]='WRONG_CAPTCHA';
				break;
			}
			if($values['password']!=$values['password2'])
			{
				$errors[]='PASSWORD_MISMATCH';
				break;
			}
			if(!UserManager::MatchPass($values['old']))
			{
				$values['old']='';
				$errors[]='WRONG_OLD_PASSWORD';
			}

			if(!$errors)
			{
				try
				{
					UserManager::Update(array('_password'=>$values['password']));
				}
				catch(EE$E)
				{
					$mess=$E->getMessage();
					switch($mess)
					{
						case'PASS_TOO_SHORT':
							$errors['PASS_TOO_SHORT']=sprintf($lang['PASS_TOO_SHORT'],$E->extra['min'],$E->extra['you']);
						break;
						default:
							$errors[]=$mess;
					}
					break;
				}
				$values['old']=$values['password']=$values['password2']='';
				$success=true;
			}
		}
		$GLOBALS['title'][]=$lang['changing_pass'];
		return Eleanor::$Template->AcNewPass($success,$errors,$values);
	}
}