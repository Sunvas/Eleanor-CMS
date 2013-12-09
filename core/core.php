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
define('ELENT',defined('ENT_HTML5') ? ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED : ENT_QUOTES);#ToDo! PHP 5.4 удалить defined
spl_autoload_register(array('Eleanor','Autoload'));

abstract class BaseClass
{
	/**
	 * Получение местоположения ошибки в коде: файл + строка
	 * @param array $d Дамп стека вызова при помощи функции debug_backtrace
	 */
	private static function _BT($d)
	{
		foreach($d as &$v)
			if(isset($v['file'],$v['line']))
				return$v;
		return array('file'=>'-','line'=>'-');
	}

	/**
	 * Обработка ошибочных вызовов несуществующих статических методов.
	 * Наличие этого метода может показаться странным: ведь если вызвать несуществующий статический метод, будет сгенерирован Fatal error,
	 * который можно отловить и залогировать. Но удобство метода проявляется в классе наследнике с методом __callStatic который не может
	 * выполнить все вызываемые методы.
	 * @param string $n Название несуществующего метода
	 * @param array $p Массив входящих параметров вызываемого метода
	 */
	public static function __callStatic($n,$p)
	{
		$d=self::_BT(debug_backtrace());
		$E=new EE('Called undefined method '.get_called_class().' :: '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
		$E->Log();
	}

	/**
	 * Обработка ошибочных вызовов несуществующих методов.
	 * Наличие этого метода может показаться странным: ведь если вызвать несуществующий метод объекта, будет сгенерирован Fatal error,
	 * который можно отловить и залогировать. Но удобство метода проявляется в классе наследнике с методом __call который не может
	 * выполнить все вызываемые методы.
	 * @param string $n Название несуществующего метода
	 * @param array $p Массив входящих параметров вызываемого метода
	 */
	public function __call($n,$p)
	{
		if(property_exists($this,$n) and is_object($this->$n) and method_exists($this->$n,'__invoke'))
			return call_user_func_array(array($this->$n,'__invoke'),$p);
		$d=self::_BT(debug_backtrace());
		$E=new EE('Called undefined method '.get_class().' -› '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
		$E->Log();
	}

	/**
	 * Обработка получения несуществующих свойств
	 * Наличие этого метода может показаться странным: поскольку, при попытке получить неопределенное свойство генерируется Notice,
	 * который можно отловить и залогировать. Но удобство метода проявляется в классе наследнике с методом __get, который может
	 * вернуть не все запрашиваемые свойства.
	 * @param string $n Имя запрашиваемого свойства
	 */
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
		$E=new EE('Trying to get value from the unknown variable '.get_class($this).' -› '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
		$E->Log();
	}
}

final class GlobalsWrapper implements ArrayAccess
{
	private
		$vn;

	/**
	 * Создание оболочки глобальной переменной. Требование: глобальная переменная должна быть массивом
	 * @param string $vn Имя глобальной переменной, для которой создается оболочка
	 */
	public function __construct($vn)
	{
		$this->vn=$vn;
	}

	/**
	 * Установка значения элемента глобальной переменной
	 * @param string $k Имя элемента, ключ массива
	 * @param mixed $v Значение
	 */
	public function offsetSet($k,$v)
	{
		$GLOBALS[$this->vn][$k]=$v;
	}

	/**
	 * Проверка существования определенного элемента
	 * @param string $k Имя элемента, ключ массива
	 */
	public function offsetExists($k)
	{
		return isset($GLOBALS[$this->vn][$k]);
	}

	/**
	 * Удаление определенного элемента
	 * @param string $k Имя элемента, ключ массива
	 */
	public function offsetUnset($k)
	{
		unset($GLOBALS[$this->vn][$k]);
	}

	/**
	 * Получение определенного элемента
	 * @param string $k Имя элемента, ключ массива
	 */
	public function offsetGet($k)
	{
		return isset($GLOBALS[$this->vn][$k]) ? self::Filter($GLOBALS[$this->vn][$k]) : null;
	}

	/**
	 * Преобразование опасного HTML в безопасный (обертка над функцией htmlspecialchars)
	 * @param string|array $s Строка с опасным HTML
	 */
	public static function Filter($s)
	{
		if(is_array($s))
		{
			foreach($s as &$v)
				$v=self::Filter($v);
			unset($v);
			return$s;
		}
		$s=str_replace(array("\r\n","\n\r","\r"),"\n",$s);
		return htmlspecialchars($s,ELENT,CHARSET,false);
	}
}

/**
 * Main Eleanor CMS class
 */
final class Eleanor extends BaseClass
{
	public static
		$uploads='uploads',#Каталог хранения загружаемых файлов

		#Отладочная информация
		$debug=array(),#Массив, куда помещаются данные отладки, для дальнейшего их вывода

		#Свойства генерируемой страницы
		$gzip=true,#Состояние GZIP сжатия
		$charset,#Выводимый в заголовках charset
		$caching,#Кешировать ли страницу и на сколько
		$last_mod,#Последнее изменение TIMESTAMP страницы по скрипту
		$modified,#Последнее изменение TIMESTAMP страницы по браузеру
		$maxage=0,#Срок жизни кэша на стороне браузера, без дополнительных валидаций со стороны сервера. В этот заголовок можно писать дополнительные параметры через запятую, например: 0, public
		$etag,#Etag страницы
		$content_type='text/html',#Выводимый в заголовках content-type

		#Свойства сайта
		$domain,#Readonly. Домен запуска системы
		$punycode,#Readonly. Punycode домена. Если домен нормальный - это ссылка на domain
		$site_path,#Readonly. Каталог сайта относительно домена
		$filename,#Readonly. Имя файла-сервиса, запускающего весь движок. Используется чаще всего для генерирования ссылок

		#Свойства пользователя
		$ip,#Адрес, откуда пришел запрос
		$ips=array(),#Массив со всеми айпишниками, присланными нам от пользователя
		$our_query=true,#Признак, что пользователь пришел на эту страницу со своим запросом (а не с чужой страницы путем эмуляции). Изменение этого параметра положено на сервисы.
		$sessextra='',#Дополнительная строка, которая будет писаться в таблицу сессий. Полезно для создания фичи во встроенных форумах: эту тему читают N пользователей
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

			'bots_enable'=>false,
			'bots_list'=>array(),
			'time_online'=>array(),
		),
		$services,#Данные всех сервисов
		$perms=array(),#Данные разрешений. [таблица] => [ID] => [опция] => значение

		#Системные свойства
		$os,#Тип системы, на которой стоит сайт u - *nix, w - windows
		$root,#Корень сайта
		$rootf,#Корень файла, с которого мы запустились
		$service,#Сервиса
		$nolog=false;#Флаг отключения логирования ошибок

	#Объекты
	/** @var Db*/
	public static
			$Db,#Объект базы данных
			$UsersDb;#Объект базы данных, для доступа к таблице пользователей (при включенной синхронизации), при выключенной - ссылка на $Db

	/** @var Cache */
	public static $Cache;#Кэш

	/** @var Template_Mixed */
	public static $Template;#Шаблон оформления

	/** @var Language */
	public static $Language;#Языковой объект, при конвертации его в строку - вернет имя языка

	/** @var LoginClass */
	public static $Login;#Объект главного логина. Именно объект, а не строка (название класса), только ради удобства доступа к методам

	/** @var Permissions */
	public static $Permissions;#Разрешения главного логина

	/** @var GlobalsWrapper */
	public static $POST;#Отфильтрованный POST запрос

	private static
		$Instance;#Единственный объект этого класса. Singleton

	/**
	 * Получение единственного объекта этого класса. При первом запросе - конструктор с единственным параметром:
	 * @param string $conf Путь файла с конфигурациями
	 */
	public static function getInstance($conf='config_general.php')
	{
		if(!isset(self::$Instance))
		{
			self::$Instance=new self;
			self::$root=dirname(__dir__).DIRECTORY_SEPARATOR;
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
			self::$domain=isset($_SERVER['HTTP_HOST']) && preg_match('#^[a-z0-9\-\.]+$#i',$_SERVER['HTTP_HOST'])>0 ? $_SERVER['HTTP_HOST'] : false;
			self::$site_path=rtrim(dirname($_SERVER['PHP_SELF']),'/\\').'/';
			if(self::$filename and false!==$t=strpos(self::$site_path,self::$filename))
				self::$site_path=substr(self::$site_path,0,$t);
			self::$POST=$_SERVER['REQUEST_METHOD']=='POST' ? new GlobalsWrapper('_POST') : array();
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
					$R=self::$Db->Query('SELECT `name`,`file`,`theme`,`login` FROM `'.P.'services`');
					while($a=$R->fetch_assoc())
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
					$task=DEBUG ? false : self::$Cache->Get('nextrun',true);
					$t=time();
					$task=$task===false || $task<=$t ? '<img src="'.self::$services['cron']['file'].'?rand='.$t.'" style="width:1px;height1px;" alt="" />' : '';
				}
				if(defined('ELEANOR_COPYRIGHT'))
					die('Copyright defined!');
				else
					#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
					define('ELEANOR_COPYRIGHT','<!-- ]]></script> --><a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © <!-- Eleanor CMS Team http://eleanor-cms.ru/copyright.php -->'.idate('Y').$task);

				$r=getenv('HTTP_REFERER');
				if($r and preg_match('#^'.PROTOCOL.'('.self::$vars['site_domain'].'|'.self::$domain.')'.self::$site_path.'#',$r)==0)
					self::$our_query=false;

				self::$caching=self::$vars['page_caching'];
				self::$gzip=self::$vars['gzip'];
				if(self::$vars['cookie_domain'])
					self::$vars['cookie_domain']=str_replace('*',preg_replace('#^www\.#i','',self::$domain),self::$vars['cookie_domain']);
				if(self::$vars['parked_domains']=='redirect' and self::$vars['site_domain'] or !self::$domain)
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
						throw new EE($m,EE::USER,array('ban'=>'ip'));
					foreach(self::$ips as &$ip)
						if(self::IPMatchMask($ip,$bip))
							throw new EE($m,EE::USER,array('ban'=>'ip'));
				}
				unset(self::$vars['blocked_ips']);
			}
		}
		return self::$Instance;
	}

	/**
	 * Защита идеологии Singleton
	 */
	private function __construct(){}

	/**
	 * Метод быстрого создания объектов классов
	 * @param string $n Имя класса
	 */
	public function __get($n)
	{
		if(class_exists($n))
			return$this->$n=new$n;
		return parent::__get(debug_backtrace(),$n);
	}

	/**
	 * Обработчик всех возникающих ошибок
	 * @param int $num Номер ошибок
	 * @param string $str Описание ошибки
	 * @param string $f Файл, в котором возникла ошибка
	 * @param string $l Строка в файле, на которой возникла ошибка
	 */
	public static function ErrorHandle($num,$str,$f,$l)
	{
		if(self::$nolog or $num&E_STRICT)
			return;
		$ae=array(
			E_ERROR=>'Error',
			E_WARNING=>'Warning',
			E_NOTICE=>'Notice',
			E_PARSE=>'Parse error',
		);
		if(class_exists('EE'))#Заплатка на случай отключенного автолоадера
		{
			$E=new EE((isset($ae[$num]) ? $ae[$num].': ' : '').$str,EE::DEV,array('file'=>$f,'line'=>$l));
			if(DEBUG and !(E_PARSE&$num))
				throw$E;
			$E->Log();
		}
	}

	/**
	 * Обработчик неперехваченных исключений
	 * @param exception $E Объект неперехваченного исключения
	 */
	public static function ExceptionHandle($E)
	{
		$m=$E->getMessage();
		if($E instanceof EE)
			$E->Log();
		else
		{
			$E2=new EE($m,EE::UNIT,array(),$E);
			$E2->Log();
		}
		Error($m,isset($E->extra) ? $E->extra : array());
	}

	/**
	 * Автозагрузчик недостающих классов
	 * @param string $cl Имя класса, который нужно загрузить
	 */
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
				throw new EE('Class not found: '.$cl,EE::DEV,$a);
			}
			trigger_error('Class not found: '.$cl,E_USER_ERROR);
		}
	}

	/**
	 * Инициализация сервиса, с которого мы запустились. Сервис - это файл, с которого произведен запуск системы: index.php, admin.php, ajax.php и т.п.
	 */
	public static function InitService()
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

	/**
	 * Загрузка настроек
	 * @param string|array $need Ключевое слово групп настроек, которые должны быть загружены
	 * @param bool $r Флаг возврата полученных настроек. В случае паредачи FALSE, полученные настройки будут помещены в массив Eleanor::$vars
	 * @param bool $cache Флаг включения кэширования настроек
	 */
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
			$R=self::$Db->Query('SELECT `o`.`id`,`o`.`name`,`l`.`value`,`l`.`serialized`,`l`.`language`,`o`.`multilang`,`g`.`name` `gname`,`g`.`keyword` FROM `'.P.'config` `o` INNER JOIN `'.P.'config_l` `l` USING(`id`) INNER JOIN `'.P.'config_groups` `g` ON `g`.`id`=`o`.`group` WHERE `g`.`keyword` REGEXP \''.join('|',$kw).'\' ORDER BY `o`.`id` ASC');
			while($a=$R->fetch_assoc())
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

	/**
	 * Ловушка вывода. Позволяет передать корректные заголовки клиенту
	 * @param callback|FALSE $cb Функция обработчик контента непосредственно перед выводом его пользователю. В случае передачи FALSE, содержимое $data будет немедленно выдано пользователю.
	 * @param int $code HTTP код результата
	 * @param mixed $data, данные которые будут переданы вторым аргументом функции $cb (первым будет передан полученный контент)
	 */
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
			if(self::$gzip)
				header('Content-Encoding: gzip');
			header('X-Powered-CMS: Eleanor CMS http://eleanor-cms.ru',false,$code);
		}

		if($cb===false)
			self::FinishOutPut(false,$data);
		else
		{
			ob_start();
			register_shutdown_function(array(__class__,'FinishOutPut'),true,$cb,$data);
		}
	}

	/**
	 * Метод непосредственного вывода контента клиенту
	 * @access protected Но из-за того, что register_shutdown_function может вызвать protected метод, в коде этот метод описан как public
	 * @param bool $docb Флаг выполнения callback функции $cb
	 * @param callback|string $cb В случае $docb==true, переменная содержит callback функцию, обработки полученного содержимого непосредственно перед выводов, в ином случае само содержимое на вывод
	 * @param mixed $data Данные для передачи вторым аргументом в функцию $cb
	 */
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

	/**
	 * Форматирование пути: генерация полного пути к файлам по заданным параметрам
	 * @param string $p Путь, относительно корня сайта. Если он начинается с / , к слева будет прибавлен Eleanor::$root.
	 * @param string $cp Каталог, относительно которого строиться путь $p. Либо это абсолютный путь, относительно корня файловой системы, либо относительный путь относительно корня сайта.
	 */
	public static function FormatPath($p,$cp='')
	{
		$p=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,trim($p,'/\\'));
		if(strpos($p,'/')===0 or $cp=='')
			return self::$root.$p;
		$cp=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,$cp);
		return(self::$os=='u' && strpos($cp,'/')===0 || strpos($cp,':')==1 ? rtrim($cp,'/\\') : self::$root.trim($cp,'/\\')).($p ? DIRECTORY_SEPARATOR.$p : '');
	}

	/**
	 * Установка куки с учетом домена и префиксов куки, взятых из настроек
	 * @param string $n Имя куки
	 * @param string|FALSE $v Значение куки
	 * @param int|FALSE Время жизни куки в формате \d+[tsmMhd]?, где t - точный TIMESTAMP умирания куки, s - секунды, m - минуты, h - часы, d (по умолчанию) - дни, M - месяцы
	 * @param bool $safe Флаг доступности куки только через HTTP запросы, но не через Javascript (не обольщайтесь, браузеры дырявые)
	 */
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
					case'M':
						$t=strtotime('+ '.(int)$t.' MONTH');
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

	/**
	 * Получение куки с учетом префикса куки, полученного из настроек
	 * @param string $n Имя куки
	 */
	public static function GetCookie($n)
	{
		$n=self::$vars['cookie_prefix'].$n;
		return isset($_COOKIE[$n]) ? $_COOKIE[$n] : false;
	}

	/**
	 * Обертка для создания сессии
	 * @param string $id Идентификатор сессии, возможно, сессия будет создана наново
	 * @param string $n Имя сессии
	 */
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

	/**
	 * Получение языкового значения из массива со значениями для разных языков
	 * @param array $a Массив языковых значений должен содержать в качестве ключей названия языков и ключ пустую строку для универсального значения для всех языков
	 * @param string|FALSE $l Название языка, если передано FALSE, будет использоваться системный язык
	 * @param mixed $d Значение по умолчанию, которое будет возвращено методом в случае, если значение нужно языка отсутствует
	 */
	public static function FilterLangValues(array$a,$l=false,$d=null)
	{
		if(!$l)
			$l=Language::$main;
		if(isset($a[$l]))
			return$a[$l];
		if(isset($a['']))
			return$a[''];
		return isset($a[0]) ? $a[0] : $d;
	}

	/**
	 * Инициализация шаблона
	 * @param string $tpl Название шаблона
	 * @param string $path Путь к каталогу с шаблонами
	 */
	public static function InitTemplate($tpl,$path='templates/')
	{
		$f=self::$rootf.$path.$tpl;
		if(!is_dir($f))
			throw new EE('Template '.$tpl.' not found!',EE::ENV);

		self::$Template=new Template_Mixed;
		self::$Template->paths[$tpl]=$f.'/';
		self::$Template->default['theme']=$path.$tpl.'/';
		$init=$f.'.init.php';
		if(is_file($init))
			include$init;
		$config=$f.'.config.php';
		self::$Template->default['CONFIG']=is_file($config) && ($cfg=include$config) ? (array)$cfg : array();
		self::$Template->queue[]='Index';
	}

	/**
	 * Загрузка PHP файла в качестве оформления. Возвращает его содержимое с замененными переменными.
	 * @param string $f Абсолютный путь к файлу
	 * @param array $vars Массив переменных, передаваемых файлу
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

	/**
	 * Загрука файла шаблона списка
	 * @param string $n Название шаблона списка
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
				throw new EE('Unable to load list template '.$n,EE::DEV);
			}while(false);
		$p=array_slice(func_get_args(),1);
		extract(count($p)==1 && is_array($p[0]) ? $p[0] : $p,EXTR_PREFIX_INVALID,'v');
		$l=include$path;
		if(!is_array($l))
			throw new EE('Incorrect list template '.$n,EE::DEV);
		$L=new Template_List($l);
		$L->default=self::$Template->default;
		return$L;
	}

	/**
	 * Интерпретация bb логики в тексте
	 * Пример вывода переменной: Переменная var равна {var}
	 * Пример условия: [var]Переменная var равна {var}[/var]
	 * Пример условия с else: [var]Переменная var равна {var}[-var] Переменная var пуста[/var]
	 * Пример подбора корректной формы слова, в зависимости от рядом стоящего числа: Возраст пользователя {var} [var=plural]год|года|лет[var]
	 */
	public static function ExecBBLogic($s,array$r)
	{
		foreach($r as $k=>$v)
		{
			$fp=0;
			while(false!==$fp=strpos($s,'['.$k,$fp))
			{
				$kl=strlen($k);

				if(trim($s{$fp+$kl+1},'=] ')!='')
				{
					++$fp;
					continue;
				}

				$fpcl=false;#First Post Close
				do
				{
					$fpcl=strpos($s,']',$fpcl ? $fpcl+1 : $fp);
					if($fpcl===false)
					{
						++$fp;
						continue 2;
					}
				}while($s{$fpcl-1}=='\\');

				$ps=substr($s,$fp+$kl+1,$fpcl-$fp-$kl-1);
				$ps=str_replace('\\]',']',trim($ps));

				$fpcl++;#1 - это ]
				$lp=strpos($s,'[/'.$k.']',$fp);
				if($lp===false)
				{
					$len=$fpcl-$fp;
					$cont=false;
				}
				else
				{
					$len=$lp-$fp+$kl+3;#3 - это [/] закрывающего тега
					$cont=substr($s,$fpcl,$lp-$fpcl);
				}

				switch($ps)
				{
					case'=plural':
						$cont=call_user_func(array(Language::$main,'Plural'),$v,explode('|',$cont));
					break;
					default:
						if(isset($ps[1]))
							switch($ps[0])
							{
								case'=':
									$v=$v==substr($ps,1);
								break;
								case'>':
									$v=$ps[1]=='=' ? $v>=(int)substr($ps,2) : $v>(int)substr($ps,1);
								break;
								case'<':
									$v=$ps[1]=='=' ? $v<=(int)substr($ps,2) : $v<(int)substr($ps,1);
								break;
							}

						$cont=explode('[-'.$k.']',$cont,2)+array(1=>'');
						$cont=$v ? $cont[0] : $cont[1];
				}
				$s=substr_replace($s,$cont,$fp,$len);
			}
			if(is_scalar($v))
				$s=str_replace('{'.$k.'}',$v,$s);
		}
		return$s;
	}

	/**
	 * Вывод массиво в JSON и JavaScript переменные.
	 * @param array $a Для представления его в виде javascript переменных, либо JSON представления
	 * @param bool $t Включение обрамления результата в <script...>...</script>
	 * @param bool|string $n Переключатель формата вывода: false - набор переменных, true - JSON, string - в одноименную переменную помещается Object.
	 * @param string $p Префикс переменной
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
				$rv=is_int($v) || is_float($v) ? $v : '"'.addcslashes($v,"\n\r\t\"\\").'"';
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

	/**
	 * Метод обработки входящей строки для применения в качестве значения элемента формы
	 * @param string $s Строка-значение
	 * @param int $m Режим работы:
	 * 0 Текст прогоняется через htmlspecialchars, таким образом мы правим строку в таком виде, в каком мы ее получили.
	 * 1 Текст прогняется сначала через htmlspecialchars_decode, а потом через htmlspecialchars. Таким образом мы правим HTML в таком виде, в котором его видит пользователь. Циферные задания символов как &#93; пользователь правит, а не видит.
	 * 2 В тексте заменяются только < и > на &lt; и &gt; соответственно.
	 * 3 Править ХТМЛ в таком виде, в котором его видит пользователь.
	 * @param string $ch Кодировка
	 */
	public static function ControlValue($s,$m=1,$ch=CHARSET)
	{
		if($m==1)
			$s=htmlspecialchars_decode($s,ELENT);

		if($m==2)
			return str_replace(array('<','>'),array('&lt;','&gt;'),$s);
		elseif($s2=htmlspecialchars($s,ELENT,$ch,$m<3) or !$ch)
			return$s2;
		#Заплатка глюка, когда на UTF версии мы пытаемся открыть 1251 Файл.
		return self::ControlValue($s,$m,null);
	}

	/**
	 * Преобразование ассоциативного массива в параметры тега
	 * @param array $a Ассоциативный массив с параметрами название параметра=>значение параметра
	 */
	public static function TagParams(array$a)
	{
		$ad='';
		foreach($a as $k=>&$v)
			if($v!==false)
				if(is_int($k))
					$ad.=' '.$v;
				else
				{
					$ad.=' '.$k;
					if($v!==true)
						$ad.='="'.str_replace('"','&quot;',(string)$v).'"';
				}
		return$ad;
	}

	/**
	 * Генерация <input type="checkbox" />
	 * Из-за особенностей работы данного элемента формы, метод не содержит отдельного аргумента для передачи значения, поскольку 99% чекбоксам
	 * не важно, какое у них значение, важно, что они передались на сервер. Но значение чекбокса вы можете установить через массив $a.
	 * @param string $n Имя
	 * @param bool $c Отмеченность
	 * @param array $a Ассоциативных массив дополнительных параметров
	 */
	public static function Check($n,$c=false,array$a=array())
	{
		return'<input'.self::TagParams($a+array('type'=>'checkbox','value'=>1,'name'=>$n,'checked'=>(bool)$c)).' />';
	}

	/**
	 * Генерация <input type="radio" />
	 * @param string $n Имя
	 * @param string $v Значение
	 * @param bool $c Отмеченность
	 * @param array $a Ассоциативных массив дополнительных параметров
	 * @param int $m Метод вывода значения, подробнее смотрите метод ControlValue
	 */
	public static function Radio($n,$v=1,$c=false,array$a=array(),$m=1)
	{
		return'<input'.self::TagParams($a+array('type'=>'radio','value'=>$v ? self::ControlValue($v,(int)$m) : $v,'name'=>$n,'checked'=>(bool)$c)).' />';
	}

	/**
	 * Генерация <textarea>
	 * @param string $n Имя
	 * @param string $v Значение
	 * @param array $a Ассоциативных массив дополнительных параметров
	 * @param int $m Метод вывода значения, подробнее смотрите метод ControlValue
	 */
	public static function Text($n,$v='',array$a=array(),$m=1)
	{
		return'<textarea'.self::TagParams($a+array('rows'=>5,'cols'=>20,'name'=>$n)).'>'.self::ControlValue($v,(int)$m).'</textarea>';
	}

	/**
	 * Генерация <input> type по умолчанию равно text
	 * @param string $n Имя
	 * @param string $v Значение
	 * @param array $a Ассоциативных массив дополнительных параметров
	 * @param int $m Метод вывода значения, подробнее смотрите метод ControlValue
	 */
	public static function Input($n,$v=false,array$a=array(),$m=1)
	{
		return'<input'.self::TagParams($a+array('value'=>$v ? self::ControlValue($v,(int)$m) : $v,'type'=>'text','name'=>$n)).' />';
	}

	/**
	 * Генерация <input> преимущественно для кнопок
	 * @param string $v Надпись на кнопке
	 * @param string $t Тип кнопки: submit, button, reset
	 * @param array $a Ассоциативных массив дополнительных параметров
	 * @param int $m Метод вывода значения, подробнее смотрите метод ControlValue
	 */
	public static function Button($v='OK',$t='submit',array$a=array(),$m=1)
	{
		return self::Input(false,$v,$a+array('type'=>$t),$m);
	}

	/**
	 * Генерация <option> для Select
	 * @param string $t Выводимое значение
	 * @param string $v Значение
	 * @param bool $s Отмеченность
	 * @param array $a Ассоциативных массив дополнительных параметров
	 * @param int $m Метод вывода значения, подробнее смотрите метод ControlValue
	 */
	public static function Option($t,$v=false,$s=false,array$a=array(),$m=1)
	{
		return'<option'.self::TagParams($a+array('value'=>$v ? self::ControlValue($v,(int)$m) : $v,'selected'=>(bool)$s)).'>'.self::ControlValue($t,(int)$m).'</option>';
	}

	/**
	 * Генерация <optgroup> для Select
	 * @param string $l Название группы
	 * @param string $o Перечень option-ов
	 * @param array $a Ассоциативных массив дополнительных параметров
	 * @param int $m Метод вывода значения, подробнее смотрите метод ControlValue
	 */
	public static function Optgroup($l,$o,array$a=array(),$m=2)
	{
		return'<optgroup'.self::TagParams($a+array('label'=>$l ? self::ControlValue($l,$m) : $l)).'>'.$o.'</optgroup>';
	}

	/**
	 * Генерация <select> с одиночным выбором
	 * @param string $n Название select-а
	 * @param string $o Перечень option-ов
	 * @param array $a Ассоциативных массив дополнительных параметров
	 */
	public static function Select($n,$o='',array$a=array())
	{
		if(!$o)
		{
			$o=self::Option('');
			$a['disabled']=true;
		}
		return'<select'.self::TagParams($a+array('name'=>$n,'size'=>1,'class'=>'select')).'>'.$o.'</select>';
	}

	/**
	 * Генерация <select> с множественным выбором
	 * @param string $n Название select-а
	 * @param string $o Перечень option-ов
	 * @param array $a Ассоциативных массив дополнительных параметров
	 */
	public static function Items($n,$o='',array$a=array())
	{
		return self::Select(substr($n,-2)=='[]' ? $n : $n.'[]',$o,$a+array('size'=>5,'multiple'=>true));
	}
