<?php
/*
	Элемент шаблона. Оформление верхних центральных блоков

	@var массив с ключами:
		title - название блока
		content - содержимое блока
*/
if(!defined('CMS'))die;?><div class="base">
	<div class="maincont"><div class="binner">
		<?php echo$content?>
		<div class="clr"></div>
	</div></div>
</div>