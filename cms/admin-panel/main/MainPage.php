<?php
namespace CMS;

/** Contents of the main page of the site
 * @var string $content Current content
 * Default:
 * @var array $links List of links */

$scripts=$head=[];
require __DIR__.'/../includes/editorjs.php';

$l10n=new L10n('mainpage',__DIR__.'/l10n/');
$title=[$l10n['title']];
$script='static/admin-panel/main-mainpage.js';

$data=\json_encode(['L10N'=>L10N,'L10NS'=>L10NS],JSON);
$data=\substr($data,1,-1);
$data="{{$data},\"content\":{$content}}";

$template=<<<HTML
<h1 class="h3"><i class="nav-icon fa-solid fa-chalkboard d-none d-sm-inline"></i> {$l10n['title']}</h1>
<form @submit.prevent="Submit">
	<div class="card border-primary">
		<div class="card-body pt-1 px-0 pb-0" ref="editor" data-placeholder="{$l10n['placeholder']}" style="min-height: 50vh"></div>
	</div>
	<div class="card mt-2">
		<div class="card-body d-flex justify-content-between">
			<button class="btn btn-primary bg-gradient btn-lg" :disabled="saved || saving"><i class="fa-solid fa-spinner fa-spin-pulse" v-if="saving"></i> {{submit_text}}</button>
			<div class="h2" v-if="loading"><i class="fa-solid fa-spinner fa-spin-pulse"></i></div>
			<select class="form-select ms-3" v-if="l10ns.length>0" v-model="lang" :disabled="loading" style="max-width:10rem">
				<option v-for="[code,title] in l10ns" :value="code" v-text="title"></option>
			</select>
		</div>
	</div>
</form>
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('head','title','scripts'));
