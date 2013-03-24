<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym. See paramss/copyrights/info.txt for more information.
*/
if(!defined('CMS'))die;

return array(
	'parents'=>array(
		'title'=>$lang['parent'],
		'descr'=>'',
		'type'=>'select',
		'bypost'=>&$Eleanor->sc_post,
		'load'=>function($a)
		{
			$a['value']=rtrim($a['value'],',');
			if(false!==$p=strrpos($a['value'],','))
				$a['value']=substr($a['value'],$p+1);
			return$a;
		},
		'save'=>function($a)use($mc)
		{global$Eleanor;
			$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.$mc['t'].'` WHERE `id`='.(int)$a['value'].' LIMIT 1');
			if($a=$R->fetch_assoc())
				return$a['parents'] ? $a['parents'].$a['id'].',' : $a['id'].',';
			return'';
		},
		'options'=>array(
			'exclude'=>0,
			'callback'=>function($a)use($mc)
			{global$Eleanor;
				$sel=Eleanor::Option('&mdash;',0,in_array('',$a['value']),array(),2);
				if(!class_exists($mc['api'],false))
					include$Eleanor->module['path'].'api.php';
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
	'title'=>array(
		'title'=>$lang['text'],
		'descr'=>$lang['text_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>2
			),
		),
	),
	'url'=>array(
		'title'=>$lang['url'],
		'descr'=>'',
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>3,
			),
		),
	),
	'eval_url'=>array(
		'title'=>$lang['eval_url'],
		'descr'=>$lang['eval_url_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>4,
			),
		),
	),
	'params'=>array(
		'title'=>$lang['params'],
		'descr'=>$lang['params_'],
		'save'=>function($a)
		{
			if(is_array($a['value']))
			{
				foreach($a['value'] as &$v)
					$v=$v ? ' '.trim($v) : '';
				return$a['value'];
			}
			return$a['value'] ? ' '.trim($a['value']) : '';
		},
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'multilang'=>Eleanor::$vars['multilang'],
		'options'=>array(
			'htmlsafe'=>false,
			'extra'=>array(
				'tabindex'=>5,
			),
		),
	),
	'pos'=>array(
		'title'=>$lang['pos'],
		'descr'=>$lang['pos_'],
		'type'=>'input',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'htmlsafe'=>true,
			'extra'=>array(
				'tabindex'=>6,
			),
		),
	),
	'in_map'=>array(
		'title'=>$lang['in_map'],
		'descr'=>'',
		'default'=>true,
		'type'=>'check',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'extra'=>array(
				'tabindex'=>7,
			),
		),
	),
	'status'=>array(
		'title'=>$lang['activate'],
		'descr'=>'',
		'default'=>true,
		'type'=>'check',
		'bypost'=>&$Eleanor->sc_post,
		'options'=>array(
			'extra'=>array(
				'tabindex'=>8,
			),
		),
	),
);