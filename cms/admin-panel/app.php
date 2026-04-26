<?php
namespace CMS;

/** App template
 * @var string $script path to script with vuejs app
 * @var string $template HTML template of vuejs app. Should not contain scripts.
 * @var string|array|null $data that will be passed as base64 to data-data param to script */

$nonce=Nonce();
$data=\is_array($data ?? 0) ? \json_encode($data,JSON) : ($data ?? '');
?>
<section class="container-xl" id="app">
	<div class="d-flex align-items-center justify-content-center mt-5">
		<div class="fa-solid fa-spinner fa-spin-pulse fa-10x align-middle text-secondary"></div>
	</div>
</section>
<script id="app-tpl" type="text/x-template"><?=$template?></script>
<script id="app-data" type="application/json"><?=$data?></script>
<script src="<?=$script?>" nonce="<?=$nonce?>" defer data-container="#app" data-template="#app-tpl" data-data="#app-data"></script>