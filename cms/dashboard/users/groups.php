<?php
namespace CMS;

/** List of groups of users
 * @var \Generator $items Groups
 * @var array $roles List of roles
 * Default:
 * @var array $links List of links */

$l10n=new \Eleanor\Classes\L10n('groups',__DIR__.'/l10n/');
$data=['L10N'=>L10N,'L10NS'=>L10NS,'roles'=>$roles,'items'=>\iterator_to_array($items)];

$title=[$l10n['title']];
$script='static/dashboard/groups.js';
$head['style']=(CMS::$T)('coloring-of-groups');

$template=<<<HTML
<div class="d-flex justify-content-between mb-2">
	<h1 class="h3 mb-0 pt-1"><i class="nav-icon fa-solid fa-user-group"></i> {$l10n['title']}</h1>
	<button type="button" class="btn btn-primary bg-gradient d-block d-sm-none" @click="Create" title="{$l10n['create']}"><i class="fa-solid fa-plus fa-lg"></i></button>
	<button type="button" class="btn btn-primary bg-gradient d-none d-sm-block" @click="Create"><i class="fa-solid fa-folder-plus fa-lg me-2"></i>{$l10n['create']}</button>
</div>

<div class="table-responsive">
	<table class="table border mb-0">
		<thead class="fw-semibold text-nowrap">
			<tr>
				<th class="bg-body-secondary text-center" style="width:2.5rem"></th>
				<th class="bg-body-secondary">{$l10n['caption']}</th>
				<th class="bg-body-secondary">{$l10n['roles']}</th>
				<th class="bg-body-secondary text-end" title="{$l10n['slow_mode_']}">{$l10n['slow_mode']}</th>
				<th class="bg-body-secondary"></th>
			</tr>
		</thead>
		<tbody>
			<tr class="align-middle" v-for="(item,index) in items" :id="'item-'+item.id">
				<td class="dropdown">
					<button class="btn btn-transparent p-0" type="button" data-coreui-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
					<ul class="dropdown-menu py-0">
						<li><button class="dropdown-item" type="button" @click="Modify(item,index)"><i class="fa-solid fa-pencil me-2"></i> {$l10n['modify']}</button></li>
						<li v-if="item.filter_users"><a :href="item.filter_users" class="dropdown-item text-primary" target="_blank"><i class="fa-solid fa-user-tag me-2"></i> {$l10n['show-users']}</a></li>
						<li v-if="item.deletable"><button class="dropdown-item text-danger" type="button" @click="Delete(item,index)"><i class="fa-solid fa-trash-can me-2"></i> {$l10n['delete']}</button></li>
					</ul>
				</td>
				<td v-text="item.title" :class="'group-'+item.id"></td>
				<td v-text="item.roles.join(', ') || '&mdash;'"></td>
				<td v-text="item.slow_mode || '&mdash;'" class="text-end"></td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>

<dialog class="modal fade bg-transparent" ref="confirm" tabindex="-1" data-coreui-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" v-text="confirm_title"></h5>
				<button type="button" class="btn-close" tabindex="-1" data-coreui-dismiss="modal"></button>
			</div>
			<div class="modal-body" v-text="confirm"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary bg-gradient px-4" data-coreui-dismiss="modal" ref="confirm_dismiss" tabindex="2">{$l10n['no']}</button>
				<button type="button" class="btn btn-primary bg-gradient px-4" data-coreui-dismiss="modal" @click="Confirmed" tabindex="1">{$l10n['yes']}</button>
			</div>
		</div>
	</div>
</dialog>

<dialog class="modal fade bg-transparent" ref="group" tabindex="-1" data-coreui-backdrop="static">
	<div class="modal-dialog">
		<form class="modal-content" @submit.prevent="Submit">
			<div class="modal-header">
				<h5 class="modal-title" v-text="group_title"></h5>
				<button type="button" class="btn-close" tabindex="-1" data-coreui-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-1e">
					<div class="col">
						<label for="group-title" class="form-label mb-0">{$l10n['caption']}</label>
						<input type="text" class="form-control" id="group-title" v-model.lazy="group.title" @change="Changed('title')" required maxlength="25">
					</div>
					<div class="col" v-if="l10ns.length>0">
						<label for="lang" class="form-label mb-0"><i class="fa-solid fa-arrow-left"></i> {$l10n['l10n']}</label>
						<select id="lang" class="form-select" v-model="lang" @change="Changed('l10n')">
							<option v-for="[code,title] in l10ns" :value="code" v-text="title"></option>
						</select>
					</div>
				</div>
				<hr v-if="l10ns.length>0">
				<div class="mb-1">
					<label for="group-roles" class="form-label mb-0">{$l10n['roles']}</label>
					<select id="group-roles" :disabled="group_id && group_id<5" class="form-select" multiple size="3" v-model="group.roles" @change="Changed('roles')">
						<option v-for="role in roles" :value="role" v-text="role" :class="'role-'+group.id"></option>
					</select>
				</div>
				<div class="row mb-1">
					<div class="col col-md-6">
						<label for="group-sm" class="form-label mb-0" title="{$l10n['slow_mode_']}">{$l10n['slow_mode']}</label>
						<input type="number" class="form-control" :disabled="group_id && group_id<3" id="group-sm" v-model.lazy="group.slow_mode" @change="Changed('slow_mode')" min="0">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary bg-gradient px-4" tabindex="1" :disabled="saving || saved"><i class="fa-solid fa-spinner fa-spin-pulse" v-if="saving"></i> {{submit_text}}</button>
			</div>
		</form>
	</div>
</dialog>
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('title','head'));
