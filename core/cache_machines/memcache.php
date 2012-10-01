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
class CacheMachineMemCache implements CacheMachineInterface
{	private
		$u,
		$n=array(''=>true),#Массив имен того, что у нас есть в кеше.
		$L=false;#Объект MemCache-a

	public function __construct($u='')
	{
		$this->u=$u;
		#Поскольку данная кеш-машина весьма специфична, рекомендую прописать значения самостоятельно.
		$this->L=memcache_connect('localhost');
		#memcache_add_server($this->L, 'server', 'port');
		if(!$this->L)
			throw new EE('MemCache failure '.__file__);
		if(function_exists('memcache_set_compress_threshold'))
			memcache_set_compress_threshold($this->L,20000,0.2);
		$this->n=$this->Get('');
		if(!$this->n or !is_array($this->n))
			$this->n=array();
	}

	public function __destruct()
	{		$this->Put('',$this->n);
		memcache_close($this->L);
	}

	public function Put($k,$v,$t=0)
	{
		$r=memcache_set($this->L,$this->u.$k,$v,(is_bool($v) or is_int($v) or is_float($v)) ? 0 : MEMCACHE_COMPRESSED,$t);
		if($r)
			$this->n[$k]=$t+time();
		return$r;
	}

	public function Get($k)
	{
		if(!isset($this->n[$k]))
			return false;
		$r=memcache_get($this->L,$this->u.$k);
		if($r===false)
			unset($this->n[$k]);
		return$r;
	}

	public function Delete($k)
	{
		unset($this->n[$k]);
		return memcache_delete($this->L,$this->u.$k);
	}

	public function CleanByTag($t)
	{
		foreach($this->n as $k=>&$v)
			if(!$t or strpos($k,$t)!==false)
				$this->Delete($k);
	}
}