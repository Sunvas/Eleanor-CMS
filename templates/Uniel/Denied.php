<?php
/*
	Шаблон страницы закрытого на профилактику сайта. Можно добавить форму для входа - она будет работать, давая доступ к сайту тем, у кого есть на то права.
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?php echo DISPLAY_CHARSET?>" />
<title><?=$ltpl['site_close_text']?></title>
<base href="<?php echo PROTOCOL.Eleanor::$punycode.Eleanor::$site_path?>" />
<style type="text/css">/*<![CDATA[*/
body {
	margin: auto;
	padding: 0;
	text-align: center;
	height: 100%;
	font-family: Tahoma, Arial, Sans-serif;
}
html { height: 100%; }
h1 { font-weight: normal; font-size: 18px; color: #4f4f4f;}
.syscopyright { font-size: 10px; color: #c0c0c0; margin-top:10px}
.syscopyright a { color: #c0c0c0; }
/*]]>*/</style>
</head>

<body>
<div style="padding-top: 20%;"><img src="<?php echo$theme?>images/denied.png" alt="" title="<?php echo$ltpl['site_close_text']?>" /><br />
<?php echo (empty(Eleanor::$vars['site_close_mes']) ? '<h1>'.$ltpl['site_close_text'].'</h1>' : OwnBB::Parse(Eleanor::$vars['site_close_mes']))?>
</div>
<div class="syscopyright"><?php
	#Внимание! САМОВОЛЬНОЕ УБИРАНИЕ КОПИРАЙТОВ ЧРЕВАТО БЛОКИРОВКОЙ НА ОФИЦИАЛЬНОМ САЙТЕ СИСТЕМЫ И ПРЕСЛЕДУЕТСЯ ПО ЗАКОНУ!
	#КОПИРАЙТЫ МЕНЯТЬ/ПРАВИТЬ НЕЛЬЗЯ! СОВСЕМ!! ОНИ ДОЛЖНЫ ОСТАВАТЬСЯ НЕИЗМЕННЫМИ ДО БИТА! Также недопустимо и их скрытие!
	echo'Powered by '.ELEANOR_COPYRIGHT?></div>
</body>
</html>