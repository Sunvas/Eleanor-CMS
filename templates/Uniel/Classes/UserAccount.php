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
class TplUserAccount
{	/*
		Шаблон страницы вывода всех групп пользователей
		$groups - массив всех групп. Формат: id=>array(), ключи внутреннего массива:
			title - название группы
			descr - описание группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
	*/
	public static function AcGroups($groups)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$c=Eleanor::$Template->Title(end($GLOBALS['title']))->OpenTable();
		$Lst=Eleanor::LoadListTemplate('table-list',2)
			->begin($lang['group'],$lang['descr']);
		foreach($groups as $k=>&$v)
			$Lst->item(array($v['html_pref'].$v['title'].$v['html_end'],'traddon'=>array('id'=>'group-'.$k)),$v['descr']);
		return$c.$Lst->end().Eleanor::$Template->CloseTable();
	}

	/*
		Шаблон страницы пользователей онлайн
		$items - массив сессий пользователей онлай
			type - тип пользовательской сессии: guest - гостя, user - пользователя, bot - поискового бота
			user_id - идентификатор пользователя для пользовательской сессии
			enter - время входа
			ip_guest - IP гостя для гостевой сессии
			ip_user - IP пользователя для пользовательской сессии
			browser - USER AGENT устройства пользователя
			location - местоположение пользователя
			botname - имя бота для сессии поискового бота
			_group - группа пользователя для пользовательской сессии
			name - имя пользователя для пользовательской сессии
			full_name - полное имя пользователя для пользовательской сессии
		$groups - массив всех групп. Формат: id=>array(), ключи внутреннего массива:
			title - название группы
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
		$cnt - количество сессий всего
		$pp - сессий на страницу
		$page - номер текущей страницы
	*/
	public static function AcUsersOnline($items,$groups,$cnt,$pp,$page)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];

		$isa=Eleanor::$Permissions->IsAdmin();
		$Lst=Eleanor::LoadListTemplate('table-list',$isa ? 5 : 4)
			->begin(
				array($lang['who'],'colspan'=>2),
				$isa ? 'IP' : false,
				array($lang['activity'],120),
				$lang['pl']
			);

		if($items)
		{
			$bicons=array(
				'opera'=>array('images/browsers/opera.png','Opera'),
				'firefox'=>array('images/browsers/firefox.png','Mozilla Firefox'),
				'chrome'=>array('images/browsers/chrome.png','Google Chrome'),
				'safari'=>array('images/browsers/safari.png','Apple Safari'),
				'msie'=>array('images/browsers/ie.png','Microsoft Internet Explore'),
			);
			$t=time();

			foreach($items as &$v)
			{
				$icon=$iconh=false;
				foreach($bicons as $br=>$brv)
					if(stripos($v['browser'],$br)!==false)
					{
						$icon=$brv[0];
						$iconh=$brv[1];
						break;
					}

				switch($v['type'])
				{
					case'bot':
						$name=htmlspecialchars($v['botname'],ELENT,CHARSET);
					break;
					case'user':
						if($v['name'] and $v['user_id'])
						{
							$name='<a href="'.Eleanor::$Login->UserLink($v['name'],$v['user_id']).'"'
								.(isset($v['_group'],$groups[$v['_group']]) ? ' title="'.$groups[$v['_group']]['title'].'">'.$groups[$v['_group']]['html_pref'].htmlspecialchars($v['name'],ELENT,CHARSET).$groups[$v['_group']]['html_end'] : '>'.htmlspecialchars($v['name'],ELENT,CHARSET))
								.'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>');
							break;
						}
					default:
						$name='<i>'.$lang['guest'].'</i>';
				}
				$v['location']=htmlspecialchars($v['location'],ELENT,CHARSET,false);
				if($isa)
					$ip=$v['ip_guest'] ? $v['ip_guest'] : $v['ip_user'];
				$loc='<a href="'.$v['location'].'" target="_blank">'.Strings::CutStr($v['location'],100).'</a>';
				$Lst->item(
					$icon ? array('<img title="'.$iconh.'" src="'.$icon.'" />','style'=>'width:16px') : false,
					$icon ? $name : array($name,'colspan'=>2),
					$isa ? array($ip,'center','href'=>'http://eleanor-cms.ru/whois/'.$ip,'hrefaddon'=>array('target'=>'_blank')) : false,
					array($ltpl['minutes_ago'](floor(($t-strtotime($v['enter']))/60)),'center'),
					$loc
				);
			}
		}
		else
			$Lst->empty($lang['snf']);

		return Eleanor::$Template->Title(end($GLOBALS['title']))->OpenTable().$Lst->end().Eleanor::$Template->Pages($cnt,$pp,$page)->CloseTable();
	}

	/*
		Шаблон основной страница аккаунта.
		Пустое место зарезервирована под нужды проектов, реализуемых на системе
		$sessions - открытые сессии пользователя, формат: ключ=>массив, ключи внутреннего массива:
			0 - TIMESTAMP истечения активности
			1 - IP адрес
			2 - USER AGENT браузера
			_candel - флаг возможности удаления сессии
	*/
	public static function AcMain($sessions)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];

		$C=static::Menu('user','index','main')
			->Title('Открытые сессии');

		$Lst=Eleanor::LoadListTemplate('table-list',3)
			->begin(
				array('Browser &amp; IP','colspan'=>2,'tableaddon'=>array('id'=>'sessions')),
				$lang['datee'],
				array($ltpl['delete'],50)
			);

		$images='templates/Audora/images/';
		$bicons=array(
			'opera'=>array('images/browsers/opera.png','Opera'),
			'firefox'=>array('images/browsers/firefox.png','Mozilla Firefox'),
			'chrome'=>array('images/browsers/chrome.png','Google Chrome'),
			'safari'=>array('images/browsers/safari.png','Apple Safari'),
			'msie'=>array('images/browsers/ie.png','Microsoft Internet Explore'),
		);

		foreach($sessions as $k=>&$v)
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
			{				$del=$Lst('func',
					array('#',$ltpl['delete'],$images.'delete.png','addon'=>array('data-key'=>$k))
				);
				$del[1]='center';			}
			else
				$del=array('<b title="'.$lang['csnd'].'">&mdash;</b>','center');

			$Lst->item(
				$icon ? array('<a href="#" data-ua="'.$ua.'"><img title="'.$iconh.'" src="'.$icon.'" /></a>','style'=>'width:16px') : array('<a href="#" data-ua="'.$ua.'">?</a>','center'),
				array($v[1],'center','href'=>'http://eleanor-cms.ru/whois/'.$v[1],'hrefaddon'=>array('target'=>'_blank')),
				array(Eleanor::$Language->Date($v[0],'fdt'),'center'),
				$del
			);
		}

		return$C.$Lst->end().'<script type="text/javascript">//<![CDATA[
