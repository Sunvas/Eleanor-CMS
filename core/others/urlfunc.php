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
class UrlFunc
{
	public
		$pa,
		$o,
		$d,
		$p,
		$e,
		$s;

	public function __construct(array$pa,array$o,$d,$p,$s='',$e='')
	{
		$this->pa=$pa;
		$this->o=$o;
		$this->d=$d;
		$this->p=$p;
		$this->s=$s;
		$this->e=$e;
	}

	public function __invoke()
	{
		$a=func_get_args();
		$i=0;
		foreach($this->o as $k=>&$v)
		{
			if(isset($a[$i]))
				$this->pa[$k]=$v($a[$i]);
			else
				break;
			$i++;
		}
		return$this;
	}

	public function __toString()
	{
		$r=array_filter($this->pa,function($v){
			return isset($v);
		});
		return($r ? $this->p.join($this->d,$r).$this->s.$this->e : preg_replace('#(&amp;|&|\?)$#','',$this->p).$this->s);
	}
}