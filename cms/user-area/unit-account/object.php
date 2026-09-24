<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class {
	use \CMS\Traits\EmbeddedTemplate;

	readonly L10n $l10n;
	function __construct()
	{
		$this->l10n=new L10n('',__DIR__.'/l10n/');
	}

	/** Page with error of signing in: users limit is exceeded
	 * @param string $error Error code
	 * @param int $max Maximum amount of logined users
	 * @param array $links Default links
	 * @return string */
	function SignInError(string$error,int$max,array$links,...$d):string
	{
		$menu=[
			[$this->l10n['overview'],$links['overview']],
			[$this->l10n['settings'],$links['settings']],
			[$this->l10n['sessions'],$links['sessions']],
			[$this->l10n['sign-in-log'],$links['sign-in-log']],
			$this->l10n['user-sign-in'],
		];

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Message($this->l10n[$error] ?? $error,'warning')

			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['user-sign-in']]
			);
	}

	/** Page with login form
	 * @param string $nonce Default nonce
	 * @param array $links Default links
	 * @param string $hcaptcha Default hcaptcha
	 * @return string */
	function SignIn(string$nonce,array$links,string$hcaptcha='',...$d):string
	{
		if(CMS::$A->current)
		{
			$menu=[
				[$this->l10n['overview'],$links['overview']],
				[$this->l10n['settings'],$links['settings']],
				[$this->l10n['sessions'],$links['sessions']],
				[$this->l10n['sign-in-log'],$links['sign-in-log']],
				$this->l10n['user-sign-in'],
			];
			$loading=$this->Loading();

			return CMS::$T->Heading($this->l10n['title'],menu:$menu)
				->hcaptcha()
				->Append(<<<HTML
<script src="static/user-area/widget-sign-in.js" nonce="$nonce" defer data-account="{$links['sign-in']}" data-container="#sign-in" data-template="#sign-in-tpl" data-hcaptcha="$hcaptcha"></script>
<div id="sign-in"></div>
<script id="sign-in-tpl" type="text/x-template">
<form @submit.prevent="Submit">
	<table class="tabstyle tabform">
		<tr>
			<th class="label">
				<label for="username">{$this->l10n['username']}</label>
			</th>
			<td><input tabindex="1" :class="{error:wrong_username}" type="text" id="username" ref="username" v-model.trim="username" autocomplete="username" :disabled="loading" autofocus required></td>
		</tr>
		<tr v-if="recovery" class="infolabel">
			<th colspan="2">{$this->l10n['2of3']}</th>
		</tr>
		<tr>
			<th class="label">
				<label for="password">{$this->l10n['password']}</label>
			</th>
			<td><input tabindex="1" :class="{error:wrong_password}" type="password" id="password" ref="password" v-model="password" autocomplete="current-password" :disabled="loading" :required="required || !recovery"></td>
		</tr>
		<tr v-if="totp_field || recovery">
			<th class="label">
				<label for="totp">{$this->l10n['totp']}</label>
			</th>
			<td><input tabindex="1" :class="{error:wrong_totp}" type="text" id="totp" ref="totp" v-model="totp" autocomplete="off" :disabled="loading" minlength="6" maxlength="8" inputmode="numeric" pattern="\d+" :required="required || totp_required"></td>
		</tr>
		<tr v-if="recovery">
			<th class="label">
				<label for="recovery_code">{$this->l10n['recovery_code']}</label>
			</th>
			<td><input tabindex="1" type="text" id="recovery_code" v-model.trim="recovery_code" autocomplete="off" :disabled="loading" :required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="remember-me">{$this->l10n['remember-me']}</label>
			</th>
			<td><input tabindex="1" type="checkbox" v-model="remember_me" id="remember-me" :disabled="loading"></td>
		</tr>
		<tr v-if="hcaptcha">
			<th></th>
			<td ref="hcaptcha" class="h-captcha" data-size="compact" data-tabindex="1"></td>
		</tr>
		<tr>
			<th></th>
			<td>
				<input tabindex="1" type="submit" value="{$this->l10n['sign-in']}" :disabled="loading" style="margin-right: 2em">
				<input tabindex="1" type="button" value="{$this->l10n['back']}" v-if="recovery" @click.prevent="Back">
				<input tabindex="1" type="button" value="{$this->l10n['recovery']}" v-else @click.prevent="Recovery">
			</td>
		</tr>
	</table>
</form>
$loading
</script>
HTML )

				->content->BaseBlock()
				->content->index(
					title:[$this->l10n['user-sign-in']]
				);
		}

		return CMS::$T->Heading($this->l10n['title'])
			->Message($this->l10n['use-widget'],'info')

			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['user-sign-in']]
			);
	}

	/** Page signing up form
	 * @param int $mpl Minimum password length
	 * @param string $hcaptcha Default hcaptcha
	 * @return string */
	function SignUp(int$mpl,string$hcaptcha,...$d):string
	{
		$script='static/user-area/unit-account-sign-up.js';
		$template=<<<HTML
<form @submit.prevent="Submit">
	<table class="tabstyle tabform">
		<tr>
			<th class="label">
				<label for="name"><span class="labinfo" title="{$this->l10n['username_']}">(?)</span> {$this->l10n['username']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="text" v-model.lazy="name" :class="{error:name_error}" id="name" ref="name" autocomplete="username" maxlength="25" required autofocus></td>
		</tr>
		<tr>
			<th class="label">
				<label for="display-name"><span class="labinfo" title="{$this->l10n['display_name_']}">(?)</span> {$this->l10n['display_name']}</label>
			</th>
			<td><input tabindex="1" type="text" id="display-name" v-model="display_name" :placeholder="name" autocomplete="name" maxlength="25"></td>
		</tr>
		<tr>
			<th class="label">
				<label for="password"><span class="labinfo" title="{$this->l10n['password_']}">(?)</span>{$this->l10n['password']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="password" v-model="password" autocomplete="new-password" minlength="$mpl" required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="password2">{$this->l10n['password2']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="password2" ref="password2" v-model.lazy="password2" autocomplete="new-password" minlength="$mpl" required></td>
		</tr>
		<tr v-if="hcaptcha">
			<th></th>
			<td ref="hcaptcha" class="h-captcha" data-size="compact" data-tabindex="1"></td>
		</tr>
		<tr>
			<th class="label"></th>
			<td><input tabindex="1" type="submit" value="{$this->l10n['register']}" :disabled="loading"></td>
		</tr>
	</table>
</form>
HTML . $this->Loading();

		return CMS::$T->Heading($this->l10n['signing-up'])
			->hcaptcha()
			->app(\compact('script','template'))
			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['signing-up']]
			);
	}

	/** Page with information about successful signing out
	 * @return string */
	function SignOut(...$d):string
	{
		$url=Uri::$base;
		$refresh=<<<HTML
<meta http-equiv="refresh" content="2;url=$url">
HTML;

		return CMS::$T->Heading($this->l10n['signed-out'])
			->Message($this->l10n['signed-out_'],'info')

			->content->BaseBlock()
			->content->index(
				head:[$refresh],
				title:[$this->l10n['signed-out']],
			);
	}

	/** Change password of user's account
	 * @param array $me Information about user
	 * @param int $mpl Minimum password length
	 * @param int $rgr Recovery grace remaining
	 * @param array $links Default links
	 * @return string */
	function Overview(array$me,int$mpl,int$rgr,array$links,...$d):string
	{
		$menu=[
			$this->l10n['overview'],
			[$this->l10n['settings'],$links['settings']],
			[$this->l10n['sessions'],$links['sessions']],
			[$this->l10n['sign-in-log'],$links['sign-in-log']],
			[$this->l10n['user-sign-in'],$links['sign-in']],
		];
		$data=\compact('me','rgr');
		$script='static/user-area/unit-account-overview.js';

		$template=<<<HTML
<table class="tabstyle tabform">
	<tr>
		<th class="label">{$this->l10n['username']}</th>
		<td v-text="me.name"></td>
	</tr>
	<tr>
		<th class="label">{$this->l10n['groups']}</th>
		<td v-html="groups"></td>
	</tr>
	<tr>
		<th class="label">{$this->l10n['registered']}</th>
		<td v-text="me.created"></td>
	</tr>
	<tr>
		<th class="label">{$this->l10n['last_login_attempt']}</th>
		<td v-text="me.last_login_attempt"></td>
	</tr>
</table>
<div class="warning" style="margin:1em" v-if="!verification_required">
	<img src="static/user-area/images/info.png" alt="" title="">
	<h4>{$this->l10n['grace-period']}</h4>
	{$this->l10n['grace-period_']}
	<div class="clr"></div>
</div>
<hr>
<form @submit.prevent="ChangePasswordSubmit">
	<table class="tabstyle tabform">
		<tr class="infolabel">
			<th colspan="2">{$this->l10n['password']}</th>
		</tr>
		<tr>
			<th class="label">{$this->l10n['password_changed_at']}</th>
			<td>{{password_changed ? l10n.just_now : (me.password_changed_at ?? '—')}} <a href="#" style="margin-left: 1em" v-if="!change_password" @click.prevent="ChangePassword">{$this->l10n['change-password']}</a></td>
		</tr>
		<template v-if="change_password">
			<tr>
				<th class="label">
					<label for="password">{$this->l10n['password']} <span class="imp">*</span></label>
				</th>
				<td><input tabindex="1" type="password" ref="password" id="password" v-model="password" autocomplete="new-password" minlength="$mpl" required></td>
			</tr>
			<tr>
				<th class="label">
					<label for="password2">{$this->l10n['password2']} <span class="imp">*</span></label>
				</th>
 				<td><input tabindex="1" type="password" id="password2" ref="password2" v-model.lazy="password2" autocomplete="new-password" minlength="$mpl" required></td>
			</tr>
			<tr v-if="verification_required">
				<th class="label">
					<label for="verification"><span class="labinfo" title="{$this->l10n['verification_']}">(?)</span> {$this->l10n['verification']} <span class="imp">*</span></label>
				</th>
				<td><input tabindex="1" type="password" ref="verification" id="verification" v-model="verification" autocomplete="current-password" required></td>
			</tr>
			<tr>
				<th class="label"></th>
				<td><input tabindex="1" type="submit" value="{$this->l10n['save']}" :disabled="loading"> <a href="#" style="margin-left: .5em" @click.prevent="change_password=false">❌</a></td>
			</tr>
		</template>
	</table>
</form>
<hr>
<table class="tabstyle tabform">
	<tr class="infolabel">
		<th colspan="2">{$this->l10n['totps']}</th>
	</tr>
	<tr>
		<th class="label">{$this->l10n['state']}</th>
		<td v-if="me.totp_enabled">
			<b style="color: darkgreen; margin-right: 1em">{$this->l10n['totp-enabled']}</b> {{totp_changed ? l10n.just_now : me.totp_changed_at ?? '—'}}
			<a href="#" style="margin-left: 1em" v-if="!totp" @click.prevent="TotpEnable">{$this->l10n['change']}</a>
			<a href="#" style="margin-left: 1em" v-if="!totp" @click.prevent="TotpDisable">{$this->l10n['disable']}</a>
		</td>
		<td v-else>
			<b style="color: darkred; margin-right: 1em">{$this->l10n['totp-disabled']}</b> {{totp_changed ? l10n.just_now : me.totp_changed_at ?? '—'}}
			<a href="#" style="margin-left: 1em" v-if="!totp" @click.prevent="TotpEnable">{$this->l10n['enable']}</a>
		</td>
	</tr>
	<template v-if="totp==='enable'">
		<tr>
			<th class="label">
				<label for="totp_issuer"><span class="labinfo" title="{$this->l10n['totp-hint']}">(?)</span> {$this->l10n['totp-issuer']}</label>
			</th>
			<td><input tabindex="1" type="text" form="totp-submit" id="totp_issuer" v-model.lazy="totp_issuer" :disabled="loading" autocomplete="off" required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="totp_digits">{$this->l10n['totp-digits']}</label>
			</th>
			<td>
				<select tabindex="1" id="totp_digits" form="totp-submit" v-model="totp_digits" :disabled="loading">
					<option :value="6">{$this->l10n['totp-digits-6']}</option>
					<option :value="7">{$this->l10n['totp-digits-7']}</option>
					<option :value="8">{$this->l10n['totp-digits-8']}</option>
				</select>
			</td>
		</tr>
		<tr>
			<th class="label">{$this->l10n['totp-qr']}</th>
			<td v-if="totp_qr" v-html="totp_qr"></td>
			<td v-else>{$this->l10n['loading']}</td>
		</tr>
		<tr>
			<th class="label">
				<label for="totp_code">{$this->l10n['totp-code']}</label>
			</th>
			<td><input tabindex="1" type="text" form="totp-submit" ref="totp_code" id="totp_code" v-model="totp_code" minlength="6" :maxlength="totp_digits" inputmode="numeric" :pattern="`\\\\d{\${totp_digits}}`" autocomplete="off" required></td>
		</tr>
		<tr v-if="verification_required">
			<th class="label">
				<label for="verification"><span class="labinfo" title="{$this->l10n['verification_']}">(?)</span> {$this->l10n['verification']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" form="totp-submit" ref="verification" id="verification" v-model="verification" autocomplete="current-password" required></td>
		</tr>
		<tr>
			<th class="label"></th>
			<td>
				<form id="totp-submit" @submit.prevent="TotpSubmit">
					<input tabindex="1" type="submit" value="{$this->l10n['save']}" :disabled="loading"> <a href="#" style="margin-left: .5em" @click.prevent="totp=false">❌</a>
				</form>
			</td>
		</tr>
	</template>
	<template v-if="totp==='disable'">
		<tr v-if="verification_required">
			<th class="label">
				<label for="verification"><span class="labinfo" title="{$this->l10n['verification_']}">(?)</span> {$this->l10n['verification']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" form="totp-submit" ref="verification" id="verification" v-model="verification" autocomplete="current-password" required></td>
		</tr>
		<tr>
			<th class="label"></th>
			<td>
				<form id="totp-submit" @submit.prevent="TotpDisableSubmit">
					<input tabindex="1" type="submit" value="{$this->l10n['totp-delete']}" :disabled="loading"> <a href="#" style="margin-left: .5em" @click.prevent="totp=false">❌</a>
				</form>
			</td>
		</tr>
	</template>
</table>
<hr>
<table class="tabstyle tabform">
	<tr class="infolabel">
		<th colspan="2">{$this->l10n['recovery-codes']}</th>
	</tr>
	<tr>
		<th class="label">{$this->l10n['state']}</th>
		<td>
			<b v-if="me.available_recovery_codes>0" style="color: darkgreen" v-text="l10n.available(me.available_recovery_codes)"></b>
			<b v-else style="color: darkred">{$this->l10n['rc-empty']}</b>
			<a href="#" style="margin-left: 1em" v-if="!rc" @click.prevent="RecoveryCodes">{$this->l10n['rc-generate']}</a>
		</td>
	</tr>
	<template v-if="rc">
		<tr>
			<th class="label">
				<label for="rc_codes">{$this->l10n['rc-codes']}</label>
			</th>
			<td><textarea tabindex="1" style="font-family:monospace;font-size:1.25em;resize:none" id="rc_codes" v-model="rc_codes" @click="RecoveryCodesClick" :disabled="loading" :rows="rc_rows" readonly></textarea></td>
		</tr>
		<tr v-if="verification_required">
			<th class="label">
				<label for="verification"><span class="labinfo" title="{$this->l10n['verification_']}">(?)</span> {$this->l10n['verification']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" form="rc-submit" ref="verification" id="verification" v-model="verification" autocomplete="current-password" required></td>
		</tr>
		<tr>
			<th class="label"></th>
			<td>
				<form id="rc-submit" @submit.prevent="RecoveryCodesSubmit">
					<input tabindex="1" type="submit" value="{$this->l10n['save']}" :disabled="loading"> <a href="#" style="margin-left: .5em" @click.prevent="rc=false">❌</a>
				</form>
			</td>
		</tr>
	</template>
	<template v-else>
		<tr>
			<th class="label">{$this->l10n['rc-created-at']}</th>
			<td v-text="rc_created ? l10n.just_now : me.recovery_codes_created_at ?? '—'"></td>
		</tr>
		<tr>
			<th class="label">{$this->l10n['rc-last-used-at']}</th>
			<td v-text="me.recovery_codes_last_used_at ?? '—'"></td>
		</tr>
		<tr v-if="me.available_recovery_codes>0">
			<th class="label"><label for="rc_check">{$this->l10n['rc-check']}</label></th>
			<td>
				<form @submit.prevent="RecoveryCodesCheck">
					<input tabindex="1" type="text" id="rc_check" v-model="rc_check" autocomplete="off" style="font-family:monospace;width:16ch;min-width:14ch;margin-right:1em;" pattern="[23456789ABCDEFGHJKLMNPQRSTUVWXYZ\\s]+" required>
					<input tabindex="1" type="submit" value="{$this->l10n['check']}" :disabled="loading">
				</form>
			</td>
		</tr>
	</template>
</table>
HTML . $this->Loading();

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->app(\compact('data','script','template'))
			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['overview'],$this->l10n['title']],
				jsdelivr:',npm/qrcode-generator@2/dist/qrcode.min.js'
			);
	}

	/** Settings of the user
	 * @param array $settings values of settings
	 * @param array $timezones array of timezones
	 * @param string $nonce Default nonce
	 * @param array $links Default links
	 * @return string */
	function Settings(array$settings,array$timezones,string$nonce,array$links=[],...$d):string
	{
		$menu=[
			[$this->l10n['overview'],$links['overview']],
			$this->l10n['settings'],
			[$this->l10n['sessions'],$links['sessions']],
			[$this->l10n['sign-in-log'],$links['sign-in-log']],
			[$this->l10n['user-sign-in'],$links['sign-in']],
		];
		$id=CMS::$A->current;
		$data=\json_encode(\compact('settings','timezones'),JSON);
		$loading=$this->Loading('saving');

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Append(<<<HTML
<div id="app"></div>
<script src="static/user-area/unit-account-settings.js" nonce="$nonce" defer data-container="#app" data-template="#app-tpl" data-data="#app-data"></script>
<script id="app-data" type="application/json">$data</script>
<script id="app-tpl" type="text/x-template">
<form @submit.prevent="Submit">
	<table class="tabstyle tabform">
		<tr>
			<th class="label">{$this->l10n['avatar']}</th>
			<td>
				<div v-if="has_avatar"> 
					<img v-if="avatar" :src="avatar" alt="{$this->l10n['avatar']}">
					<img v-else :src="'static/avatars/$id-'+settings.avatar+'.webp'" alt="{$this->l10n['avatar']}">
				</div>
				<input type="button" :value="avatar_button" @click="UploadAvatar">
			</td>
		</tr>
		<tr>
			<th class="label">
				<label for="display-name">{$this->l10n['display_name']}</label>
			</th>
			<td><input tabindex="1" type="text" id="display-name" v-model="settings.display_name" autocomplete="name" maxlength="35"></td>
		</tr>
		<tr>
			<th class="label">
				<label for="info">{$this->l10n['info']}</label>
			</th>
			<td><textarea id="info" v-model="settings.info" rows="3"></textarea></td>
		</tr>
		<tr>
			<th class="label">
				<label for="timezone">{$this->l10n['timezone']}</label>
			</th>
			<td>
				<select id="timezone" v-model="settings.timezone">
					<option value="">{$this->l10n['default']}</option>
					<optgroup label="Asia"><option v-for="item in asia" v-text="item"></option></optgroup>
					<optgroup label="Europe"><option v-for="item in europe" v-text="item"></option></optgroup>
				</select>
			</td>
		</tr>
		<tr v-if="has_l10n">
			<th class="label">
				<label for="l10n">{$this->l10n['l10n']}</label>
			</th>
			<td>
				<select id="l10n" v-model="settings.l10n">
					<option value="en">🇺🇸 English</option>
					<option value="ru">🇷🇺 Русский язык</option>
				</select>
			</td>
		</tr>
		<tr>
			<th class="label"></th>
			<td><input tabindex="1" type="submit" :value="submit_text" :disabled="saving"></td>
		</tr>
	</table>
</form>
$loading
</script>
HTML )

			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['settings'],$this->l10n['title']],
			);
	}

	/** List of user's sessions
	 * @param \Traversable $items List of sessions
	 * @param int $mtss Months to stale session
	 * @param string $nonce Default nonce
	 * @param array $links Default links
	 * @return string */
	function Sessions(\Traversable$items,int$mtss,string$nonce,array$links,...$d):string
	{
		$menu=[
			[$this->l10n['overview'],$links['overview']],
			[$this->l10n['settings'],$links['settings']],
			$this->l10n['sessions'],
			[$this->l10n['sign-in-log'],$links['sign-in-log']],
			[$this->l10n['user-sign-in'],$links['sign-in']],
		];
		$current=CMS::$a11n;

		$items=Iterator2Array($items,function($session){
			$session['sort']=\strtotime($session['used']);
			$session['used']=$session['used'] ? L10n::Date($session['used']) : '';
			$session['created']=L10n::Date($session['created']);

			return $session;
		});
		$items=\json_encode($items,JSON);

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Append(<<<HTML
<div id="app" class="binner"></div>
<script src="static/user-area/unit-account-sessions.js" nonce="$nonce" defer data-container="#app" data-template="#app-tpl" data-current="$current" data-data="#app-data"></script>
<script id="app-data" type="application/json">$items</script>
<script id="app-tpl" type="text/x-template">
<table class="tabstyle sessions" style="margin-bottom: 1em">
	<thead>
		<tr class="first tablethhead">
			<th>{$this->l10n['browser']}</th>
			<th>IP</th>
			<th>{$this->l10n['way']}</th>
			<th>{$this->l10n['created']}</th>
			<th>{$this->l10n['used']}</th>
		</tr>
	</thead>
	<tbody>
		<tr class="tabletrline2" v-for="(session,index) in items" :class="{current:session.a11n_id==current}" :title="session.a11n_id==current ? l10n.current : ''">
			<td v-text="session.ua"></td>
			<td>
				<div class="flex">
					<a target="_blank" :href="'https://www.infobyip.com/?ip='+session.ip" v-text="session.ip"></a>
					<span v-if="session.terminatable" role="button" @click="Terminate(index,session.a11n_id)" title="{$this->l10n['terminate']}">❌</span>
				</div>
			</td>
			<td v-text="l10n[session.way] ?? session.way"></td>
			<td style="text-align: center" v-text="session.created"></td>
			<td style="text-align: center">{{session.used || '&mdash;'}}</td>
		</tr>
	</tbody>
</table>
</script>
HTML )
			->Message(sprintf($this->l10n['sessions-info%'],$mtss),'info')

			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['sessions'],$this->l10n['title']],
			);
	}

	/** List of user's login attempts
	 * @param \Traversable $items List of attempts
	 * @param int $total Total amount of items
	 * @param string $sort Sorting field
	 * @param int $pp Page size
	 * @param bool $desc Flag for descending sorting
	 * @param string $nonce Default nonce
	 * @param array $links Default links
	 * @return string */
	function SignInLog(\Traversable$items,int$total,string$sort,int$pp,bool$desc,string$nonce,array$links,...$d):string
	{
		$menu=[
			[$this->l10n['overview'],$links['overview']],
			[$this->l10n['settings'],$links['settings']],
			[$this->l10n['sessions'],$links['sessions']],
			$this->l10n['sign-in-log'],
			[$this->l10n['user-sign-in'],$links['sign-in']],
		];

		$items=Iterator2Array($items,function($item){
			$item['ts']=\strtotime($item['date']);
			$item['date']=L10n::Date($item['date']);

			return $item;
		});
		$items=\json_encode(\compact('items','total','pp','sort','desc'),JSON);
		$paginator=$this->app_paginator();
		$maxdate=\date('Y-m-d');

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Append(<<<HTML
<div id="app" class="binner"></div>
<script src="static/user-area/unit-account-sign-in-log.js" nonce="$nonce" defer data-container="#app" data-template="#app-tpl" data-data="#app-data"></script>
<script id="app-data" type="application/json">$items</script>
<script id="app-tpl" type="text/x-template">
<form ref="filter" style="text-align:right; padding:1em 0;">
	<input type="hidden" v-for="[name,value] in Filter(['date'],false)" :name :value>
	{$this->l10n['date-filter']} <input type="date" v-model="date" name="date" max="$maxdate" @change="FilterSubmit">
</form>
<table class="tabstyle sign-in-log" style="margin-bottom: 1em" v-if="items.length>0">
	<thead>
		<tr class="first tablethhead">
			<th>{$this->l10n['date']}</th>
			<th>{$this->l10n['result']}</th>
			<th>IP</th>
			<th>{$this->l10n['browser']}</th>
		</tr>
	</thead>
	<tbody>
		<tr class="tabletrline2" v-for="(item,index) in items">
			<td v-text="item.date"></td>
			<td class="status" :class="{ok:item.result=='OK'}" v-text="l10n[item.result] ?? item.result"></td>
			<td>
				<a target="_blank" :href="'https://www.infobyip.com/?ip='+item.ip" v-text="item.ip"></a>
			</td>
			<td v-text="item.ua"></td>
		</tr>
	</tbody>
</table>
<div class="warning" v-else>
	<img src="static/user-area/images/info.png" alt="">
	<h4>{$this->l10n['no-records']}</h4>
	<a v-if="is_filtered" :href="Filter(['date'])">{$this->l10n['clean-filter']}</a>
	<div class="clr"></div>
</div>
$paginator
</script>
HTML )
			->content->BaseBlock()
			->content->index(
				title:[$this->l10n['sign-in-log'],$this->l10n['title']],
			);
	}

	/** Loading indicator
	 * @param string $cond Condition for showing
	 * @return string */
	private function Loading(string$cond='loading'):string
	{
		return<<<HTML
<teleport to="body">
<div id="loading" v-if="$cond">
	<span>{$this->l10n['loading']}</span>
</div>
</teleport>
HTML;
	}
};
