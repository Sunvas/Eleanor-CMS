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

class TaskSpecial_Spam extends BaseClass implements Task
{
	private
		$opts=array(),
		$data=array();

	public function __construct($opts)
	{
		$this->opts=$opts;
	}

	public function Run($data)
	{
		$this->data=$data;
		if(!isset($this->opts['id']))
			return true;
		$R=Eleanor::$Db->Query('SELECT `id`,`sent`,`total`,`per_run`,`taskid`,`finame`,`finamet`,`figroup`,`figroupt`,`fiip`,`firegisterb`,`firegistera`,`filastvisitb`,`filastvisita`,`figender`,`fiemail`,`fiids`,`deleteondone` FROM `'.P.'spam` WHERE `id`='.(int)$this->opts['id'].' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return true;
		$lid=isset($data['lastid']) ? (int)$data['lastid'] : 0;
		$users=$update=$where=array();
		$langs=array('');
		if($a['finame'] and $a['finamet'])
		{
			$name=Eleanor::$Db->Escape($a['finamet'],false);
			switch($a['finamet'])
			{
				case'b':
					$name=' LIKE \''.$name.'%\'';
				break;
				case'm':
					$name=' LIKE \'%'.$name.'%\'';
				break;
				case'e':
					$name=' LIKE \'%'.$name.'\'';
				break;
				default:
					$name='=\''.$name.'\'';
			}
			$where[]='`u`.`name`'.$name;
		}
		if($a['fiids'])
			$where[]='`id`'.Eleanor::$Db->In(explode(',',Tasks::FillInt($a['fiids'])));
		if($a['firegisterb'] and 0<$t=strtotime($a['firegisterb']))
			$where[]='`u`.`register`>=\''.date('Y-m-d H:i:s',$t).'\'';
		if($a['firegistera'] and 0<$t=strtotime($a['firegistera']))
			$where[]='`u`.`register`<=\''.date('Y-m-d H:i:s',$t).'\'';
		if($a['fiip'])
			$where[]='`ip` LIKE \''.str_replace('*','%',Eleanor::$Db->Escape($a['fiip'],false)).'\'';
		if($a['fiemail'])
			$where[]='`email` LIKE \''.str_replace('*','%',Eleanor::$Db->Escape($a['fiemail'],false)).'\'';

		if($a['figender'] and $a['figender']>-2)
			$where[]='`gender`='.(int)$a['figender'];
		if($a['figroup'])
		{
			$gr=explode(',',trim($a['figroup'],','));
			if($a['figroupt']=='and')
			{
				$g='%,';
				foreach($gr as &$v)
					$g.=(int)$v.',%';
				$where[]='`groups` LIKE \''.str_replace('*','%',$g).'\'';
			}
			else
			{
				foreach($gr as &$v);
					$v=(int)$v;
				$where[]='`groups` REGEXP \',('.join('|',$gr).'),\'';
			}
		}
		$where=$where ? join(' AND ',$where) : false;
		if($a['sent']+$a['per_run']>$a['total'])
		{
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM '.(Eleanor::$Db===Eleanor::$UsersDb ? '`'.USERS_TABLE.'` `u` INNER JOIN `'.P.'users_site` USING(`id`)' : '`'.P.'users_site`').' INNER JOIN `'.P.'users_extra` USING(`id`)'.($where ? ' WHERE '.$where : ''));
			list($a['total'])=$R->fetch_row();
			$update['total']=$a['total'];
		}
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`name`,`u`.`full_name`,`email`,`u`.`language` FROM `'.USERS_TABLE.'` `u` INNER JOIN `'.P.'users_extra` USING(`id`) INNER JOIN `'.P.'users_site` USING(`id`) WHERE `id`>'.(int)$lid.($where ? ' AND '.$where : '').' LIMIT '.$a['per_run']);
			while($temp=$R->fetch_assoc())
			{
				$users[$temp['id']]=array_slice($temp,1);
				$langs[]=$temp['language'] ? $temp['language'] : LANGUAGE;
			}
		}
		else
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`name`,`full_name`,`email` FROM `'.P.'users_site` `u` INNER JOIN `'.P.'users_extra` `e` USING(`id`) WHERE `id`>'.(int)$lid.($where ? ' AND '.$where : '').' LIMIT '.$a['per_run']);
			while($temp=$R->fetch_assoc())
				$users[$temp['id']]=array_slice($temp,1);

			if($users)
			{
				$R=Eleanor::$UsersDb->Query('SELECT `id`,`language` FROM `'.USERS_TABLE.'` WHERE `id`'.Eleanor::$UsersDb->In(array_keys($users)));
				while($temp=$R->fetch_assoc())
				{
					$users[$temp['id']]['language']=$temp['language'];
					$langs[]=$temp['language'] ? $temp['language'] : LANGUAGE;
				}
			}
		}
		$ret=true;
		do
		{
			if(!$users)
			{
				$update['sent']=$a['total'];
				$update['status']='finished';
				$update['!statusdate']='NOW()';
				break;
			}
			$frepk=$frepv=array();
			$files=glob(Eleanor::$root.Eleanor::$uploads.'/spam/'.$a['id'].'/*');
			foreach($files as $k=>&$v)
			{
				$frepk[$k]='src="'.str_replace(Eleanor::$root,PROTOCOL.Eleanor::$domain.Eleanor::$site_path,$v).'"';
				$frepv[$k]='src="cid:f'.$k.'"';
			}
			$langs=array_unique($langs);
			$R=Eleanor::$Db->Query('SELECT `language`,`title`,`text` FROM `'.P.'spam_l` WHERE `id`='.$a['id'].' AND `language`'.Eleanor::$Db->In($langs));
			$langs=array();
			while($temp=$R->fetch_assoc())
			{
				$temp['text']=OwnBB::Parse($temp['text']);
				$temp['text']=str_replace('href="go.php','href="',$temp['text']);
				$temp['text']=preg_replace('#(src|href)=(["\'])(?![a-z]{1,5}://)#','\1=\2'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path,$temp['text']);
				$temp['text']=preg_replace('#url\((["\']?)(?![a-z]{1,5}://)#','url(\1'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path,$temp['text']);
				$temp['text']=str_replace($frepk,$frepv,$temp['text']);
				$langs[$temp['language']]=array_slice($temp,1);
			}
			$c='';
			$Email=new Email;
			$Email->parts=array(
				'multipart'=>'mixed',
				array(
					'content-type'=>'text/html',
					'charset'=>DISPLAY_CHARSET,
					'content'=>&$c,
				),
			);
			foreach($files as $k=>&$v)
				$Email->parts[]=array(
					'content-type'=>Types::MimeTypeByExt($v),
					'filename'=>basename($v),
					'content'=>file_get_contents($v),
					'id'=>'f'.$k,
				);

			foreach($users as $k=>&$v)
			{
				if($v['email'])
				{
					$lang=Eleanor::FilterLangValues($langs,$v['language'],false);
					if($lang)
					{
						$Email->subject=Eleanor::ExecBBLogic($lang['title'],$v);
						$c=Eleanor::ExecBBLogic($lang['text'],$v);
						$Email->Send(array('to'=>$v['email']));
					}
				}
				++$a['sent'];
				$this->data['lastid']=$k;
			}
			$update['sent']=$a['sent'];

			$ret=false;
		}while(false);
		if($ret and $a['deleteondone'])
		{
			Eleanor::$Db->Delete(P.'spam','`id`='.$a['id'].' LIMIT 1');
			Eleanor::$Db->Delete(P.'spam_l','`id`='.$a['id']);
			Files::Delete(Eleanor::$root.Eleanor::$uploads.'/spam/'.$a['id']);
		}
		else
			Eleanor::$Db->Update(P.'spam',$update,'`id`='.$a['id'].' LIMIT 1');
		return$ret;
	}

	public function GetNextRunInfo()
	{
		return$this->data;
	}
}