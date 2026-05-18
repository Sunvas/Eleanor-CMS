<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\{Basic,Assign,Library};
use Eleanor\Classes\{E, Cache, MySQL, Template};
use CMS\Interfaces\External;

use function
	Eleanor\Autoloader,
	Eleanor\BugFileLine;

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

/** Основной "верблюд" системы: объект служит для хранения общих данных. Динамические свойства по умолчанию содержат
 * либо одноимённые юниты, либо объекты классов */
#[\AllowDynamicProperties]
class CMS extends Library
{
	static bool
		/** @var bool $json Flag when JSON is expected in response */
		$json=false,

		/** @var bool $put Flag of PUT request method */
		$put=false,

		/** @var bool $post Flag of POST request method */
		$post=false,

		/** @var bool $delete Flag of DELETE request method */
		$delete=false;

	/** @var ?int Authorization (a11n) ID */
	static ?int $a11n=null;

	/** @var Authorization|Assign|null $A This property should be set exclusively in file where system starts from (index.php, admin.php) */
	static Authorization|Assign|null $A;

	/** @var Permissions|Assign|null $P Basic user permissions on the site are pulled from the groups table. */
	static Permissions|Assign|null $P;

	/** @var MySQL|Assign|null $Db DataBase object */
	static MySQL|Assign|null $Db;

	/** @var ?\ArrayObject Object for accessing configs */
	static ?\ArrayObject $config;

	/** @var Cache|Assign|null $Cache */
	static Cache|Assign|null $Cache;

	/** @var Template|Assign|null $T Template engine object */
	static Template|Assign|null $T;

