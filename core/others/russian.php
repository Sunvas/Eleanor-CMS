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

#Класс поддержки русского языка
class Russian
{
	const
		ALPHABET='абвгдеёжзийклмнопрстуфхцчшщьъыэюяєії';

	public static function Plural($n,$forms)
	{
		return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
	}

	public static function Translit($s)
	{
		return str_replace(
			array('а','б','в','г','д','е','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ы','ё', 'ж', 'ч', 'ш', 'щ',  'э','ю', 'я', 'ъ', 'ь', 'А','Б','В','Г','Д','Е','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ы','Ё', 'Ж', 'Ч', 'Ш', 'Щ',  'Э','Ю', 'Я', 'Ъ', 'Ь'),
			array('a','b','v','g','d','e','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','y','yo','zh','ch','sh','sch','e','yu','ya','\'','\'','A','B','V','G','D','E','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','C','Y','Yo','Zh','Ch','Sh','Sch','E','Yu','Ya','\'','\''),
			$s
		);
	}

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
						$r='Январь ';
					break;
					case 2:
						$r='Февраль ';
					break;
					case 3:
						$r='Март ';
					break;
					case 4:
						$r='Апрель ';
					break;
					case 5:
						$r='Май ';
					break;
					case 6:
						$r='Июнь ';
					break;
					case 7:
						$r='Июль ';
					break;
					case 8:
						$r='Август ';
					break;
					case 9:
						$r='Сентябрь ';
					break;
					case 10:
						$r='Октябрь ';
					break;
					case 11:
						$r='Ноябрь ';
					break;
					case 12:
						$r='Декабрь ';
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

	public static function DateText($t,$adv)
	{
		$day=explode(',',date('Y,n,j,t',$t));
		$tod=explode(',',date('Y,n,j,t'));
		if($adv)
		{
			if($day[2]==$tod[2] and $day[1]==$tod[1] and $day[0]==$tod[0])
				return'Сегодня';
			if($day[2]+1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]+1==$tod[1] and $tod[0]==$day[0] and $tod[2]==1 and $day[3]==$day[2] or $day[0]+1==$tod[0] and $tod[2]==1 and $tod[1]==1 and $day[3]==$day[2])
				return'Вчера';
			if($day[2]-1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]-1==$tod[1] and $tod[0]==$day[0] and $tod[2]==$tod[3] and $day[2]==1 or $day[0]-1==$tod[0] and $tod[2]==$tod[3] and $tod[1]==12 and $day[2]==1)
				return'Завтра';
		}
		$r=$day[2];
		switch($day[1])
		{
			case 1:
				$r.=' января ';
			break;
			case 2:
				$r.=' февраля ';
			break;
			case 3:
				$r.=' марта ';
			break;
			case 4:
				$r.=' апреля ';
			break;
			case 5:
				$r.=' мая ';
			break;
			case 6:
				$r.=' июня ';
			break;
			case 7:
				$r.=' июля ';
			break;
			case 8:
				$r.=' августа ';
			break;
			case 9:
				$r.=' сентября ';
			break;
			case 10:
				$r.=' октября ';
			break;
			case 11:
				$r.=' ноября ';
			break;
			case 12:
				$r.=' декабря ';
		}
		return$r.$day[0];
	}
}