#Конец методов оформления.

#Методы пользовательского назначения
	/**
	 * Загрука класса авторизации
	 * @param string $l Название логина. По умолчанию классы загружаются из каталога core/login/*.php (значение * нужно указывать в $l)
	 */
	public static function LoadLogin($l)
	{
		$c='Login'.$l;
		if(!class_exists($c,false))
		{
			if(!is_file(self::$root.'core/login/'.$l.'.php'))
				throw new EE('Login '.$l.' not found!');
			require self::$root.'core/login/'.$l.'.php';
		}
		return new$c;
	}

	/**
	 * Применение логина, как главного в системе: установка системе пользовательских настроек (язык, часовой пояс)
	 * @param string|LoginClass $Login Логин
	 */
	public static function ApplyLogin($Login)
	{
		self::$Login=is_object($Login) ? $Login : self::LoadLogin($Login);
		self::$Permissions=new Permissions(self::$Login);
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

	/**
	 * Непосредственное получение разрешений групп
	 * @param array $g ID групп
	 * @param string $p Название параметра (столбец таблицы групп)
	 * @param string|FALSE $t Название таблицы с разрешениями групп
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
				$R=self::$Db->Query('SELECT * FROM `'.$t.'`');
				while($a=$R->fetch_assoc())
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

	/**
	 * Получение разрешений пользователя с учетом воможного членства в нескольких группах и перезагрузки настроек индивидуальными параметрами
	 * @param string $p Параметр группы, по которому необходимо получить разрешения. Выбор лучшего или худшего разрешения вы определяете самостоятельно.
	 * @param string|Login_class $l Логин
	 * @param bool|string $t Название таблицы с разрешениями групп
	 * @param string $go Название пользовательского параметра с массивом перегрузки параметров групп
	 */
	public static function GetPermission($p,$L=false,$t=false,$go='groups_overload')
	{
		if(!$L)
			$L=self::$Login;
		if(!$over=$L::GetUserValue($go) or !isset($over['method'][$p],$over['value'][$p]) or $over['method'][$p]=='inherit')
			return self::Permissions(self::GetUserGroups($L),$p,$t);
		$res=($add=$over['method'][$p]=='replace') ? array($over['value'][$p]) : self::Permissions(self::GetUserGroups($L),$p,$t);
		if(!$add)
			$res[]=$over['value'][$p];
		return$res;
	}

	/**
	 * Получение массива всех групп, в которых состоит пользователь
	 * @param string|Login_class $l Логин
	 * @return array
	 */
	public static function GetUserGroups($L=false)
	{
		if(!$L)
			$L=self::$Login;
		if($L ? $L::GetUserValue('id') : false)#Не ставить IsUser() - перестанет заходить в админку!
			return$L::GetUserValue('groups');
		else
			return self::$is_bot ? (array)self::$vars['bot_group'] : (array)self::$vars['guest_group'];
	}

	/**
	 * Запись пользовательской активности в таблицу сессии. Используется для списка "кто онлайн".
	 */
	public static function AddSession()
	{
		$uid=self::$Login->GetUserValue('id');
		$ua=getenv('HTTP_USER_AGENT');

		$n='';
		if(!$uid and self::$vars['bots_enable'] and $ua)
			foreach(self::$vars['bots_list'] as $k=>&$v)
				if(stripos($ua,$k)!==false)
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
				'user_id'=>(int)$uid,
				'!enter'=>'NOW()',
				'!expire'=>'\''.date('Y-m-d H:i:s').'\' + INTERVAL '.(isset(self::$vars['time_online'][$to]) ? (int)self::$vars['time_online'][$to] : 900).' SECOND',
				($uid>0 ? 'ip_user' : 'ip_guest')=>self::$ip,
				'info'=>serialize($info),
				'service'=>self::$service,
				'browser'=>$ua,
				'location'=>Url::Decode(preg_replace('#^'.preg_quote(self::$site_path,'#').'#','',$_SERVER['REQUEST_URI'])),
				'name'=>$n,
				'extra'=>self::$sessextra,
			)
		);
	}

	/**
	 * Проверка соответствия IP адреса заданной маске.
	 * Доступка поддержка IPv4 и IPv6, доступна поддержка диапазонов IP адресов и подсетей IP.
	 * Например: IPMatchMask('192.168.100.100','192.168.100.x'), IPMatchMask('192.168.100.100','192.168.100.50-192.168.100.150'), IPMatchMask('192.168.100.100','192.168.100.0/16')
	 * @param string $ip IP адрес, который проверяется
	 * @param string $m Маска, диапазон, диапазон с маской, подсеть
	 */
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
						$bm.=sprintf('%08b',hexdec($v));
					foreach($ip as &$v)
						$bip.=sprintf('%08b',hexdec($v));
				}
				else
				{
					$m[0]=explode('.',$m[0]);
					$ip=explode('.',$ip);
					foreach($m[0] as &$v)
						$bm.=sprintf('%08b',$v);
					foreach($ip as &$v)
						$bip.=sprintf('%08b',$v);
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
					$v=sprintf('%03d',$v);
				$m=ltrim(join($m),'0');
				foreach($mto as &$v)
					$v=sprintf('%03d',$v);
				$mto=ltrim(join($mto),'0');
				foreach($ip as &$v)
					$v=sprintf('%03d',$v);
				$ip=ltrim(join($ip),'0');
			}
			return bccomp($ip,$m)>=0 && bccomp($mto,$ip)>=0;
		}
	}
}

