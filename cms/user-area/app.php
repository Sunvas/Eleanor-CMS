<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

/** App template
 * @var string $script path to script with vuejs app
 * @var string $template HTML template of vuejs app. Should not contain scripts.
 * @var string|array|null $data that will be passed as base64 to data-data param to script
 * @var string $nonce default variable of nonce */

$data=\is_array($data ?? 0) ? \json_encode($data,JSON | \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT) : ($data ?? '');
?>
<div id="app"></div>
<script id="app-tpl" type="text/x-template"><?=$template?></script>
<script id="app-data" type="application/json"><?=$data?></script>
<script src="<?=$script?>" nonce="<?=$nonce?>" defer data-container="#app" data-template="#app-tpl" data-data="#app-data"></script>