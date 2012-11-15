<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон админки системного модуля страниц ошибок
*/
class TPLAdminErrors
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
			array($links['letters'],$lang['letters'],'act'=>$act=='letters'),
		);
	}
	/*
		Страница отображения всех страниц ошибок
		$items - массив страниц ошибок. Формат: ID=>array(), ключи внутреннего массива:
			mail - e-mail, куда будут отправляться сообщения об ошибках
			uri - URI ошибки
			title - название страницы ошибки
			_aedit - ссылка на редактирование страницы ошибки
			_adel - ссылка на удаление страницы ошибки
		$cnt - количество страниц ошибок всего
		$pp - количество страниц ошибок на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_title - ссылка на сортировку списка $items по названию (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_mail - ссылка на сортировку списка $items по e-mail для отпрвки ошибки (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внтури которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества ошибок отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'title'=>false,
			'email'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin(
				array($ltpl['title'],'sort'=>$qs['sort']=='title' ? $qs['so'] : false,'href'=>$links['sort_title']),
				array('E-mail','sort'=>$qs['sort']=='mail' ? $qs['so'] : false,'href'=>$links['sort_mail']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.$v['title'].'</a>',
					array($v['mail'] ? $v['mail'] : '&mdash;',$v['mail'] ? false : 'center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
		}
		else
			$Lst->empty($lang['not_found']);
		return Eleanor::$Template->Cover(
			'<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
	<tr>
		<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Edit('fi[title]',$qs['']['fi']['title']).'</td>
		<td><b>E-mail</b><br />'.Eleanor::Edit('fi[email]',$qs['']['fi']['email']).'</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:center">'.Eleanor::Button($ltpl['apply']).'</td>
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
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['to_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		Интерфейс предпросмотра логотипа выбранной страницы ошибки
	*/
	public static function ImagePreview()
	{
		$a=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['image'];
		return'<img src="images/spacer.png" alt="'.$a.'" title="'.$a.'" id="previw" /><script type="text/javascript">//<![CDATA[
$(function(){
	$("#image").change(function(){
		var v=$(this).val();
		if(v)
			$("#previw").attr("src","images/errors/"+v).closest("tr").show();
		else
			$("#previw").closest("tr").hide();
	}).change();
});//]]></script>';
	}

	/*
		Страница добавления/редактирования страницы ошибки
		$id - идентификатор редактируемой страницы ошибки, если $id==0 значит страница ошибки добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$back - URL возврата
		$uploader - интерфейс загрузчика файлов
		$hasdraft - признак наличия черновика
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
			nodraft - ссылка на правку/добавление категории без использования черновика или false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$uploader,$hasdraft,$links)
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
				$mchecks[$k]=!$id || !empty($values['title']['value'][$k]) || !empty($values['text']['value'][$k]) || !empty($values['uri']['value'][$k]);
		}
		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$mchecks,null,9));

		$Lst->s.='<tr><td colspan="2">'.$uploader.'</td></tr>';

		$Lst->button(
			$back.Eleanor::Button()
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
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

	/*
		Страница удаления ошибки
		$a - массив страницы ошибки, которая удаляется. Ключи:
			title- заголовок страницы ошибки
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['deleting'],$a['title']),$back));
	}

	/*
		Страница правки форматов писем
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
	*/
	public static function Letters($controls,$values,$error)
	{		static::Menu('letters');
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);
		$Lst->button(Eleanor::Button())->end()->endform();

		if($errors)
		{			$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover((string)$Lst,$errors);	}
}