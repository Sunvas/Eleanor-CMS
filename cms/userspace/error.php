<?php
namespace CMS;

use Eleanor\Classes\L10n;
use const Eleanor\SITEDIR;

/** Page with site error (404 - not found, 403 - restricted)
 * @var int $code Error code */

$l10n=new L10n('error',__DIR__.'/l10n/');
$title=$l10n[$code] ?? $code;

?><!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=SITEDIR?>">
	<meta charset="utf-8">
	<title><?=$title?></title>
	<link rel="shortcut icon" href="favicon.ico">
	<style>
body { height: 100vh; width: 100vw; text-align: center; font-family: Tahoma, Arial, Sans-serif; margin:0; }
main { padding-top: 50%; transform: translateY(-50%); user-select: none; }
h1 { color: darkslategray; }
footer { position:fixed; bottom:1em; right:1em; font-size:.8rem; }
a { text-decoration:none; }
	</style>
<?php if(CMS::$T->cron){?>
	<script nonce="<?=Nonce()?>">
		<?=require __DIR__.'/includes/cron.php' /* Cron is being run here */?>
	</script>
<?php } ?>
</head>
<body>
<main>
	<h1><?=$title?></h1>
	<a href=""><?=$l10n['main']?></a>
</main>
<footer>
	<?php /* Feel free to get rid off this shit! */ ?>
	Powered by <a href="https://eleanor-cms.com">Eleanor CMS</a>
</footer>
</body>
</html>