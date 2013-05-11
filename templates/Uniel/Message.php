<?php
/*
	Элемент шаблона. Отображает информацию в рамке иконкой "внимание", "информация" и "ошибка"

	@var array|string отображаемый текст
	@var string error|warning|info определяет тип иконки. По умолчанию тип warning
	@var int|false ttl определяет время, после которое объявление самоликвидируется
*/
if(!defined('CMS'))die;
$type=isset($v_1) ? $v_1 : 'warning';
$isa=is_array($v_0);
$ttl=isset($v_2) ? (int)$v_2 : false;
?>
<div class="base"<?php
if($ttl)
{
	$id=uniqid();
	echo' id="',$id,'"';
}?>>
	<div class="binner">
		<div class="warning">
			<img src="<?php echo$theme?>images/<?php echo$type?>.png" alt="" title="<?php
if($isa and count($v_0)>1 and $type=='error')
	$type.='s';
$title=isset(Eleanor::$Language['tpl'][$type]) ? Eleanor::$Language['tpl'][$type] : 'warning';
echo$title;?>" />
			<h4><?php echo$title?></h4>
			<?php echo$isa ? join('<br />',$v_0) : $v_0;?>
			<div class="clr"></div>
		</div>
	</div>
</div>
<?php if($ttl):?>
<script type="text/javascript">//<![CDATA[
$(function(){
	setTimeout(function(){
		$("#<?php echo$id?>").fadeOut("slow");
	},<?php echo$ttl*1000?>);
})//]]></script>
<?php endif?>