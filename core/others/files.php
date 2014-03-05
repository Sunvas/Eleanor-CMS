<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/

class Files
{
	/**
	 * Преобразования числа байт в приблизительный читаемый формат
	 * @param int $b Число байт
	 */
	public static function BytesToSize($b)
	{
		/*
		if($b>1152921504606846976)
			return round($b/1152921504606846976,2).' eb';
		elseif($b>1125899906842624)
			return round($b/1125899906842624,2).' pb';
		elseif($b>1099511627776)
			return round($b/1099511627776,2).' tb';
		else*/if($b>=1073741824)
			return round($b/1073741824,2).' gb';
		elseif($b>=1048576)
			return round($b/1048576,2).' mb';
		elseif($b>=1024)
			return round($b/1024,2).' kb';
		return $b.' b';
		#b bk mb gb tb pb eb
	}

	/**
	 * Преобразование приблизительного читаемого размера файла в количество байт
	 * @param string $b Приблизительный читаем формат
	 */
	public static function SizeToBytes($b)
	{
		$bytes=(int)$b;
		if(isset($b[1]))
			switch(preg_match('#([a-z]+)\s*$#i',$b,$m)>0 ? strtolower($m[1]) : '')
			{
				/*
				case 'eb':
				case 'e':
					return $bytes*1152921504606846976;
				case 'pb':
				case 'p':
					return $bytes*1125899906842624;
				case 'tb':
				case 't':
					return $bytes*1099511627776;
				*/
				case'gb':
				case'g':
					return $bytes*1073741824;
				case'mb':
				case'm':
					return $bytes*1048576;
				case'kb':
				case'k':
					return $bytes*1024;
			}
		return$bytes;
	}

