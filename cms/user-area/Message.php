<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Message: information in the frame with an icon for "attention", "information" or "error"
 * @var array|string $text
 * @var ?string $type Icon type: error|warning|info ; warning - default
 * @var ?string $title */

$text??=$var_0 ?? '';
$type??=$var_1 ?? 'warning';
$title??=$var_2 ?? new L10n('message',__DIR__.'/l10n/')[$type] ?? $type;
?>
<div class="binner">
	<div class="warning">
		<img src="static/user-area/images/<?=$type?>.png" alt="<?=$title?>" title="<?=$title?>">
		<h4><?=$title?></h4>
		<?=is_array($text) ? join('<br>',$text) : $text?>
		<div class="clr"></div>
	</div>
</div>