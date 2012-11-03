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
class Url extends BaseClass
{	public
		$s_prefix,#Префикс всех УРЛов в статике
		$d_prefix='?',#Префикс всех УРЛов в динамике
		$delimiter='/',#Символ, или последовательсность символов для разделения параметров в статике
		$defis='_',#Символ для отделения параметров от значений в статике
		$ending='.html',#Окончание УРЛа может использоваться только в статике
		$string,#Строка УРЛа, которую мы парсим
		$is_static=false,
		$file,#файл для динамических ссылок
		$furl=false;#ЧПУ - включает человекопонятный УРЛ

/*
	#ToDo!
	public function __invoke(array$p=array(),$pr=true,$e=true)
	{		return$this->Construct($p,$pr,$e);	}
*/
	public function Construct(array$p=array(),$pr=true,$e=true)
	{		if(isset($p['']))
		{			$suf=static::Query($p['']);			unset($p['']);
		}
		else
			$suf=false;

		$r=array();#result		if($this->furl)
		{			if($e===true)
				$e=$this->ending;
			elseif($e===false)
				$e=$this->delimiter;
			foreach($p as $pk=>&$pv)
				if(is_array($pv))
				{					$add=true;					foreach($pv as $k=>&$v)
						if(is_int($k))
						{							if($v or (string)$v=='0')
							{
								$add=false;
								$r[]=static::Encode($v);
							}
						}
						elseif($add)
						{							if($v or (string)$v=='0')
								$r[]=static::Encode($k).$this->defis.static::Encode($v);						}
						else
							$add=true;
				}
				elseif($pv or (string)$pv=='0')
					$r[]=static::Encode($pv);

			if($pr===true)
				$pr=$this->s_prefix;
			$r=$r ? $pr.join($this->delimiter,$r).$e : $pr;

			if($suf)
				$r.='?'.$suf;
		}
		else
		{			foreach($p as $pk=>&$pv)
				if(is_array($pv))
				{
					foreach($pv as $k=>&$v)
						if(is_string($k) and ($v or (string)$v=='0'))
							$r[]=urlencode($k).'='.urlencode($v);
				}
				elseif($pv or (string)$pv=='0')
					$r[]=urlencode($pk).'='.urlencode($pv);

			if($suf)
				$r[]=$suf;
			if($pr===true)
				$pr=$this->file.$this->d_prefix;

			if($e===true)
				$e='';

			$r=$r ? $pr.join('&amp;',$r).($e===false ? '&amp;' : $e) : ($e===false ? $pr : preg_replace('#(&amp;|&|\?)$#','',$pr).$e);
		}
		return$r;
	}

	public function Parse(array$params=array(),$pd=true)#Parse defis
	{		if($this->is_static)
		{			$input=$this->string;

			$input=ltrim($input,$this->delimiter);
			/*if(strpos($input,$this->delimiter)===0)
				$input=substr($input,strlen($this->delimiter));*/

			$a=$input=='' ? array() : explode($this->delimiter,$input);
			/*$a=array();
			if(strpos($input,$this->defis)!==false and strlen($this->defis)>strlen($this->delimiter))
			{
				$delim=count_chars($this->delimiter,1);
				$defis=count_chars($this->defis,1);
				if(count(array_diff_key($delim,$defis))>0)
					$a=preg_split('#(?<=[a-z0-9'.constant(Language::$main.'::ALPHABET').'])'.preg_quote($this->delimiter,'#').'(?=[a-z0-9'.constant(Language::$main.'::ALPHABET').'])#',$input);
			}
			if(!$a and $input)
				$a=explode($this->delimiter,$input);*/

			$r=array();
			$n=-1;
			foreach($a as &$v)
				if($pd and strpos($v,$this->defis)!==false)
				{
					$ek=explode($this->defis,$v,2);
					$r[$ek[0]]=$ek[1];
				}
				elseif(isset($params[++$n]))
					$r[$params[$n]]=$v;
				else
					$r[''][]=$v;
			$this->string='';		}
		else
			parse_str($this->string,$r);
		return$r;
	}

	/*
		Функция возвращает "окончание" строки. т.е. ".html", "/". Работает только для статики (по понятным причинам)
		Внимание! Рекомендуется всегда использовать окончание в УРЛах, если окончания не будет - функция будет работать неправильно.
	*/
	public function GetEnding($es=array(),$cut=true)
	{		if($es)
		{			$ends='';
			foreach((array)$es as $v)
				$ends.=preg_quote($v,'#').'|';
			$e=preg_match('#('.rtrim($ends,'|').')$#',$this->string,$m)>0 ? $m[1] : '';
		}
		else
		{			$ab=constant(Language::$main.'::ALPHABET');			$e=preg_match('/([^a-z0-9'.$ab.'][a-z0-9'.$ab.']*)$/',$this->string,$m)>0 ? $m[1] : '';
		}
		if($e and $cut)
			$this->string=substr($this->string,0,-strlen($e));
		return$e;	}
	/*
		Распарсить до первого нужного значения. Все, что идет после этого - уже параметры модуля.
	*/
	public function ParseToValue($p,$cut=true,$pd=true)#parse defis
	{		if(!$this->is_static)
			return isset($_GET[$p]) ? $_GET[$p] : false;
		$str=strtok($this->string,$this->delimiter);
		$value=false;
		$a=array();
		$ending=preg_quote($this->ending,'#');
		while($str!==false)
		{			if(!$pd or strpos($str,$this->defis)===false)
			{				$value=$str;
				break;			}
			else
			{				$temp=explode($this->defis,$str,2);
				if($temp[0]==$p)
				{					$value=$temp[1];
					break;				}
				elseif($cut)
					$a[$temp[0]]=preg_replace('#'.$ending.'$#i','',$temp[1]);			}
			$str=strtok($this->delimiter);		}
		if($a)
			$_GET+=$a;
		if($cut)
			$this->string=strtok('');
		if($value)
			$value=preg_replace('#'.$ending.'$#i','',$value);
		return$value;
	}

