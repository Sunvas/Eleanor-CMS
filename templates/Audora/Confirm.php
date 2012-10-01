<?php
/*
	Элемент шаблона. Табличка с вопросом, ответ на который либо "да" либо "нет". При нажатии на кнопку "да" - происходит отправка формы с
	ключом ok

	@var вопрос
	@var URL возврата
*/
if(!defined('CMS'))die;
$t=is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title'];
$l=Eleanor::$Language['tpl'];
$b=isset($v_1) ? $v_1 : false;
?>
<div class="wbpad"><div class="warning">
	<img src="<?php echo$theme?>images/confirm.png" class="info" alt="<?php echo$t?>" title="<?php echo$t?>" />
	<div>
		<h4><?php echo$t?></h4>
		<hr />
		<form method="post"><?php echo$v_0.($b ? Eleanor::Control('back','hidden',$b) : $b)?>
		<br />
		<input class="button" type="submit" value="<?php echo$l['yes']?>" name="ok" />
		<input class="button" type="button" value="<?php echo$l['no']?>" onclick="history.go(-1);/>
		</form>
	</div>
	<div class="clr"></div>
</div></div>