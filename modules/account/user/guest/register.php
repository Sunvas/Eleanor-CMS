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
class AccountRegister
{	public static function Menu()
	{		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'register'),true,''),
		);
	}

	public static function Content($master=true)
	{		Eleanor::LoadOptions('user-profile');
		if(!$master or $_SERVER['REQUEST_METHOD']!='POST' or Eleanor::$vars['reg_off'])
			return static::Register();

		$errors=array();
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$cach=$GLOBALS['Eleanor']->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
		$GLOBALS['Eleanor']->Captcha->Destroy();
		if(!$cach)
			$errors[]='WRONG_CAPTCHA';

		$values=array(
			'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
			'full_name'=>isset($_POST['full_name']) ? (string)$_POST['full_name'] : '',
			'email'=>isset($_POST['email']) ? (string)$_POST['email'] : '',
			'p1'=>isset($_POST['password']) ? (string)$_POST['password'] : '',
			'p2'=>isset($_POST['password2']) ? (string)$_POST['password2'] : '',
		);
		if(!$values['email'])
			$errors[]='EMPTY_EMAIL';

		if($values['name']=='')
			$errors[]='EMPTY_NAME';

		if($values['p1']=='' and $values['p2']=='')
		{
			$p=uniqid();
			$values['p1']=$values['p2']=strlen($p)>=Eleanor::$vars['min_pass_length'] ? substr($p,0,Eleanor::$vars['min_pass_length']>7 ? Eleanor::$vars['min_pass_length'] : 7) : str_pad($p,Eleanor::$vars['min_pass_length'],uniqid(),STR_PAD_RIGHT);
		}
		elseif($values['p1']!=$values['p2'])
			$errors[]='PASSWORD_MISMATCH';

		$R=Eleanor::$Db->Query('SELECT `email` FROM `'.P.'users_site` WHERE `email`='.Eleanor::$Db->Escape($values['email']).' LIMIT 1');
		if($R->num_rows>0)
			$errors[]='EMAIL_EXISTS';

		if($errors)
			return static::Register($errors);
		try
		{
			$id=UserManager::Add(array(
				'name'=>$values['name'],
				'full_name'=>$values['full_name'],
				'_password'=>$values['p1'],
				'email'=>$values['email'],
				'groups'=>Eleanor::$vars['reg_type']==1 ? GROUP_USER : GROUP_WAIT,
			));
		}
		catch(EE$E)
		{
			$mess=$E->getMessage();
			$errors=array();
			switch($mess)
			{
				case'NAME_TOO_LONG':
					$errors['NAME_TOO_LONG']=$lang['NAME_TOO_LONG']($E->addon['max'],$E->addon['you']);
				break;
				case'PASS_TOO_SHORT':
					$errors['PASS_TOO_SHORT']=$lang['PASS_TOO_SHORT']($E->addon['min'],$E->addon['you']);
				break;
				default:
					$errors[]=$mess;
			}
			return static::Register($errors);
		}
		Eleanor::$Login->Auth($id);
		$l=include $GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
		$sname=htmlspecialchars($values['name'],ELENT,CHARSET);
		switch(Eleanor::$vars['reg_type'])
		{
			#Активация не требуется
			case'1':
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'] ? $values['full_name'] : $sname,
					'login'=>$sname,
					'pass'=>$values['p1'],
					'link'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,
				);
				Eleanor::Mail(
					$values['email'],
					Eleanor::ExecBBLogic($l['reg_t'],$repl),
					Eleanor::ExecBBLogic($l['reg_fin'],$repl)
				);
				$GLOBALS['title'][]=$lang['reg_fin'];
				return Eleanor::$Template->AcSuccessReg();
			break;
			#Активация по мылу
			case'2':
				$actid=Eleanor::$Db->Insert(P.
					'confirmation',
					array(
						'hash'=>$hash=md5(uniqid(microtime())),
						'user'=>$id,
						'op'=>'regact',
						'!date'=>'NOW()',
						'!expire'=>'NOW() + INTERVAL '.(int)Eleanor::$vars['reg_act_time'].' SECOND',
						'data'=>serialize(array('newgr'=>array(GROUP_USER))),
					)
				);
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'] ? $values['full_name'] : $sname,
					'login'=>$sname,
					'pass'=>$values['p1'],
					'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
					'link'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,
					'confirm'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'activate','id'=>$actid,'md'=>$hash),true,''),
				);
				Eleanor::Mail(
					$values['email'],
					Eleanor::ExecBBLogic($l['reg_t'],$repl),
					Eleanor::ExecBBLogic($l['reg_act'],$repl)
				);
				$GLOBALS['title'][]=$lang['wait_act'];
				return Eleanor::$Template->AcWaitActivate(false);
			break;
			#Активация админом
			case'3':
				$GLOBALS['title'][]=$lang['wait_act'];
				return Eleanor::$Template->AcWaitActivate(true);
		}	}

	protected static function Register($errors=array())
	{		$GLOBALS['title'][]=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['form_reg'];		return Eleanor::$Template->AcRegister(
			array(
				'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
				'full_name'=>isset($_POST['full_name']) ? (string)$_POST['full_name'] : '',
				'email'=>isset($_POST['email']) ? (string)$_POST['email'] : '',
				'password'=>isset($_POST['password']) ? (string)$_POST['password'] : '',
				'password2'=>isset($_POST['password2']) ? (string)$_POST['password2'] : '',
			),
			$GLOBALS['Eleanor']->Captcha->disabled ? false : $GLOBALS['Eleanor']->Captcha->GetCode(),
			$errors
		);	}}