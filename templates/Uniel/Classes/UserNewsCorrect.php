<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон по умолчанию для пользователей модуля "Новости". Редактирование новостей.
*/
class TplUserNewsCorrect
{
	public static
		$lang,
		$tpl=array();
	/*
		Внутенний метод. Важный момент Cron
	*/
	protected static function TopMenu($tit=false)
	{
		$GLOBALS['jscripts'][]=Eleanor::$Template->default['theme'].'js/publications.js';
		#Cron
		if(isset(Eleanor::$services['cron']))
		{
			$task=Eleanor::$Cache->Get($GLOBALS['Eleanor']->module['config']['n'].'_nextrun');
			$t=time();
			$task=$task===false && $task<=$t ? '<img src="'.Eleanor::$services['cron']['file'].'?'.Url::Query(array('module'=>$GLOBALS['Eleanor']->module['name'],'language'=>Language::$main==LANGUAGE ? false : Language::$main,'rand'=>$t)).'" style="width:1px;height1px;" />' : '';
		}
		else
			$task='';
		#[E] Cron

		if(isset($GLOBALS['Eleanor']->module['general']))
			return$task;
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];
		return Eleanor::$Template->Menu(array(
			'menu'=>array(
				array($links['base'],static::$lang['all']),
				$links['categories'] ? array($links['categories'],$lang['categs']) : false,
				$links['tags'] ? array($links['tags'],$lang['tags']) : false,
				array($links['search'],$lang['search'],'extra'=>array('rel'=>'search')),
				$links['add'] ? array($links['add'],static::$lang['add']) : false,
				$links['my'] ? array($links['my'],$lang['my']) : false,
			),
			'title'=>($tit ? $tit : $lang['n']).$task,
		));
	}

	/*
		Страница добавления/редактирования новости
		$id идентификатор редактируемой новости, если $id==0 значит новость добавляется
		$values массив значений полей
			Общие ключи:
			author - имя автора новости (только для гостя, либо не существует)
			status - статус активности новости (только для пользователя, либо не существует): 0 - не активна, 1 - активна
			cats - массив категорий
			enddate - завершение показов новости
			show_detail - флаг включения показа ссылки "подробнее" при отсутствии подробностей новости
			show_sokr - флаг включения отображения показа сокращенной новости при просмотре подробной

			Языковые ключи:
			title - заголовок новости
			announcement - анонс новости
			text - текст новости
			uri - URI новости

			Особые языковые ключи:
			tags - теги новости

			Специальные ключи:
			_onelang - флаг моноязычной новости при включенной мультиязычности
			_maincat - идентификатор основной категории новости
		$errors - массив ошибок
		$uploader - интерфейс загрузчика
		$voting - интерфейс опросника
		$bypost - признак того, что данные нужно брать из POST запроса
		$hasdraft - признак наличия черновика
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
			nodraft - ссылка на правку/добавление категории без использования черновика или false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
		$captcha - captcha при создании новости
	*/
	public static function AddEdit($id,$values,$errors,$uploader,$voting,$bypost,$hasdraft,$back,$links,$captcha)
	{
		$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',$GLOBALS['Eleanor']->Editor->imgalt=Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1,'id'=>'title-'.$k));
				$ml['announcement'][$k]=$GLOBALS['Eleanor']->Editor->Area('announcement['.$k.']',Eleanor::FilterLangValues($values['announcement'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>6,'rows'=>10)));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>7,'rows'=>15)));
				$ml['uri'][$k]=Eleanor::Input('uri['.$k.']',Eleanor::FilterLangValues($values['uri'],$k),array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title-'.$k.'\').val())','tabindex'=>2));

				$ml['tags'][$k]=Eleanor::Input('tags['.$k.']',Eleanor::FilterLangValues($values['tags'],$k),array('tabindex'=>5));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Input('title',$GLOBALS['Eleanor']->Editor->imgalt=$values['title'],array('id'=>'title','tabindex'=>1)),
				'announcement'=>$GLOBALS['Eleanor']->Editor->Area('announcement',$values['announcement'],array('bypost'=>$bypost,'no'=>array('tabindex'=>6,'rows'=>10))),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>7,'rows'=>15))),
				'uri'=>Eleanor::Input('uri',$values['uri'],array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title\').val())','tabindex'=>2)),

				'tags'=>Eleanor::Input('tags',Eleanor::FilterLangValues($values['tags']),array('tabindex'=>5)),
			);

		$Lst=Eleanor::LoadListTemplate('table-form')->begin();

		if(isset($values['author']))
			$Lst->item(static::$lang['author'],Eleanor::Input('author',$values['author']));
		$Lst->item(static::$lang['title'],Eleanor::$Template->LangEdit($ml['title'],null))
			->item('URI',Eleanor::$Template->LangEdit($ml['uri'],null));
		if($GLOBALS['Eleanor']->Categories->dump)
		{
			$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
			$Lst->item($lang['categs'],Eleanor::Items('cats',$GLOBALS['Eleanor']->Categories->GetOptions($values['cats']),array('id'=>'cs','tabindex'=>3,'size'=>10)))
				->item(static::$lang['maincat'],Eleanor::Select('_maincat',$GLOBALS['Eleanor']->Categories->GetOptions($values['_maincat']),array('id'=>'mc','tabindex'=>4)));
		}
		$Lst->item(array(static::$lang['tags'],Eleanor::$Template->LangEdit($ml['tags'],null),'descr'=>static::$lang['ftags_']))
			->item(array(static::$lang['announcement'],Eleanor::$Template->LangEdit($ml['announcement'],null),'descr'=>static::$lang['announcement_']))
			->item(static::$lang['text'],Eleanor::$Template->LangEdit($ml['text'],null))
			->item(array(static::$lang['show_sokr'],Eleanor::Check('show_sokr',$values['show_sokr'],array('tabindex'=>8)),'descr'=>static::$lang['show_sokr_']))
			->item(array(static::$lang['show_detail'],Eleanor::Check('show_detail',$values['show_detail'],array('tabindex'=>9)),'descr'=>static::$lang['show_detail_']))
			->item(array(static::$lang['enddate'],Dates::Calendar('enddate',$values['enddate'],true,array('tabindex'=>10)),'descr'=>static::$lang['enddate_']));
		if(Eleanor::$vars['multilang'])
			$Lst->item(static::$lang['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,11));
		if(isset($values['status']))
			$Lst->item(static::$lang['status'],Eleanor::Select('status',Eleanor::Option(static::$lang['blocked'],0,$values['status']==0).Eleanor::Option(static::$lang['active'],1,$values['status']==1),array('tabindex'=>12)));
		if($captcha)
			$Lst->item(array(static::$lang['captcha'],$captcha.'<br />'.Eleanor::Input('check','',array('tabindex'=>13)),'descr'=>static::$lang['captcha_']));
		$general=(string)$Lst->end();

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];

		return static::TopMenu(reset($GLOBALS['title']))
			.($errors ? Eleanor::$Template->Message($errors,'error') : '')
			.$Lst->form()
			->tabs(
				array(static::$lang['general'],$general),
				array(static::$lang['voting'],$voting)
			)
			->submitline((string)$uploader)
			->submitline(
				$back.Eleanor::Button('OK','submit',array('tabindex'=>14))
				.($id ? ' '.Eleanor::Button(Eleanor::$Language['tpl']['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
				.($links['draft'] ? Eleanor::Input('_draft',$id,array('type'=>'hidden')).Eleanor::$Template->DraftButton($links['draft'],1).($hasdraft ? ' <a href="'.$links['nodraft'].'">'.static::$lang['nodraft'].'</a>' : '') : '')
			)
			->endform()
			.'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#cs").change(function(){
		var cs=this;
		$("#mc option").each(function(i){
			if($("option:eq("+i+")",cs).prop("selected"))
				$(this).prop("disabled",false);
			else
				$(this).prop({disabled:true,selected:false});
		});
	}).change();
	$("input[name^=\"tags\"]").each(function(){
		var m=$(this).prop("name").match(/tags\[([a-z]+)\]/),
			p={
				module:"'.$GLOBALS['Eleanor']->module['name'].'",
				event:"tags",
				lang:(m && !$("input[name=\"_onelang\"]").prop("checked")) ? m[1] : ""
			},
			a=$(this).autocomplete({
				serviceUrl:CORE.ajax_file,
				minChars:2,
				delimiter:/,\s*/,
				params:p
			});
		$("input[name=\"_onelang\"]").change(function(){
			p.lang=(m && !$(this).prop("checked")) ? m[1] : "";
			a.setOptions({params:p})
		});
	});
})//]]></script>';
	}

	/*
		Страница с информацией об успешном создании/сохранении новости
		$back - URL возврата
		$url - ссылка на отредактированную или сохраненную новость
		$edited - флаг редактирования, если false - новость создавалась
		$status - флаг активированности новости
		$title - название новости
		$mod - флаг того, что сохранненная/созданная новость подлежит модерации
	*/
	public static function AddEditComplete($back,$url,$edited,$status,$title,$mod)
	{
		if($status)
		{
			if($mod)
				$text=$edited ? static::$lang['modedit'] : static::$lang['modadd'];
			else
				$text=$edited ? static::$lang['nomodedit'] : static::$lang['nomodadd'];
		}
		else
			$text=$edited ? static::$lang['statusedit'] : static::$lang['statusadd'];
		return static::TopMenu(reset($GLOBALS['title']))->Message(sprintf($text,$title).'<br /><br />'.sprintf(static::$lang['goto'],'<a href="'.$url.'">'.$title.'</a>'.($back ? ' | <a href="'.$back.'">'.static::$lang['pagewg'].'</a>' : '')),'info');
	}

	/*
		Страница удаления новости
		$a - массив с данными удаляемой новости. Ключи:
			title - название новости
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		return static::TopMenu()->Confirm(sprintf(static::$lang['submit_del'],$a['title']),$back);
	}

	/*
		Страница завершения удаления новости
		$a - массив с данными удаленной новости. Ключи:
			title - название новости
		$back - URL возврата
	*/
	public static function DelSuccess($a,$back)
	{
		$u=$back ? $back : $GLOBALS['Eleanor']->Url->Prefix();
		return static::TopMenu(reset($GLOBALS['title']))
			->RedirectScreen($u,30)
			->Message(sprintf(static::$lang['deleting'],$a['title']).($back ? '<br />'.sprintf(static::$lang['goto'],'<a href="'.$back.'">'.static::$lang['pagewg'].'</a>') : ''),'info');
	}
}
TplUserNewsCorrect::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/news-*.php',false);