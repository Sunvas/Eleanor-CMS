<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=<?php echo DISPLAY_CHARSET?>" /><title><?php echo$title.' - Eleanor CMS '.ELEANOR_VERSION?></title><base href="<?php echo PROTOCOL.Eleanor::$domain.Eleanor::$site_path?>" /><script type="text/javascript" src="../js/jquery.min.js"></script><script type="text/javascript" src="../js/core.js"></script><link type="image/x-icon" href="../favicon.ico" rel="icon" /><link media="screen" href="<?php echo$theme?>/style/styles.css" type="text/css" rel="stylesheet" />
<?php if($head)echo join($head)?></head><body class="pagebg">
<div class="wrapper">
	<div class="elh"><div class="elh"><div class="elh">
		<div class="head">
			<h1><a href="http://eleanor-cms.ru" title="Eleanor CMS">Eleanor CMS</a></h1>
			<div class="version">
				<span><span><span>
				<?php echo (isset(Eleanor::$Language['install']['sysever']) ? Eleanor::$Language['install']['sysever'] : 'Версия системы: ').'<b>'.ELEANOR_VERSION.'</b>'?>
				</span></span></span>
			</div>
		</div>
		<div class="process">
			<div class="procline"><img style="width: <?php echo$percent?>%" src="<?php echo$theme?>/images/spacer.gif" alt="<?php echo$percent?>%" title="<?php echo$percent?>%" /></div>
			<div class="procinfo"><span><?php echo$navi?></span></div>
		</div>
	</div></div></div>
	<div class="wpbox">
		<div class="wptop"><b>&nbsp;</b></div>
		<div class="wpmid">
			<div class="wpcont"><?php echo$content?></div>
			<div class="clr"></div>
		</div>
		<div class="wpbtm"><b>&nbsp;</b></div>
	</div>
	<div class="elf"><div class="elf"><div class="elf">
		<div class="copyright">
			Powered by <?php echo ELEANOR_COPYRIGHT?>
		</div>
		<img class="elcd" src="<?php echo$theme;?>images/spacer.gif" alt="" />
	</div></div></div>
</div>
</body>
</html>