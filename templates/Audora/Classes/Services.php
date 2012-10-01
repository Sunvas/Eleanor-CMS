<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны сервисов
*/
class TPLServices
{	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['ser'];
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
		Страница отображения всех сервисов
		$items - массив sitemap-ов. Формат: >array(array(),array()...), ключи внутренних массивов:
			file - имя файла сервиса
			login - имя логина сервиса
			name - название сервиса
			protected - флаг защищенности сервиса
			theme - тема оформления, используемая в сервисе по умолчанию
			_aedit - ссылка на редактирование сервиса
			_adel - ссылка на удаление сервиса
	*/
	public static function Services($items)
	{		static::Menu('list');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language['ser'];
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',5)
			->begin($lang['name'],$lang['file'],$lang['design'],$lang['login'],array($ltpl['functs'],80));

		$images=Eleanor::$Template->default['theme'].'images/';
		foreach($items as &$v)
			$Lst->item(
				array($v['name'],'href'=>$v['_aedit']),
				array($v['file'],'style'=>$v['protected'] ? 'font-weight:bold' : ''),
				$v['theme'] ? array($v['theme'],'center','href'=>$GLOBALS['Eleanor']->Url->file.'?'.$GLOBALS['Eleanor']->Url->Construct(array('section'=>'management','module'=>'themes_editor','files'=>$v['theme']),false),'hrefaddon'=>array('title'=>$lang['etpl'])) : array('&mdash;','center'),
				array($v['login'],'center'),
				$Lst('func',
					$v['protected'] ? false : array($v['_adel'],$ltpl['delete'],$images.'delete.png'),
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png')
				)
			);
		$Lst->end();
		return Eleanor::$Template->Cover((string)$Lst);
	}

	/*
		Шаблон создания/редактирования сервиса
		$name - идентификатор редактируемого сервиса, если $name==false значит сервис добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$error - ошибка, если ошибка пустая - значит ее нету
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($name,$controls,$values,$error,$back,$links)
	{
		static::Menu($name ? '' : 'add');
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
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		return Eleanor::$Template->Cover($Lst,$error,'error');
	}


	/*
		Страница удаления сервиса
		$a - массив удаляемого сервиса, ключи:
			name - название сервиса
			file - файл сервиса
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['ser']['deleting'],$a['name']),$back));
	}
}