$(function(){	$("#sessions").on("click","a[data-key]",function(){		var th=$(this);
		CORE.Ajax({				module:"'.$GLOBALS['Eleanor']->module['name'].'",
				language:CORE.language,
				event:"killsession",
				key:th.data("key")			},
			function()
			{				th.closest("tr").remove();			}
		);		return false;	}).on("click","a[data-ua]",function(){		alert($(this).data("ua"));
		return false;	});});//]]></script>';
	}

	/*
		Шаблон страницы формы входа пользователя
		$values - массив значений полей:
			name - имя пользователя
			password - пароль пользователя
		$back - URL возврата
		$errors - массив ошибок
		$captcha - капча при входе
		$links - массив ссылок, ключи:
			login - ссылка на скрипт, обрабатывающий данные из формы входа
	*/
	public static function AcLogin($values,$back,$errors,$captcha,$links)
	{		$ltpl=Eleanor::$Language['tpl'];
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		$C=static::Menu('guest','index','main');
		if($errors)
		{			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
			$C->Message(join('<br />',$errors),'error');
		}

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form($links['login'])
			->begin()
			->item($ltpl['login'],Eleanor::Edit('login[name]',$values['name'],array('style'=>'width:300px','tabindex'=>1)))
			->item($ltpl['pass'],Eleanor::Control('login[password]','password',$values['password'],array('style'=>'width:300px','tabindex'=>2)));

		if($captcha)
			$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>3)),'descr'=>$lang['captcha_']));

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		#Метод отвечает за внешний вид интерфейса для входа пользователя
		return$C.$Lst->button($back.Eleanor::Button($ltpl['enter'],'submit',array('tabindex'=>5)))->end()->endform()
			.'<div style="text-align:center">'.Eleanor::LoadFileTemplate(Eleanor::$root.Eleanor::$Template->default['theme'].'Static/external_auth.php').'</div>';
	}

	/*
		Шаблон страница регистрации пользователя
		$values - массив значений полей, ключи:
			_external - массив, доступен только при регистрации с использованием внешнего сервиса, ключи:
				nickname - имя пользователя из внешего сервиса
			name - имя пользователя
			full_name - полное имя
			email - e-mail пользователя
			password - пароль
			password - повторение пароля
		$captcha - капча
		$errors - массив ошибок. Формат int=>code, либо code=>error, где int - целое число не имеющее никакого отношение к ошибке, возможные code:
			PASSWORD_MISMATCH - пароли не совпадают
			PASS_TOO_SHORT - пароль слишком короткий
			EMPTY_EMAIL - пусто e-mail адрес
			EMAIL_EXISTS - e-mail уже занят другим пользователем
			EMAIL_BLOCKED - e-mail заблокирован
			NAME_TOO_LONG - имя слишком длинное
			EMPTY_NAME - пустое имя
			NAME_EXISTS - имя уже занято другим пользователем
			NAME_BLOCKED - имя заблокировано
			WRONG_CAPTCHA - неправильный защитный код
	*/
	public static function AcRegister($values,$captcha,$errors)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		if(Eleanor::$vars['reg_off'])
			return static::AcMenu($handlers)->Message($lang['reg_off'],'error');

		array_push($GLOBALS['jscripts'],'js/module_account.js','js/module_account-'.Language::$main.'.js');

		$C=static::Menu('guest','register','main');

		$errname=$erremail=$errpass=$errpass2='';
		if($errors)
		{			foreach($errors as $k=>&$v)
			{				if(is_int($k))
				{					$code=$v;
					$error=isset($lang[$v]) ? $lang[$v] : $v;				}
				else
				{					$code=$k;
					$error=$v;				}

				switch($code)
				{					case'PASSWORD_MISMATCH':
						$errpass2=$error;
						unset($errors[$k]);
					break;
					case'PASS_TOO_SHORT':
						$errpass=$error;
						unset($errors[$k]);
					break;
					case'EMPTY_EMAIL':
					case'EMAIL_EXISTS':
					case'EMAIL_BLOCKED':
						$erremail=$error;
						unset($errors[$k]);
					break;
					case'NAME_TOO_LONG':
					case'EMPTY_NAME':
					case'NAME_EXISTS':
					case'NAME_BLOCKED':
						$errname=$error;
						unset($errors[$k]);
					break;
					default:
						$v=$error;				}			}

			if($errors)
				$C->Message(join('<br />',$errors),'error');
		}
		if(isset($values['_external']))
			$C->Message(sprintf($lang['external_reg'],empty($values['_external']['nickname']) ? ($values['full_name'] ? $values['full_name'] : 'Anonym') : $values['_external']['nickname']),'info');

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form(array('id'=>'regform'))
			->begin()
			->item(array($lang['name'],Eleanor::Edit('name',$values['name'],array('tabindex'=>1,'style'=>'width:80%','placeholder'=>$lang['enter_g_name'])).' <a href="#" title="'.$lang['check'].'"><img src="'.Eleanor::$Template->default['theme'].'images/no_dublicate.png" alt="" /></a><div id="name-error" style="color:red;display:none;">'.$errname.'</div>','tip'=>$lang['name_'],'imp'=>true,'td1'=>array('style'=>'width:150px;')))
			->item($lang['full_name'],Eleanor::Edit('full_name',$values['full_name'],array('tabindex'=>2,'style'=>'width:80%')))
			->item(array('E-mail',Eleanor::Edit('email',$values['email'],array('tabindex'=>3,'style'=>'width:80%','placeholder'=>$lang['enter_g_email'])).' <a href="#" title="'.$lang['check'].'"><img src="'.Eleanor::$Template->default['theme'].'images/no_dublicate.png" alt="" /></a><div id="email-error" style="color:red;display:none;">'.$erremail.'</div>','tip'=>$lang['email_'],'imp'=>true))
			->item(array($lang['pass'],Eleanor::Control('password','password',$values['password'],array('tabindex'=>4)).'<div id="password-error" style="color:red;display:none;">'.$errpass.'</div>','tip'=>$lang['pass_']))
			->item($lang['rpass'],Eleanor::Control('password2','password',$values['password2'],array('tabindex'=>5)).'<div id="password2-error" style="color:red;display:none;">'.$lang['PASSWORD_MISMATCH'].'</div>');
		if($captcha)
			$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>6)),'tip'=>$lang['captcha_']));

		return Eleanor::JsVars(
				array(
					'module'=>$GLOBALS['Eleanor']->module['name'],
					'max_name'=>Eleanor::$vars['max_name_length'],
				),
				true,false,'CORE.AcRegister.'
		).$C.$Lst->item('',Eleanor::Button($lang['do_reg'],'submit',array('tabindex'=>7)))->end()->endform()
		.'<script type="text/javascript">//<![CDATA[
