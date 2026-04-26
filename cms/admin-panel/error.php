<?php
namespace CMS;

use const Eleanor\SITEDIR;

/** Page with site error (404 - not found, 403 - restricted)
 * @var int $code Error code
 * Default:
 * @var array $links List of links */

Link('//cdn.jsdelivr.net');

$l10n=new L10n('error',__DIR__.'/l10n/');
$info=$l10n[$code] ?? $l10n['happened'];

?><!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=SITEDIR?>">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="none">
	<title><?=$code,' &ndash; ',$info?></title>
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="static/admin-panel/style.min.css">
</head>
<body>
<main class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-6">
				<div class="clearfix">
					<h1 class="float-start display-2 me-4"><?=$code?></h1>
					<h4 class="pt-3"><?=$info?></h4>
					<p><a href="<?=$links['home']?>" class="text-secondary"><?=$l10n['home']?></a></p>
				</div>
			</div>
		</div>
	</div>
</main>
</body>
</html>