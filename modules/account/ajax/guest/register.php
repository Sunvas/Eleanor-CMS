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

class AccountRegister
{	public static function Handler()
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		Eleanor::LoadOptions(array('user-profile'),false);
		$event=isset($_POST['event']) ? (string)$_POST['event'] : '';
		switch($event)
		{
			case'email':
				$email=isset($_POST['email']) ? trim((string)$_POST['email']) : '';
				$can=false;
				if($email and Strings::CheckEmail($email))
				{
					$can=true;
					try
					{
						UserManager::IsEmailBlocked($email);
					}
					catch(EE$E)
					{
						$can=false;
					}
				}

				if(!$can)
					return Result($lang['error_email']);
				$R=Eleanor::$Db->Query('SELECT `email` FROM `'.P.'users_site` WHERE `email`='.Eleanor::$Db->Escape($email).' LIMIT 1');
				$can=$R->num_rows==0;
				Result($can ? false : $lang['email_in_use']);
			break;
			case'login':
				$name=isset($_POST['name']) ? trim((string)$_POST['name']) : '';
				$long=false;
				if(!$name or Eleanor::$vars['max_name_length'] and $long=mb_strlen($name)>(int)Eleanor::$vars['max_name_length'])
					return Result($long ? $lang['name_too_long'](Eleanor::$vars['max_name_length']) : $lang['error_name']);
				try
				{
					UserManager::IsNameBlocked($name);
				}
				catch(EE$E)
				{
					return Result($lang['error_name']);
				}

				$R=Eleanor::$Db->Query('SELECT `name` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($name).' LIMIT 1');
				$can=$R->num_rows==0;
				Result($can ? false : $lang['name_in_use']);
			break;
			default:
				Error(Eleanor::$Language['main']['unknown_event']);
		}
	}
}