$(function(){	var ef={//Error field		name:$("#name-error"),
		email:$("#email-error"),
		p:$("#password-error"),
		p2:$("#password2-error")	};	$("#regform [name=name]").on("check",function(){		var th=$(this),
			v=th.val();
		if(v=="")		{
			th.removeClass("ok").addClass("error");
			ef.name.html("'.$lang['EMPTY_NAME'].'").show();
		}
		else
			CORE.AcRegister.CheckName(v,function(e){				if(e)
				{
					th.removeClass("ok").addClass("error");
					ef.name.html(e).show();
				}
				else
				{
					th.removeClass("error").addClass("ok");
					ef.name.hide();
				}			});
	})'.($errname ? '.addClass("error");ef.name.show()' : '').';

	$("#regform [name=email]").on("check",function(){
		var th=$(this),
			v=th.val();
		if(v)
			CORE.AcRegister.CheckEmail(v,function(e){
				if(e)
				{
					th.removeClass("ok").addClass("error");
					ef.email.html(e).show();
				}
				else
				{
					th.removeClass("error").addClass("ok");
					ef.email.hide();
				}
			});
		else
		{
			th.removeClass("ok").addClass("error");
			ef.email.html("'.$lang['EMPTY_EMAIL'].'").show();
		}
	})'.($erremail ? '.addClass("error");ef.email.show()' : '').';

	$("#name-error,#email-error").prev().click(function(){		$(this).prev().trigger("check");		return false;	});

	var p2=$("#regform [name=password2]"),
		p1=$("#regform [name=password]").on("check",function(){
		var th=$(this),
			v=th.val();

		if(v=="")
		{
			th.removeClass("ok error");
			ef.p.hide();
		}
		else if(v.length<'.Eleanor::$vars['min_pass_length'].')
		{			th.removeClass("ok").addClass("error");
			ef.p.html(CORE.lang.PASS_TOO_SHORT('.Eleanor::$vars['min_pass_length'].',v.length)).show();		}
		else
		{
			th.removeClass("error").addClass("ok");
			ef.p.hide();
			if(p2.val()!="")
				p2.trigger("check");
		}
	})'.($errpass ? '.addClass("error");ef.p.show()' : '').';

	p2.on("check",function(){
		if(!ef.p.is(":visible"))
			if(p2.val()!=p1.val())
			{				p2.removeClass("ok").addClass("error");
				ef.p2.html("'.$lang['PASSWORD_MISMATCH'].'").show();
			}
			else
			{				ef.p2.hide();
				p2.removeClass("error");				if(p2.val()=="")
					p2.removeClass("ok");
				else
					p2.addClass("ok");
			}
	})'.($errpass2 ? '.addClass("error");ef.p2.show()' : '').';

	$("#regform").submit(function(){		$(":input",this).trigger("check");		var errors="";
		$.each(ef,function(k,v){			if(v.is(":visible"))
				errors+=v.text()+"\n";		});
		if(errors)
		{			alert($.trim(errors));			return false;		}
		return true;	})
	.find("[name=name],[name=email],[name=password],[name=password2]").keyup(function(){
		var th=$(this);
		if(th.data("old")!=th.val())
			th.removeClass("ok error");
	}).blur(function(){		$(this).data("old",$(this).val()).trigger("check");	});});//]]></script>';
	}

	/*
		Шаблон страницы успешного завершения регистрации
	*/
	public static function AcSuccessReg()
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::Menu('guest','register','main')->Message($lang['success_reg'],'info');
	}

	/*
		Шаблон страницы завершения регистрации: ожидание активации учетной записи.
		$byadmin - флаг активации учетной записи администратором
	*/
	public static function AcWaitActivate($byadmin)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::Menu('guest','register','main')->Message($byadmin ? $lang['wait_act_admin'] : $lang['wait_act_text'](round(Eleanor::$vars['reg_act_time']/3600)),'info');
	}

	/*
		Шаблон страницы первого шага восстановления пароля: форма
		$values - массив значений полей, ключи:
			name - имя пользователя
			email - e-mail пользователя
		$captcha - капча, либо false
		$errors - массив ошибок
	*/
	public static function AcRemindPass($values,$captcha,$errors)
	{
		$ltpl=Eleanor::$Language['tpl'];
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		$C=static::Menu('guest','lostpass','main');
		if($errors)
		{
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
			$C->Message(join('<br />',$errors),'error');
		}
		$C->Message($lang['notnoem'],'info');

		$em=$values['email'] and !$values['name'];
		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin(array('id'=>'rpass'))
			->item(array($lang['enterna'],Eleanor::Edit('name',$values['name'],array('tabindex'=>1)).'<br /><a href="#" class="small">'.$lang['fogotname'].'</a>','tr'=>array('id'=>'tr-name','style'=>$em ? 'display:none' : ''),'td1'=>array('style'=>'width:170px')))
			->item(array($lang['enterem'],Eleanor::Edit('email',$values['email'],array('tabindex'=>2)).'<br /><a href="#" class="small">'.$lang['fogotemail'].'</a>','tr'=>array('id'=>'tr-email','style'=>$em ? '' : 'display:none'),'td1'=>array('style'=>'width:170px')));
		if($captcha)
			$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>3)),'tip'=>$lang['captcha_']));

		$Lst->button(Eleanor::Button('OK','submit',array('tabindex'=>4)))->end()->endform();
		return$C.$Lst.'<script type="text/javascript">//<![CDATA[