# Нижерасположенные классы находятся в этом файле только по причине гарантированного их использования при генерации 99% страницы.
# Вынесение этих классов в отдельные файлы лишь уменьшит скорость генерации страниц, ведь файлы придется инклудить - не самое быстрое действие

abstract class Template
{
	public
		$s='',#Аккомулятор результатов
		$cloned=false;#Флаг выполненной клонированости. Смысл состоит в том, что каждый fluent interface - отдельный независимый объект.

	/**
	 * Терминатор Fluent Interface, выдача результата
	 */
	public function __toString()
	{
		$s=$this->s;
		$this->s='';
		return$s;
	}

	/**
	 * Единичное выполнение какого-нибудь шаблона, без изменения текущего буфера
	 * @param string Название шаблона
	 * @params Переменные шаблона
	 */
	public function __invoke()
	{
		$n=func_num_args();
		if($n>0)
		{
			$a=func_get_args();
			return$this->_($a[0],array_slice($a,1));
		}
	}

	public function __clone()
	{
		$this->cloned=true;
	}

	/**
	 * Реализация fluent interface шаблона
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 */
	public function __call($n,$p)
	{
		if(!$this->cloned)
		{
			$O=clone$this;
			return$O->__call($n,$p);
		}

		$r=$this->_($n,$p);
		if($r===null or is_scalar($r) or is_object($r) and $r instanceof self)
		{
			$this->s.=$r;
			return$this;
		}
		return$r;
	}

