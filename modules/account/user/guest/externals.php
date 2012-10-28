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

	public static function Content($master=true)
	{		if(!$master)
			return;

		if($GLOBALS['Eleanor']->Url->is_static)
			$s=$GLOBALS['Eleanor']->Url->ParseToValue('s',true);
		else
			$s=isset($_GET['s']) ? (string)$_GET['s'] : false;

		if($s)
		{
			Eleanor::StartSession($s);
			if(!isset($_SESSION[__class__]))
				return GoAway();
			$loginza=$_SESSION[__class__];
			Eleanor::LoadOptions('user-profile');
			if(isset($_POST['name']) and !Eleanor::$vars['reg_off'])
			{				$errors=array();
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
					return static::Register($loginza,$errors);

				$regtype=Eleanor::$vars['reg_type'];
				if($regtype==2 and isset($loginza['email']) and $loginza['email']==$values['email'])
					$regtype='1';

				$uadd=array();
				if(isset($loginza['photo']))
					$uadd+=array(
						'avatar_type'=>'url',
						'avatar_location'=>(string)$loginza['photo'],
					);
				if(isset($loginza['biography']))
					$uadd['bio']=(string)$loginza['biography'];
				if(isset($loginza['gender']))
					$uadd['gender']=$loginza['gender']=='M';
				switch($loginza['provider'])
				{
					case'twitter.com':
						$uadd['twitter']=(string)$loginza['nickname'];
					break;
					case'vkontakte.ru':
						$uadd['vk']='id'.$loginza['uid'];
				}

				try
				{
					$id=UserManager::Add(array(
						'name'=>$values['name'],
						'full_name'=>$values['full_name'],
						'_password'=>$values['p1'],
						'email'=>$values['email'],
						'groups'=>Eleanor::$vars['reg_type']==1 ? GROUP_USER : GROUP_WAIT,
					)+$uadd);
					Eleanor::$Db->Insert(P.'users_external_auth',array('provider'=>$loginza['provider'],'provider_uid'=>$loginza['uid'],'id'=>$id,'identity'=>$loginza['identity']));
				}
				catch(EE$E)
				{
					$mess=$E->getMessage();
					switch($mess)
					{
						case'NAME_TOO_LONG':
							return static::Register($loginza,array('NAME_TOO_LONG'=>$lang['NAME_TOO_LONG']($E->addon['max'],$E->addon['you'])));
						break;
						case'PASS_TOO_SHORT':
							return static::Register($loginza,array('PASS_TOO_SHORT'=>$lang['PASS_TOO_SHORT']($E->addon['min'],$E->addon['you'])));
						break;
						default:
							return static::Register($loginza,array($mess));
					}
				}
				Eleanor::$Login->Auth($id);
				$l=include $GLOBALS['Eleanor']->module['path'].'letters-'.Language::$main.'.php';
				$values['name']=htmlspecialchars($values['name'],ELENT,CHARSET);
				switch(Eleanor::$vars['reg_type'])
				{
					#Активация не требуется
					case'1':
						$repl=array(
							'site'=>Eleanor::$vars['site_name'],
							'name'=>$values['full_name'] ? $values['full_name'] : $values['name'],
							'login'=>$values['name'],
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
							'name'=>$values['full_name'] ? $values['full_name'] : $values['name'],
							'login'=>$values['name'],
							'pass'=>$values['p1'],
							'hours'=>round(Eleanor::$vars['reg_act_time']/3600),
							'link'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,
							'confirm'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'activate','id'=>$actid,'md'=>$hash),true,true,false),
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
				}
			}
			return self::Register($loginza);
		}

		$token=isset($_POST['token']) ? (string)$_POST['token'] : false;
		if(!$token)
			return class_exists('AccountIndex',false) ? AccountIndex::Content(true) : null;

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
			array_walk_recursive($r,function(&$v){				$v=mb_convert_encoding($v,CHARSET,'utf-8');			});
		if(!$r or isset($r['error_type']))
			return Eleanor::$Template->LoginzaError($r);
		$r['provider']=trim(strchr($r['provider'],'/'),'/');
		if(!isset($r['uid']))
			$r['uid']=trim(strchr($r['identity'],'/'),'/');
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'users_external_auth` WHERE `provider`='.Eleanor::$Db->Escape($r['provider']).' AND `provider_uid`='.Eleanor::$Db->Escape($r['uid']).' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			if(Eleanor::$Login->Auth($a['id']))
				return GoAway(PROTOCOL.Eleanor::$punycode.Eleanor::$site_path);
			Eleanor::$Db->Delete(P.'users_external_auth','`id`='.$a['id']);
		}

		Eleanor::LoadOptions('user-profile');
		if(Eleanor::$vars['reg_off'])
			return self::Register($r);
		Eleanor::StartSession();
		$_SESSION[__class__]=$r;
		GoAway(PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'externals','s'=>session_id()),true,''));
	}

	public static function Register($loginza,$errors=array())
	{
		if(isset($loginza['name']['full_name']))
			$fn=$loginza['name']['full_name'];
		else
		{
			$f=isset($loginza['name']['first_name']) ? (string)$loginza['name']['first_name'].' ' : '';
			$l=isset($loginza['name']['last_name']) ? (string)$loginza['name']['last_name'] : '';
			$fn=trim($f.$l);
		}

		$GLOBALS['title'][]=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['form_reg'];
		return Eleanor::$Template->AcRegister(
			array(
				'name'=>isset($_POST['name']) ? (string)$_POST['name'] : (empty($loginza['nickname']) ? $fn : (string)$loginza['nickname']),
				'full_name'=>isset($_POST['full_name']) ? (string)$_POST['full_name'] : $fn,
				'email'=>isset($_POST['email']) ? (string)$_POST['email'] : (isset($loginza['email']) ? (string)$loginza['email'] : ''),
				'password'=>isset($_POST['password']) ? (string)$_POST['password'] : '',
				'password2'=>isset($_POST['password2']) ? (string)$_POST['password2'] : '',
				'_external'=>$loginza,
			),
			$GLOBALS['Eleanor']->Captcha->disabled ? false : $GLOBALS['Eleanor']->Captcha->GetCode(),
			$errors
		);
	}
}