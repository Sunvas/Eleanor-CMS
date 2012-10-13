<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Админка управления смайлами
*/
class TPLSmiles
{	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['smiles'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
					array($links['addgroup'],$lang['gadd'],'act'=>$act=='addg'),
				),
			),
		);
	}
	/*
		Страница отображения всех смайлов
		$items - массив смайлов страниц. Формат: ID=>array(), ключи внутреннего массива:
			path - путь к файлу-картинке смайла
			emotion - эмоция смайла
			show - флаг отображения смайла в редакторе
			_ok - признак того, что файл смайла существует
			_aedit - ссылка на редактирование смайла
			_adel - ссылка на удаление смайла
			_aswap - ссылка на инвертирование активности смайла, либо false
			_aup - ссылка на перемещение файла вверх, либо false если смайл уже находится в самом верху. Только в случае сортировки по положению.
			_adown - ссылка на перемещение смайла вниз, либо false если смайл находится в самом низу. Только в случае сортировки по положению.
		$cnt - количество смайлов всего
		$page - номер текущей страницы, на которой мы сейчас находимся
		$pp - количество смайлов на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$links - перечень необходимых ссылок, массив с ключами:
			sort_show - ссылка на сортировку списка $items по порядку обработки - положению (возрастанию/убыванию в зависимости от текущей сортировки). Это основная сортировка!
			sort_emotion - ссылка на сортировку списка $items по эмоции (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_path - ссылка на сортировку списка $items по пути к смайлу (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_show - ссылка на сортировку списка $items по отображению в редакторе (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
	*/	public static function SmilesList($items,$cnt,$page,$pp,$qs,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language['smiles'];
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-list',6)
			->form(array('action'=>$links['form_items'],'id'=>'checks-form','onsubmit'=>'return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))'))
			->begin(
				array($lang['smile'],'colspan'=>2,'href'=>$links['sort_emotion']),
				array($lang['path'],'href'=>$links['sort_path']),
				array($lang['show'],'href'=>$links['sort_show']),
				array($lang['pos'],'href'=>$links['sort_pos']),
				array($ltpl['functs'],110,'title'=>'ID','href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			$sasc=$qs['sort']=='pos' && $qs['so']=='asc';
			foreach($items as $k=>&$v)
			{
				if($v['_ok'])
				{
					$vddon='';
					$status=$v['_aswap'] ? array($v['_aswap'],$ltpl['deactivate'],$images.'active.png') : array($v['_aswap'],$ltpl['activate'],$images.'inactive.png');
				}
				else
				{
					$vddon='background:darkred';
					$status='';
				}

				$Lst->item(
					array($vddon ? '&nbsp;' : '<img src="'.$v['path'].'" alt="" title="'.$v['emotion'].'" />','style'=>'text-align:center;width:10px;'.$vddon,'href'=>$vddon ? '' : $v['_aedit']),
					array('<div class="fieldedit" id="it'.$k.'" style="width:90px" data-id="'.$k.'"><a href="'.$v['_aedit'].'">'.$v['emotion'].'</a></div>'),
					$v['path'],
					array(Eleanor::$Template->YesNo($v['show']),'center'),
					$sasc
						? $Lst('func',
							$v['_aup'] ? array($v['_aup'],$ltpl['moveup'],$images.'up.png') : false,
							$v['_adown'] ? array($v['_adown'],$ltpl['movedown'],$images.'down.png') : false
							)
						: array('&empty;','center'),
					$Lst('func',
						$status,
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty($lang['no_smiles']);

		return Eleanor::$Template->Cover(
		'<script type="text/javascript">/*<![CDATA[*/$(function(){
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
	$(document).on("mousedown","div.fieldedit a",function(e){
		if(e.which==1)
			var a=$(this).data("to",setTimeout(function(){
				$("<input type=\"text\">").val(a.text()).insertAfter(a).width("100%").focus();
				a.parent().data("a",a.detach());
			},100));
	})
	.on("blur","div.fieldedit input",function(){
		$(this).parent().data("a").insertAfter(this);
		$(this).remove();
	})
	.on("keypress","div.fieldedit input",function(e){
		if(e.which==13)
		{
			var th=$(this),
				t=th.val();
			if(th.parent().data("a").text()!=t)
			{
				CORE.Ajax(
					{
						direct:"admin",
						file:"smiles",
						event:"setemotion",
						language:CORE.language,
						emotion:t,
						id:th.parent().data("id")
					},
					function(r)
					{
						th.parent().data("a").text(t).insertAfter(th);
						th.remove();
					}
				);
			}
			else
				th.blur();
			return false;
		}
	})
	.on("mouseup","div.fieldedit a",function(){
		var to=$(this).data("to");
		if(to)
			clearTimeout(to);
	})
})//]]></script>'.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'
			.sprintf($lang['smpp'],$Lst->perpage($pp,$qs))
			.'</div>'.$ltpl['with_selected']
			.Eleanor::Select('op',Eleanor::Option($ltpl['activate'],'a').Eleanor::Option($ltpl['deactivate'],'d').Eleanor::Option($ltpl['delete'],'k'))
			.Eleanor::Button('Ok').'</div></form>'.Eleanor::$Template->Pages($cnt,$pp,$page,$qs));
	}

	/*
		Страница добавления/редактирования смайла
		$id - идентификатор редактируемого смайла, если $id==0 значит страница ошибки добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$links)
	{
		static::Menu($id ? '' : 'add');
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$Lst->button(
			$back.Eleanor::Button()
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		if($errors)
		{			$lang=Eleanor::$Language['smiles'];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		Страница группового добавления смайлов.
		$values - массив с ключами:
			folder - каталог, в котором производится поиск смайлов
			added - флаг того, что выбранные смайлы успешно добавлены
			error - ошибка при добавлении
			smiles - массив смайлов-кандидатов на добавление. Ключи внутреннего массива:
				f - путь к файлу-картинке смайла
				e - эмоция смайла
				s - флаг отображения смайла в редакторе
				ch - флаг отмеченности смайла-кандидата для добавления (при повторном загрузке списка при возникновении ошибки)
	*/
	public static function AddGroupSmiles($values)
	{		static::Menu('addg');
		$lang=Eleanor::$Language['smiles'];		array_push($GLOBALS['jscripts'],'addons/autocomplete/jquery.autocomplete.js','js/checkboxes.js');
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';

		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin()
			->item($lang['selcat'],Eleanor::Edit('folder',$values['folder']).' '.Eleanor::Button('Ok'))
			->end()->endform();

		$c=$Lst.'<script type="text/javascript">//<![CDATA[
$(function(){
	$("input[name=folder]").autocomplete({
		serviceUrl:CORE.ajax_file,
		minChars:2,
		delimiter: null,
		params:{
			direct:"admin",
			file:"autocomplete",
			filter:"onlydir"
		}
	});
})//]]></script>'
		.($values['added'] ? Eleanor::$Template->Message($lang['smadded'],'info') : '');

		if($values['smiles'])
		{			$Lst=Eleanor::LoadListTemplate('table-list',5)
				->form(array('id'=>'checks-form'))
				->begin(
					array($lang['smile'],'colspan'=>2),
					$lang['emotion'],
					$lang['show'],
					array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
				);
			foreach($values['smiles'] as $k=>&$v)
				$Lst->item(
					$bn,
					array('<img src="'.$v['f'].'" alt="" />','style'=>'text-align:center;width:10px;'),
					Eleanor::Edit('smiles['.$k.'][e]',$v['e']),
					array(Eleanor::Check('smiles['.$k.'][s]',$v['s']),'center'),
					array(Eleanor::Check('smiles['.$k.'][f]',$v['ch'],array('value'=>$v['v'])),'center')
				);
			$c.=$Lst->end().'<div class="submitline">'.Eleanor::Control('folder','hidden',$values['folder']).Eleanor::Button($lang['addsels']).'</div></form>
			<script type="text/javascript">/*<![CDATA[*/$(function(){One2AllCheckboxes("#checks-form","#mass-check","[name$=\"[f]\"]",true)});//]]></script>';		}
		return Eleanor::$Template->Cover($c,$values['error'],'error');	}

	/*
		Страница удаления смайла
		$smile
			path - путь к картинки смайла
			emotion - текстовая эмоция, которая выражает смайл
		$back - URL возврата
	*/
	public static function Delete($smile,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['smiles']['deleting'],'<img class="smile" src="'.$smile['path'].'" alt="'.$smile['emotion'].'" title="'.$smile['emotion'].'" />'),$back));
	}
}