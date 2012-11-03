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
{	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['blocks'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['main'],$lang['bpos'],'act'=>$act=='main'),
			array($links['ids'],$lang['ipages'],'act'=>$act=='ids',
				'submenu'=>array(
					array($links['addi'],$lang['addi'],'act'=>$act=='addi'),
				),
			),
			array($links['list'],$lang['lab'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['addb'],'act'=>$act=='add'),
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
		$lang=Eleanor::$Language['blocks'];
		$c='';
		if($items)
		{			$ltpl=Eleanor::$Language['tpl'];
			$Lst=Eleanor::LoadListTemplate('table-list',3);
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $service=>&$v)
			{
				$c.=($c ? '<br /><br />' : '')
					.'<h3 style="margin:5px">'.sprintf($lang['idser'],'<a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=services&amp;edit='.$service.'">'.ucfirst($service).'</a>').'</h3>';

				$Lst->begin($ltpl['name'],$lang['bitcode'],array($ltpl['functs'],60));
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
		return Eleanor::$Template->Cover($c ? $c : Eleanor::$Template->Message($lang['pisnf'],'info'));
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
			places - массив мест, формат: id=>array(), ключи:
				title - название места
				info - данные о положении места в визуальном редакторе положение блоков
			addon - данные о внешнем виде визуального редактора положений блоков
		$errors - массив ошибок
		$hasdraft - флаг наличия черновика
		$saved - флаг сохраненности результата
		$links - массив ссылок, ключи:
			del_group - ссылка на удаление текущей группы, либо false
			nodraft - ссылка на изменение положений в визульном редакторе без использования черновика, либо false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function BlocksGroup($gid,$blocks,$ids,$group,$errors,$hasdraft,$saved,$links)
	{		static::Menu('main');
		$GLOBALS['jscripts'][]='js/admin_blocks.js';
		$lang=Eleanor::$Language['blocks'];
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
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']');
		}
		else
			$ml=array(
				'title'=>Eleanor::Edit('title'),
			);

		foreach($group['places'] as $k=>&$v)
		{			$plblocks=$langs='';
			if(isset($group['blocks'][$k]))
				foreach($group['blocks'][$k] as &$bv)
					if(isset($blocks[$bv]))
						$plblocks.='<div class="block"><span class="caption">'.$blocks[$bv]['title'].'</span><div class="buttons">
								<a href="#" title="'.$lang['delb'].'" class="deleteblock"><img src="'.$images.'delete.png" /></a>
								<a href="#" title="'.$lang['edb'].'" class="editblock"><img src="'.$images.'edit.png" /></a>
							</div>'.Eleanor::Control('block['.$k.'][]','hidden',$bv).'</div>';

			if(Eleanor::$vars['multilang'])
				foreach(Eleanor::$langs as $l=>&$lv)
					$langs.=Eleanor::Control('place['.$k.']['.$l.']','hidden',isset($v['title']) ? Eleanor::FilterLangValues($v['title'],$l) : '');
			else
				$langs.=Eleanor::Control('place['.$k.']','hidden',isset($v['title']) ? Eleanor::FilterLangValues($v['title']) : '');
			$places.='<div class="place">
				<div class="title"><span class="caption"></span><span class="buttons"><a href="#" title="'.$lang['delp'].'" class="deleteplace"><img src="'.$images.'delete.png" /></a> <a href="#" title="'.$lang['edp'].'" class="editplace"><img src="'.$images.'edit.png" /></a></span></div>
				<div class="bcontainer">'.$plblocks.'</div>
				<div class="resize"></div>'.$langs
				.Eleanor::Control('placeinfo['.$k.']','hidden',isset($v['info']) ? $v['info'] : '').'</div>';		}

		$group['addon']+=array('verhor'=>'');

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		return Eleanor::$Template->Cover(
	$Lst->begin(array('id'=>'aep'))
		->head('')
		->item($lang['name'],Eleanor::Edit('name'))
		->item($ltpl['name'],Eleanor::$Template->LangEdit($ml['title'],null))
		->button(Eleanor::Button('Ok','button').' '.Eleanor::Button($lang['cancel'],'button'))
		->end()
	.($saved ? '<div id="saved">'.Eleanor::$Template->Message($lang['gsaved'],'info').'</div><script type="text/javascript">/*<![CDATA[*/$(function(){ setTimeout(function(){ $("#saved").fadeOut("slow").remove() },10000) });//]]></script>' : '')
	.'<div class="blocks"><form method="post">
		<div style="padding:5px">'.$lang['curg'].Eleanor::Control('similar','hidden','').Eleanor::Select('group',$gopts).'<div style="float:right;text-align:right;"><a href="#" id="addp" style="font-weight:bold">'.$lang['addp'].'</a>'.($links['del_group'] ? ' | <a href="'.$links['del_group'].'" onclicl="return confirm(\''.$lang['aysdg'].'\')">'.$lang['delg'].'</a>' : '' ).'</div></div>
		<div class="all"><div class="available">
				<b>'.$lang['avg'].'</b>'
				.($avblocks ? '<ul>'.$avblocks.'</ul>' : '')
				.'</div><div class="ver"></div><div class="site-c"><!-- Внимание! Между этими дивами нельзя ставить пробелы! -->
				<div class="site">'.$places.'</div>
			</div>
		</div>
		<div class="hor"></div>
		<div class="submitline">'.Eleanor::Control('addon[verhor]','hidden',$group['addon']['verhor'],array('id'=>'verhor'))
		.Eleanor::Button($lang['save'])
		.Eleanor::Control('_draft','hidden','g'.$gid)
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
		distribblock:$("<div>").addClass("block").html("<span class=\"caption\"></span><div class=\"buttons\"><a href=\"#\" title=\"'.$lang['delb'].'\" class=\"deleteblock\"><img src=\"'.$images.'delete.png\" /></a><a href=\"#\" title=\"'.$lang['edb'].'\" class=\"editblock\"><img src=\"'.$images.'edit.png\" /></a></div><input type=\"hidden\"/>"),
		distribplace:$("<div>").addClass("place").html("<div class=\"title\"><span class=\"caption\"></span><span class=\"buttons\"><a href=\"#\" title=\"'.$lang['delp'].'\" class=\"deleteplace\"><img src=\"'.$images.'delete.png\" /></a> <a href=\"#\" title=\"'.$lang['edp'].'\" class=\"editplace\"><img src=\"'.$images.'edit.png\" /></a></span></div><div class=\"bcontainer\"></div><div class=\"resize\"></div><input type=\"hidden\" name=\"place[][english]\" /><input type=\"hidden\" name=\"place[][russian]\" /><input type=\"hidden\" name=\"place[][ukrainian]\" /><input type=\"hidden\" />"),
		Recount:function(a){
			$(".blocks .available li b").text("");
			$.each(a,function(k,v){
				$("#bl-"+k+" b").text(v);
			});
		},
		EditPlace:function(name,title,Save){
			$("#aep :button").off("click");
			$(".blocks").hide();
			var aep=$("#aep").show()
				.on("keypress",function(e){					if(e.keyCode==13)
						$("input[type=button]:first",this).click();				})
				.find("tr:first td").text("'.$lang['editingp'].'").end()
				.find("input[name=name]").val(name).end()
				.find("input[type=button]:first").click(function(){
					var form=$(this).closest("table"),
						title='.(Eleanor::$vars['multilang'] ? '[];

					form.find("input[name^=\"title[\"]").each(function(){
						var l=$(this).prop("name").match(/\[([^\]]+)\]$/)[1];
						title[l]=$(this).val();
					});' : 'form.find("input[name=title]").val();').'

					Save(form.find("input[name=name]").val(),title);
					Change.fire();
					$(this).next().click();
				}).next().click(function(){
					$("#aep, .blocks").toggle();
				}).end().end();

			if(typeof title=="string")
				aep.find("input[name=title]").val(title);
			else
				aep.find("input[name^=\"title[\"]").each(function(){
					var l=$(this).prop("name").match(/\[([^\]]+)\]$/)[1];
					$(this).val(title[l]);
				})
		},
		Change:Change
	}).on("click",".deleteplace",function(){
		var pl=$(this).closest(".place");
		if(confirm("'.$lang['jsdelp'].'".replace("{0}",pl.find(".caption:first").text())))
			pl.trigger("delete");
		return false;
	}).on("click",".deleteblock",function(){
		var bl=$(this).closest(".block");
		if(confirm("'.$lang['jsdelb'].'".replace("{0}",bl.find(".caption:first").text())))
			bl.trigger("delete");
		return false;
	}).on("click",".editplace",function(){
		$(this).closest(".place").trigger("edit");
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

	$("#addp").click(function(){
		$("#aep :button").off("click");
		$(".blocks").hide();
		$("#aep").show()
		.find("tr:first td").text("'.$lang['addingp'].'").end()
		.find("input[type=text]").val("").end()
		.find("input[type=button]:first").click(function(){
			var form=$(this).closest("table"),
				title='.(Eleanor::$vars['multilang'] ? '[];

			form.find("input[name^=\"title[\"]").each(function(){
				var l=$(this).prop("name").match(/\[([^\]]+)\]$/)[1];
				title[l]=$(this).val();
			});' : 'form.find("input[name=title]").val();').'

			$(".blocks .site").trigger("add",[form.find("input[name=name]").val(),title]);
			$(this).next().click();
		}).next().click(function(){
			$("#aep, .blocks").toggle();
		});
		return false;
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
			var sim=!$(this).find(":selected").hasClass("exists") && confirm("'.$lang['crgonb'].'"),
				save=changed && confirm("'.$lang['jssavech'].'");

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
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$groups,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');
		array_push($GLOBALS['jscripts'],'js/checkboxes.js','js/jquery.poshytip.js','js/admin_blocks.js');
		$lang=Eleanor::$Language['blocks'];
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
				$lang['tpl'],
				array($lang['groups'],150),
				array($lang['ab'],100,'sort'=>$qs['sort']=='showfrom' ? $qs['so'] : false,'href'=>$links['sort_showfrom']),
				array($lang['af'],100,'sort'=>$qs['sort']=='showto' ? $qs['so'] : false,'href'=>$links['sort_showto']),
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
					'<div class="fieldedit" id="it'.$k.'" data-id="'.$k.'"><a href="'.$v['_aedit'].'">'.$v['title'].'</a></div><i class="small" title="'.($v['ctype']=='file' ? $v['file'].'">'.basename($v['file']) : str_replace('"','&quot;',strip_tags($v['text'],'<b><i><span><br><code><pre>')).'">'.$lang['text']).'</i>',
					$v['template'] ? $v['template'] : '<i>'.$lang['bypos'].'</i>',
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
			$Lst->empty($lang['bnf']);

		$statuses=Eleanor::Option($ltpl['no'],'-');
		$temp=array(
			1=>$lang['stac'],
			0=>$lang['stbl'],
			-3=>$lang['stbe'],
			-2=>$lang['stfi'],
		);
		foreach($temp as $k=>&$v)
			$statuses.=Eleanor::Option($v,$k,$qs['']['fi']['status']!==false and $qs['']['fi']['status']==$k);

		return Eleanor::$Template->Cover('<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Edit('fi[title]',$qs['']['fi']['title']).'</td>
					<td><b>'.$lang['status'].'</b><br />'.Eleanor::Select('fi[status]',$statuses).'</td>
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
		.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['bpp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['activate'],'a').Eleanor::Option($ltpl['deactivate'],'d').Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
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

		$lang=Eleanor::$Language['blocks'];
		$ltpl=Eleanor::$Language['tpl'];
		if(Eleanor::$vars['multilang'])
		{
			$mchecks=$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$mchecks[$k]=!$id || !empty($values['title'][$k]) || !empty($values['text'][$k]);
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']',$GLOBALS['Eleanor']->Editor->imgalt=Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1,'id'=>'title-'.$k));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>4)));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Edit('title',$GLOBALS['Eleanor']->Editor->imgalt=$values['title'],array('tabindex'=>1,'id'=>'title')),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>4))),
			);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin(array('id'=>'block'))
			->item($ltpl['title'],Eleanor::$Template->LangEdit($ml['title'],null))
			->item($lang['source'],Eleanor::Select('ctype',Eleanor::Option($lang['text'],'text',$values['ctype']=='text').Eleanor::Option($lang['file'],'file',$values['ctype']=='file'),array('tabindex'=>2)))
			->item(array($lang['fileb'],Eleanor::Edit('file',$values['file'],array('tabindex'=>3)),'tr'=>array('class'=>'trfile')))
			->item(array($lang['tfile'],Eleanor::Check('textfile',$values['textfile'],array('tabindex'=>4)),'tr'=>array('class'=>'trfile preconf')))
			->item(array($lang['text'],Eleanor::$Template->LangEdit($ml['text'],null),'tr'=>array('class'=>'trtext')));

		if($values['_config'])
			foreach($values['_config'] as $k=>&$v)
				if(is_array($v))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values['config'][$k],null),'tip'=>isset($v['descr']) ? $v['descr'] : '','tr'=>array('class'=>'trfile trconf')));
				else
					$Lst->head(array($v,'tr'=>array('class'=>'trfile trconf infolabel first')));

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$mchecks,null,5));

		$cont=(string)$Lst->end();

		$view=(string)$Lst->begin()
			->item(array($lang['afg'],Eleanor::Items('user_groups',UserManager::GroupsOpts($values['user_groups']),10,array('tabindex'=>6)),'tip'=>$lang['afg_']))
			->item($lang['ab'],Dates::Calendar('showfrom',$values['showfrom'],true,array('tabindex'=>7)))
			->item($lang['af'],Dates::Calendar('showto',$values['showto'],true,array('tabindex'=>8)))
			->item($lang['nt'],Eleanor::Check('notemplate',$values['notemplate'],array('tabindex'=>9)))
			->item(array($lang['tfb'],Eleanor::Edit('template',$values['template'],array('tabindex'=>10)),'tip'=>$lang['tfb_']))
			->item($ltpl['activate'],Eleanor::Check('status',$values['status']==1,array('value'=>1,'tabindex'=>11)))
			->end();

		$LLst=Eleanor::LoadListTemplate('table-list',3)
			->begin(
				array($lang['vn'],150,'tableaddon'=>array('id'=>'vars')),
				array($lang['val']),
				array($ltpl['functs'],65)
			);
		if($values['vars'])
			foreach($values['vars'] as $k=>&$v)
				$LLst->item(
					array(Eleanor::Edit('vn[]',$k,array('style'=>'width:100%')),'traddon'=>array('style'=>'vertical-align:top')),
					Eleanor::Text('vv[]',(string)$v,array('rows'=>2,'style'=>'width:100%')),
					array(Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus'),2),'center')
				);
		else
			$LLst->item(
				array(Eleanor::Edit('vn[]','',array('style'=>'width:100%')),'traddon'=>array('style'=>'vertical-align:top')),
				Eleanor::Text('vv[]','',array('rows'=>2,'style'=>'width:100%')),
				array(Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus'),2),'center')
			);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$c=$Lst->form()
			->tabs(
				array($lang['content'],$cont),
				array($lang['view'],$view),
				array($lang['av'],$LLst->end())
			)
			->submitline((string)$uploader)
			->submitline(
				$back
				.Eleanor::Button('Ok','submit',array('tabindex'=>20))
				.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
				.Eleanor::Control('_draft','hidden','b'.$id)
				.Eleanor::$Template->DraftButton($links['draft'],1)
				.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
			)
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
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
		$lang=Eleanor::$Language['blocks'];
		$ltpl=Eleanor::$Language['tpl'];
		if(Eleanor::$vars['multilang'])
		{
			$title=array();
			foreach(Eleanor::$langs as $k=>&$v)
				$title[$k]=Eleanor::Edit('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
		}
		else
			$title=Eleanor::Edit('title',Eleanor::FilterLangValues($values['title']),array('tabindex'=>1,'id'=>'title'));

		$ss='';
		foreach(Eleanor::$services as $k=>&$v)
			$ss.=Eleanor::Option($k,false,$values['service']==$k);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item($ltpl['name'],Eleanor::$Template->LangEdit($title,null))
			->item($lang['ser'],Eleanor::Select('service',$ss))
			->item(array($lang['codei'],$GLOBALS['Eleanor']->Editor->Area('code',$values['code'],array('bypost'=>$bypost,'codemirror'=>array('type'=>'purephp'))),'descr'=>$lang['codei_']))
			->end()
			->submitline(
				$back
				.Eleanor::Button('Ok','submit',array('tabindex'=>20))
				.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
				.Eleanor::Control('_draft','hidden','i'.$id)
				.Eleanor::$Template->DraftButton($links['draft'],1)
				.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
			)
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
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
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['blocks']['aysdi'],$a['title']),$back));
	}

	/*
		Страница удаления блока
		$a - массив удаляемого блока, ключи:
			title - название блока
		$back - URL возврата
	*/
	public static function Delete($t,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['blocks']['aysdb'],$a['title']),$back));
	}

	/*
		Страница критической ошибки
		$e - текст ошибки ошибка
	*/
	public static function FatalError($e)
	{		static::Menu('');
		return Eleanor::$Template->Cover('',$e);;	}
/*
<	влево		&#9668;
^	вверх		&#9650;
>	вправо		&#9658;
Ў	вниз		&#9660;
*/
}