<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/

class Files
{	public static function BytesToSize($b)
	{		/*
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
		#b bk mb gb tb pb eb	}

	public static function SizeToBytes($b)
	{		$bytes=(int)$b;
		if(isset($b[1]))
			switch(preg_match('#([a-z]+)\s*$#i',$b,$m)>0 ? strtolower($m[1]) : '')
			{				/*				case 'eb':
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
					return $bytes*1073741824;				case'mb':
				case'm':
					return $bytes*1048576;
				case'kb':
				case'k':
					return $bytes*1024;
			}
		return$bytes;
	}
	/*
		Метод, позволяющий корректно отдать пользователю содержимое в виде файла
	*/
	public static function OutputStream(array$a)
	{		$a+=array(
			'data'=>'',#Текст того, что нужно передать
			'file'=>'',#Файл, который будет прочитан и передан пользователю имеет приоритет над data
			'filename'=>false,#Имя файла, которое будет отображенопользователю
			'last-modified'=>mktime(0,0,0),#Время последнего изменения файла, иначе не сработает дозагрузка
			'multithread'=>true,#Разрешить мультипоточность при загрузке
			'mimetype'=>false,#Определенный mimetype для передачи файла
			'type'=>'',#Тип файла
			'etag'=>false,
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
		$fn=preg_match('#^[a-z0-9\-_\.]+$#i',$a['filename'])>0 ? $a['filename'] : '=?'.DISPLAY_CHARSET.'?B?'.base64_encode($a['filename']).'?=';
		if($a['save'])
			header('Content-Disposition: attachment; filename="'.$fn.'"');
		else
			header('Content-Disposition: inline; filename="'.$fn.'"');

		$ifr=isset($_SERVER['HTTP_IF_RANGE']) ? trim($_SERVER['HTTP_IF_RANGE']) : false;
		if($ifr and $ifr==$etag || strtotime($ifr)==$lm)
			$ifr=false;

		if($a['multithread'] and isset($_SERVER['HTTP_RANGE']) and !$ifr)
		{			#Поддержка мультипромежуточного запроса
			$range=array();
			$m=preg_match('/^bytes=((?:\d*-\d*,?\s?)+)/',$_SERVER['HTTP_RANGE'],$m)>0 ? explode(',',$m[1]) : array();
			foreach($m as $v)
			{				$v=explode('-',trim($v),2);
				if($v[0])
				{					#Если первый предел выходит за рамки размера					if($v[0]>$zsize or ($v[1] and $v[0]>$v[1]))
						continue;
					if(!$v[1] or $v[1]>$zsize)
						$v[1]=$zsize;				}
				elseif($v[1])
				{					if(0>$v[0]=$zsize-$v[1])
						continue;
					$v[1]=$zsize;				}
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
		{			$f=fopen($a['file'],'rb');
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

	/*
		Метод копирования файлов и каталогов
	*/
	public static function Copy($source,$dest)
	{		if(!file_exists($source))
			return false;
		if(is_file($source))
		{
			self::MkDir(dirname($dest));
			return copy($source,$dest);
		}
		if(is_link($source))
			return symlink(readlink($source),$dest);
		$Dir=dir($source);
		$f=__function__;
		while($entry=$Dir->read())
		{
			if($entry=='.' or $entry == '..')
				continue;
			self::$f($source.DIRECTORY_SEPARATOR.$entry,$dest.DIRECTORY_SEPARATOR.$entry);
		}
		$Dir->close();
		return true;
	}

	public static function MkDir($path)
	{
		$f=__function__;
		if(!is_dir($path))
		{
			self::$f(dirname($path));
			return mkdir($path);
		}
		return true;
	}

	public static function Delete($path)
	{
		if(is_dir($path) and !is_link($path))
		{
			$f=__function__;
			$Dir=dir($path);
			while($entry=$Dir->read())
			{
				if($entry=='.' or $entry=='..')
					continue;
				if(!self::$f($path.DIRECTORY_SEPARATOR.$entry))
					return true;
			}
			$Dir->close();
			return rmdir($path);
		}
		return is_file($path) ? unlink($path) : true;
	}

	public static function GetSize($path,$filter=false)
	{		if(is_link($path))
			return 0;
		if(is_dir($path))
		{			$size=0;
			$f=__function__;
			$Dir=dir($path);
			while($entry=$Dir->read())
			{
				if($entry=='.' or $entry=='..')
					continue;
				$size+=self::$f($path.DIRECTORY_SEPARATOR.$entry,$filter);
			}
			$Dir->close();
			return$size;
		}
		return is_file($path) && (!is_callable($filter) or call_user_func($filter,$path)) ? filesize($path) : 0;
	}

	/*
		Метод дописывания в средину файла. Функция идентична функции substr_replace, только для работы с файлом.
		Для корректно работы функции, нужно открывать файлы в режиме rb+. Режим a (дописывание в конец файла) НЕ ПОДДЕРЖИВАЕТСЯ (особенность PHP)!
		$fh - файловый указатель
		2й, 3й и 4й параметры - идентичны функции substr_replace
		$buf - объем байтов, считываемых за раз
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
						$data=fread($fh,$buf-$limiter+$i);
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
}