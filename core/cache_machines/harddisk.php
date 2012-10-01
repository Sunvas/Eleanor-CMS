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
class CacheMachineHardDisk implements CacheMachineInterface
{
	public function __construct()
	{
		if(!is_writeable(Eleanor::$root.'cache'))
			throw new EE('Folder /cache is write-protected!');
	}

	public function Put($k,$v,$t=0)
	{
		if($t>0)
			$t+=time();
		return file_put_contents(Eleanor::$root.'cache/'.$k.'.php','<?php $t='.(int)$t.';$v='.var_export($v,true).';');
	}

	public function Get($k)
	{
		$v=false;
		$t=0;
		if(is_file($f=Eleanor::$root.'cache/'.$k.'.php'))
			include$f;
		if($t>0 and $t<time())
		{
			$this->Delete($k);
			return false;
		}
		return$v;
	}

	public function Delete($k)
	{
		$r=Files::Delete(Eleanor::$root.'cache/'.$k.'.php');
		clearstatcache();
		return$r;
	}

	public function CleanByTag($t)
	{
		$t=str_replace('..','',$t);
		if($t)
			$t.='*';
		if($files=glob(Eleanor::$root.'cache/*'.$t.'.php'))
			foreach($files as &$f)
				Files::Delete($f);
		clearstatcache();
	}
}