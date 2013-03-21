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

	public static function Content($master)
	{
		if($master)
			$GLOBALS['title'][]=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['cabinet'];

		$sessions=array();
		$uid=(int)Eleanor::$Login->GetUserValue('id');
		$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$uid.' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			$cl=get_class(Eleanor::$Login);
			$lks=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
			if(isset($lks[$cl]))
				$sessions=$lks[$cl];
			$lk=Eleanor::$Login->GetUserValue('login_key');
			foreach($sessions as $k=>&$v)
				$v['_candel']=$k!=$lk;
		}

		return Eleanor::$Template->AcMain($sessions);
	}
}