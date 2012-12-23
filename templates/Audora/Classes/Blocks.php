<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class TPLBlocks
{	public static
		$lang;	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['blocks'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['main'],$lang['bpos'],'act'=>$act=='main'),
			array($links['ids'],$lang['ipages'],'act'=>$act=='ids',
				'submenu'=>array(
					array($links['addi'],static::$lang['addi'],'act'=>$act=='addi'),
				),
			),
			array($links['list'],$lang['lab'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['addb'],'act'=>$act=='add'),
				),
			),
		);
	}

	/*
		Страница идентификаторов блоков
		$items - массив идентификаторов, формат: сервис=>массив идентификаторов формата id=>array(). Ключи внутреннего массива:
			title - название идентификатора
			code - код идентификатора
			_aedit - ссылка на редактирование идентификатора
			_adel - ссылка на удаление идентификатора
	*/
	public static function BlocksIdsList($items)
	{		static::Menu('ids');
		$c='';
		if($items)
		{			$ltpl=Eleanor::$Language['tpl'];
			$Lst=Eleanor::LoadListTemplate('table-list',3);
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $service=>&$v)
			{
				$c.=($c ? '<br /><br />' : '')
					.'<h3 style="margin:5px">'.sprintf(static::$lang['idser'],'<a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=services&amp;edit='.$service.'">'.ucfirst($service).'</a>').'</h3>';

				$Lst->begin($ltpl['name'],static::$lang['bitcode'],array($ltpl['functs'],60));
				foreach($v as $id=>&$vv)
				{
					$n=$p=0;
					while($n++<2 and isset($vv['code'][$p++]) and false!==$p=strpos($vv['code'],"\n",$p));
					$Lst->item(
						'<a href="'.$vv['_aedit'].'">'.$vv['title'].'</a>',
						'<code><pre>'.htmlspecialchars(Strings::CutStr($vv['code'],$p && $p<250 ? $p-1 : 250),ELENT,CHARSET).'</pre></code>',
						$Lst('func',
							array($vv['_aedit'],$ltpl['edit'],$images.'edit.png'),
							array($vv['_adel'],$ltpl['delete'],$images.'delete.png')
						)
					);
				}
				$c.=$Lst->end();
			}
		}
		return Eleanor::$Template->Cover($c ? $c : Eleanor::$Template->Message(static::$lang['pisnf'],'info'));
	}

	/*
		Основная страница: управление блоками группы
		$gid - идентификатор группы. Целое число для дополнительных групп, имя сервиса - для стандартных групп сервисов
		$blocks - массив всех блоков. Формат: id=>array(), ключи внутреннего массива:
			title - название блока
			_aedit - ссылка на редактирование блока
			_adel - ссылка на удаление блока
		$ids - массив всех идентификаторов, формат: сервис=>id идентификатора=>array(), ключи:
			t - название идентификатора
			g - группа идентификатора (0, если идентификатору группа не присвоена)
		$group - массив текущей группы, ключи:
			blocks - массив блоков, формат: id места=>array(), где каждый элемент - идентификатор блока
			places - массив мест, формат: id=>данные о положении места в визуальном редакторе положение блоков, ключи:
			extra - данные о внешнем виде визуального редактора положений блоков
		$tpls - массив доступных тем оформления для данной группы блоков (зависит от сервиса), формат название темы=>массив, ключи
			a - ссылка на группу блоков для этой темы либо false (текущая группа)
			title - название
		$errors - массив ошибок
		$hasdraft - флаг наличия черновика
		$saved - флаг сохраненности результата
		$links - массив ссылок, ключи:
			del_group - ссылка на удаление текущей группы, либо false
			nodraft - ссылка на изменение положений в визульном редакторе без использования черновика, либо false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function BlocksGroup($gid,$blocks,$ids,$group,$tpls,$errors,$hasdraft,$saved,$links)
	{		static::Menu('main');
		$GLOBALS['jscripts'][]='js/admin_blocks.js';
		$ltpl=Eleanor::$Language['tpl'];

		$images=Eleanor::$Template->default['theme'].'images/';
		$Lst=Eleanor::LoadListTemplate('table-form',3);
		$avblocks=$gopts=$places='';
		foreach($ids as $k=>&$v)
		{			$gr='';
			foreach($v as $kk=>&$vv)
				$gr.=Eleanor::Option($vv['t'],$kk,$kk==$gid,$vv['g'] ? array('class'=>'exists') : array());
			$gopts.=Eleanor::OptGroup($k,$gr);		}
		foreach($blocks as $k=>&$v)
			$avblocks.='<li id="bl-'.$k.'"><b></b><span>'.$v['title'].'</span><i><a href="'.$v['_aedit'].'"><img src="'.$images.'edit.png" /></a> <a href="'.$v['_adel'].'"><img src="'.$images.'delete.png" /></a></i></li>';

		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
				$ml['title'][$k]=Eleanor::Input('title['.$k.']');
		}
		else
			$ml=array(
				'title'=>Eleanor::Input('title'),
			);

		foreach($group['places'] as $k=>&$v)
		{			$plblocks='';
			if(isset($group['blocks'][$k]))
				foreach($group['blocks'][$k] as &$bv)
					if(isset($blocks[$bv]))
						$plblocks.='<div class="block"><span class="caption">'.$blocks[$bv]['title'].'</span><div class="buttons">
								<a href="#" title="'.static::$lang['delb'].'" class="deleteblock"><img src="'.$images.'delete.png" /></a>
								<a href="#" title="'.static::$lang['edb'].'" class="editblock"><img src="'.$images.'edit.png" /></a>
							</div>'.Eleanor::Input('block['.$k.'][]',$bv,array('type'=>'hidden')).'</div>';

			$places.='<div class="place">
				<div class="title">'.$v['title'].'</div>
				<div class="bcontainer">'.$plblocks.'</div>
				<div class="resize"></div>'
				.Eleanor::Input('place['.$k.']',isset($v['extra']) ? $v['extra'] : '',array('type'=>'hidden')).'</div>';		}

		$themes=' ';
		foreach($tpls as &$v)
			$themes.=$v['a'] ? '<a href="'.$v['a'].'">'.$v['title'].'</a> | ' : $v['title'].' | ';

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover(
		($saved ? '<div id="saved">'.Eleanor::$Template->Message(static::$lang['gsaved'],'info').'</div><script type="text/javascript">/*<![CDATA[*/$(function(){ setTimeout(function(){ $("#saved").fadeOut("slow").remove() },10000) });//]]></script>' : '')
		.'<div class="blocks"><form method="post">
		<div style="padding:5px">'.static::$lang['curg'].Eleanor::Input('similar','',array('type'=>'hidden')).Eleanor::Select('group',$gopts).rtrim($themes,'| ').($links['del_group'] ? '<div style="float:right;text-align:right;"><a href="'.$links['del_group'].'" onclicl="return confirm(\''.static::$lang['aysdg'].'\')">'.static::$lang['delg'].'</a></div>' : '').'</div>
		<div class="all" style="height:495px"><div class="available">
				<b>'.static::$lang['avg'].'</b>'
				.($avblocks ? '<ul>'.$avblocks.'</ul>' : '')
				.'</div><div class="ver"></div><div class="site-c"><!-- Внимание! Между этими дивами нельзя ставить пробелы! -->
				<div class="site">'.$places.'</div>
			</div>
		</div>
		<div class="hor"></div>
		<div class="submitline">'.Eleanor::Input('extra',$group['extra'],array('id'=>'verhor','type'=>'hidden'))
		.Eleanor::Button(static::$lang['save'])
		.Eleanor::Input('_draft','g'.$gid,array('type'=>'hidden'))
		.Eleanor::$Template->DraftButton($links['draft'],1)
		.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		.'</div>
	</form></div>',$errors,'error')
	.'<script type="text/javascript">//<![CDATA[
