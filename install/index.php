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
define('INSTALL',true);
define('CMS',true);
require'./init.php';
$step=isset($_GET['step']) ? (int)$_GET['step'] : 1;
$percent=0;
$title=$navi=$error='';
Eleanor::StartSession(isset($_REQUEST['s']) ? $_REQUEST['s'] : '','INSTALLSESSION');
if(isset($_SESSION['lang']))
{
	Language::$main=$_SESSION['lang'];
	Eleanor::$Language->Change();
	$lang=Eleanor::$Language->Load('install/lang/install-*.php','install');
}
elseif($step>2)
	$step=1;
switch($step)
{
	case 5:
		if(isset($_GET['tzo']))
			$_SESSION['tzo']=(int)$_GET['tzo'];
		if(isset($_GET['dst']))
			$_SESSION['dst']=(bool)$_GET['dst'];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.(isset($_POST['update']) ? 'update' : 'install').'.php?s='.session_id());
		die;
	case 4:
		if(isset($_POST['agree_sanc']))
			$_SESSION['agree_sanc']=true;
		if(isset($_SESSION['agree_sanc']))
		{
			$percent=40;
			$title=$navi=$lang['srequirements'];
			$can=true;
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><form method="post" action="index.php?s='.session_id().'&amp;step=5"><table class="tablespec"><tr class="tshead"><th>'.$lang['parametr'].'</th><th>'.$lang['value'].'</th><th>'.$lang['status'].'</th></tr><tr class="tsline"><td class="label">'.$lang['php_version'].'</td><td class="sense">'.PHP_VERSION.'</td>';

			if(version_compare(PHP_VERSION,'5.3.0','<'))
			{
				$text.='<td><img src="'.Eleanor::$Template->default['theme'].'/images/warn.png" alt="'.$lang['error'].'" /></td>';
				$can=false;
			}
			else
				$text.='<td><img src="'.Eleanor::$Template->default['theme'].'/images/ok.png" alt="OK" /></td>';

			$text.='</tr><tr class="tsline"><td class="label">'.$lang['php_gd'].'</td>';
			if(function_exists('imagefttext'))
				$text.='<td class="sense">+</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/ok.png" alt="OK" /></td>';
			else
			{
				$text.='<td class="sense">&minus;</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/warn.png" alt="'.$lang['error'].'" /></td>';
				$can=false;
			}

			$text.='</tr><tr class="tsline"><td class="label">'.$lang['php_ioncube'].'</td>';
			if(extension_loaded('ionCube Loader'))
				$text.='<td class="sense">+</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/ok.png" alt="OK" /></td>';
			else
				$text.='<td class="sense">&minus;</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/warn.png" alt="!!!" /></td>';

			$text.='</tr><tr class="tsline"><td class="label">'.$lang['php_dom'].'</td>';
			if(function_exists('dom_import_simplexml'))
				$text.='<td class="sense">+</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/ok.png" alt="OK" /></td>';
			else
				$text.='<td class="sense">&minus;</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/warn.png" alt="!!!" /></td>';

			$text.='</tr><tr class="tsline"><td class="label">'.$lang['db_drivers'].'</td>';
			if(function_exists('mysqli_connect'))
				$text.='<td class="sense">MySQLi</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/ok.png" alt="OK" /></td>';
			else
			{
				$text.='<td>'.$lang['not_find'].'</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/warn.png" alt="'.$lang['error'].'" /></td>';
				$can=false;
			}

			if(function_exists('apache_get_modules'))
			{
				$text.='</tr><tr class="tsline"><td class="label">'.$lang['mod_rewrite'].'</td>';
				if(in_array('mod_rewrite',apache_get_modules()))
					$text.='<td class="sense">+</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/ok.png" alt="OK" /></td>';
				else
					$text.='<td class="sense">&minus;</td><td><img src="'.Eleanor::$Template->default['theme'].'/images/warn.png" alt="!!!" /></td>';
			}
			$text.='</tr><tr class="tsline"><td class="label" colspan="3">'.$lang['mysqlver'].'</td></tr></table>'
				.($can ? '<div class="submitline">'.Eleanor::Button($lang['install'],'submit',array('class'=>'button','name'=>'install','tabindex'=>1)).' '.Eleanor::Button($lang['update'],'submit',array('class'=>'button','name'=>'update','tabindex'=>2)).'</div>' : '')
				.'</form></div><div class="wpbtm"><b>&nbsp;</b></div></div><script type="text/javascript">//<![CDATA[
$(function(){
	$("form:first").attr("action",function(){
		var today=new Date,
			yr=today.getFullYear(),
			dst_start=new Date("March 14, "+yr+" 02:00:00"),
			dst_end=new Date("November 07, "+yr+" 02:00:00"),
			day=dst_start.getDay();
		dst_start.setDate(14-day);
		day=dst_end.getDay();
		dst_end.setDate(7-day);

		return this.action+"&tzo="+(new Date()).getTimezoneOffset()+"&dst="+(today>=dst_start && today < dst_end ? 1 : 0);
	});
});//]]></script>';
			break;
		}
		$error=$lang['you_must_sagree'];
	case 3:
		if(isset($_POST['agree_lic']))
			$_SESSION['agree_lic']=true;
		if(isset($_SESSION['agree_lic']))
		{
			$title=$navi=$lang['sanctions'];
			$percent=25;
			$license=is_file($f=Eleanor::$root.'addons/license/sanctions-'.Eleanor::$Language.'.html') ? file_get_contents($f) : file_get_contents(Eleanor::$root.'addons/license/sanctions-'.LANGUAGE.'.html');
			$license=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$license);
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">'
			.($error ? Eleanor::$Template->Message($error) : '')
			.'<form method="post" action="index.php?s='.session_id().'&amp;step=4"><div class="textarea license">'.$license.'</div><p>'.Eleanor::Check('agree_sanc',!empty($_SESSION['agree_sanc']),array('id'=>'agree','tabindex'=>1)).'<label for="agree"> '.$lang['i_am_agree_sanc'].'</label><a href="../addons/license/sanctions-'.Eleanor::$Language.'.html" style="float:right" target="_blank">'.$lang['print'].' <img src="../templates/Audora/images/print.png" /></a></p><div class="submitline">'.Eleanor::Button($lang['back'],'button',array('class'=>'button','onclick'=>'history.go(-1)','tabindex'=>3),2).Eleanor::Button($lang['next'],'submit',array('class'=>'button','tabindex'=>2),2).'</div></form>
