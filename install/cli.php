<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Library;
use Eleanor\Classes\{CLI,EM,MySQL};
use function Eleanor\AwareInclude;

const INSTALL=__DIR__.'/install.json';

require __DIR__.'/includes/common.php';

# Redirect to web installer when the script is called from the web
if(!Library::$cli)
{
	\header('Location: index.php',false,308);
	die;
}

function CLI():CLI
{
	return new CLI('Eleanor CMS CLI installer','CYAN')->reset("\n\n");
}

# If no command line argument is specified, display help information about the CLI installer
if(!isset($argv[1]) or $argv[1]==='help')
{
	$info=CLI()
		->Concat("Usage: ")->green("php $argv[0] <command> [config.json] [options]")
		->reset("\n\nAvailable commands:")
			->PURPLE("\n license")->reset("   Show license")
			->PURPLE("\n install")->reset("   Perform installation")
			->PURPLE("\n dry-run")->reset("   Validate configuration and requirements without making changes")
			->PURPLE("\n help")->reset("      Show usage information");

	if(isset($argv[1]))
		$info->reset("\n\nConfiguration:
 Values are read from the specified JSON file, or install.json by default.
 Use install.sample.json as a configuration template.
 Command-line options override corresponding configuration values.
 
Options:
  Override JSON values using --section-key=value.
  For example: ")->yellow("--mysql-host=server.local")->reset(" or ")->yellow("--site-title=\"My site\"")->reset(".
 
Examples:\n")->yellow(" php $argv[0] install
 php $argv[0] dry-run install.json
 php $argv[0] install install.json --mysql-host=server.local");
	else
		$info->reset("\n\nExample: ")->yellow("php $argv[0] install install.json --mysql-host=server.local");

	$info->reset->write();
	die;
}

/** Display the password length as asterisks */
function Asterisks(CLI $cli, int $len):void
{
	$cli->Concat(\str_repeat('*',$len))->write();
}

/** Read a password from the command line */
function ReadPassword(CLI $cli):string
{
	$stty=\stream_isatty(\STDIN) && \function_exists('system');

	if($stty)
	{
		\system('stty -echo 2>/dev/null',$code);
		$stty=$code===0;
	}

	$hidden=\rtrim((string)\fgets(\STDIN),"\r\n");

	if($stty)
	{
		Asterisks($cli,\strlen($hidden));

		$cli->PURPLE("Repeat password: ")->reset->write();
		$hidden2=\rtrim((string)\fgets(\STDIN),"\r\n");
		Asterisks($cli,\strlen($hidden2));

		\system('stty echo');

		if(\strcmp($hidden2,$hidden)!==0)
		{
			$cli->red("Passwords mismatch. Process stopped.")->reset->write();
			exit(1);
		}
	}

	return $hidden;
}

/** Handler for install and dry-run commands */
function Install(array$argv,bool$dry_run=false):never
{
	$err=" !";
	$cli=CLI();
	$errors=CheckEnv();

	if($errors)
	{
		$cli->Concat('Environment check ')->red("failed")->reset(":\n");

		if(\in_array('LOCKED',$errors))
			$cli->RED($err)->reset(' Installation is locked by ')->yellow(\realpath(LOCK))
				->reset("\n   Remove this file before reinstalling the system.\n");

		if(\in_array('LOW_PHP_VERSION',$errors))
			$cli->RED($err)->reset(' PHP ')->green(REQUIRED_PHP_VERSION)
				->reset(' or newer is required. Current version is ')->yellow(\PHP_VERSION)->reset(".\n");

		if(\in_array('MYSQLI_MISSED',$errors))
			$cli->RED($err)->reset(' The ')->yellow('mysqli')->reset(" extension is not installed or enabled.\n");

		if(isset($errors['NOT_EXIST']))
			$cli->RED($err)->reset(" Missing files or directories:\n   ")
				->yellow(join("\n   ",$errors['NOT_EXIST']))->reset(\PHP_EOL);

		if(isset($errors['NOT_WRITABLE']))
			$cli->RED($err)->reset(" Not writable files or directories:\n   ")
				->yellow(join("\n   ",$errors['NOT_WRITABLE']))->reset(\PHP_EOL);

		$cli->write(false);
		exit(1);
	}

	if($dry_run)
		$cli->Concat('Environment check ')->GREEN("passed")->reset('.')->write();

	$k=\count($argv);
	$options=[];

	# Processing options from command line
	while($k-->0)
		if(\str_starts_with($argv[$k],'--'))
		{
			$param=\ltrim($argv[$k],'-');
			[$key,$value]=\str_contains($param,'=') ? \explode('=',$param,2) : [$param,null];
			$options[$key]=$value;

			\array_splice($argv,$k,1);
		}

	$file=$argv ? \array_shift($argv) : INSTALL;
	$invalid=\array_diff(\array_keys($options),[
		'mysql-host','mysql-port','mysql-username','mysql-password','mysql-database',
		'admin-username','admin-password',
		'site-dir','site-title','site-description','site-l10n','site-l10ns',
		'hcaptcha-sitekey','hcaptcha-secret'
	]);

	if($argv or $invalid)
		$errors['UNKNOWN_OPTIONS']=\array_merge($argv,$invalid);

	if(!\is_file($file))
		$errors[]='CONFIG_NOT_EXISTS';
	elseif(!\is_readable($file))
		$errors[]='CONFIG_NOT_READABLE';
	else
	{
		$content=\file_get_contents($file);

		if(!json_validate($content))
			$errors[]='INVALID_CONFIG';
		else
		{
			$config=\json_decode($content,true);

			if(!\is_array($config))
			{
				$config=[];
				$errors[]='CONFIG_IS_NOT_ARRAY';
			}
		}
	}

	# Applying override options
	foreach($options as $key=>$value)
		if(\str_contains($key,'-'))
		{
			[$l1,$l2]=explode('-',$key,2);
			$config[$l1][$l2]=$value;
		}
		else
			$config[$key]=$value;

	# Checking mysql values
	if(!\is_array($config['mysql'] ?? 0))
		$errors[]='MYSQL_OMITTED';
	else
	{
		$cm=&$config['mysql'];
		$cm['host']??=\ini_get('mysqli.default_host') ?: null;
		$cm['port']??=\ini_get('mysqli.default_port') ?: null;
		$cm['username']??=\ini_get('mysqli.default_user') ?: null;
		$cm['password']??=\ini_get('mysqli.default_pw') ?: null;

		# PHP 8.6: migrate to pipe operator
		if(!\array_all([$cm['host'] ?? 0,$cm['username'] ?? 0,$cm['database'] ?? 0],fn($s)=>\is_string($s)))
			$errors[]='MYSQL_STRINGS';

		if(\is_string($cm['port'] ?? 0))
			$cm['port']=\intval($cm['port']);

		if(!\is_int($cm['port'] ?? '') or $cm['port']<1 or $cm['port']>65535)
			$errors[]='MYSQL_PORT';

		unset($cm);
	}

	# Admin username
	$ca=$config['admin'];

	if(!\is_string($ca['username'] ?? 0) or $ca['username']==='')
		$errors[]='ADMIN_USERNAME';
	elseif(\mb_strlen($ca['username'])>USERNAME_LENGTH)
		$errors[]='ADMIN_USERNAME_TOO_LONG';

	$config['site']??=[];
	$cs=&$config['site'];

	# Language checking
	$multi=\is_array($cs['l10ns'] ?? 0);
	$l10ns=null;

	if(!\is_string($cs['l10n'] ?? 0) or !\in_array($cs['l10n'],SUPPORTED_L10NS,true))
		$errors[]='SITE_L10N';
	elseif($multi)
	{
		$l10ns=\array_unique(\array_diff($cs['l10ns'],[$cs['l10n']]));
		$diff=\array_diff($l10ns,SUPPORTED_L10NS);

		if($diff)
			$errors['SITE_L10NS']=$diff;
		else
		{
			$stack=[$cs['l10n'],...$l10ns];

			foreach(['title','description'] as $field)
				if(\is_string($cs[$field] ?? 0))
					$cs[$field]=\array_fill_keys($stack,$cs[$field]);
				elseif(!\is_array($cs[$field]) or !\array_all($stack,fn($l10n)=>\is_string($cs[$field][$l10n] ?? 0)))
				{
					$errors[]='SITE_META';
					break;
				}
		}
	}
	elseif(!\array_all(['title','description'],fn($field)=>\is_string($cs[$field] ?? 0)))
		$errors[]='SITE_META';

	if(!\is_string($cs['dir'] ?? 0))
		$errors[]='SITE_DIR';

	# hCaptcha
	$config['hcaptcha']??=[];
	$config['hcaptcha']+=[
		'sitekey'=>'',
		'secret'=>''
	];

	if(!$errors)
	{
		# Type passwords manually
		if(!\is_string($config['mysql']['password'] ?? 0))
		{
			$cli->PURPLE("Input password for MySQL user '{$config['mysql']['username']}' (input may be hidden): ")->reset->write();

			$config['mysql']['password']=ReadPassword($cli);
		}

		if(!\is_string($config['admin']['password'] ?? 0))
		{
			$cli->PURPLE("Input password for administrator '{$config['admin']['username']}' (input may be hidden): ")->reset->write();

			$config['admin']['password']=ReadPassword($cli);
		}

		if(\strlen($config['admin']['password'])<10)
			$errors[]='ADMIN_PASSWORD';

		# MySQL connection checking
		try{
			$cm=$config['mysql'];
			$Db=new MySQL($cm['host'],$cm['username'],$cm['password'],$cm['database'],port:$cm['port']);

			if($Db->server_version<80000)
				$errors[]='MYSQL_LOW';
		}catch(EM$E){
			$errors['MYSQL_CONNECT']=$E->getMessage();
		}
	}

	if($errors)
	{
		$cli->Concat('Configuration validation ')->red("failed")->reset(":\n");

		# Options errors
		if(isset($errors['UNKNOWN_OPTIONS']))
			$cli->RED($err)->reset(' You have used unknown options: ')
				->yellow(join(',',$errors['UNKNOWN_OPTIONS']))->reset(\PHP_EOL);

		# Config errors
		if(\in_array('CONFIG_NOT_EXISTS',$errors))
			$cli->RED($err)->reset(' Configuration file ')->yellow($file)->reset(" not found.\n");
		elseif(\in_array('CONFIG_NOT_READABLE',$errors))
			$cli->RED($err)->reset(' Configuration file ')->yellow($file)->reset(" is not readable.\n");
		elseif(\in_array('INVALID_CONFIG',$errors) or \in_array('CONFIG_IS_NOT_ARRAY',$errors))
			$cli->RED($err)->reset(' Configuration file ')->yellow($file)->reset(" does not contain a valid JSON object.\n");

		# MySQL errors
		if(\in_array('MYSQL_OMITTED',$errors))
			$cli->RED($err)->reset(" Configuration for connection to MySQL is omitted.\n");
		elseif(\in_array('MYSQL_STRINGS',$errors))
			$cli->RED($err)->reset(' MySQL configuration values ')
				->yellow('host')->reset(', ')->yellow('username')->reset(' and ')->yellow('database')
				->reset(" must be strings.\n");
		elseif(\in_array('MYSQL_PORT',$errors))
			$cli->RED($err)->reset(' MySQL configuration ')->yellow('port')
				->reset(" must be a positive integer between 1 and 65535.\n");
		elseif(\in_array('MYSQL_LOW',$errors))
			$cli->RED($err)->reset(' The version of MySQL server is required to be at least ')->yellow('8.0')->reset(\PHP_EOL);
		elseif(isset($errors['MYSQL_CONNECT']))
			$cli->RED($err)->reset(' Unable to connect to the MySQL server: ')
				->yellow($errors['MYSQL_CONNECT'])->reset(\PHP_EOL);

		# Site errors
		if(\in_array('ADMIN_USERNAME',$errors))
			$cli->RED($err)->reset(" Administrator username is omitted.\n");
		elseif(\in_array('ADMIN_USERNAME_TOO_LONG',$errors))
			$cli->RED($err)->reset(' Administrator username is too long. Current limit is ')->yellow(USERNAME_LENGTH)->reset(" characters.\n");

		if(\in_array('ADMIN_PASSWORD',$errors))
			$cli->RED($err)->reset(" Administrator password must contain at least 10 characters.\n");

		if(\in_array('SITE_DIR',$errors))
			$cli->RED($err)->reset(" Site directory is omitted.\n");

		if(\in_array('SITE_L10N',$errors))
			$cli->RED($err)->reset(" Site localization (l10n) is omitted.\n");

		if(\is_array($errors['SITE_L10NS'] ?? 0))
			$cli->RED($err)->reset(' Unsupported localizations (l10ns):')->yellow(\join(',',$errors['SITE_L10NS']))->reset(\PHP_EOL);

		if(\in_array('SITE_META',$errors))
			$cli->RED($err)->reset(" Site title or description is omitted.\n");

		$cli->write(false);
		exit(1);
	}

	if($dry_run)
	{
		$cli->Concat('Configuration validation ')->GREEN("passed")->reset('.')->write();
		die;
	}

	$cli->yellow("\nDatabase structure:")->reset->write();
	$tables=AwareInclude(__DIR__.'/includes/tables.php',[
		'Db'=>$Db,
		'l10n'=>$cs['l10n'],
		'l10ns'=>$l10ns,
	]);

	foreach($tables as $table=>$query)
	{
		$err=false;

		try{
			$Db->Query($query);
		}catch(EM$E){
			$err=$E->getMessage();
		}

		if(!\is_string($table))
			continue;

		if($err)
		{
			$cli->Concat('[ ')->red('FAIL')->reset(" ] $table ")->yellow($err)->reset->write();
			$errors['TABLES'][]=$table;
		}
		else
			$cli->Concat('[  ')->green('OK')->reset('  ] '.$table)->reset->write();
	}

	if(!$errors)
	{
		$cli->yellow("\nDatabase values:")->reset->write();
		$insert=AwareInclude(__DIR__.'/includes/insert.php',[
			'Db'=>$Db,
			'l10n'=>$cs['l10n'],
			'l10ns'=>$l10ns,
		]);

		foreach($insert as $key=>$query)
		{
			$err=false;

			try{
				if(\is_string($query))
					$Db->Query($query);
				elseif($query instanceof \Closure)
					$query();
			}catch(EM$E){
				$err=(string)$E;
			}

			if(!\is_string($key))
				continue;

			if($err)
			{
				$cli->Concat('[ ')->red('FAIL')->reset(" ] $key ")->yellow($err)->reset->write();
				$errors['INSERT'][]=$key;
			}
			else
				$cli->Concat('[  ')->green('OK')->reset('  ] '.$key)->reset->write();
		}

		if(!$errors)
		{
			try{
				$Db->Insert('users',[
					'id'=>1,
					'name'=>$config['admin']['username'],
					'groups'=>'[1]',
					'password_hash'=>\password_hash($config['admin']['password'],\PASSWORD_DEFAULT),
					'avatar'=>'a'
				]);
				$cli->Concat('[  ')->green('OK')->reset('  ] Administrator')->reset->write();
			}catch(EM$E){
				$err=(string)$E;
				$errors[]='ADMIN';

				$cli->Concat('[ ')->red('FAIL')->reset(' ] Administrator ')->yellow($err)->reset->write();
			}
		}
	}

	if(!$errors)
	{
		$cli->yellow("\nWriting files:")->reset->write();
		$items=[
			# Database connection configuration
			fn()=>[PutDbConfig(...),PutDbConfig($cm['database'],$cm['host'],$cm['username'],$cm['password'],$cm['port'])],

			# robots.txt
			fn()=>[PutRobotsTxt(...),PutRobotsTxt($cs['dir'])],

			# Constants
			fn()=>[PutConstants(...),PutConstants($cs['l10n'],$l10ns)],

			# System config
			fn()=>[PutSystemConfig(...),PutSystemConfig($config['hcaptcha']['sitekey'],$config['hcaptcha']['secret'])],

			# Main page config
			fn()=>[PutSiteConfig(...),PutSiteConfig($cs['title'],$cs['description'])],
		];

		foreach($items as $item)
		{
			[$F,$ok]=$item();
			$file=ModifiedFile::Get($F);

			if($ok)
				$cli->Concat('[  ')->green('OK')->reset('  ] '.$file)->reset->write();
			else
			{
				$cli->Concat('[ ')->red('FAIL')->reset(' ] '.$file)->write();
				$errors['FILES'][]=$file;
			}
		}
	}

	if(!$errors)
	{
		PutCache();

		# Deleting unused l10n files
		DeleteUnusedL10n([$cs['l10n'],...($l10ns ?? [])]);

		# Lock the installer to prevent another installation
		$ok=LockInstaller();
		$file=ModifiedFile::Get(LockInstaller(...));

		if($ok)
			$cli->Concat('[  ')->green('OK')->reset('  ] '.$file)->reset->write();
		else
		{
			$cli->Concat('[ ')->red('FAIL')->reset(' ] '.$file)->write();
			$errors['FILES'][]=$file;
		}
	}

	if($errors)
	{
		$cli->RED("\nInstallation failed. ")->reset('See details above.')->write();
		exit(1);
	}

	$cli->green("\nCongratulations!\n")
		->cyan('Eleanor CMS installation completed successfully')
		->reset("\n\nEnable Clean URLs by routing non-existent requests to index.php.\nThis can usually be configured in the hosting control panel or web server configuration.\nAn nginx example is available in ")
		->yellow('cms/library/classes/uri.php')->reset(', lines ')->yellow('12–23')
		->reset(".\n\nThe installer is blocked by the \n ")
		->yellow(\realpath(LOCK))
		->reset("\nDelete this file only before reinstalling the system.\n\nDelete the ")
		->yellow('install')
		->reset(" directory together with all its contents.\nFor additional protection, rename ")
		->yellow('admin.php')
		->reset(' to a non-obvious filename.')
		->write();

	die;
}

/** Handler for unknown commands */
function UnknownCommand(string$command):never
{
	new CLI('Unknown command: '.$command,'RED')->reset->write();
	exit(1);
}

/** Handler for license command */
function License():never
{
	$year=\idate('Y');

	new CLI('MIT License','CYAN')->reset("\n\n")
		->green("TL;DR: Do whatever the fuck you want!")->reset("\n")
		->yellow("Copyright (c) $year Alexander Sunvas https://sunvas.online")
		->reset(<<<TEXT
\n\nPermission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
\nThe above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
\nTHE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
TEXT )->write();

	die;
}

match($argv[1])
{
	'install'=>Install(\array_slice($argv,2)),
	'dry-run'=>Install(\array_slice($argv,2),true),
	'license'=>License(),
	default=>UnknownCommand($argv[1]),
};