$(window).load(function(){
	var Change=$.Callbacks("unique"),
		changed=false;

	Change.add(function(){
		changed=true;
	});

	BlocksMain(Change);

	$(".blocks .site").EleanorVisualBlocks({		dragimg:"'.Eleanor::$Template->default['theme'].'images/catmanag.png",
		distribblock:$("<div>").addClass("block").html("<span class=\"caption\"></span><div class=\"buttons\"><a href=\"#\" title=\"'.static::$lang['delb'].'\" class=\"deleteblock\"><img src=\"'.$images.'delete.png\" /></a><a href=\"#\" title=\"'.static::$lang['edb'].'\" class=\"editblock\"><img src=\"'.$images.'edit.png\" /></a></div><input type=\"hidden\"/>"),
		Recount:function(a){
			$(".blocks .available li b").text("");
			$.each(a,function(k,v){
				$("#bl-"+k+" b").text(v);
			});
		},
		Change:Change
	}).on("click",".deleteblock",function(){
		var bl=$(this).closest(".block");
		if(confirm("'.static::$lang['jsdelb'].'".replace("{0}",bl.find(".caption:first").text())))
			bl.trigger("delete");
		return false;
	})

	$(".blocks .available").on("dragstart","li",function(e){
		if($(e.target).is("a,:input"))
			return;

		e.stopPropagation();
		e.dataTransfer.effectAllowed="copy move";
		e.dataTransfer.setData("blockid",$(this).prop("id").replace("bl-",""));
		e.dataTransfer.setData("title",$("span",this).text());
		$(this).addClass("hover");
	}).on("dragend","li",function(e){
		$(this).removeClass("hover");
	});

	var drafts=typeof CORE.drafts!="undefined";
	if(drafts)
	{		var first=true,
			lnk="",
			cnt,
			After=function(){
				if(--cnt==0)
					window.location.href=lnk;
			},
			Aclick=function(){				if(first)
				{
					$.each(CORE.drafts,function(i,v){
						v.OnSave.add(After);
					});
					first=false;
				}
				cnt=CORE.drafts.length;
				lnk=$(this).prop("href");
				$.each(CORE.drafts,function(i,v){
					v.Save();
				});
				return cnt<=0;
			};
		$(".blocks .available ul").on("click","a",Aclick);
	}

	$(".blocks").on("click","a.editblock",function(){
		if($(this).prop("href").match(/#$/))
		{
			var id=$(this).closest(".block").find("input:first").val();
			$(this).prop("href",$("#bl-"+id+" a:first").prop("href"))
			if(drafts)
			{				Aclick.call(this);
				return false;			}
		}
	});

	$("select[name=group]").change(function(){
		if($(this).val()!="'.$gid.'")
		{
			var sim=!$(this).find(":selected").hasClass("exists") && confirm("'.static::$lang['crgonb'].'"),
				save=changed && confirm("'.static::$lang['jssavech'].'");

			if(save)
			{
				$("[name=similar]").val("'.$gid.'");
				$(this).closest("form").submit();
			}
			else
			{
				window.location.href="'.htmlspecialchars_decode($GLOBALS['Eleanor']->Url->Construct(array('group'=>0))).'".replace("0",$(this).val())+(sim ? "&similar='.$gid.'" : "");
				return false;
			}
		}
	})
});//]]></script>';
	}

	/*
		Страница отображения всех блоков
		$items - массив блоков. Формат: ID=>array(), ключи внутреннего массива:
			ctype - тип контента блока: text - для текстовых блоков, file - для файловых блоков
			file - путь к файлу для файлового блока
			user_groups - массив групп пользователей
			showfrom - дата начала показов
			showto - дата завершения показов
			textfile - флаг того, что файл текстовый
			template - имя шаблона
			notemplate - флаг вывода блока без оформления
			status - статус блока: -3 - блок ожидает наступления даты начала показа, -2 -  блок не отображается, поскольку наступила дата завершения показов,
				-1 - зарезервировано, 0 - блок заблокирован, 1 - блок активирован
			title - название блока
			text - текст блока для текстовых блоков
		$groups - массив групп пользователей. Формат: ID=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
		$cnt - количество блоков всего
		$pp - количество блоков на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_title - ссылка на сортировку списка $items по названию (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_showto - ссылка на сортировку списка $items по дате начала показа (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_showfrom - ссылка на сортировку списка $items по дате окончания показа (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внтури которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества блоков отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$groups,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');
		array_push($GLOBALS['jscripts'],'js/checkboxes.js','js/jquery.poshytip.js','js/admin_blocks.js');
		$ltpl=Eleanor::$Language['tpl'];

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'status'=>false,
			'title'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',7)
			->begin(
				array($ltpl['title'],'sort'=>$qs['sort']=='title' ? $qs['so'] : false,'href'=>$links['sort_title']),
				static::$lang['tpl'],
				array(static::$lang['groups'],150),
				array(static::$lang['ab'],100,'sort'=>$qs['sort']=='showfrom' ? $qs['so'] : false,'href'=>$links['sort_showfrom']),
				array(static::$lang['af'],100,'sort'=>$qs['sort']=='showto' ? $qs['so'] : false,'href'=>$links['sort_showto']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
			{
				$grs='';
				foreach($v['user_groups'] as &$gv)
					if(isset($groups[$gv]))
						$grs.='<a href="'.$groups[$gv]['_href'].'">'.$groups[$gv]['html_pref'].$groups[$gv]['title_l'].$groups[$gv]['html_end'].'</a>, ';
				$Lst->item(
					'<div class="fieldedit" id="it'.$k.'" data-id="'.$k.'"><a href="'.$v['_aedit'].'">'.$v['title'].'</a></div><i class="small" title="'.($v['ctype']=='file' ? $v['file'].'">'.basename($v['file']) : str_replace('"','&quot;',strip_tags($v['text'],'<b><i><span><br><code><pre>')).'">'.static::$lang['text']).'</i>',
					$v['template'] ? $v['template'] : '<i>'.static::$lang['bypos'].'</i>',
					$grs ? rtrim($grs,', ') : '<i>'.$ltpl['all'].'</i>',
					array((int)$v['showfrom']>0 ? Eleanor::$Language->Date($v['showfrom'],'fdt') : '&infin;','center'),
					array((int)$v['showto']>0 ? Eleanor::$Language->Date($v['showto'],'fdt') : '&infin;','center'),
					$Lst('func',
						$v['_aswap'] ? array($v['_aswap'],$v['status']<=0 ? $ltpl['activate'] : $ltpl['deactivate'],$v['status']<0 ? $images.'waiting.png' : $images.($v['status']==0 ? 'inactive.png' : 'active.png')) : false,
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty(static::$lang['bnf']);

		$statuses=Eleanor::Option($ltpl['no'],'-');
		$temp=array(
			1=>static::$lang['stac'],
			0=>static::$lang['stbl'],
			-3=>static::$lang['stbe'],
			-2=>static::$lang['stfi'],
		);
		foreach($temp as $k=>&$v)
			$statuses.=Eleanor::Option($v,$k,$qs['']['fi']['status']!==false and $qs['']['fi']['status']==$k);

		return Eleanor::$Template->Cover('<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Input('fi[title]',$qs['']['fi']['title']).'</td>
					<td><b>'.static::$lang['status'].'</b><br />'.Eleanor::Select('fi[status]',$statuses).'</td>
				</tr>
				<tr>
					<td style="text-align:center;" colspan="2">'.Eleanor::Button($ltpl['apply']).'</td>
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
	BlocksList();
});//]]></script>
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
		.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['bpp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['activate'],'a').Eleanor::Option($ltpl['deactivate'],'d').Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page'])));
	}

	/*
		Страница добавления/редактирования блока
		$id - идентификатор редактируемого блока, если $id==0 значит блок добавляется
		$values - массив значений полей, ключи:
			Общие ключи:
			ctype - тип блока: file - для файлового, text - для текстового
			file - путь к файлу для файлового блока
			user_groups - массив ID групп пользователей, которым доступен блок
			showfrom - дата начала показов блока
			showto - дата завершения показа блоков
			textfile - флаг того, что файл файлового блока является обычным текстом
			template - шаблон, применяемый к блоку
			notemplate - флаг вывода блока без применения оформления
			vars - массив дополнительных переменных блока. Формат: name=>value
			status - статус блока: -3 - блок ожидает наступления даты начала показа, -2 -  блок не отображается, поскольку наступила дата завершения показов,
				-1 - зарезервировано, 0 - блок заблокирован, 1 - блок активирован
			_config - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов, либо false
			config - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls

			Языковые ключи:
			title - название блока
			text - текст блока для текстовых блоков

			Специальные ключи:
			_onelang - флаг моноязычного блока при включенной мультиязычности
		$errors - массив ошибок
		$bypost - признак того, что данные нужно брать из POST запроса
		$hasdraft - признак того, что у статической страницы есть черновик
		$uploader - интерфейс загрузчика файлов
		$back - URI возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление блока, либо false
			nodraft - ссылка на правку/добавление блока без использования черновика, либо false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function AddEdit($id,$values,$errors,$bypost,$hasdraft,$uploader,$back,$links)
	{
		static::Menu($id ? '' : 'add');
		array_push($GLOBALS['jscripts'],'addons/autocomplete/jquery.autocomplete.js','js/admin_blocks.js');
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';

		$ltpl=Eleanor::$Language['tpl'];
		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',$GLOBALS['Eleanor']->Editor->imgalt=Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1,'id'=>'title-'.$k));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>4)));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Input('title',$GLOBALS['Eleanor']->Editor->imgalt=$values['title'],array('tabindex'=>1,'id'=>'title')),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>4))),
			);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin(array('id'=>'block'))
			->item($ltpl['title'],Eleanor::$Template->LangEdit($ml['title'],null))
			->item(static::$lang['source'],Eleanor::Select('ctype',Eleanor::Option(static::$lang['text'],'text',$values['ctype']=='text').Eleanor::Option(static::$lang['file'],'file',$values['ctype']=='file'),array('tabindex'=>2)))
			->item(array(static::$lang['fileb'],Eleanor::Input('file',$values['file'],array('tabindex'=>3)),'tr'=>array('class'=>'trfile')))
			->item(array(static::$lang['tfile'],Eleanor::Check('textfile',$values['textfile'],array('tabindex'=>4)),'tr'=>array('class'=>'trfile preconf')))
			->item(array(static::$lang['text'],Eleanor::$Template->LangEdit($ml['text'],null),'tr'=>array('class'=>'trtext')));

		if($values['_config'])
			foreach($values['_config'] as $k=>&$v)
				if(is_array($v))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values['config'][$k],null),'tip'=>isset($v['descr']) ? $v['descr'] : '','tr'=>array('class'=>'trfile trconf')));
				else
					$Lst->head(array($v,'tr'=>array('class'=>'trfile trconf infolabel first')));

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,5));

		$cont=(string)$Lst->end();

		$view=(string)$Lst->begin()
			->item(array(static::$lang['afg'],Eleanor::Items('user_groups',UserManager::GroupsOpts($values['user_groups']),array('tabindex'=>6)),'tip'=>static::$lang['afg_']))
			->item(static::$lang['ab'],Dates::Calendar('showfrom',$values['showfrom'],true,array('tabindex'=>7)))
			->item(static::$lang['af'],Dates::Calendar('showto',$values['showto'],true,array('tabindex'=>8)))
			->item(static::$lang['nt'],Eleanor::Check('notemplate',$values['notemplate'],array('tabindex'=>9)))
			->item(array(static::$lang['tfb'],Eleanor::Input('template',$values['template'],array('tabindex'=>10)),'tip'=>static::$lang['tfb_']))
			->item($ltpl['activate'],Eleanor::Check('status',$values['status']==1,array('value'=>1,'tabindex'=>11)))
			->end();

		$LLst=Eleanor::LoadListTemplate('table-list',3)
			->begin(
				array(static::$lang['vn'],150,'tableextra'=>array('id'=>'vars')),
				array(static::$lang['val']),
				array($ltpl['functs'],65)
			);
		if($values['vars'])
			foreach($values['vars'] as $k=>&$v)
				$LLst->item(
					array(Eleanor::Input('vn[]',$k,array('style'=>'width:100%')),'trextra'=>array('style'=>'vertical-align:top')),
					Eleanor::Text('vv[]',(string)$v,array('rows'=>2,'style'=>'width:100%')),
					array(Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus'),2),'center')
				);
		else
			$LLst->item(
				array(Eleanor::Input('vn[]','',array('style'=>'width:100%')),'trextra'=>array('style'=>'vertical-align:top')),
				Eleanor::Text('vv[]','',array('rows'=>2,'style'=>'width:100%')),
				array(Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus'),2),'center')
			);

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$c=$Lst->form()
			->tabs(
				array(static::$lang['content'],$cont),
				array(static::$lang['view'],$view),
				array(static::$lang['av'],$LLst->end())
			)
			->submitline((string)$uploader)
			->submitline(
				$back
				.Eleanor::Button('Ok','submit',array('tabindex'=>20))
				.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
				.Eleanor::Input('_draft','b'.$id,array('type'=>'hidden'))
				.Eleanor::$Template->DraftButton($links['draft'],1)
				.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
			)
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover($c,$errors,'error').'<script type="text/javascript">/*<![CDATA[*/AddEditBlock()//]]></script>';
	}

	/*
		Страница добавления/редактирования статической страницы
		$id - идентификатор редактируемого идентификатора, если $id==0 значит идентификатор добавляется
		$values - массив значений полей, ключи:
			Общие ключи:
			ser - сервис
			code - код идентификации

			Языковые ключи:
			title - название идентификатора
		$errors - массив ошибок
		$bypost - признак того, что данные нужно брать из POST запроса
		$hasdraft - признак того, что у статической страницы есть черновик
		$back - URI возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление идентификатора, либо false
			nodraft - ссылка на правку/добавление идентификатора без использования черновика или false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function AddEditId($id,$values,$errors,$bypost,$hasdraft,$back,$links)
	{
		static::Menu($id ? '' : 'addi');
		$ltpl=Eleanor::$Language['tpl'];
		if(Eleanor::$vars['multilang'])
		{
			$title=array();
			foreach(Eleanor::$langs as $k=>&$v)
				$title[$k]=Eleanor::Input('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
		}
		else
			$title=Eleanor::Input('title',Eleanor::FilterLangValues($values['title']),array('tabindex'=>1,'id'=>'title'));

		$ss='';
		foreach(Eleanor::$services as $k=>&$v)
			$ss.=Eleanor::Option($k,false,$values['service']==$k);

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item($ltpl['name'],Eleanor::$Template->LangEdit($title,null))
			->item(static::$lang['ser'],Eleanor::Select('service',$ss))
			->item(array(static::$lang['codei'],$GLOBALS['Eleanor']->Editor->Area('code',$values['code'],array('bypost'=>$bypost,'codemirror'=>array('type'=>'purephp'))),'descr'=>static::$lang['codei_']))
			->end()
			->submitline(
				$back
				.Eleanor::Button('Ok','submit',array('tabindex'=>20))
				.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
				.Eleanor::Input('_draft','i'.$id,array('type'=>'hidden'))
				.Eleanor::$Template->DraftButton($links['draft'],1)
				.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
			)
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		Страница удаления идентификатора
		$a - массив удаляемого идентификатора, ключи:
			title - название идентификатора
		$back - URL возврата
	*/
	public static function DeleteI($a,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['aysdi'],$a['title']),$back));
	}

	/*
		Страница удаления блока
		$a - массив удаляемого блока, ключи:
			title - название блока
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['aysdb'],$a['title']),$back));
	}

	/*
		Страница критической ошибки
		$e - текст ошибки ошибка
	*/
	public static function FatalError($e)
	{		static::Menu('');
		return Eleanor::$Template->Cover('',isset(static::$lang[$e]) ? static::$lang[$e] : '');	}
}
TplBlocks::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/blocks-*.php',false);