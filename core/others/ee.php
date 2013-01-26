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
class EE extends Exception
{
	public
		$code,#Код исключения
		$extra;#Массив с дополнительными параметрами

	const
		USER=1,#Ошибка пользователя, выполнение некорректных действий: ошибка доступа (403, 404 ...), некорректно заполнена форма и т.п.
		DEV=2,#Ошибки разработчика: обращение к неинициализированной переменной, свойству, методу
		ENV=4,#Ошибки среды: когда нет доступа для чтения/записи в файл, нет самого файла и т.п.
		UNIT=8;#Ошибка внутри подпрограммы: передача внешним сервисом некорректной информации и т.п.

	/**
	 * Конструктор системных исключений
	 *
	 * @param string $mess Описание исключения
	 * @param int $code Код исключения
	 * @param array $extra Дополнительные данные исключения
	 * @param exception $PO Предыдущее перехваченное исключение, что послужило "родителем" для текущего
	 */
	public function __construct($mess,$code=self::USER,array$extra=array(),$PO=null)
	{
		if(isset($PO,$PO->extra))
			$extra+=$PO->extra;

		if(!empty($extra['lang']))
		{
			$le=Eleanor::$Language['exceptions'];
			if(isset($le[$mess]))
			{
				$extra['code']=$mess;
				$mess=is_callable($le[$mess]) ? $le[$mess]($extra) : $le[$mess];
			}
		}

		if(isset($extra['file']))
			$this->file=$extra['file'];
		if(isset($extra['line']))
			$this->line=$extra['line'];
		$this->extra=$extra;
		parent::__construct($mess,$code,$PO);
	}

	public function __toString()
	{
		return$this->getMessage();
	}

	/**
	 * Непосредственная запись в лог файл.
	 *
	 * Лог ошибок состоит из двух файлов: *.log и *.inc Первый содержит непосредственно лог для открытия любым удобным способом.
	 * Второй же содержит служебную информацию для группировки идентичных записей
	 *
	 * @param string $fn Имя лог файла
	 * @param string $id Уникальный идентификатор записи
	 * @param callback $F Функция для генерации записи в лог файл. Первым параметром получает данные, которые вернула в прошлый раз.
	 * Должна вернуть массив из двух элементов 0 - запись в лог файл, 1 - служебные данные, которые при следущем исключении будут переданы ей первым параметром.
	 */
	protected function LogWriter($fn,$id,$F)
	{
		if($fn==='' or Eleanor::$nolog)
			return;
		$path=Eleanor::$root.'addons/logs/'.$fn.'.log';
		$hpath=$path.'.inc';

		$isf=is_file($path);
		$ish=is_file($hpath);

		if($isf and !is_writeable($path) or !$isf and !is_writeable(Eleanor::$root.'addons/logs/'))
			die('File '.$fn.' is write-protected!');

		if($isf and filesize($path)>2097152)#2 Mb
		{
			if(self::CompressFile($path,substr($path,0,strrpos($path,'.')).'_'.date('Y-m-d_H-i-s')))
			{
				unlink($path);
				if($ish)
					unlink($hpath);
			}
			clearstatcache();
		}

		if($ish)
		{
			$help=file_get_contents($hpath);
			$help=$help ? (array)unserialize($help) : array();
		}
		else
			$help=array();

		$change=isset($help[$id]);
		$data=$F($change ? $help[$id]['d'] : array());
		if(!is_array($data) or !isset($data[0],$data[1]))
			return;
		list($data,$log)=$data;

		if($change and !isset($help[$id]['o'],$help[$id]['l']))
		{
			$change=false;
			unset($help[$id]);
		}

		if($change)
		{
			$offset=$help[$id]['o'];
			$length=$help[$id]['l'];
			unset($help[$id]);

			$size=$isf ? filesize($path) : 0;
			if($size<$offset+$length)
			{
				$change=false;
				foreach($help as &$v)
					if($size<$v['o']+$v['l'])
						unset($v['o'],$v['l']);
			}
		}

		if($change)
		{
			$fh=fopen($path,'rb+');
			if(flock($fh,LOCK_EX))
				$diff=Files::FReplace($fh,$log,$offset,$length);
			else
			{
				fclose($fh);
				return false;
			}
			$length+=$diff;
			foreach($help as &$v)
				if($v['o']>$offset)
					$v['o']+=$diff;
		}
		else
		{
			$fh=fopen($path,'a');
			if(flock($fh,LOCK_EX))
			{
				$size=fstat($fh);
				$offset=$size['size'];
				$length=strlen($log);
				fwrite($fh,$log.PHP_EOL.PHP_EOL);
			}
			else
			{
				fclose($fh);
				return false;
			}
		}
		$help[$id]=array('o'=>$offset,'l'=>$length,'d'=>$data);
		flock($fh,LOCK_UN);
		fclose($fh);

		file_put_contents($hpath,serialize($help));
		return true;
	}

