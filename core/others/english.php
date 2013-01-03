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

#Класс поддержки английского языка
class English
{
	const
		ALPHABET='abcdefghijklmnopqrstuvwxyz';#Латинский технический алфавит

	/**
	 * Образование множественной формы слова
	 *
	 * @param int $n Число
	 * @param array $forms Формы слова. Пример array('один','два и больше')
	 */
	public static function Plural($n,array$forms)
	{
		return$n==1 ? $forms[0] : $forms[1];
	}

	/**
	 * Человеческое представление даты
	 *
	 * @param int|string $d Дата в обычном машинном формате, либо timestamp
	 * @param string $t Тип вывода: t - машинное время, d - машинная дата, dt - машинная дата и время, my - месяц и год, fd - полная дата, fdt - полная дата и время
	 * @param array $a Дополнительные опции, например включения ключа advanced позволит выводить значения "Сегодня", "Завтра", "Вчера"
	 */
	public static function Date($d=false,$t='',$a=array())
	{
		if(!$d)
			$d=time();
		elseif(is_array($d))
		{
			$d+=array_combine(array('H','i','s','n','j','Y'),explode(',',date('H,i,s,n,j,Y')));
			$d=mktime($d['H'],$d['i'],$d['s'],$d['n'],$d['j'],$d['Y']);
		}
		elseif(!is_int($d))
			$d=strtotime($d);
		if(!$d)
			return;
		switch($t)
		{
			case't':#time
				return date('H:i:s',$d);
			case'd':#date
				return date('Y-m-d',$d);
			case'dt':#datetime
			default:
				return date('Y-m-d H:i:s',$d);

			case'my':#Month year
				return date('F Y',$d);
			case'fd':#full date
				$a+=array('advanced'=>true);
				return self::DateText($d,$a['advanced']);
			case'fdt':#full datetime
				$a+=array('advanced'=>true);
				return self::DateText($d,$a['advanced']).date(' H:i',$d);
		}
	}

	/**
	 * Человеческое представление даты
	 *
	 * @param int $t Дата в оформате timestamp
	 * @param bool $adv Флаг включения значений "Сегодня", "Завтра", "Вчера"
	 */
	public static function DateText($t,$adv)
	{
		$day=explode(',',date('Y,n,j,t',$t));
		$tod=explode(',',date('Y,n,j,t'));
		if($adv)
		{
			if($day[2]==$tod[2] and $day[1]==$tod[1] and $day[0]==$tod[0])
				return'Today';
			if($day[2]+1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]+1==$tod[1] and $tod[0]==$day[0] and $tod[2]==1 and $day[3]==$day[2] or $day[0]+1==$tod[0] and $tod[2]==1 and $tod[1]==1 and $day[3]==$day[2])
				return'Yesterday';
			if($day[2]-1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]-1==$tod[1] and $tod[0]==$day[0] and $tod[2]==$tod[3] and $day[2]==1 or $day[0]-1==$tod[0] and $tod[2]==$tod[3] and $tod[1]==12 and $day[2]==1)
				return'Tomorrow';
		}
		return date('m F Y',$t);
	}
}