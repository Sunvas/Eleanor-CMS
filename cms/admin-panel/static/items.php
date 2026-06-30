<?php
namespace CMS;

/** List of static pages. Logic is located in cms/units/static/admin-panel.php
 * @var \Generator $items Static pages
 * @var int $total Total static pages
 * @var int $pp Amount of static pages per page
 * @var string $sort Sorting field
 * @var bool $desc Flag of descending sorting
 * @var bool $can_create
 * @var bool $can_delete
 * Default:
 * @var array $links List of links */

$l10n=new L10n('items',__DIR__.'/l10n/');

$title=[$l10n['title']];
$script='static/admin-panel/static-items.js';

$items=Iterator2Array($items,function($item){
	$item['modified']=L10n::Date($item['modified']);
	return $item;
});

$data=\compact('items','can_create','can_delete','total','pp','sort','desc')
	+['L10N'=>L10N,'L10NS'=>L10NS];

$confirm=require __DIR__.'/../includes/dialog-confirm.php';
$say_total=$l10n['say-total']($total);
$paginator=(CMS::$T)('app-paginator');

$template=<<<HTML
<div class="d-flex gap-1 gap-md-2 mb-2">
	<h1 class="h3 mb-0 pt-1 flex-grow-1"><i class="nav-icon fa-solid fa-file d-none d-md-inline"></i> {$l10n['title']}</h1>
	<div v-if="l10ns.length>0">
		<select class="form-select text-truncate" style="width:clamp(4.4rem,100%,10rem)" v-model="lang" @change="LangChanged">
			<option v-for="[code,title] in l10ns" :value="code" v-text="title"></option>
		</select>
	</div>
	<div class="dropdown">
		<button type="button" class="btn bg-gradient d-block d-lg-none" :class="is_filtered ? 'btn-info' : 'btn-secondary'" title="{$l10n['filter']}" data-coreui-toggle="dropdown"><i class="fa-solid fa-filter"></i></button>
		<button type="button" class="btn bg-gradient d-none d-lg-block" :class="is_filtered ? 'btn-info' : 'btn-secondary'" data-coreui-toggle="dropdown"><i class="fa-solid fa-filter me-2"></i> {$l10n['filter']}</button>
		<form class="dropdown-menu dropdown-menu-end p-3 bg-body-secondary" style="min-width:18rem">
			<input type="hidden" v-for="[name,value] in Filter(['id','title','slug'],false)" :name :value />
			<p v-if="id" class="d-flex mb-1">
				<span>{$l10n['by-id']}</span>
				<mark v-text="id" class="py-0 ms-1"></mark>
				<a :href="Filter(['id'])" class="ms-auto small"><i class="fa-solid fa-xmark"></i></a>
			</p>
			<div class="mb-1">
				<label for="filter-title" class="form-label mb-1">{$l10n['caption']}</label><a :href="Filter(['title'])" v-if="title" v-once class="ms-2 small"><i class="fa-solid fa-xmark"></i></a>
				<input type="text" name="title" class="form-control" id="filter-title" :value="title" autocomplete="off">
			</div>
			<div class="mb-1">
				<label for="filter-slug" class="form-label mb-1">{$l10n['slug']}</label><a :href="Filter(['slug'])" v-if="slug" v-once class="ms-2 small"><i class="fa-solid fa-xmark"></i></a>
				<input type="text" name="slug" class="form-control" id="filter-slug" :value="slug" autocomplete="off">
			</div>
			<button type="submit" class="btn btn-primary bg-gradient">{$l10n['do-filter']}</button>
		</form>
	</div>
	<div v-if="can_create">
		<button type="button" class="btn btn-primary bg-gradient d-block d-lg-none" @click="Create" title="{$l10n['create']}" v-once><i class="fa-solid fa-file-circle-plus fa-lg"></i></button>
		<button type="button" class="btn btn-primary bg-gradient d-none d-lg-block" @click="Create" v-once><i class="fa-solid fa-file-circle-plus fa-lg me-2"></i>{$l10n['create']}</button>
	</div>
</div>

