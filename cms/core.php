<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Basic,
	Eleanor\Assign,
	Eleanor\Library,
	Eleanor\Classes\E,
	Eleanor\Classes\L10n,
	Eleanor\Classes\Cache,
	Eleanor\Classes\MySQL,
	Eleanor\Classes\Template,

	CMS\Interfaces\External;

use function
	Eleanor\Autoloader,
	Eleanor\BugFileLine;

use const Eleanor\BASE_TIME;

const
	/** @const Квинтэссенция ENT_* констант  */
	ENT = \ENT_QUOTES | \ENT_HTML5 | \ENT_SUBSTITUTE | \ENT_DISALLOWED,

	/** @const Квинтэссенция полезных опций JSON */
	JSON = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,

	/** @const Полный путь к корню каталога */
	ROOT = __DIR__.\DIRECTORY_SEPARATOR;

require ROOT.'constants.php';
require ROOT.'library/core.php';

Library::$logs=ROOT.'logs/';

/** Загрузка конфигураций
 * @param string $file Имя файла с конфигурациями
 * @param string $dir Каталог с файлами
 * @returns array */
function Config(string$file,string$dir=ROOT.'config/'):?array
{
	$php=$dir.$file.'.php';
	$json=$dir.$file.'.json';

	if(!\is_file($json) and !\is_file($php))
		return null;

	//PHP через переменную $config будет иметь доступ к содержимому JSON конфига
	$config=\is_file($json) ? \json_decode(\file_get_contents($json),true) : [];
	$config2=\is_file($php) ? (array)require$php : [];

	return$config2+$config;
}

/** Установка куки
 * @param string $n Имя
 * @param string $v Значение
 * @param int $ttl Срок жизни, при 0 устанавливается сессионная куки (автоматически удаляемая после завершения сеанса)
 * @return bool */
function SetCookie(string$n,string$v='',int$ttl=31536000):bool#1 год по умолчанию
{
	if($v=='')
		$ttl=0;
	elseif($ttl)
		$ttl+=\time();

	return \setcookie($n,$v,$ttl,\Eleanor\SITEDIR,\Eleanor\DOMAIN,\Eleanor\PROTOCOL=='https://',true);
}

/** Перенаправление на другую страницу
 * @param string $to
 * @param int $code Код редиректа 30*
 * @param string $hash Содержимое якоря (без #)
 * @return never */
function Redirect(string$to,int$code=301,string$hash=''):never
{
	if(!\str_starts_with($to,'/'))
		$to=\Eleanor\SITEDIR.$to;

	if($hash)
		$to.='#'.\ltrim($hash,'#');

	\header('Cache-Control: no-store');
	\header('Location: '.\rtrim(\html_entity_decode($to),'&?'),true,$code);
	die;
}

/** Проверка набора параметров, являются ли они строками. Пример: IsS($_POST['p1'] ?? 0,$_POST['p2'] ?? 0) */
function IsS(...$a):bool
{
	foreach($a as $v)
		if(!\is_string($v))
			return false;

	return true;
}

/** Основной "верблюд" системы: объект служит для хранения общих данных. Динамические свойства по умолчанию содержат
 * либо одноимённые юниты, либо объекты классов */
#[\AllowDynamicProperties]
class CMS extends Library
{
	static bool
		/** @var bool $json флаг запроса, на который ожидается json */
		$json=false,

		/** @var bool $post флаг POST запроса */
		$post=false,

		/** @var bool $delete флаг DELETE запроса */
		$delete=false;

	/** @var ?int ID авторизации */
	static ?int $a11n=null;

	/** @var Authorization|Assign|null $A Эта переменная должна заполняться исключительно в файле, который запускает систему (index.php, admin.php) */
	static Authorization|Assign|null $A;

	/** @var Permissions|Assign|null $P Базовые разрешения пользователя на сайте, подтягиваются из таблицы groups */
	static Permissions|Assign|null $P;

	/** @var MySQL|Assign|null $Db Базы данных */
	static MySQL|Assign|null $Db;

	/** @var ?\ArrayObject Объект для получения доступа к конфигам */
	static ?\ArrayObject $config;

	/** @var Cache|Assign|null $Cache Кэш */
	static Cache|Assign|null $Cache;

	/** @var Template|Assign|null $T Шаблонизатор */
	static Template|Assign|null $T;

	/** @var string IP адрес клиента в бинарном формате inet_pton() */
	static string $ip;

