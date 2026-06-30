<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\{Basic,Assign,Library};
use Eleanor\Classes\{E, Cache, MySQL, Template};
use CMS\Interfaces\External;

use function Eleanor\{Autoloader, BugFileLine};

const
	/** Absolute path to the CMS directory with trailing directory separator */
	CMS = __DIR__.\DIRECTORY_SEPARATOR,

	/** Default json_encode() flags preserving Unicode characters and slashes */
	JSON = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;

require CMS.'constants.php';
require CMS.'library/core.php';

Library::$logs=CMS.'logs/';

/** Load configuration from file.
 * @param string $file Filename without extension
 * @param string $dir Path to directory containing configuration files
 * @return ?array Configuration data or null when no config file exists */
function Config(string$file,string$dir=CMS.'config/'):?array
{
	$php=$dir.$file.'.php';
	$json=$dir.$file.'.json';

	if(!\is_file($json) and !\is_file($php))
		return null;

	# PHP config file can access JSON values through the $config variable
	$config=\is_file($json) ? \json_decode(\file_get_contents($json),true) : [];
	$config2=\is_file($php) ? (array)require$php : [];

	return $config2+$config;
}

/** Set HTTP cookie
 * @param string $name Cookie name
 * @param string $value Cookie value
 * @param int $ttl Cookie lifetime in seconds. If set to 0, a session cookie will be created and automatically removed
 *     when the browser session ends.
 * @param bool $strict Whether to use Strict SameSite policy instead of Lax
 * @return bool True on success or false on failure */
function SetCookie(string$name,string$value='',int$ttl=31536000,bool$strict=true):bool
{
	if($value=='')
		$ttl=0;
	elseif($ttl)
		$ttl+=\time();

	return \setcookie($name,$value,$ttl,[
		'expires'=>$ttl,
		'path'=>\Eleanor\SITEDIR,
		'domain'=>\Eleanor\DOMAIN,
		'secure'=>\Eleanor\PROTOCOL=='https://',
		'httponly'=>true,
		'samesite'=>$strict ? 'Strict' : 'Lax',
	]);
}

/** Send HTTP redirect and terminate script execution
 * @param string $to Root-relative path or path relative to site directory
 * @param int $code HTTP redirect status code (3xx)
 * @param string $hash URL fragment without leading '#'
 * @return never */
function Redirect(string$to,int$code=301,string$hash=''):never
{
	if(!\str_starts_with($to,'/'))
		$to=\Eleanor\SITEDIR.$to;

	if($hash)
		$to.='#'.$hash;

	\header('Cache-Control: no-store');
	\header('Location: '.\rtrim(\html_entity_decode($to),'&?'),true,$code);
	die;
}

/** Convert traversable items to array after applying callback to each item
 * @param \Traversable $items Source items
 * @param \Closure $callback Item transformation callback
 * @param bool $keys Whether to preserve original keys
 * @return array */
function Iterator2Array(\Traversable$items,\Closure$callback,bool$keys=false):array
{
	$F=function()use($items,$callback){
		foreach($items as $k=>$item)
			yield $k=>$callback($item);
	};

	return \iterator_to_array($F(),$keys);
}

/** Fetch a single value or row from the MySQL result and free the result set.
 * @param \mysqli_result $R MySQL result set
 * @param bool $column Whether to fetch only the first column instead of the full row
 * @return mixed */
function SingleFetch(\mysqli_result$R,bool$column=false):mixed
{
	return $column
		? [
			$R->fetch_column(),
			$R->free()
		][0]
		: [
			$R->fetch_assoc() ?: null,
			$R->free()
		][0];
}

/** Core CMS runtime class.
 * Stores shared runtime data and provides access to system components.
 * Dynamic properties usually contain either loaded units or objects of classes with matching names. */
#[\AllowDynamicProperties]
class CMS extends Library
{
	static bool
		/** @var bool $json Whether request expects JSON response */
		$json=false,

