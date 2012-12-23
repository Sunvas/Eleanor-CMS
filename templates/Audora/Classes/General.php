<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Этот файл отвечает за оформление секции "главная" в админке
*/
class TPLGeneral
{	public static
		$lang=array();	/*
		Меню раздела
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['general'];
		$links=&$GLOBALS['Eleanor']->module['links'];
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['main'],Eleanor::$Language['main']['main page'],'act'=>$act=='main'),
			array($links['server'],$lang['server_info'],'act'=>$act=='server'),
			array($links['logs'],$lang['logs'],'act'=>$act=='logs'),
			array($links['license'],$lang['license_'],'act'=>$act=='license'),
		);
	}
	/*
		Главная страница админки

		$nums - массив, содержит ключи:
			c - количество комментариев всего
			cw - количество комментариев за текущую неделю
			u - количество пользователей всего
			uw - количество пользователей за текущую неделю
			sl - срок жизни сайта в днях
		$comments содержит готовый шаблон (!) последних комментариев
		$users - массив, содержит перечень последних зарегистрированных пользователей на сайте. Структура: id=>array(), ключи массива каждого элемента:
			full_name - полное имя пользователя (безопасный HTML)
			name - логин пользователя (НЕбезопасный HTML)
			email - электронная почта пользователя
			groups - array - перечень групп пользователя
			ip - IP адрес пользователя
			register - дата регистрации пользователя в формате Y-m-d H:i:s
			last_visit - дата последнего входа пользователя в формате Y-m-d H:i:s
		$groups - массив, содержит перечень групп пользователей, для массивов $users[ID]['groups']. Структура: id=>array(), ключи массива каждого элемента:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML суффикс группы
		$mynotes - строка с содержимым "моих" заметок
		$conotes - строка с содержимым общих заметок
		$ck - логическое, характеризует очищен ли кэш
	*/
	public static function General($nums,$comments,$users,$groups,$mynotes,$conotes,$ck)
	{		static::Menu('main');		$ltpl=Eleanor::$Language['tpl'];

		$ULst=Eleanor::LoadListTemplate('table-list',7)->begin(static::$lang['name'],'E-mail',static::$lang['group'],static::$lang['reg'],static::$lang['lastw'],'IP',$ltpl['functs']);
		$myuid=Eleanor::$Login->GetUserValue('id');
		$images=Eleanor::$Template->default['theme'].'images/';
		foreach($users as $k=>&$v)
		{
			$grs='';
			foreach($v['groups'] as &$gv)
				if(isset($groups[$gv]))
					$grs.='<a href="'.$groups[$gv]['_aedit'].'">'.$groups[$gv]['html_pref'].$groups[$gv]['title'].$groups[$gv]['html_end'].'</a>, ';
			$ULst->item(
				'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],ELENT,CHARSET).'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>'),
				array($v['email'],'center'),
				rtrim($grs,' ,'),
				array(substr($v['register'],0,-3),'center'),
				array(substr($v['last_visit'],0,-3),'center'),
				array($v['ip'],'center','href'=>'http://eleanor-cms.ru/whois/'.$v['ip'],'hrefextra'=>array('target'=>'_blank')),
				$ULst('func',
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
					$myuid==$k ? false : array($v['_adel'],$ltpl['delete'],$images.'delete.png')
				)
			);
		}
		$ULst->end();

		$modules=Modules::GetCache();

		$newsurl=array_keys($modules['ids'],1);#Новости
		$newsurl=urlencode(reset($newsurl));

		$pageurl=array_keys($modules['ids'],2);#Статические страницы		$pageurl=urlencode(reset($pageurl));

		$menuurl=array_keys($modules['ids'],7);#Меню
		$menuurl=urlencode(reset($menuurl));

		$c=Eleanor::$Template->OpenTable()
	.'<div class="wbpad twocol"><div class="colomn">
<ul class="reset blockbtns">
<li><a href="'.Eleanor::$services['admin']['file'].'?section=modules&amp;module='.$newsurl.'&amp;do=add"><img src="images/modules/news-big.png" alt="" /><span>'.static::$lang['crnews'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=modules&amp;module='.$pageurl.'&amp;do=add"><img src="images/modules/static-big.png" alt="" /><span>'.static::$lang['crpage'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=modules&amp;module='.$menuurl.'&amp;do=add"><img src="images/modules/menu-big.png" alt="" /><span>'.static::$lang['crmenu'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=blocks&amp;do=add"><img src="images/modules/blocks-big.png" alt="" /><span>'.static::$lang['crbl'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=users&amp;do=add"><img src="images/modules/users-big.png" alt="" /><span>'.static::$lang['cruser'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=spam&amp;do=add"><img src="images/modules/spam-big.png" alt="" /><span>'.static::$lang['crspam'].'</span></a></li>
</ul></div>
<div class="colomn">
<div class="blockwel"><div class="pad"><h3 class="dtitle">'.static::$lang['thanks'].'</h3>'.static::$lang['thanks_'].'</div></div>
</div>
<div class="clr"></div>
</div>'.Eleanor::$Template->CloseTable();

		if(file_exists(Eleanor::$root.'install'))
			$c.=Eleanor::$Template->Message(static::$lang['install_nd'],'warning');
		$GLOBALS['jscripts'][]='js/tabs.js';
		$c.=Eleanor::$Template->Title($ltpl['info'])->OpenTable()
	.'<ul id="stabs" class="reset linetabs">
	<li><a class="selected" data-rel="stab1" href="#"><b>'.static::$lang['stat'].'</b></a></li>
	<li><a data-rel="stab2" href="#"><b>'.static::$lang['comments'].'</b></a></li>
	<li><a data-rel="stab3" href="#"><b>'.static::$lang['users'].'</b></a></li>
	<li><a data-rel="stab4" href="#"><b>'.static::$lang['newselc'].'</b></a></li>
	<li><a data-rel="mynotes" href="#"><b>'.static::$lang['ownnote'].'</b></a></li>
	<li><a data-rel="conotes" href="#"><b>'.static::$lang['gennote'].'</b></a></li>
</ul>
<div id="stab1" class="tabcontent">
<table class="tabstyle">
<tr class="first tabletrline1"><td>'.static::$lang['stcomm'].'</td><td style="text-align:center"><b>'.$nums['cw'].'</b> ('.$nums['c'].')</td></tr>
<tr class="tabletrline2"><td>'.static::$lang['stuser'].'</td><td style="text-align:center"><b>'.$nums['uw'].'</b> ('.$nums['u'].')</td></tr>
<tr class="tabletrline1"><td>'.static::$lang['stsite'].'</td><td style="text-align:center"><b>'.$nums['sl'].'</b></td></tr>
<tr class="last tabletrline2"><td>'.static::$lang['time_on_server'].'</td><td style="text-align:center">'.Eleanor::$Language->Date().'</td></tr>
</table>
</div>
<div id="stab2" class="tabcontent">'.$comments.'</div>
<div id="stab3" class="tabcontent">'.$ULst.'</div>
<div id="stab4" class="tabcontent"></div>
<div id="mynotes" class="tabcontent">'.static::Notes($mynotes).'</div>
<div id="conotes" class="tabcontent">'.static::Notes($conotes).'</div>
<script type="text/javascript">//<![CDATA[
$(function(){
	$("#stabs a").Tabs({		OnBeforeSwitch:function(a){			if(a.data("rel")=="stab4" && !$("#stab4").html())
			{				CORE.ShowLoading();				$.getJSON("http://eleanor-cms.ru/updates.php?ver=1&c=?",function(d){					$("#stab4").html(d.data);					CORE.HideLoading();
				});
			}
			return true;		}
	});
	$("#mynotes,#conotes").on("click",".submitline [type=button]",function(){		var p=$(this).closest(".tabcontent").attr("id"),
			s=$(this).data("save");		CORE.Ajax(
			{
				direct:"admin",
				file:"notes",
				event:p+(s ? "" : "load"),
				text:s ? EDITOR.Get("e"+p)||"" : "",
			},
			function(r)
			{
				$("#"+p).html(r);
			}
		);	})
});//]]></script>'.Eleanor::$Template->CloseTable()->Title(static::$lang['cachem']);

		if($ck)
			$c.=Eleanor::$Template->Message(static::$lang['cache_deleted'],'info');

		return$c.Eleanor::$Template->OpenTable().'<div class="blockcache">
		<div class="colomn"><div class="pad">'.static::$lang['cache_'].'<div class="submitline"><form method="post">'.Eleanor::Input('kill_cache','1',array('type'=>'hidden')).Eleanor::Button(static::$lang['cachedel'],'submit',array('style'=>'button')).'</form></div></div></div>
			<div class="clr"></div></div>'.Eleanor::$Template->CloseTable();
	}

	/*
		Шаблон страницы с информацией о сервере

		array $values ключи:
			gd_info - массив параметров библиотеки GD либо false
			ini_get_v - значение запрашивамой константы
			ini_get - запрашиваемая константа
			os - операционная система, на которой запущена Eleanor CMS
			pms - Post max size
			ums - Upload max size
			ml - Memory limit
			met - Max execution time
			db - версия MySQL
	*/
	public static function Server($values)
	{		static::Menu('server');
		$gdver='';
		if($values['gd_info'])
			foreach($values['gd_info'] as $k=>&$v)				$gdver.=is_bool($v) ? '<li><b>'.$k.'</b>: '.($v ? '<span style="color:green">Yes</span>' : '<span style="color:green">No</span>').'</li>' : '<li><b>'.$k.'</b>: '.$v.'</li>';
		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item('OS',$values['os'])
			->item('PHP',PHP_VERSION)
			->item('GD',$gdver ? '<ul style="list-style-type:none">'.$gdver.'</ul>' : '&mdash;')
			->item('DB',$values['db'])
			->item('Post max size',$values['pms'])
			->item('Upload max size',$values['ums'])
			->item('Memory limit',$values['ml'])
			->item('Max execution time',$values['met'])
			->item('Max int',PHP_INT_MAX)
			->item(static::$lang['get_value'],'<form method="post">'.Eleanor::Input('ini_get',$values['ini_get']).Eleanor::Button('?').'</form>');
		if($values['ini_get_v'] or $values['ini_get'])
			$Lst->item(htmlspecialchars($values['ini_get'],ELENT,CHARSET),$values['ini_get_v'] ? htmlspecialchars($values['ini_get_v'],ELENT,CHARSET) : '&mdash;');
		return Eleanor::$Template->Cover(
			$Lst->button('<a href="'.$GLOBALS['Eleanor']->Url->Prefix().'">'.Eleanor::$Language['tpl']['goback'].'</a>')->end()
		);
	}

	/*
		Страница просмотра списка лог-файлов

		$logs массив, содержит массив лог-файлов. Каждый элемент массива - массив с ключами:
			path - полный путь к файлу относительно корня сайта
			descr - описание файла
			size - размер файла в байтах
			aview - ссылка на просмотр файла
			adown - ссылка на скачивание файла
			adel - ссылка на удаление файла
	*/
	public static function Logs($logs)
	{		static::Menu('logs');
		$ltpl=Eleanor::$Language['tpl'];
		$images=Eleanor::$Template->default['theme'].'images/';
		if($logs)
		{
			$Lst=Eleanor::LoadListTemplate('table-list',4)
				->begin(static::$lang['file'],static::$lang['path'],static::$lang['size'],array(Eleanor::$Language['tpl']['functs'],70));

			foreach($logs as &$v)
				$Lst->item(
					'<a href="'.$v['aview'].'">'.$v['descr'].'</a>',
					'<a href="'.$v['adown'].'">'.$v['path'].'</a>',
					Files::BytesToSize($v['size']),
					$Lst('func',
						array($v['aview'],static::$lang['view_log'],$images.'viewfile.png'),
						array($v['adown'],static::$lang['download_log'],$images.'downloadfile.png'),
						array($v['adel'],static::$lang['delete_log'],$images.'delete.png','extra'=>array('onclick'=>'return confirm(\''.$ltpl['are_you_sure'].'\')'))
					)
				);
			$Lst->end()->s.='<br />';
		}
		else
			$Lst=Eleanor::$Template->Message(static::$lang['nologs'],'info');
		return Eleanor::$Template->Cover($Lst);	}

	/*
		Страница просмотра лог-файла

		$data - в зависимости от типа, либо массив логов, либо текст лог-файла
		$file - название лог-файла
		$links перечень необходимых ссылок, массив с ключами:
			adown - ссылка на скачивание файла
			adel - ссылка на удаление файла
	*/
	public static function ShowLog($data,$file,$links)
	{		static::Menu('logs');
		$ltpl=Eleanor::$Language['tpl'];
		if(is_array($data))
		{
			$log='<div class="logs">';
			switch($file)
			{				case'errors':
					foreach($data as $k=>&$v)
					{						$page=htmlspecialchars($v['d']['p'],ELENT,CHARSET,false);
						$p=strpos($v['d']['e'],':');

						$v['d']['e']=substr_replace($v['d']['e'],'<span style="color:red">',$p+2,0);
						$v['d']['e']=substr_replace($v['d']['e'],'</b>('.$v['d']['n'].')',$p,0);
						$log.='<div class="warning" data-id="'.$k.'"><pre><code><b>'
							.$v['d']['e'].'</span><br />'.$v['d']['f'].'['.$v['d']['l'].']<br />'
							.Eleanor::$Language->Date($v['d']['d'],'fdt')
							.'<br /><a href="'.$page.'" target="_blank">'.($page ? $page : '/').'</a></code></pre><div class="repair"><a href="#">'.static::$lang['fixed'].'</a></div></div>';
					}
				break;
				case'db_errors':
					foreach($data as $k=>&$v)
						$log.='<div class="warning" data-id="'.$k.'"><pre><code>'
							.(isset($v['d']['e']) ? '<b>'.$v['d']['e'].'</b><br />' : '')
							.(isset($v['d']['q']) ? 'Query: <span style="color:red">'.$v['d']['q'].'</span><br />' : '')
							.(isset($v['d']['h']) ? 'Host: '.$v['d']['h'].'<br />' : '')
							.(isset($v['d']['u']) ? 'User: '.$v['d']['u'].'<br />' : '')
							.(isset($v['d']['p']) ? 'Password: '.$v['d']['p'].'<br />' : '')
							.(isset($v['d']['db']) ? 'DB: '.$v['d']['db'].'<br />' : '')
							.(isset($v['d']['f'],$v['d']['l']) ? $v['d']['f'].'['.$v['d']['l'].']<br />' : '')
							.''.Eleanor::$Language->Date($v['d']['d'],'fdt')
							.'<br />Happend: <b>'.$v['d']['n'].'</b></code></pre><div class="repair"><a href="#">'.static::$lang['fixed'].'</a></div></div>';
				break;
				case'request_errors':
					foreach($data as $k=>&$v)
					{						$refs='';
						if(isset($v['d']['r']))
							foreach($v['d']['r'] as &$rv)
							{
								$rv=htmlspecialchars($rv,ELENT,CHARSET,false);
								$refs.='<a href="'.$rv.'" target="_blank">'.($rv ? $rv : '/').'</a>, ';
							}

						$page=htmlspecialchars($v['d']['p'],ELENT,CHARSET,false);
						$log.='<div class="warning" data-id="'.$k.'"><code><pre><b>'.$v['d']['e'].'</b>('.$v['d']['n'].')<br />'
							.(isset($v['d']['u']) ? '<a href="'.Eleanor::$Login->UserLink($v['d']['u'],$v['d']['ui']).'">'.htmlspecialchars($v['d']['u'],ELENT,CHARSET).'</a>' : 'Guest')
							.' &mdash; <a href="http://eleanor-cms.ru/whois/'.$v['d']['ip'].'">'.$v['d']['ip'].'</a> &mdash; '
							.$v['d']['b'].'<br />'
							.Eleanor::$Language->Date($v['d']['d'],'fdt')
							.'<br /><a href="'.$page.'" target="_blank">'.($page ? $page : '/').'</a>'
							.($refs ? ' &lt;&lt;&lt; '.rtrim($refs,', ') : '')
							.'</pre></code><div class="repair"><a href="#">'.static::$lang['fixed'].'</a></div></div>';
					}
			}
			$log.='</div><script type="text/javascript">//<![CDATA[
$(function(){	$(".logs a[href=#]").click(function(){		var div=$(this).closest(".warning");		CORE.Ajax(
			{				direct:"admin",
				file:"misc",
				event:"fixed",
				log:"'.$file.'",
				id:div.data("id")			},
			function()
			{				if($(".logs .warning").size()>1)					div.remove();
				else
					$(".submitline :button").click();
			}
		);
		return false;	});});//]]></script>';
		}
		else
			$logs=Eleanor::Text('text',$data,array('style'=>'width:100%;','readonly'=>'readonly','rows'=>30));

		return Eleanor::$Template->Cover('<p class="function"><a href="'.$links['adown'].'" title="'.static::$lang['download_log'].'"><img src="'.Eleanor::$Template->default['theme'].'images/downloadfile.png" alt="" /></a><a href="'.$links['adel'].'" title="'.static::$lang['delete_log'].'" onclick="return confirm(\''.$ltpl['are_you_sure'].'\')"><img src="'.Eleanor::$Template->default['theme'].'images/delete.png" alt="" /></a></p><div style="margin:15px;">'.$log.'</div><div class="submitline">'.Eleanor::Button($ltpl['goback'],'button',array('onclick'=>'window.location=\''.$GLOBALS['Eleanor']->Url->Prefix().'\'')).'</div>');	}

	/*
		Элемент шаблона: блокнот. Вызывается и из AJAX

		$edt содержит текстовый редактор или отредактированный шаблон
		$edit признак того, редактируется блокнот или отображается
	*/
	public static function Notes($edt,$edit=false)
	{		return'<div class="wbpad"><div class="brdbox">'.($edt ? $edt : '<div style="text-align:center;color:lightgray;font-size:1.5em">'.static::$lang['empty'].'</div>').'</div></div><div class="submitline">'.Eleanor::Button($edit ? 'OK' : Eleanor::$Language['tpl']['edit'],'button',$edit ? array('data-save'=>1) : array()).'</div>';
	}

	/*
		Страница лицензий и санкций

		$l содержит текст лицензии
		$s содержит текст санкций
	*/
	public static function License($l,$s)
	{		static::Menu('license');
		return Eleanor::$Template->Title(static::$lang['license'])
			->OpenTable()
			.'<div class="textarea license" style="margin-left:5px">'.$l.'</div><a href="addons/license/license-'.Language::$main.'.html" target="_blank" style="margin-left:5px"><img src="'.Eleanor::$Template->default['theme'].'images/print.png" alt="" /> '.static::$lang['print'].'</a>'
			.Eleanor::$Template->CloseTable()
			.'<br />'
			.Eleanor::$Template->Title(static::$lang['sanctions'])
			->OpenTable()
			.'<div class="textarea license" style="margin-left:5px">'.$s.'</div><a href="addons/license/sanctions-'.Language::$main.'.html" target="_blank" style="margin-left:5px"><img src="'.Eleanor::$Template->default['theme'].'images/print.png" alt="" /> '.static::$lang['print'].'</a>'
			.Eleanor::$Template->CloseTable();	}}
TplGeneral::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/general-*.php',false);