	/** Obtaining unit or object of class
	 * @throws E */
	function __get(string$n):mixed
	{
		$unit=__DIR__."/units/{$n}.php";

		if(\is_file($unit))
			return$this->$n=include$unit;

		return parent::__get($n);
	}

	function __invoke(string$n,string$dir=__DIR__):mixed
	{
		return parent::__invoke($n,$dir);
	}
}

class Output extends \Eleanor\Classes\Output
{
	/** @const Powered by header. Feel free to get rid of this shit */
	protected const string POWERED='X-Powered-CMS: Eleanor CMS https://eleanor-cms.com';
}

CMS::$ip=\filter_var($_SERVER['REMOTE_ADDR'] ?? 0,FILTER_VALIDATE_IP) ? \inet_pton($_SERVER['REMOTE_ADDR']) : 0;
CMS::$config=new class extends \ArrayObject
{
	/** Получение значения
	 * @param mixed $key Ключ, который необходимо получить
	 * @return mixed
	 * @throws E */
	function offsetGet(mixed$key):mixed
	{
		if($this->offsetExists($key))
			return parent::offsetGet($key);

		$config=Config($key);

		if($config)
		{
			$this->offsetSet($key,$config);
			return $config;
		}

		throw new E("Unable to load config '{$key}'",E::PHP,...BugFileLine($this));
	}
};

CMS::$json=getenv('HTTP_ACCEPT')==='application/json';
CMS::$post=$_SERVER['REQUEST_METHOD']==='POST';
CMS::$delete=$_SERVER['REQUEST_METHOD']==='DELETE';

//Чтобы сравнивать по $_SERVER['HTTP_CONTENT_TYPE']===Output::JSON, нужно передавать соответствующий заголовок, а это вызывает конфликт CORS (Access-Control-Allow-Headers)
if(!$_POST && !$_FILES && CMS::$post)
{
	$json=\json_decode(\file_get_contents('php://input'),true);

	if(\is_array($json))
	{
		CMS::$json=true;
		$_POST+=$json;
	}
}

#All errors will be shown in JSON mode
if(CMS::$json)
	CMS::$bsodtype=Output::JSON;

Assign::For(CMS::$Db,fn()=>new \Eleanor\Classes\MySQL(
	CMS::$config['db']['host'],
	CMS::$config['db']['user'],
	CMS::$config['db']['pass'],
	CMS::$config['db']['db'],
));
Assign::For(CMS::$Cache,fn()=>new Cache(ROOT.'cache/',ROOT.'cache/storage/'));
Assign::For(CMS::$T,fn()=>new class extends Template {
	/** @var bool Flag to run cron.php (background tasks) */
	private(set) bool $cron {
		get {
			if(!isset($this->cron))
			{
				$task=CMS::$Cache->Get('cron');//Contains timestamp to next run
				$this->cron=$task===null || $task<=\time();
			}

			return $this->cron;
		}
	}
});
Assign::For(CMS::$P,fn()=>Permissions());

/** Механизм для проверки авторизации пользователя на сайте и его выхода. Аутентификация здесь не производится */
class Authorization extends Basic
{
	/** @var int ID основного пользователя, из-под которого осуществляется работа (0 - пользователя нет) */
	protected(set) int$current;

	/** @var array ID пользователя доступных для быстрого переключения */
	protected(set) array$available;

	/** @param string $table Таблица, связывающая пользователей с a11n
	 * @param int $current ID текущего пользователя (если указан недоступный, будет выбран первый попавшийся)
	 * @param ?External $Ext Внешняя авторизация пользователей
	 * @throws \Throwable */
	function __construct(readonly string$table,int$current=0,readonly ?External$Ext=null)
	{
		$available=$Ext?->Get() ?? [];
		$hidden=[];

		if(CMS::$a11n)
		{
			$failed=[];

			$R=CMS::$Db->Execute(<<<SQL
SELECT `user_id`, `salt`, `way` FROM `{$table}` WHERE `a11n_id`=?
SQL ,[CMS::$a11n]);
			while($a=$R->fetch_assoc())
			{
				$id=(int)$a['user_id'];

				if(\in_array($id,$available))
					continue;

				if($a['way']=='dashboard')
					$hidden[]=$id;

				$salt=$this->Salt($id);
				$salt=\is_string($_COOKIE[$salt] ?? 0) ? $_COOKIE[$salt] : '';

				if(\trim($a['salt']) and \bin2hex($a['salt'])!==$salt)
					$failed[]=$id;
				else
					$available[]=$id;
			}

			foreach($failed as $id)
			{
				CMS::$Db->Delete($this->table,"`user_id`={$id} AND `a11n_id`=".CMS::$a11n);
				$Ext?->SignOut($id);
			}
		}

		if(\in_array($current,$available))
		{
			$this->current=$current;
			$available=\array_filter($available,fn($item)=>$item!==$current);
		}
		else
			$this->current=\array_shift($available) ?? 0;

		$this->available=$available;

		#Updating information about last user's activity
		if($this->current)
		{
			if(!\in_array($this->current,$hidden))
				CMS::$Db->Update('users',['activity'=>fn()=>'NOW()'],'`id`='.$this->current);

			CMS::$Db->Update('a11n',[
				'used'=>fn()=>'NOW()',
				'ip'=>CMS::$ip,
				'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? '',
			],'`id`='.CMS::$a11n);
		}
	}

