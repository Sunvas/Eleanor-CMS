<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны админки рассылок
*/
class TPLSpam
{	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['spam'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				),
			),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);
	}
	/*
		Страница отображения всех рассылок
		$items - массив рассылок. Формат: ID=>array(), ключи внутреннего массива:
			title - название рассылки
			sent - количество отправленных писем
			total - всего нужно отправить писем
			status - статус рассылки (разослано, на паузе, остановлено, запущено)
			statusdate - дата установки статуса
			_aedit - ссылка на редактирование рассылки
			_adel - ссылка на удаление рассылки

			В зависимости от статусов, возможны дополнительные ключи.
			Для статуса runned (запущена), доступны ключи
			_astop - ссылка на остановку
			_apause - ссылка на паузу

			Для статуса "на паузе" доступен ключ
			_astop - ссылка на остановку
			+
			Для остальных статусов, доступен ключ:
			_arun - ссылка на запуск

		$cnt - количество рассылок всего
		$pp - количество рассылок на страницу
		$page - номер текущей страницы, на которой мы сейчас находимся
		$qs - массив параметров адресной строки для каждого запроса
		$links - перечень необходимых ссылок, массив с ключами:
			sort_innertitle - ссылка на сортировку списка $items по названию (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_status - ссылка на сортировку списка $items по статусу для отпрвки ошибки (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	*/	public static function ShowList($items,$cnt,$pp,$page,$qs,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language['spam'];
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin(
				array($ltpl['name'],'sort'=>$qs['sort']=='innertitle' ? $qs['so'] : false,'href'=>$links['sort_innertitle']),
				array($lang['condition'],250,'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
			{				switch($v['status'])
				{					case'runned':
						$status='<progress data-id="'.$v['id'].'" style="width:100%" value="'.$v['sent'].'" max="'.$v['total'].'" title="'.($pers=$v['total']>0 ? round($v['sent']/$v['total']*100,2) : 0).'%"><span>'.$pers.'</span>%</progress><br /><a href="'.$a['_astop'].'">'.$lang['stop'].'</a> <a href="'.$a['_apause'].'">'.$lang['pause'].'</a>';
					break;					case'paused':
						$status=$lang['paused'].' '.$v['statusdate'].'<br /><a href="'.$a['_astop'].'">'.$lang['stop'].'</a> <a href="'.$a['_arun'].'">'.$lang['run'].'</a>';
					break;
					case'finished':
						$status='<span style="color:green">'.$lang['finished'].' '.$v['statusdate'].'</span><br /><a href="'.$a['_arun'].'">'.$lang['+run'].'</a>';
					break;
					case'stopped':
					default:
						$status='<span style="color:red">'.$lang['stopped'].' '.$v['statusdate'].'</span><br /><a href="'.$a['_arun'].'">'.$lang['run'].'</a>';
				}
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.$v['title'].'</a>',
					array($status,'center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty($lang['nospam']);

		return Eleanor::$Template->Cover(
			'<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end()
			.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['spp'],$Lst('perpage',$pp)).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k'))
			.Eleanor::Button('Ok').'</div></form><script type="text/javascript">/*<![CDATA[*/$(function(){One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);new ProgressList("'.$GLOBALS['Eleanor']->module['name'].'","'.Eleanor::$services['cron']['file'].'");})//]]></script>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,$qs)
		);	}

	/*
		Шаблон создания/редактирования рассылки

		$id - идентификатор редактируемой настройки, если $id==0 значит настройка добавляется
		$values - массив значений полей
			Общие ключи:
			per_run - количество писем отправляемых за раз
			finame - фильтр по имени пользователя
			finamet - условие для фильтра по имени пользователя
			figroup - фильтр по группам
			figroupt - условие для фильтра по группам
			fiip - фильтр по IP адресам
			firegisterb - фильтр по регистрации ОТ
			firegistera - фильтр по регистрации ДО
			filastvisitb - фильтр по последнему визиту ОТ
			filastvisita - фильтр по последнему визиту ДО
			figender - фильтр по полу
			fiemail - фильтр по e-mail
			fiids - фильтр по ID пользователей
			deleteondone - флаг автоудаления рассылки после завершения
			status - статус рассылки (stopped, runned, finished, paused)
			_onelang - флаг моноязычности

			Языковые ключи:
			innertitle - внутреннее название рассылки
			title - заголовок письма
			text - текст письма

		$runned - признак того, что рассылка заблокирована (производится рассылка)
		$uploader - интерфейс загрузчика файлов
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
		$errors - массив ошибок
		$bypost - признак того, что данные нужно брать из POST запроса
		$back - URL возврата
	*/
	public static function AddEdit($id,$values,$runned,$uploader,$links,$errors,$bypost,$back)
	{		static::Menu($id ? '' : 'add');		$lang=Eleanor::$Language['spam'];
		$ltpl=Eleanor::$Language['tpl'];
		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		if(Eleanor::$vars['multilang'])
		{
			$mchecks=$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$mchecks[$k]=!$id || !empty($values['title'][$k]) || !empty($values['descr'][$k]) || !empty($values['startgroup'][$k]);
				$ml['innertitle'][$k]=Eleanor::Edit('innertitle['.$k.']',Eleanor::FilterLangValues($values['innertitle'],$k),array('tabindex'=>17));
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>18));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>19)));
			}
		}
		else
			$ml=array(
				'innertitle'=>Eleanor::Edit('innertitle',$values['innertitle'],array('tabindex'=>17)),
				'title'=>Eleanor::Edit('title',$values['title'],array('tabindex'=>18)),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>19))),
			);

		$Lst=Eleanor::LoadListTemplate('table-form');
		$addon=$runned ? array('disabled'=>'disabled') : array();

		$uf=$Lst->begin()
			->item($lang['groups'],Eleanor::Items('figroup',UserManager::GroupsOpts($values['figroup']),10,$addon+array('tabindex'=>1))
				.'<br /><label>'.Eleanor::Radio('figroupt','and',$values['figroupt']=='and',$addon+array('tabindex'=>2)).$lang['and'].'</label> <label>'.Eleanor::Radio('figroupt','or',$values['figroupt']=='or',$addon+array('tabindex'=>3)).$lang['or'].'</label>'
			)
			->item($lang['username'],Eleanor::Select('finamet',Eleanor::Option($lang['b'],'b','b'==$values['finamet']).Eleanor::Option($lang['e'],'e','e'==$values['finamet']).Eleanor::Option($lang['c'],'c','c'==$values['finamet']).Eleanor::Option($lang['m'],'m','m'==$values['finamet']),$addon+array('tabindex'=>3,'style'=>'width:200px')).Eleanor::Edit('finame',$values['finame'],$addon+array('tabindex'=>5,'style'=>'width:50%')))
			->item($lang['register'],Dates::Calendar('firegisterb',$values['firegisterb'],true,$addon+array('style'=>'width:40%','tabindex'=>4)).' &mdash; '.Dates::Calendar('firegistera',$values['firegistera'],true,$addon+array('style'=>'width:40%','tabindex'=>5)))
			->item($lang['last_visit'],Dates::Calendar('filastvisitb',$values['filastvisitb'],true,$addon+array('style'=>'width:40%','tabindex'=>6)).' &mdash; '.Dates::Calendar('filastvisita',$values['filastvisita'],true,$addon+array('style'=>'width:40%','tabindex'=>7)))
			->item('IP',Eleanor::Edit('fiip',$values['fiip'],$addon+array('tabindex'=>8)))
			->item($lang['gender'],Eleanor::Select('figender',Eleanor::Option($lang['ni'],-2,$values['figender']==-2).Eleanor::Option($lang['ns'],-1,$values['figender']==-1).Eleanor::Option($lang['female'],0,$values['figender']==0).Eleanor::Option($lang['male'],1,$values['figender']==1),$addon+array('tabindex'=>9)))
			->item('E-mail',Eleanor::Edit('fiemail',$values['fiemail'],$addon+array('tabindex'=>10)))
			->item('IDs',Eleanor::Edit('fiids',$values['fiids'],$addon+array('tabindex'=>11)))
			->button(Eleanor::Button($lang['ts'],'button',array('onclick'=>'TryUsers()','tabindex'=>12)).' '.Eleanor::Button($lang['hideres'],'button',array('onclick'=>'HideTryUsers()','style'=>'display:none','tabindex'=>13,'id'=>'hideres')))
			->end()
			.'<div id="tryusers" style="display:none"></div><script type="text/javascript">//<![CDATA[
			function TryUsers(page)
			{
				var request={direct:"admin",file:"spam",event:"search",page:page||0,pp:$("input[name=\"per_run\"]").val()};
				$("#newspam").find("[name^=\"fi\"]").each(function(){
					var obj=$(this),v=obj.val();
					if(v)
						request[obj.attr("name")]=v;
				})
				CORE.Ajax(
					request,
					function(result)
					{
						$("#tryusers").html(result).add("#hideres").show();
					}
				);
			}
			function HideTryUsers()
			{
				$("#tryusers").empty().add("#hideres").hide();
			}//]]></script>';

		$Lst->begin()
			->item(array($lang['per_run'],'descr'=>$lang['per_run_'],Eleanor::Edit('per_run',$values['per_run'],array('tabindex'=>14))))
			->item(array($lang['delspam'],'descr'=>$lang['delspam_'],Eleanor::Check('deleteondone',$values['deleteondone'],array('tabindex'=>15))))
			->item($lang['condition'],Eleanor::Select('status',Eleanor::Option($lang['stopped'],'stopped',$values['status']=='stopped').Eleanor::Option($lang['run'],'runned',$values['status']=='runned').Eleanor::Option($lang['paused'],'paused',$values['status']=='paused').Eleanor::Option($lang['finished'],'finished',$values['status']=='finished'),array('tabindex'=>16)))
			->item($lang['innertitle'],Eleanor::$Template->LangEdit($ml['innertitle'],null))
			->item($lang['topic'],Eleanor::$Template->LangEdit($ml['title'],null))
			->item($lang['text'],Eleanor::$Template->LangEdit($ml['text'],null));

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$mchecks,null,20));

		$ss=(string)$Lst->button((string)$uploader)->end();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];

		return Eleanor::$Template->Cover(
			($runned ? Eleanor::$Template->Message($lang['runned'],'info') : '')
			.$Lst->form(array('id'=>'newspam'))
			->tabs(
				array($lang['userfilter'],$uf,),
				array($lang['ssetting'],$ss,)
			)
			->submitline($back.Eleanor::Button('OK','submit',array('tabindex'=>21)).($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>22,'onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->endform(),
			$errors,'error'
		);
	}

	/*
		Элемент шаблона. Вывод таблицы пользователей из тестового поиска.

		$items - массив пользователей, формат: ID=>array(), ключи внутреннего массива:
			full_name - полное имя пользователя
			name - имя пользоватя НЕ безопасный HTML!
			email - e-mail пользователя
			groups - массив групп пользователя
			ip - IP адрес пользователя
			last_visi - дата последнего визита пользователя на сайт в формате YYYY-MM-DD HH:II:SS
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя, может быть false, если удаление пользователя недоступно
		$groups - массив групп пользователей. Формат ID=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML суффикс группы
		$pp - количество пользователей на страницу
		$page - номер текущей страницы, на которой мы сейчас находимся
		$cnt - количество пользователей всего
	*/
	public static function UsersList($items,$groups,$pp,$page,$cnt)
	{		$lang=Eleanor::$Language['spam'];
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin($lang['username'],$lang['groups'],$lang['last_visit'],$ltpl['functs']);

		$c='';
		if($items)
		{
			$editimg=Eleanor::$Template->default['theme'].'images/edit.png';
			$delimg=Eleanor::$Template->default['theme'].'images/delete.png';
			foreach($items as &$v)
			{
				$grs='';
				foreach($v['groups'] as &$gv)
					if(isset($groups[$gv]))
						$grs.='<a href="'.$groups[$gv]['_aedit'].'">'.$groups[$gv]['html_pref'].$groups[$gv]['title'].$groups[$gv]['html_end'].'</a>, ';
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],ELENT,CHARSET).'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>'),
					rtrim($grs,' ,'),
					array(substr($v['last_visit'],0,-3),'center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$editimg),
						$v['_adel'] ? array($v['_adel'],Eleanor::$Language['main']['delete'],$delimg) : false
					)
				);
			}
		}
		else
			$Lst->empty($lang['nousers']);
		return$Lst->end().Eleanor::$Template->Pages($cnt,$pp,$page,'#','TryUsers');
	}

	/*
		Страница удаления рассылки
		$a - массив удаляемой рассылки, ключи:
			innertitle - внутреннее название рассылки
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['spam']['deleting'],$a['innertitle']),$back));
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}