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
	public static function Handler()
	{
		$event=isset($_POST['event']) ? (string)$_POST['event'] : '';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		switch($event)
		{
			case'killsession':
				$key=isset($_POST['key']) ? (string)$_POST['key'] : '';
				$uid=(int)Eleanor::$Login->GetUserValue('id');
				$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$uid.' LIMIT 1');
				if($a=$R->fetch_assoc())
				{
					$cl=get_class(Eleanor::$Login);
					$lks=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
					unset($lks[$cl][$key]);
					if(empty($lks[$cl]))
						unset($lks[$cl]);
					Eleanor::$Db->Update(P.'users_site',array('login_keys'=>$lks ? serialize($lks) : ''),'`id`='.$uid.' LIMIT 1');
				}
				Result(true);
			break;
			default:
				Error(Eleanor::$Language['main']['unknown_event']);
		}
	}
}