		/** @var bool $put Whether request method is PUT */
		$put=false,

		/** @var bool $post Whether request method is POST */
		$post=false,

		/** @var bool $delete Whether request method is DELETE */
		$delete=false;

	/** @var ?int Current authorization (a11n) ID */
	static ?int $a11n=null;

	/** @var Authorization|Assign|null $A Authorization object.
	 * Must be initialized only by system entrypoints: index.php or admin.php */
	static Authorization|Assign|null $A;

	/** @var Permissions|Assign|null $P Permissions object.
	 * User permissions loaded from the `groups` table */
	static Permissions|Assign|null $P;

	/** @var MySQL|Assign|null $Db Database connection object */
	static MySQL|Assign|null $Db;

	/** @var ?\ArrayObject Configuration storage object with automatic lazy loading and caching */
	static ?\ArrayObject $config;

	/** @var Cache|Assign|null $Cache Cache handler object */
	static Cache|Assign|null $Cache;

	/** @var Template|Assign|null $T Template engine object */
	static Template|Assign|null $T;

	/** @var string Client IP address in binary \inet_pton() format, or "\0" when unavailable */
	static string $ip;

	/** Lazily load and return a unit or class object by property name.
	 * @throws E */
	function __get(string$n):mixed
	{
		$unit=__DIR__."/units/{$n}.php";

		if(\is_file($unit))
			return $this->$n=include$unit;

		return parent::__get($n);
	}

	/** Create and return an object instance by class name.
	 * Uses registered factory if available; otherwise attempts to load the class file from the classes' directory.
	 * @param string $n Class name
	 * @param string $dir Base directory for class lookup
	 * @param array $params Factory arguments
	 * @throws E */
	function __invoke(string$n,string$dir=__DIR__,array$params=[]):mixed
	{
		return parent::__invoke($n,$dir,$params);
	}
}

class Output extends \Eleanor\Classes\Output
{
	/** @const X-Powered-CMS header. Feel free to remove it. */
	protected const string POWERED='X-Powered-CMS: Eleanor CMS https://eleanor-cms.com';
}

class L10n extends \Eleanor\Classes\L10n
{
	/** Get localized value from existing l10n data
	 * @param array $l10n Localization values indexed by language code
	 * @param mixed $d Default value returned when localization is not found
	 * @param string $f Fallback language code
	 * @return mixed */
	static function Item(array$l10n,mixed$d=null,string$f=L10N):mixed
	{
		return parent::Item($l10n,$d,$f);
	}
}

/** Configuration storage with automatic lazy loading and caching */
CMS::$config=new class extends \ArrayObject
{
	/** Return configuration by key, loading it automatically if needed
	 * @param mixed $key Configuration name
	 * @throws E
	 * @return mixed */
	function offsetGet(mixed$key):mixed
	{
		if($this->offsetExists($key))
			return parent::offsetGet($key);

		$config=Config($key);

		if($config!==null)
		{
			$this->offsetSet($key,$config);
			return $config;
		}

		throw new E("Unable to load config '{$key}'",E::PHP,...BugFileLine($this));
	}
};

CMS::$ip=\filter_var($_SERVER['REMOTE_ADDR'] ?? 0,\FILTER_VALIDATE_IP) ? \inet_pton($_SERVER['REMOTE_ADDR']) : "\0";
CMS::$json=\getenv('HTTP_ACCEPT')==='application/json';

switch($_SERVER['REQUEST_METHOD'] ?? '')
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

# Avoid Content-Type checks here: requiring this header may conflict with CORS Access-Control-Allow-Headers.
if(!$_POST && !$_FILES && CMS::$post)
{
	$json=\json_decode(\file_get_contents('php://input'),true);

	if(\is_array($json))
	{
		CMS::$json=true;
		$_POST+=$json;
	}
}

# All errors will be returned in JSON mode
if(CMS::$json)
	CMS::$bsodtype=Output::JSON;

