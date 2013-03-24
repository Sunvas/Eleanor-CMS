<?php
$mc=include Eleanor::$root.'modules/menu/config.php';
$lang=Eleanor::$Language->Load(Eleanor::$root.'modules/menu/admin-*.php',false);
return array(
	'parent'=>array(
		'title'=>$lang['parent'],
		'descr'=>'',
		'type'=>'select',
		'save'=>function($a)use($mc)
		{
			return(int)$a['value'];
		},
		'options'=>array(
			'exclude'=>0,
			'callback'=>function($a)use($mc)
			{global$Eleanor;
				$sel=Eleanor::Option('&mdash;',0,in_array('',$a['value']),array(),2);
				if(!class_exists($mc['api'],false))
					include Eleanor::$root.'modules/menu/api.php';
				$Plug=new$mc['api']($mc);
				$items=$Plug->GetOrderedList(false,false);
				foreach($items as $k=>&$v)
				{
					if($k==$a['options']['exclude'] or strpos(','.$v['parents'],','.$a['options']['exclude'].',')!==false)
						continue;
					$sel.=Eleanor::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')).'›&nbsp;' : '').$v['title'],$k,in_array($k,$a['value']),array('style'=>$v['status']==0 ? 'color:gray;' : ''),2);
				}
				return$sel;
			},
			'extra'=>array(
				'tabindex'=>1
			),
		),
	),
);