</div></div><div class="wpbtm"><b>&nbsp;</b></div></div><script type="text/javascript">//<![CDATA[
$(function(){
	$("#agree").click(function(){
		$(":submit").prop("disabled",!$(this).prop("checked"));
	}).triggerHandler("click");
});//]]></script>';
			break;
		}
		$error=$lang['you_must_lagree'];
	case 2:
		$can=isset($lang);
		if($step==2 and isset($_GET['lang'],Eleanor::$langs[$_GET['lang']]) and (!isset($_SESSION['lang']) or $_SESSION['lang']!=$_GET['lang']))
		{
			Language::$main=$_SESSION['lang']=(string)$_GET['lang'];
			Eleanor::$Language->Change();
			$lang=Eleanor::$Language->Load('install/lang/install-*.php','install');
			$can=true;
		}
		if($can)
		{
			$title=$navi=$lang['license'];
			$percent=10;
			$license=is_file($f=Eleanor::$root.'addons/license/license-'.Eleanor::$Language.'.html') ? file_get_contents($f) : file_get_contents(Eleanor::$root.'addons/license/license-'.LANGUAGE.'.html');
			$license=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$license);
			$text='<div class="wpbox wpbwhite"><div class="wptop"><b>&nbsp;</b></div><div class="wpmid"><div class="wpcont">'
			.($error ? Eleanor::$Template->Message($error) : '')
			.'<form method="post" action="index.php?s='.session_id().'&amp;step=3"><div class="textarea license">'.$license.'</div><p>'.Eleanor::Check('agree_lic',!empty($_SESSION['agree_lic']),array('id'=>'agree','tabindex'=>1)).'<label for="agree"> '.$lang['i_am_agree_lic'].'</label><a href="../addons/license/license-'.Eleanor::$Language.'.html" style="float:right" target="_blank">'.$lang['print'].' <img src="../templates/Audora/images/print.png" /></a></p><div class="submitline">'.Eleanor::Button($lang['back'],'button',array('class'=>'button','onclick'=>'history.go(-1)','tabindex'=>3),2).Eleanor::Button($lang['next'],'submit',array('class'=>'button','tabindex'=>2),2).'</div></form>
</div></div><div class="wpbtm"><b>&nbsp;</b></div></div><script type="text/javascript">//<![CDATA[
$(function(){
	$("#agree").click(function(){
		$(":submit").prop("disabled",!$(this).prop("checked"));
	}).triggerHandler("click");
});//]]></script>';
			break;
		}
	default:
		$_SESSION=array();
		$langs='';
		$l=isset($_GET['lang']) ? (string)$_GET['lang'] : false;
		$langsel=false;
		foreach(Eleanor::$langs as $k=>&$v)
		{
			if($l and !$langsel and strncasecmp($l,$k,min(strlen($l),strlen($k)))==0)
			{
				$l=$k;
				$langsel=true;
			}
			$langs.='<a href="index.php?s='.session_id().'&step=2&amp;lang='.$k.'" title="'.$v['sel'].'"><img src="../images/lang_flags/'.$k.'-big.png" /><span><b>'.$v['name'].'</b><br />'.$v['sel'].'</span></a>';
		}
		if($langsel or $l)
			$percent=5;
		else
		{
			$percent=0;
			reset(Eleanor::$langs);
			$l=key(Eleanor::$langs);
			$head=array(
				'lang'=>'<script type="text/javascript">//<![CDATA[
var langtry=["language","Language","userLanguage","systemLanguage"],
	lang="";
for(var i in langtry)
	if(typeof navigator[langtry[i]]!="undefined")
	{
		window.location.href="'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'index.php?s='.session_id().'&lang="+navigator[langtry[i]].substring(0,2);
		break;
	}
//]]></script>'
			);
		}
		Language::$main=$l;
		$lang=Eleanor::$Language->Load('install/lang/install-*.php','install');
		$navi=$lang['lang_select'];

		$title=$lang['welcome'];
		$text='<div class="selectlang">'.$langs.'</div>';
}
Start($percent,$navi);
echo$text;