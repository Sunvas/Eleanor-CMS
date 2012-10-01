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
class EE_SQL extends EE
{
	private
		$qerror,
		$query;

	public function __construct($error,$params=array(),$PO=null)
	{
		$lang=Eleanor::$Language->Load('langs/db-*.php',false);
		$params+=array('query'=>false,'no'=>false,'error'=>false);
		$d=debug_backtrace();
		foreach($d as &$v)
		{
			if((!isset($v['class']) or $v['class']!='Db') and isset($params['file'],$params['line']))
				break;
			$this->file=$v['file'];
			$this->line=$v['line'];
		}
		switch($error)
		{
			case'connect':
				$error=$lang['connect']($params);
			break;
			case'query':
				$error=$lang['query']($params);
		}
		$this->qerror=$params['error'];
		$this->query=$params['query'];
		parent::__construct($error,EE::ALT,$params,$PO);
		if(!isset(self::$vars['log_db_errors']))
			self::$vars['log_db_errors']='addons/logs/db_errors.log';
	}

	public function LogIt()
	{		if(self::$vars['log_db_errors'])
			parent::LogIt(self::$vars['log_db_errors'],$this->qerror.PHP_EOL.'Query: '.$this->query);
	}
}