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
{	public static
		$lang;
	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],Eleanor::$Language['ser']['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
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
			->begin($lang['name'],$lang['file'],static::$lang['design'],$lang['login'],array($ltpl['functs'],80));

		$images=Eleanor::$Template->default['theme'].'images/';
		foreach($items as &$v)
			$Lst->item(
				array($v['name'],'href'=>$v['_aedit']),
				array($v['file'],'style'=>$v['protected'] ? 'font-weight:bold' : ''),
				$v['theme'] ? array($v['theme'],'center','href'=>$GLOBALS['Eleanor']->Url->file.'?'.$GLOBALS['Eleanor']->Url->Construct(array('section'=>'management','module'=>'themes_editor','files'=>$v['theme']),false),'hrefextra'=>array('title'=>static::$lang['etpl'])) : array('&mdash;','center'),
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
		$error - ошибка, если ошибка пустая - значит ее нет
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($name,$controls,$values,$errors,$back,$links)
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
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst->button(
			$back.Eleanor::Button()
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst,$errors,'error');
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
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['name']),$back));
	}
}
TplServices::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/services-*.php',false);