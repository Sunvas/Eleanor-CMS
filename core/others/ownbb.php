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

class OwnBbCode extends BaseClass
{
	const
		SINGLE=false;

	/**
	 * Вывод заглушки, в случае когда использование тега запрещено ограничениями группы
	 */
	public static function RestrictDisplay()
	{
		return Eleanor::$Template->RestrictedSection(Eleanor::$Language['ownbb']['restrict']);
	}

	/**
	 * Обработка информации перед показом на странице
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		return$c;
	}

	/**
	 * Обработка информации перед её правкой
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 * @param bool $e Флаг наличия закрывающего тега
	 */
	public static function PreEdit($t,$p,$c,$cu,$e=self::SINGLE)
	{
		return static::PreSave($t,$p,$c,true,$e);
	}

	/**
	 * Обработка информации перед её сохранением
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreSave($t,$p,$c,$cu,$e=self::SINGLE)
	{
		if(!is_array($p))
			$p=Strings::ParseParams($p,$t);
		$tp=isset($p[$t]) ? '' : ' ';
		if(!$cu or isset($p['noparse']))
		{
			unset($p['noparse']);
			$cu=false;
		}
		foreach($p as $k=>$v)
		{
			if($v==$k)
			{
				$tp.=$k.' ';
				continue;
			}

			if(strpos($v,' ')===false)
				$q=$v;
			elseif(strpos($v,'\'')===false)
				$q='"'.$v.'"';
			elseif(strpos($v,'"')===false)
				$q='\''.$v.'\'';
			else
				$q='"'.str_replace('"','&quot;',$v).'"';

			if($k==$t)
				$tp='='.$q.' '.$tp;
			else
				$tp.=$k.'='.$q;
			$tp.=' ';
		}
		return'['.$t.rtrim($tp).($cu ? '' : ' noparse').']'.$c.($e ? '' : '[/'.$t.']');
	}

	/*
		Функции, которым для обработки необходимо передать весь массив текста - каждый дочерний класс должен содержать следующие методы:
		public static function TotalPreSave($s,$ts,$cu){ return$s; }
		public static function TotalPreEdit($s,$ts,$cu){ return$s; }
		public static function TotalPreDisplay($s,$ts,$cu){ return$s; }
		@param string $s Весь текст
		@param array $ts Массив тегов, которые необходимо обрабатывать
		@param bool $cu Флаг возможности использования тега
	*/
}

class OwnBB extends BaseClass
{
	const#Константы типа обрабоки
		DISPLAY=1,#Обработка сохраненных данных перед показом
		SHOW=2,#Обработка несохраненных данных перед показом: отличие от DISPLAY состоит в том, что используется разрешение не gr_see, а gr_use
		EDIT=4,#Обработка сохраненных данных перед правкой
		SAVE=8;#Обработка несохранных (полученных от пользователя) данных перед показом

	public static
		$replace=array(),#Массив замен классов обработчиков BB кодов. Формат: имя класса => имя класса замены
		$bbs=array(),#Массив с данными обрабатываемых ownbb кодов. Заполняется в конце этого файла
		$opts=array(),#Массив с тонкими настройками для каждого класса. Например ключ alt отвечает за присвоение всем картинкам параметра alt по умолчанию, visual - флаг визуального редактора с которого получены данные
		$np;#Массив, который используется в методах StoreNotParsed и ParseNotParsed

	/**
	 * Грамотная обработка ownbb кодов
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $t Тип обработки (см. константы выше)
	 * @param array $codes Исключительный массив только эти ББ коды нужно парсить
	 */
	public static function Parse($s,$t=self::DISPLAY,array$c=array())
	{
		$s=static::StoreNotParsed($s,$t);
		$s=static::ParseBBCodes($s,$t,$c);
		return static::ParseNotParsed($s,$t);
	}

