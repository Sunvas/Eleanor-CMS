<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

#ToDo! array() => []
#Немножко упростим себе жизнь
ignore_user_abort(true);
error_reporting(E_ALL^E_NOTICE);
set_error_handler(array('Eleanor','ErrorHandle'));
set_exception_handler(array('Eleanor','ExceptionHandle'));
define('ELENT',ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED);
spl_autoload_register(array('Eleanor','Autoload'));

abstract class BaseClass
{
	private static function _BT($d)
	{
		foreach($d as &$v)
			if(isset($v['file'],$v['line']))
				return$v;
		return array('file'=>'-','line'=>'-');
	}

	public static function __callStatic($n,$p)
	{
		$d=self::_BT(debug_backtrace());
		$E=new EE('Called undefined method '.get_called_class().' :: '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
	}

	public function __call($n,$p)
	{		if(property_exists($this,$n) and is_object($this->$n) and method_exists($this->$n,'__invoke'))
			return call_user_func_array(array($this->$n,'__invoke'),$p);
		$d=self::_BT(debug_backtrace());
		$E=new EE('Called undefined method '.get_class().' -› '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
	}

	public function __toString()
	{
		$d=self::_BT(debug_backtrace());
		$E=new EE('Trying to get string form class '.get_class(),EE::DEV,array('file'=>$d[0]['file'],'line'=>$d[0]['line']));
		if(DEBUG)
			throw$E;
	}

	public function __invoke(){}#Для $class()

	public static function __set_state($a)#Для var_export($class)
	{
		$O=new get_class();
		foreach($a as $k=>&$v)
			$O->$k=$v;
		return$O;
	}

	public function __get($n)
	{
		if(is_array($n))
		{
			$d=$n;
			$n=func_get_arg(1);
		}
		else
			$d=debug_backtrace();
		$d=self::_BT($d);
		$E=new EE('Trying to get value from the unknown variable <code><b>'.get_class($this).' -› '.$n.'</b></code>',EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
	}
}

final class FilterArrays implements ArrayAccess
{
	private
		$vn;

	public function __construct($vn)
	{
		$this->vn=$vn;
	}

	public function offsetSet($k,$v)
	{
		$GLOBALS[$this->vn][$k]=$v;
	}

	public function offsetExists($k)
	{
		return isset($GLOBALS[$this->vn][$k]);
	}

	public function offsetUnset($k)
	{
		unset($GLOBALS[$this->vn][$k]);
	}

	public function offsetGet($k)
	{
		return isset($GLOBALS[$this->vn][$k]) ? self::Filter($GLOBALS[$this->vn][$k]) : null;
	}

	public static function Filter($n)
	{
		if(is_array($n))
		{
			foreach($n as &$v)
				$v=self::Filter($v);
			unset($v);
			return$n;
		}
		return htmlspecialchars($n,ELENT,CHARSET,false);
	}
}

final class Eleanor extends BaseClass
{
	public static
		$uploads='uploads',#Папка, где хранятся загружаемые файлы

		#Отладочная информация
		$debug=array(),#Сюда складируются данные отладки

		#Свойства генерируемой страницы.
		$gzip=true,#Состояние GZIP сжатия.
		$charset,#Выводимый в заголовках charset
		$caching,#Кешировать ли страницу и на сколько
		$last_mod,#Последнее изменение TIMESTAMP страницы по скрипту
		$modified,#Последнее изменение TIMESTAMP страницы по браузеру
		$maxage=0,#Срок жизни кэша на стороне браузера, без дополнительных валидаций со стороны сервера. В этот заголовок писать дополнительные параметры через запяту, например: 0, public
		$etag,#Etag страницы
		$content_type='text/html',#Выводимый в заголовках content-type

		#Свойства сайта
		$domain,#Ч Домен, с которого мы загрузились.
		$punycode,#Ч Punycode домена. Если домен нормальный - это ссылка на domain
		$site_path,#Ч. Каталог сайта
		$filename,#Ч. Название файла-сервиса, запускающего весь движок. Используется чаще всего для генерирования УРЛов

		#Свойства пользователя
		$ip,#Адрес, откуда мы загрузились
		$ips=array(),#Массив со всеми айпишниками, присланными нам от пользователя.
		$our_query=true,#Признак того, что пользователь пришел на эту страницу со своим запросом (а не с чужой страницы путем эмуляции). Изменение этого параметра положено на сервисы.
		$sessaddon='',#Дополнительная строка, которая будет писаться в таблицу сессий. Полезно для создания фичи во встроенных форумах: эту тему читают N пользователей
		$is_bot,#Посковый бот? Нет? - гость. Имя поискового бота

		#Масивы данных
		$langs=array(),#Массив языков
		$lvars=array(),#Резерв переменных конфигурационных настроек, для которых включена мультиязычность
		$vars=array(#Переменные, взятые из конфига.
			'page_caching'=>false,
			'gzip'=>true,
			'cookie_domain'=>'',
			'site_domain'=>'',
			'cookie_save_time'=>86400,
			'cookie_prefix'=>'',
			'multilang'=>false,
			'bot_group'=>0,
			'guest_group'=>0,
			'parked_domains'=>'',
			'exceptions_log_file'=>false,

			'bots_enable'=>false,
			'bots_list'=>array(),
			'time_online'=>array(),
		),
		$services,#Данные всех сервисов
		$perms=array(),#Данные разрешений. [таблица] => [ID] => [опция] => значение

		#Объекты
		$Db,#База данных
		$UsersDb,#База данных пользователей
		$Cache,#Кэш
		$Template,#Шаблон оформления
		$Language,#Объект, при конвертации его в строку - вернет имя языка
		$Login,#Объект главного логина
		$Permissions,#Разрешения главного логина
		$POST,#Отфильтрованный POST запрос

		#Системные свойства
		$os,#Тип системы, на которой стоит сайт u - *nix, w - windows
		$root,#Корень сайта
		$rootf,#Корень файла, с которого мы запустились
		$service,#Сервиса
		$nolog=false;#Не логировать ошибки

	private static
		$Instance;

	public static function getInstance($conf='config_general.php')
	{
		if(!isset(self::$Instance))
		{
			self::$Instance=new self();
			self::$root=dirname(dirname(__file__)).DIRECTORY_SEPARATOR;
			self::$rootf=dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
			self::$filename=basename($_SERVER['SCRIPT_FILENAME']);
			chdir(self::$root);
			#Detect IP
			self::$ip=$_SERVER['REMOTE_ADDR'];
			foreach(array('HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_X_COMING_FROM','HTTP_COMING_FROM','HTTP_CLIENT_IP','HTTP_X_CLUSTER_CLIENT_IP','HTTP_PROXY_USER','HTTP_XROXY_CONNECTION','HTTP_PROXY_CONNECTION','HTTP_USERAGENT_VIA') as $v)
			{
				$ip=getenv($v);
				if($ip!=self::$ip and $ip)
					self::$ips[$v]=$ip;
			}
			self::$ips=array_unique(self::$ips);
			#Detect IP [E]
			self::$domain=$_SERVER['HTTP_HOST'];
			self::$site_path=rtrim(dirname($_SERVER['PHP_SELF']),'/\\').'/';
			if(self::$filename and false!==$t=strpos(self::$site_path,self::$filename))
				self::$site_path=substr(self::$site_path,0,$t);
			self::$POST=$_SERVER['REQUEST_METHOD']=='POST' ? new FilterArrays('_POST') : array();
			self::$os=stripos(PHP_OS,'win')===0 ? 'w' : 'u';

			$c=false;
			if($conf)
			{
				$conf=self::FormatPath('',$conf);
				if(is_file($conf))
				{
					$c=include $conf;
					self::$langs=$c['langs'];
				}
			}

			#А мы-то собственно, установлены или нет?
			if(!defined('CHARSET'))
			{
				if(is_file(self::$root.'install/index.php') and !headers_sent())
					header('Location: http://'.self::$domain.self::$site_path.'install/');
				die('CMS Eleanor not installed!');
			}

			if(!DEBUG)
			{
				if(isset($_SERVER['HTTP_IF_NONE_MATCH']))
					self::$etag=$_SERVER['HTTP_IF_NONE_MATCH'];
				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					self::$modified=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			}

			self::$charset=DISPLAY_CHARSET;
			mb_internal_encoding(CHARSET);
			self::$Language=new Language(true);
			self::$Language->Change();
			self::$Cache=new Cache;
			if($c)
			{
				self::$Db=new Db(array(
					'host'=>$c['db_host'],
					'user'=>$c['db_user'],
					'pass'=>$c['db_pass'],
					'db'=>$c['db'],
				));
				if(isset($c['users']))
					self::$UsersDb=new Db(array(
						'host'=>$c['users']['db_host'],
						'user'=>$c['users']['db_user'],
						'pass'=>$c['users']['db_pass'],
						'db'=>$c['users']['db'],
					));
				else
					self::$UsersDb=&self::$Db;
				if(!isset(self::$services) and false===self::$services=self::$Cache->Get('system-services'))
				{
					self::$services=array();
					self::$Db->Query('SELECT `name`,`file`,`theme`,`login` FROM `'.P.'services`');
					while($a=self::$Db->fetch_assoc())
						self::$services[$a['name']]=array_slice($a,1);
					self::$Cache->Put('system-services',self::$services);
				}

				self::LoadOptions('system');
				if(self::$vars['time_zone'])
				{
					date_default_timezone_set(self::$vars['time_zone']);
					self::$Db->SyncTimeZone();
					if(self::$UsersDb!==self::$Db)
						self::$UsersDb->SyncTimeZone();
				}

				$task='';
				if(isset(self::$services['cron']))
				{
					$task=self::$Cache->Get('nextrun',true);
					$t=time();
					$task=$task===false || $task<=$t ? '<img src="'.self::$services['cron']['file'].'?rand='.$t.'" style="width:1px;height1px;" />' : '';
				}
				if(defined('ELEANOR_COPYRIGHT'))
					throw new EE('Copyright defined!',EE::FATAL);
				else
					#Внимание! САМОВОЛЬНОЕ УБИРАНИЕ КОПИРАЙТОВ ЧРЕВАТО БЛОКИРОВКОЙ НА ОФИЦИАЛЬНОМ САЙТЕ СИСТЕМЫ И ПРЕСЛЕДУЕТСЯ ПО ЗАКОНУ!
					#КОПИРАЙТЫ МЕНЯТЬ/ПРАВИТЬ НЕЛЬЗЯ! СОВСЕМ!! ОНИ ДОЛЖНЫ ОСТАВАТЬСЯ НЕИЗМЕННЫМИ ДО БИТА!
					define('ELEANOR_COPYRIGHT','<!-- ]]></script> --><a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © <!-- Eleanor CMS Team http://eleanor-cms.ru/copyright.php -->'.idate('Y').$task);

				$r=getenv('HTTP_REFERER');
				if($r and preg_match('#^'.PROTOCOL.'('.self::$vars['site_domain'].'|'.self::$domain.')'.self::$site_path.'#',$r)==0)
					self::$our_query=false;

				self::$caching=self::$vars['page_caching'];
				self::$gzip=self::$vars['gzip'];
				if(self::$vars['cookie_domain'])
					self::$vars['cookie_domain']=str_replace('*',preg_replace('#^www\.#i','',self::$domain),self::$vars['cookie_domain']);
				if(self::$vars['parked_domains']=='redirect' and self::$vars['site_domain'])
					self::$domain=self::$vars['site_domain'];
				#Заплатка для браузеров FF & IE, когда они не хотят воспринимать куки с доменов первого уровня аля localhost
				if(strpos(self::$domain,'.')===false)
					self::$vars['cookie_domain']='';
				#Заплатка для оперы и ко, которые не хотят воспринимать куки с IP адресов
				elseif(strpos(self::$domain,':')!==false or preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#',self::$domain)!=0)
					self::$vars['cookie_domain']='';
				#Поддержка IDN
				if(strpos(self::$domain,'xn--')!==false)
				{
					self::$punycode=self::$domain;
					self::$domain=Punycode::Domain(self::$domain,false);
				}
				elseif(preg_match('#^[a-z0-9\-\.]+$#i',self::$domain)==0)
					self::$punycode=Punycode::Domain(self::$domain);
				else
					self::$punycode=&self::$domain;
				$ips=explode(',',self::$vars['blocked_ips']);
				foreach($ips as &$bip)
				{
					if(strpos($bip,'=')!==false)
					{
						$m=substr($bip,strpos($bip,'=')+1);
						$bip=substr($bip,0,strpos($bip,'='));
					}
					else
						$m=self::$vars['blocked_message'];
					if(self::IPMatchMask(self::$ip,$bip))
						throw new EE($m,EE::BAN);
					foreach(self::$ips as &$ip)
						if(self::IPMatchMask($ip,$bip))
							throw new EE($m,EE::BAN);
				}
				unset(self::$vars['blocked_ips']);
			}
		}
		return self::$Instance;
	}
	#Защита идеологии Singleton
	private function __construct(){}

	public function __get($n)
	{
		if(class_exists($n))
			return$this->$n=new$n;
		return parent::__get(debug_backtrace(),$n);
	}

	public static function ErrorHandle($num,$str,$f,$l)
	{
		if(self::$nolog or $num&E_STRICT)
			return;
		$ae=array(
			E_ERROR=>'Error',
			E_WARNING=>'Warning',
			E_NOTICE=>'Notice'
		);
		if(class_exists('EE'))#Заплатка в случае отключенного автолоадера
		{
			$E=new EE((isset($ae[$num]) ? $ae[$num].': ' : '').$str,EE::DEV,array('file'=>$f,'line'=>$l));
			if(DEBUG)
				throw$E;
		}
	}

	public static function ExceptionHandle($E)
	{
		if($E instanceof EE)
		{
			if(isset($E->addon['call']) and is_callable($E->addon['call']))
				call_user_func($E->addon['call'],$E);
			$mess=$E->getMessage();
			if($E->addon['log'])
				$E->LogIt($E->addon['logfile'],$mess);
			Error($mess,$E->addon);
		}
		elseif(self::$vars['exceptions_log_file'])
		{
			$E2=new EE('',EE::INFO);
			$E2->LogIt(self::$vars['exceptions_log_file'],'Exception: '.$E->getMessage(),$E->getFile(),$E->getLine());
		}
	}

	public static function Autoload($cl)
	{
		if(is_file($f=self::$root.'core/others/'.strtolower($cl).'.php'))
			require$f;
		else
		{
			if(class_exists('EE',false) or include(self::$root.'core/others/ee.php'))
			{
				$d=debug_backtrace();
				$a=array();
				foreach($d as &$v)
					if(isset($v['file'],$v['line']) and $v['file']!=__file__)
					{
						$a['file']=$v['file'];
						$a['line']=$v['line'];
						break;
					}
				throw new EE('Class not found: '.$cl,EE::FATAL,$a);
			}
			trigger_error('Class not found: '.$cl,E_USER_ERROR);
		}
	}

	public static function LoadService()
	{
		if(self::$service and isset(self::$services[self::$service]))
			$a=self::$services[self::$service];
		else
			throw new EE('Unknown service!');
		if($a['file']!=self::$filename)
		{
			self::$Db->Update(P.'services',array('file'=>self::$filename),'`name`=\''.self::$service.'\' LIMIT 1');
			self::$Cache->Obsolete('system-services');
		}
		self::ApplyLogin($a['login']);
	}

	public static function LoadOptions($need,$r=false,$cache=true)
	{
		$need=(array)$need;
		$lgetted=$getted=array();
		if($cache)
			foreach($need as $k=>&$v)
				if($value=self::$Cache->Lib->Get('config-'.$v))
				{
					unset($need[$k]);
					foreach($value['v'] as $ok=>&$ov)
					{
						$getted[$ok]=$ov;
						$lgetted[$ok]=in_array($ok,$value['m']);
					}
				}
		if($need)
		{
			$kw=array();
			foreach($need as &$v)
				$kw[]=preg_quote(self::$Db->Escape($v,false));
			$ml=$config=$cache=array();
			$oid=0;
			$ogname='';
			self::$Db->Query('SELECT `o`.`id`,`o`.`name`,`l`.`value`,`l`.`serialized`,`l`.`language`,`o`.`multilang`,`g`.`name` `gname`,`g`.`keyword` FROM `'.P.'config` `o` INNER JOIN `'.P.'config_l` `l` USING(`id`) INNER JOIN `'.P.'config_groups` `g` ON `g`.`id`=`o`.`group` WHERE `g`.`keyword` REGEXP \''.join('|',$kw).'\' ORDER BY `o`.`id` ASC');
			while($a=self::$Db->fetch_assoc())
			{
				if($a['serialized'])
					$a['value']=unserialize($a['value']);
				if($oid==$a['id'])
				{
					if($a['multilang'])
						$cache[$a['gname']][$a['name']][$a['language']]=$a['value'];
					if($a['multilang'] or $a['language']!=Language::$main)
						continue;
				}
				$oid=$a['id'];
				if($ogname!=$a['gname'])
				{
					$ogname=$a['gname'];
					$temp=$a['keyword'] ? explode(',',trim($a['keyword'],',')) : array();
					foreach($temp as &$v)
						$config['config-'.$v][]=$a['gname'];
				}

				$cache[$a['gname']][$a['name']]=$a['multilang'] ? array($a['language']=>$a['value']) : $a['value'];
				if($a['multilang'])
					$ml[$a['gname']][$a['name']]=true;
			}
			foreach($config as $kw=>&$v)
			{
				$tocache=array('v'=>array(),'m'=>array());
				foreach($v as &$grname)
					foreach($cache[$grname] as $ok=>&$ov)
					{
						if(!isset($tocache['v'][$ok]))
						{
							$tocache['v'][$ok]=$ov;
							if(isset($ml[$grname][$ok]))
								$tocache['m'][]=$ok;
						}
						$getted[$ok]=$ov;
						$lgetted[$ok]=isset($ml[$grname][$ok]);
					}
				self::$Cache->Lib->Put($kw,$tocache);
			}
		}
		if($r)
		{
			foreach($getted as $k=>&$v)
				if($lgetted[$k])
					$v=self::FilterLangValues($v);
			return$getted;
		}
		foreach($getted as $k=>&$v)
			if($lgetted[$k])
			{
				self::$lvars[$k]=$v;
				self::$vars[$k]=self::FilterLangValues($v);
			}
			else
				self::$vars[$k]=$v;
	}

	public static function HookOutPut($cb='',$code=200,$data='')
	{static $d=false;
		if($d)
			return;
		$d=true;
		#Только здесь идет отсылка заголовков. Чтобы их в случае чего можно было изменить. Самый яркий пример - функция Error в index.php
		if(!headers_sent())
		{
			if(self::$gzip)
				self::$gzip=isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')!==false && extension_loaded('zlib');

			if(self::$caching and self::$last_mod)
			{
				$etag=self::$etag ? self::$etag : md5($_SERVER['REQUEST_URI']);
				header('Cache-Control: max-age='.self::$maxage.', must-revalidate');
				header('ETag: '.$etag);
				if(self::$modified and self::$modified>=self::$last_mod)
					return header('Last-Modified: '.gmdate('D, d M Y H:i:s ',self::$last_mod).'GMT',true,304);
				header('Last-Modified: '.gmdate('D, d M Y H:i:s ',self::$last_mod).'GMT',false,$code);
			}
			else
				header('Cache-Control: no-store');
			header('Content-Type: '.self::$content_type.'; charset='.self::$charset);
			header('Content-Language: '.self::$langs[Language::$main]['d']);
			header('Content-Encoding: '.(self::$gzip ? 'gzip' : 'none'),false,$code);
			header('X-Powered-CMS: Eleanor CMS http://eleanor-cms.ru');
		}

		if($cb===false)
			self::FinishOutPut(false,$data);
		else
		{
			ob_start();
			register_shutdown_function(array(__class__,'FinishOutPut'),true,$cb,$data);
		}
	}

	public static function FinishOutPut($docb,$cb,$data=null)
	{
		if($docb)
		{
			$s=ob_get_contents();
			if($s!==false)
				ob_end_clean();
			if(is_callable($cb))
				$s=call_user_func($cb,$s,$data);
		}
		else
			$s=$cb;
		if($s===false)
			return;
		if(self::$gzip)
		{
			$gsize=strlen($s);
			$gcrc=crc32($s);
			$s=gzcompress($s,1);
			$s=substr($s,0,-4);
			$s="\x1f\x8b\x08\x00\x00\x00\x00\x00".$s.pack('V',$gcrc).pack('V',$gsize);
		}
		echo$s;
	}

	/*
		$path - путь, который не может быть полностью серверным. Если он начинается с / - это означает, что к нему нужно прибавить root.
		$current_path - папка, которая может содержать либо полный серверный путь, либо быть пустой.
	*/
	public static function FormatPath($p,$cp='')
	{
		$p=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,trim($p,'/\\'));
		if(strpos($p,'/')===0 or !$cp)
			return self::$root.$p;
		$cp=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,$cp);
		return(self::$os=='u' && strpos($cp,'/')===0 || strpos($cp,':')==1 ? rtrim($cp,'/\\') : self::$root.trim($cp,'/\\')).($p ? DIRECTORY_SEPARATOR.$p : '');
	}

	public static function SetCookie($n,$v='',$t=false,$safe=false)
	{
		if($t===false)
			$t=$v ? self::$vars['cookie_save_time']+time() : 0;
		elseif($t)
			do
			{
				switch(substr($t,-1))
				{
					case't':
						$t=(int)$t;
					break 2;
					case's':
						$t=(int)$t;
					break;
					case'm':
						$t=(int)$t*60;
					break;
					case'h':
						$t=(int)$t*3600;
					break;
					default:#Days...
						$t=(int)$t*86400;
				}
				$t+=time();
			}while(false);
		return setcookie(self::$vars['cookie_prefix'].$n,$v,$t,self::$site_path,self::$vars['cookie_domain'],false,$safe);
	}

	public static function GetCookie($n)
	{
		$n=self::$vars['cookie_prefix'].$n;
		return isset($_COOKIE[$n]) ? $_COOKIE[$n] : false;
	}

	/*
		Пример:
		Eleanor::Mail('mail@example.com','Тема письма','Текст письма',array('files'=>array('имя файла'=>'Содержимое файла',0=>'path/to/files.txt')));
	*/
	public static function Mail($to,$subj,$mess,array$a=array())
	{
		$a+=array(
			'type'=>'text/html',
			'files'=>array(),
			'copy'=>array(),
			'hidden'=>array(),
		);
		self::$Instance->Email->parts=array(
			'multipart'=>'mixed',
			array(
				'content-type'=>$a['type'],
				'charset'=>DISPLAY_CHARSET,
				'content'=>$mess,
			),
		);
		foreach($a['files'] as $k=>&$v)
		{
			if(is_int($k))
			{
				$name=basename($v);
				$c=file_get_contents($v);
			}
			else
			{
				$name=basename($k);
				$c=$v;
			}
			self::$Instance->Email->parts[]=array(
				'content-type'=>Types::MimeTypeByExt($name),
				'filename'=>$name,
				'content'=>$c,
			);
		}
		self::$Instance->Email->subject=$subj;
		self::$Instance->Email->Send(array('to'=>$to,'cc'=>$a['copy'],'bcc'=>$a['hidden']));
		self::$Instance->Email->subject='';
		self::$Instance->Email->parts=array();
	}

	public static function StartSession($id='',$n='')
	{
		if(isset($_SESSION))
		{
			if(session_id()==$id and (!$n or session_name()==$n))
				return;
			session_write_close();
		}
		ini_set('session.use_cookies',0);
		ini_set('session.use_trans_sid',0);
		if($n and preg_match('#^[a-z0-9]+$#i',$n)>0)
			session_name($n);
		if($id and preg_match('#^[a-z0-9,\-]+$#i',$id)>0)
			session_id($id);
		session_start();
	}

	public static function FilterLangValues(array$a,$l=false,$d='')
	{
		if(!$l)
			$l=Language::$main;
		if(!$a)
			return$d;
		if(isset($a[$l]))
			return$a[$l];
		if(isset($a[LANGUAGE]))
			return$a[LANGUAGE];
		return isset($a['']) ? $a[''] : reset($a);
	}

	public static function WinFiles($f,$inv=false)
	{
		if(self::$os=='w' and CHARSET=='utf-8')
			$f=$inv ? mb_convert_encoding($f,CHARSET,'cp1251') : mb_convert_encoding($f,'cp1251');
		return$f;
	}

	/*
		Метод инициализации темы оформления
	*/
	public static function InitTemplate($tpl,$path='templates/')
	{
		$f=self::$rootf.$path.$tpl;
		if(!is_dir($f))
			throw new EE('Template '.$tpl.' not found!',EE::ENV);

		self::$Template=new MixedTemplate;
		self::$Template->paths[__class__]=$f.'/';
		self::$Template->default['theme']=$path.$tpl.'/';
		$init=$f.'.init.php';
		if(is_file($init))
			include$init;
		$config=$f.'.config.php';
		self::$Template->default['CONFIG']=is_file($config) && ($cfg=include$config) ? (array)$cfg : array();
		self::$Template->queue[]='Index';
	}

	/*
		Загрузка обычного PHP Файла темы оформления. Возвращает его содержимое с замененными переменными.
	*/
	public static function LoadFileTemplate($f,array$vars=array())
	{
		extract($vars,EXTR_PREFIX_INVALID,'v');
		ob_start();
		$r=include$f;
		if($r===1)
			$r=ob_get_contents();
		ob_end_clean();
		return$r;
	}

	/*
		Загрука файла шаблона списка.
	*/
	public static function LoadListTemplate($n)
	{
		$path=self::$rootf.self::$Template->default['theme'].'Lists/'.$n.'.php';
		if(!is_file($path))
			do
			{
				foreach(self::$Template->paths as &$v)
					if(is_file($path=$v.'Lists/'.$n.'.php'))
						break 2;
				throw new EE('Unable to load list template '.$n,EE::ALT);
			}while(false);
		$p=array_slice(func_get_args(),1);
		extract(count($p)==1 && is_array($p[0]) ? $p[0] : $p,EXTR_PREFIX_INVALID,'v');
		$l=include$path;
		if(!is_array($l))
			throw new EE('Incorrect list template '.$n,EE::ALT);
		$L=new ListTemplate($l);
		$L->default=self::$Template->default;
		return$L;
	}

	public static function ExecBBLogic($s,array$r,$p='')
	{
		foreach($r as $k=>&$v)
		{
			$k=$p.$k;
			while(false!==$fp=strpos($s,'['.$k.']') and false!==$lp=strpos($s,'[/'.$k.']',$fp))
			{
				$klen=strlen($k);
				if(false!==$midpos=strpos($s,'[-'.$k.']',$fp) and $midpos<$lp)
				{
					if($v)
					{
						$s=substr_replace($s,'',$midpos,$lp-$midpos+2+$klen*2);
						$s=substr_replace($s,'',$fp,$klen+2);#2 - это /] закрывающего тега
					}
					else
					{
						$s=substr_replace($s,'',$lp,$klen+3);#3 - это [/] закрывающего тега
						$s=substr_replace($s,'',$fp,$midpos-$fp+3+$klen);#2 - это - ] среднего тега
					}
				}
				elseif($v)
				{
					$s=substr_replace($s,'',$lp,$klen+3);#3 - это [/] закрывающего тега
					$s=substr_replace($s,'',$fp,$klen+2);#2 - это / ] закрывающего тега
				}
				else
					$s=substr_replace($s,'',$fp,$lp-$fp+3+$klen);#3 - это [/] закрывающего тега
			}
			if(is_scalar($v))
				$s=str_replace('{'.$k.'}',$v,$s);
		}
		return$s;
	}

	/*
		Метод служит для вывода переменных в JavaScript. Полностью сохраняет структуру переменной.
		$a - массив в виде имя переменной (ключ массива) => значение. Поддерживаются многомерные массивы.
		$tag - признак обрамления результата в <script...>...</script>
		$name - если результат требуется в виде массива, в эту переменную следует указать имя этого массива.
	*/
	public static function JsVars($a,$t=true,$n=false,$p='var ')
	{
		if($n)
		{
			$r=$n===true ? '{' : $p.$n.'={';
			$p='"';
			$s='":';
			$e=',';
		}
		else
		{
			$r='';
			$s='=';
			$e=';';
		}
		foreach($a as $k=>&$v)
		{
			if(is_array($v))
				$rv=self::JsVars($v,false,true);
			elseif(is_bool($v))
				$rv=$v ? 'true' : 'false';
			elseif($v===null)
				$rv='null';
			elseif(substr($k,0,1)=='!')
			{
				$rv=$v;
				$k=substr($k,1);
			}
			else
				$rv=(is_int($v) or is_float($v)) ? $v : '"'.addcslashes($v,"\n\r\t\"\\").'"';
			$r.=$p.$k.$s.$rv.$e;
		}
		if($n)
		{
			$r=rtrim($r,',').'}';
			if($n===true)
				return$r;
			$r.=';';
		}
		return $t ? '<script type="text/javascript">/*<![CDATA[*/'.$r.'//]]></script>' : $r;
	}

	/*
		Метод обработки входящей строки для показа ее в контроле.
		$text - текст.
		$mode:
			0 - текст прогоняется через htmlspecialchars, таким образом мы правим строку в таком виде, в каком мы ее получили.
			1 - текст прогняется сначала через htmlspecialchars_decode, а потом через htmlspecialchars. Таким образом мы правим HTML в таком виде, в котором его видит пользователь. Циферные задания символов как &#93; пользователь правит, а не видит.
			2 - в тексте заменяются только < и > на &lt; и &gt; соответственно.
			3 - Править ХТМЛ в таком виде, в котором его видит пользователь.
	*/
	public static function ControlValue($t,$m=1,$ch=CHARSET)
	{
		if($m==1)
			$t=htmlspecialchars_decode($t,ELENT);

		if($m==2)
			return str_replace(array('<','>'),array('&lt;','&gt;'),$t);
		elseif($t2=htmlspecialchars($t,ELENT,$ch,$m<3) or !$ch)
			return$t2;
		#Заплатка глюка, когда на UTF версии мы пытаемся открыть 1251 Файл.
		return self::ControlValue($t,$m,null);
	}

	public static function TagParams(array$a)
	{
		$ad='';
		foreach($a as $k=>&$v)
			if($v!==false)
				$ad.=is_int($k) ? ' '.$v : ' '.$k.'='.(strpos($v,'"')===false ? '"'.$v.'"' : '\''.$v.'\'');
		return$ad;
	}

	public static function Check($n,$c=false,array$a=array())
	{
		unset($a['checked']);
		return'<input type="checkbox"'.($n ? ' name="'.$n.'"' : '').self::TagParams($a+array('value'=>1)).($c ? ' checked="checked"' : '').' />';
	}

	public static function Text($n,$v='',array$a=array(),$m=1)
	{
		return'<textarea'.($n ? ' name="'.$n.'"' : '').self::TagParams($a+array('rows'=>5,'cols'=>20)).'>'.self::ControlValue($v,(int)$m).'</textarea>';
	}

	public static function Radio($n,$v=1,$checked=false,array$a=array(),$m=1)
	{
		unset($a['checked']);
		return'<input type="radio" value="'.self::ControlValue($v,(int)$m).'"'.self::TagParams($a+array('name'=>$n)).' '.($checked ? 'checked="checked"' : '').' />';
	}

	public static function Edit($n,$v='',array$a=array(),$m=1)
	{
		return'<input type="text" value="'.self::ControlValue($v,(int)$m).'"'.self::TagParams($a+array('name'=>$n)).' />';
	}

	public static function Button($v='OK',$t='submit',array$a=array(),$m=1)
	{
		return self::Control(isset($a['name']) ? $a['name'] : false,$t,$v,$a,(int)$m);
	}

	public static function Control($n,$t,$v='',array$a=array(),$m=1)
	{
		unset($a['name'],$a['type'],$a['value']);
		$v=(string)$v;
		return'<input type="'.$t.'"'.self::TagParams($a+array('name'=>$n,'value'=>$v==='' ? false : self::ControlValue($v,(int)$m))).' />';
	}

	public static function Option($t,$v=false,$s=false,array$a=array(),$m=1)
	{
		if($v!==false)
			$v=' value="'.trim(self::ControlValue($v,2)).'"';
		return'<option'.$v.($s ? ' selected="selected"' : '').self::TagParams($a).'>'.self::ControlValue($t,(int)$m).'</option>';
	}

	public static function Optgroup($l,$o,array$a=array(),$m=2)
	{
		unset($a['label']);
		$l=' label="'.self::ControlValue($l,$m).'"';
		return'<optgroup'.$l.self::TagParams($a).'>'.$o.'</optgroup>';
	}

	public static function Select($n,$o='',array$a=array())
	{
		if(!$o)
		{
			$o=self::Option('',0);
			$a['disabled']='disabled';
		}
		unset($a['name']);
		return'<select'.self::TagParams($a+array('name'=>$n,'size'=>1,'class'=>'select')).'>'.$o.'</select>';
	}

	public static function Item($n,$o='',$s=10,array$a=array())
	{
		return self::Select($n,$o,$a+array('size'=>(int)$s));
	}

	public static function Items($n,$o='',$s=10,array$a=array())
	{
		if(substr($n,-2)!='[]')
			$n.='[]';
		return self::Select($n,$o,$a+array('size'=>(int)$s,'multiple'=>'multiple'));
	}
#Конец методов оформления.

#Методы пользовательского назначения
	/*
		Загрука класса авторизации. Классы авторизазции берутся из /core/login/*.php (значение * нужно указывать в $l)
	*/
	public static function LoadLogin($l)
	{
		if(!is_file(self::$root.'core/login/'.$l.'.php'))
			throw new EE('Authorization '.$l.'.php not found!');
		$c='Login'.$l;
		if(!class_exists($c,false))
			require self::$root.'core/login/'.$l.'.php';
		return$c::getInstance();
	}

	/*
		Принимает на вход объект класса-логина. Применяет все пользовательские настройки + устанавливает этот логин в качестве главного Login
	*/
	public static function ApplyLogin($Obj)
	{
		if(!is_object($Obj))
			$Obj=self::LoadLogin($Obj);
		self::$Login=&$Obj;
		self::$Permissions=&$Obj->Permissions;
		if(self::$Login->IsUser())
		{
			if(self::$vars['multilang'] and $l=self::$Login->GetUserValue('language') and Language::$main!=$l and isset(self::$langs[$l]))
			{
				Language::$main=$l;
				self::$Language->Change($l);
			}
			if($t=self::$Login->GetUserValue('timezone') and in_array($t,timezone_identifiers_list()))
			{
				date_default_timezone_set($t);
				self::$Db->SyncTimeZone();
				if(self::$UsersDb!==self::$Db)
					self::$UsersDb->SyncTimeZone();
			}
			self::$Login->ApplyCheck();
		}
	}

	/*
		$g - ИДЫ групп
		$p - параметр
		$t - table
	*/
	public static function Permissions(array$ids,$p,$t=false)
	{
		if(!$t)
			$t=P.'groups';
		if(isset(self::$perms[$t]))
			$g=self::$perms[$t];
		else
		{
			if(false===$g=self::$Cache->Get($t))
			{
				$g=array();
				self::$Db->Query('SELECT * FROM `'.$t.'`');
				while($a=self::$Db->fetch_assoc())
				{
					$r=array();
					$id=0;
					foreach($a as $k=>&$v)
						if($k=='id')
							$id=$v;
						elseif($k=='parents')
							$r[$k]=$v ? array_reverse(explode(',',rtrim($v,','))) : array();
						elseif('_l'==substr($k,-2))
							$r[$k]=$v ? (array)unserialize($v) : array();
						elseif($v!==null)
							$r[$k]=$v;
					if($id!=0)
						$g[$id]=$r;
				}
				self::$Cache->Put($t,$g,3600);
			}
			self::$perms[$t]=$g;
		}
		$r=array();
		foreach($ids as &$v)
			if(isset($g[$v][$p]))
				$r[$v]=$g[$v][$p];
			else
			{
				$r[$v]=null;
				if(isset($g[$v]))#Для наследования групп
					foreach($g[$v]['parents'] as &$pv)
						if(isset($g[$pv][$p]))
							$r[$v]=$g[$pv][$p];
			}
		return$r;
	}

	/*
		$p - Параметр
		$l - Login
		$t - table
	*/
	public static function GetPermission($p,$L=false,$t=false,$go='groups_overload')
	{
		if(!$L)
			$L=self::$Login;
		if(!$over=$L->GetUserValue($go) or !isset($over['method'][$p],$over['value'][$p]) or $over['method'][$p]=='inherit')
			return self::Permissions(self::GetUserGroups($L),$p,$t);
		$res=($add=$over['method'][$p]=='replace') ? array($over['value'][$p]) : self::Permissions(self::GetUserGroups($L),$p,$t);
		if(!$add)
			$res[]=$over['value'][$p];
		return$res;
	}

	/*
		Эта функция возвращает только массивы
		$L - Login
	*/
	public static function GetUserGroups($L=false)
	{
		if(!$L)
			$L=self::$Login;
		if($L ? $L->GetUserValue('id') : false)#Не ставить IsUser() - перестанет заходить в админку!
			return$L->GetUserValue('groups');
		else
			return self::$is_bot ? (array)self::$vars['bot_group'] : (array)self::$vars['guest_group'];
	}

	/*
		Функция записывае пользовательскую сессию с данными в базу. Используется для списка "кто онлайн".
	*/
	public static function AddSession()
	{
		$uid=self::$Login->GetUserValue('id');
		$ua=getenv('HTTP_USER_AGENT');

		$n='';
		if(!$uid and self::$vars['bots_enable'] and $ua)
			foreach(self::$vars['bots_list'] as $k=>&$v)
				if(stripos($_SERVER['HTTP_USER_AGENT'],$k)!==false)
				{
					$n=self::$is_bot=$v;
					break;
				}
		unset(self::$vars['bots_list']);

		$info=array(
			'r'=>getenv('HTTP_REFERER'),
			'c'=>getenv('HTTP_ACCEPT_CHARSET'),
			'e'=>getenv('HTTP_ACCEPT_ENCODING'),
		);
		if(self::$ips)
			$info['ips']=self::$ips;

		$to=get_class(self::$Login);
		self::$Db->Replace(P.
			'sessions',
			array(
				'type'=>self::$is_bot ? 'bot' : $uid ? 'user' : 'guest',
				'user_id'=>$uid,
				'!enter'=>'NOW()',
				'!expire'=>'\''.date('Y-m-d H:i:s').'\' + INTERVAL '.(isset(self::$vars['time_online'][$to]) ? (int)self::$vars['time_online'][$to] : 900).' SECOND',
				($uid>0 ? 'ip_user' : 'ip_guest')=>self::$ip,
				'info'=>serialize($info),
				'service'=>self::$service,
				'browser'=>$ua,
				'location'=>Url::Decode(preg_replace('#^'.preg_quote(self::$site_path,'#').'#','',$_SERVER['REQUEST_URI'])),
				'name'=>$n,
				'addon'=>self::$sessaddon,
			)
		);
	}

	public static function IPMatchMask($ip,$m)
	{
		$m=trim($m);
		if($ipv6=strpos($ip,':')!==false)
		{
			if(strpos($m,':')===false)
				return false;
			$n=substr_count($ip,':')-2;
			$r=str_repeat(':0000',8-$n-2);
			$ip=str_replace('::',$r.':',$ip);
		}
		if(strpos($m,'-')===false)
		{
			if($ipv6 and false!==$p=strpos($m,'::'))
			{
				$n=substr_count($m,':')-2-($p==0);
				$r=str_repeat(':0000',8-$n-2);
				$m=trim(str_replace('::',$r.':',$m),':');
			}
			if(strpos($m,'/')!==false)
			{
				$m=explode('/',$m,2);
				$bm=$bip='';
				if($ipv6)
				{
					$m[0]=explode(':',$m[0]);
					$ip=explode(':',$ip);
					foreach($m[0] as &$v)
						$bm.=str_pad(decbin(hexdec($v)),8,'0',STR_PAD_LEFT);
					foreach($ip as &$v)
						$bip.=str_pad(decbin(hexdec($v)),8,'0',STR_PAD_LEFT);
				}
				else
				{
					$m[0]=explode('.',$m[0]);
					$ip=explode('.',$ip);
					foreach($m[0] as &$v)
						$bm.=str_pad(decbin($v),8,'0',STR_PAD_LEFT);
					foreach($ip as &$v)
						$bip.=str_pad(decbin($v),8,'0',STR_PAD_LEFT);
				}
				return strncmp($bm,$bip,(int)$m[1])==0;
			}
			$m=str_replace('\*','[a-f0-9]{1,4}',preg_quote($m,'#'));
			return preg_match('#^'.$m.'$#',$ip)>0;
		}
		else
		{
			$m=explode('-',$m,2);
			if($ipv6)
			{
				if(false!==$p=strpos($m[1],'::'))
				{
					$n=substr_count($m[1],':')-2-($p==0);
					$r=str_repeat(':0000',8-$n-2);
					$m[1]=trim(str_replace('::',$r.':',$m[1]),':');
				}
				if(false!==$p=strpos($m[0],'::'))
				{
					$n=substr_count($m[0],':')-2-($p==0);
					$r=str_repeat(':0000',8-$n-2);
					$m[0]=trim(str_replace('::',$r.':',$m[0]),':');
				}
				$mto=explode(':',$m[1],8);
				$m=explode(':',$m[0],8);
				$ip=explode(':',$ip,8);
				foreach($m as &$v)
					$v=hexdec($v);
				$m=join($m);
				foreach($mto as &$v)
					$v=hexdec($v);
				$mto=join($mto);
				foreach($ip as &$v)
					$v=hexdec($v);
				$ip=implode($ip);
			}
			else
			{
				$mto=explode('.',str_replace('*',255,$m[1]),4);
				$m=explode('.',str_replace('*',0,$m[0]),4);
				$ip=explode('.',$ip,4);
				foreach($m as &$v)
					$v=str_pad($v,3,0,STR_PAD_LEFT);
				$m=ltrim(join($m),0);
				foreach($mto as &$v)
					$v=str_pad($v,3,0,STR_PAD_LEFT);
				$mto=ltrim(join($mto),0);
				foreach($ip as &$v)
					$v=str_pad($v,3,0,STR_PAD_LEFT);
				$ip=ltrim(join($ip),0);
			}
			return(bccomp($ip,$m)>=0 and bccomp($mto,$ip)>=0);
		}
	}
}

abstract class Template
{
	public
		$s='';

	public function __toString()
	{
		$s=$this->s;
		$this->s='';
		return$s;
	}

	public function __invoke()
	{
		$n=func_num_args();
		if($n>0)
		{
			$a=func_get_args();
			return$this->_($a[0],array_slice($a,1));
		}
	}

	public function __call($n,$p)
	{
		$r=$this->_($n,$p);
		if($r===null or is_scalar($r) or is_object($r) and $r instanceof self)
		{
			$this->s.=$r;
			return$this;
		}
		return$r;
	}

	abstract public function _($n,array$p);
}

class MixedTemplate extends Template
{
	public
		$default=array(),#Переменные по-умолчанию, которые будут использоваться во всех темах. Ключ theme КРАЙНЕ НЕ рекомендуется трогать!
		$queue=array(),#Очередь классов на загрузку

		$classes=array(),#Классы тем оформления
		$paths=array(),#Дополнительные пути
		$files=array();#Дампы файлов

	protected
		$cloned=false;

	public function __construct($noclone=false)
	{
		$this->cloned=$noclone;
	}

	public function __call($n,$p)
	{
		if($this->cloned)
			return parent::__call($n,$p);
		$O=clone$this;
		return$O->__call($n,$p);
	}

	public function __clone()
	{
		$this->cloned=true;
	}

	public function _($n,array$p)
	{
		$c=end($this->classes);
		while($c)
		{
			if(method_exists($c,$n))
				return call_user_func_array(array($c,$n),$p);
			$c=array($c,$n);
			if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
				return$s;
			$c=prev($this->classes);
		}

		foreach($this->paths as $k=>&$v)
		{
			if(!isset($this->files[$k]))
			{
				$this->files[$k]=array();
				if(is_dir($v) and $fs=glob($v.'*.php',GLOB_MARK))
					foreach($fs as &$fv)
						if($fv=substr(strrchr($fv,'/'),1))#Оставляем только имена файлов
							$this->files[$k][]=substr($fv,0,strrpos($fv,'.'));
			}
			if(in_array($n,$this->files[$k]))
				return Eleanor::LoadFileTemplate($v.$n.'.php',(count($p)==1 && is_array($p[0]) ? $p[0] : $p)+$this->default);
		}

		while($cl=array_pop($this->queue))
		{			$c='Tpl'.$cl;
			if(!class_exists($c,false))
				do
				{
					foreach($this->paths as &$v)
						if(is_file($path=$v.'Classes/'.$cl.'.php'))
						{
							include$path;
							if(class_exists($c,false))
								break 2;
						}
					continue 2;
				}while(false);
			$this->classes[]=$c;
			if(method_exists($c,$n))
				return call_user_func_array(array($c,$n),$p);
			$c=array($c,$n);
			if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
				return$s;
		}
		$d=debug_backtrace();
		$a=array();
		foreach($d as &$v)
			if(isset($v['file'],$v['line']) and $v['file']!=__file__)
			{
				$a['file']=$v['file'];
				$a['line']=$v['line'];
				break;
			}
		throw new EE('Template '.$n.' was not found!',EE::FATAL,$a);
	}
}

class ListTemplate extends Template
{
	public
		$default=array();

	protected
		$tpl;

	public function __construct(array$n)
	{
		$this->tpl=$n;
	}

	public function _($n,array$p)
	{
		if(!isset($this->tpl[$n]))
			throw new EE('Unknown list template: '.$n,EE::DEV);
		if(is_callable($this->tpl[$n]))
			return call_user_func_array($this->tpl[$n],$p);
		return Eleanor::ExecBBLogic($this->tpl[$n],(count($p)==1 && is_array($p[0]) ? $p[0] : $p)+$this->default);
	}
}

class ClassTemplate extends Template
{
	public
		$class;

	public function __construct(string$cl)
	{
		$this->class=$cl;
	}

	public function _($n,array$p)
	{
		if(is_callable(array($this->class,$n)))
			return call_user_func_array(array($this->class,$n),$p);
		throw new EE('Template: '.$this->class.'::'.$n,EE::DEV);
	}
}

### Cache

interface CacheMachineInterface #Интерфейс для создания кэшей
{
	/*
		Метод для занесения параметров в кэш
		$key - ключ.
		$value - значение
		$ttl - "Time to live" время жизни.
	*/
	public function Put($k,$v,$ttl=0);

	public function Get($k);

	public function Delete($k);

	/*
		Метод удаления кеша по тегам. Если имя тега пустое - удаляется вешь кэш.
	*/
	public function CleanByTag($tag);
}

class Cache
{
	public
		$Lib;#Кэш-машина

	public function __construct($cm=false,$u=false,$a=array())
	{
		if(!$u)
			$u=crc32(__file__);
		if(function_exists('apc_store'))
			$a['apc']=array('CacheMachineApc',Eleanor::$root.'core/cache_machines/apc.php');
		if(function_exists('memcache_connect'))
			$a['memcache']=array('CacheMachineMemCache',Eleanor::$root.'core/cache_machines/memcache.php');
		if(class_exists('Memcached',false))
			$a['memcache']=array('CacheMachineMemCached',Eleanor::$root.'core/cache_machines/memcached.php');
		if(function_exists('output_cache_put'))
			$a['zend']=array('CacheMachineZend',Eleanor::$root.'core/cache_machines/zend.php');

		if($cm and isset($a[$cm]) and (class_exists($a[$cm][0],false) or is_file($a[$cm][1]) and include$a[$cm][1]))
			$this->Lib=new $a[$cm][0]($u);

		if(!isset($this->Lib))
			foreach($a as &$v)
				if(class_exists($v[0],false) or is_file($v[1]) and include$v[1])
				{
					$this->Lib=new $v[0]($u);
					break;
				}

		if(!isset($this->Lib))
		{
			#Вместо Serialize можно использовать HardDisk
			if(!class_exists('CacheMachineSerialize',false))
				include Eleanor::$root.'core/cache_machines/serialize.php';
			$this->Lib=new CacheMachineSerialize;
		}
	}

	/*
		$insur - от "insurance" страховка для предотвращения dog-pile effect
	*/
	public function Put($n,$v=false,$ttl=0,$tdb=false,$insur=false)
	{
		if(!is_array($n))
			$n=array($n=>$v);
		$r=true;
		if(!DEBUG)
		{
			if(!$insur)
				$insur=$ttl*2;
			foreach($n as $k=>&$v)
			{
				$r&=$this->Lib->Put($k,array($v,$ttl,time()+$ttl),$insur);
				if($tdb)
					$v=serialize($v);
			}
		}
		if($tdb)
			Eleanor::$Db->Replace(P.'cache',array('key'=>array_keys($n),'value'=>array_values($n)));
		return$r;
	}

	public function Get($n,$fdb=false)
	{
		if($a=is_array($n))
		{
			$r=array();
			foreach($n as $k=>&$v)
				if(false!==$r[$v]=$this->Lib->Get($v))
					unset($n[$k]);
		}
		elseif(DEBUG)
			$r=false;
		elseif(false!==$r=$this->Lib->Get($n))
		{
			if($r[1]>0 and $r[2]<time())
			{
				$this->Put($n,$r[0],$r[1],false,$r[1]);
				return false;
			}
			return$r[0];
		}
		if(!$fdb or !$n)
			return$r;

		$db=array();
		$R=Eleanor::$Db->Query('SELECT `key`,`value` FROM `'.P.'cache` WHERE `key`'.Eleanor::$Db->In($n));
		while($a=$R->fetch_assoc())
			$db[$a['key']]=unserialize($a['value']);
		if($db and !DEBUG)
			$this->Put($db);
		return$a ? $db+$r : reset($db);
	}

	public function Delete($n,$fdb=false)
	{
		if(is_array($n))
			foreach($n as &$v)
				$this->Lib->Delete($v);
		else
			$this->Lib->Delete($n);
		if($fdb)
			Eleanor::$Db->Delete(P.'cache','`key`'.Eleanor::$Db->In($n));
	}

	public function Obsolete($n,$fdb=false)
	{
		if(false!==$r=$this->Lib->Get($n))
		{
			if($r[1]==0)
				return$this->Delete($n,$fdb);
			$ttl=max(time()-$r[2]+$r[1],1);
			$r[2]=0;
			$this->Lib->Put($n,$r,$ttl);
		}
	}
}

### DB
class Db extends BaseClass
{
	public
		$Driver,
		$Result,
		$db,#Имя базы данных
		$queries=0;#Счетчик запросов

	/*
		Соединение с БД
		['host'] - сервер БД.
		['user'] - пользователь БД
		['pass'] - пароль пользоваетля
		['db'] - база данных
	*/
	public function __construct(array$p)
	{
		if(!isset($p['host'],$p['user'],$p['pass'],$p['db']))
			throw new EE_SQL('connect',$p);
		$M=new MySQLi($p['host'],$p['user'],$p['pass'],$p['db']);
		if($M->connect_errno or !$M->server_version)
			throw new EE_SQL('connect',$p+array('error'=>$M->connect_error,'errno'=>$M->connect_errno));
		$M->autocommit(true);
		$M->set_charset(DB_CHARSET);

		$this->Driver=$M;
		$this->db=$p['db'];
	}

	public function __call($n,$p)
	{
		if(method_exists($this->Driver,$n))
			return call_user_func_array(array($this->Driver,$n),$p);
		elseif(is_object($this->Result) and method_exists($this->Result,$n))
			return call_user_func_array(array($this->Result,$n),$p);
		return parent::__call($n,$p);
	}

	public function SyncTimeZone()
	{
		$t=date_offset_get(date_create());
		$s=$t>0 ? '+' : '-';
		$t=abs($t);
		$s.=floor($t/3600).':'.($t%3600);
		$this->Driver->query('SET TIME_ZONE=\''.$s.'\'');
	}

	#Работа с транзакциями
	public function Transaction()
	{
		$this->Driver->autocommit(false);
	}

	public function Commit()
	{
		$this->Driver->commit();
		$this->Driver->autocommit(true);
	}

	public function RollBack()
	{
		$this->Driver->rollback();
		$this->Driver->autocommit(true);
	}
	#[E]Работа с транзакциями

	public function Query($q)
	{
		++$this->queries;
		if(DEBUG)
		{
			$d=debug_backtrace();
			$f=$l='';
			foreach($d as &$v)
			{
				if(!isset($v['class']) or $v['class']!='Db')
					break;
				$f=$v['file'];
				$l=$v['line'];
			}
			$debug=array(
				'e'=>$q,
				'f'=>$f,
				'l'=>$l,
			);
			$timer=microtime();
		}
		$this->Result=$this->Driver->query($q);
		if($this->Result===false)
		{
			if($q)
			{
				$e=$this->Driver->error;
				$en=$this->Driver->errno;
			}
			else
			{
				$e='Empty query';
				$en=0;
			}
			throw new EE_SQL('query',array('error'=>$e,'no'=>$en,'query'=>$q));
		}
		if(DEBUG)
		{
			$debug['t']=round(array_sum(explode(' ',microtime()))-array_sum(explode(' ',$timer)),4);
			Eleanor::$debug[]=$debug;
		}
		return$this->Result;
	}

	public function Insert($t,$a,$add='IGNORE')
	{
		$this->Query('INSERT '.$add.' INTO `'.$t.'`'.$this->GenerateInsert($a));
		return$this->Driver->insert_id;
	}

	public function Replace($t,$a,$add='')
	{
		$this->Query('REPLACE '.$add.' INTO `'.$t.'` '.$this->GenerateInsert($a));
		return$this->Driver->affected_rows;
	}

	public function GenerateInsert($a)
	{
		$rk=$rv='';#result key & result value
		$k=key($a);
		if(is_int($k))
		{
			foreach($a as &$v)
			{
				if(!$rk)
				{
					$ks=array();
					foreach($v as $vk=>&$vv)
					{
						$ks[]=$vk=ltrim($vk,'!');
						$rk.='`'.$vk.'`,';
					}
					$rk='('.rtrim($rk,',').')VALUES';
				}
				$rv='(';
				foreach($ks as $ksk=>&$ksv)
					if(isset($v[$ksv]))
						$rv.=$this->Escape($v[$ksv]).',';
					elseif(isset($v['!'.$ksv]))
						$rv.=$v['!'.$ksv].',';
					elseif(isset($v[$ksk]))
						$rv.=$this->Escape($v[$ksk]).',';
					else
						$rv.='NULL,';
				$rk.=rtrim($rv,',').'),';
			}
			return rtrim($rk,',');
		}
		$va=array();#values array
		$isa=true;
		foreach($a as $k=>&$v)
		{
			if($k[0]=='!')
				$k=substr($k,1);
			else
				$v=$this->Escape($v);

			if(is_array($v))
			{
				foreach($v as $vk=>&$vv)
					$va[$vk][]=$vv;
				$v='Array';#В этом случае генерится ошибка. Зато сразу понятно, что идет не так.
			}
			else
				$isa=false;
			$rv.=$v.',';
			$rk.='`'.$k.'`,';
		}
		if($va and $isa)
		{
			foreach($va as &$v)
				$v=join(',',$v);
			$rv='('.join('),(',$va).')';
		}
		else
			$rv='('.rtrim($rv,',').')';
		return'('.rtrim($rk,',').') VALUES '.$rv;
	}

	public function Update($t,$a,$w='',$add='IGNORE')
	{
		$q='UPDATE '.$add.' `'.$t.'` SET ';
		foreach($a as $k=>&$v)
			$q.=$k[0]=='!' ? '`'.substr($k,1).'`='.$v.',' : '`'.$k.'`='.$this->Escape(is_array($v) ? serialize($v) : $v).',';
		$q=rtrim($q,',');
		if($w)
			$q.=' WHERE '.$w;
		$this->Query($q);
		return$this->Driver->affected_rows;
	}

	public function Delete($t,$w='')
	{
		$this->Query($w ? 'DELETE FROM `'.$t.'` WHERE '.$w : 'TRUNCATE TABLE `'.$t.'`');
		return$this->Driver->affected_rows;
	}

#Дополнительные функции
	public function In($v,$not=false)
	{
		if(is_array($v) and count($v)==1)
			$v=reset($v);
		if(is_array($v))
			return ($not ? ' NOT' : '').' IN ('.join(',',$this->Escape($v)).')';
		return ($not ? '!' : '').'='.$this->Escape($v);
	}

	public function Escape($s,$qs=true)
	{
		if($s===null)
			return'NULL';
		if(is_array($s))
		{
			foreach($s as &$v)
				$v=$this->Escape(is_array($v) ? serialize($v) : $v,$qs);
			return$s;
		}
		if(is_int($s) or is_float($s))
			return$s;
		if(is_bool($s))
			return(int)$s;
		$s=$this->Driver->real_escape_string($s);
		return $qs ? '\''.$s.'\'' : $s;
	}
}
#Функция для обработки результатов запросов

interface LoginClass //Интерфейс для создания медов авторизации
{
	#Singleton
	public static function getInstance();

	/*
		Функция для авторизации по имени пользователя и паролю. В случае, если вход невозможен - выбрасывает исключение EE::INFO
	*/
	public function Login(array $data);

	/*
		Функция для авторизации только по ID, без ввода логина и пароля
	*/
	public function Auth($id);

	/*
		Функция выполняет "автовход" пользователя. Фукнция кэширует результат, если $hard==false. Возвращает истину или ложь.
	*/
	public function IsUser($hard=false);

	/*
		Функция, которая вызывается после применения логина, как главного в системе. Может выкинуть, например, сообщение о забаненности пользователя.
	*/
	public function ApplyCheck();

	/*
		Выход пользователя. Тут можно предусмотреть не только вытирание куков, но и переход на
		главную страницу сайта.
	*/
	public function Logout();

	/*
		Функция, которая должна возвращать ссылку на страницу о пользователей, либо false в случае, если пользователь не существует
	*/
	public function UserLink($name,$id=0);

	/*
		Получить значение пользовательской переменной.
	*/
	public function GetUserValue($value,$safe=true);

}

class Language extends BaseClass implements ArrayAccess
{
	public static
		$main=LANGUAGE;#Глобальный язык

	public
		$loadfrom='langs',#Каталог по-умолчанию, откуда будут загружаться неинициализированные файлы
		$queue=array();#Очередь файлов для загрузки имя группы => файл

	protected
		$l,#Имя языка
		$gr,#Признак группировки значений по секциям
		$db,#Данные всех языков
		$files=array();#Включенные файлы

	public function __construct($f=false,$s='')
	{
		if($f===true)
			$this->gr=true;
		elseif($f)
			$this->Load($f,$s);
		$this->l=self::$main;
	}

	public function __toString()
	{
		return$this->l;
	}

	public function __call($n,$p)
	{
		if(method_exists($this->l,$n))
			return call_user_func_array(array($this->l,$n),$p);
		$c=array($this->l,$n);
		if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
			return$s;
		return parent::__call($n,$p);
	}

	/*
		Структура языкового файла:
		<?php
		return array(
			'param1'=>'value1',
			...
		);

		$f - имя файла, в котором вместо * система подставит языковое значение.
		$s - имя секции
	*/
	public function Load($f,$s='')
	{
		if(is_array($f))
		{
			$l=array();
			foreach($f as &$v)
				$l+=$this->Load($v,$s);
			return$l;
		}
		$rf=Eleanor::FormatPath('',$f);
		$f=str_replace('*',$this->l,$rf);
		do
		{
			if(is_file($f))
				break;
			$f=str_replace('*',LANGUAGE,$rf);
			if(is_file($f))
				break;
			return false;
		}while(false);
		$this->files[$s][]=$rf;
		$l=include$f;
		if($s)
			$this->gr=true;
		elseif($s===false)
			return$l;
		return$this->db[$s]=isset($this->db[$s]) ? $l+$this->db[$s] : $l;
	}

	public function Change($l=false)
	{
		if(!$l)
			$l=self::$main;
		$loc=Eleanor::$langs[$l]['l'];
		setlocale(LC_TIME,$loc);
		setlocale(LC_COLLATE,$loc);
		setlocale(LC_CTYPE,$loc);
		if($l==$this->l)
			return;
		foreach($this->files as $s=>&$fs)
			foreach($fs as $f)
			{
				$f=str_replace('*',$l,$f);
				if(is_file($f))
				{
					$li=include$f;
					$this->db[$s]=$li+$this->db[$s];
				}
			}
		$this->l=$l;
	}

	public function offsetSet($k,$v)
	{
		if($this->gr)
			$this->db[$k]=$v;
		else
			$this->db[''][$k]=$v;
	}

	public function offsetExists($k)
	{
		return $this->gr ? isset($this->db[$k]) : isset($this->db[''][$k]);
	}

	public function offsetUnset($k)
	{
		if($this->gr)
			unset($this->db[$k]);
		else
			unset($this->db[''][$k]);
	}

	public function offsetGet($k)
	{
		if($this->gr)
		{
			if(!isset($this->db[$k]) and !$this->Load(isset($this->queue[$k]) ? $this->queue[$k] : $this->loadfrom.DIRECTORY_SEPARATOR.$k.'-*.php',$k))
				return parent::__get(debug_backtrace(),$k);
			return$this->db[$k];
		}

		if(!isset($this->db[''][$k]))
		{
			while($l=array_pop($this->queue))
				if($this->Load($this->loadfrom.DIRECTORY_SEPARATOR.$l) and isset($this->db[''][$k]))
					return$this->db[''][$k];
			return parent::__get($k);
		}

		return$this->db[''][$k];
	}
}