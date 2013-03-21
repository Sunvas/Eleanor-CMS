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

class Tasks extends BaseClass
{
	/**
	 * Пересчет точной даты и времени следующего запуска крона
	 */
	public static function UpdateNextRun()
	{
		$R=Eleanor::$Db->Query('SELECT UNIX_TIMESTAMP(`nextrun`) FROM `'.P.'tasks` WHERE `status`=1 AND `locked`=0 ORDER BY `free` ASC, `nextrun` ASC');
		list($next)=$R->fetch_row();

		if(!$next)
			$next=strtotime('+1day');
		Eleanor::$Cache->Put('nextrun',$next,0,true);
	}

	/**
	 * Выбор первого числа из массива, меньшего, чем заданное
	 *
	 * @param array $ints Массив числел
	 * @param int $from Число-критерий выбора
	 */
	public static function MinFrom($ints,$from)
	{
		foreach($ints as &$v)
			if($v>=$from)
				return$v;
		return false;
	}

	/**
	 * Раскрытие диапазонов, описанных в строке с последовательностью чисел.
	 *
	 * 1,2,5-10 будет преобразовано в 1,2,5,6,7,8,9,10
	 * Так же существует возможность задания шага раскрытий указав после диапазона : и шаг, например 1,2,5-10:2,15 будет преобразовано в 1,2,5,7,9,10,15
	 */
	public static function FillInt($str)
	{
		$str=preg_replace('#[^0-9,:\-*]+#','',$str);
		$str=preg_replace_callback(
			'/([0-9]+)\-([0-9]+)(?::([0-9]+))?/',
			function($abc)
			{
				$a=(int)$abc[1];
				$b=(int)$abc[2];
				$c=isset($abc[3]) ? (int)$abc[3] : 1;
				if($c<1)
					$c=1;
				if($a>=$b)
					return$a.','.$b;
				$result='';
				for(;$a<$b;$a+=$c)
					$result.=$a.',';
				return$result.$b;
			},
			$str
		);
		return$str;
	}

	/**
	 * Вычисление времени ближайшего запуска, исходя из входящий пожалений
	 *
	 * @param array $t Массив пожеланий времени, детали смотрите в начале тела метода
	 * @param int|FALSE $do Смещение в секундах по часовому поясу
	 */
	public static function CalcNextRun(array$t=array(),$do=false)
	{
		$t+=array(
			'year'=>'*',
			'month'=>'*',
			'day'=>'*',
			'hour'=>'*',
			'minute'=>'*',
			'second'=>'*',
		);
		foreach($t as &$v)
		{
			$v=is_string($v) ? explode(',',self::FillInt($v)) : (array)$v;
			sort($v,SORT_NUMERIC);
		}

		if($do===false)
			$do=date_offset_get(date_create());

		#Массив довесков
		$extra=array('year'=>0,'month'=>0,'day'=>0,'hour'=>0,'minute'=>0);

		list($y,$m,$d,$h,$i,$s)=explode('-',gmdate('Y-n-j-G-i-s',time()+$do));
		$i=(int)$i;
		if(!in_array('*',$t['second'],true) and false===$s=self::MinFrom($t['second'],$s))
		{
			$s=reset($t['second']);
			$extra['minute']++;
		}

		if(!in_array('*',$t['minute'],true))
		{
			$i+=$extra['minute'];
			if(false===$tmp=self::MinFrom($t['minute'],$i))
			{
				$i=reset($t['minute']);
				$extra['hour']++;
				$extra['minute']=0;
			}
			else
			{
				if($tmp>=$i)
					$extra['minute']=0;
				$i=$tmp;
			}
		}

		if(!in_array('*',$t['hour'],true))
		{
			$h+=$extra['hour'];
			if(false===$tmp=self::MinFrom($t['hour'],$h))
			{
				$h=reset($t['hour']);
				$extra['day']++;
				$extra['hour']=$extra['minute']=0;
			}
			else
			{
				if($tmp>=$h)
					$extra['hour']=0;
				$h=$tmp;
			}
		}

		if(!in_array('*',$t['day'],true))
		{
			$d+=$extra['day'];
			if(false===$tmp=self::MinFrom($t['day'],$d))
			{
				$d=reset($t['day']);
				$extra['month']++;
				$extra['day']=$extra['hour']=$extra['minute']=0;
			}
			else
			{
				if($tmp>=$d)
					$extra['day']=0;
				$d=$tmp;
			}
		}

		if(!in_array('*',$t['month'],true))
		{
			$m+=$extra['month'];
			if(false===$tmp=self::MinFrom($t['month'],$m))
			{
				$m=reset($t['month']);
				$extra['year']++;
				$extra['month']=$extra['day']=$extra['hour']=$extra['minute']=0;
			}
			else
			{
				if($tmp>=$m)
					$extra['month']=0;
				$m=$tmp;
			}
		}

		if(!in_array('*',$t['year'],true))
		{
			$y+=$extra['year'];
			if(false===$tmp=self::MinFrom($t['year'],$y))
				return false;
			if($tmp>=$y)
				$extra['year']=0;
			$y=$tmp;
		}

		$ret=gmmktime($h,$i,$s,$m,$d,$y);

		if(0!=$s=array_sum($extra))
		{
			$s=$s>0 ? '+' : '-';
			foreach($extra as $k=>&$v)
				$s.=$v.$k;
			$ret=strtotime($s,$ret);
		}
		#Смещение по времени от пользователя
		$ret-=$do;

		return$ret;
	}
}