<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
if(!defined('CMS'))die;

return array(
	'language'=>Eleanor::$vars['multilang'] ? array(
		'title'=>$lang['language'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'callback'=>function($a) use ($lang)
			{
				$sel=Eleanor::Option($lang['forallt'],'',in_array('',$a['value']));
				foreach(Eleanor::$langs as $k=>&$v)
					$sel.=Eleanor::Option($v['name'],$k,in_array($k,$a['value']));
				return$sel;
			},
			'extra'=>array(
				'tabindex'=>1
			),
		),
	) : null,
	'name'=>array(
		'title'=>$lang['tname'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>2
			),
		),
	),
);