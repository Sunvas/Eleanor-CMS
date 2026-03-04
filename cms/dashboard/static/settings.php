<?php
namespace CMS;

/** Unit static pages: page with settings
 * @var array $config stored configuration
 * @var \Generator $groups of users who has access to dashboard
 * Default:
 * @var array $links List of links */

$l10n=new \Eleanor\Classes\L10n('settings',__DIR__.'/l10n/');
$data=[
	'config'=>$config,
	'groups'=>\iterator_to_array($groups),
];
$title=[$l10n['title']];
$script='static/dashboard/static-settings.js';
$head['style']=(CMS::$T)('coloring-of-groups');

$template=<<<HTML
<h1 class="h3"><i class="nav-icon fa-solid fa-screwdriver"></i> {$l10n['title']}</h1>

<form @submit.prevent="Submit">
	<div class="row">
		<div class="col-lg-4 col-sm-6 col-xs-12">
			<div class="card border-primary border-top-3">
				<h2 class="card-header bg-primary bg-gradient lh-base h6" style="--cui-bg-opacity: .25;">{$l10n['rights']}</h2>
				<div class="card-body">
					<div class="mb-1">
						<label for="group">{$l10n['group']}</label>
						<select class="form-select form-select-sm" v-model="group" id="group">
							<option v-for="group in groups" :value="group.id" v-text="group.title" :class="'group-'+group.id"></option>
						</select>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="create" v-model="config.create" :value="group">
						<label class="form-check-label user-select-none" for="create">{$l10n['create']}</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="delete" v-model="config.delete" :value="group">
						<label class="form-check-label user-select-none" for="delete">{$l10n['delete']}</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card mt-2">
		<div class="card-body">
			<button class="btn btn-primary bg-gradient btn-lg" :disabled="saved || saving"><i class="fa-solid fa-spinner fa-spin-pulse" v-if="saving"></i> {{submit_text}}</button>
		</div>
	</div>
</form>
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('title','head'));