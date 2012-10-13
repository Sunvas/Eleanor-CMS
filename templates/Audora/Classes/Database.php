<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны баз данных админки
*/
class TplDatabase
{	/*
		Меню модуля
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['db'];
		$links=&$GLOBALS['Eleanor']->module['links'];
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['br'],$lang['backup&recovery'],'act'=>$act=='sypex'),
			array($links['rn'],$lang['recovernames'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				)
			),
		);
	}
	/*
		Шаблон страницы для дампера БД Sypex
	*/	public static function Sypex()
	{		static::Menu('sypex');		return Eleanor::$Template->OpenTable().'<div style="width:586px;height:462px;margin:0px auto;"><iframe src="addons/sxd/index.php?eleanorid='.session_id().'" width="586" height="462" style="border:0px;">Loading...</iframe></div>'.Eleanor::$Template->CloseTable();	}

	/*
		Шаблон страницы для задач обновления имен

		$items список задач по обновлению имен. Формат: ID=>array(), ключи массива:
			options - массив с ключами
				total - всего записей
				tables - названия таблиц, в которых необходимо изменить имена пользователей
				ids - имена полей таблицы с ID пользователя
				names - имена полей таблицы с именем пользователя
			lastrun - дата последнего запуска задачи
			status - статус активности
			data - массив с ключами
				total - число измененных записей
				done - флаг завершенности задачи
				updated - (только если done) число обновленных записей
			_aswap - ссылка на инвертирование статуса, если возможно
			_aedit - ссылка на редактирование, если возможно
			_adel - ссылка на удаление
		$cnt - число задач всего
		$page - страница, на которой мы сейчас находимся
		$pp - число задач на страницу
	*/
	public static function ShowList($items,$cnt,$page,$pp)
	{		static::Menu('list');
		$lang=Eleanor::$Language['db'];
		$ltpl=Eleanor::$Language['tpl'];		$Lst=Eleanor::LoadListTemplate('table-list',4)->begin($lang['tables'],$lang['fields'],$lang['status'],array($ltpl['functs'],80));

		$image=Eleanor::$Template->default['theme'].'images/';
		if($items)
			foreach($items as &$v)
			{				$status=$v['status'] && $v['options']['total']>=$v['data']['total'] && $v['options']['total']>0 ? '<progress data-id="'.$v['id'].'" style="width:100%" value="'.$v['data']['total'].'" max="'.$v['options']['total'].'" title="'.($pers=round($v['data']['total']/$v['options']['total']*100,2)).'%"><span>'.$pers.'</span>%</progress>' : '&mdash;';
				$Lst->item(
					join(', ',array_keys($v['options']['tables'])),
					join(', ',array_merge($v['options']['ids'],$v['options']['names'])),
					array($v['data']['done'] ? '<span style="color:green">'.sprintf($lang['done'],Eleanor::$Language->Date($v['lastrun'],'fdt'),$v['data']['updated']).'</span>' : $status,'center'),
					$Lst('func',
						$v['_aswap'] ? array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png','addon'=>array('id'=>'swap-'.$v['id'])) : false,
						$v['_aedit'] ? array($v['_aedit'],$ltpl['edit'],$images.'edit.png') : false,
						array($a['_adel'],$lptl['delete'],$images.'delete.png','addon'=>array('onclick'=>'return confirm(\''.$lptl['are_you_sure'].'\')'))
					)
				);
			}
		else
			$Lst->empty($lang['notasks']);
		return Eleanor::$Template->Cover($Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['tpp'],$Lst->perpage($pp)).'</div></div>'.Eleanor::$Template->Pages($cnt,$pp,$page))
			.'<script type="text/javascript">/*<![CDATA[*/$(function(){new ProgressList("'.$GLOBALS['Eleanor']->module['name'].'","'.Eleanor::$services['cron']['file'].'");})//]]></script>';	}

