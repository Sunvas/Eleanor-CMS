<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Админка управления модулями
*/
class TPLModules
{	/*
		Меню модуля
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['modules'];
		$links=&$GLOBALS['Eleanor']->module['links'];
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'modules','act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'addmodule','act'=>$act=='add'),
				),
			),
		);	}
	/*
		Шаблон списка модулей
		$items массив модулей. Формат: id=>array(), ключи внутреннего массива:
			services - массив сервисов, в которых доступен модуль
			title - название модуля
			descr - описание модуля
			protected - флаг защищенности модуля (защищенные модули - это чаще всего системные модули)
			path - каталог модуля
			image - картинка-логотип модуля
			active - флаг активности модуля
	*/
	public static function ShowList($items)
	{		static::Menu('list');		$lang=Eleanor::$Language['modules'];
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',4);
		$Lstp=clone$Lst;#protected
		$Lst->begin(array($lang['module'],15,'colspan'=>2),$lang['services'],$ltpl['functs']);

		$images=Eleanor::$Template->default['theme'].'images/';
		$di='images/modules/default-small.png';

		$serpref=$GLOBALS['Eleanor']->Url->file.'?section=management&amp;module=services&amp;';
		foreach($items as &$v)
		{
			$img=$di;
			if($v['image'])
			{
				$v['image']='images/modules/'.str_replace('*','small',$v['image']);
				if(is_file(Eleanor::$root.$v['image']))
					$img=$v['image'];
			}

			$services='';
			if($v['services'])
			{
				$v['services']=array_intersect($v['services'],array_keys(Eleanor::$services));
				foreach($v['services'] as &$sv)
					$services.='<a href="'.$serpref.'edit='.$sv.'">'.$sv.'</a>, ';
				$services=trim($services,', ');
			}

			$O=$v['protected'] ? $Lstp : $Lst;
			$O->item(
				array('<img src="'.$img.'" alt="" title="'.$v['title'].'" />','style'=>'width:1px','href'=>$v['_aedit']),
				array($v['title'],'title'=>$v['descr'],'style'=>$v['protected'] ? 'font-weight:bold;' : '','href'=>$v['_aedit']),
				array($services ? $services : $ltpl['all'],'style'=>$services ? '' : 'font-style:italic;text-align:center'),
				$Lst('func',
					$v['_aswap'] ? array($v['_aswap'],$v['active'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['active'] ? $images.'active.png' : $images.'inactive.png') : false,
					$v['_adel'] ? array($v['_adel'],$ltpl['delete'],$images.'delete.png') : false,
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png')
				)
			);
		}
		$Lst->s.=$Lstp;
		return Eleanor::$Template->Cover((string)$Lst->end());
	}

	/*
		Страница добавления/редактирования модуля
		$id идентификатор редактируемого модуля, если $id==0 значит модуль добавляется
		$values массив значений полей:
			Общие ключи:
			sections - массив секций модуля. Формат: имя секции=>имена модуля. Имена модуля это массив, где ключами
				выступают названия языков либо пустая строка (универсальные имена для всех языков), а значениям - имена, перечисленные через запятую
			m_folder - каталог модуля
			multiservice - флаг мультисервисного модуля (мультисервисный модуль, это модуль, файлы для запуска которого находятся в каталогах с именами сервисов внутри главного каталога модуля)
			file - имя файла для запуска модуля
			files - массив имен файлов для запуска модуля, в случае, если модуль не мультисервисный. Формат: имя сервиса=>имя файла
			active - флаг активности модуля
			image - картинка модуля
			api - файл с классом API для взаимодействия с модулем сторонних скриптов
			protected - флаг защищенного (системного) модуля. Можно задать только при установке модуля. Защищенные модули нельзя удалить.

			Языковые ключи:
			title - название модуля
			descr - описание модуля

		$errors - массив ошибок
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($id,$values,$errors,$back,$links)
	{		static::Menu($id ? '' : 'add');
		$lang=Eleanor::$Language['modules'];
		$ltpl=Eleanor::$Language['tpl'];

		array_push($GLOBALS['jscripts'],'js/jquery.drag.js','addons/autocomplete/jquery.autocomplete.js','js/admin_modules.js','js/admin_modules-'.Language::$main.'.js');
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';

		$sections='<ul style="list-style-type:none;padding-left:0px" id="sections">';
		foreach($values['sections'] as $name=>$data)
		{
			if(is_array($data))
			{
				$uni=isset($data['']) ? $data[''] : false;
				unset($data['']);
				foreach(Eleanor::$langs as $l=>&$q)
				{
					$vs=isset($data[$l]) ? (array)$data[$l] : array();
					if($uni)
						$vs=array_merge($uni,$vs);
					$data[$l]=join(',',$vs);
				}
			}
			else
				$data=array(''=>$data);

			$section='<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" style="margin-right:30px" alt="" title="'.$lang['updown'].'" class="updown" /><span class="name" style="font-weight:bold;cursor:pointer">'.$name.'</span>:<a href="#" style="float:right;font-weight:bold;" class="delete">'.$ltpl['delete'].'</a><br />';
			if(Eleanor::$vars['multilang'])
			{
				$flags='';
				foreach(Eleanor::$langs as $k=>&$v)
				{
					$section.='<div id="'.$name.'-'.$k.'" class="langtabcont">'.Eleanor::Edit('sections['.$name.']['.$k.']',isset($data[$k]) ? $data[$k] : '').'</div>';
					$flags.='<a href="#" data-rel="'.$name.'-'.$k.'"'.($k==Language::$main ? ' class="selected"' : '').' title="'.Eleanor::$langs[$k]['name'].'"><img src="images/lang_flags/'.$k.'.png" alt="'.Eleanor::$langs[$k]['name'].'" /></a>';
				}
				$section.='<div id="langs-'.$name.'" class="langtabs">'.$flags.'</div><script type="text/javascript">/*<![CDATA[*/$("#langs-'.$name.' a").Tabs();//]]></script>';
			}
			else
				$section.=Eleanor::Edit('sections['.$name.']',Eleanor::FilterLangValues($data));
			$sections.='<li style="margin-top:5px">'.$section.'</li>';

		}
		$sections.='</ul>';

		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
				$ml['descr'][$k]=Eleanor::Edit('descr['.$k.']',Eleanor::FilterLangValues($values['descr'],$k),array('tabindex'=>3));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Edit('title',Eleanor::FilterLangValues($values['title']),array('tabindex'=>1)),
				'descr'=>Eleanor::Text('descr',Eleanor::FilterLangValues($values['descr']),array('tabindex'=>3)),
			);


		$files=$services='';
		foreach(Eleanor::$services as $k=>&$v)
		{
			if(!isset($values['files'][$k]))
				$values['files'][$k]=$v['file'];
			$services.=Eleanor::Option($k,$k,$act=in_array($k,$values['services']));
			$files.='<li'.($act ? '' : ' style="display:none"').'><span style="font-weight:bold">'.$k.'</span>:<br />'.Eleanor::Edit('files['.$k.']',isset($values['files'][$k]) ? $values['files'][$k] : '',$act ? array() : array('disabled'=>true)).'</li>';
		}

		$prevm=$values['image'] ? str_replace('*','small',$values['image']) : 'images/spacer.png';
		$extra=$id && $values['protected'] ? array('disabled'=>true) : array();
		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item(array($ltpl['name'],Eleanor::$Template->LangEdit($ml['title'],null),'imp'=>true))
			->item($lang['sections'].'<br /><a href="#" id="addsession">'.$lang['add'].'</a>',$sections)
			->item(array($ltpl['descr'],Eleanor::$Template->LangEdit($ml['descr'],null),'tip'=>$lang['descr_']))
			->item(array($lang['m_folder'],Eleanor::Edit('path',$values['path'],$extra),'imp'=>true))
			->item(array($lang['access_in_s'],Eleanor::Items('services[]',$services,10,$extra),'imp'=>true))
			->item(array($lang['multi'],Eleanor::Check('multiservice',$values['multiservice'],$extra),'tip'=>$lang['multi_']))
			->item(array($lang['filename'],Eleanor::Edit('file',$values['file'],$extra),'tr'=>array('class'=>'multitrue')))
			->item(array($lang['files'],'<ul style="list-style-type:none;padding-left:0px" id="files">'.$files.'</ul>','tip'=>$lang['files_'],'tr'=>array('class'=>'multifalse')))
			->item($ltpl['active'],Eleanor::Check('active',$values['active'],$extra))
			->item(array($lang['img'],Eleanor::Edit('image',$values['image'],array('id'=>'image')).' <img id="preview" src="'.$prevm.'" '.($values['image'] ? '' : ' style="display:none"').' />','tip'=>$lang['img_']))
			->item('API',Eleanor::Edit('api',$values['api']))
			->item(array($lang['prot'],Eleanor::Check('protected',$values['protected'],$id ? array('disabled'=>true) : array()),'imp'=>$lang['prot_']))
			->button($back.Eleanor::Button().($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->end()
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		return Eleanor::$Template->Cover((string)$Lst,$errors,'error').'<script type="text/javascript">//<![CDATA[
function AppyDragAndDrop()
{
	$("#sections").DragAndDrop({
		move:".updown",
		replace:"<li style=\"height:40px\">"
	});
}//]]></script>';
	}

	/*
		Страница удаления модуля
		$a - массив удаляемого модуля, ключи:
			title - название удаляемого модуля
		$back - URL возврата
	*/
	public static function Delete($t,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['modules']['deleting'],$a['title']),$back));
	}
}