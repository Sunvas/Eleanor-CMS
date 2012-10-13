<?php
/*

	Меню строится вида:
	$Eleanor->module['navigation']=array
	(
		array('href','title'),
		array('href','title'),
		array('href','title'),
		#Если встречается строковый ключ, то его массив воспринимается как набор пунктов меню, а не пункт меню!:
		'string'=>array(
						array('href','title',[image]),
						array('href','title',[image]),
						array('href','title',[image]),
		),
		#Если встречается submenu - оно будет обработано как подменю.
		array('href','title','submenu'=>
										array('href','title'),
										array('href','title'),
		),
	}
	Если 'act'=>true пункт будет отмечен как активный.
*/
if(!defined('CMS'))die;
$c='';
if(isset($Eleanor->module['navigation']))
	foreach($Eleanor->module['navigation'] as &$v)
	{
		if(!$v)
			continue;
		$submenu='';
		if(!empty($v['submenu']))
		{
			$submenu='';
			foreach($v['submenu'] as &$subhref)
				if($subhref)
					$submenu.='<li><a href="'.$subhref[0].'"'.($subhref['act'] ? ' class="active"' : '').(isset($subhref['addon']) ? ' '.$subhref['addon'] : '').'><span>'.$subhref[1].'</span></a></li>';
			if($submenu)
				$submenu='<ul class="submenu">'.$submenu.'</ul>';
		}
		$image=isset($v[2]) ? '<img src="'.Eleanor::$Template->default['theme'].'images/'.$v[2].'.png" alt="" />' : '';
		$c.='<li><a href="'.$v[0].'"'.(empty($v['act']) ? '' : ' class="active"').(isset($v['addon']) ? Eleanor::TagParams($v['addon']) : '').' title="'.$v[1].'"><span>'.$image.$v[1].'</span></a>'.$submenu.'</li>';
	}
$ref=getenv('HTTP_REFERER');
if($ref)
	$c.='<li><a href="'.htmlspecialchars($ref,ELENT,CHARSET,false).'"><img src="'.Eleanor::$Template->default['theme'].'images/back.png" alt="" />'.Eleanor::$Language['tpl']['goback'].'</a></li>';
echo$c ? '<div class="block bvnav">
	<div class="dtop">&nbsp;</div>
	<div class="dmid">
			<h3 class="dtitle">'.Eleanor::$Language['tpl']['navigation'].'</h3>
			<div class="dcont"><ul class="reset navs">'.$c.'</ul></div>
	</div>
	<div class="dbtm">&nbsp;</div>
</div>' : '';