<?php
/*
	Оформление для содержимого блока пользователей онлайн. Само содержимое блока смотрите в файле Classes/users.php UsersOnline
*/
if(!defined('CMS'))die;
$l=Eleanor::$Language['tpl'];
?>
<div id="who-online"><?php echo $l['loading']?></div><a href="#"><b><?php echo $l['update']?></b></a><a href="<?php echo Eleanor::$services['admin']['file']?>?section=management&amp;module=users&amp;do=online" style="float:right"><b><?php echo $l['alls']?></b></a>
<script type="text/javascript">//<![CDATA[
$(function(){
	var old=CORE.loading,
		F=function(){
			var w=500,h=250,
				win=window.open('','win'+$(this).data("uid")+$(this).data("gip"),'height='+h+',width='+w+',toolbar=no,directories=no,menubar=no,scrollbars=no,status=no,top='+Math.round((screen.height-h)/2)+',left='+Math.round((screen.width-w)/2));
			CORE.Ajax(
				{
					direct:"admin",
					file:"users",
					event:"online_detail",
					ip:$(this).data("gip")||"",
					id:$(this).data("uid")||0,
					service:$(this).data("s")
				},
				function(r)
				{
					win.document.open('text/html','replace');
					win.document.write(r);
					win.document.close();
				}
			);
			return false;
		};
	CORE.loading=false;
	$("#onlinelist").on("click",".entry",F)
	$("#who-online").on("click",".entry",F).next().click(function(){
		CORE.Ajax(
			{
				direct:"admin",
				file:"users",
				event:"online"
			},
			function(r)
			{
				$("#who-online").html(r);
			}
		);
		return false;
	}).click();
	CORE.loading=old;
})
//]]></script>