	/**
	 * Команда залогировать исключение
	 */
	public function Log()
	{
		$THIS=$this;#PHP 5.4 убрать рудмиент
		switch($this->code)
		{
			case self::USER:
				#Пока логируются только ошибочные запросы
				if(isset($this->extra['code'],$this->extra['back']))
					$this->LogWriter(
						'request_errors',
						md5($this->extra['code'].Url::$curpage),
						function($data)use($THIS)
						{
							$uinfo=Eleanor::$Login->GetUserValue(array('id','name'));

							$data['n']=isset($data['n']) ? $data['n']+1 : 1;
							$data['p']=Url::$curpage;
							$data['ip']=Eleanor::$ip;
							$data['d']=date('Y-m-d H:i:s');
							if($uinfo)
							{
								$data['u']=$uinfo['name'];
								$data['ui']=$uinfo['id'];
							}
							$data['b']=getenv('HTTP_USER_AGENT');
							$data['e']=$THIS->extra['code'].' - '.$THIS->getMessage();
							if($THIS->extra['back'] and (!isset($data['r']) or !in_array($THIS->extra['back'],$data['r'])))
								$data['r'][]=$THIS->extra['back'];
							$dcnt=count($data['r']);
							if($dcnt>50)
								array_splice($data['r'],0,$dcnt-50);

							return array(
								$data,
								$data['e'].'('.$data['n'].'): '.(Url::$curpage ? Url::$curpage : '/').PHP_EOL.'Date: '.$data['d'].PHP_EOL.'IP: '.$data['ip'].PHP_EOL.($uinfo ? 'User: '.$data['u'].PHP_EOL : '').'Browser: '.$data['b'].PHP_EOL.'Referrers: '.join(', ',$data['r'])
							);
						}
					);
			break;
			default:
				$this->LogWriter(
					'errors',
					md5($this->line.$this->file.$this->message),
					function($data)use($THIS)
					{
						$data['n']=isset($data['n']) ? $data['n']+1 : 1;
						$data['p']=Url::$curpage;
						$data['d']=date('Y-m-d H:i:s');

						$data['f']=substr($THIS->getFile(),strlen(Eleanor::$root));
						$data['l']=$THIS->getLine();
						$data['e']=$THIS->getMessage();

						return array(
							$data,
							($data['n']>1 ? substr_replace($data['e'],'('.$data['n'].')',strpos($data['e'],':'),0) : $data['e']).PHP_EOL.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL.'URL: '.(Url::$curpage ? Url::$curpage : '/').PHP_EOL.'Date: '.$data['d']
						);
					}
				);
		}
	}

	/**
	 * Создание архива лог файла для экономии места.
	 *
	 * @param string $from Путь к сжимаемому файлу
	 * @param string $to Путь с зажатому файлу (результату)
	 */
	static function CompressFile($from,$to)
	{
		if(!is_file($from) or file_exists($to))
			return false;
		if(!is_writable(substr($to,0,strrpos($to,'/'))))
			return false;
		$hf=fopen($from,'r');
		$r=false;
		if(function_exists('bzopen') and $hbz=bzopen($to.'.bz2','w'))
		{
			while(!feof($hf))
				bzwrite($hbz,fread($hf,1024*16));
			bzclose($hbz);
			$r=true;
		}
		elseif(function_exists('gzopen') and $hgz=gzopen($to.'.gz','w9'))
		{
			while(!feof($hf))
				gzwrite($hgz,fread($hf,1024*64));
			gzclose($hgz);
			$r=true;
		}
		fclose($hf);
		return$r;
	}
}