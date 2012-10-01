<?php
/*
	Базовый шаблон всплывающих окон. Используется, например, при детальном просмотре сессии онлайн.

	@var содержимое body
*/
if(!defined('CMS'))die;?><!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=<?php echo DISPLAY_CHARSET?>" /><title><?php echo is_array($GLOBALS['title']) ? join(' &raquo; ',$GLOBALS['title']) : $GLOBALS['title']?></title>
<style type="text/css">
/* General */
	body { height: 100%; color: #000; background-color: #FFF; font-size: 0.70em; font-style: normal; line-height: 1.4em; font-family: Tahoma, Arial, Verdana, sans-serif;}
	a{ color: #469ebf; text-decoration: none; outline: none; }
	a:hover { text-decoration: underline; }
	.clr {clear:both;}
	.copyright { position:fixed; bottom:10px; right:10px; }

/* Qmenu editor */
	.table {border:1px solid #d8d8d8; width:100%; border-collapse: collapse;}
	.table td, .table th {padding:10px;}
	.drag:hover{background-color:#f9f9f9;}
	.drag:active{background-color:#e9e9e9;}
	.drag th {width:30px;}
	div.column { float:left;width:50%; }
	div.column ul { list-style:none; line-height:1.7; }
	div.column ul li { margin-bottom:10px; }
	div.column ul span { margin-left:20px; }
</style><base href="<?php echo PROTOCOL.Eleanor::$domain.Eleanor::$site_path?>" /><script src="js/jquery.min.js" type="text/javascript"></script><script src="js/core.js" type="text/javascript"></script>
<?php
echo Eleanor::JsVars(array(
			'c_domain'=>Eleanor::$vars['cookie_domain'],
			'c_prefix'=>Eleanor::$vars['cookie_prefix'],
			'c_time'=>Eleanor::$vars['cookie_save_time'],
			'ajax_file'=>Eleanor::$services['ajax']['file'],
			'site_path'=>Eleanor::$site_path,
			'language'=>Language::$main,
			'!head'=>$GLOBALS['head'] ? '["'.join('","',array_keys($GLOBALS['head'])).'"]' : '[]',
		),true,false,'CORE.').join($GLOBALS['head']);
foreach($GLOBALS['jscripts'] as &$v)
	echo'<script type="text/javascript" src="',$v,'"></script>';?>
</head>
<body style="text-align: left; margin: 20px;">
<?php if(isset($v_0))echo $v_0;
echo'<span class="copyright">Powered by '.ELEANOR_COPYRIGHT?></span>
</body>
</html>