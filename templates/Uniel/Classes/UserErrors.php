<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для пользователей системного модуля страниц ошибок
*/
class TPLUserErrors
{	public static
		$lang;	/*
		Вывод страницы ошибки
		$a - параметры ошибки, массив с ключами:
			id - идентификатор ошибки в БД
			http_code - HTTP код ошибки
			image - логотип ошибки
			mail - e-mail, куда необходимо присылать сообщение от пользователей
			title - название страницы ошибки
			text - текст с пояснением ошибки
			meta_title - заголовок окна
			meta_descr - meta description
		$sent - флаг отправленности сообщения
		$values - массив значений полей, ключи
			text - текст сообщения
			name - имя для гостя
		$errors - массив ошибок
		$back - URI возврата
		$captcha - captcha при отправке письма
	*/
	public static function ShowError($a,$sent,$values,$errors,$back,$captcha)
	{		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		if($sent)
			$tosend='<hr /><br />'.Eleanor::$Template->Message(static::$lang['sent'],'info');
		elseif($a['mail'])
			$tosend='<hr />'.($errors ? Eleanor::$Template->Message($errors,'error') : '')
				.'<form method="post">'
				.(Eleanor::$Login->IsUser() ? '' : '<div class="errorinput"><span>'.static::$lang['yourname'].'</span><br />'.Eleanor::Edit('name',$values['name'] || $errors ? $values['name'] : static::$lang['guest']).'</div><br />')
				.'<div class="errorinput"><span>'
				.static::$lang['tell_us'].'</span><br />'.$GLOBALS['Eleanor']->Editor->Area('text',$values['text']).'</div>'
				.($back ? Eleanor::Control('back','hidden',$back) : '')
				.($captcha ? '<br /><div class="errorinput"><span>'.static::$lang['captcha'].'</span><br /><span class="small">'.static::$lang['captcha_'].'</span><br />'.Eleanor::Edit('check','').'<br />'.$captcha.'</div>' : '')
				.'<div style="text-align:center;"><a href="#" onclick="$(this).closest(\'form\').submit();return false;" class="button">'.static::$lang['send'].'</a></div></form>';
		else
			$tosend='';
		return'<div class="base"><div class="heading2"><div class="binner"><h6>'.$a['title']
			.'</h6><div class="clr"></div></div></div><div class="maincont"><div class="binner">'
			.($a['image'] ? '<img style="float:left;margin-right:10px;" src="images/errors/'.$a['image'].'" alt="'.$a['title'].'" title="'.$a['title'].'" />' : '')
			.$a['text'].'<div class="clr"></div>'.$tosend
			.'<div class="clr"></div></div></div><div class="morelink"><div class="binner">'
			.($back ? '<a href="'.$back.'"><b>'.static::$lang['back'].'</b></a><br />' : '')
			.'<div class="clr"></div></div></div></div>';	}
}
TPLUserErrors::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/errors-*.php',false);