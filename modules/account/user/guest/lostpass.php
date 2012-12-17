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
class AccountLostPass
{
	public static function Menu()
	{
		if(!isset(Eleanor::$vars['account_pass_rec_t']))
			Eleanor::LoadOptions('user-profile');
		if(Eleanor::$vars['account_pass_rec_t'])
			return array(
				'main'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'lostpass'),true,''),
			);
	}

	public static function Content($master=true)
	{
		if(!isset(Eleanor::$vars['account_pass_rec_t']))
			Eleanor::LoadOptions('user-profile');

		if(!Eleanor::$vars['account_pass_rec_t'])
			return$master ? GoAway() : '';

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		if($master)
		{
			if($GLOBALS['Eleanor']->Url->is_static)
				$_GET+=$GLOBALS['Eleanor']->Url->Parse(array('id','md'));

			$post=$_SERVER['REQUEST_METHOD']=='POST';
			$errors=array();
			if($post)
			{				$cach=$GLOBALS['Eleanor']->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
				$GLOBALS['Eleanor']->Captcha->Destroy();
				if(!$cach)
					$errors[]='WRONG_CAPTCHA';			}

			if(isset($_GET['id'],$_GET['md']))
			{
				$R=Eleanor::$Db->Query('SELECT `user`,`data` FROM `'.P.'confirmation` WHERE `id`='.(int)$_GET['id'].' AND `hash`='.Eleanor::$Db->Escape($_GET['md']).' AND `expire`>=\''.date('Y-m-d H:i:s').'\' AND `op`=\'lostpass\'');
				if($a=$R->fetch_assoc())
				{
					$R=Eleanor::$Db->Query('SELECT `full_name`,`name`,`email` FROM `'.P.'users_site` WHERE `id`='.$a['user'].' LIMIT 1');
					if(!$user=$R->fetch_assoc())
						return GoAway();

					if(Eleanor::$vars['account_pass_rec_t']==2)
						$ps=true;
					elseif($post)
					{						$values=array(
							'password'=>isset($_POST['password']) ? (string)$_POST['password'] : '',
							'password2'=>isset($_POST['password2']) ? (string)$_POST['password2'] : '',
						);
						if($values['password']!==$values['password2'])
							$errors[]='PASSWORD_MISMATCH';
						else
						{
							$ps=$values['password']==='';
							$pass=$values['password'];
						}
					}
					else
						return static::RemindNewPass($user);
					if($errors)
						return static::RemindNewPass($user,$values,$errors);

					if($ps)
					{						$p=uniqid();
						$pass=strlen($p)>=Eleanor::$vars['min_pass_length'] ? substr($p,0,Eleanor::$vars['min_pass_length']>7 ? Eleanor::$vars['min_pass_length'] : 7) : str_pad($p,Eleanor::$vars['min_pass_length'],uniqid(),STR_PAD_RIGHT);					}

					try
					{
						UserManager::Update(array('_password'=>$pass),$a['user']);
					}
					catch(EE$E)
					{
						$mess=$E->getMessage();
						switch($mess)
						{
							case'PASS_TOO_SHORT':
								$errors['PASS_TOO_SHORT']=sprintf($lang['PASS_TOO_SHORT'],$E->extra['min'],$E->extra['you']);
							break;
							default:
								$errors[]=$mess;
						}
						return static::RemindNewPass($user,$values,$errors);
					}
					$l=include $GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
					$sname=htmlspecialchars($user['name'],ELENT,CHARSET);
					$repl=array(
						'site'=>Eleanor::$vars['site_name'],
						'name'=>$user['full_name'] ? $user['full_name'] : $sname,
						'login'=>$sname,
						'pass'=>$pass,
						'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
					);
					Email::Simple(
						$user['email'],
						Eleanor::ExecBBLogic($l['passremfin_t'],$repl),
						Eleanor::ExecBBLogic($l['passremfin'],$repl)
					);
					Eleanor::$Db->Delete(P.'confirmation','`id`='.(int)$_GET['id'].' LIMIT 1');
					$GLOBALS['title'][]=$lang['successful'];
					return Eleanor::$Template->AcRemindPassSent($ps,$user);
				}
			}
			if($post)
			{				$values=array(
					'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
					'email'=>isset($_POST['email']) ? (string)$_POST['email'] : '',
				);
				if($values['name']=='' and $values['email']=='')
					$errors[]='EMPTY_FIELDS';

				if($errors)
					return static::RemindPass($errors);
				if(Eleanor::$Db===Eleanor::$UsersDb)
				{
					$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`s`.`email` FROM `'.USERS_TABLE.'` `u` INNER JOIN `'.P.'users_site` `s` USING(`id`) WHERE '.($values['name'] ? '`u`.`name`='.Eleanor::$Db->Escape($values['name']) : '`s`.`email`='.Eleanor::$Db->Escape($values['email'])).' LIMIT 1');
					$a=$R->fetch_assoc();
				}
				else
				{
					$id=false;
					if($values['name'])
					{
						$R=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$UsersDb->Escape($values['name']).' LIMIT 1');
						list($id)=$R->fetch_row();
					}
					$R=Eleanor::$Db->Query('SELECT `id`,`full_name`,`name`,`email` FROM `'.P.'users_site` WHERE '.($id ? '`id`='.$id : '`email`='.Eleanor::$Db->Escape($values['email'])).' LIMIT 1');
					$a=$R->fetch_assoc();
				}

				if(!$a)
					return static::RemindPass(array('ACCOUNT_NOT_FOUND'));
				Eleanor::$Db->Delete(P.'confirmation','`op`=\'lostpass\' AND `user`='.$a['id']);
				$actid=Eleanor::$Db->Insert(P.
					'confirmation',
					array(
						'hash'=>$hash=md5(uniqid(microtime())),
						'user'=>$a['id'],
						'op'=>'lostpass',
						'!date'=>'NOW()',
						'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
					)
				);
				$l=include $GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
				$sname=htmlspecialchars($a['name'],ELENT,CHARSET);
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$a['full_name'] ? $a['full_name'] : $sname,
					'login'=>$sname,
					'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
					'confirm'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'lostpass','id'=>$actid,'md'=>$hash),true,''),
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				Email::Simple(
					$a['email'],
					Eleanor::ExecBBLogic($l['passrem_t'],$repl),
					Eleanor::ExecBBLogic($l['passrem'],$repl)
				);
				$GLOBALS['title'][]=$lang['wait_pass1'];
				return Eleanor::$Template->AcRemindPassStep2();			}
		}
		return self::RemindPass();
	}

	protected static function RemindPass($errors=array())
	{		$GLOBALS['title'][]=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['reminderpass'];
		return Eleanor::$Template->AcRemindPass(
			array(
				'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
				'email'=>isset($_POST['email']) ? (string)$_POST['email'] : '',
			),
			$GLOBALS['Eleanor']->Captcha->disabled ? false : $GLOBALS['Eleanor']->Captcha->GetCode(),
			$errors
		);
	}

	protected static function RemindNewPass($user,$values=array(),$errors=array())
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$values+=array(
			'password'=>'',
			'password2'=>'',
		);
		$GLOBALS['title'][]=sprintf($lang['new_pass'],$user['name']);
		return Eleanor::$Template->AcRemindPassStep3($values,$GLOBALS['Eleanor']->Captcha->disabled ? false : $GLOBALS['Eleanor']->Captcha->GetCode(),$errors);
	}
}