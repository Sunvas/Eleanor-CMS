<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Section header
 * @var string $title Header caption
 * @var array $menu Links for the menu [link, caption] */

$title??=$var_0 ?? '';
$menu??=$var_1 ?? [];

$menu=\array_reduce(
		$menu,
		fn($a,$item)=>$a.(\is_array($item) ? "<li><a href='$item[1]'>$item[0]</a></li>" : "<li><span class='active'>$item</span></li>"),
		''
);
?>
<div class="heading2"><div class="binner">
	<h6><?=$title?></h6>
	<div class="clr"></div>
</div></div>
<?=$menu ? "<nav><ul class='modulemenu'>$menu</ul></nav>" : ''?>