	/**
	 * Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 */
	abstract public function _($n,array$p);
}

class Template_Mixed extends Template
{
	public
		$default=array(),#Переменные по-умолчанию, которые будут использоваться во всех темах. Ключ theme КРАЙНЕ НЕ рекомендуется трогать!
		$queue=array(),#Очередь классов на загрузку

		$classes=array(),#Классы тем оформления
		$paths=array(),#Дополнительные пути
		$files=array();#Дампы файлов

	/**
	 * Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 */
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
						$this->files[$k][]=basename($fv,'.php');#Оставляем только имена файлов
			}
			if(in_array($n,$this->files[$k]))
				return Eleanor::LoadFileTemplate($v.$n.'.php',(count($p)==1 && is_array($p[0]) ? $p[0] : $p)+$this->default);
		}

		while($cl=array_pop($this->queue))
		{
			$c='Tpl'.$cl;
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
		throw new EE('Template '.$n.' was not found!',EE::DEV,$a);
	}
}

class Template_List extends Template
{
	public
		$cloned=true,
		$default=array();

	protected
		$tpl;

	/**
	 * Конструктор шаблонизатора списка
	 * @param array $a Список шаблонов
	 */
	public function __construct(array$a)
	{
		$this->tpl=$a;
	}

	/**
	 * Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 */
	public function _($n,array$p)
	{
		if(!isset($this->tpl[$n]))
			throw new EE('Unknown list template: '.$n,EE::DEV);
		if(is_callable($this->tpl[$n]))
			return call_user_func_array($this->tpl[$n],$p);
		return Eleanor::ExecBBLogic($this->tpl[$n],(count($p)==1 && is_array($p[0]) ? $p[0] : $p)+$this->default);
	}
}
/*
class Template_Class extends Template
{
	public
		$cloned=true,
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
*/
### Cache

