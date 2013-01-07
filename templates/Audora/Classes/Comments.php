<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для админки управления комментариями
*/
class TPLComments
{
	public static
		$lang;

	protected static function Menu($act='')
	{
		$links=&$GLOBALS['Eleanor']->module['links'];

		$ln=static::$lang['news'];
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],Eleanor::$Language['lc']['list'],'act'=>$act=='list',
				'submenu'=>$links['news']
					? array(
						array($links['news']['link'],$ln($links['news']['cnt'])),
					)
					: false,
			),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);
	}

	/*
		Страница отображения всех комментариев
		$items - массив комментариев. Формат: ID=>array(), ключи внутреннего массива:
			module - id модуля
			contid - строка-идентификатор контентины модуля
			status - статус комментария (-1 - ожидание модерации, 0 - заблокирован, 1 - активен)
			date - дата публикации комментария
			author - имя автора комментария
			author_id - ID автора комментария
			ip - ip адресс комментария
			text - текст комментария
			_aswap - ссылка на инвертирование активности комментария
			_aedit - ссылка на редактирование комментария
			_adel - ссылка на удаление комментария
		$modules - массив модулей. Формат id=>название
		$titles - массив заголовков и ссылок на комментарии. Формат: id=>array(), ключи внутреннего массива:
			0 - заголовок контентины
			1 - ссылка на комментарий
		$cnt - количество комментариев всего
		$pp - количество комментариев на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_date - ссылка на сортировку списка $items по дате (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_author - ссылка на сортировку списка $items по автору (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_module - ссылка на сортировку списка $items по модулю (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_ip - ссылка на сортировку списка $items по ip (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества комментариев отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
		$ong - флаг отображения интерфейса на главной странице админки
	*/
	public static function CommentsList($items,$modules,$titles,$cnt,$pp,$qs,$page,$links,$ong)
	{
		if(!$ong)
			static::Menu('list');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',$ong ? 6 : 7)
			->begin(
				array(static::$lang['date'],70,'sort'=>$qs['sort']=='date' ? $qs['so'] : false,'href'=>$links['sort_date']),
				array(static::$lang['author'],70,'sort'=>$qs['sort']=='author' ? $qs['so'] : false,'href'=>$links['sort_author']),
				array(static::$lang['published'],'sort'=>$qs['sort']=='module' ? $qs['so'] : false,'href'=>$links['sort_module']),
				array('IP',62,'sort'=>$qs['sort']=='ip' ? $qs['so'] : false,'href'=>$links['sort_ip']),
				array(static::$lang['text'],300),
				array($ltpl['functs'],60,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				$ong ? false : array(Eleanor::Check('mass',false,array('id'=>'mass-check')),10)
			);
		if($items)
		{
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					Eleanor::$Language->Date($v['date'],'fdt'),
					$v['author_id'] ? '<a href="'.Eleanor::$Login->UserLink(htmlspecialchars_decode($v['author'],ELENT),$v['author_id']).'">'.$v['author'].'</a>' : $v['author'],
					isset($titles[$k]) ? '<a href="'.$titles[$k][1].'" target="_blank">'.$titles[$k][0].'</a>' : '',
					'<a href="http://eleanor-cms.ru/whois/'.$v['ip'].'">'.$v['ip'].'</a>',
					Strings::CutStr(strip_tags($v['text']),160),
					$Lst('func',
						array($v['_aswap'],$v['status']<=0 ? $ltpl['activate'] : $ltpl['deactivate'],$v['status']<0 ? $images.'waiting.png' : $images.($v['status']==0 ? 'inactive.png' : 'active.png')),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					$ong ? false : Eleanor::Check('mass[]',false,array('value'=>$k))
				);
		}
		else
			$Lst->empty(static::$lang['cnf']);
		$Lst->end();
		if($ong)
			return$Lst;

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'module'=>false,
		);
		$omods=Eleanor::Option('&mdash;',0,false,array(),2);
		foreach($modules as $k=>&$v)
			$omods.=Eleanor::Option($v,$k,$k==$qs['']['fi']['module']);

		return Eleanor::$Template->Cover('<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td colspan="2"><a href="#">'.static::$lang['filter'].'</a></td></tr>
	<tr>
		<td><b>'.static::$lang['module'].'</b><br />'.Eleanor::Select('fi[module]',$omods).'</td>
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
});//]]></script>
</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
		.$Lst
		.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['cpp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k').Eleanor::Option($ltpl['active'],'a').Eleanor::Option($ltpl['inactive'],'d').Eleanor::Option(static::$lang['blocked'],'b')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page'])));
	}

	/*
		Страница редактирования комментария
		$id идентификатор редактируемого комментария
		$module название модуля
		$info - массив с данными о публикации комментария, ключи:
			0 - название контентины
			1 - ссылка на комментарий
		$values массив значений полей, ключи:
			date - дата комментария (неизменно)
			author - имя автора комментария (неизменно)
			author_id - ID автора комментария (неизменно)
			text - текст комментария
			status - статус комментария (-1 - ожидание модерации, 0 - залокирован, 1 - активен)
		$bypost флаг загрузки данных из POST запроса
		$error ошибка, если ошибка пустая - значит ее нет
		$back URL возврата
		$links перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
	*/
	public static function Edit($id,$module,$info,$values,$bypost,$error,$back,$links)
	{
		static::Menu();
		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form');
		return Eleanor::$Template->Cover($Lst->form()
			->begin()
			->item(static::$lang['module'],$module)
			->item(static::$lang['published'],'<a href="'.$info[1].'" target="_blank">'.$info[0].'</a>')
			->item(static::$lang['date'],Eleanor::$Language->Date($values['date'],'fdt'))
			->item(static::$lang['author'],$values['author_id'] ? '<a href="'.Eleanor::$Login->UserLink(htmlspecialchars_decode($values['author'],ELENT),$values['author_id']).'">'.$values['author'].'</a>' : $values['author'])
			->item(static::$lang['text'],$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost)))
			->item(static::$lang['status'],Eleanor::Select('status',Eleanor::Option($ltpl['activate'],1,$values['status']==1).Eleanor::Option($ltpl['deactivate'],0,$values['status']==0).Eleanor::Option($ltpl['waiting_act'],-1,$values['status']==-1)))
			->button($back.Eleanor::Button().' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')))
			->end()
			->endform(),$error);
	}

	/*
		Страница удаления комментария
		$t - массив удаляемого комментария, ключи:
			text - текст удаляемого комментария
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],Strings::CutStr(strip_tags($a['text']),200)),$back));
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{
		static::Menu('options');
		return$c;
	}
}
TplComments::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/comments-*.php',false);