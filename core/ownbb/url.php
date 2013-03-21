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

#Класс, отвечает за работку ownbb тега [url]
class OwnBbCode_url extends OwnBbCode
{
	/**
	 * Обработка информации перед показом на странице
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,'href') : array();
		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		if(!isset($p['href']) and !isset($p['url']))
			$p['href']=trim($c);

		if(!isset(Eleanor::$vars['antidirectlink']))
			Eleanor::LoadOptions('editor');

		if(isset($p['self']))
		{
			$np=array();
			unset($p['self']);
		}
		else
			$np=array('target'=>'_blank');
		foreach($p as $k=>&$v)
			switch($k)
			{
				case'url':
				case'href':
					if(!Strings::CheckUrl($v))
						return '';
					if(Eleanor::$vars['antidirectlink'] and 0!==strpos($v,PROTOCOL.Eleanor::$domain) and false!==$pos=strpos($v,'://') and $pos<7)
						if(Eleanor::$vars['antidirectlink']=='nofollow')
							$np['rel']='nofollow';
						else
							$v='go.php?'.$v;
					$np['href']=$v;
				break;
				case'target':
					if($v=='_blank' or $v=='_self')
						$np['target']=$v;
				break;
				case'title':
				case'name':
					$np[$k]=$v;
			}
		return'<a'.Eleanor::TagParams($np).'>'.$c.'</a>';
	}

	/**
	 * Обработка информации перед её сохранением
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreSave($t,$p,$c,$cu)
	{
		return parent::PreSave($t,array_intersect_key(Strings::ParseParams($p,$t),array('title'=>'','href'=>'',$t=>'','self'=>'','target'=>'')),$c,$cu);
	}
}