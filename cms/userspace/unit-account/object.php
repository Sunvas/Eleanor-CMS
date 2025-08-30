<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Classes\L10n;

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
			$this->l10n['add-user'],
			[$this->l10n['change-password'],$links['change-password']],
			[$this->l10n['settings'],$links['settings']],
			[$this->l10n['sessions'],$links['sessions']],
		];

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Message($this->l10n[$error] ?? $error,'warning')

			->content->index([$this->l10n['user-sign-in']]);
	}

	/** Page with login form
	 * @param array $links Default links
	 * @param string $hcaptcha Default hcaptcha
	 * @return string */
	function SignIn(array$links=[],string$hcaptcha='',...$d):string
	{
		if(CMS::$A->current)
		{
			$menu=[
				$this->l10n['add-user'],
				[$this->l10n['change-password'],$links['change-password']],
				[$this->l10n['settings'],$links['settings']],
				[$this->l10n['sessions'],$links['sessions']],
			];
			$nonce=Nonce();

			#Telegram sign in
			$telegram='';
			if(CMS::$config['system']['bot_name'])
			{
				$bot=CMS::$config['system']['bot_name'];
				$telegram=<<<HTML
<hr>
<div style="padding-left:25ch">
	<script async nonce="{$nonce}" src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="{$bot}" data-size="medium" data-onauth="TelegramAuth(user)" data-request-access="write"></script>
</div>
HTML;
			}

			return CMS::$T->Heading($this->l10n['title'],menu:$menu)
				->hcaptcha()
				->Append(<<<HTML
<script src="static/userspace/widget-sign-in.js" nonce="{$nonce}" defer data-account="{$links['sign-in']}" data-container="#sign-in" data-template="#sign-in-tpl" data-hcaptcha="{$hcaptcha}"></script>
<div id="sign-in"></div>{$telegram}
<script id="sign-in-tpl" type="text/x-template">
<form @submit.prevent="Submit">
<table class="tabstyle tabform">
	<tr>
		<th class="label">
			<label for="username">{$this->l10n['username']}</label>
		</th>
		<td><input tabindex="1" type="text" id="username" autocomplete="username" v-model.trim="username" :disabled="loading" autofocus required></td>
	</tr>
	<tr>
		<th class="label">
			<label for="password">{$this->l10n['password']}</label>
		</th>
		<td><input tabindex="1" type="password" id="password" autocomplete="current-password" v-model="password" :disabled="loading" required></td>
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
			<input tabindex="1" type="submit" value="{$this->l10n['sign-in']}">
		</td>
	</tr>
</table>
</form>
</script>
HTML )

				->content->index([$this->l10n['user-sign-in']]);
		}

		return CMS::$T->Heading($this->l10n['title'])
			->Message($this->l10n['use-widget'],'info')
			->content->index([$this->l10n['user-sign-in']]);
	}

	/** Page with error of signing up: telegram credentials expected
	 * @param string $error Error code
	 * @return string */
	function SignUpError(string$error,...$d):string
	{
		return CMS::$T->Heading($this->l10n['signing-up'])
			->Message($this->l10n[$error] ?? $error,$error=='MISSED_TELEGRAM' ? 'warning' : 'info')

			->content->index([$this->l10n['signing-up']]);
	}

	/** Page signing up form
	 * @param int $mpl Minimum password length
	 * @param array $telegram Telegram credentials
	 * @return string */
	function SignUp(int$mpl,array$telegram,...$d):string
	{
		$d_name=trim($telegram['first_name'].' '.$telegram['last_name']);
		$nonce=Nonce();

		return CMS::$T->Heading($this->l10n['signing-up'])
			->Append(<<<HTML
<div id="app"></div>
<script src="static/userspace/unit-account-sign-up.js" nonce="{$nonce}" defer data-container="#app" data-template="#app-tpl" data-avatar="{$telegram['photo_url']}" data-name="{$telegram['username']}" data-display_name="{$d_name}"></script>
<script id="app-tpl" type="text/x-template">
<form @submit.prevent="Submit">
	<table class="tabstyle tabform">
		<tr>
			<th class="label" style="width:25ch">Telegram</th>
			<td v-once>
				<a :href="'//t.me/'+name" target="_blank" v-if="name" v-text="display_name"></a>
				<span v-else v-text="display_name"></span>
			</td>
		</tr>
		<tr>
			<th class="label">
				<label for="name"><span class="labinfo" title="{$this->l10n['username_']}">(?)</span> {$this->l10n['username']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="text" v-model.lazy="name" :class="{error:name_error}" id="name" ref="name" autocomplete="username" maxlength="25" required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="display-name">{$this->l10n['display_name']}</label>
			</th>
			<td><input tabindex="1" type="text" id="display-name" v-model="display_name" autocomplete="name" maxlength="25"></td>
		</tr>
		<tr>
			<th class="label">
				<label for="password">{$this->l10n['password']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="password" v-model="password" autocomplete="new-password" minlength="{$mpl}" required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="password2">{$this->l10n['password2']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="password2" ref="password2" v-model.lazy="password2" autocomplete="new-password" minlength="{$mpl}" required></td>
		</tr>
		<tr>
			<th class="label"></th>
			<td><input tabindex="1" type="submit" value="{$this->l10n['register']}" :disabled="loading"></td>
		</tr>
	</table>
</form>
</script>
HTML )

		->content->index([$this->l10n['signing-up']]);
	}

	/** Page with information about successful signing out
	 * @return string */
	function SignOut(...$d):string
	{
		$url=Uri::$base;
		$refresh=<<<HTML
<meta http-equiv="refresh" content="2;url={$url}">
HTML;

		return CMS::$T->Heading($this->l10n['signed-out'])
			->Message($this->l10n['signed-out_'],'info')

			->content->index(
				head:[$refresh],
				title:[$this->l10n['signed-out']],
			);
	}

	/** Settings of the user
	 * @param array $settings values of settings
	 * @param array $timezones array of timezones
	 * @param array $links Default links
	 * @return string */
	function Settings(array$settings,array$timezones,array$links=[],...$d):string
	{
		$menu=[
			[$this->l10n['add-user'],$links['sign-in']],
			[$this->l10n['change-password'],$links['change-password']],
			$this->l10n['settings'],
			[$this->l10n['sessions'],$links['sessions']],
		];
		$id=CMS::$A->current;
		$nonce=Nonce();
		$data=\json_encode(\compact('settings','timezones'),JSON);

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Append(<<<HTML
<div id="app"></div>
<script src="static/userspace/unit-account-settings.js" nonce="{$nonce}" defer data-container="#app" data-template="#app-tpl" data-data="#app-data"></script>
<script id="app-data" type="application/json">{$data}</script>
<script id="app-tpl" type="text/x-template">
<form @submit.prevent="Submit">
	<table class="tabstyle tabform">
		<tr>
			<th class="label">{$this->l10n['avatar']}</th>
			<td>
				<div v-if="has_avatar"> 
					<img v-if="avatar" :src="avatar" alt="{$this->l10n['avatar']}">
					<img v-else :src="'static/avatars/{$id}-'+settings.avatar+'.webp'" alt="{$this->l10n['avatar']}">
				</div>
				<input type="button" :value="avatar_button" @click="UploadAvatar">
			</td>
		</tr>
		<tr>
			<th class="label">
				<label for="display-name">{$this->l10n['display_name']}</label>
			</th>
			<td><input tabindex="1" type="text" id="display-name" v-model="settings.display_name" @change="Changed('display_name')" autocomplete="name" maxlength="35"></td>
		</tr>
		<tr>
			<th class="label">
				<label for="info">{$this->l10n['info']}</label>
			</th>
			<td><textarea id="info" v-model="settings.info" rows="3" @change="Changed('info')"></textarea></td>
		</tr>
		<tr>
			<th class="label">
				<label for="timezone">{$this->l10n['timezone']}</label>
			</th>
			<td>
				<select id="timezone" v-model="settings.timezone" @change="Changed('timezone')">
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
				<select id="l10n" v-model="settings.l10n" @change="Changed('l10n')">
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
</script>
HTML )

			->content->index(
				[$this->l10n['settings'],$this->l10n['title']],
			);
	}

	/** Change password of user's account
	 * @param int $mpl Minimum password length
	 * @param bool $old_required Flag when asking of the old password is required
	 * @param array $links Default links
	 * @return string */
	function ChangePassword(int$mpl,bool$old_required,array$links,...$d):string
	{
		$menu=[
			[$this->l10n['add-user'],$links['sign-in']],
			$this->l10n['change-password'],
			[$this->l10n['settings'],$links['settings']],
			[$this->l10n['sessions'],$links['sessions']],
		];
		$nonce=Nonce();

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Append(<<<HTML
<div id="app"></div>
<script src="static/userspace/unit-account-change-password.js" nonce="{$nonce}" defer data-container="#app" data-template="#app-tpl" data-old_required="{$old_required}"></script>
<script id="app-tpl" type="text/x-template">
<form @submit.prevent="Submit">
	<table class="tabstyle tabform">
		<tr v-if="old_required">
			<th class="label">
				<label for="old-password">{$this->l10n['old-password']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="old-password" v-model="old_password" autocomplete="current-password" required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="password">{$this->l10n['password']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="password" v-model="password" autocomplete="new-password" minlength="{$mpl}" required></td>
		</tr>
		<tr>
			<th class="label">
				<label for="password2">{$this->l10n['password2']} <span class="imp">*</span></label>
			</th>
			<td><input tabindex="1" type="password" id="password2" ref="password2" v-model.lazy="password2" autocomplete="new-password" minlength="{$mpl}" required></td>
		</tr>
		<tr>
			<th class="label"></th>
			<td><input tabindex="1" type="submit" value="{$this->l10n['set-password']}" :disabled="saving"></td>
		</tr>
	</table>
</form>
</script>
HTML )

			->content->index(
				[$this->l10n['password-changing'],$this->l10n['title']],
			);
	}

	/** List of user's sessions
	 * @param array $sessions List of sessions
	 * @param int $mtss Months to stale session
	 * @param array $links Default links
	 * @return string */
	function Sessions(array$sessions,int$mtss,array$links,...$d):string
	{
		$menu=[
			[$this->l10n['add-user'],$links['sign-in']],
			[$this->l10n['change-password'],$links['change-password']],
			[$this->l10n['settings'],$links['settings']],
			$this->l10n['sessions'],
		];
		$nonce=Nonce();
		$current=CMS::$a11n;

		foreach($sessions as &$session)
		{
			$session['sort']=strtotime($session['used']);
			$session['used']=(int)$session['used']>2000 ? L10n::Date($session['used']) : '';
			$session['created']=L10n::Date($session['created']);
		}
		$data=\json_encode($sessions,JSON);

		return CMS::$T->Heading($this->l10n['title'],menu:$menu)
			->Append(<<<HTML
<div id="app" class="binner"></div>
<script src="static/userspace/unit-account-sessions.js" nonce="{$nonce}" defer data-container="#app" data-template="#app-tpl" data-current="{$current}" data-data="#app-data"></script>
<script id="app-data" type="application/json">{$data}</script>
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
		<tr class="tabletrline2" v-for="(session,index) in sessions" :class="{current:session.a11n_id==current}" :title="session.a11n_id==current ? l10n.current : ''">
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

			->content->index(
				[$this->l10n['signed-out']],
			);
	}
};