interface CacheMachineInterface #Интерфейс для создания кэшей
{
	/**
	 * Запись значения
	 * @param string $k Ключ. Обратите внимение, что ключи рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $value Значение
	 * @param int $t Время жизни этой записи кэша в секундах
	 */
	public function Put($k,$v,$ttl=0);

	/**
	 * Получение записи из кэша
	 * @param string $k Ключ
	 */
	public function Get($k);

	/**
	 * Удаление записи из кэша
	 * @param string $k Ключ
	 */
	public function Delete($k);

	/**
	 * Удаление записей по тегу. Если имя тега пустое - удаляется вешь кэш.
	 * @param string $t Тег
	 */
	public function DeleteByTag($tag);
}

class Cache
{
	public
		$table,#Таблица "вечного" кэша
		$Lib;#Кэш-машина

	/**
	 * Конструктор кэширующего класса
	 * @param string|FALSE $u Уникализация кэш хранилища
	 * @param string|FALSE $table Название таблицы для хранения "вечного" кэша
	 * @param array $cm Массив доступных кэш машин. Формат: имя класса=>путь к файлу
	 */
	public function __construct($u=false,$table=false,array$cm=array())
	{
		if($u===false)
			$u=crc32(__file__);
		$this->table=$table===false && defined('P') ? P.'cache' : $table;

		$a=array();
		if(function_exists('apc_store'))
			$a['CacheMachineApc']=Eleanor::$root.'core/cache_machines/apc.php';
		if(function_exists('memcache_connect'))
			$a['CacheMachineMemCache']=Eleanor::$root.'core/cache_machines/memcache.php';
		if(class_exists('Memcached',false))
			$a['CacheMachineMemCached']=Eleanor::$root.'core/cache_machines/memcached.php';
		if(function_exists('output_cache_put'))
			$a['CacheMachineZend']=Eleanor::$root.'core/cache_machines/zend.php';
		$cm+=$a;

		if(!isset($this->Lib))
			foreach($cm as $k=>&$v)
				if(class_exists($k,false) or is_file($v) and include$v)
				{
					try
					{
						$this->Lib=new $k($u);
					}catch(Exception$E){}
				}

		if(!isset($this->Lib))
		{
			#Вместо Serialize можно использовать HardDisk
			if(!class_exists('CacheMachineSerialize',false))
				include Eleanor::$root.'core/cache_machines/serialize.php';
			$this->Lib=new CacheMachineSerialize;
		}
	}

