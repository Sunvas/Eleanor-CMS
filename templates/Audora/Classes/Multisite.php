<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны мультисайта
*/
class TPLMultisite
{	public static
		$lang;
	/*
		Меню модуля
	*/
	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['main'],Eleanor::$Language['ms']['conf'],'act'=>$act=='main'),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);

	}
	/*
		Шаблон редактирования мультисайта
		$sites - массив id=>результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$error - ошибка, если ошибка пустая - значит ее нет
	*/
	public static function Multisite($sites,$controls,$error)
	{		static::Menu('main');		$GLOBALS['jscripts'][]='js/multisite_manager.js';
		$Lst=Eleanor::LoadListTemplate('table-form')->form(array('id'=>'multisite'));

		foreach($sites as $sn=>&$site)
		{
			$Lst->begin();
			foreach($controls as $k=>&$v)
			{
				if(is_array($v))
					$Lst->item(array($v['title'].$cl,Eleanor::$Template->LangEdit($site[$k],null),'tip'=>$v['descr'],'imp'=>$v['imp']));
				else
				{
					switch($v)
					{						case'site':
							$h=static::$lang['sgd'].' <a href="#" class="delsite">'.static::$lang['dels'].'</a>';
						break;
						default:
							$h=static::$lang['dbt'].' <a href="#" class="checkdb">'.static::$lang['chdb'].'</a>';					}
					$Lst->head($h);
				}
				$cl='';
			}
			$Lst->end();
		}
		$Lst->submitline(Eleanor::Button(static::$lang['addsite'],'button',array('class'=>'addsite')).' '.Eleanor::Button(static::$lang['saveconf']))->endform();
		return Eleanor::$Template->Cover((string)$Lst,$error,'error');
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}
TplMultisite::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/multisite-*.php',false);