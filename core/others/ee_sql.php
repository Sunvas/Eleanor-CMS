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
	public#private - #PHP 5.4, заменить после того, как уберется костыль $THIS ниже
		$type;

	/**
	 * Конструктор исключений, связанных с базой данных
	 *
	 * @param string $type Тип исключения: connect - ошибка при подключении, query - ошибка при запросе
	 * @param array $extra Дополнительные данные исключения
	 * @param exception $PO Предыдущее перехваченное исключение, что послужило "родителем" для текущего
	 */
	public function __construct($type,$extra=array(),$PO=null)
	{
		$lang=Eleanor::$Language->Load('langs/db-*.php',false);
		$extra+=array('query'=>false,'no'=>false,'error'=>false);
		$db=debug_backtrace();

		$d=end($db);
		foreach($db as $v)
		{
			if(isset($v['class']) and $v['class']=='Db')
				$d=$v;
		}

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

	/**
	 * Команда залогировать исключение
	 */
	public function Log()
	{
		$THIS=$this;#PHP 5.4 убрать рудмиент
		$this->LogWriter(
			'db_errors',
			md5($this->extra['error'].$this->line.$this->file),
			function($data)use($THIS)
			{
				$data['n']=isset($data['n']) ? $data['n']+1 : 1;
				$data['d']=date('Y-m-d H:i:s');
				$data['e']=$THIS->extra['error'];
				$data['f']=substr($THIS->getFile(),strlen(Eleanor::$root));
				$data['l']=$THIS->getLine();
				$data['t']=$THIS->type;
				$log=$data['e'].PHP_EOL;

				switch($THIS->type)
				{
					case'connect':
						if(strpos($data['e'],'Access denied for user')===false)
						{
							$data['h']=isset($THIS->extra['host']) ? $THIS->extra['host'] : '';
							$data['u']=isset($THIS->extra['user']) ? $THIS->extra['user'] : '';
							$data['p']=isset($THIS->extra['pass']) ? $THIS->extra['pass'] : '';
							$log.='Host: '.$data['h'].PHP_EOL.'User: '.$data['u'].PHP_EOL;
						}

						$data['db']=isset($THIS->extra['db']) ? $THIS->extra['db'] : '';
						$log.='DB: '.$data['db'].PHP_EOL.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'Date: '.$data['d'].PHP_EOL.'Happend: '.$data['n'];
					break;
					case'query':
						$data['q']=$THIS->extra['query'];
						$log.='Query: '.$data['q'].PHP_EOL.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'Date: '.$data['d'].PHP_EOL.'Happend: '.$data['n'];
					break;
					default:
						$log.='File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'Date: '.$data['d'].PHP_EOL.'Happend: '.$data['n'];
				}
				return array($data,$log);
			}
		);
	}
}