	/**
	 * Запись данных в кэш
	 * @param string $n Имя ячейки хранения кэша
	 * @param mixed $v Хранимые данные
	 * @param int $ttl Время хранения в секундах
	 * @param bool $tdb Флаг записи в таблицу с целью "вечного" кэширования
	 * @param int|FALSE Время безнадежного устаревания кэша. По умолчанию в два раза больше $ttl. Используется для предотвращения dog-pile effect
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
			$del=array();
			foreach($n as $k=>&$v)
				if($v===false)
					$del[]=$k;
				else
				{
					$r&=$this->Lib->Put($k,array($v,$ttl,time()+$ttl),$insur);
					if($tdb)
						$v=serialize($v);
				}
			if($del)
				$this->Delete($del,true);
		}

		if($tdb and $this->table)
		{
			if(DEBUG)
				foreach($n as &$v)
					$v=serialize($v);
			Eleanor::$Db->Replace($this->table,array('key'=>array_keys($n),'value'=>array_values($n)));
		}
		return$r;
	}

	/**
	 * Получение данных из кэша
	 * @param string $n Имя ячейки хранения кэша
	 * @param bool $fdb Флаг для осуществления попытки добыть кэш из таблицы "вечного" хранения, в случае неудачи при добычи кэша из основного хранилища
	 */
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
		if(!$fdb or !$n or !$this->table)
			return$r;

