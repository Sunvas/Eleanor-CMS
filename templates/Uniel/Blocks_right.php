<?php
/*
	Элемент шаблона. Оформление правых блоков

	@var массив с ключами:
		title - название блока
		content - содержимое блока
*/
if(!defined('CMS'))die;?><div class="blocktype2"><div class="dbottom">
	<div class="dtop">
		<h3><?php echo$title?></h3>
	</div>
	<div class="dcont">
		<?php echo$content?>
	</div>
</div></div>