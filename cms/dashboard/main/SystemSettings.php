<?php
namespace CMS;

/** Page with settings of the system: options here matter mainly for code not from HTML templates. Values are reachable via CMS::$config['system']
 * @var array $config stored configuration
 * Default:
 * @var array $links List of links */

$l10n=new \Eleanor\Classes\L10n('system-settings',__DIR__.'/l10n/');
$data=['L10N'=>L10N,'L10NS'=>L10NS,'config'=>$config];
$title=[$l10n['title']];
$script='static/dashboard/system-settings.js';

$template=<<<HTML
<div class="d-flex justify-content-between mb-2">
	<h1 class="h3 mb-0"><i class="nav-icon fa-solid fa-gear"></i> {$l10n['settings']}</h1>
	<ul class="nav nav-underline justify-content-end">
		<li class="nav-item">
			<a class="nav-link py-1 text-body border-bottom border-2" href="{$links['settings']}">{$l10n['site']}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link py-1 text-primary active" href="{$links['system-settings']}">{$l10n['system']}</a>
		</li>
	</ul>
</div>

<form @submit.prevent="Submit">
	<div class="card border-info border-top-3">
		<h2 class="card-header bg-info bg-gradient lh-base h6" style="--cui-bg-opacity: .25;">Telegram <a href="https://core.telegram.org/bots" target="_blank" class="fa-solid fa-up-right-from-square"></a></h2>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 col-12">
					<label for="bot_name">{$l10n['bot_name']}</label>
					<input type="text" class="form-control" id="bot_name" v-model.trim="config.bot_name">
					<small class="text-secondary">{$l10n['bot_name_']}</small>
				</div>
				<div class="col-md-8 col-12">
					<label for="bot_key">{$l10n['bot_key']}</label>
					<input type="text" class="form-control" id="bot_key" v-model.trim="config.bot_key" placeholder="0000000000:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" pattern="\d+:[A-Za-z\d\-_]{10,}">
				</div>
			</div>
		</div>
	</div>
	<div class="card border-secondary border-top-3 mt-2">
		<h2 class="card-header bg-secondary bg-gradient lh-base h6" style="--cui-bg-opacity: .25;">{$l10n['hcaptcha']} <a href="https://hcaptcha.com/?r=2b68096cb450" target="_blank" class="fa-solid fa-up-right-from-square"></a></h2>
		<div class="card-body">
			<div class="form-check form-switch mb-2">
				<input class="form-check-input" type="checkbox" role="switch" id="captcha" v-model="config.captcha">
				<label class="form-check-label" for="captcha">{$l10n['captcha']}</label>
			</div>
			<div class="row">
				<div class="col-md-6 col-lg-4 col-12">
					<label for="hcaptcha">SiteKey</label>
					<input type="text" class="form-control" id="hcaptcha" v-model.trim="config.hcaptcha" placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX" pattern="[a-f\d]{8}-[a-f\d]{4}-[a-f\d]{4}-[a-f\d]{4}-[a-f\d]{12}">
				</div>
				<div class="col-md-6 col-lg-5 col-12">
					<label for="hcaptcha_secret">Secret</label>
					<input type="text" class="form-control" id="hcaptcha_secret" v-model.trim="config.hcaptcha_secret" placeholder="0xXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" pattern="0x[A-Fa-f\d]{40}">
				</div>
			</div>
		</div>
	</div>
	<div class="card border-warning border-top-3 mt-2">
		<h2 class="card-header bg-warning bg-gradient lh-base h6" style="--cui-bg-opacity: .25;">{$l10n['service']}</h2>
		<div class="card-body">
			<div class="form-check form-switch mb-2">
				<input class="form-check-input" type="checkbox" role="switch" id="maintenance" v-model="config.maintenance">
				<label class="form-check-label" for="maintenance">{$l10n['maintenance']}</label>
			</div>
		</div>
	</div>
	<div class="card mt-2">
		<div class="card-body d-flex justify-content-between">
			<button class="btn btn-primary bg-gradient btn-lg" :disabled="saved || saving"><i class="fa-solid fa-spinner fa-spin-pulse" v-if="saving"></i> {{submit_text}}</button>
			<select class="form-select ms-3" v-if="l10ns.length>0" v-model="lang" style="max-width:10rem">
				<option v-for="[code,title] in l10ns" :value="code" v-text="title"></option>
			</select>
		</div>
	</div>
</form>
HTML;


return CMS::$T->app(\compact('data','script','template'))->content->index(title:$title);