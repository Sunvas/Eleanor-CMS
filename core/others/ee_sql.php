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
		$type;

	public function __construct($type,$extra=array(),$PO=null)
	{
		$lang=Eleanor::$Language->Load('langs/db-*.php',false);
		$extra+=array('query'=>false,'no'=>false,'error'=>false);
		$d=debug_backtrace();

		if(isset($d[1],$d[2],$d[0]['class'],$d[1]['class']) and $d[0]['class']=='EE_SQL' and $d[1]['class']=='Db')
			$d=isset($d[1]['function']) && $d[1]['function']=='Query' ? $d[1] : $d[2];
		else
			$d=$d[0];

		$this->file=$d['file'];
		$this->line=$d['line'];
		$this->type=$type;

		switch($type)
		{
			case'connect':
				$mess=$lang['connect']($extra);
				$code=EE::UNIT;
			break;
			case'query':
				$mess=$lang['query']($extra);
				$code=EE::DEV;
			break;
			default:
				$mess=$extra['error'];
				$code=EE::UNIT;
		}
		parent::__construct($mess,$code,$extra,$PO);
	}

	public function Log()
	{		$THIS=$this;#PHP 5.4 убрать рудмиент
		$this->LogWriter(
			'db_errors',
			md5($this->extra['error'].$this->line.$this->file),
			function($data)use($THIS)
			{				$data['n']=isset($data['n']) ? $data['n']+1 : 1;
				$data['d']=date('Y-m-d H:i:s');
				$data['e']=$THIS->extra['error'];
				$data['f']=substr($THIS->getFile(),strlen(Eleanor::$root));
				$data['l']=$THIS->getLine();
				$log=$data['e'].PHP_EOL;

				switch($THIS->type)
				{					case'connect':
						if(strpos($data['e'],'Access denied for user')===false)
						{
							$data['h']=isset($this->extra['host']) ? $this->extra['host'] : '';
							$data['u']=isset($this->extra['user']) ? $this->extra['user'] : '';
							$data['p']=isset($this->extra['pass']) ? $this->extra['pass'] : '';
							$log.='Host: '.$data['h'].PHP_EOL.'User: '.$data['u'].PHP_EOL;
						}

						$data['db']=isset($this->extra['db']) ? $this->extra['db'] : '';
						$log.='DB: '.$data['db'].PHP_EOL.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'Date: '.$data['d'].PHP_EOL.'Happend: '.$data['n'];
					break;
					case'query':
						$data['q']=$THIS->extra['query'];
						$log.='Query: '.$data['q'].PHP_EOL.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'Date: '.$data['d'].PHP_EOL.'Happend: '.$data['n'];
					break;
					default:
						$log.='File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'Date: '.$data['d'].PHP_EOL.'Happend: '.$data['n'];				}
				return array($data,$log);			}
		);
	}
}