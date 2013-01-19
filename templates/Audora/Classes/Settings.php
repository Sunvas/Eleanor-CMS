<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон секции настроек
*/
class TPLSettings
{
	public static
		$lang;

	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language['settings'];
		$links=&$GLOBALS['Eleanor']->module['links_settings'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			'opts'=>$links['opts']
				? array($links['opts'],static::$lang['olist'],'modules','act'=>$act=='options',
					'submenu'=>$links['addoption']
					? array(
						array($links['addoption'],static::$lang['addo'],'act'=>$act=='addo'),
					)
					: false,
				)
				: false,
			'grs'=>array($links['grs'],$lang['grlist'],'mgblocks','act'=>$act=='groups',
				'submenu'=>$links['addgroup']
				? array(
					array($links['addgroup'],static::$lang['addg'],'act'=>$act=='addg'),
				)
				: false,
			),
			'opt'=>$links['addoption'] && !$links['opts'] ? array($links['addoption'],static::$lang['addo'],'addoption','act'=>$act=='addo') : false,
			'im'=>$links['import'] ? array($links['import'],$lang['import'],'import','act'=>$act=='import') : false,
			'ex'=>$links['export'] ? array($links['export'],$lang['export'],'export','act'=>$act=='export') : false,
		);
	}

	/*
		Шаблон страницы с группами настроек
		$items - массив групп настроек. Формат: ID=>array(), ключи внутренних массивов:
			title - название группы
			descr - описание группы
			protected - флаг защищенности группы
			position - позиция группы. По этому полю группы отсортированы
			cnt - количество настроек в группе
			_buttons - массив "кнопок" для группы, формат type=>link. Постоянные ключи:
				reset - ссылка на сброс настроек группы (значения их становятся значениями по умолчанию)
				show - ссылка на настройки группы
				Возможные ключи:
				up - ссылка на поднятие группы наверх
				down - ссылка на опускание группы вниз
				default - ссылка на подмену значений настроек по умолчанию текущими настройками (текущие настройки станут настройками по умолчанию)
				edit - ссылка на редактирование группы
				delete - ссылка на удаление группы
	*/
	public static function SettGroupsCover($items,$links)
	{
		static::Menu('groups');
		$trs='';
		$ltpl=Eleanor::$Language['tpl'];
		$h=Eleanor::$Template->default['theme'];
		$lo=static::$lang['options'];
		foreach($items as $k=>&$v)
		{
			$trs.='<tr><td style="width:80%" id="gr'.$k.'"><a href="'.$v['_buttons']['show'].'"><b>'.$v['title'].'</b></a><br /><span class="small"><b>'
				.$lo($v['cnt'])
				.'</b>&nbsp;&nbsp;&nbsp;'.$v['descr'].'</span></td><td class="function">';
			if(isset($v['_buttons']['up']))
				$trs.='<a href="'.$v['_buttons']['up'].'" title="'.static::$lang['up'].'"><img src="'.$h.'images/up.png" alt="" /></a>';
			if(isset($v['_buttons']['down']))
				$trs.='<a href="'.$v['_buttons']['down'].'" title="'.static::$lang['down'].'"><img src="'.$h.'images/down.png" alt="" /></a>';
			$trs.='<a href="'.$v['_buttons']['reset'].'" title="'.static::$lang['reset_def_gr'].'"><img src="'.$h.'images/o_del.png" alt="" /></a>';
			if(isset($v['_buttons']['default']))
				$trs.='<a href="'.$v['_buttons']['default'].'" title="'.static::$lang['make_def_gr'].'"><img src="'.$h.'images/o_add.png" alt="" /></a>';
			if(isset($v['_buttons']['edit']))
				$trs.='<a href="'.$v['_buttons']['edit'].'" title="'.$ltpl['edit'].'"><img src="'.$h.'images/edit.png" alt="" /></a>';
			if(isset($v['_buttons']['delete']))
				$trs.='<a href="'.$v['_buttons']['delete'].'" title="'.$ltpl['delete'].'"><img src="'.$h.'images/delete.png" alt="" /></a>';
			$trs.='</td></tr>';
		}

		return Eleanor::$Template->Cover('<table class="tabstyle tabform">'.$trs
			.(isset($links['wg']) ? '<tr><td style="width:80%"><a href="'.$links['wg'].'"><b>'.static::$lang['ops_without_g'].'</b></a><br /><span class="small">'.static::$lang['ops_wo_g_d'].'</span></td><td></td></tr>' : '')
			.'</table>'
			.(isset($links['search']) ? '<form action="'.$links['search'].'" method="post"><div class="submitline" style="text-align: right;"><input style="width: 200px;" type="text" value="" name="search" /><input class="button" type="submit" value="'.static::$lang['find'].'" /></div></form>' : ''));
	}

	/*
		Шаблон страницы с показом ошибки
		$err - текст ошибки
	*/
	public static function SettShowError($err)
	{
		static::Menu();
		return Eleanor::$Template->Cover('',$err,'error');
	}

	/*
		Шаблон страницы-подтвержения о подмене значений настроек группы по умолчанию текущими значениями настроек группы
		$a - массив группы настроек, ключи:
			title - название группы
		$back - URL возврата
	*/
	public static function SettGrDefault($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['make_o_def_c'],$a['title']),$back));
	}

	/*
		Шаблон страницы-подтвержения о сбросе значений настроек группы настройками по умолчанию
		$a - массив группы настроек, ключи:
			title - название группы
		$back - URL возврата
	*/
	public static function SettGrReset($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['ays_to_rg'],$a['title']),$back));
	}

	/*
		Шаблон страницы с отображением контролов-настроек

		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов. Формат: name=>array(). Ключи внутреннего массива:
			id - ID настройки
			multilang - признак мультиязычного поля
			_areset - ссылка на сброс настройки (выставление значения по умолчанию) или false
			_adefault - ссылка на замену значения по умолчанию текущим значением настройки или false
			_aedit - ссылка на редактирование настройки или false
			_adelete - ссылка на удаление настройки или false
			_aup - ссылка на поднятие настройки вверх, если равна false - значит настройка уже и так находится в самом верху
			_adown - ссылка на опускание настройки вниз, если равна false - значит настройка уже и так находится в самом низу
			_agroup - ссылка на группу настройки (при осуществлении поиска), в противном случае равна false
			titles - массив с ключами:
				title - название настройки
				descr - описание настройки
				group - подгруппа настройки и всех последующих настроек
				gtitle - название группы настройки, при осуществлении поиска
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$crerrors - массив критических ошибок контролов. Если в массиве существует ключ из массива контролов, значит ошибка должна быть показана вместо контрола - она критическая
		$errors - массив ошибок сохранения контролов. Если в массиве существует ключ из массива контролов, ошибка должна быть показана над контролом. Мультиязычная переменная.
		$links - перечень необходимых ссылок, массив с ключами:
			form - ссылка на сохранение формы настроек
		$word - искомое слово, необходима подсветка
		$gshow - флаг отображения подгрупп опций
		$error - общая ошибка
	*/
	public static function SettOptionsList($controls,$values,$crerrors,$errors,$links,$word,$gshow,$error)
	{
		static::Menu('options');
		if(!$controls)
			return Eleanor::$Template->Message(Eleanor::$Language['settings']['nooptions'],'error');

		$c='';
		$n=0;
		$ids=array();
		$ltpl=Eleanor::$Language['tpl'];
		$tabs=$tip=false;
		foreach($controls as $k=>&$v)
		{
			$ids[]=$v['id'];

			if($word)
			{
				$v['titles']['title']=Strings::MarkWords($word,$v['titles']['title']);
				$v['titles']['descr']=Strings::MarkWords($word,$v['titles']['descr']);
			}

			$html='';
			if(isset($crerrors[$k]))
				$html=Eleanor::$Template->Message($crerrors[$k],'error');
			else
			{
				$va=&$values[$k];
				if($v['multilang'] and is_array($va))
				{
					$flags='';
					$u=uniqid('l');

					foreach($va as $vak=>&$vav)
					{
						$html.='<div id="'.$u.'-'.$vak.'" class="langtabcont">'.(isset($errors[$k][$vak]) ? Eleanor::$Template->Message($errors[$k][$vak],'error').'<br />' : '').$vav.'</div>';
						$flags.='<a href="#" data-rel="'.$u.'-'.$vak.'" class="'.$vak.($vak==Language::$main ? ' selected' : '').'" title="'.Eleanor::$langs[$vak]['name'].'"><img src="images/lang_flags/'.$vak.'.png" alt="'.Eleanor::$langs[$vak]['name'].'" /></a>';
					}
					$tabs=true;
					$html.='<div id="div-'.$u.'" class="langtabs">'.$flags.'</div><script type="text/javascript">/*<![CDATA[*/$("#div-'.$u.' a").Tabs();//]]></script>';
				}
				else
					$html.=(isset($errors[$k]) ? Eleanor::$Template->Message($errors[$k],'error').'<br />' : '').$va;
			}

			if($v['titles']['descr'])
			{
				$tip=true;
				$descr='<span class="labinfo" title="'.htmlspecialchars($v['titles']['descr'],ELENT,CHARSET).'">(?)</span>';
			}
			else
				$descr='';
			$descr.=$v['titles']['title'];

			if($v['_agroup'])
				$descr.='<br /><br />'.static::$lang['group'].' <a href="'.$v['_agroup'].'">'.$v['titles']['gtitle'].'</a>';


			$n++;
			if($gshow and $v['titles']['group'] or $n==1)
			{
				if($n>1)
					$c.='</table>'.Eleanor::$Template->CloseTable();
				$c.=Eleanor::$Template->Title($v['titles']['group'] ? $v['titles']['group'] : end($GLOBALS['title']))
					.Eleanor::$Template->OpenTable().'<table class="tabstyle tabform">';
			}
			$c.='<tr><td class="label" id="opt'.$v['id'].'">'.$descr.'</td><td>'.$html
				.'</td><td class="function" style="width:130px">'
				.($v['_aup'] ? '<a href="'.$v['_aup'].'" title="'.static::$lang['up'].'"><img src="'.Eleanor::$Template->default['theme'].'images/up.png" alt="" /></a>' : '')
				.($v['_adown'] ? '<a href="'.$v['_adown'].'" title="'.static::$lang['down'].'"><img src="'.Eleanor::$Template->default['theme'].'images/down.png" alt="" /></a>' : '')
				.($v['_areset'] ? '<a href="'.$v['_areset'].'" title="'.static::$lang['reset_opt'].'"><img src="'.Eleanor::$Template->default['theme'].'images/o_del.png" alt="" /></a>' : '')
				.($v['_adefault'] ? '<a href="'.$v['_adefault'].'" title="'.static::$lang['default_opt'].'"><img src="'.Eleanor::$Template->default['theme'].'images/o_add.png" alt="" /></a>' : '')
				.($v['_aedit'] ? '<a href="'.$v['_aedit'].'" title="'.$ltpl['edit'].'"><img src="'.Eleanor::$Template->default['theme'].'images/edit.png" alt="" /></a>' : '')
				.($v['_adelete'] ? '<a href="'.$v['_adelete'].'" title="'.$ltpl['delete'].'"><img src="'.Eleanor::$Template->default['theme'].'images/delete.png" alt="" /></a>' : '')
				.'</td></tr>';
		}

		if($tabs)
			$GLOBALS['jscripts'][]='js/tabs.js';
		if($tip)
			$GLOBALS['jscripts'][]='js/jquery.poshytip.js';

		return($error ? Eleanor::$Template->Message($error,'error') : '')
			.'<form method="post" enctype="multipart/form-data" action="'.$links['form'].'">'
			.$c.'</table>'.Eleanor::Input('ids',join(',',$ids),array('type'=>'hidden'))
			.'<div class="submitline">'.Eleanor::Button().'</div>'
			.Eleanor::$Template->CloseTable().'</form>'
			.($tip ? '<script type="text/javascript">//<![CDATA[
$(function(){
	$("span.labinfo").poshytip({
		className: "tooltip",
		offsetX: -7,
		offsetY: 16,
		allowTipHover: false
	});
});//]]></script>' : '');
	}

	/*
		Шаблон страницы экспорта настроек

		$a - массив со всеми группами и настройками. Формат ID=>array(), ключи массива
			title - название группы настроек
			descr - описание группы настроек
			opts - массив с настройками, формат ID=>array(), ключи массива
				title - название настройки
				descr - описание настройки
		$groups - массив отмеченных групп (IDs)
		$options - массив отмеченных настроек (IDs)
	*/
	public static function SettExport($a,$groups,$options)
	{
		static::Menu('export');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$c='<form method="post"><table class="tabstyle" id="table-ch"><tr class="tablethhead"><th style="width:15px">'.Eleanor::Check('all',false,array('id'=>'all-ch')).'</th><th>'.static::$lang['olist'].'</th></tr>';
		$n=0;
		$script='';
		foreach($a as $k=>&$v)
		{
			$c.='<tr class="'.($n++ % 2 ? 'tabletrline2' : 'tabletrline1').'"><td style="text-align:center">'.Eleanor::Check('groups[]',in_array($k,$groups),array('value'=>$k,'id'=>'gr-'.$k)).'</td><td>';
			if(isset($v['opts']))
			{
				$c.='<a href="#" style="font-weight:bold" onclick="$(\'#opts-'.$k.'\').slideToggle(\'fast\');return false">'.$v['title'].'</a>'.($v['descr'] ? '<br />'.$v['descr'] : '').'<table class="tabstyle" id="opts-'.$k.'" style="display:none;margin:5px">';
				$no=0;
				foreach($v['opts'] as $ok=>&$ov)
					$c.='<tr class="'.($no++ % 2 ? 'tabletrline2' : 'tabletrline1').'"><td style="text-align:center;width:15px;vertical-align:top">'.Eleanor::Check('options[]',in_array($ok,$options),array('value'=>$ok)).'</td><td><b>'.$ov['title'].'</b>'.($ov['descr'] ? '<br />'.$ov['descr'] : '').'</td></tr>';
				$c.='</table>';
			}
			else
				$c.='<b>'.$v['title'].'</b>'.($v['descr'] ? '<br />'.$v['descr'] : '');
			$c.='</td></tr>';
			$script.='new One2AllCheckboxes("#opts-'.$k.'","#gr-'.$k.'","input[name=\"options[]\"]");';
		}
		$c.='</table><div class="submitline">'.static::$lang['ex_with_ex'].Eleanor::Select('update',Eleanor::Option(static::$lang['ex_ignore'],'ignore').Eleanor::Option(static::$lang['ex_update'],'update').Eleanor::Option(static::$lang['ex_full'],'full').Eleanor::Option(static::$lang['ex_delete'],'delete')).' '.Eleanor::Button(static::$lang['do_export']).'</div></form><script type="text/javascript">/*<![CDATA[*/$(function(){'.$script.'new One2AllCheckboxes("#table-ch","#all-ch","input[name=\"groups[]\"]",true);})//]]></script>';
		return Eleanor::$Template->Cover($c);
	}


	/*
		Шаблон страницы импорта настроек

		$info - массив статуса об успешном импорте (без <br />) либо false (если импорт не производился)
		$error - сообщение об ошибке
	*/
	public static function SettImport($info,$error)
	{
		$rilang=static::$lang['import_result'];
		static::Menu('import');
		return Eleanor::$Template->Cover('<form method="post" enctype="multipart/form-data">'
			.($info===false || $error ? '' : Eleanor::$Template->Message($info ? $rilang(count($info['gdel']),count($info['odel']),count($info['groups_ins']),count($info['groups_upd']),count($info['options_ins']),count($info['options_upd'])) : $rilang(),'info'))
			.'<table class="tabstyle tabform"><tr class="tabletrline1"><td class="label">'.static::$lang['select_file_im'].'</td><td>'.Eleanor::Input('import',false,array('tabindex'=>1,'type'=>'file')).'</td></tr></table><div class="submitline">'.Eleanor::Button(static::$lang['do_import'],'submit',array('tabindex'=>2)).'</div></form>',$error);
	}

	/*
		Страница удаления группы
		$a - массив удаляемой группы, ключи:
			title - название группы
		$back - URL возврата
	*/
	public static function SettGroupDelete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting_g'],$a['title']),$back));
	}

	/*
		Страница удаления настройки
		$a - массив удаляемой настройки, ключи:
			title - название настройки
		$back - URL возврата
	*/
	public static function SettDelete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting_o'],$a['title']),$back));
	}

	/*
		Шаблон редактирования группы настроек

		$id - идентификатор редактируемой группы, если $id==0 значит группа добавляется
		$values - массив значений полей
			Общие ключи:
			pos - позиция группы
			keyword - ключевые слова группы (не меняется в случае защищенности)
			name - название группы (не меняется в случае защищенности)
			protected - флаг защищенности группы
			_onelang - флаг моноязычности

			Языковые ключи:
			title - название группы
			descr - описание группы

		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление группы или false
		$errors - массив ошибок
		$bypost - признак того, что данные нужно брать из POST запроса
		$back - URL возврата
	*/
	public static function SettAddEditGroup($id,$values,$links,$errors,$bypost,$back)
	{
		static::Menu($id ? '' : 'addg');
		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
				$ml['descr'][$k]=Eleanor::Text('descr['.$k.']',Eleanor::FilterLangValues($values['descr'],$k),array('tabindex'=>2));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Input('title',$values['title'],array('tabindex'=>1)),
				'descr'=>Eleanor::Text('descr',$values['descr'],array('tabindex'=>2)),
			);

		$extra=$id && $values['protected'] ? array('disabled'=>true) : array();
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()
			->begin()
			->item(array($ltpl['name'],Eleanor::$Template->LangEdit($ml['title'],null),'imp'=>true))
			->item($ltpl['descr'],Eleanor::$Template->LangEdit($ml['descr'],null))
			->item(array(static::$lang['pos'],Eleanor::Input('pos',$values['pos'],array('tabindex'=>3)),'tip'=>static::$lang['pos_']))
			->item(array(static::$lang['keyw_g'],Eleanor::Input('keyword',$values['keyword'],array('tabindex'=>4)+$extra),'imp'=>true))
			->item(array(static::$lang['priv_name'],Eleanor::Input('name',$values['name'],array('tabindex'=>5)+$extra),'imp'=>true))
			->item(static::$lang['prot_g'],Eleanor::Check('protected',$values['protected'],array('tabindex'=>6)+$extra));

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,4));

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));
		$Lst->end()->submitline(
			$back
			.Eleanor::Button('Ok','submit',array('tabindex'=>7))
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover((string)$Lst,$errors);
	}

	/*
		Шаблон создания/редактирования настройки

		$id - идентификатор редактируемой настройки, если $id==0 значит настройка добавляется
		$values - массив значений полей
			Общие ключи:
			group - идентификатор группы, к которой относится настройка
			pos - позиция настройки
			name - внутреннее название настройки
			protected - флаг защищенности группы
			eval_load - код загрузки контрола
			eval_save - код сохранения контрола
			_onelang - флаг моноязычности

			Языковые ключи:
			title - название группы
			descr - описание группы
			startgroup - название подгруппы настроек

		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление настройки или false
		$errors - массив ошибок
		$bypost - признак того, что данные нужно брать из POST запроса
		$back - URL возврата
	*/
	public static function SettAddEditOption($id,$values,$groups,$control,$links,$bypost,$back,$errors)
	{
		static::Menu($id ? '' : 'addo');
		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
				$ml['descr'][$k]=Eleanor::Text('descr['.$k.']',Eleanor::FilterLangValues($values['descr'],$k),array('tabindex'=>2));
				$ml['startgroup'][$k]=Eleanor::Input('startgroup['.$k.']',Eleanor::FilterLangValues($values['startgroup'],$k),array('tabindex'=>4));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Input('title',$values['title'],array('tabindex'=>1)),
				'descr'=>Eleanor::Text('descr',$values['descr'],array('tabindex'=>2)),
				'startgroup'=>Eleanor::Input('startgroup',$values['startgroup'],array('tabindex'=>4)),
			);

		$ltpl=Eleanor::$Language['tpl'];
		$extra=$id && $values['protected'] ? array('disabled'=>true) : array();
		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));
		$langs=array();
		foreach(Eleanor::$langs as $k=>&$v)
			$langs[]='"'.$k.'"';

		$grs='';
		foreach($groups as $k=>&$v)
			$grs.=Eleanor::Option($v,$k,$k==$values['group']);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item(array($ltpl['name'],Eleanor::$Template->LangEdit($ml['title'],null),'imp'=>true))
			->item($ltpl['descr'],Eleanor::$Template->LangEdit($ml['descr'],null))
			->item(array(static::$lang['group'],Eleanor::Select('group',$grs,array('tabindex'=>3)+$extra),'imp'=>true))
			->item(static::$lang['beg_subg'],Eleanor::$Template->LangEdit($ml['startgroup'],null))
			->item(array(static::$lang['pos'],Eleanor::Input('pos',$values['pos'],array('tabindex'=>5)),'tip'=>static::$lang['pos_']))
			->item(static::$lang['priv_name'],Eleanor::Input('name',$values['name'],array('tabindex'=>6)+$extra))
			->item(static::$lang['prot_o'],Eleanor::Check('protected',$values['protected'],array('tabindex'=>7)+$extra));

		if(Eleanor::$vars['multilang'])
			$Lst->item(array(static::$lang['multilang'],Eleanor::Check('multilang',$values['multilang'],array('tabindex'=>7)),'descr'=>static::$lang['multilang_']))
				->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],'Multilangs',9));

		$general=(string)$Lst->end();

		$evals=(string)$Lst->begin()
			->item(array(static::$lang['eval_load'],'descr'=>sprintf(static::$lang['inc_vars'],'$co,$Obj'),Eleanor::Text('eval_load',$values['eval_load'],$extra+array('style'=>'width:100%')).'<br /><a href="#" onclick="$(this).next(\'div\').toggle();return false">'.static::$lang['op_example'].'</a><div style="display:none">'.Eleanor::Text('_','if($a[\'multilang\'])
	foreach($a[\'value\'] as &$v)
	{
		#Your code...
		#$v-=10;
	}
else
{
		#Your code...
		#$a[\'value\']-=10;
}
return $a;',array('style'=>'width:100%','readonly'=>'readonly')).'</div>'))
			->item(array(static::$lang['eval_save'],'descr'=>sprintf(static::$lang['inc_vars'],'$co,$Obj'),Eleanor::Text('eval_save',$values['eval_save'],$extra+array('style'=>'width:100%')).'<a href="#" onclick="$(this).next(\'div\').toggle();return false">'.static::$lang['op_example'].'</a><div style="display:none">'.Eleanor::Text('_','if($a[\'multilang\'])
	foreach($a[\'value\'] as &$v)
	{
		#Your code...
		#$v+=10;
	}
else
{
	#Your code...
	#$a[\'value\']+=10;
}
return $a[\'value\'];',array('style'=>'width:100%','readonly'=>'readonly')).'</div>'))
			->end();

		$c=(string)$Lst->form()
			->tabs(
				array($ltpl['general'],$general),
				array(static::$lang['edit_control'],$control ? $control : null),
				array(static::$lang['evals'],$evals)
			)
			->submitline(
				$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
				.' '.($links['delete'] ? '' : Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')))
			)
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover($c,$errors).'<script type="text/javascript">//<![CDATA[
$(function(){
	$(".linetabs a").Tabs();
	$("[name=multilang]:first").click(function(){
		if(typeof Multilangs=="undefined")
			return;
		var th=$(this);
		if(th.prop("checked"))
		{
			Multilangs.opts.where=document;
			Multilangs.Click();
		}
		else
		{
			Multilangs.opts.where=$("#tab0");//.add($("#tab2 tr.temp").slice(1));
			Multilangs.opts.Switch(["'.Language::$main.'"],['.join(',',$langs).'],$("#edit-control-preview").add($("#tab2 tr.temp").slice()));
		}
	}).triggerHandler("click");
});//]]></script>';
	}
}
TplSettings::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/settings-*.php',false);