	/**
	 * Непосредственная обработка ownbb кодов. Отличие от Parse в том, именно этот метод обрабатывает ownbb коды, в то время как Parse всего лишь надстройка
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 * @param array $codes Исключительный массив только эти ББ коды нужно парсить
	 */
	public static function ParseBBCodes($s,$type,array$codes=array())
	{
		switch($type)
		{
			case static::EDIT:
				$mth='PreEdit';
			break;
			case static::SAVE:
				$mth='PreSave';
			break;
			default:
				$mth='PreDisplay';
		}
		$groups=Eleanor::GetUserGroups();
		foreach(static::$bbs as &$bb)
		{
			$ts=explode(',',$bb['tags']);
			if($codes and count(array_intersect($codes,$ts))==0 or !$codes and $bb['special'])
				continue;

			$cu=true;
			if($type&static::SAVE)
				$grs=$bb['gr_use'];
			elseif($type&static::DISPLAY)
				$grs=$bb['gr_see'];
			elseif($type&static::SHOW)
				$grs=array_merge($bb['gr_use'],$bb['gr_see']);
			else
				$grs=false;

			if($grs)
				$cu=(bool)array_intersect($grs,$groups);

			$h=(false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p);
			if(isset(static::$replace[$h]))
			{
				$c=static::$replace[$h];
				$cch=false;#Class Check
			}
			else
			{
				$c='OwnBbCode_'.$h;
				$cch=true;
			}
			foreach($ts as &$t)
			{
				$ocp=-1;
				$cp=0;
				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{
						++$cp;
						continue;
					}
					$tl=strlen($t);
					#Если мы нашли нужный нам тег т.е. i != img (отшибем все следующие знаки после найденного тега - )
					if(trim($s{$cp+$tl+1},'=] ')!='')
					{
						++$cp;
						continue;
					}

					$l=false;
					do
					{
						$l=strpos($s,']',$l ? $l+1 : $cp);
						if($l===false)
						{
							++$cp;
							continue 2;
						}
					}while($s{$l-1}=='\\');

					if($cch and !class_exists($c,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue 3;

					if(method_exists($c,'Total'.$mth))
					{
						$s=call_user_func(array($c,'Total'.$mth),$s,$ts,$cu);
						continue 3;
					}

					$ps=substr($s,$cp+$tl+1,$l-$cp-$tl-1);
					$ps=str_replace('\\]',']',trim($ps));
					$e=constant($c.'::SINGLE');
					if($e or false===$clpos=stripos($s,'[/'.$t.']',$l+1))
					{
						$l-=$cp-1;#]
						$ct='';
					}
					else
					{
						$ct=substr($s,$l+1,$clpos-$l-1);
						$l=$clpos-$cp+$tl+3;#[/]
					}
					$r=call_user_func(array($c,$mth),$t,$ps,$ct,$cu,$e);
					$s=substr_replace($s,$r,$cp,$l);
					$ocp=$cp;
				}
			}
		}

		#Удаление лишних <br>. Например, после цитаты, которая заканчивается блочным элементом </blockquot>, <br> не нужен.
		return $type&(static::DISPLAY|static::SHOW) ? preg_replace('#<!-- NOBR --><br\s?/?>#i','',$s) : str_replace('<!-- NOBR -->','',$s);
	}

	/**
	 * Сохранение содержимого специальных ownbb кодов, в которых нельзя производить парсинг содержимого
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 */
	public static function StoreNotParsed($s,$type)
	{
		$s=str_replace('<!-- NP ','<!-- ',$s);
		$n=0;
		static::$np=array();
		$groups=Eleanor::GetUserGroups();
		foreach(static::$bbs as &$bb)
		{
			if(!$bb['no_parse'])
				continue;

			$cu=true;
			if($type&static::SAVE)
				$grs=$bb['gr_use'];
			elseif($type&static::DISPLAY)
				$grs=$bb['gr_see'];
			elseif($type&static::SHOW)
				$grs=array_merge($bb['gr_use'],$bb['gr_see']);
			else
				$grs=false;

			if($grs and !(bool)array_intersect($grs,$groups))
				continue;

			$h=(false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p);
			if(isset(static::$replace[$h]))
			{
				$c=static::$replace[$h];
				$cch=false;#Class Check
			}
			else
			{
				$c='OwnBbCode_'.$h;
				$cch=true;
			}
			$ts=explode(',',$bb['tags']);
			foreach($ts as &$t)
			{
				$ocp=-1;
				$cp=0;
				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{
						++$cp;
						continue;
					}
					$tl=strlen($t);
					#Если мы нашли нужный нам тег т.е. i != img (отшибем все следующие знаки после найденного тега - )
					if(trim(substr($s,$cp+$tl+1,1),'=] ')!='')
					{
						++$cp;
						continue;
					}

					if(false!==$nop=strpos($s,'noparse]',$cp) and $nop<strpos($s,']',$cp))
					{
						++$cp;
						continue;
					}

					if($cch and !class_exists($c,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue 3;

					$e=constant($c.'::SINGLE');
					if($e or false===$l=strpos($s,'[/'.$t.']',$cp))
					{
						$l=strpos($s,']',$cp);
						if($l===false)
						{
							++$cp;
							continue;
						}
						$l-=$cp-1;#]
					}
					else
						$l-=$cp-$tl-3;#[/]
					$r='<!-- NP '.$n++.' -->';
					$ct=substr($s,$cp,$l);
					$s=substr_replace($s,$r,$cp,$l);
					static::$np[]=array(
						'r'=>$r,
						't'=>$ct,
						's'=>$bb['sp_tags'] ? $bb['sp_tags']+array(''=>$t) : array($t),
					);
					$ocp=$cp;
				}
			}
		}
		return$s;
	}

	/**
	 * Обработка содержимого специальных ownbb кодов. Вызывается после их сохранения методом StoreNotParsed и обработки основных кодов методом ParseBBCodes
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 */
	public static function ParseNotParsed($s,$type)
	{
		if(static::$np)
			if($type)
				foreach(static::$np as &$v)
					$s=str_replace($v['r'],static::ParseBBCodes($v['t'],$type,$v['s']),$s);
			else
				foreach(static::$np as &$v)
					$s=str_replace($v['r'],$v['t'],$s);
		static::$np=array();
		return$s;
	}

	/**
	 * Создание кэша ownbb кодов
	 */
	public static function Recache()
	{
		static::$bbs=array();
		$R=Eleanor::$Db->Query('SELECT `handler`,`tags`,`no_parse`,`special`,`sp_tags`,`gr_use`,`gr_see`,`sb` FROM `'.P.'ownbb` WHERE `active`=1 ORDER BY `pos` ASC');
		while($a=$R->fetch_assoc())
		{
			$a['sp_tags']=$a['sp_tags'] ? explode(',',$a['sp_tags']) : array();
			$a['gr_use']=$a['gr_use'] ? explode(',',$a['gr_use']) : array();
			$a['gr_see']=$a['gr_see'] ? explode(',',$a['gr_see']) : array();
			static::$bbs[]=$a;
		}
		Eleanor::$Cache->Put('ownbb',static::$bbs);
	}
}

OwnBB::$bbs=Eleanor::$Cache->Get('ownbb');
if(OwnBB::$bbs===false)
	OwnBB::Recache();