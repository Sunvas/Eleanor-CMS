<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Library,
	Eleanor\Classes\Cache;

const
	JSON = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
	LOCK = __DIR__.'/../install.lock',
	BASE = __DIR__.'/../../',
	SUPPORTED_L10NS = ['ru','en'],
	USERNAME_LENGTH = 25,
	REQUIRED_PHP_VERSION = 8.5;

require BASE.'cms/library/core.php';

Library::$logs=BASE.'cms/logs/';

/** Identifies the file modified by a function */
#[\Attribute(\Attribute::TARGET_FUNCTION)]
class ModifiedFile {
	function __construct(
		readonly string $filepath
	){}

	/** Get the file modified by the specified function */
	static function Get(\Closure$closure):?string
	{
		$A=new \ReflectionFunction($closure)->getAttributes(static::class)[0] ?? null;
		return $A?->newInstance()->filepath;
	}
}

/** Check whether the environment is ready for installation
 * @return array of errors */
function CheckEnv():array
{
	$errors=[];

	if(\file_exists(LOCK))
		return ['LOCKED'];

	if(\version_compare(\PHP_VERSION,REQUIRED_PHP_VERSION,'<'))
		$errors[]='LOW_PHP_VERSION';

	if(!\function_exists('mysqli_connect'))
		$errors[]='MYSQLI_MISSED';

	# Logs directory
	if(!\is_dir(Library::$logs))
		$errors['NOT_EXIST'][]=Library::$logs;
	elseif(!\is_writeable(Library::$logs))
		$errors['NOT_WRITABLE'][]=Library::$logs;

	$base=\realpath(BASE);

	# Check write access to robots.txt, database config, and constants
	foreach([$base.'/robots.txt',$base.'/cms/config/db.php',$base.'/cms/constants.php'] as $f)
		if(!\is_file($f))
			$errors['NOT_EXIST'][]=$f;
		elseif(!\is_writeable($f))
			$errors['NOT_WRITABLE'][]=$f;

	# Writable directories
	foreach([$base.'/static/uploads/',$base.'/cms/config/',$base.'/cms/cache/'] as $d)
		if(!\is_dir($d))
			$errors['NOT_EXIST'][]=$d;
		elseif(!\is_writeable($d))
			$errors['NOT_WRITABLE'][]=$d;

	return $errors;
}

/** Write the database configuration file */
#[ModifiedFile('cms/config/db.php')]
function PutDbConfig(string$db,string$host,string$user,string$pass,?int$port=null):bool
{
	$d_host=\ini_get('mysqli.default_host');
	$d_user=\ini_get('mysqli.default_user');
	$d_pass=\ini_get('mysqli.default_pw');
	$d_port=\ini_get('mysqli.default_port');

	$db=\var_export($db,true);
	$host=\var_export($host==$d_host ? null : $host,true);
	$user=\var_export($user==$d_user ? null : $user,true);
	$pass=\var_export($pass==$d_pass ? null : $pass,true);
	$port=\var_export($port==$d_port ? null : $port,true);

	$config_db=<<<PHP
<?php
return[
	'db'=>$db,
	'host'=>$host,
	'user'=>$user,
	'pass'=>$pass,
	'port'=>$port,
];
PHP;
	return \file_put_contents(BASE.'cms/config/db.php',$config_db,\LOCK_EX)!==false;
}

/** Write the robots.txt file */
#[ModifiedFile('robots.txt')]
function PutRobotsTxt(string$sitedir):bool
{
	$protocol=\Eleanor\PROTOCOL;
	$domain=\Eleanor\DOMAIN;
	$config_robots=<<<TEXT
User-agent: *
Sitemap: $protocol$domain{$sitedir}sitemap.xml
TEXT;

	return \file_put_contents(BASE.'robots.txt',$config_robots,\LOCK_EX)!==false;
}

/** Update the system constants file */
#[ModifiedFile('cms/constants.php')]
function PutConstants(string$l10n,?array$l10ns):bool
{
	$l10ns2=\is_array($l10ns) ? \join(',',\array_map(fn($item)=>\var_export($item,true),$l10ns)) : '';

	$config=\file_get_contents(BASE.'cms/constants.php');
	$config=\preg_replace('#L10N=[^,]+#',"L10N='$l10n'",$config);
	$config=\preg_replace('#L10NS=[^;]+#',$l10ns===null ? 'L10NS=null' : "L10NS=[$l10ns2]",$config);

	return \file_put_contents(BASE.'cms/constants.php',$config,\LOCK_EX)!==false;
}

/** Write the system configuration file */
#[ModifiedFile('cms/config/system.json')]
function PutSystemConfig(string$sitekey,string$secret):bool
{
	$system=\json_encode([
		'maintenance'=>false,
		'captcha'=>false,
		'hcaptcha'=>$sitekey,
		'hcaptcha_secret'=>$secret,
	],JSON);
	return \file_put_contents(BASE.'cms/config/system.json',$system,\LOCK_EX)!==false;
}

/** Write the site configuration file */
#[ModifiedFile('cms/config/site.json')]
function PutSiteConfig(array|string$title,array|string$description):bool
{
	$json=\json_encode(\compact('title','description'),JSON);
	return \file_put_contents(BASE.'cms/config/site.json',$json,\LOCK_EX)!==false;
}

/** Write initial cache data */
function PutCache():void
{
	new Cache(BASE.'cms/cache')->Put('admin-panel','admin.php',0,true);
}

/** Lock the installer by creating the lock file */
#[ModifiedFile('install/install.lock')]
function LockInstaller():bool
{
	return \file_put_contents(LOCK,1,\LOCK_EX)!==false;
}

/** Delete unused localization files
 * @param array $l10ns Allowed localizations */
function DeleteUnusedL10n(array$l10ns):void
{
	$folders=[
		'admin-panel/l10n',
		'admin-panel/main/l10n',
		'admin-panel/sidebar/l10n',
		'admin-panel/static/l10n',
		'admin-panel/users/l10n',
		//'library/l10n',
		'user-area/l10n',
		'user-area/unit-account/l10n',
	];

	foreach($folders as $folder)
	{
		$folder=BASE."cms/$folder/";
		$files=\scandir($folder);

		if(!\is_array($files))
			continue;

		$files=array_filter($files,fn($item)=>\str_ends_with($item,'.php'));

		foreach($files as $file)
		{
			$filename=\strrchr($file,'.php',true);
			$l10n=\explode('-',$filename) |> array_last(...);

			if(!\in_array($l10n,$l10ns))
				\unlink($folder.$file);
		}
	}

	# Delete unused localized main page files
	foreach(\array_diff(['en','ru'],$l10ns) as $l10n)
		@\unlink(BASE."cms/units/main/mainpage-$l10n.json");
}