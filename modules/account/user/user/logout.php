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
class AccountLogOut
{
	public static function Content($master)
	{
		if($master)
		{
			Eleanor::$Login->Logout();
			GoAway(isset($_GET['return']) ? (string)$_GET['return'] : false);
		}
	}
}