Assign::Bind(CMS::$Db,fn()=>new MySQL(
	CMS::$config['db']['host'],
	CMS::$config['db']['user'],
	CMS::$config['db']['pass'],
	CMS::$config['db']['db'],
));
Assign::Bind(CMS::$Cache,fn()=>new Cache(CMS.'cache'));

if(!CMS::$cli)
{
	Assign::Bind(CMS::$T,fn(...$a)=>new class(...$a) extends Template {
		/** @var bool Whether cron.php (background tasks) should be run */
		private(set) bool $cron {
			get {
				if(!isset($this->cron))
				{
					$task=CMS::$Cache->Get('cron');# Timestamp of the next scheduled run
					$this->cron=$task===null || $task<=\time();
				}

				return $this->cron;
			}
		}
	});
	Assign::Bind(CMS::$P,fn()=>Permissions());
}

/** Manages user authorization (a11n), logout, and user switching. User authentication is not performed here. */
class Authorization extends Basic
{
	/** @var int Current user ID (0 means guest user) */
	protected(set) int$current;

	/** @var array User IDs available for quick switching */
	protected(set) array$available;

	/** @param string $table Table linking users with a11n records
	 * @param int $current Preferred current user ID. If unavailable, the first available user will be selected.
	 * @param ?External $Ext External authorization provider object. See cms/external.php
	 * @throws \Throwable */
	function __construct(readonly string$table,int$current=0,readonly ?External$Ext=null)
	{
		$available=$Ext?->Get() ?? [];
		$hidden=[];# User IDs whose public activity timestamp should not be updated

		if(CMS::$a11n)
		{
			$failed=[];

			$R=CMS::$Db->Execute(<<<SQL
SELECT `user_id`, `marker`, `way` FROM `{$table}` WHERE `a11n_id`=?
SQL ,[CMS::$a11n]);
			foreach($R as $a)
			{
				$id=(int)$a['user_id'];

				if(\in_array($id,$available))
					continue;

				# Do not update public activity for admin-panel sign-ins
				if($a['way']=='admin-panel')
					$hidden[]=$id;

				$marker=$this->Marker($id);
				$marker=\is_string($_COOKIE[$marker] ?? 0) ? $_COOKIE[$marker] : '';

				# Temporary sign-ins require an additional per-user marker cookie
				if($a['marker']!=="\0" and \bin2hex($a['marker'])!==$marker)
					$failed[]=$id;
				else
					$available[]=$id;
			}
			$R->free();

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

		# Update user's last activity
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

	/** Get the marker cookie name for the user
	 * @param int $id User ID
	 * @return string */
	protected function Marker(int$id):string
	{
		return A11N_COOKIE."-{$this->table}-".$id;
	}

	/** Log out a user and optionally switch to another authorized user.
	 * @param ?int $id User ID to log out. If null, the current user is logged out.
	 * @param int $next User ID to switch to after logout. Only applies when logging out the current user.
	 * @throws \Throwable */
	function SignOut(?int$id=null,int$next=0):void
	{
		$id??=$this->current;

		CMS::$Db->Delete($this->table,"`user_id`={$id} AND `a11n_id`=".CMS::$a11n);
		$this->Ext?->SignOut($id);

		if($id==$this->current)
			$this->current=\in_array($next,$this->available)
				? \array_splice($this->available,\array_search($next,$this->available),1)[0]
				: (\array_shift($this->available) ?? 0);

		elseif(\in_array($id,$this->available))
			\array_splice($this->available,\array_search($id,$this->available),1);
	}

	/** Register successful sign-in and set related cookies
	 * @param int $id User ID
	 * @param bool $temp Whether to create a temporary sign-in bound to a session cookie
	 * @param array $extra Additional session fields stored in the database. Array keys represent table fields and
	 *     values represent their contents.
	 * @throws \Throwable */
	function SignIn(int$id,bool$temp=false,array$extra=[]):void
	{
		A11N();

		$marker=$temp ? \random_bytes(5) : "\0";

		CMS::$Db->Replace($this->table,['user_id'=>$id,'a11n_id'=>CMS::$a11n,'marker'=>$marker]+$extra);

		if($temp)
			SetCookie($this->Marker($id),\bin2hex($marker),0);

		$this->Ext?->SignIn($id);

		if($this->current and $this->current!=$id and !\in_array($id,$this->available))
			$this->available[]=$id;
		elseif(!$this->current)
			$this->current=$id;
	}
}

/** Group-based user permission system. */
class Permissions extends Basic
{
	/** @param array $groups User groups where the first element is the primary group
	 * @param array $rights Permission definitions in one of the following formats:
	 *     - right name => group ID => permission value
	 *     - right name => [group IDs]
	 *       The array must be a valid list array (array_is_list() === true).
	 *       This format means the right is granted to the specified groups.
	 * @param array $roles List of assigned roles */
	function __construct(readonly array$groups,readonly array$rights,readonly array$roles=[]){}

	/** Get permission value for current user groups.
	 * Depending on permission definition format, returns:
	 *     - bool when permission is defined as a list of allowed group IDs
	 *     - array when permission contains group-specific values
	 * Guests always receive an empty array.
	 * @param string $n Permission name
	 * @throws E
	 * @return array|bool */
	function __get(string$n):array|bool
	{
		# Guests have no permissions
		if(!$this->rights)
			return[];

		if(!\is_array($this->rights[$n] ?? 0))
			throw new E('Reading invalid right: '.$n,E::PHP,...BugFileLine($this));

		if(\array_is_list($this->rights[$n]))
			return (bool)\array_intersect($this->rights[$n],$this->groups);

		$result=[];

		foreach($this->groups as $g)
			if(isset($this->rights[$n][$g]))
				$result[]=$this->rights[$n][$g];

		return $result;
	}
}

/** Get user permissions.
 * @param ?int $id User ID.
 *     If null, permissions of the current user are returned.
 * @return Permissions
 * @throws \Throwable */
function Permissions(?int$id=null):Permissions
{
	$groups=$rights=$roles=[];
	$id??=CMS::$A->current;

	if($id)
	{
		$R=CMS::$Db->Query(<<<SQL
SELECT `groups` FROM `users` WHERE `id`=$id LIMIT 1
SQL );
		if($R->num_rows>0)
			$groups=\json_decode($R->fetch_column(),true) ?? [];
		$R->free();
	}

	if($groups)
	{
		$multi=L10NS!==null;

		$R=CMS::$Db->Query('SELECT * FROM `groups` WHERE `id`'.CMS::$Db->In($groups));
		foreach($R as $a)
		{
			$gid=(int)$a['id'];

			if($a['roles'])
				\array_push($roles,...\explode(',',$a['roles']));

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			foreach(\array_slice($a,2) as $right=>$v)# Skip id and roles
				$rights[$right][$gid]=\is_string($v) && \ctype_digit($v) ? (int)$v : $v;
		}
		$R->free();
	}

	return new Permissions($groups,$rights,\array_unique($roles));
}

/** Initialize current authorization ID.
 * @throws \Throwable */
function A11N():void
{
	if(CMS::$a11n!==null)
		return;

	$bytes=\random_bytes(7);
	$id=CMS::$Db->Insert('a11n',\compact('bytes'));

	# If the session ID counter approaches the column limit, clear the session table.
	# This logs out all users and may be especially inconvenient for recent sign-ins, but should happen very rarely.
	if($id>A11N_TRUNCATE_AFTER)
	{
		CMS::$Db->Query('DELETE FROM `a11n`');
		CMS::$Db->Query('ALTER TABLE `a11n` AUTO_INCREMENT=1');

		$id=CMS::$Db->Insert('a11n',\compact('bytes'));
	}

	CMS::$a11n=$id;

	SetCookie(A11N_COOKIE,\bin2hex($bytes).\dechex($id));
}

/** Get user data from database table.
 * @param array|string $keys Field name or list of fields to retrieve
 * @param ?int $id User ID.
 *     If null, the current user ID is used.
 * @param string $table User table name
 * @return string|array|null
 *     Returns a field value when $keys is a string,
 *     otherwise returns an associative array of field values.
 * @throws \Throwable */
function GetUserData(array|string$keys,?int$id=null,string$table='users'):array|string|null
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

		$R=CMS::$Db->Query(<<<SQL
SELECT `{$fields}` FROM `{$table}` WHERE `id`={$id} LIMIT 1
SQL );
		if($R->num_rows<1)
		{
			$R->free();
			throw new E('User not found: '.$id,E::SYSTEM,input:$id);
		}

		$data[$id]+=SingleFetch($R);
	}

