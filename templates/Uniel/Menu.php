<?php
/*
	Элемент шаблона: вывод "шапки модуля" с названием и меню.

	@var array(
		title - название в шапке
		menu - массив элементов меню по названиям. Каждый элемент - массив с ключами:
			0 - ссылка пункта меню, либо false
			1 - текст элемента меню
			extra - массив дополнительных параметров тега a, меню
			submenu - рекурсивный массив внутреннего меню элемента
	)
*/
if(!defined('CMS'))die;
$mainmenu='';
if(isset($menu))
{
	if(!function_exists('TPLFMenu'))
	{
		function TPLFMenu(array$menu)
		{
			$c='';
			foreach($menu as &$v)
				if(is_array($v) and $v)
				{					if(!empty($v['act']) and !isset($v['extra']['class']))
						$v['extra']['class']='active';
					$a=isset($v['extra']) ? Eleanor::TagParams($v['extra']) : '';
					$c.='<li>'.($v[0]===false ? '<span'.$a.'>'.$v[1].'</span>' : '<a href="'.$v[0].'"'.$a.'>'.$v[1].'</a>')
						.(empty($v['submenu']) ? '' : '<ul>'.TPLFMenu($v['submenu']).'</ul>')
						.'</li>';
				}
			return$c;
		}
	}
	$menu=TPLFMenu($menu);
	if($menu)
	{
		$GLOBALS['jscripts'][]='js/menu_multilevel.js';
		$u=uniqid();
		$mainmenu='<ul id="menu-'.$u.'" class="modulemenu">'.$menu.'</ul><script type="text/javascript">/*<![CDATA[*/$(function(){$("#menu-'.$u.'").MultiLevelMenu();});//]]></script>';
	}
}
?>
<div class="base">
	<div class="heading2"><div class="binner">
		<h6><?php echo$title?></h6>
		<div class="clr"></div>
	</div></div>
	<nav><?php echo$mainmenu?></nav>
</div>