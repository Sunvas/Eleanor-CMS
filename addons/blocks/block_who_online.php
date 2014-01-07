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
$suser=Eleanor::$service=='user';
if($suser)
{
	$users=$bots=array();
	$g=$u=$b=0;
	$limit=30;
	$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`user_id`,`s`.`enter`,`s`.`name` `botname`,`us`.`groups`,`us`.`name` FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id` WHERE `s`.`expire`>\''.date('Y-m-d H:i:s').'\' AND `s`.`service`=\''.Eleanor::$service.'\' ORDER BY `s`.`expire` DESC LIMIT '.$limit);
	while($a=$R->fetch_assoc())
	{
		$limit--;

		if($a['user_id']>0 and $a['type']=='user')
		{
			if($a['groups'])
			{
				$gs=array((int)ltrim($a['groups'],','));
				$p=join(Eleanor::Permissions($gs,'html_pref'));
				$e=join(Eleanor::Permissions($gs,'html_end'));
			}
			else
				$e=$p='';

			$users[$a['user_id']]=array(
				'p'=>$p,
				'e'=>$e,
				'n'=>$a['name'],
				't'=>$a['enter'],
			);
			$u++;
		}
		elseif($a['botname'] and Eleanor::$vars['bots_enable'])
		{
			if(isset($bots[ $a['botname'] ]))
				$bots[ $a['botname'] ]['cnt']++;
			else
				$bots[ $a['botname'] ]=array(
					'cnt'=>1,
					't'=>$a['enter'],
				);
			$b++;
		}
		else
			$g++;
	}

	if($limit<=0)
	{
		$R=Eleanor::$Db->Query('SELECT `type`, COUNT(`type`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''.date('Y-m-d H:i:s').'\' AND `service`=\''.Eleanor::$service.'\' GROUP BY `type`');
		while($a=$R->fetch_row())
			$ucnt[$a[0]]=$a[1];
		if(isset($ucnt['guest']))
			$g=$ucnt['guest'];
		if(isset($ucnt['user']))
			$u=$ucnt['user'];
		if(isset($ucnt['bot']))
			$b=$ucnt['bot'];
	}
}

try
{
	if($suser)
		return (string)Eleanor::$Template->BlockWhoOnline($users,$bots,$u,$b,$g);
	return (string)Eleanor::$Template->BlockWhoOnline();
}
catch(EE$E)
{
	return'Template BlockWhoOnline does not exists.';
}