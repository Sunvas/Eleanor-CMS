<?php
namespace CMS;

/** Шапка раздела
 * @var string $title Название в шапке
 * @var array $menu Ссылки для меню [ссылка, название] */

$title??=$var_0 ?? '';
$menu??=$var_1 ?? [];
?>
<div class="heading2"><div class="binner">
	<h6><?=$title?></h6>
	<div class="clr"></div>
</div></div>
<?=$menu ? '<nav><ul class="modulemenu">'.array_reduce($menu,fn($a,$item)=>$a.(is_array($item) ? "<li><a href='{$item[1]}'>{$item[0]}</a></li>" : "<li><span class='active'>{$item}</span></li>"),'').'</ul></nav>' : ''?>