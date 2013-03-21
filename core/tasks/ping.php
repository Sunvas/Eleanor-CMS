<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class TaskPing extends BaseClass implements Task
{
	public function Run($d)
	{
		Eleanor::LoadOptions('site');
		return Ping::Proccess();
	}

	public function GetNextRunInfo()
	{
		return'';
	}
}