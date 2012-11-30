<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	ќформление дл€ админки системного модул€ "√лавна€ страница".
*/
class TPLAdminMainpage
{	public static
		$lang;	/*
		ћеню модул€
	*/	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],static::$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					$links['add'] ? array($links['add'],static::$lang['add'],'act'=>$act=='add') : false,
				),
			),
		);	}
	/*
		ѕеречень модулей, основна€ страница админки модул€

		$items - массив, содержащий в себе перечень модулей. ‘ормат: ID=>array() ключи внутреннего массива:
			services - массив, каждый элемент которого - им€ сервиса, в котором доступен модуль
			title - строка, название модул€
			descr - строка, описание модул€
			protected - флаг защищенного модул€
			path - каталог модул€
			image - путь к логотипу модул€. ≈сли в имени картинки встречаетс€ *, значит ее нужно заменить на "small"
			active - флаг активного (включенного модул€)
			user_groups - идентификаторы групп пользователей, которым доступен данных модуль
			pos - числовой идентификатор позиции модул€. ¬есь массив $items отсортирован именно по этому полю от меньшего (1) к большему
			_aedit - ссылка на редактирование модул€
			_adel - ссылка на удаление модул€
			_aup - ссылка на подн€тие модул€ вверх, если равна false - значит модуль уже и так находитс€ в самом верху
			_adown - ссылка на опускание модул€ вниз, если равна false - значит модуль уже и так находитс€ в самом низу
	*/
	public static function ShowList($items)
	{		static::Menu('list');
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
		{			$images=Eleanor::$Template->default['theme'].'images/';
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
		—траница добавлени€/редактировани€ модул€, загружаемого на главной странице

		$id - числовой идентификатор модул€, который мы правим. ≈сли $id==0, значит модуль добавл€етс€.
		$values - массив значений.  лючи:
			id - идентификатор выбранного модул€
			pos - числовой идентификатор очередности загрузки модул€
		$modules - перечень доступных модулей. ‘ормат: ID=>название модул€
		$error - текст ошибки, если пусто - ошибки нет
		$back - URL дл€ возврата
		$bypost - флаг загрузки содержимого из POST запроса
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление или false
	*/
	public static function AddEdit($id,$values,$modules,$error,$back,$links)
	{		static::Menu($id ? '' : 'add');		$ltpl=Eleanor::$Language['tpl'];
		$mops='';
		foreach($modules as $k=>&$v)
			$mops.=Eleanor::Option($v,$k,$k==$values['id']);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item(static::$lang['module'],Eleanor::Select('id',$mops,array('tabindex'=>1)))
			->item(array(static::$lang['pos'],'tip'=>static::$lang['pos_'],Eleanor::Control('pos','number',$values['pos'],array('tabindex'=>2,'min'=>1))))
			->button($back.Eleanor::Button('OK','submit',array('tabindex'=>10)).($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>3,'onclick'=>'if(confirm(\''.$ltpl['are_you_sure'].'\'))window.location=\''.$links['delete'].'\'')) : ''))
			->end()
			->endform();
		return Eleanor::$Template->Cover((string)$Lst,$error);
	}
}
TplAdminMainpage::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/mainpage-*.php',false);