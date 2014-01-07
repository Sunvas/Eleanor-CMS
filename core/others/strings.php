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
{
	/**
	 * Проверка корректиности e-mail
	 * @param string $s Проверяемый e-mail
	 * @param bool $ep Флаг интерпретации пустого значения, как корректного
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

	/**
	 * Проверка корректности адреса ссылки
	 * @param string $s Проверямая ссылка
	 */
	public static function CheckUrl($s)
	{
		$s=trim($s);
		if(strpos($s,'mailto:')===0)
		{
			#Вырежем параметры
			$parpos=strpos($s,'?');
			return self::CheckEmail(substr($s,7,$parpos===false ? strlen($s) : $parpos-7));
		}
		$ab=constant(Language::$main.'::ALPHABET');
		return preg_match('~^([a-z]{3,10}://[\wa-z'.$ab.'0-9/\._\-:]+\.[\wa-z'.$ab.'\-]{2,}/)?(?:[^\s{}]*)?$~i',$s)>0;
	}

	/**
	 * Преобразование текстовой строки параметров в массив. Корректно обрабатывает даже некорректные данные.
	 * Метод корректно работает с UTF-8: lобавлять параметры mb_ в substr не нужно.
	 * @param string $s Строка параметров, формата param1="value1" param2=   value2 param3=  "value3"
	 * @param string $first Имя первого параметра, в случае если $s начинается с "=" (в BB кодах такое возможно [url=http://eleanor-cms.ru]CMS[/url]
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
							$a[$param]=true;#Обрубаем "висячие" параметры.
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
					$a[$param]=true;#Обрубаем "висячие" параметры.
				$cur=$m[0][1]+strlen($m[0][0]);
			}
			else
				break;
		}
		return$a;
	}

	/**
	 * Корректная обрезка строки до N символов. Метод не ломает html мнемоники.
	 * @param string $s Строка, которую необходимо обрезать
	 * @param int $n Число символов, до которых нужно обрезать строку, считая слева направо
	 * @param string $e Замена обрезанных символов
	 */
	public static function CutStr($s,$n=30,$e='...')
	{
		if(mb_strlen($s)>$n)
		{
			$s=mb_substr($s,0,$n);
			$s=trim(preg_replace('#[&<][^;>]*$#','',$s),';., ').$e;
		}
		return$s;
	}

	/**
	 * Версия ucfirst функции, корректно работающая с utf-8
	 * @param string $s Воходящая строка
	 */
	public static function UcFirst($s)
	{
		if(!$s)
			return$s;
		return mb_strtoupper(mb_substr($s,0,1)).mb_substr($s,1);
	}

	/**
	 * Преобработка строки для последующего корректного преобразования в массив при помощи explode.
	 * Удаляет дубли разделителей, убирает резделити в начале и в конце строки.
	 * @param string $s Входящая строка
	 * @param string $d Разделитель для последующего explode
	 */
	public static function CleanForExplode($s,$d=',')
	{
		$dq=preg_quote($d,'/');
		$s=preg_replace('/(?:'.$dq.'){2,}/',$d,$s);
		return preg_replace(array('/(?:'.$dq.')$/','/^(?:'.$dq.')/'),'',$s);
	}

	/**
	 * Выделение слов в тексте определенным цветов. Метод корректно минут все теги.
	 * @param string|array $w Слово для выделения
	 * @param string $s Текст в котором слово необходимо выделить
	 * @param string $c Цвет текста в выделении
	 * @param string $bc Цвет самого выделения
	 */
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

		return preg_replace_callback(
			'#(?<=>|^)([^<]+)#',
			function($s)use($w,$c,$bc)
			{
				return preg_replace('#(?:\b)('.join('|',$w).')(?:\b)#i','<span style="background-color: '.$c.'; color: '.$bc.';">\1</span>',$s[1]);
			},
			$s
		);
	}
}