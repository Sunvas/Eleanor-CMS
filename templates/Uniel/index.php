<?php
/*
	Скелет основного шаблона.
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];?><!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
{head}
<script type="text/javascript" src="js/menu_multilevel.js"></script>
<link media="screen" href="<?php echo$theme?>style/main.css" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="favicon.ico" />
</head>

<body class="page_bg">
<div id="loading">
	<span><?php echo$ltpl['loading']?></span>
</div><script type="text/javascript">//<![CDATA[
$(function(){
	$("#loading").on("show",function(){
		$(this).css({
			left:Math.round(($(window).width()-$(this).width())/2),
			top:Math.round(($(window).height()-$(this).height())/2)
		});
	}).triggerHandler("show");
	$(window).resize(function(){
		$("#loading").triggerHandler("show");
	});
});//]]></script>
<?php
if(Eleanor::$Permissions->IsAdmin())
	include Eleanor::$root.'addons/blocks/block_adminheader.php';
?>
<div class="wrapper">
<div id="headerboxic"><div class="dleft"><div class="dright">
	<a class="logotype" href="<?php echo$GLOBALS['Eleanor']->Url->special?>"><img src="<?php echo$theme?>images/eleanorcms.png" alt="<?php echo Eleanor::$vars['site_name']?>" title="<?php echo Eleanor::$vars['site_name']?>" /></a>
	<span class="headbanner">
		<!-- Баннер 468x60-->
		<!-- <a href="link.html" title="Ваш баннер"><img src="<?php echo$theme?>images/spacer.png" alt="Ваш баннер" /></a> -->
	</span>
</div></div></div>

<div id="menuhead"><div class="dleft"><div class="dright">
<div class="language">
<?php
if(Eleanor::$vars['multilang'])
{
	$langs=Eleanor::$langs;
	unset($langs[Language::$main]);
	foreach($langs as $k=>$v)
		echo'<a href="',Eleanor::$filename,'?language=',$k,'" title="',$v['name'],'"><b>',substr($k,0,3),'</b></a>';
}
?>
</div>
<?php echo'<nav><ul class="topmenu">',include Eleanor::$root.'addons/menus/multiline.php'; ?>
</nav><script type="text/javascript">/*<![CDATA[*/ $(function(){ $(".topmenu").MultiLevelMenu(); });//]]></script>
</div></div></div>

<div class="container">
	<div class="mainbox">
<?php
$blocks=Blocks::Get(array('right','left','center_up','center_down'));
echo'<div id="maincol',$blocks['right'] ? 'R' : '','">
			<div class="baseblock"><div class="dtop"><div class="dbottom">
				<div class="dcont">',
				$blocks['center_up'],
				'<!-- CONTEXT LINKS -->{module}<!-- /CONTEXT LINKS -->',
				$blocks['center_down'],
				'</div>
			</div></div></div>
		</div>',$blocks['right'] ? '<div id="rightcol">'.$blocks['right'].'</div>' : '';
?>
	</div>
	<div id="leftcol">
<?php
	include Eleanor::$root.$theme.'Static/login.php';
	echo$blocks['left'];
?>
	</div>

	<div class="clr"></div>
</div>

<div id="footmenu"><div class="dleft"><div class="dright">
	<a title="<?php echo$ltpl['to_top']?>" onclick="scroll(0,0); return false" href="#" class="top-top"><img src="<?php echo$theme?>images/top-top.png" alt="" /></a>
	<span class="menu"><?php echo join(include Eleanor::$root.'addons/menus/single.php'); ?></span>
</div></div></div>

<div id="footer"><div class="dleft"><div class="dright">
	<div class="count">
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 60px;">  <a href="http://validator.w3.org/check?uri=referer"><img src="<?php echo$theme?>images/html5_valid.png" alt="Valid HTML 5" title="Valid HTML 5" width="60" height="31" /></a></span>

	</div>
	<!-- КОПИРАЙТЫ -->
	<span class="copyright">&copy; Copyright</span>
	<div class="clr"></div>
</div></div></div>
<div id="syscopyright">
	<span class="centroarts"><a href="http://centroarts.com" title="Шаблон разработан студией CENTROARTS.com">Designed by CENTROARTS.com</a></span>
	<div><?php
#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
echo'Powered by '.ELEANOR_COPYRIGHT?></div>
[page status]	<div>{page status}</div>
[/page status][debug]	<div>{debug}</div>
[/debug]</div>
</div>

</body>
</html>