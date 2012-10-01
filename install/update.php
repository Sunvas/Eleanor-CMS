<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym. See addons/copyrights/info.txt for more information.
*/
define('CMS',true);
define('INSTALL',true);
define('UPDATE',true);
require './init.php';

$step=isset($_GET['step']) ? (int)$_GET['step'] : 1;
Eleanor::StartSession(isset($_REQUEST['s']) ? $_REQUEST['s'] : '','INSTALLSESSION');
if(empty($_SESSION['agree_sanc']) or empty($_SESSION['agree_lic']))
	return GoAway(PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'index.php?s='.session_id());
if(isset($_SESSION['lang']))
{
	Language::$main=$_SESSION['lang'];
	Eleanor::$Language->Change();
}
$lang=Eleanor::$Language->Load('install/lang/update-*.php','install');
$percent=0;
$error=$text=$navi='';

switch($step)
{
	case 5:
		if(!empty($_SESSION['done']))
		{
			$percent=100;
			$navi=$title=$lang['finish'];
			Install::IncludeDb();

			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont"><div class="information" style="text-align:center"><h4 style="color: green;">'.$title.'</h4></div><div class="information">'.sprintf($lang['upd_fin'],ELEANOR_VERSION).'</div><div class="submitline">'
			.sprintf($lang['mback'],PROTOCOL.Eleanor::$punycode.dirname(Eleanor::$site_path).'/').'</div></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			break;
		}
	case 4:
		if(isset($_SESSION['build']))
		{
			$navi=$title=$lang['updating'];
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">';
			if($_SESSION['build']>ELEANOR_BUILD)
			{
				header('Location: update.php?step=3&s='.session_id());
				die;
			}
			Install::IncludeDb();

			if(!isset($_SESSION['range']))
			{
				$folders=glob(Eleanor::$root.'install/data_update/*',GLOB_ONLYDIR);
				foreach($folders as &$v)
					$v=(int)substr(strrchr($v,'/'),1);
				$_SESSION['range']=array_intersect(range($_SESSION['build']+1,ELEANOR_BUILD),$folders);
				$_SESSION['cnt']=count($_SESSION['range']);
				$_SESSIN['data']=null;
			}
			$cnt=count($_SESSION['range']);
			$p=ceil(25/$_SESSION['cnt']);
			$p*=$_SESSION['cnt']-$cnt;
			if($p>25)
				$p=25;
			$percent=75+$p;

			if($cnt==0)
			{				$_SESSION['done']=true;				Eleanor::$Db->Insert(P.'upgrade_hist',array('version'=>ELEANOR_VERSION,'!date'=>'NOW()','build'=>ELEANOR_BUILD,'uid'=>$_SESSION['uid']));
				header('location: update.php?step=5&s='.session_id());
				die;
			}
			$udir=reset($_SESSION['range']);
			if(is_file(Eleanor::$root.'install/data_update/'.$udir.'/index.php'))
			{				$substep=isset($_GET['substep']) ? (int)$_GET['substep'] : 0;
				include Eleanor::$root.'install/data_update/'.$udir.'/index.php';
				$cl='Update_'.$udir;
				if(class_exists($cl,false) and is_subclass_of($cl,'UpdateClass'))
				{
					if($cl::Run(isset($_SESSION['data']) ? $_SESSION['data'] : null)===false)
						$_SESSION['data']=$cl::GetNextRunInfo();
					else
					{
						array_splice($_SESSION['range'],0,1);
						$_SESSION['data']=null;
					}
					$info=$cl::GetText();
				}
				else
				{
					array_splice($_SESSION['range'],0,1);
					$_SESSION['data']=null;
					$info=false;
				}
				$url='update.php?step=4&amp;substep='.++$substep.'&amp;s='.session_id();
				Eleanor::$Template->RedirectScreen($url,5);
				$text.='<div class="information">'.($info ? $info : $lang['inprogress']).'</div><div class="submitline"><a href="'.$url.'">'.$lang['press_here'].'</a></div>';
			}
			$text.='</div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			break;
		}
	case 3:
		if(!empty($_SESSION['uid']))
		{
			Install::IncludeDb();
			$R=Eleanor::$Db->Query('SELECT `version`,`build` FROM `'.P.'upgrade_hist` ORDER BY `id` DESC LIMIT 1');
			if(!$a=$R->fetch_assoc() or $a['build']>=ELEANOR_BUILD)
			{				$percent=100;
				$navi=$title=$lang['finish'];

				$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div>
				<div class="wpmid"><div class="wpcont"><div class="information" style="text-align:center"><h3>'.$lang['unn'].'</h3><div class="submitline">'.sprintf($lang['mback'],PROTOCOL.Eleanor::$punycode.dirname(Eleanor::$site_path).'/').'</div></div>
				</div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			}
			else
			{				$navi=$title=$lang['uready'];
				$percent=70;
				$_SESSION['build']=$a['build'];

				$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div>
					<div class="wpmid"><div class="wpcont"><form method="post" action="update.php?step=4">
					<div class="information" align="center"><h3>'.sprintf($lang['update_to'],$a['version'],ELEANOR_VERSION).'</h3></div>
					<div class="submitline">'.Eleanor::Control('s','hidden',session_id())
					.Eleanor::Button($lang['yes'],'submit',array('class'=>'button','tabindex'=>1)).Eleanor::Button($lang['no'],'button',array('class'=>'button','tabindex'=>2,'onclick'=>'window.close()'))
					.'</div></form></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			}
			break;
		}
	case 2:
		$errors=array();
		if(isset($_POST['host'],$_POST['name'],$_POST['user'],$_POST['pass'],$_POST['pref']))
		{
			$percent=55;
			do
			{
				$a=array(
					'host'=>(string)$_POST['host'],
					'user'=>(string)$_POST['user'],
					'pass'=>(string)$_POST['pass'],
					'prefix'=>(string)$_POST['pref'],
					'db'=>(string)$_POST['name'],
					'languages'=>isset($_POST['languages']) ? (array)$_POST['languages'] : array(),
				);
				$langs=array();
				foreach($a['languages'] as &$v)
					if(isset(Eleanor::$langs[$v]))
						$langs[]=Eleanor::$langs[$v]['name'];
				try
				{
					Eleanor::$Db=new Db($a);
				}
				catch(EE$E)
				{					$error=$E->getMessage();
					break;
				}
				if(!Install::CheckMySQLVersion())
				{
					$error=$lang['low_mysql'];
					break;
				}
				$_SESSION=$a+$_SESSION;
				$title=$navi=$lang['vcf'];
				$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div>
				<div class="wpmid"><div class="wpcont">
				<form method="post">
				<h3 class="subhead">'.$lang['vcf'].'</h3>
				<ul class="reset formfield">
				<li class="ffield"><span class="label">'.$lang['db_host'].'</span><div class="ffdd"><h4>'.$_POST['host'].'</h4></div></li>
				<li class="ffield"><span class="label">'.$lang['db_name'].'</span><div class="ffdd"><h4>'.htmlspecialchars($_POST['name'],ELENT,CHARSET).'</h4></div></li>
				<li class="ffield"><span class="label">'.$lang['db_user'].'</span><div class="ffdd"><h4>'.htmlspecialchars($_POST['user'],ELENT,CHARSET).'</h4></div></li>
				<li class="ffield"><span class="label">'.$lang['db_pass'].'</span><div class="ffdd"><h4>'.htmlspecialchars($_POST['pass'],ELENT,CHARSET).'</h4></div></li>
				<li class="ffield"><span class="label">'.$lang['db_pref'].'</span><div class="ffdd"><h4>'.htmlspecialchars($_POST['pref'],ELENT,CHARSET).'</h4></div></li>
				<li class="ffield"><span class="label">'.$lang['addonl'].'</span><div class="ffdd"><h4>'.($langs ? implode(', ',$langs) : $lang['no']).'</h4></div></li>
				</ul>
				<div class="submitline">'.Eleanor::Control('s','hidden',session_id())
				.Eleanor::Button($lang['back'],'button',array('class'=>'button','onclick'=>'history.go(-1)','tabindex'=>2),2)
				.Eleanor::Button($lang['next'],'submit',array('class'=>'button','tabindex'=>1),2).'</div></form>
				</div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			}while(false);
			if(!$error)
				break;
		}
		elseif(is_file(Eleanor::$root.'config_general.php') and !is_file(Eleanor::$root.'config_general.bak'))
		{			Install::IncludeDb();
			$percent=65;
			$navi=$lang['enter_pass'];
			if(isset($_POST['login'],$_POST['pass']))
			{				$values=array('login'=>(string)$_POST['login'],'pass'=>(string)$_POST['pass']);
				$R=Eleanor::$UsersDb->Query('SELECT `id`,`name`,`pass_salt`,`pass_hash` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$UsersDb->Escape($values['login']).' LIMIT 1');
				do
				{					if(!$a=$R->fetch_assoc())
					{						$error=$lang['WRONG_LOGIN'];						break;					}

					$R=Eleanor::$UsersDb->Query('SELECT `groups`,`groups_overload` FROM `'.P.'users_site` WHERE `id`='.(int)$a['id'].' LIMIT 1');
					if($R->num_rows==0)
					{
						$error=$lang['WRONG_LOGIN'];
						break;
					}
					$a+=$R->fetch_assoc();

					if($a['pass_hash']!=UserManager::PassHash($a['pass_salt'],$values['pass']))
					{						$error=$lang['WRONG_PASSWORD'];
						break;					}

					$over=$a['groups_overload'] ? (array)unserialize($a['groups_overload']) : array();
					if(!isset($over['method']['access_cp'],$over['value']['access_cp']) or $over['method']['access_cp']=='inherit')
						$acp=Eleanor::Permissions(explode(',,',trim($a['groups'],',')),'access_cp');
					else
					{
						$acp=($add=$over['method']['access_cp']=='replace') ? array($over['value']['access_cp']) : Eleanor::Permissions(explode(',,',trim($a['groups'],',')),'access_cp');
						if(!$can)
							$acp[]=$over['value']['access_cp'];
					}

					if(array_sum($acp))
					{
						$_SESSION['uid']=$a['id'];
						$_SESSION['name']=$a['name'];
						header('Location: update.php?step=3&s='.session_id());
						die;
					}
					else
						$error=$lang['noacp'];
				}while(false);

			}
			else
				$values=array('login'=>'','pass'=>'');

			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div>
			<div class="wpmid"><div class="wpcont">
			<div class="information"><h4>'.$lang['enter_a'].'</h4></div>'
			.($error ? Eleanor::$Template->Message($error) : '')
			.'<form method="post">
			<ul class="reset formfield">
				<li class="ffield">
					<span class="label">'.$lang['login'].'</span><div class="ffdd">'.Eleanor::Edit('login',$values['login'],array('class'=>'f_text','tabindex'=>1)).'</div>
				</li>
				<li class="ffield">
					<span class="label">'.$lang['db_pass'].'</span><div class="ffdd">'.Eleanor::Control('pass','password','',array('class'=>'f_text','tabindex'=>2)).'</div>
				</li>
			</ul>
			<div class="submitline">'.Eleanor::Button($lang['next'],'submit',array('class'=>'button','tabindex'=>3),2).'</div>
			</form></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			break;
		}
		elseif(isset($_POST['s']))
		{
			if(is_file(Eleanor::$root.'config_general.bak') and !is_file(Eleanor::$root.'config_general.php'))
			{
				$from=array(
					'[language]',
					'[version]',
					'[db_host]',
					'[db]',
					'[db_user]',
					'[db_pass]',
					'[db_prefix]',
					'[charset]',
					'[display_charset]',
					'[db_charset]',
					'#[users_db]',
				);
				$to=array(
					Language::$main,
					ELEANOR_VERSION,
					$_SESSION['host'],
					$_SESSION['db'],
					$_SESSION['user'],
					$_SESSION['pass'],
					$_SESSION['prefix'],
					CHARSET,
					DISPLAY_CHARSET,
					DB_CHARSET,
					'#',
				);
				foreach(Eleanor::$langs as $k=>&$v)
				{
					if($k==Language::$main or in_array($k,$_SESSION['languages']))
						$from[]='#[lang_'.$k.']';
					else
						$from[]='[lang_'.$k.']';
					$to[]='';
				}
				file_put_contents(Eleanor::$root.'config_general.bak',str_replace($from,$to,file_get_contents(Eleanor::$root.'config_general.bak')));
				if(!rename(Eleanor::$root.'config_general.bak',Eleanor::$root.'config_general.php'))
				{
					$error=$lang['unwbak'];
					break;
				}
			}

			$percent=65;
			$navi=$title=$lang['updating'];
			$url='update.php?step=2&s='.session_id();
			Eleanor::$Template->RedirectScreen($url,5);

			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">
			<div class="information" style="text-align:center"><h4>'.$lang['cfcs'].'</h4></div>
			<div class="submitline"><a href="'.$url.'">'.$lang['press_here'].'</a></div></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			break;
		}
	default:
		if(is_file(Eleanor::$root.'config_general.bak'))
		{
			if(!is_writeable(Eleanor::$root.'config_general.bak'))
				$error=$lang['unwbak'];
			if(is_file(Eleanor::$root.'config_general.php'))
				if(is_writeable(Eleanor::$root.'config_general.php'))
				{					Eleanor::$nolog=true;
					$conf=include(Eleanor::$root.'config_general.php');
					Eleanor::$nolog=false;
					rename(Eleanor::$root.'config_general.php',Eleanor::$root.'config_general_old.php');
					$from=array(
						'[charset]',
						'[display_charset]',
						'[db_charset]',
						'[version]',
						'[language]',
						'[db_prefix]',
						'[db_host]',
						'[db]',
						'[db_user]',
						'[db_pass]',
					);
					$to=array(
						CHARSET,
						DISPLAY_CHARSET,
						DB_CHARSET,
						ELEANOR_VERSION,
						Language::$main,
						P,
						$conf['db_host'],
						$conf['db'],
						$conf['db_user'],
						$conf['db_pass'],
					);

					if(isset($conf['users']))
					{
						$from[]='#[users_db]';
						$to[]='';
						$from[]='[users_db_host]';
						$to[]=$conf['users']['db_host'];
						$from[]='[users_db]';
						$to[]=$conf['users']['db'];
						$from[]='[users_db_user]';
						$to[]=$conf['users']['db_user'];
						$from[]='[users_db_pass]';
						$to[]=$conf['users']['db_pass'];
					}
					foreach(Eleanor::$langs as $k=>&$v)
					{
						if(isset($conf['langs'][$k]))
							$from[]='#[lang_'.$k.']';
						else
							$from[]='[lang_'.$k.']';
						$to[]='';
					}

					file_put_contents(Eleanor::$root.'config_general.php',str_replace($from,$to,file_get_contents(Eleanor::$root.'config_general.bak')));
					unlink(Eleanor::$root.'config_general.bak');
					header('location: update.php?step=2&s='.session_id());
					die;
				}
				else
				{
					$error=$lang['unwcphp'];
					break;
				}
		}
		elseif(is_file(Eleanor::$root.'config_general.php'))
		{
			header('location: update.php?step=2&s='.session_id());
			die;
		}
		else
		{
			$error=$lang['nobak'];
			break;
		}

		$percent=50;
		$navi=$title=$lang['crconfig'];

		$agm=function_exists('apache_get_modules');
		$canurl=$agm && in_array('mod_rewrite',apache_get_modules());
		$host=isset($_POST['host']) ? (string)$_POST['host'] : 'localhost';
		$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
		$user=isset($_POST['user']) ? (string)$_POST['user'] : '';
		$pass=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
		$pref=isset($_POST['pref']) ? (string)$_POST['pref'] : 'el_';
		$langs=isset($_POST['languages']) ? (array)$_POST['languages'] : array();

		$languages='';
		foreach(Eleanor::$langs as $k=>&$v)
			if($k!=Language::$main)
				$languages.=Eleanor::Option($v['name'],$k,in_array($k,$langs));
		$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">'
		.($error ? Eleanor::$Template->Message($error) : '')
		.'<form method="post" action="update.php?step=2">
		<h3 class="subhead">'.$lang['db'].'</h3>
		<ul class="reset formfield">
			<li class="ffield">
				<span class="label"><b>'.$lang['db_host'].'</b></span><div class="ffdd">'.Eleanor::Edit('host',$host,array('class'=>'f_text','tabindex'=>1)).'</div>
			</li>
			<li class="ffield">
				<span class="label"><b>'.$lang['db_name'].'</b></span><div class="ffdd">'.Eleanor::Edit('name',$name,array('class'=>'f_text','tabindex'=>2)).'</div>
			</li>
			<li class="ffield">
				<span class="label"><b>'.$lang['db_user'].'</b></span><div class="ffdd">'.Eleanor::Edit('user',$user,array('class'=>'f_text','tabindex'=>3)).'</div>
			</li>
			<li class="ffield">
				<span class="label"><b>'.$lang['db_pass'].'</b></span><div class="ffdd">'.Eleanor::Edit('pass',$pass,array('class'=>'f_text','tabindex'=>4)).'</div>
			</li>
			<li class="ffield">
				<span class="label"><b>'.$lang['db_pref'].'</b></span><div class="ffdd">'.Eleanor::Edit('pref',$pref,array('class'=>'f_text','tabindex'=>5)).'</div>
			</li>
		</ul>
		<br />
		<h3 class="subhead">'.$lang['addonl'].'</h3>
		<ul class="reset formfield">
			<li class="ffield">
				<span class="label">'.$lang['addonl_'].'</span>
				</span><div class="ffdd">'.Eleanor::Items('languages',$languages,5,array('class'=>'f_text','tabindex'=>6)).'</div>
			</li>
		</ul>
		<div class="submitline">'.Eleanor::Control('s','hidden',session_id()).Eleanor::Button($lang['next'],'submit',array('class'=>'button','tabindex'=>7),2).'</div></form></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
}
if($error and !$text)
	$text=Eleanor::$Template->Message($error,'error');
Start($percent,$navi);
echo$text;