<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблона управления категориями
*/
class TplCategoriesManager
{	protected static function Menu($lang,$act='')
	{		$links=&$GLOBALS['Eleanor']->module['links_categories'];		$GLOBALS['Eleanor']->module['navigation']=array(
			'categories'=>array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				),
			),
		);
	}

	/*
		Страница отображения всех категорий
		$items - массив категорий. Формат: ID=>array(), ключи внутреннего массива:
			title - название категории
			image - путь к картинке-логотипу категории, если пустое - значит логотипа нет
			pos - целое число, характеризующее позицию категории
			_aedit - ссылка на редактирование категории
			_adel - ссылка на удаление категории
			_aparent - ссылка на просмотр подкатегорий текущей категории
			_aup - ссылка на поднятие категории вверх, если равна false - значит категория уже и так находится в самом верху
			_adown - ссылка на опускание категории вниз, если равна false - значит категория уже и так находится в самом низу
			_aaddp - ссылка на добавление подкатегорий к данной категории
		$subitems - массив подкатегорий для страниц из массива $items. Формат: ID=>array(id=>array(), ...), где ID - идентификатор статической страницы, id - идентификатор категории. Ключи массива подкатегорий:
			title - заголовок категории
			_aedit - ссылка на редактирование подкатегории
		$navi - массив, хлебные крошки навигации. Формат ID=>array(), ключи:
			title - заголовок крошки
			_a - ссылка на подпункты данной крошки. Может быть равно false
		$cnt - количество статических страниц всего
		$pp - количество статических страниц на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_title - ссылка на сортировку списка $items по названию (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_pos - ссылка на сортировку списка $items по позиции (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
		$lang - массив языковых параметров, ключи и значения смотрите в файле langs/categories_manage-*.php
	*/
	public static function CMList($items,$subitems,$navi,$cnt,$pp,$qs,$page,$links,$lang)
	{		static::Menu($lang,'list');		$ltpl=Eleanor::$Language['tpl'];
		$nav=array();
		foreach($navi as &$v)
			$nav[]=$v['_a'] ? '<a href="'.$v['_a'].'">'.$v['title'].'</a>' : $v['title'];

		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin(
				array($ltpl['title'],'href'=>$links['sort_title'],'colspan'=>2),
				array($lang['pos'],80,'href'=>$links['sort_pos']),
				array($ltpl['functs'],80,'href'=>$links['sort_id'])
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';

			$posasc=!$qs['sort'] || $qs['sort']=='pos' && $qs['so']=='asc';
			foreach($items as $k=>&$v)
			{
				$subs='';
				if(isset($subitems[$k]))
					foreach($subitems[$k] as $kk=>&$vv)
						$subs.='<a href="'.$vv['_aedit'].'">'.$vv['title'].'</a>, ';

				$Lst->item(
					$v['image'] ? array('<a href="'.$v['_aedit'].'"><img src="'.$v['image'].'" /></a>','style'=>'width:1px') : false,
					array('<a id="cat'.$k.'" href="'.$v['_aedit'].'">'.$v['title'].'</a><br /><span class="small"><a href="'.$v['_aparent'].'" style="font-weight:bold">'.$lang['subitems'].'</a> '.rtrim($subs,', ').' <a href="'.$v['_aaddp'].'" title="'.$lang['addsubitem'].'"><img src="'.$images.'plus.gif'.'" /></a></span>','colspan'=>$v['image'] ? false : 2),
					$posasc
						? $Lst('func',
							$v['_aup'] ? array($v['_aup'],$lang['up'],$images.'up.png') : false,
							$v['_adown'] ? array($v['_adown'],$lang['down'],$images.'down.png') : false
						)
						: array('&empty;','center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					)
				);
			}
		}
		else
			$Lst->empty($lang['no']);
		return Eleanor::$Template->Cover(
			($nav ? '<table class="filtertable"><tr><td style="font-weight:bold">'.join(' &raquo; ',$nav).'</td></tr></table>' : '')
			.'<form action="'.$links['form_items'].'" method="post">'
			.$Lst->end().'<div class="submitline" style="text-align:left">'.sprintf($lang['to_pages'],$Lst->perpage($pp,$links['pp'])).'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		Страница добавления/редактирования категории
		$id - идентификатор редактируемой категории, если $id==0 значит категория добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
			nodraft - ссылка на правку/добавление категории без использования черновика или false
			draft - ссылка на сохранение черновика (для фоновых запросов)
		$lang - массив языковых параметров, ключи и значения смотрите в файле langs/categories_manage-*.php
	*/
	public static function CMAddEdit($id,$controls,$values,$errors,$back,$links,$lang)
	{		static::Menu($lang,$id ? 'edit' : 'add');		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if($values[$k])
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

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Control('_draft','hidden',$id)
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($links['nodraft'] ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)->end()->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];

		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		Страница удаления статической страницы
		$a - массив удаляемой категории, ключи:
			title - название категории
		$back - URL возврата
		$error - ошибка, если ошибка пустая - значит ее нет
		$lang - массив языковых параметров, ключи и значения смотрите в файле langs/categories_manage-*.php
	*/
	public static function CMDelete($a,$back,$error,$lang)
	{		static::Menu($lang);		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf($lang['deleting'],$a['title']),$back),$error);	}
}