<?php
/*
	Элемент шаблона. Оформление нижних центральных блоков

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