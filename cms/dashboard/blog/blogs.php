<?php
namespace CMS;

/** Demo blogs start page. See example of passed variables in star.php */

#Demo of the top menu
$topmenu=<<<'HTML'
<ul class="header-nav d-none d-lg-flex">
	<li class="nav-item"><a class="nav-link" href="#">Demo link #1</a></li>
	<li class="nav-item"><a class="nav-link" href="#">Demo link #2</a></li>
</ul>
<ul class="header-nav ms-auto">
	<li class="nav-item dropdown">
		<button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button" data-coreui-toggle="dropdown">
			<i class="fa-solid fa-comments icon icon-xl theme-icon-active"></i>
		</button>
		<ul class="dropdown-menu dropdown-menu-end">
			<li><a class="dropdown-item" href="#"><i class="icom me-2 fa-solid fa-comment"></i> Demo link #3</a></li>
			<li><a class="dropdown-item" href="#"><i class="icom me-2 fa-solid fa-paper-plane"></i> Demo link #4</a></li>
		</ul>
	<li class="nav-item dropdown">
		<button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button" data-coreui-toggle="dropdown">
			<i class="fa-solid fa-bell icon icon-xl theme-icon-active"></i>
		</button>
		<ul class="dropdown-menu dropdown-menu-end">
			<li><a class="dropdown-item" href="#"><i class="icom me-2 fa-solid fa-at"></i> Demo link #5</a></li>
			<li><a class="dropdown-item" href="#"><i class="icom me-2 fa-solid fa-bullhorn"></i> Demo link #6</a></li>
		</ul>
	</li>
	<li class="nav-item py-1">
		<div class="vr h-100 mx-2 text-body text-opacity-75"></div>
	</li>
</ul>
HTML;

$title=['Blog demo 1'];
$content=<<<'HTML'
<section class="container-xl">
	<div class="card">
		<div class="card-body">
			Hello! This is demo of the blog dashboard. This page is visible for admins and site team members.
		</div>
	</div>
</section>
HTML;


return(CMS::$T)('index',\compact('title','content','topmenu'));