<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use const Eleanor\CHARSET;

/** Login widget is included from ../index.php, so:
 * @var array $l10n
 * @var string $nonce
 * Default:
 * @var string $hcaptcha hcaptcha key (if empty - off)
 * @var ?string $adminpanel Link to admin panel */

$account=$GLOBALS['CMS']->account->slug;

if(CMS::$A->current)
{
	#For links to settings and sign out
	$Uri=new Uri($account);

	#User data
	\extract(GetUserData(['name','display_name','avatar']));

	$id=CMS::$A->current;
	$ent=\ENT_QUOTES | \ENT_HTML5 | \ENT_SUBSTITUTE | \ENT_DISALLOWED;
	$name=\htmlspecialchars($name,$ent,CHARSET,false);
	$display_name=\htmlspecialchars($display_name,$ent,CHARSET,false);
	?>
	<div class="blocklogin"><div class="dbottom"><div class="dtop">
		<div class="dcont">
			<a href="<?=$Uri?>">
				<img style="float:left;margin-right:10px;width:40px;" src="<?=$avatar ? "static/avatars/$id-$avatar.webp" : 'static/user-area/images/noavatar.png'?>" alt="<?=$name?>">
			</a>
			<strong><?=$display_name ?: $name?></strong>
			<div>
				<?=$adminpanel ? "<a href='$adminpanel'>{$l10n['admin-panel']}</a> | " : ''?>
				<a href="<?=$Uri('sign-out')?>"><?=$l10n['sign-out']?></a>
			</div>
<?php if(CMS::$A->available){
	$users='';
	$base=Uri::$base;

	#List of available users to switch to
	foreach(CMS::$A->available as $id)
	{
		$name=GetUserData('name',$id);
		$name=\htmlspecialchars($name,$ent,CHARSET,false);

		$users.=<<<HTML
<a href="$base?@=$id">$name</a>, 
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
}
else
{
	require_once __DIR__.'/../hcaptcha.php';
	?>
	<div class="blocklogin"><div class="dbottom"><div class="dtop">
		<div class="dcont" id="widget-sign-in"></div>
	</div></div></div>
	<script src="static/user-area/widget-sign-in.js" nonce="<?=$nonce?>" defer data-account="<?=Uri::$base.Uri::Make([$account],'/sign-in')?>" data-container="#widget-sign-in" data-template="#widget-sign-in-tpl" data-hcaptcha="<?=$hcaptcha?>"></script>
	<script id="widget-sign-in-tpl" type="text/x-template">
		<form @submit.prevent="Submit">
			<div class="logintext">
				<label for="sign-in-username"><?=$l10n['username']?></label>
				<div><div><input tabindex="1" type="text" id="sign-in-username" ref="username" v-model.trim="username" autocomplete="username" :disabled="loading" autofocus required></div></div>
			</div>
			<h5 v-if="recovery" style="text-align:center;margin-top:.75em"><?=$l10n['2of3']?></h5>
			<div class="logintext">
				<label for="sign-in-password"><?=$l10n['password']?><a href="#" v-if="recovery" @click.prevent="Back"><?=$l10n['back']?></a><a href="#" v-else @click.prevent="Recovery"><?=$l10n['recovery']?></a></label>
				<div><div><input tabindex="1" type="password" id="sign-in-password" ref="password" v-model="password" autocomplete="current-password" :disabled="loading" :required="required || !recovery"></div></div>
			</div>
			<div class="logintext" v-if="totp_field || recovery">
				<label for="sign-in-totp"><?=$l10n['totp']?></label>
				<div><div><input tabindex="1" type="text" id="sign-in-totp" ref="totp" v-model="totp" autocomplete="off" :disabled="loading" minlength="6" maxlength="8" inputmode="numeric" pattern="\d+" :required="required || totp_required"></div></div>
			</div>
			<div class="logintext" v-if="recovery">
				<label for="sign-in-recovery-code"><?=$l10n['recovery_code']?></label>
				<div><div><input tabindex="1" type="text" id="sign-in-recovery-code" v-model.trim="recovery_code" autocomplete="off" :disabled="loading" :required></div></div>
			</div>
			<label title="<?=$l10n['cookie-explain']?>"><input tabindex="1" type="checkbox" v-model="allow_cookie" required :disabled="loading"> <span><?=$l10n['allow-cookie']?></span></label><br>
			<label><input tabindex="1" type="checkbox" v-model="remember_me" :disabled="loading"> <span><?=$l10n['remember-me']?></span></label>
			<div v-if="hcaptcha" ref="hcaptcha" class="h-captcha" data-size="compact" data-tabindex="1"></div>
			<div class="submit">
				<input tabindex="1" value="<?=$l10n['sign-in']?>" class="enterbtn" type="submit" :disabled="loading">
				<div style="margin-top: .75em"><a href="<?=Uri::$base.Uri::Make([$account,'sign-up'])?>"><?=$l10n['sign-up']?></a></div>
			</div>
		</form>
	</script>
	<?php
}