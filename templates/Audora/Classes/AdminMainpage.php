<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Оформление для админки системного модуля "Главная страница".
*/
class TPLAdminMainpage
{
	public static
		$lang;
	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],static::$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					$links['add'] ? array($links['add'],static::$lang['add'],'act'=>$act=='add') : false,
				),
			),
		);
	}

	/*
		Перечень модулей, основная страница админки модуля

		$items - массив, содержащий в себе перечень модулей. Формат: ID=>array() ключи внутреннего массива:
			services - массив, каждый элемент которого - имя сервиса, в котором доступен модуль
			title - строка, название модуля
			descr - строка, описание модуля
			protected - флаг защищенного модуля
			path - каталог модуля
			image - путь к логотипу модуля. Если в имени картинки встречается *, значит ее нужно заменить на "small"
			active - флаг активного (включенного модуля)
			user_groups - идентификаторы групп пользователей, которым доступен данных модуль
			pos - числовой идентификатор позиции модуля. Весь массив $items отсортирован именно по этому полю от меньшего (1) к большему
			_aedit - ссылка на редактирование модуля
			_adel - ссылка на удаление модуля
			_aup - ссылка на поднятие модуля вверх, если равна false - значит модуль уже и так находится в самом верху
			_adown - ссылка на опускание модуля вниз, если равна false - значит модуль уже и так находится в самом низу
	*/
	public static function ShowList($items)
	{
		static::Menu('list');
		$ltpl=Eleanor::$Language['tpl'];
		$cnt=count($items);

		$Lst=Eleanor::LoadListTemplate('table-list',5)
			->begin(
				array($ltpl['title'],'colspan'=>2),
				static::$lang['services'],
				array(static::$lang['pos'],80),
				array($ltpl['functs'],80)
			);
		if($items)
		{
			$images=Eleanor::$Template->default['theme'].'images/';
			$di='images/modules/default-small.png';
			$modpref=$GLOBALS['Eleanor']->Url->file.'?section=management&amp;module=modules&amp;';
			$grspref=$GLOBALS['Eleanor']->Url->file.'?section=management&amp;module=groups&amp;';
			$serpref=$GLOBALS['Eleanor']->Url->file.'?section=management&amp;module=services&amp;';
			foreach($items as $k=>&$v)
			{
				$img=$di;
				if($v['image'])
				{
					$v['image']='images/modules/'.str_replace('*','small',$v['image']);
					if(is_file(Eleanor::$root.$v['image']))
						$img=$v['image'];
				}
				$grs=$services='';
				if($v['services'])
				{
					$v['services']=array_intersect($v['services'],array_keys(Eleanor::$services));
					foreach($v['services'] as &$sv)
						$services.='<a href="'.$serpref.'edit='.$sv.'">'.$sv.'</a>, ';
					$services=trim($services,', ');
				}

				$Lst->item(
					array('<img src="'.$img.'" alt="" title="'.$v['title'].'" id="it'.$k.'" />','style'=>'width:1px','href'=>$modpref.'edit='.$k),
					array($v['title'],'title'=>$v['descr'],'style'=>$v['protected'] ? 'font-weight:bold;' : '','href'=>$v['_aedit']),
					array($services ? $services : $ltpl['all'],'style'=>$services ? '' : 'font-style:italic;text-align:center'),
					$Lst('func',
						$v['_aup'] ? array($v['_aup'],static::$lang['up'],$images.'up.png') : false,
						$v['_adown'] ? array($v['_adown'],static::$lang['down'],$images.'down.png') : false
					),
					$Lst('func',
						$v['protected'] ? false : '<img src="'.$images.($v['active'] ? 'active.png' : 'inactive.png').'" alt="" title="'.($v['active'] ? $ltpl['active'] : $ltpl['inactive']).'" />',
						#$v['protected'] ? false : array($modpref.'swap='.$k,$v['active'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['active'] ? $images.'active.png' : $images.'inactive.png'),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png','extra'=>array('onclick'=>'return confirm(\''.$ltpl['are_you_sure'].'\')'))
					)
				);
			}
		}
		else
			$Lst->empty(static::$lang['no']);
		return Eleanor::$Template->Cover((string)$Lst->end());
	}

	/*
		Страница добавления/редактирования модуля, загружаемого на главной странице

		$id - числовой идентификатор модуля, который мы правим. Если $id==0, значит модуль добавляется.
		$values - массив значений. Ключи:
			id - идентификатор выбранного модуля
			pos - числовой идентификатор очередности загрузки модуля
		$modules - перечень доступных модулей. Формат: ID=>название модуля
		$error - текст ошибки, если пусто - ошибки нет
		$back - URL для возврата
		$bypost - флаг загрузки содержимого из POST запроса
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление или false
	*/
	public static function AddEdit($id,$values,$modules,$error,$back,$links)
	{
		static::Menu($id ? '' : 'add');
		$ltpl=Eleanor::$Language['tpl'];
		$mops='';
		foreach($modules as $k=>&$v)
			$mops.=Eleanor::Option($v,$k,$k==$values['id']);

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item(static::$lang['module'],Eleanor::Select('id',$mops,array('tabindex'=>1)))
			->item(array(static::$lang['pos'],'tip'=>static::$lang['pos_'],Eleanor::Input('pos',$values['pos'],array('type'=>'number','tabindex'=>2,'min'=>1))))
			->button($back.Eleanor::Button($id ? static::$lang['save'] : static::$lang['add'],'submit',array('tabindex'=>10)).($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>3,'onclick'=>'if(confirm(\''.$ltpl['are_you_sure'].'\'))window.location=\''.$links['delete'].'\'')) : ''))
			->end()
			->endform();
		return Eleanor::$Template->Cover((string)$Lst,$error);
	}
}
TplAdminMainpage::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/mainpage-*.php',false);