<template v-if="items.length>0">
<div class="table-responsive" style="min-height:12em">
	<table class="table border mb-0">
		<thead class="fw-semibold text-nowrap">
			<tr>
				<th class="bg-body-secondary text-center" style="width:2.5rem">
					<i v-if="sort=='id'" class="fa-solid" :class="desc ? 'fa-arrow-up-9-1' : 'fa-arrow-up-1-9'"></i>
					<a :href="Sort('id')" class="text-decoration-none">ID</a>
				</th>
				<th class="bg-body-secondary">
					<i v-if="sort=='title'" class="fa-solid" :class="desc ? 'fa-arrow-up-z-a' : 'fa-arrow-up-a-z'"></i>
					<a :href="Sort('title')" class="text-decoration-none">{$l10n['caption']}</a>
					<a :href="Filter(['sort','order'])" v-if="sort=='title'" class="ms-3 small"><i class="fa-solid fa-xmark"></i></a>
				</th>
				<th class="bg-body-secondary">
					<i v-if="sort=='slug'" class="fa-solid" :class="desc ? 'fa-arrow-up-z-a' : 'fa-arrow-up-a-z'"></i>
					<a :href="Sort('slug')" class="text-decoration-none">{$l10n['slug']}</a>
					<a :href="Filter(['sort','order'])" v-if="sort=='slug'" class="ms-3 small"><i class="fa-solid fa-xmark"></i></a>
				</th>
				<th class="bg-body-secondary">{$l10n['status']}</th>
				<th class="bg-body-secondary" colspan="2">
					<i v-if="sort=='modified'" class="fa-solid" :class="desc ? 'fa-arrow-up-z-a' : 'fa-arrow-up-a-z'"></i>
					<a :href="Sort('modified')" class="text-decoration-none">{$l10n['modified']}</a>
					<a :href="Filter(['sort','order'])" v-if="sort=='modified'" class="ms-3 small"><i class="fa-solid fa-xmark"></i></a>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="align-middle" v-for="(item,index) in items" :id="'item-'+item.id" :class="{'table-danger':item.empty_password}" :title="item.empty_password ? l10n.empty_password : ''">
				<td v-text="item.id" class="text-end"></td>
				<td class="overflow-x-auto"><a :href="ItemURL(item.id)" class="text-decoration-none icon-link link-primary"><i class="fa-solid fa-pen-to-square"></i> {{item.title}}</a></td>
				<td class="overflow-x-auto">
					<a :href="Link2UserArea(item)" v-if="item.slug && item.status==='ACTIVE'"class="text-decoration-none icon-link link-info" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> {{item.slug}}</a>
					<span v-else-if="item.slug" v-text="item.slug"></span>
					<span v-else>&mdash;</span>
				</td>
				<td v-text="l10n[item.status] ?? item.status" :class="{'text-danger':item.status==='DRAFT','text-success':item.status==='ACTIVE'}"></td>
				<td v-text="item.modified"></td>
				<td class="text-end"><span v-if="can_delete" role="button" @click="Delete(item,index)" title="{$l10n['delete']}">❌</span></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="row mb-1 gap-1 gap-md-0">
	<div class="col-12 col-md order-1 order-md-2 mt-2 mt-md-0"><div class="mx-auto" style="width: fit-content">{$paginator}</div></div>
	<div class="col order-2 order-md-1 pt-1">{$say_total}</div>
	<ul class="col order-3 nav justify-content-end">
		<li class="nav-item" v-for="item in pps">
			<b v-if="item==pp" class="nav-link ps-3 pe-0 py-1 disabled" v-text="item"></b>
			<a v-else :href="PP(item)" class="nav-link ps-3 pe-0 py-1" v-text="item"></a>
		</li>
	</ul>
</div>
</template>

<div v-else class="alert alert-info"><i class="fa-solid fa-info"></i> {$l10n['nothing-found']}</div>

{$confirm}
<dialog class="modal modal-lg fade bg-transparent" ref="creating" tabindex="-1" data-coreui-backdrop="static">
	<div class="modal-dialog">
		<form class="modal-content" @submit.prevent="CreateSubmit">
			<div class="modal-header">
				<h5 class="modal-title">{$l10n['creating']}</h5>
				<button type="button" class="btn-close" tabindex="-1" data-coreui-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<input type="text" class="form-control" placeholder="{$l10n['caption']}" v-model.lazy="creating_title" required maxlength="100">
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary bg-gradient px-4" tabindex="1" :disabled="saving"><i class="fa-solid fa-spinner fa-spin-pulse" v-if="saving"></i> {$l10n['create']}</button>
			</div>
		</form>
	</div>
</dialog>
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('title'));