	/** Получение имени солёной куки для пользователя
	 * @param int $id ID пользователя
	 * @return string */
	protected function Salt(int$id):string
	{
		return A11N_COOKIE."-{$this->table}-".$id;
	}

	/** Выход из учётной записи
	 * @param ?int $id ID пользователей на выход
	 * @param int $next ID of next current user (when $id is current user)
	 * @throws \Throwable */
	function SignOut(?int$id=null,int$next=0):void
	{
		$id??=$this->current;

		CMS::$Db->Delete($this->table,"`user_id`={$id} AND `a11n_id`=".CMS::$a11n);
		$this->Ext?->SignOut($id);

		if($id===$this->current)
			$this->current=\in_array($next,$this->available)
				? \array_splice($this->available,\array_search($next,$this->available),1)[0]
				: (\array_shift($this->available) ?? 0);

		elseif(\in_array($id,$this->available))
			\array_splice($this->available,\array_search($id,$this->available));
	}

	/** Установка куки успешной авторизации
	 * @param int $id ID пользователя
	 * @param bool $temp Флаг временной сессии (куки удалятся после закрытия окна/вкладки браузера)
	 * @param array $extra Extra DB parameters of session
	 * @throws \Throwable */
	function SignIn(int$id,bool$temp=false,array$extra=[]):void
	{
		A11N();

		$salt=$temp ? \random_bytes(5) : 0;

		CMS::$Db->Replace($this->table,['user_id'=>$id,'a11n_id'=>CMS::$a11n,'salt'=>$salt]+$extra);

		if($temp)
			SetCookie($this->Salt($id),\bin2hex($salt),0);

		$this->Ext?->SignIn($id);

		if($this->current and $this->current!=$id and !in_array($id,$this->available))
			$this->available[]=$id;
		elseif(!$this->current)
			$this->current=$id;
	}
}

/** Проверка прав пользователя на сайте на основе его нахождения в определённых группах */
 class Permissions extends Basic
 {
	/** @param array $groups of user groups, first one is user's main group
	 * @param array $rights right name => group id => value
	 * @param array $roles list of roles */
	function __construct(readonly array$groups,readonly array$rights,readonly array$roles=[]){}

	function __get(string$n):array
	{
		//Если гость, то у него все права пустые
		if(!$this->rights)
			return[];

		if(!isset($this->rights[$n]))
			new E('Reading unknown right: '.$n,E::PHP,...BugFileLine($this))->Log();

		$result=[];

		foreach($this->groups as $g)
			if(isset($this->rights[$n][$g]))
				$result[]=$this->rights[$n][$g];

		return $result;
	}
}

/** Get permissions of the user
 * @param ?int $id UserID
 * @return Permissions */
function Permissions(?int$id=null):Permissions
{
	$groups=$rights=$roles=[];
	$id??=CMS::$A->current;

	if($id)
	{
		$R=CMS::$Db->Query(<<<SQL
SELECT `groups` FROM `users` WHERE `id`={$id} LIMIT 1
SQL );
		if($R->num_rows>0)
			$groups=\json_decode($R->fetch_column(),true) ?? [];
	}

	if($groups)
	{
		$multi=L10NS!==null;

		$R=CMS::$Db->Query('SELECT * FROM `groups` WHERE `id`'.CMS::$Db->In($groups));
		while($a=$R->fetch_assoc())
		{
			$id=(int)$a['id'];

			if($a['roles'])
				\array_push($roles,...\explode(',',$a['roles']));

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			foreach(array_slice($a,2) as $right=>$v)//Skip id and roles
				$rights[$right][$id]=\ctype_digit($v) ? (int)$v : $v;
		}
	}

	return new Permissions($groups,$rights,\array_unique($roles));
}

