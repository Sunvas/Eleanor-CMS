<?php
namespace CMS;

use const Eleanor\CHARSET;

/** Login widget is included from ../index.php, so:
 * @var array $l10n
 * @var string $nonce
 * Default:
 * @var string $hcaptcha hcaptcha key (if empty - off)
 * @var ?string $dashboard Link to dashboard */

$account=$GLOBALS['Shared']->account->slug;
$bot=CMS::$config['system']['bot_name'];

if(CMS::$A->current)
{
	#For links to settings and sign out
	$Uri=new Uri($account)->IAM();

	#User data
	extract(GetUsers(['name','display_name','avatar']));

	$id=CMS::$A->current;
	$name=htmlspecialchars($name,ENT,CHARSET,false);
	$display_name=htmlspecialchars($display_name,ENT,CHARSET,false);
	?>
	<div class="blocklogin"><div class="dbottom"><div class="dtop">
		<div class="dcont">
			<a href="<?=$Uri('settings')?>">
				<img style="float:left;margin-right:10px;width:40px;" src="<?=$avatar ? "static/avatars/{$id}-{$avatar}.webp" : 'static/userspace/images/noavatar.png'?>" alt="<?=$name?>">
			</a>
			<h5 style="padding:4px 0 4px"><?=$display_name ?: $name?></h5>
			<div>
				<?=$dashboard ? "<a href='{$dashboard}'>{$l10n['dashboard']}</a> | " : ''?>
				<a href="<?=$Uri('sign-out')?>"><?=$l10n['sign-out']?></a>
			</div>
<?php if(CMS::$A->available){
	$users='';
	$base=Uri::$base;

	#List of available users to switch to
	foreach(CMS::$A->available as $id)
	{
		$name=GetUsers('name',$id);
		$name=htmlspecialchars($name,ENT,CHARSET,false);

		$users.=<<<HTML
<a href="{$base}?iam={$id}">{$name}</a>, 
HTML;
	}
	?>
			<div class="clr" style="margin-top:1em"><?=$l10n['switch-to'],' ',\rtrim($users,', ')?></div>
<?php }else{?>
			<div class="clr"></div>
<?php }?>
		</div>
	</div></div></div>
	<?php
}else{
	require_once __DIR__.'/../hcaptcha.php';
	?>
	<div class="blocklogin"><div class="dbottom"><div class="dtop">
		<div class="dcont" id="widget-sign-in"></div>
	<?php if($bot){?>
		<hr>
		<div class="dcont">
			<script async nonce="<?=$nonce?>" src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="<?=$bot?>" data-size="medium" data-onauth="TelegramAuth(user)" data-request-access="write"></script>
		</div>
	<?php }?>
	</div></div></div>
	<script src="static/userspace/widget-sign-in.js" nonce="<?=$nonce?>" defer data-account="<?=Uri::$base.Uri::Make([$account],'/')?>" data-container="#widget-sign-in" data-template="#widget-sign-in-tpl" data-hcaptcha="<?=$hcaptcha?>"></script>
	<script id="widget-sign-in-tpl" type="text/x-template">
		<form @submit.prevent="Submit">
			<div class="logintext">
				<label for="block-name"><?=$l10n['username']?></label>
				<div><div><input tabindex="1" type="text" id="block-name" autocomplete="username" v-model.trim="username" :disabled="loading" autofocus required></div></div>
			</div>
			<div class="logintext">
				<label for="block-password"><?=$l10n['password']?><a href="#" @click.prevent="Forgot"><?=$l10n['forgotten']?></a></label>
				<div><div><input tabindex="1" type="password" id="block-password" autocomplete="current-password" v-model="password" :disabled="loading" required></div></div>
			</div>
			<label title="<?=$l10n['cookie-explain']?>"><input tabindex="1" type="checkbox" v-model="allow_cookie" required :disabled="loading"> <span><?=$l10n['allow-cookie']?></span></label><br>
			<label><input tabindex="1" type="checkbox" v-model="remember_me" :disabled="loading"> <span><?=$l10n['remember-me']?></span></label>
			<div v-if="hcaptcha" ref="hcaptcha" class="h-captcha" data-size="compact" data-tabindex="1"></div>
			<div class="submit">
				<input tabindex="1" value="<?=$l10n['sign-in']?>" class="enterbtn" type="submit" :disabled="loading">
			</div>
		</form>
	</script>
	<?php
}