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
class AccountChangeEmail
{
	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'changeemail'),true,''),
		);
	}

	public static function Content($master=true)
	{
		Eleanor::LoadOptions('user-profile');

		if($master)
		{
			$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
			$GLOBALS['title'][]=$lang['changing_email'];
			if($GLOBALS['Eleanor']->Url->is_static)
				$_GET+=$GLOBALS['Eleanor']->Url->Parse(array('id','md','secret'));
		}

		$errors=array();
		if($master and isset($_GET['id'],$_GET['md']))
		{
			$data=Eleanor::$Login->GetUserValue(array('id','full_name','name','email'),true);
			$R=Eleanor::$Db->Query('SELECT `id`,`hash`,`data` FROM `'.P.'confirmation` WHERE `id`='.(int)$_GET['id'].' AND `hash`='.Eleanor::$Db->Escape($_GET['md']).' AND `expire`>=\''.date('Y-m-d H:i:s').'\' AND `user`='.$data['id'].' AND `op`=\'changeemail\'');
			if($a=$R->fetch_assoc() and $a['data']=unserialize($a['data']))
				if($a['data']['step']==1)
				{
					$a['data']['secret']=md5(uniqid(microtime()));
					$a['data']['step']=2;
					$l=include $GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
					$repl=array(
						'site'=>Eleanor::$vars['site_name'],
						'name'=>$data['full_name'] ? $data['full_name'] : $data['name'],
						'login'=>$data['name'],
						'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
						'oldemail'=>$data['email'],
						'newemail'=>$a['data']['email'],
						'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
						'confirm'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'changeemail','id'=>$a['id'],'md'=>$a['hash'],'secret'=>$a['data']['secret']),true,''),
					);
					Email::Simple(
						$a['data']['email'],
						Eleanor::ExecBBLogic($l['newemail_t'],$repl),
						Eleanor::ExecBBLogic($l['newemail_new'],$repl)
					);
					Eleanor::$Db->Update(P.
						'confirmation',
						array(
							'!date'=>'NOW()',
							'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
							'data'=>serialize($a['data']),
						),
						'`id`='.$a['id'].' LIMIT 1'
					);
					return Eleanor::$Template->AcEmailChangeSteps12(2);
				}
				elseif(!isset($a['data']['secret']) or isset($_GET['secret'],$a['data']['secret']) and $_GET['secret']==$a['data']['secret'])
				{
					Eleanor::$Db->Delete(P.'confirmation','`id`='.(int)$_GET['id'].' LIMIT 1');
					UserManager::Update(array('email'=>$a['data']['email']));
					return Eleanor::$Template->AcEmailChangeSuccess();
				}
			$errors[]='EMAIL_BROKEN_LINK';
		}

		$values=array(
			'email'=>isset($_POST['email']) ? (string)$_POST['email'] : '',
		);

		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$cach=$GLOBALS['Eleanor']->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
			$GLOBALS['Eleanor']->Captcha->Destroy();
			if(!$cach)
				$errors[]='WRONG_CAPTCHA';

			if(!Strings::CheckEmail($values['email'],false))
				$errors[]='EMAIL_ERROR';

			try
			{
				UserManager::IsEmailBlocked($values['email']);
			}
			catch(EE $E)
			{
				$errors[]='EMAIL_BLOCKED';
			}

			$data=Eleanor::$Login->GetUserValue(array('id','full_name','name','email'),false);
			if($data['email']==$values['email'])
				$errors[]='EMAIL_YOURS';

			$data['name']=htmlspecialchars($data['name'],ELENT,CHARSET);
			$R=Eleanor::$Db->Query('SELECT `email` FROM `'.P.'users_site` WHERE `email`='.Eleanor::$Db->Escape($values['email']).' AND `id`!='.(int)$data['id'].' LIMIT 1');
			if($R->num_rows>0)
				$errors[]='EMAIL_EXISTS';

			if(!$errors)
			{
				$l=include$GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
				Eleanor::$Db->Delete(P.'confirmation','`op`=\'changeemail\' AND `user`='.$data['id']);
				if($data['email'])
				{
					$hash=md5(uniqid(microtime()));
					$actid=Eleanor::$Db->Insert(P.
						'confirmation',
						array(
							'hash'=>$hash,
							'user'=>$data['id'],
							'op'=>'changeemail',
							'!date'=>'NOW()',
							'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
							'data'=>serialize(array('step'=>1,'email'=>$values['email'])),
						)
					);
					$repl=array(
						'site'=>Eleanor::$vars['site_name'],
						'name'=>$data['full_name'] ? $data['full_name'] : $data['name'],
						'login'=>$data['name'],
						'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
						'oldemail'=>$data['email'],
						'newemail'=>$values['email'],
						'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
						'confirm'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'changeemail','id'=>$actid,'md'=>$hash),true,''),
					);
					Email::Simple(
						$data['email'],
						Eleanor::ExecBBLogic($l['newemail_t'],$repl),
						Eleanor::ExecBBLogic($l['newemail_old'],$repl)
					);
					return Eleanor::$Template->AcEmailChangeSteps12(1);
				}
				else
				{
					$actid=Eleanor::$Db->Insert(P.
						'confirmation',
						array(
							'hash'=>$hash=md5(uniqid(microtime())),
							'user'=>$data['id'],
							'op'=>'changeemail',
							'!date'=>'NOW()',
							'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
							'data'=>serialize(array('step'=>2,'email'=>$values['email'])),
						)
					);
					$repl=array(
						'site'=>Eleanor::$vars['site_name'],
						'name'=>$data['full_name'] ? $data['full_name'] : $data['name'],
						'login'=>$data['name'],
						'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
						'oldemail'=>$data['email'],
						'newemail'=>$values['email'],
						'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
						'confirm'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'changeemail','id'=>$actid,'md'=>$hash),true,''),
					);
					Email::Simple(
						$values['email'],
						Eleanor::ExecBBLogic($l['newemail_t'],$repl),
						Eleanor::ExecBBLogic($l['newemail_new'],$repl)
					);
					return Eleanor::$Template->AcEmailChangeSteps12(2);
				}
			}
		}
		return Eleanor::$Template->AcEmailChange($values,$GLOBALS['Eleanor']->Captcha->disabled ? false : $GLOBALS['Eleanor']->Captcha->GetCode(),$errors);
	}
}