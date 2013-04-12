<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны управления группами пользователей в админке
*/
class TplGroups
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
			array($links['list'],Eleanor::$Language['g']['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				),
			),
		);
	}


	/*
		Страница отображения всех групп пользователей
		$items - массив групп пользователей. Формат: ID=>array(), ключи внутреннего массива (если какое-то значение равно null, значит свойство наследуется):
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML суффикс группы
			protected - флаг защищенной группы
			access_cp - флаг доступа в панель администратора
			captcha - флаг отображения капчи
			moderate - флаг включенной перемодерации публикаций
			parents - строка родителей группы
			_aedit - ссылка на редактирование группы
			_adel - ссылка на удаление группы (если доступно)
			_aparent - ссылка на просмотр подгрупп
			_aaddp - ссылка на добавление подгруппы
		$subitems - массив подгрупп для групп из массива $items. Формат: ID=>array(id=>array(), ...), где ID - идентификатор группы, id - идентификатор подгруппы. Ключи массива подгруппы:
			title - название подгруппы
			_aedit - ссылка на редактирование подгруппы
		$navi - массив, хлебные крошки навигации. Формат ID=>array(), ключи:
			title - заголовок крошки
			_a - ссылка подпункта данной крошки. Может быть равно false
	*/
	public static function ShowList($items,$subitems,$navi)
	{
		static::Menu('list');
		$ltpl=Eleanor::$Language['tpl'];
		$lang=Eleanor::$Language['g'];

		$nav=array();
		foreach($navi as &$v)
			$nav[]=$v['_a'] ? '<a href="'.$v['_a'].'">'.$v['title'].'</a>' : $v['title'];

		$Lst=Eleanor::LoadListTemplate('table-list',6)
			->begin(
				$lang['g_name'],
				array(static::$lang['adminth'],'title'=>$lang['aa']),
				array(static::$lang['captchath'],'title'=>$lang['captcha_']),
				array(static::$lang['moderateth'],'title'=>$lang['moderate_']),
				static::$lang['prot'],
				$ltpl['functs']
			);

		if($items)
		{
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
			{
				$subs='';
				if(isset($subitems[$k]))
					foreach($subitems[$k] as $kk=>&$vv)
						$subs.='<a href="'.$vv['_aedit'].'">'.$vv['title'].'</a>, ';

				$adds=' <a href="'.$v['_aaddp'].'" title="'.static::$lang['addsubg'].'"><img src="'.$images.'plus.gif" alt="" /></a>';
				$Lst->item(
					array($v['html_pref'].$v['title'].$v['html_end'].($subs ? '<br /><span class="small"><a href="'.$v['_aparent'].'" style="font-weight:bold">'.static::$lang['subg'].'</a> '.rtrim($subs,', ').$adds.'</span>' : $adds),'href'=>$v['_aedit']),
					array(Eleanor::$Template->YesNo($v['access_cp']===null ? join(Eleanor::Permissions(array($v['id']),'access_cp')) : $v['access_cp']),'center'),
					array(Eleanor::$Template->YesNo($v['captcha']===null ? join(Eleanor::Permissions(array($v['id']),'captcha')) : $v['captcha']),'center'),
					array(Eleanor::$Template->YesNo($v['moderate']===null ? join(Eleanor::Permissions(array($v['id']),'moderate')) : $v['moderate']),'center'),
					array(Eleanor::$Template->YesNo($v['protected']===null ? join(Eleanor::Permissions(array($v['id']),'protected')) : $v['protected']),'center'),
					$Lst('func',
						$v['_adel'] ? array($v['_adel'],$ltpl['delete'],$images.'delete.png') : false,
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png')
					)
				);
			}
		}
		else
			$Lst->empty(static::$lang['subgnf']);
		return Eleanor::$Template->Cover(($nav ? '<table class="filtertable"><tr><td style="font-weight:bold">'.join(' &raquo; ',$nav).'</td></tr></table>' : '').$Lst->end());
	}

	/*
		Страница добавления/редактирования группы
		$id - идентификатор группы, если $id==0 значит группа добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$inherit - массив параметров, которые наследуются. Параметры заданы ключами массива
		$errors - массив ошибок
		$back - URL возврата
		$hasdraft - признак наличия черновика
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($id,$controls,$values,$inherit,$errors,$back,$links)
	{
		static::Menu($id ? '' : 'add');
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin(array('id'=>'tg'))
			->item(static::$lang['parent'],Eleanor::Select('_parent',Eleanor::Option('&mdash;',0,!$values['_parent'],array(),2).UserManager::GroupsOpts($values['_parent'],$id ? $id : array()),array('id'=>'parent')));
		foreach($controls as $k=>&$v)
			if($v)
				if(is_array($v) and $values[$k])
					$Lst->item(array(
						(empty($v['noinherit']) ? Eleanor::Check('inherit[]',in_array($k,$inherit),array('style'=>'display:none','value'=>$k)) : '').$v['title'],
						'<div>'.Eleanor::$Template->LangEdit($values[$k],null).'</div>',
						'tip'=>$v['descr']
					));
				elseif(is_string($v))
					$Lst->head($v);

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst,$errors,'error').'<script type="text/javascript">//<![CDATA[
$(function(){
	var caninh=true,
		Check=function(ftd,state)
		{
			var ch=$(ftd).find(":checkbox");
			if(typeof state=="undefined")
				state=!ch.prop("checked");
			if(state)
				ch.end().css("text-decoration","line-through").prop("title","'.static::$lang['inherit'].'").next().children("div").hide();
			else
				ch.end().css("text-decoration","").prop("title","").next().children("div").show();
			ch.prop("checked",state)
		},
		Ea=function(){
			Check(this,$(":checkbox",this).prop("checked"));
		},
		tds=$("#tg tr").find("td:first").filter(function(){
			return $(this).has(":checkbox").size()>0;
		}).click(function(){
			if(caninh)
				Check(this);
		}).each(Ea);

		$("#parent").change(function(){
			if($(this).val()>0)
			{
				tds.css("cursor","pointer");
				caninh=true;
			}
			else
			{
				tds.css("cursor","");
				caninh=false;
				tds.find(":checkbox").prop("checked",false).end().each(Ea);
			}
		}).change();
})//]]></script>';
	}

	/*
		Страница удаления группы пользователя
		$a - массив удаляемой группы
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['html_pref'].$a['title'].$a['html_end']),$back));
	}
}
TplGroups::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/groups-*.php',false);