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
class CacheMachineApc implements CacheMachineInterface
{	private
		$u,
		$n=array(''=>true);#Массив имен того, что у нас есть в кеше.

	public function __construct($u='')
	{
		$this->u=$u;
		$this->n=$this->Get('');
		if(!$this->n or !is_array($this->n))
			$this->n=array();
	}

	public function __destruct()
	{
		$this->Put('',$this->n);
	}

	public function Put($k,$v,$t=0)
	{
		$r=apc_store($this->u.$k,$v,$t);
		if($r)
			$this->n[$k]=$t+time();
		return$r;
	}

	public function Get($k)
	{
		if(!isset($this->n[$k]))
			return false;
		$r=apc_fetch($this->u.$k,$s);
		if(!$s)
			unset($this->n[$k]);
		return$r;
	}

	public function Delete($k)
	{
		unset($this->n[$k]);
		return apc_delete($this->u.$k);
	}

	public function CleanByTag($t)
	{
		foreach($this->n as $k=>&$v)
			if(!$t or strpos($k,$t)!==false)
				$this->Delete($k);
	}
}