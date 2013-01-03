<?php
/*
	Элемент шаблона: блок логина пользователя. Вынесено в отдельный файл, дабы во-первых не засорять блоки, а во-вторых предоставить дизайнеру
	возможность разместить этот блок в самом логичном по его мнению месте
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];
global$Eleanor;
$ma=array_keys($Eleanor->modules['sections'],'account');
$ma=reset($ma);
if(Eleanor::$Login->IsUser()):
	$user=Eleanor::$Login->GetUserValue(array('name','avatar_type','avatar_location'));
	switch($user['avatar_location'] ? $user['avatar_type'] : '')
	{
		case'local':
			$avatar='images/avatars/'.$user['avatar_location'];
		break;
		case'upload':
			$avatar=Eleanor::$uploads.'/avatars/'.$user['avatar_location'];
		break;
		case'url':
			$avatar=$user['avatar_location'];
		break;
		default:
			$avatar='images/avatars/user.png';
	}
?>
<div class="blocklogin"><div class="dbottom"><div class="dtop">
	<div class="dcont">
	<?php if($avatar):?><a href="<?php echo Eleanor::$vars['link_options']?>"><img style="float:left;margin-right:10px;width:40px;" src="<?php echo$avatar?>" alt="<?php echo$user['name']?>" /></a><?php endif?>
	<h5 style="padding-top: 4px;"><?php echo sprintf($ltpl['hello'],'<a href="'.Eleanor::$vars['link_options'].'">'.$user['name'].'</a>')?></h5>
	<div><?php if(Eleanor::$Permissions->IsAdmin()):?><a href="<?php echo Eleanor::$services['admin']['file']?>"><?php echo$ltpl['adminka']?></a> | <?php endif; ?><a href="<?php echo$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'logout'),false,'')?>"><?php echo$ltpl['exit']?></a>
<?php if($GLOBALS['Eleanor']->multisite):
echo Eleanor::Select(false,Eleanor::Option($ltpl['msjump'],'',true),array('id'=>'msjump','style'=>'width:100%','onchange'=>'CORE.MSJump($(this).val())'))?>
<script type="text/javascript">//<![CDATA[
$(function(){
	$.each(CORE.mssites,function(k,v){
		$("<option>").text(v.title).val(k).appendTo("#msjump");
	})
})//]]></script><?php endif?>
	</div>
	<div class="clr"></div>
	</div>
</div></div></div>
<?php else: ?>

<div class="blocklogin"><div class="dbottom"><div class="dtop">
	<div class="dcont">
		<form action="<?php echo$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'login'),false,'')?>" method="post">
			<div class="logintext">
				<span><?php echo$ltpl['login']?></span>
				<div><div><input type="text" name="login[name]" tabindex="1" /></div></div>
			</div>
			<div class="logintext">
				<span><?php echo$ltpl['pass']?></span>
				<div><div><input type="password" name="login[password]" tabindex="2" /></div></div>
			</div>
			<div style="text-align:center">
				<div style="padding-bottom: 6px;"><input value="<?php echo$ltpl['enter']?>" class="enterbtn" type="submit" tabindex="3" /></div>
				<a href="<?php echo Eleanor::$vars['link_register']?>"><?php echo$ltpl['register']?></a> | <a href="<?php echo Eleanor::$vars['link_passlost']?>"><?php echo$ltpl['lostpass']?></a>
<hr /><?php include Eleanor::$root.$theme.'Static/external_auth.php'?>
			</div>
		</form>
	</div>
</div></div></div>
<?php if($GLOBALS['Eleanor']->multisite):?>
<script type="text/javascript">//<![CDATA[
CORE.MSQueue.done(function(qw){	var al=$(".externals");	$.each(qw,function(k,v){		var a=$("<a>").prop({			href:"#",
			title:v.name,
			style:"font-weight:bold"		}).text(v.title).click(function(){			CORE.MSLogin(k);
			return false;		});		al.each(function(){			$(this).append("<br />").append(a);
			a=a.clone(true);		});	})});
//]]></script>
<?php endif;endif;?>