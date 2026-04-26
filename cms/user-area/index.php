<?php
namespace CMS;

$title??=$var_0 ?? '';

/** Index template for all pages
 * @var string|array $title Head title
 * @var string $content Content of the page
 * @var ?array $head Extra injections into head section
 * @var ?array $scripts injection of scripts: values with integer keys will be injected as files, with string values - as scripts themselves
 * @var ?string $jsdelivr JsDelivr script injection via combine
 * @var ?string $canonical Canonical link to the genuine page
 * Default:
 * @var array $hreflang Links to alternative language versions of the page */

$head??=[];
$scripts??=[];
$script=$inline='';
$nonce=Nonce();

foreach($scripts as $k=>$s)
	if(is_int($k))
		$script.=<<<HTML
<script src="{$s}" nonce="{$nonce}" defer></script>
HTML;
	else
		$inline.=$s;

if(isset($canonical))
{
	$pref=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE;
	$head['canonical']=<<<HTML
<link rel="canonical" href="{$pref}{$canonical}">
HTML;
	$head['robots']=<<<'HTML'
<meta name="robots" content="noindex, follow">
HTML;
}

#Appending site title to the title of pages
if(is_array($title))
	$title[]=is_array(CMS::$config['site']['name']) ? L10n::Item(CMS::$config['site']['name']) : CMS::$config['site']['name'];

$l10n=new L10n('',__DIR__.'/l10n/');
$Menu=new Uri()->IAM();

Link('//cdn.jsdelivr.net');
?>
<!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=\Eleanor\SITEDIR?>">
	<meta charset="utf-8">
	<title><?=strip_tags(is_array($title) ? join(' :: ',$title) : $title)?></title>

	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="static/user-area/styles/main.css">
<?php
if(isset($hreflang))
{
	$base=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;
	echo \array_reduce(\array_keys($hreflang),fn($a,$code)=>$a."<link rel='alternate' href='{$base}{$hreflang[$code]}' hreflang='{$code}'>","\t<link rel='alternate' href='{$base}' hreflang='x-default'>");
}
?>

	<script src="//cdn.jsdelivr.net/combine/npm/jquery@4/dist/jquery.slim.min.js,npm/vue@3/dist/vue.global.prod.min.js<?=$jsdelivr ?? ''?>" nonce="<?=$nonce?>" defer></script>
	<script nonce="<?=$nonce?>">
		const L=new Promise(F=>document.readyState==="loading"?addEventListener('DOMContentLoaded',F):F()),J=async r=>r.ok ? r.json() : Promise.reject(r);
		L.then(()=>$(`nav a[href='${location.pathname+location.search}']`).addClass("active"));
		<?=$inline,require __DIR__.'/includes/cron.php' /* Cron is being run here */?>
	</script><?=$script,join("\n",$head)?>
</head>
<body class="page_bg">

<dialog id="loading">
	<span><?=$l10n['loading']?></span>
</dialog>

<div class="wrapper">
	<header>
		<div id="headerboxic"><div class="dleft"><div class="dright">
			<a class="logotype" href="">
				<img src="static/user-area/images/eleanorcms.png" alt="">
			</a>
			<span class="headbanner">
				<!-- Баннер 468x60-->
				<!-- <a href="link.html" title="Ваш баннер"><img src="static/user-area/images/spacer.png" alt="Ваш баннер"></a> -->
			</span>
		</div></div></div>

		<div id="menuhead"><div class="dleft"><div class="dright">
<?php if(isset($hreflang)){ ?>
			<div class="language">
				<?=\array_reduce(\array_keys($hreflang),fn($a,$code)=>$a."<a href='{$hreflang[$code]}' hreflang='{$code}' rel='alternate'><b>{$l10n[$code]}</b></a>",'')?>
			</div>
<?php } ?>
			<nav>
				<ul class="topmenu">
					<li><a href="<?=$Menu('blog')?>"><?=$l10n['demo-blog']?></a></li>
					<li><a href="<?=$Menu('demo-static')?>"><?=$l10n['demo-static']?></a></li>
					<li><a href="<?=$Menu('demo-direct')?>"><?=$l10n['demo-direct']?></a></li>
					<li><a href="<?=$Menu('demo-text')?>"><?=$l10n['demo-text']?></a></li>
					<li><a href="<?=$Menu('demo-json')?>"><?=$l10n['demo-json']?></a></li>
				</ul>
			</nav>
		</div></div></div>
	</header>

	<div class="container">
		<div class="mainbox">
			<div id="maincol">
				<div class="baseblock"><div class="dtop"><div class="dbottom">
					<main class="dcont">
						<?=$content?>
					</main>
				</div></div></div>
			</div>
		</div>

		<aside id="leftcol">
<?php

#Login widget in the separate file to make code clear
require __DIR__.'/includes/widget-user.php';

#Demo of fluent interface
echo CMS::$T->BlockLight(title:'Light widget 1',content:'Light content 1')
	->BlockDark(title:'Dark widget 1',content:'Dark content 1')
	->BlockLight(title:'Light widget 2',content:'Light content 2')
	->BlockDark(title:'Dark widget 2',content:'Dark content 2');
?>
		</aside>
		<div class="clr"></div>
	</div>

	<footer>
		<div id="footmenu"><div class="dleft"><div class="dright">
			<a title="<?=$l10n['to_top']?>" href="#" class="top-top" id="top"><img src="static/user-area/images/top-top.png" alt=""></a>
			<nav class="menu">
				<a href="<?=Uri::$base?>blog"><?=$l10n['demo-blog']?></a>
				<a href="<?=Uri::$base?>demo-static"><?=$l10n['demo-static']?></a>
				<a href="<?=Uri::$base?>demo-direct"><?=$l10n['demo-direct']?></a>
				<a href="<?=Uri::$base?>demo-test"><?=$l10n['demo-text']?></a>
				<a href="<?=Uri::$base?>demo-json"><?=$l10n['demo-json']?></a>
			</nav>
		</div></div></div>
		<script nonce="<?=$nonce?>">L.then(()=>$("a#top").on("click",e=>{e.preventDefault();$(document.body).get(0).scrollIntoView();}));</script>

		<div id="footer"><div class="dleft"><div class="dright">
			<div class="count">
				<span style="width: 88px;"><!-- кнопка, счетчик --></span>
				<span style="width: 60px;">  <a href="https://validator.w3.org/check?uri=referer" rel="nofollow"><img src="static/user-area/images/html5_valid.png" alt="Valid HTML 5" title="Valid HTML 5" width="60" height="31"></a></span>
			</div>
			<span class="copyright">Copyright &copy; <?=idate('Y')?></span>
			<div class="clr"></div>
		</div></div></div>

		<div id="syscopyright">
			<span class="centroarts"><a href="//centroarts.com" title="Шаблон разработан студией CENTROARTS.com">Designed by CENTROARTS.com</a></span>
			<?php /* Feel free to get rid off this shit! */ ?>
			<div>Powered by <a href="https://eleanor-cms.com" target="_blank">Eleanor CMS</a></div>
		</div>
	</footer>
</div>
</body>
</html>
<!-- Page generated in <?=sprintf('%.3f',(\hrtime(true)-STARTED)/1e+9)?> sec; Memory peak usage is <?=sprintf('%.3f',memory_get_peak_usage()/1e+6)?> Mb -->