$(function(){	$("#rpass a.small").click(function(){		$("#tr-email,#tr-name").toggle();
		return false;	});
})//]]></script>';
	}

	/*
		Страница второго шага восстановления пароля: для проложения необходимо перейти по ссылке, отправленной на мыло
	*/
	public static function AcRemindPassStep2()
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::Menu('guest','lostpass','main')->Message($lang['wait_pass1_text'],'info');
	}

	/*
		Шаблон страницы третьего (опциального) шага: ввода нового пароля после того, как пользователь перешел по ссылке с письма
		$values - массив значений полей, ключи:
			password - пароль
			password2 - повторение пароля
		$captcha - капча
		$errors - массив ошибок. Формат int=>code, либо code=>error, где int - целое число не имеющее никакого отношение к ошибке, возможные code:
			PASSWORD_MISMATCH - пароли не совпадают
			PASS_TOO_SHORT - пароль слишком короткий
			WRONG_CAPTCHA - неправильный защитный код
	*/
	public static function AcRemindPassStep3($values,$captcha,$errors=array())
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$GLOBALS['jscripts'][]='js/module_account-'.Language::$main.'.js';
		$C=static::Menu('guest','lostpass','main');
		$errpass=$errpass2='';
		if($errors)
		{
			foreach($errors as $k=>&$v)
			{
				if(is_int($k))
				{
					$code=$v;
					$error=isset($lang[$v]) ? $lang[$v] : $v;
				}
				else
				{
					$code=$k;
					$error=$v;
				}

				switch($code)
				{
					case'PASSWORD_MISMATCH':
						$errpass2=$error;
						unset($errors[$k]);
					break;
					case'PASS_TOO_SHORT':
						$errpass=$error;
						unset($errors[$k]);
					break;

					default:
						$v=$error;
				}
			}

			if($errors)
				$C->Message(join('<br />',$errors),'error');
		}

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form(array('id'=>'newpass'))
			->begin()
			->item(array($lang['ent_newp'],Eleanor::Control('password','password',$values['password'],array('tabindex'=>1)).'<div id="password-error" style="color:red;display:none;">'.$errpass.'</div>','tip'=>$lang['pass_'],'td1'=>array('style'=>'width:200px')))
			->item($lang['rep_newp'],Eleanor::Control('password2','password',$values['password2'],array('tabindex'=>2)).'<div id="password2-error" style="color:red;display:none;">'.$lang['PASSWORD_MISMATCH'].'</div>');
		if($captcha)
			$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>3)),'descr'=>$lang['captcha_']));

		return$C.$Lst->button(Eleanor::Button('OK','submit',array('tabindex'=>4)))->end()->endform()
			.'<script type="text/javascript">//<!CDATA[
