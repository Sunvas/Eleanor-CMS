<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Ёлемент шаблона: левое меню в админке.
	—одержит в себе небольшое количество программного кода, поскольку используемый код имеет отношение исключительно к данному шаблону
*/
if(!defined('CMS'))die;
$uid=Eleanor::$Login->GetUserValue('id');
$c=Eleanor::$Cache->Get(Eleanor::$service.'_qmenu'.$uid.Language::$main);
if($c===false)
{global$Eleanor;
	$big=Eleanor::$Cache->Get(Eleanor::$service.'_qbmenu'.$uid,true);
	$menus=$modules=array();
	$R=Eleanor::$Db->Query('SELECT `mid`,`lid` FROM `'.P.'qmenu` WHERE `type`=\''.Eleanor::$service.'\' AND `uid`='.$uid.' ORDER BY `pos` ASC');
	while($arr=$R->fetch_assoc())
	{
		$menus[]=$arr;
		$modules[]=$arr['mid'];
	}

	if($modules)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`path`,`image`,`api` FROM `'.P.'modules` WHERE `id`'.Eleanor::$Db->In($modules));
		$modules=array();
		while($arr=$R->fetch_assoc())
		{
			$f=Eleanor::FormatPath($arr['api'],$arr['path']);
			if(!is_file($f))
				continue;
			$class='Api'.basename(dirname($f));
			if(!class_exists($class,false))
				include$f;
			if(method_exists($class,'QuickMenu'))
			{
				$Plug=new$class;
				$menu=$Plug->QuickMenu(Eleanor::$service,$arr);
				if($menu)
					$modules[$arr['id']]=array(
						'menu'=>$menu,
						'image'=>$arr['image'] ? str_replace('*',$big ? 'big' : 'small',$arr['image']) : 'default.png',
					);
			}
		}
	}
	$c='';
	foreach($menus as &$v)
		if(isset($modules[$v['mid']]['menu'][$v['lid']]))
			$c.='<li><a href="'.$modules[$v['mid']]['menu'][$v['lid']]['href'].'"><img src="images/modules/'.$modules[$v['mid']]['image'].'" alt="" /><span>'.$modules[$v['mid']]['menu'][$v['lid']]['title'].'</span></a></li>';
	$c='<div class="block"'.($c ? '' : ' style="display:none"').'><div class="dtop">&nbsp;</div><div class="dmid">'
		.($c ? '<ul class="reset navs n'.($big ? 'big' : 'small').'">'.$c.'</ul>' : '')
		.'</div>';
	Eleanor::$Cache->Put(Eleanor::$service.'_qmenu'.$uid.Language::$main,$c);
}
echo$c,'<div class="dbtm">&nbsp;</div></div><div class="block"><a class="editmenu" title="'
	.Eleanor::$Language['tpl']['edit_menu']
	.'" href="#" onclick="var h=500,w=600;window.open(\''
	.$GLOBALS['Eleanor']->Url->file.'?'.$Eleanor->Url->Construct(array('section'=>'management','module'=>'qmenu'),false)
	.'\',\'qmenu\',\'height=\'+h+\',width=\'+w+\',toolbar=no,menubar=no,location=no,scrollbars=no,focus=yes,top=\'+Math.round((screen.height-h)/2)+\',left=\'+Math.round((screen.width-w)/2)); return false;">'.Eleanor::$Language['tpl']['edit_menu'].'</a></div>';