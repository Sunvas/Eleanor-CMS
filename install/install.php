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
define('ROOT_FILE',__file__);
define('CMS',true);
define('INSTALL',true);
require'./init.php';

$step=isset($_GET['step']) ? (int)$_GET['step'] : 1;
Eleanor::StartSession(isset($_REQUEST['s']) ? $_REQUEST['s'] : '','INSTALLSESSION');
if(empty($_SESSION['agree_sanc']) or empty($_SESSION['agree_lic']))
	return GoAway(PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'index.php?s='.session_id());
if(isset($_SESSION['lang']))
{
	Language::$main=$_SESSION['lang'];
	Eleanor::$Language->Change();
}
$lang=Eleanor::$Language->Load('install/lang/install-*.php','install');
$percent=0;
$error=$text=$navi='';
if($ability=Install::CheckErrors())
{
	$navi=$title=$lang['error'];
	$text='';
	foreach($ability as &$v)
		$text.=Eleanor::$Template->Message($v,'error');
}
else
	switch($step)
	{
		case 5:
			$navi=$lang['finish'];
			$percent=100;
			Eleanor::$Db=new Db($_SESSION);
			define('P',Eleanor::$Db->Escape($_SESSION['prefix'],false));
			date_default_timezone_set($_SESSION['timezone']);
			Eleanor::$Db->SyncTimeZone();
			Eleanor::LoadOptions('mailer',false);
			Eleanor::$vars['site_name']=$_SESSION['sitename'];#Заплаточка для маилера
			$path=preg_replace('#install/$#','',Eleanor::$site_path);
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
				P,
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
			file_put_contents(Eleanor::$root.'install/install.lock',1);
			file_put_contents(Eleanor::$root.'config_general.bak',str_replace($from,$to,file_get_contents(Eleanor::$root.'config_general.bak')));
			if(file_exists(Eleanor::$root.'config_general.php'))
				unlink(Eleanor::$root.'config_general.php');
			$ranamed=rename(Eleanor::$root.'config_general.bak',Eleanor::$root.'config_general.php');
			file_put_contents(
				Eleanor::$root.'robots.txt',
				str_replace(
					array(
						'{domain}',
						'{protocol}',
						'{site_path}'
					),
					array(
						Eleanor::$domain,
						PROTOCOL,
						$path,
					),
					file_get_contents(Eleanor::$root.'robots.txt')
				)
			);
			file_put_contents(
				Eleanor::$root.'.htaccess',
				str_replace(
					array(
						'{full}',
						'{shost}',
						'{sprotocol}'
					),
					array(
						PROTOCOL.Eleanor::$punycode.$path,
						preg_quote(Eleanor::$punycode),
						preg_quote(PROTOCOL),
					),
					file_get_contents(Eleanor::$root.'.htaccess')
				)
			);
			$url1='http://'.Eleanor::$domain.$path.'index.php';
			$url2='http://'.Eleanor::$domain.$path.'admin.php';
			$title=$lang['install_finished'];
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont"><div class="information" align="center"><h4 style="color: green;">'.$title.'</h4></div>'.($ranamed ? '<div class="information">'.$lang['inst_fin_text'].'</div>' : Eleanor::$Template->Message($lang['inst_err_text'],'error'))
			.'<div class="information" id="bm" style="display:none;text-align:center"><a href="#" onclick="this.style.behavior=\'url(#default#homepage)\';this.setHomePage(\'http://eleanor-cms.ru\'); return false;">'.$lang['sethomepage'].'</a><br /><a href="#" rel="sidebar" onclick="window.external.AddFavorite(location.href,\'http://eleanor-cms.ru\');return false;">'.$lang['addfavourite'].'</a></div><script type="text/javascript">/*<![CDATA[*/if(CORE.browser.ie) $("#bm").show();//]]></script><div class="submitline">'
			.sprintf($lang['links'],$url1,$url2).'</div></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
			try
			{
				#Внимание! Отправка e-mail-а осуществляется в информативных целях и НЕ содержит никакой конфиденциальной информации
				Email::Simple(
					'newsite@eleanor-cms.ru',
					'Новый сайт: '.$_SESSION['sitename'],
					'URL: http://'.Eleanor::$domain.$path.'.<br />Encoding: '.CHARSET
				);
			}
			catch(EE $E){}
			break;
		break;
		case 4:
			$navi=$title=$lang['create_admin'];
			$percent=90;
			$error='';
			Eleanor::$Db=new Db($_SESSION);
			define('P',Eleanor::$Db->Escape($_SESSION['prefix'],false));
			define('USERS_TABLE',P.'users');
			date_default_timezone_set($_SESSION['timezone']);
			Eleanor::$Db->SyncTimeZone();
			if(isset($_POST['name'],$_POST['pass'],$_POST['email']))
				do
				{
					if($_POST['pass']!=$_POST['rpass'])
						$error=$lang['pass_mismatch'];
					if(!Strings::CheckEmail($_POST['email']))
						$error=($error ? '<br />' : '').$lang['err_email'];
					if($error)
						break;
					Eleanor::LoadOptions('blocker',false);
					try
					{
						UserManager::Add(array(
							'name'=>$_POST['name'],
							'_password'=>$_POST['pass'],
							'email'=>$_POST['email'],
							'groups'=>array(1),
							'avatar_location'=>'av-1.png',
							'avatar_type'=>'upload',
						));
					}
					catch(EE$E)
					{
						$mess=$E->getMessage();
						switch($mess)
						{
							case'PASS_TOO_SHORT':
								$error=$lang['PASS_TOO_SHORT']($E->extra['min'],$E->extra['you']);
							break;
							default:
								$error=isset($lang[$mess]) ? $lang[$mess] : $mess;
						}
						break;
					}
					header('Location: install.php?step=5&s='.session_id());
					die;
				}while(false);
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">'.CreateAdmin($error);
		break;
		case 3:
			if($_SERVER['REQUEST_METHOD']=='POST')
				unset($_SESSION['tables'],$_SESSION['values']);
			$navi=$lang['installing'];
			$percent=70;
			Eleanor::$Db=new Db($_SESSION);
			$title=$lang['install'];
			$text=DoInstall();
		break;
		case 2:
			$title=$navi=$lang['already_to_install'];
			$percent=60;
			if(isset($_POST['host'],$_POST['name'],$_POST['user'],$_POST['pass'],$_POST['pref'],$_POST['sitename'],$_POST['email'],$_POST['timezone']))
			{
				do
				{
					if(!Strings::CheckEmail($_POST['email']))
					{
						$error=$lang['err_email'];
						break;
					}
					if(empty($_POST['sitename']))
						$_POST['sitename']='New site on Eleanor CMS';
					$a=array(
						'host'=>(string)$_POST['host'],
						'user'=>(string)$_POST['user'],
						'pass'=>(string)$_POST['pass'],
						'prefix'=>(string)$_POST['pref'],
						'db'=>(string)$_POST['name'],
						'dostep'=>1,
						'sitename'=>(string)$_POST['sitename'],
						'email'=>(string)$_POST['email'],
						'furl'=>isset($_POST['furl']),
						'timezone'=>(string)$_POST['timezone'],
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
					{
						$error=$E->getMessage();
						break;
					}
					if(!Install::CheckMySQLVersion())
					{
						$error=$lang['low_mysql'];
						break;
					}
					$_SESSION=$a+$_SESSION;
					$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div>
					<div class="wpmid"><div class="wpcont">
					<form method="post" action="install.php?step=3">
					<h3 class="subhead">'.$lang['db'].'</h3>
					<ul class="reset formfield">
					<li class="ffield"><span class="label">'.$lang['db_host'].'</span><div class="ffdd"><h4>'.(string)$_POST['host'].'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['db_name'].'</span><div class="ffdd"><h4>'.htmlspecialchars((string)$_POST['name'],ELENT,CHARSET).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['db_user'].'</span><div class="ffdd"><h4>'.htmlspecialchars((string)$_POST['user'],ELENT,CHARSET).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['db_pass'].'</span><div class="ffdd"><h4>'.htmlspecialchars((string)$_POST['pass'],ELENT,CHARSET).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['db_pref'].'</span><div class="ffdd"><h4>'.htmlspecialchars((string)$_POST['pref'],ELENT,CHARSET).'</h4></div></li>
					</ul>
					<br />
					<h3 class="subhead">'.$lang['gen_data'].'</h3>
					<ul class="reset formfield">
					<li class="ffield"><span class="label">'.$lang['sitename'].'</span><div class="ffdd"><h4>'.htmlspecialchars((string)$_POST['sitename'],ELENT,CHARSET).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['email'].'</span><div class="ffdd"><h4>'.htmlspecialchars((string)$_POST['email'],ELENT,CHARSET).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['furl'].'</span><div class="ffdd"><h4>'.($a['furl'] ? $lang['yes'] : $lang['no']).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['addl'].'</span><div class="ffdd"><h4>'.($langs ? implode(', ',$langs) : $lang['no']).'</h4></div></li>
					<li class="ffield"><span class="label">'.$lang['timezone'].'</span><div class="ffdd"><h4>'.$a['timezone'].'</h4></div></li>
					</ul>
					<div class="submitline">'.Eleanor::Input('s',session_id(),array('type'=>'hidden'))
					.Eleanor::Button($lang['back'],'button',array('class'=>'button','onclick'=>'history.go(-1)','tabindex'=>2),2)
					.Eleanor::Button($lang['install_me'],'submit',array('class'=>'button','tabindex'=>1),2).'</div></form>
					</div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
				}while(false);
				if(!$error)
					break;
			}
		default:
			$navi=$title=$lang['get_data'];
			$percent=50;
			$agm=function_exists('apache_get_modules');
			$canurl=$agm && in_array('mod_rewrite',apache_get_modules());
			$host=isset($_POST['host']) ? (string)$_POST['host'] : 'localhost';
			$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
			$user=isset($_POST['user']) ? (string)$_POST['user'] : '';
			$pass=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
			$pref=isset($_POST['pref']) ? (string)$_POST['pref'] : 'el_';
			$sitename=isset($_POST['sitename']) ? (string)$_POST['sitename'] : '';
			$langs=isset($_POST['languages']) ? (array)$_POST['languages'] : array();
			$email=isset($_POST['email']) ? (string)$_POST['email'] : '';
			$furl=$_SERVER['REQUEST_METHOD']=='POST' ? isset($_POST['furl']) : $canurl;
			if(isset($_SESSION['tzo'],$_SESSION['dst']))
			{				$tzo=array();
				$tal=timezone_abbreviations_list();
				foreach($tal as &$tv)
					foreach($tv as &$v)
						if($v['offset']/60==-$_SESSION['tzo'] and $v['dst']==$_SESSION['dst'])
							$tzo[]=$v['timezone_id'];
				#Сюда можно добавить пояса по-умолчанию
				if(in_array('Europe/Kiev',$tzo))
					$tzo='Europe/Kiev';
				elseif(in_array('Europe/Moscow',$tzo))
					$tzo='Europe/Moscow';
				else
					$tzo=reset($tzo);
			}
			else
				$tzo=date_default_timezone_get();
			$timezone=isset($_POST['timezone']) ? (string)$_POST['timezone'] : $tzo;
			$languages='';
			foreach(Eleanor::$langs as $k=>&$v)
				if($k!=Language::$main)
					$languages.=Eleanor::Option($v['name'],$k,in_array($k,$langs));
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">'
			.($error ? Eleanor::$Template->Message($error) : '')
			.'<form method="post" action="install.php?step=2">
			<h3 class="subhead">'.$lang['db'].'</h3>
			<ul class="reset formfield">
				<li class="ffield">
					<span class="label"><b>'.$lang['db_host'].'</b></span><div class="ffdd">'.Eleanor::Input('host',$host,array('class'=>'f_text','tabindex'=>1)).'</div>
				</li>
				<li class="ffield">
					<span class="label"><b>'.$lang['db_name'].'</b></span><div class="ffdd">'.Eleanor::Input('name',$name,array('class'=>'f_text','tabindex'=>2)).'</div>
				</li>
				<li class="ffield">
					<span class="label"><b>'.$lang['db_user'].'</b></span><div class="ffdd">'.Eleanor::Input('user',$user,array('class'=>'f_text','tabindex'=>3)).'</div>
				</li>
				<li class="ffield">
					<span class="label"><b>'.$lang['db_pass'].'</b></span><div class="ffdd">'.Eleanor::Input('pass',$pass,array('class'=>'f_text','tabindex'=>4)).'</div>
				</li>
				<li class="ffield">
					<span class="label"><b>'.$lang['db_pref'].'</b></span><div class="ffdd">'.Eleanor::Input('pref',$pref,array('class'=>'f_text','tabindex'=>5)).'
					<span class="small" style="color:red">'.$lang['db_prefinfo'].'</span>
					</div>
				</li>
			</ul>
			<br />
			<h3 class="subhead">'.$lang['gen_data'].'</h3>
			<ul class="reset formfield">
				<li class="ffield">
					<span class="label"><b>'.$lang['sitename'].'</b></span><div class="ffdd">'.Eleanor::Input('sitename',$sitename,array('class'=>'f_text','tabindex'=>6)).'</div>
				</li>
				<li class="ffield">
					<span class="label"><b>'.$lang['email'].'</b></span><div class="ffdd">'.Eleanor::Input('email',$email,array('class'=>'f_text','tabindex'=>7)).'</div>
				</li>'.($canurl || !$agm ? '
				<li class="ffield">
					<span class="label"><b>'.$lang['furl'].'</b></span><div class="ffdd">'.Eleanor::Check('furl',$furl,array('tabindex'=>8)).'</div>
				</li>' : '').'
				<li class="ffield">
					<span class="label"><b>'.$lang['timezone'].'</b></span><div class="ffdd">'.Eleanor::Select('timezone',Types::TimeZonesOptions($timezone),array('class'=>'f_text','tabindex'=>9)).'</div>
				</li>
				<li class="ffield">
					<span class="label"><b>'.$lang['addl'].'</b><br />
					<span class="small">'.$lang['addl_'].'</span>
					</span><div class="ffdd">'.Eleanor::Items('languages',$languages,array('class'=>'f_text','tabindex'=>10,'size'=>2)).'</div>
				</li>
			</ul>
			<div class="submitline">'.Eleanor::Input('s',session_id(),array('type'=>'hidden')).Eleanor::Button($lang['next'],'submit',array('class'=>'button','tabindex'=>11),2).'</div></form></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
	}
Start($percent,$navi);
echo$text;

function DoInstall()
{global$lang,$percent;
	$do=isset($_GET['do']) ? (int)$_GET['do'] : 1;
	$do=$_SESSION['dostep']<$do ? $_SESSION['dostep'] : $do;
	date_default_timezone_set($_SESSION['timezone']);
	Eleanor::$Db->SyncTimeZone();
	$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">';
	switch($do)
	{
		case 1:#Создание таблиц
			if(isset($_SESSION['tables']))
				$temp=array($lang['skip']);
			else
			{
				$prefix=Eleanor::$Db->Escape($_SESSION['prefix'],false);
				require Eleanor::$root.'install/data_install/tables.php';
				if(empty($tables))
				{
					$text=Eleanor::$Template->Message($lang['error_cont']);
					break;
				}
				$temp=array();
				foreach($tables as $k=>&$v)
				{
					$dbe=false;
					try
					{
						Eleanor::$Db->Query($v);
					}
					catch(EE $E)
					{
						$dbe=$E->getMessage();
					}
					if(!is_int($k))
						$temp[]='<span style="color:'.($dbe ? 'red' : 'green').'" title="'.($dbe ? $dbe : 'OK').'">'.$k.'</span>';
				}
				$_SESSION['tables']=true;
				unset($_SESSION['values']);
			}
			$text.='<div class="information"><h4>'.$lang['creating_tables'].'</h4>'.join(', ',$temp).'</div>';
			$url='install.php?step=3&amp;s='.session_id().'&amp;do='.++$do;
		break;
		case 2:#Создание записей в таблицы
			$percent=80;
			if(isset($_SESSION['values']))
				$temp=array($lang['skip']);
			else
			{
				$prefix=Eleanor::$Db->Escape($_SESSION['prefix'],false);
				$email=Eleanor::$Db->Escape($_SESSION['email'],false);
				$sitename=Eleanor::$Db->Escape($_SESSION['sitename'],false);
				$furl=$_SESSION['furl'];
				$timezone=$_SESSION['timezone'];
				$mainlang=Language::$main;
				$languages=$_SESSION['languages']+array('m'=>$mainlang);
				require Eleanor::$root.'install/data_install/insert.php';
				if(empty($insert))
				{
					$text=Eleanor::$Template->Message($lang['error_cont']);
					break;
				}
				$temp=array();
				foreach($insert as $k=>$v)
				{
					$dbe=false;
					try
					{
						Eleanor::$Db->Query($v);
					}
					catch(EE$E)
					{
						$dbe=$E->getMessage();
					}
					if(!is_int($k))
						$temp[]='<span style="color:'.($dbe ? 'red' : 'green').'" title="'.($dbe ? $dbe : 'OK').'">'.$k.'</span>';
				}
				$_SESSION['values']=true;
			}
			$text.='<div class="information"><h4>'.$lang['inserting_v'].'</h4>'.join(', ',$temp).'</div>';
			$url='install.php?step=4&amp;s='.session_id();
		break;
		default:
			die('Unknown event!');
	}
	$_SESSION['dostep']=$do;
	Eleanor::$Template->RedirectScreen($url,5);
	return$text.'<div class="submitline"><a href="'.$url.'">'.$lang['press_here'].'</a></div></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
}

function CreateAdmin($error='')
{global$lang;
	$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
	$email=isset($_POST['email']) ? (string)$_POST['email'] : $_SESSION['email'];
	return ($error ? Eleanor::$Template->Message($error) : '')
	.'<form method="post" action="install.php?step=4" onsubmit="if($(\'#pass\').val()!=$(\'#rpass\').val()){alert(\''.htmlspecialchars($lang['pass_mismatch'],ELENT,CHARSET).'\'); return false;}">
	<ul class="reset formfield">
		<li class="ffield"><span class="label"><b>'.$lang['a_name'].'</b></span><div class="ffdd">'.Eleanor::Input('name',$name,array('class'=>'f_text','style'=>'width:260px','tabindex'=>1)).'</div></li>
		<li class="ffield"><span class="label"><b>'.$lang['db_pass'].'</b></span><div class="ffdd">'.Eleanor::Input('pass',isset($_POST['pass']) ? (string)$_POST['pass'] : '',array('type'=>'password','class'=>'f_text','style'=>'width:260px','id'=>'pass','tabindex'=>2)).'</div></li>
		<li class="ffield"><span class="label"><b>'.$lang['a_rpass'].'</b></span><div class="ffdd">'.Eleanor::Input('rpass',isset($_POST['rpass']) ? (string)$_POST['rpass'] : '',array('type'=>'password','class'=>'f_text','style'=>'width:260px','id'=>'rpass','tabindex'=>3)).'</div></li>
		<li class="ffield"><span class="label"><b>'.$lang['a_email'].'</b></span><div class="ffdd">'.Eleanor::Input('email',$email,array('class'=>'f_text','style'=>'width:260px','tabindex'=>4)).'</div></li>
	</ul>
	<div class="submitline"><input class="button" type="submit" value="'.$lang['do_create_admin'].'" name="task_button" tabindex="5" /></div>'
	.Eleanor::Input('s',session_id(),array('type'=>'hidden')).'</form></div></div><div class="wpbtm"><b>&nbsp;</b></div></div>';
}