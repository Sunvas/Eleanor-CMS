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

#Класс поддержки украинского языка
class Ukrainian
{
	const
		ALPHABET='абвгдеёжзийклмнопрстуфхцчшщьъыэюяєії';#Украинский технический алфавит

	/**
	 * Образование множественной формы слова
	 *
	 * @param int $n Число
	 * @param array $forms Формы слова. Пример array('один','два, три, четыре','пять, шесть, семь, восемь, девять, ноль')
	 */
	public static function Plural($n,array$forms)
	{
		$forms+=array(false,false,false);
		return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
	}

	/**
	 * Транслитерация строки в латинницу
	 *
	 * @param string $s Текст
	 */
	public static function Translit($s)
	{
		return str_replace(
			array('а','б','в','г','ґ','д','е','є', 'з','и','і','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ы','ё', 'ж', 'ч', 'ш', 'щ',  'э', 'ю', 'я', 'ї', 'ъ', 'ь', 'А','Б','В','Г','Ґ','Д','Е','Є', 'З','И','І','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ы','Ё', 'Ж', 'Ч', 'Ш', 'Щ',  'Э', 'Ю', 'Я', 'Ї', 'Ъ', 'Ь',),
			array('a','b','v','g','g','d','e','ye','z','i','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','y','yo','zh','ch','sh','sch','je','yu','ya','yi','\'','\'','A','B','V','G','G','D','E','Ye','Z','I','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','C','Y','Yo','Zh','Ch','Sh','Sch','Ye','Yu','Ya','Yi','\'','\''),
			$s
		);
	}

	/**
	 * Человеческое представление даты с настройками
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
		$r='';
		switch($t)
		{
			case't':#time
				return date('H:i:s',$d);
			break;
			case'd':#date
				return date('Y-m-d',$d);
			break;
			case'dt':#datetime
			default:
				return date('Y-m-d H:i:s',$d);
			break;

			case'my':#Month year
				$day=explode(',',date('Y,n',$d));
				switch($day[1])
				{
					case 1:
						$r='Січень ';
					break;
					case 2:
						$r='Лютий ';
					break;
					case 3:
						$r='Березень ';
					break;
					case 4:
						$r='Квітень ';
					break;
					case 5:
						$r='Травень ';
					break;
					case 6:
						$r='Червень ';
					break;
					case 7:
						$r='Липень ';
					break;
					case 8:
						$r='Серпень ';
					break;
					case 9:
						$r='Вересень ';
					break;
					case 10:
						$r='Жовтень ';
					break;
					case 11:
						$r='Листопад ';
					break;
					case 12:
						$r='Грудень ';
				}
				$r.=$day[0];
			break;
			case'fd':#full date
				$a+=array('advanced'=>true);
				$r=self::DateText($d,$a['advanced']);
			break;
			case'fdt':#full datetime
				$a+=array('advanced'=>true);
				$r=self::DateText($d,$a['advanced']).date(' H:i',$d);
		}
		$a+=array('lowercase'=>false);
		return $a['lowercase'] ? mb_strtolower($r) : $r;
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
				return'Сьогодні';
			if($day[2]+1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]+1==$tod[1] and $tod[0]==$day[0] and $tod[2]==1 and $day[3]==$day[2] or $day[0]+1==$tod[0] and $tod[2]==1 and $tod[1]==1 and $day[3]==$day[2])
				return'Вчора';
			if($day[2]-1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]-1==$tod[1] and $tod[0]==$day[0] and $tod[2]==$tod[3] and $day[2]==1 or $day[0]-1==$tod[0] and $tod[2]==$tod[3] and $tod[1]==12 and $day[2]==1)
				return'Завтра';
		}
		$r=$day[2];
		switch($day[1])
		{
			case 1:
				$r.=' січня ';
			break;
			case 2:
				$r.=' лютого ';
			break;
			case 3:
				$r.=' березня ';
			break;
			case 4:
				$r.=' квітня ';
			break;
			case 5:
				$r.=' травня ';
			break;
			case 6:
				$r.=' червня ';
			break;
			case 7:
				$r.=' липня ';
			break;
			case 8:
				$r.=' серпня ';
			break;
			case 9:
				$r.=' вересня ';
			break;
			case 10:
				$r.=' жовтня ';
			break;
			case 11:
				$r.=' листопада ';
			break;
			case 12:
				$r.=' грудня ';
		}
		return$r.$day[0];
	}
}