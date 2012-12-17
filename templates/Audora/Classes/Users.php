<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны управления пользователями в админке
*/
class TplUsers
{	public static
		$lang;	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['users'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				)
			),
			array($links['online'],$lang['whoonline'],'act'=>$act=='online'),
			array($links['letters'],$lang['letters'],'act'=>$act=='letters'),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);
	}
	/*
		Страница отображения пользователей
		$items - массив пользователей. Формат: ID=>array(), ключи внутреннего массива:
			name - имя пользователя (не безопасных HTML)
			full_name - полное имя пользователя
			email - e-mail пользователя
			groups - массив групп пользователя
			ip - IP адрес пользователя
			last_visit - дата последнего визита пользователя
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя либо false
		$groups - массив групп пользователей. Формат: ID=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
		$cnt - количество пользователей всего
		$pp - количество пользователей на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_name - ссылка на сортировку списка $items по имени пользователя (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_email - ссылка на сортировку списка $items по email (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_group - ссылка на сортировку списка $items по группе (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_visit - ссылка на сортировку списка $items по последнему визиту (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_ip - ссылка на сортировку списка $items по ip (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/	public static function ShowList($items,$groups,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');		$ltpl=Eleanor::$Language['tpl'];		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'name'=>false,
			'namet'=>false,
			'sname'=>false,
			'snamet'=>false,
			'group'=>false,
			'lvto'=>false,
			'lvfrom'=>false,
			'regto'=>false,
			'regfrom'=>false,
			'ip'=>false,
			'email'=>false,
			'id'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',7)
			->begin(
				array(static::$lang['name'],'sort'=>$qs['sort']=='name' ? $qs['so'] : false,'href'=>$links['sort_name']),
				array('E-mail','sort'=>$qs['sort']=='email' ? $qs['so'] : false,'href'=>$links['sort_email']),
				array(static::$lang['group'],'sort'=>$qs['sort']=='groups' ? $qs['so'] : false,'href'=>$links['sort_group']),
				array(static::$lang['last_visit'],'sort'=>$qs['sort']=='last_visit' ? $qs['so'] : false,'href'=>$links['sort_visit']),
				array('IP','sort'=>$qs['sort']=='ip' ? $qs['so'] : false,'href'=>$links['sort_ip']),
				array($ltpl['functs'],'sort'=>$qs['sort']=='id' ? $qs['so'] : false,80,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as &$v)
			{				$grs='';
				foreach($v['groups'] as &$gv)
					if(isset($groups[$gv]))
						$grs.='<a href="'.$groups[$gv]['_aedit'].'">'.$groups[$gv]['html_pref'].$groups[$gv]['title'].$groups[$gv]['html_end'].'</a>, ';				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],ELENT,CHARSET).'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>'),
					array($v['email'],'center'),
					rtrim($grs,' ,'),
					array(substr($v['last_visit'],0,-3),'center'),
					array($v['ip'],'center','href'=>'http://eleanor-cms.ru/whois/'.$v['ip'],'hrefextra'=>array('target'=>'_blank')),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						$v['_adel'] ? array($v['_adel'],$ltpl['delete'],$images.'delete.png') : false
					),
					Eleanor::Check('mass[]',false,array('value'=>$v['id']))
				);
			}
		}
		else
			$Lst->empty(static::$lang['unf']);

		$fisnamet=$finamet='';
		$namet=array(
			'b'=>static::$lang['begins'],
			'q'=>static::$lang['match'],
			'e'=>static::$lang['endings'],
			'm'=>static::$lang['contains'],
		);
		foreach($namet as $k=>&$v)
			$finamet.=Eleanor::Option($v,$k,$qs['']['fi']['namet']==$k);
		foreach($namet as $k=>&$v)
			$fisnamet.=Eleanor::Option($v,$k,$qs['']['fi']['snamet']==$k);

		return Eleanor::$Template->Cover(
		'<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.static::$lang['name'].'</b><br />'.Eleanor::Select('fi[namet]',$finamet,array('style'=>'width:30%')).Eleanor::Input('fi[name]',$qs['']['fi']['name'],array('style'=>'width:68%')).'</td>
					<td><b>'.static::$lang['fullname'].'</b><br />'.Eleanor::Select('fi[snamet]',$finamet,array('style'=>'width:30%')).Eleanor::Input('fi[sname]',$qs['']['fi']['sname'],array('style'=>'width:68%')).'</td>
				</tr>
				<tr>
					<td><b>IDs</b><br />'.Eleanor::Input('fi[id]',$qs['']['fi']['id']).'</td>
					<td><b>'.static::$lang['group'].'</b><br />'.Eleanor::Select('fi[group]',Eleanor::Option(static::$lang['not_imp'],0).UserManager::GroupsOpts($qs['']['fi']['group'])).'</td>
				</tr>
				<tr>
					<td><b>'.static::$lang['last_visit'].'</b> '.static::$lang['from-to'].'<br />'.Dates::Calendar('fi[lvfrom]',$qs['']['fi']['lvfrom'],true,array('style'=>'width:35%')).' - '.Dates::Calendar('fi[lvto]',$qs['']['fi']['lvto'],true,array('style'=>'width:35%')).'</td>
					<td><b>'.static::$lang['register'].'</b> '.static::$lang['from-to'].'<br />'.Dates::Calendar('fi[regfrom]',$qs['']['fi']['regfrom'],true,array('style'=>'width:35%')).' - '.Dates::Calendar('fi[regto]',$qs['']['fi']['regto'],true,array('style'=>'width:35%')).'</td>
				</tr>
				<tr>
					<td><b>E-mail</b><br />'.Eleanor::Input('fi[email]',$qs['']['fi']['email']).'</td>
					<td><b>IP</b><br />'.Eleanor::Input('fi[ip]',$qs['']['fi']['ip']).'</td>
				</tr>
				<tr>
					<td style="text-align:center;vertical-align:middle" colspan="2">'.Eleanor::Button($ltpl['apply']).'</td>
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
		</form>
		<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
		.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['upp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'d')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page'])));	}

	/*
		Страница добавления/редактирования пользователя
		$id - идентификатор редактируемого пользователя, если $id==0 значит пользователь добавляется
		$values - массив значений полей для правки. Ключи:
			name - имя пользователя
			full_name - полное имя пользователя
			_slname - только при редактировании, флаг отправки письма пользователю о новом имени
			pass - поле для изменения пароля
			pass2 - повтор пароля для его изменения
			_slpass - только при редактировании, флаг отправки письма пользователю о новом пароле
			_slnew - только при добавлении: флаг отправки письма пользователю о создании ему аккаунта на сайте
			email - e-mail пользователя
			_group - основная группа пользователя
			groups - массив дополнительных групп пользователя
			language - язык пользователя
			timezone - часовая зона пользователя
			staticip - флаг статического IP пользователя
			_atype - тип аватара пользователя: загруженный или локальный (из галереи)
			avatar_location - расположение аватара
			banned_until - дата снятия бана
			ban_explain - описание причин бана
			_overskip - данные для перезагрузки параметров разрешений групп
			_externalauth - массив внешних авторизаций. Ключи внутреннего массива:
				provider - идентификатор внешнего сервиса
				provider_uid - идентификатор пользователя на внешнем сервисе
				identity - ссылка на пользователя на внешнем сервисе
			_sessions - массив открытых сессий пользователя, формат: LoginClass=>Ключ=>array(), ключи внутреннего массива:
				0 - TIMESTAMP истечения активности
				1 - IP адрес
				2 - USER AGENT браузера
				_candel - флаг возможности удаления сессии

			не для правки, для информации:
			failed_logins - массив массивов неудачных попыток авторизации. Ключи внутренних массивов:
				0 - дата попытки
				1 - сервис
				2 - браузер
				3 - IP
		$overload - перечень контролов для перезагрузки разрешений групп в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$ovv - результирующий HTML код контролов перезагрузки разрешений групп, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $overload
		$upavatar - результирующий HTML код для загрузки аватара
		$extra - перечень контролов дополнительных полей пользователя. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$exv - результирующий HTML код дополнительных полей пользователя, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $overload
		$bypost - признак того, что данные нужно брать из POST запроса
		$errors - массив ошибок
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление пользователя или false
	*/
	public static function AddEditUser($id,$values,$overload,$ovv,$upavatar,$extra,$exv,$bypost,$errors,$back,$links)
	{		static::Menu($id ? '' : 'add');
		#Весь JS вынесен в отдельный файл, потому что его слишком много, чтобы писать здесь		$GLOBALS['jscripts'][]='js/admin_users_ae.js';

		$lang=Eleanor::$Language['users'];
		$ltpl=Eleanor::$Language['tpl'];

		$langs=Eleanor::Option($lang['by_default'],'',!$values['language']);
		foreach(Eleanor::$langs as $k=>&$v)
			$langs.=Eleanor::Option($v['name'],$k,$k==$values['language']);		list($awidth,$aheight)=explode(' ',Eleanor::$vars['avatar_size']);
		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->head(static::$lang['lap'])
			->item(static::$lang['name'],Eleanor::Input('name',$values['name'],array('id'=>'name','tabindex'=>1)))
			->item(static::$lang['fullname'],Eleanor::Input('full_name',$values['full_name'],array('id'=>'full-name','tabindex'=>2)));

		if($id)
			$Lst->item(array(static::$lang['slname'],Eleanor::Check('_slname',$values['_slname'],array('tabindex'=>3,'id'=>'slname')),'tip'=>static::$lang['slname_']));

		$Lst->item(array(static::$lang['pass'],Eleanor::Input('pass',$values['pass'],array('type'=>'password','id'=>'pass','tabindex'=>4)),'tip'=>static::$lang['pass_']))
			->item(static::$lang['passc'],Eleanor::Input('pass2',$values['pass2'],array('type'=>'password','id'=>'pass2','tabindex'=>5)));

		if($id)
			$Lst->item(array(static::$lang['slpass'],Eleanor::Check('_slpass',$values['_slpass'],array('tabindex'=>6,'id'=>'slpass')),'tip'=>static::$lang['slpass_']));
		else
			$Lst->item(array(static::$lang['slnew'],Eleanor::Check('_slnew',$values['_slnew'],array('tabindex'=>6)),'tip'=>static::$lang['slnew_']));

		$Lst->head(static::$lang['account'])
			->item('E-mail',Eleanor::Input('email',$values['email'],array('tabindex'=>7)))
			->item(static::$lang['group'],Eleanor::Select('_group',UserManager::GroupsOpts($values['_group']),array('tabindex'=>8)))
			->item(static::$lang['agroups'],Eleanor::Items('groups',UserManager::GroupsOpts($values['groups']),array('tabindex'=>9)))
			->item(static::$lang['lang'],Eleanor::Select('language',$langs,array('tabindex'=>10)))
			->item(static::$lang['timezone'],Eleanor::Select('timezone',Eleanor::Option($lang['by_default'],'',!$values['timezone']).Types::TimeZonesOptions($values['timezone']),array('tabindex'=>11)))
			->item(array(static::$lang['staticip'],Eleanor::Check('staticip',$values['staticip'],array('tabindex'=>12)),'tip'=>static::$lang['staticip_']))
			->head(static::$lang['avatar'])
			->item(
				static::$lang['alocation'],
				Eleanor::Select(
					'_atype',
					Eleanor::Option(static::$lang['agallery'],'gallery',!$values['_aupload'])
					.Eleanor::Option(static::$lang['apersonal'],'upload',$values['_aupload']),
					array('id'=>'atype','tabindex'=>14)
				)
			)
			->item(
				static::$lang['amanage'],
				Eleanor::Input('avatar_location',$values['avatar_location'],array('id'=>'avatar-input','type'=>'hidden'))
				.'<div id="avatar-local">
					<div id="avatar-select"></div>
					<div id="avatar-view">
						<a class="imagebtn getgalleries" href="#">'.static::$lang['gallery_select'].'</a><div class="clr"></div>
						<span id="avatar-no" style="width:'.($awidth ? $awidth : '180').'px;height:'.($aheight ? $aheight : '145').'px;text-decoration:none;max-height:100%;max-width:100%;" class="screenblock">
							<b>'.static::$lang['noavatar'].'</b><br />
							<span>'.sprintf('<b>%s</b> <small>x</small> <b>%s</b> <small>px</small>',$awidth ? $awidth : '&infin;',$aheight ? $aheight : '&infin;').'</span>
						</span>
						<img id="avatar-image" style="border:1px solid #c9c7c3;max-width:'.($awidth>0 ? $awidth.'px' : '100%').';max-height:'.($aheight>0 ? $aheight.'px' : '100%').'" src="images/spacer.png" /><div class="clr"></div>
						<a id="avatar-delete" class="imagebtn" href="#">'.$ltpl['delete'].'</a>
					</div>
				</div>
				<div id="avatar-upload">'.$upavatar.'</div>
<script type="text/javascript">/*<![CDATA[*/$(function(){AddEditUser('.($id ? $id : 'false').')})//]]></script>')
			->end();

		$general=(string)$Lst;

		$block=(string)$Lst->begin()
			->item(static::$lang['ban-to'],Dates::Calendar('banned_until',$values['banned_until'],true))
			->item(static::$lang['ban-exp'],$GLOBALS['Eleanor']->Editor->Area('ban_explain',$values['ban_explain'],array('bypost'=>$bypost)))
			->end();

		$Lst->begin();
		foreach($extra as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($exv[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);
		$extra=(string)$Lst->end();

		$Lst->begin();
		foreach($overload as $k=>&$v)
			if(is_array($v))
			{				$inherited=!isset($values['_overskip'][$k]) || $values['_overskip'][$k]=='inherit';
				$Lst->item(array(
					$v['title'],
					'<div class="overload"'.($inherited ? ' style="display:none"' : '').'>'.Eleanor::$Template->LangEdit($ovv[$k],null).'</div><div class="inherit"'.($inherited ? '' : ' style="display:none"').'>---<div class="clr"></div></div>',
					'tip'=>$v['descr'],
					'descr'=>Eleanor::Select(
						'_overskip['.$k.']',
						Eleanor::Option(static::$lang['inherit'],'inherit',$inherited)
						.Eleanor::Option(static::$lang['replace'],'replace',isset($values['_overskip'][$k]) and $values['_overskip'][$k]=='replace')
						.Eleanor::Option(static::$lang['addo'],'add',isset($values['_overskip'][$k]) and $values['_overskip'][$k]=='add'),
						array('style'=>'width:100px')
					),
				));
			}
			else
				$Lst->head($v);
		$special=(string)$Lst->end();

		if($id)
		{			$fla=$axauth='';
			foreach($values['failed_logins'] as &$v)
				$fla.='Date: '.Eleanor::$Language->Date($v[0])."\r\nService: ".$v[1]."\r\nBrowser: ".$v[2]."\r\nIP: ".$v[3]."\r\n\r\n";
			foreach($values['_externalauth'] as &$v)
				$axauth.='<span><a href="'.$v['identity'].'" target="_blank" class="exl">'.(isset(static::$lang[$v['provider']]) ? static::$lang[$v['provider']] : $v['provider']).'</a><a href="#" onclick="return data-provider="'.$v['provider'].'" data-providerid="'.$v['provider_uid'].'" title="'.$ltpl['delete'].'">X</a></span> ';
			$Lst->begin()
				->item(static::$lang['fla'],Eleanor::Text('',$fla,array('readonly'=>'readonly','style'=>'width:95%')).'<br /><label>'.Eleanor::Check('_cleanfla',$values['_cleanfla']).' '.static::$lang['clean'].'</label>')
				->item(static::$lang['register'],$values['register'])
				->item(static::$lang['last_visit'],$values['last_visit']);
			if($axauth)
				$Lst->item(static::$lang['externals'],$axauth);

			if($values['_sessions'])
			{				$images=Eleanor::$Template->default['theme'].'images/';
				$bicons=array(
					'opera'=>array('images/browsers/opera.png','Opera'),
					'firefox'=>array('images/browsers/firefox.png','Mozilla Firefox'),
					'chrome'=>array('images/browsers/chrome.png','Google Chrome'),
					'safari'=>array('images/browsers/safari.png','Apple Safari'),
					'msie'=>array('images/browsers/ie.png','Microsoft Internet Explore'),
				);
				$Ls=Eleanor::LoadListTemplate('table-list',4)
					->begin(
						array('Browser &amp; IP','colspan'=>2,'tableextra'=>array('id'=>'sessions')),
						static::$lang['datee'],
						array($ltpl['delete'],70)
					);

				foreach($values['_sessions'] as $cl=>&$sess)
				{					$uses='';
					foreach(Eleanor::$services as $kk=>&$vv)
						if('Login'.ucfirst($vv['login'])==$cl)
							$uses.=$kk.', ';

					$Ls->empty($cl.' ('.rtrim($uses,', ').')');
					foreach($sess as $k=>&$v)
					{
						$icon=$iconh=false;
						foreach($bicons as $br=>$brv)
							if(stripos($v[2],$br)!==false)
							{
								$icon=$brv[0];
								$iconh=$brv[1];
								break;
							}

						$ua=htmlspecialchars($v[2],ELENT,CHARSET);
						if($v['_candel'])
						{
							$del=$Ls('func',
								array('#',$ltpl['delete'],$images.'delete.png','extra'=>array('data-key'=>$k,'data-cl'=>$cl))
							);
							$del[1]='center';
						}
						else
							$del=array('<b title="'.static::$lang['csnd'].'">&mdash;</b>','center');

						$Ls->item(
							$icon ? array('<a href="#" data-ua="'.$ua.'"><img title="'.$iconh.'" src="'.$icon.'" /></a>','style'=>'width:16px') : array('<a href="#" data-ua="'.$ua.'">?</a>','center'),
							array($v[1],'center','href'=>'http://eleanor-cms.ru/whois/'.$v[1],'hrefextra'=>array('target'=>'_blank')),
							array(Eleanor::$Language->Date($v[0],'fdt'),'center'),
							$del
						);
					}
				}

				$stats=(string)$Lst->head(static::$lang['sessions'])->end().$Ls->end().'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#sessions").on("click","a[data-key]",function(){
		var th=$(this);
		CORE.Ajax({
				direct:"admin",
				file:"users",
				event:"killsession",
				key:th.data("key"),
				cl:th.data("cl"),
				uid:"'.$id.'"
			},
			function()
			{
				th.closest("tr").remove();
			}
		);
		return false;
	}).on("click","a[data-ua]",function(){
		alert($(this).data("ua"));
		return false;
	});
});//]]></script>';
			}
			else
				$stats=(string)$Lst->end();
		}

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst->form(array('id'=>'form','data-pmm'=>static::$lang['PASSWORD_MISMATCH']))
			->tabs(
				array(static::$lang['general'],$general),
				array(static::$lang['extra'],$extra),
				array(static::$lang['special'],$special),
				array(static::$lang['block'],$block),
				$id ? array(static::$lang['statistics'],$stats) : false
			)
			->submitline($back.Eleanor::Button().($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover((string)$Lst,$errors,'error');	}

	/*
		Элемент шаблона: загрузка галерей
		$galleries - массив галерей, каждый элемент массива - массив с ключами:
			n - имя галереи
			i - путь к картинке относительно корня сайта
			d - описание галереи
	*/
	public static function Galleries($galleries)
	{		$c='';
		foreach($galleries as &$v)
			$c.='<a href="#" class="gallery" data-gallery="'.$v['n'].'"><b><img src="'.$v['i'].'" alt="" /><span>'.$v['d'].'</span></b></a>';
		return$c ? '<a class="imagebtn cancelavatar" href="#">'.static::$lang['cancel_avatar'].'</a><div class="clr"></div><div class="galleryavatars">'.$c.'</div>' : '<div class="noavatars cancelavatar">'.static::$lang['no_avatars'].'</div>';	}

	/*
		Элемент шаблона: загрузка аватаров
		$avatar - массив аватаров, каждый элемент массива - массив с ключами:
			p - путь к файлу, относительно корня сайта, с закрывающим слешем
			f - имя файла
	*/
	public static function Avatars($avatars)
	{		$c='';
		foreach($avatars as &$v)
			$c.='<a href="#" class="applyavatar" title="'.$v['f'].'"><img src="'.join($v).'" /></a>';
		return$c ? '<a class="imagebtn getgalleries" href="#">'.static::$lang['togals'].'</a><a class="imagebtn cancelavatar" href="#">'.static::$lang['cancel_avatar'].'</a><div class="clr"></div><div class="avatarscover">'.$c.'</div>' : '<div class="noavatars cancelavatar">'.static::$lang['no_avatars'].'</div>';
	}

	/*
		Шаблон страницы просмотра пользователей онлайн
		$items - массив сессий на сайте. Ключи внутренних массивов:
			type - тип сессии: user, guest или bot (пользователь, гость или поисковый бот) в зависимости от того, кто работает
			user_id - ID пользователя
			enter - дата входа
			expire - дата истечения сессии
			_online - признак пользователя онлайн
			ip_guest - IP адрес для гостя и бота
			ip_user - IP адрес для пользователя
			service - идентификатор сервиса
			browser - USER AGENT устройства пользователя
			location - местоположение пользователя
			botname - имя для бота
			groups - массив групп пользователя
			name - имя для пользователя (не безопасный HTML)
			full_name - полное имя для пользователя
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя либо false
		$groups - массив групп для пользователей. Формат: ID=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
		$cnt - количество сессий всего
		$pp - количество сессий на страницу
		$qs - массив параметров для адресной строки
		$links - перечень необходимых ссылок, массив с ключами:
			sort_ip - ссылка на сортировку списка $items по ip (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_enter - ссылка на сортировку списка $items по дате входа (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_location - ссылка на сортировку списка $items по местоположению (возрастанию/убыванию в зависимости от текущей сортировки)
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function UsersOnline($items,$groups,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('online');
		$ltpl=Eleanor::$Language['tpl'];
		$sess=array(
			static::$lang['awo'],
			static::$lang['alls'],
			static::$lang['allg']
		);

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'online'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',6)
			->begin(
				array(static::$lang['who'],'colspan'=>2,'tableextra'=>array('id'=>'onlinelist')),
				array('IP','sort'=>$qs['sort']=='ip' ? $qs['so'] : false,'href'=>$links['sort_ip']),
				array(static::$lang['ets'],'sort'=>$qs['sort']=='enter' ? $qs['so'] : false,'href'=>$links['sort_enter']),
				array(static::$lang['pl'],'sort'=>$qs['sort']=='location' ? $qs['so'] : false,'href'=>$links['sort_location']),
				array($ltpl['functs'],80)
			);

		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';			$bicons=array(
				'opera'=>array('images/browsers/opera.png','Opera'),
				'firefox'=>array('images/browsers/firefox.png','Mozilla Firefox'),
				'chrome'=>array('images/browsers/chrome.png','Google Chrome'),
				'safari'=>array('images/browsers/safari.png','Apple Safari'),
				'msie'=>array('images/browsers/ie.png','Microsoft Internet Explore'),
			);

			foreach($items as &$v)
			{				$user=$icon=$iconh=false;
				foreach($bicons as $br=>$brv)
					if(stripos($v['browser'],$br)!==false)
					{
						$icon=$brv[0];
						$iconh=$brv[1];
						break;
					}

				switch($v['type'])
				{					case'bot':
						$name='<span class="entry" data-gip="'.$v['ip_guest'].'" data-s="'.$v['service'].'">'.htmlspecialchars($v['botname'],ELENT,CHARSET).'</span>';
					break;
					case'user':
						$name='<a class="entry" href="'.$v['_aedit'].'" data-uid="'.$v['user_id'].'" data-s="'.$v['service'].'"'
							.(isset($v['_group'],$groups[$v['_group']]) ? ' title="'.$groups[$v['_group']]['title'].'">'.$groups[$v['_group']]['html_pref'].htmlspecialchars($v['name'],ELENT,CHARSET).$groups[$v['_group']]['html_end'] : '>'.htmlspecialchars($v['name'],ELENT,CHARSET))
							.'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>');
						$user=true;
					break;
					default:						$name='<i class="entry" data-gip="'.$v['ip_guest'].'" data-s="'.$v['service'].'">'.static::$lang['guest'].'</i>';
				}
				$v['location']=htmlspecialchars($v['location'],ELENT,CHARSET,false);
				$ip=$v['ip_guest'] ? $v['ip_guest'] : $v['ip_user'];
				$loc='<a href="'.$v['location'].'" target="_blank">'.Strings::CutStr($v['location'],100).'</a>';
				$Lst->item(
					$icon ? array('<img title="'.$iconh.'" src="'.$icon.'" />','style'=>'width:1px') : false,
					$icon ? $name : array($name,'colspan'=>2),
					array($ip,'center','href'=>'http://eleanor-cms.ru/whois/'.$ip,'hrefextra'=>array('target'=>'_blank')),
					array(($v['_online'] ? '<span style="color:green" title="'.sprintf(static::$lang['expire'],Eleanor::$Language->Date($v['expire'],'fdt')).'">' : '<span style="color:red" title="'.sprintf(static::$lang['expired'],Eleanor::$Language->Date($v['expire'],'fdt')).'">').Eleanor::$Language->Date($v['enter'],'fdt').'</span>','center'),
					$user ? $loc : array($loc,'colspan'=>2),
					$user ? $Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						$v['_adel'] ? array($v['_adel'],$ltpl['delete'],$images.'delete.png') : false
					) : false
				);
			}
		}
		else
			$Lst->empty(static::$lang['snf']);

		$fisess='';
		foreach($sess as $k=>&$v)
			$fisess.=Eleanor::Option($v,$k,$qs['']['fi']['online']==$k);

		return Eleanor::$Template->Cover(
		'<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.static::$lang['sshow'].'</b><br />'.Eleanor::Select('fi[online]',$fisess).'</td>
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
});//]]></script>
		</form>'
		.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['spp'],$Lst->perpage($pp,$links['pp'])).'</div></div>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page'])));
	}

	/*
		Шаблон страницы поиска пользователей для вставки их в поле выбора пользователя
		$users - массив пользователей. Формат: id=>array(), ключи внутреннего массива:
			name - имя пользователя (не безопасный HTML)
			groups - перечень идов групп, в которых состоит пользователь
			_a - ссылка на пользователя
		$groups - массив групп пользователей. Формат: id=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML суффикс группы
		$total - количество пользователей всего
		$pp - количество пользователей на страницу
		$page - номер текущей страницы, на которой мы находимся
		$values - значение контролов формы для поиска. Массив с ключами:
			name - имя пользователя
		$links - перечень необходимых ссылок, массив с ключи:
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function FindUsers($users,$groups,$total,$pp,$page,$values,$links)
	{		$n=($page-1)*$pp;
		foreach($users as $k=>&$v)
		{			if(isset($groups[$v['_group']]))
			{				$g=&$groups[$v['_group']];				$t=$g['title'];
				$p=$g['html_pref'];
				$e=$g['html_end'];			}
			else
				$t=$p=$e='';			$v=++$n.'. <a href="'.$v['_a'].'" data-id="'.$k.'"'.($t ? ' title="'.$t.'"' : '').'>'.$p.htmlspecialchars($v['name'],ELENT,CHARSET).$e.'</a>';
		}
		return'<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset='.DISPLAY_CHARSET.'" /><title>'.static::$lang['list'].'</title>
<style type="text/css">
	:link, :visited { color: #ff5a00; text-decoration: none; }
	:link:hover, :visited:hover { color: #ff9600; text-decoration: none; }
	ul { margin: 2px 0; padding: 0 0 0 5px; }
	ul li { margin: 5px 0; padding: 0px 0 0px 14px; list-style-type: none; background: none; }
	h2 { font-size: 18px; font-weight: normal; line-height: 133%; margin: 0.5em 0 0.2em 0; }
	input, textarea, select { font-size: 11px; font-family: Tahoma, Helvetica, sans-serif; }
	body, td, div, li { color: #6d6a65; font-size: 11px; font-family: Tahoma, Helvetica, sans-serif; }
	body { text-align: left; height: 100%; line-height: 142%; padding: 0; margin: 20px; background-color: #FFFFFF; }
	.clr {clear:both;}
	hr	{ height: 1px; border: solid #d8d8d8 0px; border-top-width: 1px; }
</style>
<base href="'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'" />
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/core.js" type="text/javascript"></script>
</head>
<body style="text-align: left; margin: 20px;">
<script type="text/javascript">//<![CDATA[
$(function(){	$("table a").click(function(){		window.opener.AuthorSelected($(this).text(),$(this).data("id"),window.name);
		window.close();
		return false;	})});//]]></script>
<table><tr>
	<td colspan="3"><h2>'.static::$lang['list'].'</h2><hr /></td>
	</tr>'.($total==0 ? '<tr><td colspan="3" aling="center"><b>'.static::$lang['unf'].'</b></td></tr>' : '
	<tr>
	<td><ul><li>'.implode('</li><li>',array_splice($users,0,10)).'</li></ul></td>
	<td><ul><li>'.implode('</li><li>',array_splice($users,0,10)).'</li></ul></td>
	<td><ul><li>'.implode('</li><li>',$users).'</li></ul></td>
	</tr>').'<tr><td colspan="3">'.Eleanor::$Template->Pages($total,$pp,$page,array($links['pages'],$links['first_page'])).'<div class="clr"></div><hr /><form method="post">'.Eleanor::Input('name',$values['name'],array('tabindex'=>1)).Eleanor::Button(static::$lang['find'],'submit',array('tabindex'=>2)).'</form></td></tr></table></body></html>';	}

	/*
		Шаблон страницы с редактированием форматов писем
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
	*/
	public static function Letters($controls,$values)
	{		static::Menu('letters');
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);
		return Eleanor::$Template->Cover($Lst->button(Eleanor::Button())->end()->endform());
	}

	/*
		Страница удаления пользователя
		$a - массив удаляемого пользователя, ключи:
			name - имя пользователя
			full_name - полное имя пользователя

		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['name'],$a['full_name']),$back));
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}
TplUsers::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/users-*.php',false);