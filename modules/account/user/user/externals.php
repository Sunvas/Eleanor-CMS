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
	#Внимание! Для достижения наибольше безопасности, посетите http://loginza.ru/ и преобретите свои ID и SECRET
	const
		ID=0,
		SECRET='';

	public static function Menu()
	{
		return array(
			'externals'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'externals'),true,''),
		);
	}

	public static function Content($master=true)
	{
		$uid=(int)Eleanor::$Login->GetUserValue('id');
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$added=$error=false;
		if(isset($_POST['token']))
		{
			$token=(string)$_POST['token'];
			$cu=curl_init('http://loginza.ru/api/authinfo?token='.$token.(self::ID ? '&id='.self::ID.'&sig='.md5($token.self::SECRET) : ''));
			curl_setopt_array($cu,array(
				CURLOPT_RETURNTRANSFER=>1,
				CURLOPT_TIMEOUT=>15,
				CURLOPT_HEADER=>false,
			));
			$r=curl_exec($cu);
			curl_close($cu);
			$r=json_decode($r,true);
			if(CHARSET!='utf-8')
				array_walk_recursive($r,function(&$v){
					$v=mb_convert_encoding($v,CHARSET,'utf-8');
				});
			if(!$r or isset($r['error_type']))
				$error=$r;
			else
			{
				$r['provider']=trim(strchr($r['provider'],'/'),'/');
				if(!isset($r['uid']))
					$r['uid']=trim(strchr($r['identity'],'/'),'/');
				$added=$r;
				Eleanor::$Db->Delete(P.'users_external_auth','`provider`='.Eleanor::$Db->Escape($r['provider']).' AND `provider_uid`='.Eleanor::$Db->Escape($r['uid']));
				Eleanor::$Db->Insert(P.'users_external_auth',array('provider'=>$r['provider'],'provider_uid'=>$r['uid'],'id'=>$uid,'identity'=>$r['identity']));
			}
		}

		$items=array();
		$R=Eleanor::$Db->Query('SELECT `provider`,`provider_uid`,`identity` FROM `'.P.'users_external_auth` WHERE `id`='.$uid);
		while($a=$R->fetch_assoc())
			$items[]=$a;

		$links=array(
			'return'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'loginza'),true,''),
		);
		$GLOBALS['title'][]=$lang['externals'];
		return Eleanor::$Template->Loginza($items,$added,$error,$links);
	}
}