	foreach($keys as $k)
		$result[$k]=$data[$id][$k];

	return $isa ? $result : \array_first($result);
}

/** Alias for Output::Nonce().
 * Generate CSP nonce for scripts. The same nonce may be reused for multiple script tags.
 * @return string
 * @throws \Random\RandomException */
function Nonce():string
{
	return Output::Nonce();
}

/** Alias for Output::Return304().
 * Attempt to send HTTP 304 Not Modified response when the client cache is still valid. */
function Return304(...$a):bool
{
	return Output::Return304(...$a);
}

/** Alias for Output::Link(). Add resource hint to the HTTP Link header.
 * @param string $url Resource URL
 * @param string $rel Link relation type. Usually "preconnect" or "preload". */
function Link(...$a):void
{
	Output::Link(...$a);
}

/** Send HTML response and terminate script execution.
 * @param string $output HTML page content
 * @param int $code HTTP status code
 * @param string|int $cache Cache control parameter:
 *     - int: cache lifetime in seconds
 *     - string: ETag content
 * @return never */
function HTML(string$output,...$a):never
{
	if($output=='')
		\header('Cache-Control: no-store',true,204);
	else
		Output::SendHeaders(Output::HTML,...$a);

	die($output);
}

/** Send JSON response and terminate script execution.
 * @param ?array $json Response data
 * @param int $code HTTP status code
 * @param string|int $cache Cache control parameter:
 *     - int: cache lifetime in seconds
 *     - string: ETag content
 * @return never */