		$db=array();
		$R=Eleanor::$Db->Query('SELECT `key`,`value` FROM `'.$this->table.'` WHERE `key`'.Eleanor::$Db->In($n));
		while($a=$R->fetch_assoc())
			$db[$a['key']]=unserialize($a['value']);
		if($db and !DEBUG)
			$this->Put($db);
		return$a ? $db+$r : reset($db);
	}

	/**
	 * Удаление данных из кэша
	 * @param string $n Имя ячейки хранения кэша
	 * @param bool $fdb Флаг удаления кэша из таблицы "вечного" хранения
	 */
	public function Delete($n,$fdb=false)
	{
		if(is_array($n))
			foreach($n as &$v)
				$this->Lib->Delete($v);
		else
			$this->Lib->Delete($n);
		if($fdb and $this->table)
			Eleanor::$Db->Delete($this->table,'`key`'.Eleanor::$Db->In($n));
	}

	/**
	 * Пометка кэша устаревшим для его перегенерации. В отличии от метода Delete, использование этого метода не влечет за собой возможность появления dog-pile effect
	 * @param string $n Имя ячейки хранения кэша
	 * @param bool $fdb Флаг удаления кэша из таблицы "вечного" хранения
	 */
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

/**
 * Database class
 * @property MySQLi $Driver Объект MySQLi
 * @property string $db Имя базы данных
 * @property int $queries Счетчик запросов
 */
class Db extends BaseClass
{
	public
		$Driver,
		$db,
		$queries=0;

	/**
	 * Соединение с БД
	 * @param array $p Параметры соединения с БД. Ключи массива:
	 * host Сервер БД.
	 * user Пользователь БД
	 * pass Пароль пользоваетля
	 * db Название базы данных
	 * @throws EE_SQL
	 */
	public function __construct(array$p)
	{
		if(!isset($p['host'],$p['user'],$p['pass'],$p['db']))
			throw new EE_SQL('connect',$p);
		Eleanor::$nolog=true;#Подавление warining
		$M=new MySQLi($p['host'],$p['user'],$p['pass'],$p['db']);
		Eleanor::$nolog=false;
		if($M->connect_errno or !$M->server_version)
			throw new EE_SQL('connect',$p+array('error'=>$M->connect_error,'errno'=>$M->connect_errno));
		$M->autocommit(true);
		$M->set_charset(DB_CHARSET);

		$this->Driver=$M;
		$this->db=$p['db'];
	}

	/**
	 * Обертка для упрощенного доступа к методам объектов MySQLi и результата MySQLi
	 * @param string $n Имя вызываемого метода
	 * @param array $p Параметры вызова
	 */
	public function __call($n,$p)
	{
		if(method_exists($this->Driver,$n))
			return call_user_func_array(array($this->Driver,$n),$p);
		return parent::__call($n,$p);
	}

	/**
	 * Синхронизация времени БД со временем PHP (применение часового пояса). Синхронизируются только поля типа TIMESTAMP.
	 */
	public function SyncTimeZone()
	{
		$t=date_offset_get(date_create());
		$s=$t>0 ? '+' : '-';
		$t=abs($t);
		$s.=floor($t/3600).':'.($t%3600);
		$this->Driver->query('SET TIME_ZONE=\''.$s.'\'');
	}

	/**
	 * Старт транзакции
	 */
	public function Transaction()
	{
		$this->Driver->autocommit(false);
	}

	/**
	 * Подтверждение транзакции
	 */
	public function Commit()
	{
		$this->Driver->commit();
		$this->Driver->autocommit(true);
	}

	/**
	 * Откат транзакции
	 */
	public function RollBack()
	{
		$this->Driver->rollback();
		$this->Driver->autocommit(true);
	}

	/**
	 * Выполнение SQL запроса в базу
	 * @param string|array $q SQL запрос (в случае array, будет использовано multi_query)
	 * @param int|false $mode
	 * @return FALSE|mysqli_result|mysqli_object
	 */
	public function Query($q,$mode=MYSQLI_STORE_RESULT)
	{
		++$this->queries;

		$isa=is_array($q);
		if($isa)
			$q=join(';',$q);

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

		if($isa)
		{
			$R=$this->Driver->multi_query($q);
			$return_r=false;
		}
		elseif($mode===false)
		{
			$R=$this->Driver->real_query($q);
			$return_r=false;
		}
		else
		{
			$R=$this->Driver->query($q,$mode);
			$return_r=!defined('MYSQLI_ASYNC') || $mode!=MYSQLI_ASYNC;
		}

		if($R===false)
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

		return$return_r ? $R : $this->Driver;
	}

	/**
	 * Обертка для удобного осуществления INSERT запросов
	 * @param string $t Имя таблицы, куда необходимо вставить данные
	 * @param array $a Массив данных. Все данные автоматически экранируются. Если экранирование не нужно, перед именем поля поставьте !.
	 * Поддерживаются 3 формата вставки:
	 * 1. Вставка одной строки: array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field4'=>'NOW()')
	 * 2. Вставка многих строк вариант 1: array('field1'=>array('value1','value11'),'field2'=>(2,3),'field3'=>array(null,null),'!field4'=>array('NOW()','NOW() + INTERVAL 1 DAY'))
	 * 3. Вставка многих строк вариант 2: array( array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field4'=>'NOW()'), array('field1'=>'value11','field2'=>3,'field3'=>NULL,'!field4'=>'NOW() + INTERVAL 1 DAY') )
	 * Исходя из особенностей INSERT запросов в MySQL, при использовании 3го формата, ключи внутренних массивов должны быть ИДЕНТИЧНЫМИ.
	 * @param string $add Тип INSERT запроса
	 * @return int Insert ID
	 */
	public function Insert($t,array$a,$add='IGNORE')
	{
		$this->Query('INSERT '.$add.' INTO `'.$t.'`'.$this->GenerateInsert($a));
		return$this->Driver->insert_id;
	}

	/**
	 * Обертка для удобного осуществления REPLACE запросов
	 * @param string $t Имя таблицы, куда необходимо вставить данные
	 * @param array $a Массив данных. Идентично методу Insert
	 * @param string $add Тип REPLACE запроса
	 * @return int Affected rows
	 */
	public function Replace($t,array$a,$add='')
	{
		$this->Query('REPLACE '.$add.' INTO `'.$t.'` '.$this->GenerateInsert($a));
		return$this->Driver->affected_rows;
	}

