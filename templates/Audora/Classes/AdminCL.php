<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для админки модуля контекстных ссылок
*/
class TPLAdminCL
{	/*
		Меню модуля
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				),
			),
		);
	}
	/*
		Страница отображения всех контекстных ссылок
		$items - массив контекстных ссылок. Формат: ID=>array(), ключи внутреннего массива:
			date_from - дата начала обработки
			date_till - дата завершения обработки
			status - статус активности контекстной ссылки
			from - исходная строка преобразования контекстной ссылки (из)
			to - результат преобразования контекстной ссылки (в)
			_aswap - ссылка на включение / выключение активности контекстной ссылки
			_aedit - ссылка на редактирование контекстной ссылки
			_adel - ссылка на удаление контекстной ссылки
		$cnt - число пунктов меню всего
		$pp - количество контекстных ссылок на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_from - ссылка на сортировку списка $items по словам для замены (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_to - ссылка на сортировку списка $items по тексту ссылки (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_date_from - ссылка на сортировку списка $items по дате начала преобразований (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_date_till - ссылка на сортировку списка $items по дате завершения преобразований (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_status - ссылка на сортировку списка $items по статусу активности (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества контекстных ссылок отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'from'=>false,
		);

		$l=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-list',7)
			->begin(
				array('ID',15,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array($l['from'],'sort'=>$qs['sort']=='from' ? $qs['so'] : false,'href'=>$links['sort_from']),
				array($l['to'],'sort'=>$qs['sort']=='to' ? $qs['so'] : false,'href'=>$links['sort_to']),
				array($l['date_from'],'sort'=>$qs['sort']=='date_from' ? $qs['so'] : false,'href'=>$links['sort_date_from']),
				array($l['date_till'],'sort'=>$qs['sort']=='date_till' ? $qs['so'] : false,'href'=>$links['sort_date_till']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					array($k,'right'),
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.$v['from'].'</a>',
					$v['to'],
					array((int)$v['date_from']>0 ? $v['date_from'] : '&infin;','center'),
					array((int)$v['date_till']>0 ? $v['date_till'] : '&infin;','center'),
					$Lst('func',
						array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png'),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png','extra'=>array('onclick'=>'return confirm(\''.$ltpl['are_you_sure'].'\')'))
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
		}
		else
			$Lst->empty($l['not_found']);
		return Eleanor::$Template->Cover(
			'<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td><a href="#">'.$ltpl['filters'].'</a></td></tr>
	<tr>
		<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Edit('fi[from]',$qs['']['fi']['from']).' '.Eleanor::Button($ltpl['apply']).'</td>
	</tr>
</table>
<script type="text/javascript">//<![CDATA[
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
});//]]></script>
		</form>'
		.'<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($l['to_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);	}

	/*
		Страница добавления/редактирования контекстной ссылки
		$id - идентификатор редактируемой контексной ссылки, если $id==0 значит контекстная ссылка добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$back - URL возврата
		$hasdraft - признак того, что у контекстной ссылки черновик
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
			nodraft - ссылка на правку/добавление категории без использования черновика или false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$hasdraft,$links)
	{		static::Menu($id ? '' : 'add');		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);

		if(Eleanor::$vars['multilang'])
		{
			$mchecks=array();
			foreach(Eleanor::$langs as $k=>&$_)
				$mchecks[$k]=!$id || !empty($values['title']['value'][$k]) || !empty($values['text']['value'][$k]) || !empty($values['url']['value'][$k]);
		}
		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$mchecks,null,9));

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Control('_draft','hidden',$id)
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)->end()->endform();

		if($errors)
		{			$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}
}