<?php
/*
	ќбщий шаблон админки
*/
if(!defined('CMS'))die;
global$Eleanor,$jscripts;
$jscripts[]='js/admin.js';?><!DOCTYPE html><html><head>{head}
<link rel="shortcut icon" href="favicon.ico" />
<link media="screen" href="<?php echo$theme?>style/styles.css" type="text/css" rel="stylesheet" />
<link media="screen" href="<?php echo$theme?>style/main.css" type="text/css" rel="stylesheet" />
<style type="text/css">
	.pagebg {
		background-image: url(<?php echo$CONFIG['imagebg']?>);
		background-color: <?php echo$CONFIG['colorbg']?>;
		background-repeat: <?php echo$CONFIG['bgrepeat']?>;
		background-position: <?php echo$CONFIG['positionimg']?>;
		background-attachment: <?php echo$CONFIG['bgattachment'] ? 'scroll' : 'fixed'?>;
	}
<?php echo $CONFIG['sizethm']=='r' ? '.wrapper { width: 90%; max-width: 1400px; min-width: 996px; }' : '.wrapper { width: 996px; }';?>
</style>

</head>

<body class="pagebg">
<div id="loading"><div>&nbsp;</div></div>
<script type="text/javascript">//<![CDATA[
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
	<div class="wrapper">
		<div class="elh"><div class="elh"><div class="elh">
			<div class="elhead">
				<h1><a href="http://eleanor-cms.ru" title="Eleanor CMS" target="_blank">Eleanor CMS</a></h1>
				<div><ul class="reset">
						<li class="bnt1"><a href="<?php echo Eleanor::$services['admin']['file']?>?logout=true"><img src="<?php echo$theme?>images/spacer.gif" title="<?php echo Eleanor::$Language['tpl']['exit']?>" alt="" /></a></li>
						<li class="bnt2">
							<h3><?php echo'<a href="'.$Eleanor->Url->file.'?'.$Eleanor->Url->Construct(array('section'=>'management','module'=>'users','edit'=>Eleanor::$Login->GetUserValue('id')),false).'">'.Eleanor::$Login->GetUserValue('name').'</a>'?></h3>
							<span class="small">
<?php
$ug=Eleanor::GetPermission('title_l');
$ug=Eleanor::FilterLangValues(reset($ug));
echo $ug;
?></span>
						</li>
				</ul></div>
			</div>
			<?php include Eleanor::$root.$theme.'Static/top_menu.php'; ?>
		</div></div></div>
			<div class="container">
				<div class="midside">
					<div class="container">
						<div class="mainside">
							<table class="conts"><tr><td style="padding: 0;">
							<div class="wpbox">
								<div class="wptop"><b><span>&nbsp;</span></b></div>
								<div class="wpmid">
<?php
$section=isset($_GET['section']) ? $_GET['section'] : 'general';
if($section!='general')
{
	global$Eleanor;
	$image=empty($Eleanor->module['image']) ? 'images/modules/default-big.png' : 'images/modules/'.str_replace('*','big',$Eleanor->module['image']);
	$title=isset($Eleanor->module['title']) ? $Eleanor->module['title'] : $Eleanor->module['stitle'];
	$descr=isset($Eleanor->module['descr']) ? $Eleanor->module['descr'] : '';
	echo'<div class="wbpad"><div class="bighead'.($descr ? '' : ' nodesc').'"><div><div>
	<img src="'.($image ? $image : 'images/modules/default-big.png').'" alt="'.$title.'" /><p><b>'.$title.'</b>'.($descr ? '<span>'.$descr.'</span>' : '').'</p>
	</div></div></div></div>';
}
?>
									{module}
								</div>
								<div class="wpbtm"><b><span>&nbsp;</span></b></div>
							</div>
							</td></tr></table>
						</div>
						<div class="rightside">
							<?php
								include Eleanor::$root.$theme.'Static/navigation.php';
								echo Blocks::Get('right');
							?>
						</div>
					</div>
				</div>
				<div class="leftside">
					<?php include Eleanor::$root.$theme.'Static/left_menu.php'; ?>
				</div>
				<div class="clr"></div>
			</div>
		<div class="elf"><div class="elf"><div class="elf">
			<div class="elfoot">
				<span class="copyright">
					<?php
#ѕожалуйста, не удал€йте и не измен€йте наши копирайты, если, конечно, у вас есть хоть немного уважени€ к разработчикам.
echo 'Powered by '.ELEANOR_COPYRIGHT?>
				</span>
				<span class="version">
					<?php echo Eleanor::$Language['tpl']['sversion'].' <b>'.ELEANOR_VERSION.'</b>'?>
				</span>
			</div>
		</div></div></div>
		<div class="finfo">
			<span class="pageinfo">
			{page status}
			<br />{debug}
			</span><?php if($GLOBALS['Eleanor']->multisite):
echo Eleanor::Select(false,Eleanor::Option(Eleanor::$Language['tpl']['msjump'],'',true),array('id'=>'msjump','style'=>'float:right','onchange'=>'CORE.MSJump($(this).val())'))?>
<script type="text/javascript">//<![CDATA[
$(function(){	$.each(CORE.mssites,function(k,v){		$("<option>").text(v.title).val(k).appendTo("#msjump");	})})//]]></script><?php endif?>		</div>
	</div>
</body>
</html>