<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон отвечает за оформление админки "своих" BB кодов
*/
class TplOwnBB
{
	public static
		$lang;

	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language['ownbb'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>$links['add']
				? array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				)
				: false,
			),
			array($links['recache'],static::$lang['update']),
		);
	}

	/*
		Шаблон страницы со списком "своих" BB кодов
		$items - массив, содержащий перечень "своих" BB кодов. Форма: ID=>array. Каждый элемент массива это массив с ключами:
			pos - от 1 до N содержит порядок обработки данного BB кода. Все элементы массива $items отсортированы по этому параметру по наростанию
			active - флаг активности (включенности) BB кода.
			handler - имя файла-обработчика BB кода
			tags - имена тегов, которые обрабатываются данным BB кодом [tagname]...[/tagname]
			special - флаг того, что данный BB код является специальным, тоесть обрабатывается только внутри других "своих" BB кодов
			sp_tags - имена тегов, которые обрабатываются внутри данного BB кода
			_aedit - ссылка на редактирование "своего" BB кода
			_adel - ссылка на удаление "своего" BB кода
			_aup - ссылка на поднятие "своего" BB кода вверх, если равна false - значит "свой" BB код уже и так находится в самом верху
			_adown - ссылка на опускание "своего" BB кода вниз, если равна false - значит "свой" BB код уже и так находится в самом низу
	*/
	public static function ShowList($items)
	{
		static::Menu('list');
		$lang=Eleanor::$Language['ownbb'];
		$ltpl=Eleanor::$Language['tpl'];
		$images=Eleanor::$Template->default['theme'].'images/';
		$Lst=Eleanor::LoadListTemplate('table-list',5)->begin(
			array($lang['tags'],'title'=>$lang['tags_']),
			array($lang['handler'],150),
			array($lang['special'],'title'=>$lang['special_'],100),
			array($lang['sp_tags'],'title'=>$lang['sp_tags_'],150),
			array($ltpl['functs'],110)
		);

		$cnt=count($items);
		foreach($items as $k=>&$v)
		{
			if($v['_aact'])
			{
				$extra=array('style'=>'color:red');
				$active='';
			}
			else
			{
				$extra=array();
				$active=$v['active'] ? array($v['_aact'],$ltpl['deactivate'],$images.'active.png') : array($v['_aact'],$ltpl['activate'],$images.'inactive.png');
			}

			$Lst->item(
				array($v['tags'],'href'=>$v['_aedit'])+$extra,
				$v['handler'],
				array(Eleanor::$Template->YesNo($v['special']),'center'),
				$v['sp_tags'],
				$Lst('func',
					$v['_aup'] ? array($v['_aup'],$ltpl['moveup'],$images.'up.png') : false,
					$v['_adown'] ? array($v['_adown'],$ltpl['movedown'],$images.'down.png') : false,
					$active,
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
					array($v['_adel'],$ltpl['delete'],$images.'delete.png')
				)
			);
		}

		if($cnt==0)
			$Lst->empty($lang['no_tags']);

		return Eleanor::$Template->Cover($Lst->end());
	}

	/*
		Шаблон страницы добавления / правки "своего" BB тега
		$id - ид "своего" BB кода, который правится. Если $id==0, значит "свой" BB код добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление "своего" BB тега или false
		$back - адрес страницы, с которой мы пришли править / добавлять контрол. На эту страницу будет совершен возврат после сохранения
	*/
	public static function AddEdit($id,$controls,$values,$errors,$links,$back)
	{
		static::Menu($id ? '' : 'add');
		$Lst=Eleanor::LoadListTemplate('table-form')->begin();
		$tabs=array();
		$head=false;
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
			{
				if($head)
				{
					$tabs[]=array($head,(string)$Lst->end());
					$Lst->begin();
				}
				$head=$v;
			}

		$tabs[]=array($head,(string)$Lst->end());

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));
		$Lst->form()->tabs($tabs)->submitline($back.Eleanor::Button().($id ? ' '.Eleanor::Button(Eleanor::$Language['tpl']['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover((string)$Lst,$errors,'error');
	}

	/*
		Шаблон страницы удаления контрола

		$a - массив удаляемого своего BB кода, ключи:
			tags - теги
		$back - адрес страницы, с которой мы пришли чтобы удалить контрол. На эту страницу будет совершен возврат после удаления
	*/
	public static function Delete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['ownbb']['deleting'],$a['tags']),$back));
	}
}
TplOwnBB::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/ownbb-*.php',false);