	/*
		Функция создает из строки ее ЧПУшное представление - заменяет все непечатаемые символы и пробелы на $rep
	*/
	public function Filter($s,$l=false,$rep=false)
	{		if(!$l)
			$l=Language::$main;
		if(Eleanor::$vars['trans_uri'] and method_exists($l,'Translit'))
			$s=$l::Translit($s);
		if($rep===false)#ToDo! parent::framework
			$rep=Eleanor::$vars['url_rep_space'];

		$s=preg_replace(array('`('.preg_quote($this->defis,'`').'|'.preg_quote($this->delimiter,'`').'|[\\\\=\s#,"\'\\/:*\?&\+<>%\|])+`','#('.preg_quote($this->ending,'#').')+$#'),$rep,$s);
		$rep=preg_quote($rep,'#');
		return preg_replace('#^('.$rep.')+|('.$rep.')+$#','',$s);
	}

	public function Prefix($e=true)
	{		if($this->furl)
			return$e===false ? $this->s_prefix : preg_replace('#'.preg_quote($this->delimiter,'#').'$#','',$this->s_prefix).($e===true ? $this->ending : $e);

		$p=$this->file.$this->d_prefix;
		return$e===false ? $p : preg_replace('#(&amp;|&|\?)$#','',$p).($e===true ? '' : $e);
	}

	public function GetDel()
	{		return $this->furl ? $this->delimiter : '&amp;';	}

	public function SetPrefix($p,$a=false)
	{		if($p and is_array($p))
		{			$f=$this->furl;
			$this->furl=true;			$this->s_prefix=($a ? $this->s_prefix : '').$this->Construct($p,false,false);			$this->furl=false;
			$this->d_prefix=($a ? $this->d_prefix : '?').$this->Construct($p,false,false);
			$this->furl=$f;
		}
		elseif($this->furl)
		{			$p=preg_replace('#('.preg_quote($this->delimiter,'#').'|'.preg_quote($this->ending,'#').')+$#','',$p).$this->delimiter;
			$this->s_prefix=$a ? $this->s_prefix.$p : $p;
		}
		else
		{
			if(!$p and !$a)
				$p='?';
			else
			{
				$p=preg_replace('#(&amp;)+$#','',$p);
				if(false!==$qp=strpos($p,'?'))
					$p=substr($p,$qp);
				$p.='&amp;';
			}
			$this->d_prefix=$a ? $this->d_prefix.$p : $p;
		}
	}

	public function __construct($qs=false)
	{
		if($qs===false)
			$qs=isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING'];
		if(strpos($qs,'!')===0)
		{
			$qs=substr($qs,1);
			$ap=strpos($qs,'!&');

			if($ap!==false)
				$qs=substr($qs,0,$ap);
			elseif(preg_match('#.*!$#',$qs)>0)
				$qs=substr($qs,0,-1);

			$this->string=static::Decode($qs);
			$this->is_static=true;
		}
		$this->file=Eleanor::$filename;
	}

	public static function Encode($s)
	{
		return urlencode(CHARSET=='utf-8' ? $s : mb_convert_encoding((string)$s,'utf-8'));
	}

	public static function Decode($s)
	{		$s=urldecode($s);
		return preg_match('/^.{1}/us',$s)==1 ? mb_convert_encoding($s,CHARSET,'utf-8') : $s;
	}

	protected static function QueryPart($a,$p,&$r)
	{
		$i=0;
		foreach($a as $k=>&$v)
			if(is_array($v))
				static::QueryPart($v,$p.$k.'][',$r);
			elseif($v or (string)$v=='0')
				$r[]=$p.(($k===$i++) ? '' : urlencode($k)).']='.(is_string($v) ? urlencode($v) : (int)$v);
	}

	public static function Query($a,array$o=array())
	{
		$o+=array(
			'delim'=>'&amp;',
		);
		$r=array();
		foreach($a as $k=>&$v)
		{			$k=urlencode($k);
			if(is_array($v))
				static::QueryPart($v,$k.'[',$r);
			elseif($v or (string)$v=='0')
				$r[]=$k.'='.(is_string($v) ? urlencode($v) : (int)$v);
		}
		return join($o['delim'],$r);
	}
}