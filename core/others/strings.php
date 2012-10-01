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
class Strings extends BaseClass
{	/*
		$ep(empty pass) - валидизировать пустое значение?
	*/
	public static function CheckEmail($s,$ep=true)
	{
		$ab=constant(Language::$main.'::ALPHABET');
		$s=(array)$s;
		foreach($s as &$v)
			if((!$ep or $v) and preg_match('#^[_\-\.\wa-z'.$ab.'0-9]+\@([\wa-z'.$ab.'0-9](?:[\.\-\wa-z'.$ab.'0-9][\wa-z'.$ab.'0-9])*)+\.[\wa-z'.$ab.'\-]{2,}$#i',$v)==0)
				return false;
		return true;
	}

	public static function CheckUrl($s)
	{
		$s=trim($s);
		if(strpos($s,'mailto:')===0)
			return self::CheckEmail(substr($s,7));
		$ab=constant(Language::$main.'::ALPHABET');
		return preg_match('~^([a-z]{3,10}://[\wa-z'.$ab.'0-9/\._\-:]+\.[\wa-z'.$ab.'\-]{2,}/)?(?:[^\s{}]*)?$~i',$s)>0;
	}

	/*
		Ёта функци€ нормально работает с UTF-8! ƒобавл€ть параметры mb_ в substr не нужно!!
		ћетод служит дл€ того, чтобы распарсить строку параметров вида
		param1="value1" param2=   value2 param3="v      arlue3"
		$str - строка
		$first_param служит дл€ указани€ имени первого параметра, если $str начинаетс€ с =value
	*/
	public static function ParseParams($s,$first=0)
	{
		$a=array();
		$s=trim($s);
		$l=strlen($s);

		$cur=0;
		$finp=false;
		$param='';

		while($cur<$l)
		{
			if($cur==0 and substr($s,$cur,1)=='=')
			{
				$param=$first;
				$finp=true;
				$cur++;
			}
			if($finp)
			{
				$finp=false;
				switch($q=substr($s,$cur,1))
				{
					case'"':
					case'\'':
						if(preg_match('#'.$q.'([^'.$q.']*)'.$q.'#',$s,$m,PREG_OFFSET_CAPTURE,$cur)>0)
							$a[$param]=$m[1][0];
						else
						{
							$a[$param]=substr($s,$cur+1);
							break 2;
						}
						$cur=$m[0][1]+strlen($m[0][0]);
					break;
					default:
						if(preg_match('#[^\s"\']+#',$s,$m,PREG_OFFSET_CAPTURE,$cur)>0)
							$a[$param]=$m[0][0];
						else
						{
							$a[$param]=$param;#ќбрубаем "вис€чие" параметры.
							break 2;
						}
						$cur=$m[0][1]+strlen($m[0][0]);
				}
			}
			elseif(preg_match('#([a-z0-9]+)(\s*=\s*)?#i',$s,$m,PREG_OFFSET_CAPTURE,$cur)>0)
			{
				$param=$m[1][0];
				if(isset($m[2]))
					$finp=true;
				else
					$a[$param]=$param;#ќбрубаем "вис€чие" параметры.
				$cur=$m[0][1]+strlen($m[0][0]);
			}
			else
				break;
		}
		return$a;
	}

	public static function CutStr($s,$n=30,$e='...')
	{
		if(mb_strlen($s)>$n)
		{
			$s=mb_substr($s,0,$n);
			$s=preg_replace('#[&<][^;>]*$#','',$s).$e;
		}
		return$s;
	}

	public static function UcFirst($s)
	{
		if(!$s)
			return$s;
		return mb_strtoupper(mb_substr($s,0,1)).mb_substr($s,1);
	}

	public static function CleanForExplode($s,$d=',')
	{
		$dq=preg_quote($d,'/');
		$s=preg_replace('/(?:'.$dq.'){2,}/',$d,$s);
		return preg_replace(array('/(?:'.$dq.')$/','/^(?:'.$dq.')/'),'',$s);
	}

	public static function MarkWords($w,$s,$c='#FFFF00',$bc='#FF0000')
	{
		if(!$s or !$w)
			return $s;
		$w=(array)$w;
		foreach($w as $k=>&$v)
		{
			$v=preg_quote(str_replace(array('<','>'),'',trim($v)),'#');
			if($v=='')
				unset($w[$k]);
		}
		return preg_replace('#(?<=>|^)([^<]+)#e','self::DoMarkWords(\'\1\',$w,$c,$bc)',$s);
	}

	protected static function DoMarkWords($s,$w,$c,$b)
	{
		$s=stripslashes($s);
		foreach($w as &$v)
			$v=preg_quote($v,'#');
		return preg_replace('#(?:\b)('.join('|',$w).')(?:\b)#i','<span style="background-color: '.$c.'; color: '.$b.';">\1</span>',$s);
	}
}