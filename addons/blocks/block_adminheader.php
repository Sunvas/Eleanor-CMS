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
if(!defined('CMS'))die;

$AL=Eleanor::LoadLogin(Eleanor::$services['admin']['login']);
if($AL->IsUser())
{
	global$Eleanor;
	$af=Eleanor::$services['admin']['file'];
	$hfu=Eleanor::$Cache->Get('ahfu'.Language::$main,false);
	if($hfu===false)
	{
		$manag='';
		$premodules=$modules=array();
		require Eleanor::$root.'addons/admin/info.php';
		$di='images/modules/default-small.png';
		foreach($info as $k=>&$v)
		{
			if(!isset($v['services']['admin']) or !empty($v['hidden']))
				continue;
			$img=$di;
			if($v['image'])
			{
				$v['image']='images/modules/'.str_replace('*','small',$v['image']);
				if(is_file(Eleanor::$root.$v['image']))
					$img=$v['image'];
			}
			$manag.='<li><a href="'.$af.'?section=management&amp;module='.urlencode($k).'"><span><img src="'.$img.'" alt="" />'.$v['title'].'</span></a></li>';
		}

		$R=Eleanor::$Db->Query('SELECT `sections`,`title_l` `title`,`descr_l` `descr`,`protected`,`image` FROM `'.P.'modules` WHERE `services`=\'\' OR `services` LIKE \'%,admin,%\' AND `active`=1');
		while($a=$R->fetch_assoc())
		{
			$img=$di;
			if($a['image'])
			{
				$a['image']='images/modules/'.str_replace('*','small',$a['image']);
				if(is_file(Eleanor::$root.$a['image']))
					$img=$a['image'];
			}

			$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
			$a['descr']=$a['descr'] ? Eleanor::FilterLangValues((array)unserialize($a['descr'])) : '';

			$sections=$a['sections'] ? (array)unserialize($a['sections']) : false;
			if($sections)
			{
				foreach($sections as $k=>&$v)
					if(isset($v['']))
						$v=reset($v['']);
					elseif(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
						$v=reset($v[Language::$main]);
					elseif(isset($v[LANGUAGE]))
						$v=reset($v[LANGUAGE]);
					else
						continue;
				$titles[]=$a['title'];
				$premodules[]='<li><a href="'.$af.'?section=modules&amp;module='.urlencode(reset($sections)).'" title="'.$a['descr'].'"><span><img src="'.$img.'" alt="" />'.$a['title'].'</span></a></li>';
			}
		}
		asort($titles,SORT_STRING);
		foreach($titles as $k=>&$v)
			$modules[]=$premodules[$k];

		Eleanor::$Cache->Put('hfu'.Language::$main,array($manag,$modules),3600,false);
	}
	else
		list($manag,$modules)=$hfu;

	$modcnt=count($modules);
	if($three=$modcnt>23)
		$modcnt_d=ceil($modcnt/3);
	else
		$modcnt_d=$modcnt>10 ? ceil($modcnt/2) : $modcnt;

	$GLOBALS['head'][]='<link rel="stylesheet" media="screen" type="text/css" href="templates/'.Eleanor::$services['admin']['theme'].'/style/adminmenu.css" />';
	$GLOBALS['jscripts'][]='js/admin.js';

	echo'<div id="adminblockf"><div id="subm1" class="adminsubmenu';
	if($modcnt>10)
		echo $three ? ' threecol' : ' twocol';
	echo'"><div class="colomn"><ul class="reset">',
	join(array_slice($modules,0,$modcnt_d)),
	'</ul></div>';

	if($modcnt>10)
		echo'<div class="colomn"><ul class="reset">',
		join(array_slice($modules,$modcnt_d,$modcnt_d)),
		'</ul></div>';

	if($three>10)
		echo'<div class="colomn"><ul class="reset">',
		join(array_slice($modules,$modcnt_d*2)),
		'</ul></div>';

	unset($module,$modcnt,$modcnt_d);

	$ladmin=Eleanor::$Language['admin'];
	echo'<div class="clr"></div></div>
	<script type="text/javascript">//<![CDATA[
$(function(){
	$("a.mlink").MainMenu();
	var ab=$("#adminblockh"),
		h=$("#adminblockf").height()+"px",
		abf=$("#adminblockf");
	if(localStorage.getItem("ahfu"))
	{
		ab.addClass("active");
		abf.addClass("fixmenupanel").next().css("margin-top",h)
	}

	$("#adminblockh a").click(function(){
		abf.toggleClass("fixmenupanel");
		if(ab.is(".active"))
		{
			ab.removeClass("active");
			abf.next().css("margin-top",0);
			scroll(0,0);
			localStorage.removeItem("ahfu");
		}
		else
		{
			ab.addClass("active");
			abf.next().css("margin-top",h);
			localStorage.setItem("ahfu","1");
		}
		return false;
	});
})//]]></script>
	<div class="adminmenupanel">
	<div class="backtoadmin" id="adminblockh"><a href="#"></a></div>
	<a href="'.$af.'" class="logotypepanel"><img src="templates/'.Eleanor::$services['admin']['theme'].'/images/eleanorcms_menu.png" alt="Eleanor CMS" /></a>
	<ul class="hmenu">
		<li><a class="link" href="'.$af.'?section=general"><span>'.$ladmin['adminka'].'</span></a></li>
		<li><a class="mlink" href="'.$af.'?section=modules" data-rel="#subm1"><span>'.$ladmin['modules'].'</span></a></li>
		<li><a class="mlink" href="'.$af.'?section=management" data-rel="#subm2"><span>'.$ladmin['management'].'</span></a></li>
		<li><a class="link" href="'.$af.'?section=options"><span>'.$ladmin['options'].'</span></a></li>
	</ul><div id="subm2" class="adminsubmenu"><ul class="reset">'.$manag.'</ul></div></div></div>';
}