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

class AccountExternals
{
	public static function Handler()
	{
		if(isset($_POST['provider'],$_POST['pid']))
		{
			Eleanor::$Db->Delete(P.'users_external_auth','`provider`='.Eleanor::$Db->Escape((string)$_POST['provider']).' AND `provider_uid`='.Eleanor::$Db->Escape((string)$_POST['pid']).' AND `id`='.(int)Eleanor::$Login->GetUserValue('id'));
			Result('');
		}
		else
			Error();
	}
}