function JSON(?array$json,...$a):never
{
	Output::SendHeaders(Output::JSON,...$a);

	die(\json_encode($json,JSON));
}

\spl_autoload_register(fn(string$c)=>Autoloader($c,__DIR__,__NAMESPACE__));

(function(){
	if(CMS::$cli)
		return;

	$a11n=\is_string($_COOKIE[A11N_COOKIE] ?? 0) ? $_COOKIE[A11N_COOKIE] : '';

	# The session key consists of 7 random bytes and a sequence number in hex format
	if(!\ctype_xdigit($a11n) or \strlen($a11n)<15)
		return;

	[$bytes,$id]=\str_split($a11n,14);

	$R=CMS::$Db->Query(<<<SQL
SELECT IF(`generated`<NOW() - INTERVAL 1 WEEK,1,0) `regen`, IF(`generated`<NOW() - INTERVAL 1 YEAR,1,0) `obsolete`
FROM `a11n`
WHERE `id`=0x{$id} AND `bytes`=0x{$bytes}
LIMIT 1
SQL );

	$sess=SingleFetch($R);

	if($sess===null)
		return;

	$upd=[];

	# Regenerate old session key
	if($sess['regen'])
	{
		$upd['generated']=fn()=>'NOW()';
		$upd['bytes']=\random_bytes(7);

		SetCookie(A11N_COOKIE,\bin2hex($upd['bytes']).$id);
	}

	$id=\hexdec($id);

	# If the session is older than one year, clear all sign-ins (via foreign keys)
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