/** Установка $a11n
 * @throws \Throwable */
function A11N():void
{
	if(CMS::$a11n!==null)
		return;

	$bytes=\random_bytes(7);
	$id=CMS::$Db->Insert('a11n',\compact('bytes'));

	//Если все ID закончились... Очистим таблицу. Для недавно вошедших пользователей создаётся неудобство, но случается такое нечасто
	if($id>A11N_TRUNCATE_AFTER)
	{
		CMS::$Db->Query('DELETE FROM `a11n`');
		CMS::$Db->Query('ALTER TABLE `a11n` AUTO_INCREMENT=1');

		$id=CMS::$Db->Insert('a11n',\compact('bytes'));
	}

	CMS::$a11n=$id;

	SetCookie(A11N_COOKIE,\bin2hex($bytes).\dechex($id));
}

/** Get user's data from table
 * @param array|string $keys fields from users table
 * @param ?int $id User ID
 * @param string $table Table name
 * @return string|array depends on type of $keys param */
function GetUsers(array|string$keys,?int$id=null,string$table='users'):array|string
{static$data=[];
	$id??=CMS::$A->current;
	$isa=is_array($keys);
	$data[$id]??=[];
	$result=[];

	if(!$isa)
		$keys=[$keys];

	$F=fn($item)=>!isset($data[$id][$item]);

	if(array_any($keys,$F))
	{
		$fields=array_filter($keys,$F);
		$fields=join('`,`',$fields);

		$data[$id]+=CMS::$Db->Query(<<<SQL
SELECT `{$fields}` FROM `{$table}` WHERE `id`={$id} LIMIT 1
SQL )->fetch_assoc();
	}

	foreach($keys as $k)
		$result[$k]=$data[$id][$k];

	return $isa ? $result : reset($result);
}

/** Alias. Генерация nonce для скриптов. Они могут быть использованы повторно
 * @return string
 * @throws \Random\RandomException */
function Nonce():string
{
	return OutPut::Nonce();
}

/** Стандартный вывод
 * @param string $output Содержимое страницы
 * @rnever-return */
function HTML(string$output,...$a):never
{
	Output::SendHeaders(Output::HTML,...$a);

	die($output);
}

/** Вывод JSON
 * @param ?array $json Содержимое страницы
 * @rnever-return */
function JSON(?array$json,...$a):never
{
	Output::SendHeaders(Output::JSON,...$a);

	die(\json_encode($json,JSON));
}

\spl_autoload_register(fn(string$c)=>Autoloader($c,__DIR__,__NAMESPACE__));

(function(){
	$a=\is_string($_COOKIE[A11N_COOKIE] ?? 0) ? $_COOKIE[A11N_COOKIE] : '';

	//Ключ сессии состоит из 14 случайных байт и порядкового номера в hex виде
	if(!\ctype_xdigit($a) or \strlen($a)<15)
		return;

	[$bytes,$id]=\str_split($a,14);

	$R=CMS::$Db->Query(<<<SQL
SELECT IF(`generated`<NOW() - INTERVAL 1 WEEK,1,0) `regen`, IF(`generated`<NOW() - INTERVAL 1 YEAR ,1,0) `obsolete` 
FROM `a11n`
WHERE `id`=0x{$id} AND `bytes`=0x{$bytes}
LIMIT 1
SQL );
	if($R->num_rows<1)
		return;

	$sess=$R->fetch_assoc();
	$upd=[];

	//Регенерация ключа
	if($sess['regen'])
	{
		$upd['generated']=fn()=>'NOW()';
		$upd['bytes']=\random_bytes(7);

		SetCookie(A11N_COOKIE,\bin2hex($upd['bytes']).$id);
	}

	$id=\hexdec($id);

	//Если сессии больше года - очистим все входы (через внешние ключи)
	if($sess['obsolete'])
	{
		$upd['id']=$id;

		CMS::$Db->Delete('a11n','`id`='.$id);
		CMS::$Db->Insert('a11n',$upd);
	}
	elseif($upd)
		CMS::$Db->Update('a11n',$upd,'`id`='.$id);

	CMS::$a11n=$id;
})();