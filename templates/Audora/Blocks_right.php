<?php
/*
	Элемент шаблона. Оформление правых блоков

	@var массив с ключами:
		title - название блока
		content - содержимое блока
*/
if(!defined('CMS'))die;?><div class="block">
		<div class="dtop">&nbsp;</div>
			<div class="dmid">
					<h3 class="dtitle"><?php echo$title?></h3>
					<div class="dcont">
					<?php echo$content?>
					</div>
			</div>
			<div class="dbtm">&nbsp;</div>
</div>