	/**
	 * Генерация INSERT запроса из данных в массиве
	 * @param array $a Массив даных из метода Insert или Replace
	 */
	public function GenerateInsert(array$a)
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

	/**
	 * Обертка для удобного осуществления UPDATE запросов
	 * @param string $t Имя таблицы, где необходимо обновить данные
	 * @param array $a Массив изменямых данных. Все данные автоматически экранируются. Если экранирование не нужно, перед именем поля поставьте !.
	 * Например: array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field5'=>'NOW()')
	 * @param string Условие обновления. Секция WHERE, без ключевого слова WHERE
	 * @return int Affected rows
	 */
	public function Update($t,array$a,$w='',$add='IGNORE')
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

	/**
	 * Обертка для удобного осуществления DELETE запросов
	 * @param string $t Имя таблицы, откуда необходимо удалить данные
	 * @param string Условие удаления. Секция WHERE, без ключевого слова WHERE. Если этот параметр не заполнить, выполнится не DELETE, а TRUNCATE запрос.
	 * @return int Affected rows
	 */
	public function Delete($t,$w='')
	{
		$this->Query($w ? 'DELETE FROM `'.$t.'` WHERE '.$w : 'TRUNCATE TABLE `'.$t.'`');
		return$this->Driver->affected_rows;
	}

	/**
	 * Преобразование массива в последовательность для конструкции IN(). Данные автоматически экранируются
	 * @param mixed $a Данные для конструкции IN
	 * @param bool $not Включение конструкции NOT IN. Для оптимизации запросов, конструкция IN не всегда включается, иногда используется просто =
	 */
	public function In($a,$not=false)
	{
		if(is_array($a) and count($a)==1)
			$a=reset($a);
		if(is_array($a))
			return($not ? ' NOT' : '').' IN ('.join(',',$this->Escape($a)).')';
		return($not ? '!' : '').'='.$this->Escape($a);
	}

	/**
	 * Экранирование опасных символов в строках
	 * @param string $s Строка для экранирования
	 * @param bool $qs Флаг включения одинарных кавычек в начало и в конец результата
	 */
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
		return$qs ? '\''.$s.'\'' : $s;
	}
}
#Функция для обработки результатов запросов

interface LoginClass#Интерфейс для создания медов авторизации
{
	/**
	 * Аутентификация по определенным входящим параметрам, например, по логину и паролю
	 * @param array $data Массив с данными
	 * @throws EE
	 */
	public static function Login(array$data);

	/**
	 * Аутентификация только по ID пользователя
	 * @param int $id ID пользователя
	 * @throws EE
	 */
	public static function Auth($id);

	/**
	 * Авторизация пользователя: проверка является ли пользователь пользователем
	 * @param bool $hard Метод кэширует результат, для сброса кэша передайте true
	 * @return bool
	 */
	public static function IsUser($hard=false);

	/**
	 * Применение логина, как главного в системе: подстройка системы под пользователя, настройка часового пояса, проверка забаненности и т.п.
	 * @throws EE
	 */
	public static function ApplyCheck();

	/**
	 * Выход пользователя из учетной записи
	 */
	public static function Logout();

	/**
	 * Формирование ссылки на учётную запись пользователя
	 * @param string|array $name Имя пользователя
	 * @param string|array $id ID пользователя
	 * @return string|array|FALSE
	 */
	public static function UserLink($name,$id=0);

	/**
	 * Получение значения пользовательского параметра
	 * @param array|string $key Один или несколько параметров, значения которых нужно получить
	 * @return array|string В зависимости от типа переданной переменной
	 */
	public static function GetUserValue($value);

	/**
	 * Установка значения пользовательского параметра. Метод не должен обновлять данны пользователя в БД! Только на время работы скрипта
	 * @param array|string $key Имя параметра, либо массив в виде $key=>$value
	 * @param mixed $value Значения
	 */
	public static function SetUserValue($key,$value=null);
}

class Language extends BaseClass implements ArrayAccess
{
	public static
		$main=LANGUAGE;#Переменная определяющая системный язык

	public
		$loadfrom='langs',#Каталог по умолчанию, откуда будут загружаться неинициализированные файлы
		$queue=array();#Очередь файлов для загрузки имя группы => файл

	protected
		$l,#Имя языка
		$gr,#Признак группировки значений по секциям
		$db,#Данные всех языков
		$files=array();#Включенные файлы

	/**
	 * Конструктор языкового объекта
	 * @param bool|string $f Путь до файла с языковыми переменным. В случае передачии true, включается группировка значений по секциям, false - создается "пустой" объект
	 * @param string $s В случае передачи в $f файла с языковыми переменными, эта переменная указывает на секцию, в которую необходимо поместить результат
	 */
	public function __construct($f=false,$s='')
	{
		if($f===true)
			$this->gr=true;
		elseif($f)
			$this->Load($f,$s);
		$this->l=self::$main;
	}

	/**
	 * Возвращение текущего языка объекта
	 */
	public function __toString()
	{
		return$this->l;
	}

	/**
	 * Выполнение универсальных методов языковых классов. Название языкового класса совпадает с названием языка: Russian, English...
	 * @param string $n Название метода
	 * @param array $p Параметры метода
	 */
	public function __call($n,$p)
	{
		if(method_exists($this->l,$n))
			return call_user_func_array(array($this->l,$n),$p);
		$c=array($this->l,$n);
		if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
			return$s;
		return parent::__call($n,$p);
	}

	/**
	 * Загрузка языкового файла в объект
	 * Структура языкового файла должна быть такой:
	 * <?php
	 * return array(
	 *     'param1'=>'value1',
	 *     ...
	 *     );
	 *
	 * @param string $f Имя файла, в котором вместо * будет подставлено название текущего языка
	 * @param string $s Название секции, в которую будет помещен языковой массив из файла
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

	/**
	 * Изменение языка объекта. В этом случае все языковые файлы будут перезагружены
	 * @param string|FALSe $l Название нового языка. В случае передачи FALSE, будет установлен системный язык
	 */
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

	/**
	 * Установка языковой переменной
	 * @param string $k Имя переменной
	 * @param mixed $v Языковое значение
	 */
	public function offsetSet($k,$v)
	{
		if($this->gr)
			$this->db[$k]=$v;
		else
			$this->db[''][$k]=$v;
	}

	/**
	 * Проверка существования языковой переменной
	 * @param string $k Имя переменной
	 */
	public function offsetExists($k)
	{
		return$this->gr ? isset($this->db[$k]) : isset($this->db[''][$k]);
	}

	/**
	 * Удаление языковой переменной
	 * @param string $k Имя переменной
	 */
	public function offsetUnset($k)
	{
		if($this->gr)
			unset($this->db[$k]);
		else
			unset($this->db[''][$k]);
	}

	/**
	 * Получение языковой переменной
	 * @param string $k Имя переменной
	 */
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