<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны для админки генератора sitemap-ов
*/
class TPLSitemap
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
			array($links['list'],Eleanor::$Language['sitemap']['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				),
			),
			array($links['er'],static::$lang['editrobots'],'act'=>$act=='er'),
		);
	}

	/*
		Страница отображения всех sitemap-ов
		$items - массив sitemap-ов. Формат: ID=>array(), ключи внутреннего массива:
			title - название sitemap-а
			modules - массив ID модулей, которые генерируются в данном sitemap
			taskid - ID задачи данного sitemap-а
			total - число ссылок, которых всего нужно сгенерировать
			already - число уже сгенерированных ссылок
			file - название файла с результатом генерерации (название файла sitemap-а)
			compress - флаг сжатия sitemap-а
			status - статус активности sitemap-а
			lastrun - время последнего запуска задачи sitemap-a
			nextrun - время следующего запуска задачи sitemap-a
			free - флаг завершенности процесса создания sitemap-а. Когда значение данного ключа равно 1, значит в этот момент происходит генерация sitemap-a
			_aswap - ссылка на инвертирование статуса активности sitemap-a
			_aedit - ссылка на редактирование sitemap-а
			_adel - ссылка на удаление sitemap-а
		$cnt - количество sitemap-ов всего
		$modules - массив название модулей. Формат id=>название модуля
		$pp - количество sitemap-ов на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_file - ссылка на сортировку списка $items по файла (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_status - ссылка на сортировку списка $items по статусу (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внтури которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества sitemap-ов отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$cnt,$modules,$page,$pp,$qs,$links)
	{
		static::Menu('list');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$ltpl=Eleanor::$Language['tpl'];

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'file'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',6)
			->begin(
				$ltpl['name'],
				array(static::$lang['file'],'sort'=>$qs['sort']=='file' ? $qs['so'] : false,'href'=>$links['sort_file']),
				Eleanor::$Language['main']['modules'],
				array(static::$lang['status'],'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		$images=Eleanor::$Template->default['theme'].'images/';
		if($items)
		{
			foreach($items as $k=>&$v)
			{
				if($v['free'])
					$status=$v['lastrun']===null ? '<span style="color:red">Error</span>' : '<span style="color:green" title="'.static::$lang['pnrun'].'">'.((int)$v['lastrun']>0 ? Eleanor::$Language->Date($v['lastrun'],'fdt') : '&empty;').' - '.($v['nextrun'] ? Eleanor::$Language->Date($v['nextrun'],'fdt') : '&empty;').'</span>';
				else
					$status='<progress data-id="'.$k.'" style="width:100%" value="'.$v['already'].'" max="'.($v['total']>0 ? $v['total'] : 1).'" title="'.($pers=$v['total']>0 ? round($v['already']/$v['total']*100,2) : 0).'%"><span>'.$pers.'</span>%</progress>';
				$ms='';
				foreach($v['modules'] as &$mv)
					if(isset($modules[$mv]))
						$ms.=$modules[$mv].', ';

				$Lst->item(
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.($v['title'] ? $v['title'] : '&mdash;').'</a>',
					$v['file'],
					$ms ? rtrim($ms,', ') : '&mdash;',
					$status,
					$Lst('func',
						$ms ? array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png') : false,
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty(static::$lang['nosm']);
		return Eleanor::$Template->Cover(
		'<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.static::$lang['file'].'</b><br />'.Eleanor::Input('fi[file]',$qs['']['fi']['file']).'</td>
					<td style="text-align:center;vertical-align:middle">'.Eleanor::Button($ltpl['apply']).'</td>
				</tr>
			</table>
<script type="text/javascript">//<![CDATA[
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);

	new ProgressList("'.$GLOBALS['Eleanor']->module['name'].'","'.Eleanor::$services['cron']['file'].'");
});//]]></script>
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
		.$Lst->end()
		.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['smpp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page'])));
	}

	/*
		Шаблон создания/редактирования sitemap-а

		$id - идентификатор редактируемого sitemap-а, если $id==0 значит настройка добавляется
		$values - массив значений полей
			Общие ключи:
			modules - ID (мн.) модулей, используемых в данном sitemap-е
			file - имя файла sitemap-a
			compress - флаг сжатия sitemap-а
			fulllink - флаг полных ссылок
			status - статус активности sitemap-а
			per_time - количество ссылок, генерируемых за раз
			sendservice - массив сервисов, куда разослать sitemap после его генерации
			run_year - год запуска sitemap-а
			run_month - месяц запуска sitemap-а
			run_day - день запуска sitemap-а
			run_hour - час запуска sitemap-а
			run_minute - минута запуска sitemap-а
			run_second - секунда запуска sitemap-а
			_recreate - флаг воссоздания sitemap-а (только для редактирования)
			_runnow - флаг запуска создания sitemap-а сразу после сохранения

			Языковые ключи:
			title_l - название sitemap-а

		$settings - массив настроек для модулей. Формат array(array(),array()...). Ключи внутренних массивов:
			id - ID модуля
			d - описание модуля
			t - название модуля
			e - ошибка настроек (общая)
			c - массив исходных конфигураций модуля, ключи
				title - название настройки
				descr - (возможный ключ) описание настройки
			s - массив результирующего HTML для настроек, ключи сходны с ключами массива c выше.
		$errors - массив ошибок
		$bypost - признак того, что данные нужно брать из POST запроса
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
		$back - URL возврата
	*/
	public static function AddEdit($id,$values,$modules,$settings,$errors,$bypost,$links,$back)
	{
		static::Menu($id ? '' : 'add');
		$ltpl=Eleanor::$Language['tpl'];

		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
				$ml['title_l'][$k]=Eleanor::Input('title_l['.$k.']',Eleanor::FilterLangValues($values['title_l'],$k));
		}
		else
			$ml=array(
				'title_l'=>Eleanor::Input('title_l',Eleanor::FilterLangValues($values['title_l'])),
			);

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$ss=$mods=$opts='';
		foreach($modules as $k=>&$v)
			$mods.=Eleanor::Option($v,$k,in_array($k,$values['modules']));

		foreach(array('google'=>'Google','yahoo!'=>'Yahoo!','ask.com'=>'Ask.com','bing'=>'Bing') as $ks=>$vs)
			$ss.=Eleanor::Option($vs,$ks,!is_array($values['sendservice']) or in_array($ks,$values['sendservice']));

		foreach($settings as &$v)
			$opts=self::GetSettings($v);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item($ltpl['name'],Eleanor::$Template->LangEdit($ml['title_l'],null))
			->item(Eleanor::$Language['main']['modules'],Eleanor::Items('modules',$mods,array('id'=>'modules')))
			->item(array(static::$lang['file'],Eleanor::Input('file',$values['file']),'tip'=>static::$lang['file_']))
			->item(static::$lang['egzip'],Eleanor::Check('compress',$values['compress']))
			->item(static::$lang['fullurl'],Eleanor::Check('fulllink',$values['fulllink']))
			->item(static::$lang['per_run'],Eleanor::Input('per_time',$values['per_time'],array('min'=>10,'max'=>65000,'type'=>'number')))
			->item(static::$lang['status'],Eleanor::Check('status',$values['status']))
			->item(static::$lang['sendservice'],Eleanor::Items('sendservice',$ss))
			->item(static::$lang['runnow'],Eleanor::Check('_runnow',$values['_runnow']));
		if($id)
			$Lst->item(static::$lang['recreate'],Eleanor::Check('_recreate',$values['_recreate']));
		$ge=(string)$Lst->end();

		$tt=(string)$Lst->begin()
			->item(array(static::$lang['runyear'],Eleanor::Input('run_year',$values['run_year']),'tip'=>static::$lang['runyear_']))
			->item(array(static::$lang['runmonth'],Eleanor::Input('run_month',$values['run_month']),'tip'=>static::$lang['runmonth_']))
			->item(array(static::$lang['runday'],Eleanor::Input('run_day',$values['run_day']),'tip'=>static::$lang['runday_']))
			->item(array(static::$lang['runhour'],Eleanor::Input('run_hour',$values['run_hour']),'tip'=>static::$lang['runhour_']))
			->item(array(static::$lang['runminute'],Eleanor::Input('run_minute',$values['run_minute']),'tip'=>static::$lang['runminute_']))
			->item(array(static::$lang['runsecond'],Eleanor::Input('run_second',$values['run_second']),'tip'=>static::$lang['runsecond_']))
			->end();

		$c=$Lst->form()
			->tabs(
				array($ltpl['general'],$ge),
				array(static::$lang['timetorun'],$tt),
				array(
					static::$lang['mopts'],
					'<div id="mod-options"'.($opts ? ' style="display:none"' : '').'>'.Eleanor::$Template->Message(static::$lang['nomops'],'info').'</div><div id="msetts"'.($opts ? '' : ' style="display:none"').'>'.$opts.'</div>'
				)
			)
			->submitline($back.Eleanor::Button().($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($c,$errors,'error').'<script type="text/javascript">//<![CDATA[
$(function(){
	var msetts={},
		noms=$("#mod-options");
	$("table[id^=\"ms-\"]","#msetts").each(function(){
		msetts[$(this).prop("id").replace("ms-","")]=$(this).data("showed",true);
	});
	$("#modules").change(function(){
		var th=$(this),
			mids=th.val()||[],
			ina,
			oldv=false,
			messsh=false;
		$.each(msetts,function(k,v){
			ina=$.inArray(k,mids);
			if(ina>-1)
			{
				if(!v.data("showed"))
				{
					if(oldv)
						v.insertAfter(oldv);
					else
						v.prependTo("#msetts");
					v.data("showed",true);
				}
				messsh=true;
				mids.splice(ina,1);
				oldv=v;
			}
			else if(ina==-1 && v.data("showed"))
				v.detach().data("showed",false);
		})

		if(messsh)
		{
			$("#mod-options").hide();
			$("#msetts").show();
		}
		else
		{
			$("#mod-options").show();
			$("#msetts").hide();
		}
		if(mids.length>0)
			CORE.Ajax(
				{
					direct:"admin",
					file:"sitemap",
					event:"loadmsetts",
					mids:mids
				},
				function(r)
				{
					var op;
					$.each(r,function(k,v){
						if($.inArray(k,mids)>-1)
						{
							op=th.find("option[value=\""+k+"\"]").prev();
							while(op.size()>0)
							{
								if(typeof msetts[op.val()]!="undefined" && msetts[op.val()].data("showed"))
								{
									msetts[op.val()].after(v);
									op=false;
									break;
								}
								op=op.prev();
							}
							if(op)
								$("#msetts").prepend(v);
							msetts[k]=$("#ms-"+k).data("showed",true);
							if(!messsh)
							{
								$("#mod-options").hide();
								$("#msetts").show();
							}
						}
					})
				}
			);
	});
})//]]></script>';
	}

	/*
		Элемент шаблона. Отображение настроек модуля при правке sitemap-а. Используется и для AJAX

		$ms массив с параметрами сайта. Ключи:
			id - ID модуля
			d - описание модуля
			t - название модуля
			e - ошибка настроек (общая)
			c - массив исходных конфигураций модуля, ключи
				title - название настройки
				descr - (возможный ключ) описание настройки
			s - массив результирующего HTML для настроек, ключи сходны с ключами массива c выше.
	*/
	public static function GetSettings($ms)
	{
		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin(array('id'=>'ms-'.$ms['id']))
			->head('<span title="'.$ms['d'].'">'.$ms['t'].'</span>');

		if($ms['e'])
			$Lst->s.='<tr><td colspan="2">'.Eleanor::$Template->Message($ms['e'],'error').'</td></tr>';
		else
			foreach($ms['c'] as $ck=>&$cv)
				$Lst->item(array($cv['title'],$ms['s'][$ck],'descr'=>isset($cv['descr']) ? $cv['descr'] : false));
		return$Lst->end();
	}

	/*
		Страница правки файла robots.txt

		$v - содержимое файла
		$save - флаг сохраненности
	*/
	public static function EditRobots($v,$saved)
	{
		static::Menu('er');
		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item(static::$lang['robots'],Eleanor::Text('text',$v,array(),0))
			->button(Eleanor::Button())
			->end()
			->endform();
		return Eleanor::$Template->Cover(($saved ? Eleanor::$Template->Message(static::$lang['rsaved'],'info') : '').$Lst);
	}

	/*
		Страница удаления карты сайта
		$a - массив удаляемой карты сайта, ключи:
			file - файл карты сайта
			title - название карты сайта
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['title'],$a['file']),$back));
	}
}
TplSitemap::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/sitemap-*.php',false);