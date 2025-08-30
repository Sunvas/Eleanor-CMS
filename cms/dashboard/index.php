<?php
namespace CMS;

use Eleanor\Classes\L10n;

/** Index template for all pages
 * @var array|string $title Head title
 * @var string $content Content of the page
 * @var ?array $head Extra injections into head section
 * @var ?array $scripts Injection of scripts: values with integer keys will be injected as files, with string values - as scripts themselves
 * @var ?string $jsdelivr JsDelivr script injection via combine
 * @var ?string $topmenu Contents of top menu
 * @var ?array $breadcrumb link=>text
 * @var ?bool $formfuse Flag for inserting script to prevent the form.fuse from accidentally leaving the page */

$l10n=new L10n('index',__DIR__.'/l10n/');
$head??=[];
$nonce=Nonce();
$scripts??=[];

if(is_array($title))
	array_push($title,$l10n['dashboard'],is_array(CMS::$config['site']['name']) ? L10n::Item(CMS::$config['site']['name']) : CMS::$config['site']['name']);

if($formfuse ?? false)
	$scripts['confirm']=<<<'SCRIPT'
const P=e=>void($("form.fuse [name]").toArray().some(e=>$(e).val()!==e.defaultValue) && e.preventDefault());
$(window).on("beforeunload",P);
$"#form.fuse").on("submit",()=>$(window).off("beforeunload",P));
SCRIPT;

if(is_array($breadcrumb ?? 0))
{
	array_walk($breadcrumb,function(&$item,$link){
		$item=is_string($link)
			? <<<HTML
<li class="breadcrumb-item"><a href="{$link}">{$item}</a></li>
HTML
			: <<<HTML
<li class="breadcrumb-item active">{$item}</li>
HTML;
	});
	$breadcrumb=join('',$breadcrumb);

	if($breadcrumb)
		$breadcrumb=<<<HTML
<div class="container-fluid px-4">
	<nav>
		<ol class="breadcrumb my-0">{$breadcrumb}</ol>
	</nav>
</div>
HTML;
}
else
	$breadcrumb='';

foreach($scripts as $k=>&$script)
	$script=\is_int($k)
		? <<<HTML
<script src="{$script}" nonce="{$nonce}" defer></script>
HTML
		: <<<HTML
<script nonce="{$nonce}">L.then(async()=>{{$script}})</script>
HTML;
?>
<!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=\Eleanor\SITEDIR?>">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="robots" content="none">
	<title><?=strip_tags(is_array($title) ? join(' :: ',$title) : $title)?></title>

	<link rel="shortcut icon" href="favicon.ico">
	<link rel="stylesheet" href="static/dashboard/style.min.css">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/css/all.min.css">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.css">

	<script src="//cdn.jsdelivr.net/combine/npm/jquery@3/dist/jquery.slim.min.js,npm/vue@3/dist/vue.global.prod.min.js,npm/swiped-events@1,npm/@coreui/coreui@5/dist/js/coreui.bundle.min.js<?=$jsdelivr ?? ''?>" nonce="<?=$nonce?>" crossorigin="anonymous" defer></script>
	<script nonce="<?=Nonce()?>">const L=new Promise(F=>document.readyState==="loading"?addEventListener('DOMContentLoaded',F):F());</script>
	<script src="static/dashboard/index.js" nonce="<?=$nonce?>" data-cron="<?=CMS::$T->cron ? 1 : ''?>" defer></script>
	<?=join('',$scripts),join('',$head)?>
	<style>
		ul.sidebar-nav { scrollbar-width:thin; overflow-y:clip; scrollbar-color:green transparent; }
		nav.sidebar:hover ul.sidebar-nav { overflow-y:auto; }
	</style>
</head>
<body>
<nav class="sidebar sidebar-dark sidebar-fixed border-end hide" id="sidebar">
	<div class="sidebar-header border-bottom">
		<div class="sidebar-brand">
			<a href="" class="sidebar-brand-full link-underline-dark fw-medium text-light" target="_blank"><b class="nav-icon me-3 fs-5">🐎</b>Eleanor CMS</a>
			<a href="" class="sidebar-brand-narrow link-underline-dark fs-5" target="_blank">🐎</a>
		</div>
		<button class="btn-close d-lg-none sidebar-toggle" type="button" data-coreui-dismiss="offcanvas" data-coreui-theme="dark"></button>
	</div>
	<ul class="sidebar-nav" data-coreui="navigation"><?=require __DIR__.'/includes/sidebar.php'?></ul>
	<footer class="sidebar-footer border-top d-none d-md-flex">
		<button class="sidebar-toggler" type="button"></button>
	</footer>
</nav>
<div class="wrapper d-flex flex-column min-vh-100">
	<header class="header header-sticky p-0 mb-3">
		<div class="container-fluid border-bottom px-4">
			<button class="header-toggler" type="button" style="margin-inline-start: -1rem">
				<i class="fa-solid fa-bars icon icon-xl"></i>
			</button>
			<?=$topmenu ?? ''?>
			<ul class="header-nav">
				<li class="nav-item dropdown">
					<button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button" data-coreui-toggle="dropdown">
						<i class="fa-solid fa-user-tie icon icon-xl theme-icon-active"></i>
					</button>
					<ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem;">
						<li>
							<a class="dropdown-item" href="<?=Classes\UriDashboard::$base?>?u=users&amp;id=<?=CMS::$A->current?>">
								<i class="icom me-2 fa-solid fa-user-gear"></i> <?=$l10n['my-profile']?>
							</a>
						</li>
						<li>
							<button class="dropdown-item" id="sign-out">
								<i class="icom me-2 fa-solid fa-people-pulling"></i> <?=$l10n['logout']?>
							</button>
						</li>
					</ul>
				</li>
				<li class="nav-item py-1">
					<div class="vr h-100 mx-2 text-body text-opacity-75"></div>
				</li>
				<li class="nav-item dropdown">
					<button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button" data-coreui-toggle="dropdown" title="Menu">
						<i class="fa-solid fa-ellipsis-vertical icon icon-xl theme-icon-active"></i>
					</button>
					<ul class="dropdown-menu dropdown-menu-end pb-1 pt-0" style="--cui-dropdown-min-width: 8rem;" id="theme-selector">
						<li class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-1"><?=$l10n['theme']?></li>
						<li>
							<button class="dropdown-item d-flex align-items-center" type="button" name="light">
								<i class="fa-regular fa-sun me-2"></i> <?=$l10n['light']?>
							</button>
						</li>
						<li>
							<button class="dropdown-item d-flex align-items-center" type="button" name="dark">
								<i class="fa-solid fa-moon me-2"></i> <?=$l10n['dark']?>
							</button>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<?=$breadcrumb?>
	</header>
	<main class="body flex-grow-1"><?=$content?></main>
	<footer class="footer px-4 text-muted">
		<div>&copy; <?=idate('Y')?></div>
		<?php /* Feel free to get rid off this shit! */ ?>
		<div class="ms-auto">Powered by <a href="https://eleanor-cms.com" target="_blank">Eleanor CMS</a></div>
	</footer>
</div>
</body>
</html>