$(function(){	var ef={//Error field
			p:$("#password-error"),
			p2:$("#password2-error")
		},
		p2=$("#newpass [name=password2]"),
		p1=$("#newpass [name=password]").on("check",function(){
		var th=$(this),
			v=th.val();

		if(v=="")
		{
			th.removeClass("ok error");
			ef.p.hide();
		}
		else if(v.length<'.Eleanor::$vars['min_pass_length'].')
		{
			th.removeClass("ok").addClass("error");
			ef.p.html(CORE.lang.PASS_TOO_SHORT('.Eleanor::$vars['min_pass_length'].',v.length)).show();
		}
		else
		{
			th.removeClass("error").addClass("ok");
			ef.p.hide();
			if(p2.val()!="")
				p2.trigger("check");
		}
	})'.($errpass ? '.addClass("error");ef.p.show()' : '').';

	p2.on("check",function(){
		if(!ef.p.is(":visible"))
			if(p2.val()!=p1.val())
			{
				p2.removeClass("ok").addClass("error");
				ef.p2.html("'.$lang['PASSWORD_MISMATCH'].'").show();
			}
			else
			{
				ef.p2.hide();
				p2.removeClass("error");
				if(p2.val()=="")
					p2.removeClass("ok");
				else
					p2.addClass("ok");
			}
	})'.($errpass2 ? '.addClass("error");ef.p2.show()' : '').';

	$("#newpass").submit(function(){
		$(":input",this).trigger("check");
		var errors="";
		$.each(ef,function(k,v){
			if(v.is(":visible"))
				errors+=v.text()+"\n";
		});
		if(errors)
		{
			alert($.trim(errors));
			return false;
		}
		return true;
	})
	.find("[name=password],[name=password2]").keyup(function(){
		var th=$(this);
		if(th.data("old")!=th.val())
			th.removeClass("ok error");
	}).blur(function(){
		$(this).data("old",$(this).val()).trigger("check");
	});})//]]></script>';
	}

	/*
		Шаблон страницы четвертого (завершающего) шага смены пароля пользователю: вывод информации об успешной операции
		$passsent - флаг когда новый пароля выслан на e-mail и для получения этого пароля, необходио проверить e-mail
		$user - массив данных пользователя, ключи:
			name - имя пользователя (не безопасный HTML)
			full_name - полное имя пользователя
			email - e-mail пользователя
	*/
	public static function AcRemindPassSent($passsent,$user)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::Menu('guest','lostpass','main')->Message($passsent ? $lang['new_pass_sent'] : $lang['pass_changed'],'info');
	}

	/*
		Шаблон страницы с результатом активации учетной записи
		$success - флаг успешной активации
	*/
	public static function AcActivate($success)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$C=static::Menu('user','activate','main');
		if($success)
			return$C->Message($lang['activation_ok'],'info');
		return$C->Message($lang['activation_err'],'error');
	}

	/*
		Шаблон страницы с формой повторной актиации
		$sent - флаг успешной повторной активации
		$captcha - капча
		$errors - массив ошибок
		$hours - при включенном флаге $sent опредяет количество часов, оставшихся для активации
	*/
	public static function AcReactivation($sent,$captcha,$errors,$hours)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		$C=static::Menu('user','activate','new');
		if($errors)
		{
			foreach($errors as $k=>&$v)
				if(is_int($k))
				{
					$code=$v;
					if(isset($lang[$v]))
						$v=$lang[$v];
				}

			$C->Message(join('<br />',$errors),'error');
		}
		if($sent)
			$C->Message($lang['wait_new_act']($hours),'info');
		else
		{
			$Lst=Eleanor::LoadListTemplate('table-form')
				->form()
				->begin();

			if($captcha)
				$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>1)),'descr'=>$lang['captcha_'],'td1'=>array('addon')));

			$C.=$Lst->button(Eleanor::Button($lang['ractletter'],'submit',array('tabindex'=>2)))
				->end()->endform();
		}
		return$C;
	}

	/*
		Шаблон страницы изменения электронной почты
		$values - масив значений полей, ключи:
			email - электронная почта
		$captcha - капча
		$errors - массив ошибок
	*/
	public static function AcEmailChange($values,$captcha,$errors)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		$C=static::Menu('user','changeemail','main');
		if($errors)
		{
			foreach($errors as $k=>&$v)
				if(is_int($k))
				{
					$code=$v;
					if(isset($lang[$v]))
						$v=$lang[$v];
				}

			$C->Message(join('<br />',$errors),'error');
		}

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item(array($lang['curr_email'],($em=Eleanor::$Login->GetUserValue('email')) ? $em : '&mdash;','td1'=>array('style'=>'width:200px')))
			->item($lang['new_email'],Eleanor::Edit('email',$values['email'],array('tabindex'=>1)));

		if($captcha)
			$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>1)),'descr'=>$lang['captcha_'],'td1'=>array('addon')));

		return$C.$Lst->button(Eleanor::Button($lang['continue'],'submit',array('tabindex'=>3)))->end()->endform();
	}

	/*
		Шаблон страницы шага 1 и 2 изменения электронной почты.
		Первый шаг - ожидание перехода по ссылке, отправленной на старый e-mail.
		Второй шаг - ожидание перехода по ссылке, отправленной на новый (введенный) e-mail.
		$step - идентификатор шага: 1 или 2.
	*/
	public static function AcEmailChangeSteps12($step)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::Menu('user','changeemail','main')->Message($lang['wait_change'.$step],'info');
	}

	/*
		Шаблон страницы успешного завершения изменения e-mail адреса
	*/
	public static function AcEmailChangeSuccess()
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::Menu('user','changeemail','main')->Message($lang['email_success'],'info');
	}

	/*
		Шаблон страницы изменения пароля
	*/
	public static function AcNewPass($success,$errors,$values)
	{		$GLOBALS['jscripts'][]='js/module_account-'.Language::$main.'.js';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		Eleanor::LoadOptions('user-profile');
		$C=static::Menu('user','changepass','main');

		$errpass=$errpass2='';
		if($errors)
		{
			foreach($errors as $k=>&$v)
			{
				if(is_int($k))
				{
					$code=$v;
					$error=isset($lang[$v]) ? $lang[$v] : $v;
				}
				else
				{
					$code=$k;
					$error=$v;
				}

				switch($code)
				{
					case'PASSWORD_MISMATCH':
						$errpass2=$error;
						unset($errors[$k]);
					break;
					case'PASS_TOO_SHORT':
						$errpass=$error;
						unset($errors[$k]);
					break;

					default:
						$v=$error;
				}
			}

			if($errors)
				$C->Message(join('<br />',$errors),'error');
		}
		elseif($success)
			$C->Message($lang['pass_changed'],'info');

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form(array('id'=>'newpass'))
			->begin()
			->head($lang['your_curr_pass'])
			->item(array($lang['en_ycp'],Eleanor::Control('old','password',$values['old'],array('tabindex'=>1)),'td1'=>array('style'=>'width:200px')))
			->head($lang['new_pass_me'])
			->item($lang['ent_newp'],Eleanor::Control('password','password',$values['password'],array('tabindex'=>2)).'<div id="password-error" style="color:red;display:none;">'.$errpass.'</div>')
			->item($lang['rep_newp'],Eleanor::Control('password2','password',$values['password2'],array('tabindex'=>3)).'<div id="password2-error" style="color:red;display:none;">'.$lang['PASSWORD_MISMATCH'].'</div>')
			->button(Eleanor::Button('OK','submit',array('tabindex'=>4)))
			->end()
			->endform();

		return$C.$Lst.'<script type="text/javascript">//<!CDATA[
$(function(){
	var ef={//Error field
			p:$("#password-error"),
			p2:$("#password2-error")
		},
		p2=$("#newpass [name=password2]"),
		p1=$("#newpass [name=password]").on("check",function(){
		var th=$(this),
			v=th.val();

		if(v.length<'.Eleanor::$vars['min_pass_length'].')
		{
			th.removeClass("ok").addClass("error");
			ef.p.html(CORE.lang.PASS_TOO_SHORT('.Eleanor::$vars['min_pass_length'].',v.length)).show();
		}
		else
		{
			th.removeClass("error").addClass("ok");
			ef.p.hide();
			if(p2.val()!="")
				p2.trigger("check");
		}
	})'.($errpass ? '.addClass("error");ef.p.show()' : '').';

	p2.on("check",function(){
		if(!ef.p.is(":visible"))
			if(p2.val()!=p1.val())
			{
				p2.removeClass("ok").addClass("error");
				ef.p2.html("'.$lang['PASSWORD_MISMATCH'].'").show();
			}
			else
			{
				ef.p2.hide();
				p2.removeClass("error");
				if(p2.val()=="")
					p2.removeClass("ok");
				else
					p2.addClass("ok");
			}
	})'.($errpass2 ? '.addClass("error");ef.p2.show()' : '').';

	$("#newpass").submit(function(){
		$(":input",this).trigger("check");
		var errors="";
		$.each(ef,function(k,v){
			if(v.is(":visible"))
				errors+=v.text()+"\n";
		});
		if(errors)
		{
			alert($.trim(errors));
			return false;
		}
		return true;
	})
	.find("[name=password],[name=password2]").keyup(function(){
		var th=$(this);
		if(th.data("old")!=th.val())
			th.removeClass("ok error");
	}).blur(function(){
		$(this).data("old",$(this).val()).trigger("check");
	});
})//]]></script>';
	}

	/*
		Шаблон, формирующий шапку меню. Меню необходимо брать из статического метода Menu() классов, которые находятся в массиве $GLOBALS['Eleanor']->module['handlers'],
		ключи которого являются названиями обработчиков, а значения - именами классов, которые реализуют данные обработчики.
		$section - секция модуля. Это может быть user или guest
		$ih - индекс обработчика активного пункта меню
		$im - индекс пункта меню
	*/
	protected static function Menu($section='',$ih='',$im='')
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];

		$menu=array();
		foreach($GLOBALS['Eleanor']->module['handlers'] as $k=>&$v)
			if(method_exists($v,'Menu') and $a=$v::Menu())
				$menu[$k]=$a;

		$rmenu=array(
			'index'=>array(),
		);

		$actmess=false;
		foreach($menu as $k=>&$v)
		{			$rmenu[$k]=array();
			foreach($v as $kk=>&$vv)
				if($vv)
				{					$act=$k==$ih and $kk==$im;
					switch(array($section,$k,$kk))
					{						case array('user','index','main'):
							$t=$lang['main'];
						break;
						case array('user','settings','main'):
							$t=$lang['settings'];
						break;
						case array('user','activate','new'):
							if(!$act)
								$actmess=$vv;
							$t=false;
						break;
						case array('user','changeemail','main'):
							$t=$lang['change_email'];
						break;
						case array('user','changepass','main'):
							$t=$lang['change_pass'];
						break;
						case array('user',0,'externals'):
							$t=$lang['externals'];
						break;
						case array('guest','index','main'):
							$t=$ltpl['enter'];
						break;
						case array('guest','register','main'):
							$t=$ltpl['register'];
						break;
						case array('guest','lostpass','main'):
							$t=$ltpl['lostpass'];
						break;
						default:
							$t=false;
					}
					if($t)
						$rmenu[$k]+=array($act ? false : $vv,$t,'act'=>$act);
				}
		}

		$menu=Eleanor::$Template->Menu(array('title'=>end($GLOBALS['title']),'menu'=>$rmenu));
		if($actmess)
			$menu->Message($lang['please_activate'](round($actmess['remain']/3600),$actmess['link']),'info');
		return$menu;
	}

	public static function AcOptions($controls,$values,$avatar,$errors,$saved)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];
		list($awidth,$aheight)=explode(' ',Eleanor::$vars['avatar_size']);

		$tabs=array();
		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item(
				$lang['alocation'],
				Eleanor::Select(
					'_atype',
					Eleanor::Option($lang['agallery'],'gallery',!$values['_aupload'])
					.Eleanor::Option($lang['apersonal'],'upload',$values['_aupload']),
					array('id'=>'atype','tabindex'=>10)
				)
			)
			->item(
				$lang['amanage'],
				Eleanor::Control('avatar_location','hidden',$values['avatar_location'],array('id'=>'avatar-input'))
				.'<div id="avatar-local">
					<div id="avatar-select"></div>
					<div id="avatar-view">
						<a class="imagebtn getgalleries" href="#">'.$lang['gallery_select'].'</a><div class="clr"></div>
						<span id="avatar-no" style="width:'.($awidth ? $awidth : '180').'px;height:'.($aheight ? $aheight : '145').'px;text-decoration:none;max-height:100%;max-width:100%;" class="screenblock">
							<b>'.$lang['noavatar'].'</b><br />
							<span>'.sprintf('<b>%s</b> <small>x</small> <b>%s</b> <small>px</small>',$awidth ? $awidth : '&infin;',$aheight ? $aheight : '&infin;').'</span>
						</span>
						<img id="avatar-image" style="border:1px solid #c9c7c3;max-width:'.($awidth>0 ? $awidth.'px' : '100%').';max-height:'.($aheight>0 ? $aheight.'px' : '100%').'" src="images/spacer.png" /><div class="clr"></div>
						<a id="avatar-delete" class="imagebtn" href="#">'.$ltpl['delete'].'</a>
					</div>
				</div>
				<div id="avatar-upload">'.$avatar.'</div>
				<script type="text/javascript">//<![CDATA[
				$(function(){
					var ai=$("#avatar-input").val();
					if(ai)
					{
						$("#avatar-image").attr("src",ai);
						$("#avatar-delete").show();
						$("#avatar-no").hide();
					}
					else
						$("#avatar-image,#avatar-delete").hide();

					$("#atype").change(function(){
						if($(this).val()=="upload")
						{
							$("#avatar-view").hide();
							$("#avatar-upload").show();
						}
						else
						{
							$("#avatar-upload").hide();
							$("#avatar-view").show();
						}
					}).change();

					var g=false;
					$("#form").on("click",".getgalleries",function(){
						if(g)
						{
							$("#avatar-view").hide();
							$("#avatar-select").html(g).show();
						}
						else
							CORE.Ajax(
								{
									module:"'.$GLOBALS['Eleanor']->module['name'].'",
									lang:CORE.language,
									"do":"settings",
									event:"galleries"
								},
								function(r)
								{
									$("#avatar-view").hide();
									$("#avatar-select").html(r).show();
									g=r;
								}
							);
						return false;
					});

					var galleries=[];
					$("#form")
					.on("click",".cancelavatar",function(){
						$("#avatar-select").hide();
						$("#avatar-view").show();
						return false;
					})
					.on("click",".gallery",function(){
						var v=$(this).data("gallery")
						if(galleries[v])
							$("#avatar-select").html(galleries[v]);
						else
							CORE.Ajax(
								{
									module:"'.$GLOBALS['Eleanor']->module['name'].'",
									lang:CORE.language,
									"do":"settings",
									event:"avatars",
									gallery:v
								},
								function(r)
								{
									$("#avatar-select").html(r);
									galleries[v]=r;
								}
							);
						return false;
					})
					.on("click",".applyavatar",function(){
						var f=$("img",this).attr("src");
						$("#avatar-input").val(f);
						$("#avatar-image").attr("src",f).add("#avatar-delete,#avatar-view").show();
						$("#avatar-no,#avatar-select").hide();
						return false;
					});

					$("#avatar-delete").click(function(){
						$("#avatar-input").val("");
						$("#avatar-image,#avatar-delete").hide();
						$("#avatar-no").show();
						return false;
					});
				});//]]></script>');

		$tabs[]=array($lang['avatar'],(string)$Lst->end());
		$head=false;

		$n=0;
		$Lst->begin();
		foreach($controls as $k=>&$v)
		{
			if(is_array($v))
			{
				$Lst->item(array($v['title'],$values[$k],'tip'=>$v['descr'],'td1'=>$n++ ? false : array('style'=>'width:130px')));
			}
			elseif($v)
			{
				if($head)
				{					$tabs[]=array($head,(string)$Lst->end());
					$Lst->begin();
					$avatar='';
				}
				$head=$v;
			}
		}
		$tabs[]=array($head,(string)$Lst->end());

		$C=static::Menu('user','settings','main');
		if($errors)
		{
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
			$C->Message(join('<br />',$errors),'error');
		}
		if($saved)
			$C->Message($lang['optssaved'],'info');

		return$C.$Lst->form(array('id'=>'form'))
			->tabs($tabs)
			->submitline(Eleanor::Button())
			->endform();
	}

	/*
		Страница просмотра пользователя. Данные пользователя нужно брать из массива $GLOBALS['Eleanor']->module['user'], ключи:
			id - идентификатор пользователя
			full_name - полное имя пользователя
			name - логин пользователя (не безопасный HTML)
			register - дата регистрации
			last_visit - дата последнего визита
			language - язык пользовател
			timezone - часовой пояс
			+все поля из таблицы users_extra
		$groups - группы пользователя, формат: id=>array(), ключи внутреннего массива:
			html_pref - HTML префикс группы
			html_end - HTML окончание группы
			title - название группы
			_a - ссылка на просмотр информации о группе
			_main - флаг основной группы
	*/
	public static function AcUserInfo($groups)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$user=$GLOBALS['Eleanor']->module['user'];
		$C=static::Menu('view','main','main');
		$ogr=$mgr='';
		foreach($groups as &$v)
			if($v['_main'])
				$mgr.='<a href="'.$v['_a'].'">'.$v['html_pref'].$v['title'].$v['html_end'].'</a>';
			else
				$ogr.='<a href="'.$v['_a'].'">'.$v['html_pref'].$v['title'].$v['html_end'].'</a>, ';

		$sname=htmlspecialchars($user['name'],ELENT,CHARSET);
		if($user['avatar_location'])
		{
			switch($user['avatar_type'])
			{
				case'upload':
					$a=Eleanor::$uploads.'/avatars/';
				break;
				case'local':
					$a='images/avatars/';
				break;
				default:
					$a='';
			}
			list($w,$h)=explode(' ',Eleanor::$vars['avatar_size']);
			$avatar='<img src="'.$a.$user['avatar_location'].'" style="'.($w ? 'max-width:'.$w.'px;' : '').($h ? 'max-height:'.$h.'px;' : '').'" alt="'.$sname.'" title="'.$sname.'" />';
		}
		else
			$avatar='<img src="'.Eleanor::$vars['noavatar'].'" alt="'.$user['name'].'" title="'.$user['name'].'" />';

		$Lst=Eleanor::LoadListTemplate('table-form');

		switch($user['gender'])
		{
			case 0:
				$gender=$lang['female'];
			break;
			case 1:
				$gender=$lang['male'];
			break;
			default:
				$gender=$lang['nogender'];
		}
		$personal=(string)$Lst->begin()
			->item($lang['gender'],$gender)
			->item($lang['bio'],$user['bio'] ? $user['bio'] : '&mdash;')
			->item($lang['interests'],$user['interests'] ? $user['interests'] : '&mdash;')
			->item($lang['location'],$user['location'] ? $user['location'] : '&mdash;')
			->item($lang['site'],$user['site'] ? $user['site'] : '&mdash;')
			->item($lang['signature'],$user['signature'] ? $user['signature'] : '&mdash;')
			->item($lang['timezone'],$user['timezone'] ? $user['timezone'] : '<i>'.$lang['by_default'].'</i>')
			->end();

		$user['skype']=htmlspecialchars($user['skype'],ELENT,CHARSET,false);
		if($user['icq'])
			$icq=number_format($user['icq'],0,3,'-');
		$connect=(string)$Lst->begin()
			->item('Jabber',$user['jabber'] ? $user['jabber'] : '&mdash;')
			->item('Skype',$user['skype'] ? '<a href="skype:'.$user['skype'].'">'.$user['skype'].'</a>' : '&mdash;')
			->item('ICQ',$user['icq'] ? '<img src="http://status.icq.com/online.gif?icq='.$user['icq'].'&amp;img=5" alt="'.$icq.'" title="'.$icq.'" /> '.$icq : '&mdash;')
			->item($lang['vk'],$user['vk'] ? $user['vk'] : '&mdash;')
			->item('Twitter',$user['twitter'] ? $user['twitter'] : '&mdash;')
			->end();

		$C.=$Lst->begin()
			.'<tr><td rowspan="5" style="padding:5px;width:10%">'.$avatar.'</td><td class="label" style="width:150px">'.$lang['nickname'].'</td><td>'.$sname.'</td></tr>'
			.($sname==$user['full_name'] ? '' : $Lst->item($lang['full_name'],$user['full_name']));

		$Lst->item($lang['registered'],Eleanor::$Language->Date($user['register'],'fdt'))
			->item($lang['last_visit'],Eleanor::$Language->Date($user['last_visit'],'fdt'));
		if($mgr)
			$mgr=$Lst->item($lang['maingroup'],$mgr);
		if($ogr)
			$Lst->item($lang['othgroups'],rtrim($ogr,' ,'));
		if(Eleanor::$vars['multilang'])
			$Lst->item($lang['lang'],$user['language'] && isset(Eleanor::$langs[$user['language']]) ? '<span title="'.$user['language'].'">'.Eleanor::$langs[$user['language']]['name'].'</span>' : '<i>'.$lang['by_default'].'</i>');
		$Lst->end()
			->tabs(
				array($lang['personal'],$personal),
				array($lang['connect'],$connect)
			);
		return$C.$Lst;
	}

	/*
		Элемент шаблона: загрузка галерей
		$galleries - массив галерей, каждый элемент массива - массив с ключами:
			n - имя галереи
			i - путь к картинке относительно корня сайта
			d - описание галереи
	*/
	public static function Galleries($galleries)
	{
		$c='';
		foreach($galleries as &$v)
			$c.='<a href="#" class="gallery" data-gallery="'.$v['n'].'"><b><img src="'.$v['i'].'" alt="" /><span>'.$v['d'].'</span></b></a>';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return$c ? '<a class="imagebtn cancelavatar" href="#">'.$lang['cancel_avatar'].'</a><div class="clr"></div><div class="galleryavatars">'.$c.'</div>' : '<div class="noavatars cancelavatar">'.$lang['no_avatars'].'</div>';
	}

	/*
		Элемен шаблона: загрузка аватаров
		$avatar - массив аватаров, каждый элемент массива - массив с ключами:
			p - путь к файлу, относительно корня сайта, с закрывающим слешем
			f - имя файла
	*/
	public static function Avatars($avatars)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$c='';
		foreach($avatars as &$v)
			$c.='<a href="#" class="applyavatar" title="'.$v['f'].'"><img src="'.join($v).'" /></a>';
		return$c ? '<a class="imagebtn getgalleries" href="#">'.$lang['togals'].'</a><a class="imagebtn cancelavatar" href="#">'.$lang['cancel_avatar'].'</a><div class="clr"></div><div class="avatarscover">'.$c.'</div>' : '<div class="noavatars cancelavatar">'.$lang['no_avatars'].'</div>';
	}

	#Loginza
	/*
		Страница просмотра внешних авторизаций, при интеграции с сервисом loginza.ru
		$items - массив всех внешних авторизаций, каждый элемент - массив с ключами:
			identity - ссылка на пользователя внешнего сервиса
			provider - название провайдера внешней авторизации
		$added - данные добавленной внешней авторизации, массив с ключами:
			identity - ссылка на пользователя внешнего сервиса
			provider - название провайдера внешней авторизации
		$error - ошибка, если пустая - значит ошибок нет
	*/
	public static function Loginza($items,$added,$error)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];
		$C=static::Menu('user','externals','main');

		if($added)
			$C->Message(sprintf($lang['aexternal'],'<a href="'.$added['identity'].'" target="_blank">'.(isset($lang[$added['provider']]) ? $lang[$added['provider']] : $added['provider']).'</a>'),'info');
		if($error)
			$C->Message($error ? $error['error_message'] : 'Error...','error');

		$s='';
		foreach($items as &$v)
			$s.='<span><a href="'.$v['identity'].'" target="_blank" style="font-size:2em">'.(isset($lang[$v['provider']]) ? $lang[$v['provider']] : $v['provider']).'</a><a href="#" data-provider="'.$v['provider'].'" data-uid="'.$v['provider_uid'].'" title="'.$ltpl['delete'].'">X</a> </span>';
		return $C.'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#externals").on("click","a[href=#]",function(){
		var o=$(this);
		CORE.Ajax(
			{
				module:"'.$GLOBALS['Eleanor']->module['name'].'",
				"do":"externals",
				provider:o.data("provider"),
				pid:o.data("uid")
			},
			function()
			{
				o.closest("span").remove();
			}
		);
		return false;
	})
})//]]></script><script type="text/javascript" src="http://loginza.ru/js/widget.js"></script><div style="text-align:center;">
<img src="http://loginza.ru/img/providers/facebook.png" title="Yandex" />
<img src="http://loginza.ru/img/providers/yandex.png" title="Yandex" />
<img src="http://loginza.ru/img/providers/google.png" title="Google Accounts" />
<img src="http://loginza.ru/img/providers/vkontakte.png" title="VK" />
<img src="http://loginza.ru/img/providers/mailru.png" title="Mail.ru" />
<img src="http://loginza.ru/img/providers/twitter.png" title="Twitter" />
<img src="http://loginza.ru/img/providers/loginza.png" title="Loginza" />
<img src="http://loginza.ru/img/providers/myopenid.png" title="MyOpenID" />
<img src="http://loginza.ru/img/providers/openid.png" title="OpenID" />
<img src="http://loginza.ru/img/providers/webmoney.png" title="WebMoney" /></a><br />'.($s ? '<div id="externals">'.rtrim($s,', ').'</div><br />' : '').'<a href="https://loginza.ru/api/widget?token_url='.urlencode(PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$GLOBALS['Eleanor']->Url->Construct(array('do'=>'loginza'),true,'')).'" class="loginza link-button" style="width:150px"><b>'.$lang['add'].'</b></a></div>';
	}

	/*
		Ошибка аутентификации при помощи сервиса loginza.
		$loginza - данные, полученные с сервиса
	*/
	public static function LoginzaError($loginza)
	{
		return static::Menu('guest','externals','main')->Message($loginza ? $loginza['error_message'] : 'Error...','error');
	}
}