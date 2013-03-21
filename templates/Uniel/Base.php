<?php
/*
	Элемент шаблона. Базовая таблица для размещения на сайте любого контента. Содержит заголовок, верхние элементы, основную часть и нижние элементы

	@var array(
		title - название материала, можно ссылкой
		text - основная часть материала (текст новости)
		top - массив верхних элементов. Чаще всего, это ссылки, например: array('<a href="...">ссылка 1</a>','<a href="...">ссылка 2</a>',...)
		bottom - массив нижних элементов. Чаще всего, это ссылки, как выше. Ключом, можно дополнительно указать тип элемента:
			rating - для рейтинга (будет отображен справа)
			readmore - для ссылки "читать далее" (будет отображена слева и выделена полужирным шрифтом)
	)
*/
if(!defined('CMS'))die;?>
<div class="base">
	<div class="heading"><div class="binner">
		<h1><?php echo$title?></h1>
<?php
if(isset($top))
{
	echo'<div class="moreinfo">';
	foreach($top as &$v)
		if($v!==false)
			echo'<span class="arg">'.$v.'</span>';
	echo'<div class="clr"></div>
	</div>';
}
?>
		<div class="clr"></div>
	</div></div>
	<div class="maincont"><div class="binner"><?php echo$text?>
		<div class="clr"></div>
	</div></div>
<?php
if(isset($bottom))
{
	echo'<div class="morelink"><div class="binner">';
	foreach($bottom as $k=>&$v)
		if($v!==false)
			switch($k)
			{
				case'rating':
					echo'<div class="ratebase">'.$v.'</div>';
				break;
				case'readmore':
					echo'<span class="argmore">'.$v.'</span>';
				break;
				default:
					echo'<span class="arg">'.$v.'</span>';
			}
	echo'<div class="clr"></div>
	</div></div>';
}
?>
</div>