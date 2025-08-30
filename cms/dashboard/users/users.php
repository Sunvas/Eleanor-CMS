<?php
namespace CMS;

use Eleanor\Classes\L10n;

/** Userlist
 * @var \Generator $users
 * @var \Generator $groups
 * @var int $total Total users
 * @var int $pp Amount users per page
 * @var string $sort Sorting field
 * @var bool $desc Flag of descending sorting
 * @var bool $is_admin Is current user an administrator
 * Default:
 * @var array $links List of links */

$l10n=new \Eleanor\Classes\L10n('users',__DIR__.'/l10n/');

$items=[];
foreach($users as $item)
{
	$item['created']=L10n::Date($item['created']);

	if((int)$item['activity']>2000)
	{
		$item['activity_ts']=\strtotime($item['activity']);
		$item['activity']=L10n::Date($item['activity_ts']);
	}
	else
		$item['activity_ts']=$item['activity']=null;

	$items[]=$item;
}

$my_id=CMS::$A->current;
$data=['L10N'=>L10N,'L10NS'=>L10NS,'groups'=>\iterator_to_array($groups)]
	+\compact('items','is_admin','my_id','total','pp','sort','desc');

$title=[$l10n['title']];
$script='static/dashboard/users.js';
$head['style']=(CMS::$T)('coloring-of-groups');
$head['style2']=<<<'HTML'
<style>small .group + .group:before { content:", "; color:grey; }</style>
HTML;

$say_total=$l10n['say-total']($total);
$paginator=(CMS::$T)('app-paginator');
$mpl=MIN_PASSWORD_LENGTH;

$template=<<<HTML
<div class="d-flex justify-content-between mb-2">
	<h1 class="h3 mb-0 pt-1"><i class="nav-icon fa-solid fa-user-group d-none d-md-inline"></i> {$l10n['title']}</h1>
	<div class="gap-1 gap-md-2 d-flex">
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
				<p v-if="group" class="d-flex mb-1">
					<span>{$l10n['by-group']}</span>
					<mark :class="'group-'+group" class="py-0 ms-1">{{group2title[group] ?? group}}</mark>
					<a :href="Filter(['group'])" class="ms-auto small"><i class="fa-solid fa-xmark"></i></a>
				</p>
				<div class="mb-1">
					<label for="filter-login" class="form-label mb-1">{$l10n['login']}</label><a :href="Filter(['name'])" v-if="name" v-once class="ms-2 small"><i class="fa-solid fa-xmark"></i></a>
					<input type="text" name="name" class="form-control" id="filter-login" :value="name" autocomplete="off">
				</div>
				<button type="submit" class="btn btn-primary bg-gradient">{$l10n['do-filter']}</button>
			</form>
		</div>
		<button v-if="is_admin" type="button" class="btn btn-primary bg-gradient d-block d-md-none" @click="Create" title="{$l10n['create']}"><i class="fa-solid fa-user-plus fa-lg"></i></button>
		<button v-if="is_admin" type="button" class="btn btn-primary bg-gradient d-none d-md-block" @click="Create"><i class="fa-solid fa-user-plus fa-lg me-2"></i>{$l10n['create']}</button>
	</div>
</div>

