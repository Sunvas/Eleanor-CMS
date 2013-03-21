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

		#Синхронизация обновленных и удаленных пользователей. Добавление здесь не делается, оно происходит в момент входа пользователя
		if(Eleanor::$UsersDb!==Eleanor::$Db or USERS_TABLE!=P.'users')
		{
			$lastdate=Eleanor::$Cache->Get('date-users-sync',true);
			if(!$lastdate)
			{
				$R=Eleanor::$UsersDb->Query('SELECT MIN(`date`) FROM `'.USERS_TABLE.'_updated`');
				list($lastdate)=$R->fetch_row();
			}
			if($lastdate)
			{
				$del=$ids=array();
				$n=1;
				$R=Eleanor::$UsersDb->Query('(SELECT `id`,`date` FROM `'.USERS_TABLE.'_updated` WHERE `date`=\''.$lastdate.'\')UNION ALL(SELECT `id` FROM `'.USERS_TABLE.'_updated` WHERE `date`>\''.$lastdate.'\' ORDER BY `date` ASC LIMIT 50)');
				while($a=$R->fetch_assoc())
				{
					if($n++!=$R->num_rows or $lastdate==$a['date'])
						$ids[]=$a['id'];
					$lastdate=$a['date'];
				}

				$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`last_visit`,`language`,`timezone` FROM `'.USERS_TABLE.'` WHERE `id`'.Eleanor::$Db->In($ids).' AND `temp`=0');
				while($a=$R->fetch_assoc())
				{
					$del[]=$a['id'];
					Eleanor::$Db->Update(P.'users_site',array_slice($a,1),'`id`='.$a['id'].' LIMIT 1');
				}

				$del=array_diff($ids,$del);
				if($del)
					UserManager::Delete($del);
				Eleanor::$Cache->Put('date-users-sync',$lastdate,true);
			}
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