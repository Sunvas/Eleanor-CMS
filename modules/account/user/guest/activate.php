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
class AccountActivate
{
	public static function Content($master=true)
	{		if($master)
			return AccountIndex::Content($master);		ExitPage();
	}
}