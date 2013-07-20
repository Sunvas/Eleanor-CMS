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

class Punycode
{
	/**
	 * Кодирование и декозирование домена в Punycode и из Punycode.
	 * Метод сам определяет, представлен ли домен в нужной форме и, если нет - выполняет преобразования.
	 *
	 * @param string $domain Доменное имя
	 * @param bool $encode Флаг кодирования в Punycode
	 */
	public static function Domain($domain,$encode=true)
	{
		if(!Strings::CheckUrl($domain))
			return;
		if($encode)
		{
			if(CHARSET!='utf-8')
				$domain=mb_convert_encoding($domain,'utf-8',CHARSET);
			$domain=explode('.',$domain);
			foreach($domain as &$d)
				if(strpos($d,'xn--')!==0 and preg_match('#[^a-z\.\-]#i',$d)>0)
					$d=self::Encode(mb_strtolower($d,'utf-8'));
			$domain=join('.',$domain);
		}
		else
		{
			$domain=explode('.',$domain);
			foreach($domain as &$d)
				$d=self::Decode(strtolower($d));
			$domain=join('.',$domain);
			if(CHARSET!='utf-8')
				$domain=mb_convert_encoding($domain,CHARSET,'utf-8');
		}
		return$domain;
	}

	/**
	 * Кодирование Punycode в utf-8 строку
	 *
	 * @param string $s Домен в Punycode
	 */
	public static function Decode($s)
	{
		if(strpos($s,'xn--')!==0)
			return $s;
		$first=700;
		$bias=72;
		$idx=0;
		$char=0x80;
		$decoded=array();

		$dpos=strrpos($s,'-');
		if($dpos>4)#4 - это длина префикса xn--
			for($k=4;$k<$dpos;++$k)
				$decoded[]=ord($s[$k]);

		$decol=count($decoded);
		$encol=strlen($s);

		for($enco_idx=$dpos ? $dpos+1 : 0;$enco_idx<$encol;++$decol)
		{
			$old_idx=$idx;
			$w=1;
			$k=36;
			while(true)
			{
				$cp=ord($s[$enco_idx++]);
				$digit=$cp-48<10 ? $cp-22 : ($cp-65<26 ? $cp-65 : ($cp-97<26 ? $cp-97 : 36));
				$idx+=$digit*$w;
				$t=$k<=$bias ? 1 : ($k>=$bias+26 ? 26 : $k-$bias);
				if($digit<$t)
					break;
				$w*=36-$t;
				$k+=36;
			}
			$delta=floor(($idx-$old_idx)/$first);
			$first=2;
			$delta+=floor($delta/($decol+1));
			for($k=0;$delta>455;$k+=36)
				$delta=floor($delta/35);
			$bias=floor($k+36*$delta/($delta+38));
			$char+=floor($idx/($decol+1));
			$idx%=$decol+1;
			if($decol>0)
				for($i=$decol;$i>$idx;$i--)
					$decoded[$i]=$decoded[$i-1];
			$decoded[$idx++]=$char;
		}

		$s='';
		foreach($decoded as &$v)
			if($v<128)
				$s.=chr($v);#7bit are transferred literally
			elseif($v<(1<<11))
				$s.=chr(192+($v>>6)).chr(128+($v&63));#2 bytes
			elseif($v<(1<<16))
				$s.=chr(224+($v>>12)).chr(128+($v>>6&63)).chr(128+($v&63));#3 bytes
			elseif($v<(1<<21))
				$s.=chr(240+($v>>18)).chr(128+($v>>12&63)).chr(128+($v>>6&63)).chr(128+($v&63));# 4 bytes
			else
				$s.=0xFFFC;
		return$s;
	}

	/**
	 * Декодирование utf-8 строки в Punycode
	 *
	 * @param string $s Домен
	 */
	public static function Encode($s)
	{
		$values=$unicode=array();
		$n=strlen($s);
		for($i=0;$i<$n;$i++)
		{
			$v=ord($s[$i]);
			if($v<128)
				$unicode[]=$v;
			else
			{
				if(!$values)
					$cc=$v<224 ? 2 : 3;
				$values[]=$v;
				if(count($values)==$cc)
				{
					$unicode[]=$cc==3 ? $values[0]%16*4096 + $values[1]%64*64 + $values[2]%64 : $values[0]%32*64 + $values[1]%64;
					$values=array();
				}
			}
		}
		#[E] utf to unicode func
		unset($s,$values);

		$delta=$cc=0;
		$n=128;
		$bias=72;
		$first=700;
		$ex=$bs='';
		$ucnt=count($unicode);

		foreach($unicode as &$v)
			if($v<128)
			{
				$bs.=chr($v);
				$cc++;
			}

		while($cc<$ucnt)
		{
			$m=100000;
			foreach($unicode as &$v)
				if($v>=$n and $v<=$m)
					$m=$v;

			$delta+=($m-$n)*($cc+1);
			$n=$m;

			foreach($unicode as &$v)
			{
				if($v<$n)
					$delta++;
				elseif($v==$n)
				{
					$q=$delta;
					$k=36;
					while(true)
					{
						if($k<=$bias+1)
							$t=1;
						elseif($k>=$bias+26)
							$t=26;
						else
							$t=$k-$bias;

						if($q<$t)
							break;

						$ex.=self::EncodeDigit($t+($q-$t)%(36-$t));
						$q=floor(($q-$t)/(36-$t));
						$k+=36;
					}
					$ex.=self::EncodeDigit($q);

					$delta=floor($delta/$first);
					$delta+=floor($delta/($cc+1));
					$first=2;
					$k=0;
					while($delta>455)
					{
						$delta=floor($delta/35);
						$k+=36;
					}
					$bias=$k+floor(36*$delta/($delta+38));

					$delta=0;
					$cc++;
				}
			}
			$delta++;
			$n++;
		}

		if($bs!='' and $ex=='')
			return$bs;

		if($bs!='' and $ex!='')
			return'xn--'.$bs.'-'.$ex;

		if($bs=='' and $ex!='')
			return'xn--'.$ex;
	}

	/**
	 * Внутренний метод
	 *
	 * @param int $d
	 */
	protected static function EncodeDigit($d)
	{
		return chr($d+22+75*($d<26));
	}
}