<template v-if="items.length>0">
<div class="table-responsive" style="min-height:12em">
	<table class="table border mb-0">
		<thead class="fw-semibold text-nowrap">
			<tr>
				<th class="bg-body-secondary text-center" style="width:2.5rem"><i class="fa-solid fa-ellipsis-vertical" v-if="is_admin"></i></th>
				<th class="bg-body-secondary">
					<i v-if="sort=='name'" class="fa-solid" :class="desc ? 'fa-arrow-up-z-a' : 'fa-arrow-up-a-z'"></i>
					<a :href="Sort('name')" class="text-decoration-none">{$l10n['login']}</a>
					<a :href="Filter(['sort','order'])" v-if="sort!='id'" class="ms-3 small"><i class="fa-solid fa-xmark"></i></a>
				</th>
				<th class="bg-body-secondary">{$l10n['comment']}</th>
				<th class="bg-body-secondary">{$l10n['display_name']}</th>
				<th class="bg-body-secondary">Telegram</th>
				<th class="bg-body-secondary text-end">{$l10n['activity']}</th>
			</tr>
		</thead>
		<tbody>
			<tr class="align-middle" v-for="(item,index) in items" :id="'item-'+item.id" :class="{'table-danger':item.empty_password}" :title="item.empty_password ? l10n.empty_password : ''">
				<td class="text-center" :class="{dropend:is_admin}">
					<div class="avatar avatar-md" :role="is_admin ? 'button' : ''" data-coreui-toggle="dropdown">
						<img v-if="item.avatar" class="avatar-img" :src="`static/avatars/\${item.id}-\${item.avatar}.webp`" :alt="item.name">
						<i v-else class="fa-solid fa-user fa-2xl avatar-img text-muted"></i>
						<span class="avatar-status" :class="item.status_class" :title="l10n[item.status_hint]"></span>
					</div>
					<ul class="dropdown-menu py-0" v-if="is_admin">
						<li><button class="dropdown-item" type="button" title="{$l10n['copy-id']}" @click="Copy(item,index)"><i class="fa-solid fa-copy me-2"></i> {{item.id.toString().padStart(4,"0")}}</button></li>
						<li><button class="dropdown-item" type="button" @click="Modify(item,index)"><i class="fa-solid fa-user-pen me-2"></i> {$l10n['modify']}</button></li>
						<li><button class="dropdown-item text-primary" type="button" @click="SignIn(item,index)"><i class="fa-solid fa-right-to-bracket me-2"></i> {$l10n['sign-in']}</button></li>
						<li v-if="item.id!=my_id"><button class="dropdown-item text-danger" type="button" @click="Delete(item,index)"><i class="fa-solid fa-trash-can me-2"></i> {$l10n['delete']}</button></li>
					</ul>
				</td>
				<td>
					<div class="text-nowrap" v-text="item.name"></div>
					<small class="text-body-secondary text-nowrap"><span v-for="gid in item.groups" class="group" :class="'group-'+gid" v-text="group2title[gid] ?? gid"></span> | <span title="{$l10n['reg']}" v-text="item.created"></span></small>
				</td>
				<td v-text="item.comment || '&mdash;'"></td>
				<td v-text="item.display_name || '&mdash;'"></td>
				<td>
					<a :href="'//t.me/'+item.telegram_username" v-if="item.telegram_username" target="_blank" class="text-decoration-none">@{{item.telegram_username}}</a>
					<span v-else>&mdash;</span>
				</td>
				<td v-text="item.activity || '&mdash;'" class="text-end"></td>
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

<div v-else class="alert alert-info"><i class="fa-solid fa-info"></i> {$l10n['nobody-found']}</div>

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

<dialog class="modal fade bg-transparent" ref="user" tabindex="-1" data-coreui-backdrop="static">
	<div class="modal-dialog">
		<form class="modal-content" @submit.prevent="Submit">
			<div class="modal-header">
				<h5 class="modal-title" v-text="user_title"></h5>
				<button type="button" class="btn-close" tabindex="-1" data-coreui-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-1">
					<div class="col" :class="{'was-validated':user_name_error!==null}">
						<label for="user-name" class="form-label mb-0">{$l10n['login']}</label>
						<input type="text" class="form-control" id="user-name" v-model.lazy="user.name" autocomplete="username" @change="Changed('name')" ref="user_name" required maxlength="25">
						<div class="invalid-feedback" v-text="user_name_error"></div>
					</div>
					<div class="col">
						<label for="user-password" class="form-label mb-0">{$l10n['password']}</label>
						<input type="password" class="form-control" id="user-password" v-model="user.password" minlength="{$mpl}" :required="!user_id" autocomplete="new-password" @change="Changed('password')">
					</div>
				</div>
				<div class="mb-1">
					<label for="user-l10n" class="form-label mb-0">{$l10n['groups']}</label>
					<select class="form-select" multiple size="3" v-model="user.groups" @change="Changed('groups')" required>
						<option v-for="group in groups" :value="group.id" v-text="group.title" :class="'group-'+group.id"></option>
					</select>
				</div>
				<div class="row mb-1">
					<div class="col">
						<label for="user-dn" class="form-label mb-0">{$l10n['display_name']}</label>
						<input type="text" class="form-control" id="user-dn" v-model="user.display_name" autocomplete="off" @change="Changed('display_name')">
					</div>
					<div class="col" v-if="l10ns.length>0">
						<label for="user-l10n" class="form-label mb-0">{$l10n['l10n']}</label>
						<select id="user-l10n" class="form-select" v-model="user.l10n" @change="Changed('l10n')">
							<option v-for="[code,title] in l10ns" :value="code" v-text="title"></option>
						</select>
					</div>
				</div>
				<div class="mb-1">
					<label for="user-comment" class="form-label mb-0">{$l10n['comment']}</label>
					<textarea class="form-control" rows="2" v-model="user.comment" @change="Changed('comment')" style="resize:none"></textarea>
					<small class="form-text">{$l10n['only4dashboard']}</small>
				</div>
				<div class="mb-1">
					<label for="user-info" class="form-label mb-0">{$l10n['info']}</label>
					<textarea class="form-control" rows="2" v-model="user.info" @change="Changed('info')" style="resize:none"></textarea>
					<small class="form-text">{$l10n['4anybody']}</small>
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
