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

class TaskMainClean extends BaseClass implements Task
{
	public function Run($data)
	{
		$d=date('Y-m-d H:i:s');
		Eleanor::$Db->Delete(P.'timecheck','`timegone`=1 AND `date`<\''.$d.'\'');
		Eleanor::$Db->Delete(P.'sessions','`expire`<\''.$d.'\'');
		Eleanor::$Db->Delete(P.'confirmation','`expire`<\''.$d.'\'');
		Eleanor::$Db->Delete(P.'multisite_jump','`expire`<\''.$d.'\'');

		Eleanor::LoadOptions(array('user-profile','drafts'));
		if(Eleanor::$vars['reg_unactivated']=='1')
		{
			$ids=array();
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'users_site` WHERE `groups`=\','.GROUP_WAIT.',\' AND `register`<\''.date('Y-m-d H:i:s').'\' - INTERVAL '.Eleanor::$vars['reg_act_time'].' SECOND');
			while($a=$R->fetch_assoc())
				$ids[]=$a['id'];
			if($ids)
				UserManager::Delete($ids);
		}

		#Удаляем черновики
		Eleanor::$Db->Delete(P.'drafts','`date`<\''.date('Y-m-d H:i:s').'\' - INTERVAL '.(int)Eleanor::$vars['drafts_days'].' DAY');

		#Удаляем все файлы из каталога temp, которые добавлен больше дня назад. Естественно, о них все забыли и они не будут обработаны уже никогда :)
		Eleanor::$nolog=true;#Почему-то появляется ошибка Warning: rmdir([path]\uploads\temp) [<a href='function.rmdir'>function.rmdir</a>]: No such file or directory. Хер знает почему
		self::RemoveTempFiles(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.'temp',time()-86400);
		Eleanor::$nolog=false;

		#Удаляем всех удаленных пользователей (если используется синхронизация)
		$lastid=Eleanor::$Cache->Get('deleted-users',true);
		$ids=array();
		$R2=Eleanor::$UsersDb->Query('SELECT `id`,`uid` FROM `'.USERS_TABLE.'_deleted` WHERE `id`>'.(int)$lastid.' ORDER BY `id` ASC LIMIT 50');
		while($a=$R2->fetch_assoc())
			$ids[]=$a['uid'];
		if($ids)
		{
			UserManager::Delete($ids);
			Eleanor::$Cache->Put('deleted-users',end($ids),true);
		}

		#Синхронизация обновленных пользователей
		if(Eleanor::$UsersDb!==Eleanor::$Db or USERS_TABLE!=P.'users')
		{
			$lastd=Eleanor::$Cache->Get('updated-users',true);
			if(!$lastd)
			{
				$R3=Eleanor::$UsersDb->Query('SELECT MIN(`updated`) FROM `'.USERS_TABLE.'` LIMIT 1');
				list($lastd)=$R3->fetch_row();
			}
			$ids=array();
			$R4=Eleanor::$UsersDb->Query('SELECT `id`,`updated` FROM `'.USERS_TABLE.'` WHERE `updated`>\''.$lastd.'\' ORDER BY `updated` ASC LIMIT 50');
			while($a=$R4->fetch_assoc())
			{
				$ids[]=$a['id'];
				$lastd=$a['updated'];
			}
			Eleanor::$Cache->Put('updated-users',$lastd,true);
		}
	}

	public function GetNextRunInfo()
	{
		return'';
	}

	private static function RemoveTempFiles($path,$t)
	{
		if(is_link($path))
			return unlink($path);
		elseif(is_file($path))
			return $t>=filectime($path) ? unlink($path) : false;
		elseif(is_dir($path))
		{
			$emp=true;
			$f=__function__;
			if($files=glob($path.'/*'))
				foreach($files as &$file)
					$emp&=self::$f($file,$t);
			return $emp ? rmdir($path) : false;
		}
		return true;
	}
}