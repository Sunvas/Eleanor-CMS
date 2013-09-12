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
{
	public static
		$lang;
	/*
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
		$isu - флаг пользователя (не гостя)
		$captcha - captcha при отправке сообщения
	*/
	public static function Contacts($canupload,$info,$whom,$values,$bypost,$errors,$isu,$captcha)
	{
		$content=Eleanor::$Template->Menu(array(
			'title'=>$GLOBALS['Eleanor']->module['title'],
		));
		if($info)
		{
			$content->OpenTable();
			$content.=$info.Eleanor::$Template->CloseTable();
		}
		if($whom)
		{
			if($errors)
			{
				foreach($errors as $k=>&$v)
					if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
						$v=static::$lang[$v];
				$content.=Eleanor::$Template->Message($errors,'error');
			}

			$wh='';
			if(count($whom)>1)
				foreach($whom as $k=>&$v)
					$wh.=Eleanor::Option($v,$k,$k==$values['whom']);

			$Lst=Eleanor::LoadListTemplate('table-form')->form($canupload ? array('enctype'=>'multipart/form-data') : array())->begin();
			if(!$isu)
				$Lst->item(array(static::$lang['email'],Eleanor::Input('from',$values['from'],array('type'=>'email','tabindex'=>1)),'tip'=>static::$lang['email_']));
			if($wh)
				$Lst->item(static::$lang['whom'],Eleanor::Select('whom',$wh,array('tabindex'=>2)));
			$Lst
				->item(static::$lang['subject'],Eleanor::Input('subject',$values['subject'],array('tabindex'=>3)))
				->item(static::$lang['message'],$GLOBALS['Eleanor']->Editor->Area('message',$values['message'],array('bypost'=>$bypost,'no'=>array('tabindex'=>4))));

			if($canupload)
				$Lst->item(array(static::$lang['file'],Eleanor::Input('file',false,array('type'=>'file')),'descr'=>$canupload===true ? '' : sprintf(static::$lang['maxfs'],Files::BytesToSize($canupload))));

			if($captcha)
				$Lst->item(array(static::$lang['captcha'],$captcha.'<br />'.Eleanor::Input('check','',array('tabindex'=>5)),'descr'=>static::$lang['captcha_']));

			$content.=$Lst->end()->submitline(Eleanor::Input('sess',$values['sess'],array('type'=>'hidden')).Eleanor::Button(static::$lang['send'],'submit',array('tabindex'=>6)))->endform();
		}
		return$content;
	}

	/*
		Страница с информацией о том, что сообщение успешно отправлено
	*/
	public static function Sent()
	{
		return Eleanor::$Template->Menu(array(
			'title'=>Eleanor::$Language['contacts']['st'],
		))->Message(sprintf(static::$lang['sent'],$GLOBALS['Eleanor']->Url->Prefix()),'info');
	}
}
TplUserContacts::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/contacts-*.php',false);