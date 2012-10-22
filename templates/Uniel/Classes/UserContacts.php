<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для пользователей модуля "обратная связь"
*/
class TplUserContacts
{	public static
		$lang;	/*
		Основная страница обратной связи

		$canupload - флаг возможности загрузки файла
		$info - информация по обратной связи, заполняемая в админке
		$whom - массив выбора получателя письма. Формат id=>имя получателя
		$values - массив значений формы, ключи:
			subject - тема сообщения
			message - текст сообщения
			whom - идентификатор получателя
			sess - идентификатор сессии
		$bypost - флаг загрузки содержимого из POST запроса
		$errors - массив ошибок
		$captcha - captcha при отправке сообщения
	*/	public static function Contacts($canupload,$info,$whom,$values,$bypost,$errors,$captcha)
	{		$content=Eleanor::$Template->Menu(array(
			'title'=>$GLOBALS['Eleanor']->module['title'],
		));
		if($info)
		{
			$content->OpenTable();
			$content.=$info.Eleanor::$Template->CloseTable();
		}
		if($whom)
		{			if($errors)
			{
				foreach($errors as $k=>&$v)
					if(is_int($k) and isset(static::$lang[$v]))
						$v=static::$lang[$v];
				$content.=Eleanor::$Template->Message($errors,'error');
			}

			$wh='';
			if(count($whom)>1)
				foreach($whom as $k=>&$v)
					$wh.=Eleanor::Option($v,$k,$k==$values['whom']);

			$Lst=Eleanor::LoadListTemplate('table-form')->form($canupload ? array('enctype'=>'multipart/form-data') : array())->begin();
			if($wh)
				$Lst->item(static::$lang['whom'],Eleanor::Select('whom',$wh,array('tabindex'=>1)));
			$Lst
				->item(static::$lang['subject'],Eleanor::Edit('subject',$values['subject'],array('tabindex'=>2)))
				->item(static::$lang['message'],$GLOBALS['Eleanor']->Editor->Area('message',$values['message'],array('bypost'=>$bypost,'no'=>array('tabindex'=>3))));

			if($canupload)
				$Lst->item(array(static::$lang['file'],Eleanor::Control('file','file'),'descr'=>$canupload===true ? '' : sprintf(static::$lang['maxfs'],Files::BytesToSize($canupload))));

			if($captcha)
				$Lst->item(array(static::$lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>4)),'descr'=>static::$lang['captcha_']));

			$content.=$Lst->end()->submitline(Eleanor::Control('sess','hidden',$values['sess']).Eleanor::Button('OK','submit',array('tabindex'=>5)))->endform();
		}
		return$content;
	}

	/*
		Страница с информацией о том, что сообщение успешно отправлено
	*/
	public static function Sent()
	{		return Eleanor::$Template->Menu(array(
			'title'=>static::$lang['st'],
		))->Message(sprintf(static::$lang['sent'],$GLOBALS['Eleanor']->Url->Prefix()),'info');	}
}
TplUserContacts::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/contacts-*.php',false);