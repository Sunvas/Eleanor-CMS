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
	public static
		$wait=true;

	public static function Menu()
	{
		$uinfo=Eleanor::$Login->GetUserValue(array('groups','register'),false);
		if($uinfo['groups']==array(GROUP_WAIT) and static::$wait)
		{
			if(!isset(Eleanor::$vars['reg_act_time']))
				Eleanor::LoadOptions('user-profile');
			if(Eleanor::$vars['reg_type']==2)
				return array(
					'new'=>array(
						'link'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'activate','id'=>0),true,''),
						'remain'=>strtotime($uinfo['register'])-time()+Eleanor::$vars['reg_act_time'],
					),
				);
		}
	}

	public static function Content($master=true)
	{
		if(!$master)
			return;
		$uinfo=Eleanor::$Login->GetUserValue(array('id','groups'));
		if($uinfo['groups']!=array(GROUP_WAIT))
			return GoAway();

		if($GLOBALS['Eleanor']->Url->is_static)
			$_GET+=$GLOBALS['Eleanor']->Url->Parse(array('id','md'));

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$id=isset($_GET['id']) ? (int)$_GET['id'] : -1;
		Eleanor::LoadOptions('user-profile');
		if($id==0 and Eleanor::$vars['reg_type']==2)
		{
			$errors=array();
			$sent=false;
			$hours=0;
			if($_SERVER['REQUEST_METHOD']=='POST' or $GLOBALS['Eleanor']->Captcha->disabled)
				do
				{
					$cach=$GLOBALS['Eleanor']->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
					$GLOBALS['Eleanor']->Captcha->Destroy();
					if(!$cach)
					{
						$errors[]='WRONG_CAPTCHA';
						break;
					}
					Eleanor::$Db->Delete(P.'confirmation','`op`=\'regact\' AND `user`='.$uinfo['id']);
					if(Eleanor::$Db===Eleanor::$UsersDb)
					{
						$table=USERS_TABLE;
						$join=' INNER JOIN `'.P.'users_site` USING(`id`)';
					}
					else
					{
						$table=P.'users_site';
						$join='';
					}

					$R=Eleanor::$Db->Query('SELECT `id`,`u`.`name`,`u`.`full_name`,`email`,UNIX_TIMESTAMP(`u`.`register`) `register` FROM `'.$table.'` `u`'.$join.' WHERE `id`='.$uinfo['id'].' LIMIT 1');
					if($a=$R->fetch_assoc())
					{
						$l=include $GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
						$sname=htmlspecialchars($a['name'],ELENT,CHARSET);
						$hours=round(($a['register']-time()+Eleanor::$vars['reg_act_time'])/3600);
						$actid=Eleanor::$Db->Insert(
								P.'confirmation',
								array(
									'hash'=>$hash=md5(uniqid(microtime())),
									'user'=>$a['id'],
									'op'=>'regact',
									'!date'=>'NOW()',
									'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
									'data'=>serialize(array('newgr'=>array(GROUP_USER))),
								)
						);
						$repl=array(
							'site'=>Eleanor::$vars['site_name'],
							'name'=>$a['full_name'] ? $a['full_name'] : $sname,
							'login'=>$sname,
							'pass'=>false,
							'hours'=>$hours,
							'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
							'confirm'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'activate','id'=>$actid,'md'=>$hash),true,''),
						);
						Email::Simple(
							$a['email'],
							Eleanor::ExecBBLogic($l['reg_t'],$repl),
							Eleanor::ExecBBLogic($l['reg_act'],$repl)
						);
						$sent=true;
					}
					else
						return GoAway();
				}while(false);
			$GLOBALS['title'][]=$lang['reactivation'];
			return Eleanor::$Template->AcReactivation($sent,$GLOBALS['Eleanor']->Captcha->disabled ? false : $GLOBALS['Eleanor']->Captcha->GetCode(),$errors,$hours);
		}

		if(!$id or !isset($_GET['md']))
			return GoAway(true);
		$GLOBALS['title'][]=$lang['activate'];
		$R=Eleanor::$Db->Query('SELECT `data` FROM `'.P.'confirmation` WHERE `id`='.$id.' AND `hash`='.Eleanor::$Db->Escape((string)$_GET['md']).' AND `expire`>=\''.date('Y-m-d H:i:s').'\' AND `user`='.$uinfo['id'].' AND `op`=\'regact\'');
		if($a=$R->fetch_assoc() and $a['data']=unserialize($a['data']))
		{
			UserManager::Update(array('groups'=>$a['data']['newgr']));
			if(Eleanor::$Db===Eleanor::$UsersDb)
			{
				$table=USERS_TABLE;
				$join=' INNER JOIN `'.P.'users_site` USING(`id`)';
			}
			else
			{
				$table=P.'users_site';
				$join='';
			}
			Eleanor::$Db->Delete(P.'confirmation','`id`='.$id.' LIMIT 1');
			$R=Eleanor::$Db->Query('SELECT `u`.`name`,`u`.`full_name`,`email` FROM `'.$table.'` `u`'.$join.' WHERE `id`='.$uinfo['id'].' LIMIT 1');
			if($a=$R->fetch_assoc())
			{
				$l=include$GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
				$sname=htmlspecialchars($a['name'],ELENT,CHARSET);
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$a['full_name'] ? $a['full_name'] : $sname,
					'login'=>$sname,
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				Email::Simple(
					$a['email'],
					Eleanor::ExecBBLogic($l['act_t'],$repl),
					Eleanor::ExecBBLogic($l['act_success'],$repl)
				);
				static::$wait=false;
				return Eleanor::$Template->AcActivate(true);
			}
		}
		return Eleanor::$Template->AcActivate(false);
	}
}