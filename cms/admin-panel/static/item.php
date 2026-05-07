<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

/** Unit static pages: page of creating or updating static page
 * @var array $item key=>value of static page (an object of attention)
 * @var bool $can_delete Flag of right to delete static pages
 * Default:
 * @var array $links List of links */

$l10n=new L10n('item',__DIR__.'/l10n/');
$data=\compact('item','can_delete')+['L10N'=>L10N,'L10NS'=>L10NS];
$title=sprintf($l10n['updating%'],$item['title']);
$script='static/admin-panel/static-item.js';

$scripts=$head=[];
require __DIR__.'/../includes/editorjs.php';
$confirm=require __DIR__.'/../includes/dialog-confirm.php';

$template=<<<HTML
<h1 class="h3"><i class="nav-icon fa-solid fa-file"></i> {$title}</h1>

<form @submit.prevent="Submit">
	<div class="card border-primary-subtle">
		<div class="card-body">
			<div class="row g-2">
				<div class="col-lg-6 col-12">
					<div class="form-floating">
						<input type="text" v-model.trim="form.title" id="title" placeholder="{$l10n['title']}" class="form-control" maxlength="100" required>
						<label for="title">{$l10n['title']}</label>
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-12">
					<div class="form-floating">
						<input type="text" v-model.trim="form.slug" id="slug" placeholder="{$l10n['slug']}" class="form-control form-control-sm" maxlength="100" required>
						<label for="slug">{$l10n['slug']}</label>
					</div>
				</div>
				<div class="col-lg-2 col-md-6 col-12">
					<div class="form-floating">
						<select id="status" class="form-select" :class="{'text-success':form.slug && form.status=='ACTIVE','text-danger':!form.slug || form.status=='DRAFT'}" v-model="form.status" title="{$l10n['status']}">
							<option value="ACTIVE" :class="{'text-success':form.slug,'text-danger':!form.slug}">{$l10n['active']}</option>
							<option value="DRAFT" class="text-danger" title="{$l10n['draft_']}">{$l10n['draft']}</option>
						</select>
						<label for="status">{$l10n['status']}</label>
					</div>
				</div>
				<div class="col-12">
					<div class="form-floating">
						<input type="text" v-model.trim="form.description" id="description" placeholder="{$l10n['description']}" class="form-control form-control-sm" maxlength="250">
						<label for="description">{$l10n['description']}</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card border-primary my-2">
		<div class="card-body pt-1 px-0 pb-0" ref="editor" data-placeholder="{$l10n['placeholder']}" style="min-height: 50vh"></div>
	</div>

	<div class="card">
		<div class="card-body d-flex justify-content-between align-items-center">
			<button class="btn btn-primary bg-gradient btn-lg" :disabled="saved || saving"><i class="fa-solid fa-spinner fa-spin-pulse" v-if="saving"></i> {{submit_text}}</button>
			<div v-if="l10ns.length>0">
				<select id="lang" class="form-select" v-model="lang" :disabled="loading || saving" title="{$l10n['l10n']}">
					<option value="" class="text-info-emphasis fst-italic" title="{$l10n['mono_']}">{$l10n['mono']}</option>
					<option v-for="[code,title] in l10ns" :value="code" v-text="title" class="bg-opacity-25" :class="form.l10ns.has(code) ? 'bg-info' : 'bg-secondary'"></option>
					<option value="delete" class="text-danger-emphasis fst-italic" title="{$l10n['delete_']}">{$l10n['delete']}</option>
				</select>
			</div>
		</div>
	</div>
</form>
{$confirm}
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('head','title','scripts'));