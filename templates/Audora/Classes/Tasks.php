<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны менеджера задач
*/
class TPLTasks
{	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['tasks'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>$links['add']
					? array(
						array($links['add'],$lang['add'],'act'=>$act=='add'),
					)
					: false,
			),
		);
	}
	/*
		Страница отображения всех задач
		$items - массив задач Формат: ID=>array(), ключи внутреннего массива:
			task - файл-обработчик задачи
			title - название задачи
			free - флаг завершенности процесса создания задачи. Когда значение данного ключа равно 1, значит в этот момент происходит выполнение задачи
			lastrun - время последнего запуска задачи
			nextrun - время следующего запуска задачи
			run_year - год запуска задачи
			run_month - месяц запуска задачи
			run_day - день запуска задачи
			run_hour - час запуска задачи
			run_minute - минута запуска задачи
			run_second - секунда запуска задачи
			status - статус активности задачи
			_aedit - ссылка на редактирование задачи или false
			_adel - ссылка на удаление задачи

		$cnt - количество задач всего
		$page - номер текущей страницы, на которой мы сейчас находимся
		$pp - количество задач на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$links - перечень необходимых ссылок, массив с ключами:
			sort_nextrun - ссылка на сортировку списка $items по дате следующего запуска (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_task - ссылка на сортировку списка $items по задаче (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_free - ссылка на сортировку списка $items по флагу завершенности (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_status - ссылка на сортировку списка $items по статусу (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			pp - фукнция-генератор ссылок на изменение количества задач отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$cnt,$page,$pp,$qs,$links)
	{		static::Menu('list');
		$lang=Eleanor::$Language['tasks'];
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',9)
			->begin(
				array($ltpl['name'],'sort'=>$qs['sort']=='task' ? $qs['so'] : false,'href'=>$links['sort_task']),
				array($lang['nextrun'],'sort'=>$qs['sort']=='nextrun' ? $qs['so'] : false,'href'=>$links['sort_nextrun']),
				$lang['runyear'],
				$lang['runmonth'],
				$lang['runday'],
				$lang['runhour'],
				$lang['runminute'],
				$lang['runsecond'],
				array($ltpl['functs'],'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id'])
			);

		$images=Eleanor::$Template->default['theme'].'images/';
		if($items)
			foreach($items as $k=>&$v)
				$Lst->item(
					array($v['title'],'href'=>$v['_aedit']),
					array($v['free'] ? Eleanor::$Language->Date($v['nextrun']) : $lang['now'],'center'),
					array($v['run_year'],'center'),
					array($v['run_month'],'center'),
					array($v['run_day'],'center'),
					array($v['run_hour'],'center'),
					array($v['run_minute'],'center'),
					array($v['run_second'],'center'),
					$Lst('func',
						array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png'),
						$v['_aedit'] ? array($v['_aedit'],$ltpl['edit'],$images.'edit.png') : false,
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					)
				);
		else
			$Lst->empty($lang['notasks']);

		return Eleanor::$Template->Cover(
			$Lst->end()
			.'<div class="submitline" style="text-align:left">'.sprintf($lang['tpp'],$Lst->perpage($pp,$links['pp'])).'</div>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		Страница добавления/редактирования задачи
		$id - идентификатор редактируемой задачи, если $id==0 значит задача добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$links)
	{		static::Menu($id ? '' : 'add');
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
		{			$lang=Eleanor::$Language['tasks'];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		Страница удаления задачи
		$a - массив удаляемого задания, ключи:
			title - название задания
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['tasks']['deleting'],$a['title']),$back));
	}
}