	/** @var string IP in binary inet_pton() */
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

class L10n extends \Eleanor\Classes\L10n
{
	/** Static obtaining value from existed l10n pool
	 * @param array $l10n Pool of values
	 * @param mixed $d Default value
	 * @param string $f Fallback value
	 * @return mixed */
	static function Item(array$l10n,mixed$d=null,string$f=L10N):mixed
	{
		return parent::Item($l10n,$d,$f);
	}
}

CMS::$config=new class extends \ArrayObject
{
	/** Obtaining values by key
	 * @param mixed $key
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

CMS::$ip=\filter_var($_SERVER['REMOTE_ADDR'] ?? 0,FILTER_VALIDATE_IP) ? \inet_pton($_SERVER['REMOTE_ADDR']) : 0;
CMS::$json=\getenv('HTTP_ACCEPT')==='application/json';

switch($_SERVER['REQUEST_METHOD'])
{
	case'PUT':
		CMS::$put=true;
	break;
	case'POST':
		CMS::$post=true;
	break;
	case'DELETE':
		CMS::$delete=true;
}

//Comparison via header like $_SERVER['HTTP_CONTENT_TYPE']===Output::JSON causes CORS conflict (Access-Control-Allow-Headers)
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
Assign::For(CMS::$Cache,fn()=>new Cache(ROOT.'cache'));
Assign::For(CMS::$T,fn(...$a)=>new class(...$a) extends Template {
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

/** Checking user authorization and logout on the site. No authentication is performed here. */
class Authorization extends Basic
{
	/** @var int ID of the current user (0 - no user) */
	protected(set) int$current;

	/** @var array User IDs available for quick switching */
	protected(set) array$available;

	/** @param string $table A table linking users to a11n
	 * @param int $current ID of the current user (if unavailable, the first available one will be used)
	 * @param ?External $Ext Object of external user authorization. See cms/external.php
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

				if($a['way']=='admin-panel')
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

	/** Obtaining name of salt cookie for the user
	 * @param int $id User ID
	 * @return string */
	protected function Salt(int$id):string
	{
		return A11N_COOKIE."-{$this->table}-".$id;
	}

	/** Logout
	 * @param ?int $id User ID
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

	/** Setting cookie of the successful authorization
	 * @param int $id User ID
	 * @param bool $temp Temporary session flag (cookies are deleted after the browser window/tab is closed)
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

/** Checking user rights on the site based on their membership in groups. */
class Permissions extends Basic
{
	/** @param array $groups of user groups, first one is user's main group
	 * @param array $rights right name => group id => value OR right name => [IDS of groups] (array should be list)
	 * @param array $roles list of roles */
	function __construct(readonly array$groups,readonly array$rights,readonly array$roles=[]){}

	function __get(string$n):array|bool
	{
		//If guest, all fields will be empty
		if(!$this->rights)
			return[];

		if(!\is_array($this->rights[$n] ?? 0))
			new E('Reading incorrect right: '.$n,E::PHP,...BugFileLine($this))->Log();

		if(\array_is_list($this->rights[$n]))
			return \boolval(\array_intersect($this->rights[$n],$this->groups));

		$result=[];

		foreach($this->groups as $g)
			if(isset($this->rights[$n][$g]))
				$result[]=$this->rights[$n][$g];

		return $result;
	}
}

/** Get permissions of the user
 * @param ?int $id UserID
 * @return Permissions
 * @throws \Throwable */
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

/** Setting $a11n
 * @throws \Throwable */
function A11N():void
{
	if(CMS::$a11n!==null)
		return;

	$bytes=\random_bytes(7);
	$id=CMS::$Db->Insert('a11n',\compact('bytes'));

	//If we'd run out of IDs... Let's clear the table. This creates an inconvenience for recently logged in users, but it doesn't happen often.
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
 * @return string|array depends on type of $keys param
 * @throws \Throwable */
function GetUsers(array|string$keys,?int$id=null,string$table='users'):array|string
{static$data=[];
	$id??=CMS::$A->current;
	$isa=\is_array($keys);
	$data[$id]??=[];
	$result=[];

	if(!$isa)
		$keys=[$keys];

	$F=fn($item)=>!isset($data[$id][$item]);

	if(\array_any($keys,$F))
	{
		$fields=\array_filter($keys,$F);
		$fields=\join('`,`',$fields);

		$data[$id]+=CMS::$Db->Query(<<<SQL
SELECT `{$fields}` FROM `{$table}` WHERE `id`={$id} LIMIT 1
SQL )->fetch_assoc();
	}

	foreach($keys as $k)
		$result[$k]=$data[$id][$k];

	return $isa ? $result : \array_first($result);
}

/** Alias. Generate nonces for scripts. They can be reused.
 * @return string
 * @throws \Random\RandomException */
function Nonce():string
{
	return OutPut::Nonce();
}

/** Alias. Attempt to return 304 http code (Not Modified) when browser's cache is up to date */
function Return304(...$a):bool
{
	return OutPut::Return304(...$a);
}

/** Alias */
function Link(...$a):void
{
	OutPut::Link(...$a);
}

/** HTML output
 * @param string $output Content of the page
 * @never-return */
function HTML(string$output,...$a):never
{
	if($output=='')
		header('Cache-Control: no-store',true,204);
	else
		Output::SendHeaders(Output::HTML,...$a);

	die($output);
}

/** JSON output
 * @param ?array $json output content
 * @never-return */
function JSON(?array$json,...$a):never
{
	Output::SendHeaders(Output::JSON,...$a);

	die(\json_encode($json,JSON));
}

\spl_autoload_register(fn(string$c)=>Autoloader($c,__DIR__,__NAMESPACE__));

(function(){
	$a=\is_string($_COOKIE[A11N_COOKIE] ?? 0) ? $_COOKIE[A11N_COOKIE] : '';

	//The session key consists of 14 random bytes and a sequence number in hex format.
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

	//Key regeneration
	if($sess['regen'])
	{
		$upd['generated']=fn()=>'NOW()';
		$upd['bytes']=\random_bytes(7);

		SetCookie(A11N_COOKIE,\bin2hex($upd['bytes']).$id);
	}

	$id=\hexdec($id);

	//If the sessions are more than a year old, let's clear all sign-in (via foreign keys)
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