/*
		Шаблон создания/редактирования задачи по обновлению имен пользователей

		$id - идентификатор редактируемой задачи, если $id==0 значит задача добавляется
		$tables - массив всех таблиц в базе данных, куда установлена Eleanor CMS
		$values - массив значений полей
			ids - массив полей с ID пользователей array(field1,field2,...)
			names - массив полей с именами пользователей array(field1,field2,...)
			tables - массив таблицы, в которых проводить замену
			per_load - число записей, обновляющихся за раз
			status - флаг активности задачи
			delete - флаг автоудаления задачи после завершения
		$runned - признак того, что производится обновление (правка заблокирована)
		$error - ошибка, если ошибка пустая - значит ее нет
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function AddEdit($id,$tables,$values,$runned,$error,$back,$links)
	{		static::Menu($id ? '' : 'add');
		$lang=Eleanor::$Language['db'];
		$ltpl=Eleanor::$Language['tpl'];

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$Lst=Eleanor::LoadListTemplate('table-form');
		$items=$fields=$fid=$fname='';
		foreach($tables as &$v)
			$items.=Eleanor::Option($v,false,in_array($v,$values['tables']));

		foreach($values['ids'] as $k=>&$v)
		{			if(!isset($values['names'][$k]))
				continue;
			if($k==0)
			{
				$fid=$v;
				$fname=$values['names'][$k];
				continue;
			}
			$fields.='<li><a href="#" class="tlistbtn"><img src="'.Eleanor::$Template->default['theme'].'images/minus_d.gif" alt="&minus;" title="&minus;" /></a><ul><li><span>'.$lang['fid'].'</span><div>'.Eleanor::Edit('ids[]',$v,array('style'=>'width:100%')).'</div></li><li><span>'.$lang['fname'].'</span><div>'.Eleanor::Edit('names[]',$values['names'][$k],array('style'=>'width:100%')).'</div></li></ul><div class="clr"></div></li>';
		}

		$Lst->form(array('id'=>'newtask','onsubmit'=>$runned ? 'return false;' : false))
			->begin()
			->item($lang['tables'],Eleanor::Items('tables',$items,10,array('tabindex'=>1)))
			->item($lang['fields'].'<br /><a href="#" class="plus"><img align="right" src="'.Eleanor::$Template->default['theme'].'images/plus_d.gif" alt="+" title="+" /></a>','<ul class="reset tlist" id="fields"><li><a href="#" class="tlistbtn"><img src="'.Eleanor::$Template->default['theme'].'images/minus_d.gif" alt="&minus;" title="&minus;" /></a><ul><li><span>Поле ID пользователя</span><div>'.Eleanor::Edit('ids[]',$fid,array('style'=>'width:100%','tabindex'=>2)).'</div></li><li><span>Поле имени пользователя</span><div>'.Eleanor::Edit('names[]',$fname,array('style'=>'width:100%','tabindex'=>2)).'</div></li></ul><div class="clr"></div></li>'.$fields.'</ul>')
			->item($lang['per_load'],Eleanor::Control('per_load','number',$values['per_load'],array('min'=>1,'tabindex'=>3)))
			->item(array($ltpl['activate'],Eleanor::Check('status',$values['status'],array('tabindex'=>4)),'tip'=>$lang['act_']))
			->item($lang['del'],Eleanor::Check('delete',$values['delete'],array('tabindex'=>5)))
			->button($back.($runned ? '' : Eleanor::Button('OK','submit',array('tabindex'=>6))).($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>7,'onclick'=>'if(confirm(\''.$ltpl['are_you_sure'].'\'))window.location=\''.$links['delete'].'\'')) : ''))
			->end()
			->endform();

		return Eleanor::$Template->Cover(($runned ? Eleanor::$Template->Message($lang['runned'],'info') : '').$Lst,$error,'error')
			.'<script type="text/javascript">//<![CDATA
$(function(){'.($runned ? '$("#newtask").find(":input").prop("disabled",true);' : '')
.'$(this)
		.on("click","a.plus",function(){
			$("#fields li:first").clone().find(":input").val("").end()
				.find("a:first").show().end().appendTo("#fields");
			return false;
		})
		.on("click","a.tlistbtn",function(){			if($("#fields").children("li").size()>1)
				$(this).parent().remove();
			else
				$("#fields input").val("");
			return false;
		})
})//]]></script>';	}
}