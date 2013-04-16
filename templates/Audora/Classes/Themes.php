<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны менеджера шаблонов
*/
class TplThemes
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
			array($links['list'],Eleanor::$Language['te']['list'],'act'=>$act=='list'),
			$links['info']
			? array($links['info']['link'],$links['info']['name'],'act'=>$act=='info',
				'submenu'=>array(
					array($links['files'],static::$lang['file_work'],'act'=>$act=='files'),
					$links['config'] ? array($links['config'],static::$lang['config'],'act'=>$act=='config') : false,
				),
			)
			: false,
		);
	}

	/*
		Основная страница менеджера шаблонов. Вывод всех шаблонов, которые существуют в системе
		$tpls - массив шаблонов. Формат имя шаблона=>array(), ключи внутреннего массива:
			title - название шаблона
			setto - массив сервисов, куда этот шаблон может быть установлен. Формат название сервиса=>ссылка на установку
			_afiles - ссылка на управление файлами шаблона
			_aopts - ссылка на конфигурирование шаблона, либо false
			_ainfo - ссылка на информацию о шаблоне, либо false
			_adel - ссылка на удаление шаблона
			image - ссылка на изображение-логотип шаблона
			used - массив сервисов, использующие этот шаблон по умолчанию
			creation - дата создания шаблона (задается создателем шаблона)
	*/
	public static function TemplatesGeneral($tpls)
	{
		static::Menu('list');
		$ltpl=Eleanor::$Language['tpl'];
		$images=Eleanor::$Template->default['theme'].'images/';
		$used=$notused='';
		$n=$nn=2;
		foreach($tpls as $k=>&$v)
		{
			$setto='';
			$title=$v['title'] ? $v['title'].' ('.$k.')' : $k;
			foreach($v['setto'] as $sk=>&$sv)
				$setto.='<p><a href="'.$sv.'" style="font-weight:bold">'.sprintf(static::$lang['set_for'],$sk).'</a></p>';

			$c='<td><div class="thm_block"><div class="thm_brd"><div class="thm_menu"><div class="thm_cont"><div class="thm_mtop">'
				.($v['_aopts'] ? '<a href="'.$v['_aopts'].'">'.static::$lang['config'].'</a> | ' : '')
				.($v['_ainfo'] ? '<a href="'.$v['_ainfo'].'">'.$ltpl['info'].'</a> | ' : '')
				.'<a href="'.$v['_afiles'].'">'.static::$lang['file_work'].'</a></div>'
				.$setto.'<div class="thm_btn"><a href="#" title="'.static::$lang['copy'].'" class="copy" data-n="'.$k.'"><img src="'.$images.'thm_copy.png" alt="" /></a>'
				.($v['_adel'] ? '<a href="'.$v['_adel'].'" title="'.$ltpl['delete'].'"><img src="'.$images.'thm_del.png" alt="" /></a>' : '')
				.'</div></div></div><div class="thm_cont"><span class="thm_img"><img src="'.($v['image'] ? $v['image'] : $images.'default_theme.png').'" alt="" title="'.$title.'" /></span><div class="thm_mcont"><div class="thm_heading"><h3>'.$title.'</h3>'
				.($v['used'] ? '<b>'.sprintf(static::$lang['services'],join(', ',$v['used'])).'</b>' : '').'</div><div class="thm_info">'.($v['author'] ? '<p>'.sprintf(static::$lang['author'],$v['author']).'</p>' : '')
				.($v['creation'] ? '<p>'.sprintf(static::$lang['cr_date'],$v['creation']).'</p>' : '').'</div></div></div></div></div></td>';

			if($v['used'])
			{
				$used.=($n--==2 ? '<tr>' : '').$c;
				if($n==0)
				{
					$used.='</tr>';
					$n=2;
				}
			}
			else
			{
				$notused.=($nn--==2 ? '<tr>' : '').$c;
				if($nn==0)
				{
					$notused.='</tr>';
					$nn=2;
				}
			}
		}
		if($n>0 and $n<2)
		{
			while($n--)
				$used.='<td></td>';
			$used.='</tr>';
		}
		if($nn>0 and $nn<2)
		{
			while($nn--)
				$notused.='<td></td>';
			$notused.='</tr>';
		}
		return($used ? Eleanor::$Template->Title(static::$lang['used_templ'])->OpenTable().'<table class="tablethm">'.$used.'</table>'.Eleanor::$Template->CloseTable() : '')
			.($notused ? Eleanor::$Template->Title(static::$lang['avai_templ'])->OpenTable().'<table class="tablethm">'.$notused.'</table>'.Eleanor::$Template->CloseTable() : '')
			.'<script type="text/javascript">//<![CDATA[
$(function(){
	$(".tablethm").on("click",".copy",function(){
		var nt=prompt("'.static::$lang['enter_nt'].'"),
			t=$(this).data("n");
		if(!nt || !t || t==nt)
			return false;
		CORE.Ajax(
			{
				direct:"admin",
				file:"'.$GLOBALS['Eleanor']->module['name'].'",
				event:"copy",
				theme:t,
				newtpl:nt
			},
			function(r)
			{
				window.location.reload();
			}
		);
		return false;
	});
})//]]></script>';
	}

	/*
		Страница принятия лицензионного соглашения шаблона. Выводится в случае установки темы.
		$t - название шаблона
		$back - URI возврата
		$lic - лицензионное соглашение
	*/
	public static function License($t,$back,$lic)
	{
		static::Menu('info');
		return'<div class="wbpad"><div class="warning">
<img src="'.Eleanor::$Template->default['theme'].'images/warning.png" class="info" alt="" title="'.$t.'" />
<div>
	<h4>'.$t.'</h4><hr /><div class="wbpad" style="max-height:300px;margin:10px 0 10px 0;">'.$lic.'</div><hr />
	<form method="post">'.($back ? '' : Eleanor::Input('back',$back,array('type'=>'hidden'))).'<div style="text-align:center;margin-top:10px">
	<input class="button" name="submit" type="submit" value="'.static::$lang['submitlic'].'" />
	<input class="button" name="refuse" type="submit" value="'.static::$lang['refuselic'].'" />
	<input class="button" type="button" value="'.static::$lang['cancel'].'" onclick="history.go(-1); return false;" />
	</div>
	</form>
</div>
<div class="clr"></div>
</div></div>';
	}

	/*
		Страница с информацией о шаблоне оформления
		$name - имя шаблона
		$info - информация о шаблоне
		$license - лицензия шаблона
	*/
	public static function Info($name,$info,$license)
	{
		static::Menu('info');
		return ($info
			? Eleanor::$Template->Title($name)
				->OpenTable().'<div class="wbpad" style="max-height:300px">'.$info.'</div>'.Eleanor::$Template->CloseTable()
			: '')
			.($license
			? Eleanor::$Template->Title(Eleanor::$Language['te']['agreement'])
				->OpenTable().'<div class="wbpad" style="max-height:300px">'.$license.'</div>'.Eleanor::$Template->CloseTable()
			: '');
	}

	/*
		Страница управления файлами шаблона
		$files - интерфейс аплоадера файлов
		$name - название шаблона
	*/
	public static function Files($files,$name)
	{
		static::Menu('files');
		return Eleanor::$Template->Cover($files).'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#showb-tpl").hide().click();
	FItpl.Open=function(url)
	{
		url=encodeURIComponent(FItpl.Get("realpath").replace("templates/","")+"/"+url).replace(/!/g,"%21").replace(/\'/g,"%27").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/\*/g,"%2A").replace(/%20/g,"+")
		window.open(window.location.protocol+"//"+window.location.hostname+CORE.site_path+"'.Eleanor::$services['download']['file'].'?direct='.Eleanor::$service.'&file='.$GLOBALS['Eleanor']->module['name'].'&f="+url);
		return false;
	}
})//]]></script>';
	}

	/*
		Шаблон страницы с редактированием конфигураций шаблона
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
	*/
	public static function Config($controls,$values,$errors)
	{
		static::Menu('config');
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if($v)
				if(is_array($v) and !empty($values[$k]))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
				elseif(is_string($v))
					$Lst->head($v);

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst->button(Eleanor::Button())->end()->endform(),$errors,'error');
	}

	/*
		Страница удаления шаблона
		$t - текст-подтверждение удаления
		$back - URL возврата
	*/
	public static function Delete($t,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm($t,$back));
	}
}
TplThemes::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/themes-*.php',false);