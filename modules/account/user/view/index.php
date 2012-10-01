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
{	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Prefix(),
		);
	}

	public static function Content()
	{
		Eleanor::LoadOptions('user-profile',false);
		$groups=$GLOBALS['Eleanor']->module['user']['groups'] ? explode(',,',trim($GLOBALS['Eleanor']->module['user']['groups'],',')) : array();
		if($groups)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
			$main=reset($groups);
			$tosort=$groups=$grs=array();
			while($a=$R->fetch_assoc())
			{
				$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
				$a['_a']=$GLOBALS['Eleanor']->Url->Construct(array('module'=>$GLOBALS['Eleanor']->module['sections']['groups']),false).'#group-'.$a['id'];
				$a['_main']=$main==$a['id'];
				$grs[$a['id']]=array_slice($a,1);
				$tosort[$a['id']]=$a['title'];
			}
			asort($tosort,SORT_STRING);
			foreach($tosort as $k=>&$v)
				$groups[$k]=$grs[$k];
		}

		class_exists('OwnBB');
		include_once Eleanor::$root.'core/ownbb/url.php';
		$user=&$GLOBALS['Eleanor']->module['user'];
		if($user['signature'])
			$user['signature']=OwnBB::Parse($user['signature']);
		if($user['site'])
			$user['site']=OwnBbCode_url::PreDisplay('',false,$user['site'],true);
		if($user['vk'])
			$user['vk']=OwnBbCode_url::PreDisplay('',false,'http://vk.com/'.$user['vk'],true);
		if($user['twitter'])
			$user['twitter']=OwnBbCode_url::PreDisplay('',false,'http://twitter.com/'.$user['twitter'],true);

		$GLOBALS['title'][]=$GLOBALS['Eleanor']->module['user']['full_name'];
		return Eleanor::$Template->AcUserInfo($groups);
	}
}