	/**
	 * Отдача содержимого клиенту в виде файла
	 * @param array $a Опции передачи. Детальнее смотрите в теле метода
	 */
	public static function OutputStream(array$a)
	{
		$a+=array(
			'data'=>'',#Данные, которые необходимо передать
			'file'=>'',#Файл, который будет прочитан и передан пользователю. Имеет приоритет над data
			'filename'=>false,#Имя файла, которое будет отображено пользователю
			'last-modified'=>mktime(0,0,0),#Время последнего изменения файла (иначе не сработает дозагрузка)
			'multithread'=>true,#Разрешить мультипоточность при загрузке
			'mimetype'=>false,#Определенный mimetype для передачи файла
			'type'=>'',#Тип файла
			'etag'=>false,#Etag передачи
			'save'=>true,#Показывать пользователю диалог для сохранения файла или нет? Для картинок рекомендуется ставить значение false
		);

		if(headers_sent())
			die;
		ignore_user_abort(false);

		if(!$a['filename'])
			$a['filename']=$a['file'] ? basename($a['file']) : 'file'.$a['type'];
		if(!$a['mimetype'])
			$a['mimetype']=Types::MimeTypeByExt($a['type'] ? $a['type'] : $a['filename'],'auto-detect');

		if(!$a['data'] and !is_file($a['file']))
			throw new EE('No file '.$a['filename'],EE::DEV);

		$size=$a['data'] ? strlen($a['data']) : filesize($a['file']);
		#Размер, включая 0 байт
		$zsize=$size-1;
		$etag=$a['etag'] ? $a['etag'] : md5($a['filename'].$a['mimetype']);
		$lm=$a['file'] ? filemtime($a['file']) : $a['last-modified'];
		header('Accept-Ranges: '.($a['multithread'] ? 'bytes' : 'none'));
		header('Connection: '.($a['multithread'] ? 'keep-alive' : 'close'));
		header('Content-Type: '.$a['mimetype']);
		header('Content-encoding: none');
		header('Etag: '.$etag);
		header('Date: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$lm).' GMT');
		$fn=preg_match('#^[a-z0-9\-_\.\(\)]+$#i',$a['filename'])>0 ? $a['filename'] : '=?'.DISPLAY_CHARSET.'?B?'.base64_encode($a['filename']).'?=';
		if($a['save'])
			header('Content-Disposition: attachment; filename="'.$fn.'"');
		else
			header('Content-Disposition: inline; filename="'.$fn.'"');

		$ifr=isset($_SERVER['HTTP_IF_RANGE']) ? trim($_SERVER['HTTP_IF_RANGE']) : false;
		if($ifr and $ifr==$etag || strtotime($ifr)==$lm)
			$ifr=false;

		if($a['multithread'] and isset($_SERVER['HTTP_RANGE']) and !$ifr)
		{
			#Поддержка мультипромежуточного запроса
			$range=array();
			$m=preg_match('/^bytes=((?:\d*-\d*,?\s?)+)/',$_SERVER['HTTP_RANGE'],$m)>0 ? explode(',',$m[1]) : array();
			foreach($m as $v)
			{
				$v=explode('-',trim($v),2);
				if($v[0])
				{
					#Если первый предел выходит за рамки размера
					if($v[0]>$zsize or ($v[1] and $v[0]>$v[1]))
						continue;
					if(!$v[1] or $v[1]>$zsize)
						$v[1]=$zsize;
				}
				elseif($v[1])
				{
					if(0>$v[0]=$zsize-$v[1])
						continue;
					$v[1]=$zsize;
				}
				else
					continue;
				$range[]=$v;
			}

			#Отсеим пересекающиеся промежутки т.е. 500-799,600-1023,800-849 => 500-1023
			foreach($range as $k1=>&$v1)
				foreach($range as $k2=>&$v2)
					if($k1!==$k2 and $v2[0]>=$v1[0] and $v2[0]<=$v1[1])
					{
						if($v1[1]<$v2[1])
							$v1[1]=$v2[1];
						unset($range[$k2]);
					}
			#Сбойные учатки? Пошлем такой запрос нах.й
			if(!$range)
			{
				header('HTTP/1.1 416 Requested range not satisfiable');
				die;
			}

			$s='';
			$total=0;
			foreach($range as &$v)
			{
				$s.=$v[0].'-'.$v[1].',';
				$total+=$v[1]-$v[0]+1;

				#Преобразуем в offset и length
				$v[1]-=$v[0]-1;
			}
			header('Content-Length: '.$total,true,206);
			header('Content-Range: bytes '.rtrim($s,',').'/'.$size);
		}
		else
		{
			header('Content-Length: '.$size,true,200);
			$range=array(array(0,$size));
		}

		if($a['data'])
			foreach($range as &$r)
			{
				echo substr($a['data'],$r[0],$r[1]);
				flush();
			}
		else
		{
			$f=fopen($a['file'],'rb');
			foreach($range as &$r)
			{
				fseek($f,$r[0],SEEK_SET);
				$d=0;
				while(!feof($f) and connection_status()==0 and $d<$r[1])
				{
					$b=min(1024*16,$r[1]-$d);
					$d+=$b;
					echo fread($f,$b);
					flush();
				}
			}
			fclose($f);
		}
		die;
	}

	/**
	 * Копирование файлов и каталогов
	 * @param string $source Источник: путь откуда будет происходить копирование
	 * @param string $dest Назначение: путь, куда будет происходить копирование
	 */
	public static function Copy($source,$dest)
	{
		$args=func_num_args();
		$origdest=$args==3 ? func_get_arg(2) : $dest;

		#Предотвращение копирования самого в себя
		if($source=='' or !file_exists($source) or strpos($source,$origdest)===0)
			return false;

		#Путь может быть неполным
		$source=realpath($source);

		$destdir=dirname($dest);
		static::MkDir($destdir);
		$dest=realpath($destdir).DIRECTORY_SEPARATOR.basename($dest);

		if($args==2)
			$origdest=$dest;

		if(is_link($source) or Eleanor::$os=='w' and readlink($source)!=$source)#Ниже важная информация
			return symlink(readlink($source),$dest);

		if(is_file($source))
			return copy($source,$dest);

		$f=__FUNCTION__;
		$files=array_diff(scandir($source),array('.','..'));

		foreach($files as $entry)
			static::$f($source.DIRECTORY_SEPARATOR.$entry,$dest.DIRECTORY_SEPARATOR.$entry,$origdest);

		return true;	
	}

	/**
	 * Рекурсивное создание симлинков. Каталоги копируются и симлинки копируются, а не симлинкуются
	 * @param string $source Источник: путь откуда будет происходить копирование
	 * @param string $dest Назначение: путь, куда будет происходить копирование
	 * @param bool $deldest Флаг обязательно очистки каталога-приемника
	 */
	public static function SymLink($source,$dest,$deldest=true)
	{
		#Очистка значений
		$source=realpath($source);#Путь может быть неполным
		$dest=rtrim($dest,'/\\');

		/*
			В винде символические ссылки всегда будут с полным путем, а в *nix системах - относительным.
			http://lists.unixcenter.ru/archives/mlug/2004-April/025317.html
		*/
		$nix=Eleanor::$os!='w';

		if(!file_exists($source))
			return false;

		if(is_link($source))
		{
			$source=readlink($source);
			if($nix)
			{
				$source=realpath($source);
				$source=static::ShortPath($dest,$source);
			}
			return symlink($source,$dest);
		}

		if($deldest ? file_exists($dest) : is_file($dest))
			static::Delete($dest);

		if(is_file($source))
		{
			static::MkDir(dirname($dest));
			return symlink($nix ? static::ShortPath($dest,$source) : $source,$dest);
		}

		$f=__function__;
		$files=array_diff(scandir($source),array('.','..'));

		foreach($files as $entry)
			static::$f($source.DIRECTORY_SEPARATOR.$entry,$dest.DIRECTORY_SEPARATOR.$entry);

		return true;
	}

	/**
	 * Обновление каталога с файлами, после сохранения записи к которой каталог относится
	 * @param string $temp Каталог, в котором происходили изменения
	 * @param string $dest "Рабочий" каталог с файлами, прикрепленный к записи
	 */
	public static function UpdateDir($temp,$dest)
	{
		#Очистка значений
		$temp=realpath($temp);#Путь может быть неполным
		$dest=rtrim($dest,'/\\');

		/*
			Внимание! Функция is_link на винде работает крайне нестабильно!
			Поэтому проверяем через костыли: если readlink($path)!=$path, значит перед нами ссылка,
			но есть ньюанс, в эту функцию в винде ВСЕГДА нужно передавать полные пути, иначе может не срабоать.
		*/
		$windows=Eleanor::$os=='w';

		if($windows)
		{#readlink на винде всегда возвращает ссылки с \
			$temp=str_replace('/',DIRECTORY_SEPARATOR,$temp);
			$dest=str_replace('/',DIRECTORY_SEPARATOR,$dest);
		}

		if(!is_dir($temp))
			return false;

		$links=array_diff(scandir($temp),array('.','..'));

		#Если $dest не существует или попросту не каталог - это существенно упрощает нам работу
		if(!is_dir($dest))
		{
			if(count($links)==0)
				return static::Delete($temp);

			if(file_exists($dest))
				static::Delete($dest);

			return rename($temp,$dest);
		}

		foreach($links as $k=>$file)
		{
			$fulltemp=$temp.DIRECTORY_SEPARATOR.$file;

			$fulltemp=realpath($fulltemp);#Путь может быть неполным
			if($windows ? is_file($fulltemp) && readlink($fulltemp)!=$fulltemp : is_link($fulltemp))
			{
				#Сперва проверим: возможно, мы пытаемся обновить совершенно чужие между собой каталоги
				$orig=readlink($fulltemp);

				if(!is_file($orig) || dirname($orig)!=$dest)
					throw new EE('DISPARATE_DIRS',EE::ENV);
				#Файл переименовали?
				elseif(basename($orig)!=$file)
					rename($orig,dirname($orig).DIRECTORY_SEPARATOR.$file);
			}
			#Переименовали каталог?
			elseif(is_dir($fulltemp) and !is_dir($dest.DIRECTORY_SEPARATOR.$file))
				if(static::FixRanamedDir($fulltemp,$dest)==='d')
				{
					die(__line__.' Delete: '.$links[$k]);
					unset($links[$k]);
				}
		}

		$files=array_diff(scandir($dest),array('.','..'));

		#Удалим удаленное
		foreach(array_diff($files,$links) as $file)
		{
			$fulldest=$dest.DIRECTORY_SEPARATOR.$file;
			$fulltemp=$temp.DIRECTORY_SEPARATOR.$file;

			#Если удалили или перезалили (файл)
			if(is_dir($fulldest) or $windows ? !is_file($fulltemp) || readlink($fulltemp)==$fulltemp : !is_link($fulltemp))
				static::Delete($fulldest);
		}

		#Перенесем теперь загруженное
		$f=__function__;
		foreach($links as $file)
		{
			$full=$temp.'/'.$file;
			$fulldest=$dest.'/'.$file;

			if(!in_array($file,$files))
				rename($full,$fulldest);
			elseif(is_dir($full))
				static::$f($full,$fulldest);
		}

		static::Delete($temp);
		return true;
	}

	/**
	 * Реализация действия, когда переименовывается каталог. Часть метода UpdateDir
	 * @param string $path Путь к каталогу, который переименовали
	 * @param string $parent Путь к каталогу-родителю, в котором содержтся непереименовый каталог
	 * @return string d - каталог удален
	 */
	protected static function FixRanamedDir($path,$parent)
	{
		$links=array_diff(scandir($path),array('.','..'));
		$windows=Eleanor::$os=='w';#Выше важная информация

		if(!$links)
		{
			ReturnD:
			static::Delete($path);
			return'd';
		}

		$recrsym=false;#Массив ссылок, которые нужно будет пересоздать
		$dirs=array();

		foreach($links as $k=>$file)
		{
			$full=$path.DIRECTORY_SEPARATOR.$file;
			if(is_dir($full))
				$dirs[$k]=$full;
			elseif($windows ? $rl=readlink($full) and $rl!=$full : is_link($full))
			{
				$orig=readlink($full);

				if(strpos($orig,$parent)===0)
				{
					#Что надо переименовать
					$torename=substr(dirname($orig),strlen($parent)+1);
					$torename=explode(DIRECTORY_SEPARATOR,$torename);

					#Во что надо переименовать
					$names=explode(DIRECTORY_SEPARATOR,$path);
					$names=array_slice($names,-count($torename));

					#Получим массив всех удаленных симлинков и путей, куда они вели
					$recrsym=static::GetDelSym($path,$links);
					break;
				}
			}
		}

		if($recrsym===false)
		{
			$f=__function__;
			foreach($dirs as $k=>$dir)
				if(static::$f($dir,$parent)==='d')
					unset($links[$k]);

			if(!$links)
				goto ReturnD;
		}
		else
		{
			$from=$to=$parent;
			foreach($names as $k=>$name)
			{
				$from.=DIRECTORY_SEPARATOR.$torename[$k];
				$to.=DIRECTORY_SEPARATOR.$name;

				if(rename($parent.DIRECTORY_SEPARATOR.$torename[$k],$parent.DIRECTORY_SEPARATOR.$name))
					$parent.=DIRECTORY_SEPARATOR.$name;
				else
					return false;
			}

			foreach($recrsym as $k=>$v)
				symlink(str_replace($from,$to,$v),$k);
		}
	}

	public static function GetDelSym($path,array$links=array())
	{
		if(!$links)
		{
			$links=array_diff(scandir($path),array('.','..'));
			if(!$links)
			{
				static::Delete($path);
				return array();
			}
		}

		$windows=Eleanor::$os=='w';#Выше важная информация
		$recrsym=array();
		$f=__function__;

		foreach($links as $k=>$file)
		{
			$full=$path.DIRECTORY_SEPARATOR.$file;

			if(is_dir($full))
				$recrsym+=static::$f($full);
			elseif($windows ? $rl=readlink($full) and $rl!=$full : is_link($full))
			{
				$recrsym[ $full ]=readlink($full);
				unlink($full);
			}
		}

		return$recrsym;
	}

	/**
	 * Создание каталога. В отличии от стандартной функции mkdir, метод позволяет создать сразу цепочку каталогов
	 * @param string $path Путь до каталога, который необходимо создать
	 */
	public static function MkDir($path)
	{
		$f=__function__;

		if($path!='' and !is_dir($path))
		{
			static::$f(dirname($path));
			return mkdir($path);
		}

		return true;
	}

	/**
	 * Удаление файлов, каталогов и ссылок на файлы
	 * @param string $path Путь к файлу, каталогу или ссылке которые нужно удалить
	 */
	public static function Delete($path,$nocheck=false)
	{
		if(is_dir($path))
		{
			$f=__function__;
			$entries=array_diff(scandir($path),array('.','..'));

			foreach($entries as $entry)
				if(!static::$f($path.DIRECTORY_SEPARATOR.$entry,true))
					return true;

			return rmdir($path);
		}
		#Если ссылка битая, file_exists её не определяет, поэтому $check
		return $nocheck||file_exists($path) ? unlink($path) : true;
	}

	/**
	 * Получение размера каталога
	 * @param sting $path Путь к каталогу, размер которого необходимо узнать
	 * @param callback|FALSE Функция для фильтрации, в случае если нужно считать размер каких-то определенных файлов. На первым аргументом получает адрес к файлу, должна вернуть bool
	 * @return int Возвращает СУММУ размеров всех внутрилежащих файлов, а не реальное занимаемое место на диске
	 */
	public static function GetSize($path,$filter=false)
	{
		if(is_link($path))
			return 0;

		if(is_dir($path))
		{
			$size=0;
			$f=__function__;
			$entries=array_diff(scandir($path),array('.','..'));

			foreach($entries as $entry)
				$size+=static::$f($path.DIRECTORY_SEPARATOR.$entry,$filter);

			return$size;
		}

		return is_file($path) && (!is_callable($filter) or call_user_func($filter,$path)) ? filesize($path) : 0;
	}

	/**
	 * Преобразование имен файлов в корректную последовательность символов для ОС Windows, где имена файлов задаются в однобайтовой кодировке.
	 * @param string $f Имя файла
	 * @param bool $dec Флаг декодирования (включение обратного преобразования)
	 */
	public static function Windows($f,$dec=false)
	{
		if(Eleanor::$os=='w' and CHARSET=='utf-8')
			$f=$dec ? mb_convert_encoding($f,CHARSET,'cp1251') : mb_convert_encoding($f,'cp1251');
		return$f;
	}

	/**
	 * Дописывание в средину файла. Метод идентичен функции substr_replace, только для работы с файлом.
	 * Для корректно работы функции, нужно открывать файлы в режиме rb+. Режим a (дописывание в конец файла) НЕ ПОДДЕРЖИВАЕТСЯ (особенность PHP)!
	 * @param resource $fh Файловый указатель, возвращаемый функцией fopen
	 * @param string $s Строка на замену (идентично 2му параметру функции substr_replace)
	 * @param int $o Отступ в байтах (идентично 3му параметру функции substr_replace)
	 * @param int $l Длина в байтах (идентично 4му параметру функции substr_replace)
	 * @param int $buf Число байтов, считываемых за раз
	 */
	public static function FReplace($fh,$s,$o,$l=0,$buf=4096)
	{
		$len=strlen($s);
		if(!is_resource($fh) or $len==0 and $l==0)
			return false;

		#PHP 5.4 fstat($fh)['size'];
		$size=fstat($fh);
		$size=$size['size'];

		$diff=$len-$l;
		if($diff==0 and $o<$size)
		{
			fseek($fh,$o,SEEK_SET);
			fwrite($fh,$s);
		}
		elseif($o>=$size)
		{
			fseek($fh,0,SEEK_END);
			fwrite($fh,$s);
		}
		else
		{
			$diff=strlen($s)-$l;

			if($diff>0)
			{
				$step=1;
				$limiter=$o+$l;
				do
				{
					$i=$size-$buf*$step++;
					if($i>$limiter)
					{
						$seek=$i;
						fseek($fh,$seek,SEEK_SET);
						$data=fread($fh,$buf);
					}
					else
					{
						$seek=$limiter;
						fseek($fh,$seek,SEEK_SET);
						$data=fread($fh,$buf+$limiter-$i);
					}
					fseek($fh,$seek+$diff,SEEK_SET);
					fwrite($fh,$data);
				}while($i>$limiter);
			}
			else
			{
				for($i=$o+$l;$i<$size;$i+=$buf)
				{
					fseek($fh,$i,SEEK_SET);
					$data=fread($fh,min($buf,$size-$i));
					fseek($fh,$i+$diff,SEEK_SET);
					fwrite($fh,$data);
				}
				ftruncate($fh,$size+$diff);
			}
			if($len>0)
			{
				fseek($fh,$o,SEEK_SET);
				fwrite($fh,$s);
			}
		}
		return$diff;
	}

	/**
	 * Генерация относительного путь для перехода из одного каталога в другой
	 * @param string $a Путь к первому каталогу
	 * @param string $b Путь ко второму каталогу
	 * @return string Например: ../../aa/bb/cc
	 */
	public static function ShortPath($a,$b)
	{
		$a=preg_split('#[/\\\\]+#',$a);
		$b=preg_split('#[/\\\\]+#',$b);
		$m=min($acnt=count($a),count($b));
		for($i=0;$i<$m;++$i)
		{
			if($i==0 and $a[0]!=$b[0])
				return false;
			if($a[$i]!=$b[$i])
				break;
		}
		$acnt-=$i+1;
		$ret=$acnt>0 ? array_merge(array_fill(0,$acnt,'..'),array_slice($b,$i)) : array_slice($b,$i);
		return join('/',$ret);
	}
}