<?php
namespace CMS;

use Eleanor\Classes\L10n;

/** List of static pages. Logic is located in cms/units/static/dashboard.php
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
$script='static/dashboard/static-items.js';

$data=['L10N'=>L10N,'L10NS'=>L10NS,'items'=>[]]
	+\compact('items','can_create','can_delete','total','pp','sort','desc');

foreach($items as $item)
{
	$item['modified']=L10n::Date($item['modified']);

	$data['items'][]=$item;
}

$say_total=$l10n['say-total']($total);
$paginator=(CMS::$T)('app-paginator');

//ToDo Выбор языка. По умолчанию отображаются только страницы определённого языка
$template=<<<HTML
<div class="d-flex justify-content-between mb-2">
	<h1 class="h3 mb-0 pt-1"><i class="nav-icon fa-solid fa-file d-none d-md-inline"></i> {$l10n['title']}</h1>
	<div class="gap-1 gap-md-2 d-flex align-items-end">
		<div class="dropdown">
			<button type="button" class="btn bg-gradient d-block d-md-none" :class="is_filtered ? 'btn-info' : 'btn-secondary'" title="{$l10n['filter']}" data-coreui-toggle="dropdown"><i class="fa-solid fa-filter"></i></button>
			<button type="button" class="btn bg-gradient d-none d-md-block" :class="is_filtered ? 'btn-info' : 'btn-secondary'" data-coreui-toggle="dropdown"><i class="fa-solid fa-filter me-2"></i> {$l10n['filter']}</button>
			<form class="filter dropdown-menu dropdown-menu-end p-3 bg-body-secondary" style="min-width:18rem">
				<input type="hidden" v-for="[name,value] in Filter(['name'],false)" :name :value />
				<p v-if="id" class="d-flex mb-1">
					<span>{$l10n['by-id']}</span>
					<mark v-text="id" class="py-0 ms-1"></mark>
					<a :href="Filter(['id'])" class="ms-auto small"><i class="fa-solid fa-xmark"></i></a>
				</p>
				<div class="mb-1">
					<label for="filter-name" class="form-label mb-1">{$l10n['caption']}</label><a :href="Filter(['title'])" v-if="title" v-once class="ms-2 small"><i class="fa-solid fa-xmark"></i></a>
					<input type="text" name="title" class="form-control" id="filter-title" :value="title" autocomplete="off">
				</div>
				<button type="submit" class="btn btn-primary bg-gradient">{$l10n['do-filter']}</button>
			</form>
		</div>
		<a v-if="can_create" role="button" class="btn btn-primary bg-gradient d-block d-md-none" title="{$l10n['create']}" v-once :href="ItemURL(0)"><i class="fa-solid fa-file-circle-plus fa-lg"></i></a>
		<a v-if="can_create" role="button" class="btn btn-primary bg-gradient d-none d-md-block" v-once :href="ItemURL(0)"><i class="fa-solid fa-file-circle-plus fa-lg me-2"></i>{$l10n['create']}</a>
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
					<i v-if="sort=='location'" class="fa-solid" :class="desc ? 'fa-arrow-up-z-a' : 'fa-arrow-up-a-z'"></i>
					<a :href="Sort('location')" class="text-decoration-none">{$l10n['location']}</a>
					<a :href="Filter(['sort','order'])" v-if="sort=='location'" class="ms-3 small"><i class="fa-solid fa-xmark"></i></a>
				</th>
				<th class="bg-body-secondary">{$l10n['status']}</th>
				<th class="bg-body-secondary text-end">{$l10n['modified']}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr class="align-middle" v-for="(item,index) in items" :id="'item-'+item.id" :class="{'table-danger':item.empty_password}" :title="item.empty_password ? l10n.empty_password : ''">
				<td v-text="item.id" class="text-end"></td>
				<td v-text="item.title"></td>
				<td v-text="item.slug || '&mdash;'"></td>
				<td v-text="item.status"></td>
				<td v-text="item.modified"></td>
				<td></td>
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
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('title'));
