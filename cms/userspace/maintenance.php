<?php
namespace CMS;

use Eleanor\Classes\L10n;
use const Eleanor\SITEDIR;

/** Page for situation when site is closet for maintenance */

$l10n=new L10n('maintenance',__DIR__.'/l10n/');

?><!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=SITEDIR?>">
	<meta charset="utf-8">
	<title><?=$l10n['title']?></title>
	<link rel="shortcut icon" href="favicon.ico">
	<style>
body { height: 100vh; width: 100vw; text-align: center; font-family: Tahoma, Arial, Sans-serif; margin:0; }
main { padding-top: 50%; transform: translateY(-50%); user-select: none; }
h1 { color: darkslategray; }
footer { position:fixed; bottom:1em; right:1em; font-size:.8rem; }
a { text-decoration:none; }
	</style>
	<script nonce="<?=Nonce()?>">
		<?=require __DIR__.'/includes/cron.php' /* Cron is being run here */?>
	</script>
</head>
<body>
<main>
	<h1><?=$l10n['title']?></h1>
</main>
<footer>
	<?php /* Feel free to get rid off this shit! */ ?>
	Powered by <a href="https://eleanor-cms.com" target="_blank">Eleanor CMS</a>
</footer>
</body>
</html>