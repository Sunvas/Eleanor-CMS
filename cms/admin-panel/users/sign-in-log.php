<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

/** List of users' sign-in attempts
 * @var \Generator $items List of attempts
 * @var \Generator $users List of users
 * @var int $total Total records
 * @var int $pp Amount per page
 * @var string $sort Sorting field
 * @var bool $desc Flag of descending sorting
 * @var bool $is_root Is current user an administrator
 * Default:
 * @var array $links List of links */

$l10n=new L10n('sign-in-log',__DIR__.'/l10n/');

$items=Iterator2Array($items,function($item){
	$item['ts']=\strtotime($item['date']);
	$item['date']=L10n::Date($item['date']);

	return $item;
});
$users=\iterator_to_array($users);

$data=\compact('items','users','total','pp','sort','desc')
	+['L10N'=>L10N,'L10NS'=>L10NS];

$title=[$l10n['title']];
$script='static/admin-panel/users-sign-in-log.js';
$head['style']=(CMS::$T)('coloring-of-groups');
$head['style2']=<<<'HTML'
<style>
input.form-control-plaintext[readonly] { min-height:0; }
input+hr {width: 1em}
input[type="date"] { width: 9em}
</style>
HTML;

$say_total=$l10n['say-total']($total);
$paginator=(CMS::$T)('app_paginator');

$template=<<<HTML
<div class="d-flex gap-1 gap-md-2 mb-2">
	<h1 class="h3 mb-0 pt-1 flex-grow-1"><i class="nav-icon fa-solid fa-address-book d-none d-md-inline"></i> {$l10n['title']}</h1>
	<div class="dropdown">
		<button type="button" class="btn bg-gradient d-block d-lg-none" :class="is_filtered ? 'btn-info' : 'btn-secondary'" title="{$l10n['filter']}" data-coreui-toggle="dropdown"><i class="fa-solid fa-filter"></i></button>
		<button type="button" class="btn bg-gradient d-none d-lg-block" :class="is_filtered ? 'btn-info' : 'btn-secondary'" data-coreui-toggle="dropdown"><i class="fa-solid fa-filter me-2"></i> {$l10n['filter']}</button>
		<form class="dropdown-menu dropdown-menu-end p-3 bg-body-secondary" style="min-width:18rem">
			<input type="hidden" v-for="[name,value] in Filter(['date','result'],false)" :name :value>
			<p v-if="id" class="d-flex mb-1">
				<span>{$l10n['by-user']}</span>
				<a v-if="filtered_user" class="ms-2" :href="Link2User(filtered_user)" target="_blank" :class="'group-'+filtered_user.group_id" v-text="filtered_user.user_name"></a>
				<mark v-else v-text="id" class="py-0 ms-1"></mark>
				<a :href="Filter(['id'])" class="ms-auto small"><i class="fa-solid fa-xmark"></i></a>
			</p>
			<div class="mb-1">
				<label for="filter-result" class="form-label mb-1">{$l10n['result']}</label><a :href="Filter(['result'])" v-if="result" v-once class="ms-2 small"><i class="fa-solid fa-xmark"></i></a>
				<select class="form-select" id="filter-result" v-model="result" name="result">
					<option value="">&mdash;</option>
					<option value="OK" class="bg-success-subtle">Ok</option>
					<option value="WRONG_TOTP" v-text="l10n.WRONG_TOTP"></option>
					<option value="WRONG_PASSWORD" v-text="l10n.WRONG_PASSWORD"></option>
					<option value="WRONG_RECOVERY_CODE" v-text="l10n.WRONG_RECOVERY_CODE"></option>
				</select>
			</div>
			<div class="mb-1">
				<label for="filter-date" class="form-label mb-1">{$l10n['date']}</label><a :href="Filter(['date'])" v-if="date" v-once class="ms-2 small"><i class="fa-solid fa-xmark"></i></a>
				<div class="d-flex justify-content-between">
					<input type="date" class="form-control me-1" id="filter-date" v-model="date_from" :max="max_date">
					<hr>
					<input type="date" class="form-control ms-1" v-model="date_to" :min="date_from" :max="today_date">
				</div>
				<input type="hidden" name="date" :value="date" :disabled="!date">
			</div>
			<button type="submit" class="btn btn-primary bg-gradient">{$l10n['do-filter']}</button>
		</form>
	</div>
</div>

<template v-if="items.length>0">
<div class="table-responsive" style="min-height:12em">
	<table class="table table-hover border mb-0">
		<thead class="fw-semibold text-nowrap">
			<tr>
				<th class="bg-body-secondary">{$l10n['user']}</th>
				<th class="bg-body-secondary">
					<i v-if="sort=='date'" class="fa-solid" :class="desc ? 'fa-arrow-up-9-1' : 'fa-arrow-down-1-9'"></i>
					<a :href="Sort('date')" class="text-decoration-none">{$l10n['date']}</a>
				</th>
				<th class="bg-body-secondary">{$l10n['result']}</th>
				<th class="bg-body-secondary">IP</th>
				<th class="bg-body-secondary">{$l10n['browser']}</th>
			</tr>
		</thead>
		<tbody>
			<tr :class="{'table-success':item.result=='OK'}" v-for="item in items">
				<td><a :href="Link2User(item)" target="_blank" :class="'group-'+item.group_id" v-text="item.user_name"></a></td>
				<td v-text="item.date"></td>
				<td v-text="l10n[item.result] ?? item.result"></td>
				<td><a target="_blank" :href="'https://www.infobyip.com/?ip='+item.ip" v-text="item.ip"></a></td>
				<td class="pe-2"><input type="text" readonly class="form-control-plaintext lh-sm font-monospace p-0" :value="item.ua" @click="SelectAll"></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="row mb-1 gap-1 gap-md-0">
	<div class="col-12 col-md order-1 order-md-2 mt-2 mt-md-0"><div class="mx-auto" style="width: fit-content">$paginator</div></div>
	<div class="col order-2 order-md-1 pt-1">$say_total</div>
	<ul class="col order-3 nav justify-content-end">
		<li class="nav-item" v-for="item in pps">
			<b v-if="item==pp" class="nav-link ps-3 pe-0 py-1 disabled" v-text="item"></b>
			<a v-else :href="PP(item)" class="nav-link ps-3 pe-0 py-1" v-text="item"></a>
		</li>
	</ul>
</div>
</template>

<div v-else class="alert alert-info"><i class="fa-solid fa-info"></i> {$l10n['no-records']}</div>
HTML;

return CMS::$T->app(\compact('data','script','template'))->content->index(\compact('title','head'));
