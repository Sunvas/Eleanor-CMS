<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны для админки модуля аккаунт пользователя*/
class TPLAdminAccount
{	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$options=isset($GLOBALS['Eleanor']->module['navigation']['options']) ? $GLOBALS['Eleanor']->module['navigation']['options'] : false;
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['inactives'],$lang['inactives'],'act'=>$act=='list'),
			array($links['letters'],$lang['letters'],'act'=>$act=='letters'),
			$options ? $options : array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);
	}
	/*
		Шаблон отображения списка пользователей, ожидающих модерации
		$items - массив пользователей страниц. Формат: ID=>array(), ключи внутреннего массива:
			full_name -
			name - имя пользователя (небезопасный HTML!)
			email - e-mail пользователя
			ip - IP адрес пользователя
			_aact - ссылка на активацию пользователя
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя
			_adelr - ссылка на удаление пользователя с указание причины
		$cnt - количество пользователей, ожидающих модерации страниц всего
		$pp - количество пользователей, ожидающих модерации на страницу
		$page - номер текущей страницы, на которой мы сейчас находимся
		$qs - массив параметров адресной строки для каждого запроса
		$links - перечень необходимых ссылок, массив с ключами:
			sort_name - ссылка на сортировку списка $items по имени пользователя (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_email - ссылка на сортировку списка $items по email (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_ip - ссылка на сортировку списка $items по ip адресу (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
	*/
	public static function InactiveUsers($items,$sletters,$cnt,$pp,$page,$qs,$links)
	{		static::Menu('list');
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];
		$GLOBALS['jscripts'][]='js/checkboxes.js';

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'name'=>false,
			'namet'=>false,
			'id'=>false,
			'regto'=>false,
			'regfrom'=>false,
			'ip'=>false,
			'email'=>false,
		);

		$finamet='';
		$namet=array(
			'b'=>$lang['begins'],
			'q'=>$lang['match'],
			'e'=>$lang['endings'],
			'm'=>$lang['contains'],
		);
		foreach($namet as $k=>&$v)
			$finamet.=Eleanor::Option($v,$k,$qs['']['fi']['namet']==$k);

		$Lst=Eleanor::LoadListTemplate('table-list',5)
			->begin(
				array($lang['user_name'],'href'=>$links['sort_name']),
				array('E-mail','href'=>$links['sort_email']),
				array('IP','href'=>$links['sort_ip']),
				array($ltpl['functs'],'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.$v['name'].'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>').(in_array($k,$sletters) ? '<br /><b style="color:green">'.$lang['lettersent'].'</b>' : ''),
					array($v['email'],'center'),
					array($v['ip'],'center','href'=>'http://eleanor-cms.ru/whois/'.$v['ip'],'hrefaddon'=>array('target'=>'_blank')),
					$Lst('func',
						array($v['_aact'],$ltpl['activate'],$images.'active.png'),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						$v['_adel'] ? array($v['_adel'],$ltpl['delete'],$images.'delete.png') : false,
						$v['_adelr'] ? array($v['_adelr'],$lang['delr'],$images.'delete.png') : false
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
					<td><b>'.$lang['user_name'].'</b><br />'.Eleanor::Select('fi[namet]',$finamet,array('style'=>'width:30%')).Eleanor::Edit('fi[name]',$qs['']['fi']['name'],array('style'=>'width:68%')).'</td>
					<td><b>E-mail</b><br />'.Eleanor::Edit('fi[email]',$qs['']['fi']['email']).'</td>
				</tr>
				<tr>
					<td><b>IDs</b><br />'.Eleanor::Edit('fi[id]',$qs['']['fi']['id']).'</td>
					<td><b>IP</b><br />'.Eleanor::Edit('fi[ip]',$qs['']['fi']['ip']).'</td>
				</tr>
				<tr>
					<td><b>'.$lang['register'].'</b> '.$lang['from-to'].'<br />'.Dates::Calendar('fi[regfrom]',$qs['']['fi']['regfrom'],true,array('style'=>'width:35%')).' - '.Dates::Calendar('fi[regto]',$qs['']['fi']['regto'],true,array('style'=>'width:35%')).'</td>
					<td style="text-align:center;vertical-align:middle">'.Eleanor::Button($ltpl['apply']).'</td>
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
		</form>
		<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && ($(\'select\',this).val()==\'dr\' || confirm(\''.$ltpl['are_you_sure'].'\')))">'
		.$Lst->end()
		.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['to_pages'],$Lst->perpage($pp,$qs)).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['activate'],'a').Eleanor::Option($lang['sendlet'],'s').Eleanor::Option($ltpl['delete'],'d').Eleanor::Option($lang['delr'],'dr')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,$qs));
	}

	/*
		Шаблон страницы удаления пользователей с указанием причины
		$users - массив пользователей id=>имя пользователя
		$back - URI возврата
	*/
	public static function ToDelete($users,$back)
	{
		static::Menu();
		if($back)
			$back=Eleanor::Control('back','hidden',$back);
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];
		$tusers='';
		foreach($users as $k=>&$v)
			$tusers.='<label>'.Eleanor::Check('ids[]',true,array('value'=>$k)).' '.htmlspecialchars($v['name'],ELENT,CHARSET).($v['name']==$v['full_name'] ? '' : ' ('.htmlspecialchars($v['name'],ELENT,CHARSET).')').'</label><br />';
		return Eleanor::$Template->Cover('<div class="wbpad"><div class="warning"><img src="'.Eleanor::$Template->default['theme'].'/images/confirm.png" class="info" alt="" /><div><form method="post" action="">'
		.$back.'<h4>'.$lang['del_users'].'</h4><hr />'.$tusers.'<br /><h4>'.$lang['dreason'].'</h4><hr />'.$GLOBALS['Eleanor']->Editor->Area('reason').'<div style="text-align:center"><input class="button" type="submit" value="'.$ltpl['yes'].'" /><input class="button" type="button" value="'.$ltpl['no'].'" onclick="history.go(-1); return false;" /></div></form></div><div class="clr"></div></div></div>');
	}

	/*
		Шаблон страницы с редактированием форматов писем
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
	*/
	public static function Letters($controls,$values)
	{		static::Menu('letters');
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);
		return Eleanor::$Template->Cover($Lst->button(Eleanor::Button())->end()->endform());
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{
		static::Menu('options');
		return$c;
	}
}