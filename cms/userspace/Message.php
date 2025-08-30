<?php
namespace CMS;

/** Сообщение: информацию в рамке с иконкой "внимание", "информация" или "ошибка"
 * @var array|string $text Текст
 * @var ?string $type error|warning|info Тип иконки. По умолчанию тип warning
 * @var ?string $title Заголовок */

$text??=$var_0 ?? '';
$type??=$var_1 ?? 'warning';
$title??=$var_2 ?? new \Eleanor\Classes\L10n('message',__DIR__.'/l10n/')[$type] ?? $type;
?>
<div class="binner">
	<div class="warning">
		<img src="static/userspace/images/<?=$type?>.png" alt="<?=$title?>" title="<?=$title?>">
		<h4><?=$title?></h4>
		<?=is_array($text) ? join('<br>',$text) : $text?>
		<div class="clr"></div>
	</div>
</div>