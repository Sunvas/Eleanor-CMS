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

		if(isset($d[1],$d[2],$d[0]['class'],$d[1]['class']) and $d[0]['class']=='EE_SQL' and $d[1]['class']=='Db')
			$d=isset($d[1]['function']) && $d[1]['function']=='Query' ? $d[1] : $d[2];
		else
			$d=$d[0];
		$this->file=$d['file'];
		$this->line=$d['line'];

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