<?php
namespace CMS;

/** Page with settings of the site: options here matter mainly for users (for HTML templates). Values are reachable via CMS::$config['site']
 * @var array $config stored configuration
 * Default:
 * @var array $links List of links */

$l10n=new \Eleanor\Classes\L10n('site-settings',__DIR__.'/l10n/');
$data=['L10N'=>L10N,'L10NS'=>L10NS,'config'=>$config];
$title=[$l10n['title']];
$script='static/dashboard/site-settings.js';

$template=<<<HTML
<div class="d-flex justify-content-between mb-2">
	<h1 class="h3 mb-0"><i class="nav-icon fa-solid fa-gear"></i> {$l10n['settings']}</h1>
	<ul class="nav nav-underline justify-content-end">
		<li class="nav-item">
			<a class="nav-link py-1 text-primary active" href="{$links['settings']}">{$l10n['site']}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link py-1 text-body border-bottom border-2" href="{$links['system-settings']}">{$l10n['system']}</a>
		</li>
	</ul>
</div>

<form @submit.prevent="Submit">
	<div class="card border-primary border-top-3">
		<h2 class="card-header bg-primary bg-gradient lh-base h6" style="--cui-bg-opacity: .25;">{$l10n['site-name']}</h2>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 col-12">
					<label for="name">{$l10n['site-title']}</label>
					<input type="text" class="form-control" id="name" v-model.trim="config.name" required>
				</div>
				<div class="col-md-8 col-12">
					<label for="title">{$l10n['main-title']}</label>
					<input type="text" class="form-control" id="title" v-model.trim="config.title" :placeholder="config.name">
				</div>
				<div class="col-12 mt-2">
					<label for="description">{$l10n['main-description']}</label>
					<input type="text" class="form-control" id="description" v-model.trim="config.description" required>
				</div>
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