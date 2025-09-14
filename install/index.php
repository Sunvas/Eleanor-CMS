<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Library,
	Eleanor\Classes\EM,
	Eleanor\Classes\L10n,
	Eleanor\Classes\MySQL,
	Eleanor\Classes\Output,
	Eleanor\Classes\Template;

use const Eleanor\SITEDIR;
use function Eleanor\AwareInclude;

const
	JSON = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
	LOCK = __DIR__.'/install.lock',
	BASE = __DIR__.'/../';

require BASE.'cms/library/core.php';
require BASE.'cms/constants.php';//Just for VERSION

Library::$logs=BASE.'cms/logs/';

/** Проверка среды для возможности установки
 * @return array of errors */
function CheckEnv():array
{
	$errors=[];

	if(\file_exists(LOCK))
		return['LOCKED'];

	if(\version_compare(PHP_VERSION,'8.4','<'))
		$errors[]='LOW_PHP_VERSION';

	if(!\function_exists('mysqli_connect'))
		$errors[]='MYSQLI_MISSED';

	#Каталог с логами
	if(!\is_dir(Library::$logs))
		$errors['NOT_EXIST'][]=Library::$logs;
	elseif(!\is_writeable(Library::$logs))
		$errors['NOT_WRITABLE'][]=Library::$logs;

	#Проверка на запись robots.txt, конфига главной страницы, конфига для доступа к БД и константы
	foreach([BASE.'robots.txt',BASE.'cms/config/db.php',BASE.'cms/constants.php'] as $f)
		if(!\is_file($f))
			$errors['NOT_EXIST'][]=$f;
		elseif(!\is_writeable($f))
			$errors['NOT_WRITABLE'][]=$f;

	#Uploads, config
	foreach([BASE.'static/uploads/',BASE.'cms/config/',BASE.'cms/cache/',BASE.'cms/cache/storage/'] as $d)
		if(!\is_dir($d))
			$errors['NOT_EXIST'][]=$d;
		elseif(!\is_writeable($d))
			$errors['NOT_WRITABLE'][]=$d;

	return$errors;
}

/** Alias. Генерация nonce для скриптов. Они могут быть использованы повторно
 * @return string
 * @throws \Random\RandomException */
function Nonce():string
{
	return OutPut::Nonce();
}

/** Шаг 1: выбор языка системы */
function Step1():string
{global$T;
	if(($_SESSION['step'] ?? 0)===1 and \in_array($_POST['l10n'] ?? 0,['ru','en'],true))
	{
		$_SESSION['l10n']=$_POST['l10n'];
		return Step2();
	}

	$_SESSION=[
		'step'=>1
	];

	return$T('Step1');
}

/** Шаг 2: формальное лицензионное соглашение или ошибки окружения */
function Step2(?array$errors=null):string
{global$T;
	$errors??=CheckEnv();

	#Переход к следующему шагу
	if(($_SESSION['step'] ?? 0)===2 and !$errors and $_POST)
	{
		if(isset($_POST['agree']))
			return Step3();

		if(isset($_POST['back']))
			return Step1();
	}

	#Установка значений
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=2;

	return$errors ? $T('Problems',$errors) : $T('Step2');
}

/** Шаг 3: Настройки подключения к БД */
function Step3($errors=[]):string
{global$T;

	#Переход к следующему шагу
	if(($_SESSION['step'] ?? 0)===3 and $_POST)
	{
		$next=true;

		foreach(['host','user','pass','db', 'title','description','hcaptcha','hsecret','bot_name','bot_key', 'username','password','password2'] as $f)
			if(\is_string($_POST[$f] ?? 0))
				$_SESSION[$f]=$_POST[$f];
			else
				$next=false;

		$_SESSION['multilang']=isset($_POST['multilang']);
		$_SESSION['l10ns']=\is_array($_POST['l10ns'] ?? 0) && $_SESSION['multilang']
			? \array_filter($_POST['l10ns'],fn($item)=>\in_array($item,['ru','en']))
			: null;

		if($next and isset($_POST['next']))
			return Step4();

		if(isset($_POST['back']))
			return Step2();
	}

	#Установка значений
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=3;

	return$T('Step3',
		host:$_SESSION['host'] ?? 'localhost',
		user:$_SESSION['user'] ?? '',
		pass:$_SESSION['pass'] ?? '',
		db:$_SESSION['db'] ?? '',

		multilang:$_SESSION['multilang'] ?? false,
		l10ns:$_SESSION['l10ns'] ?? [],

		title:$_SESSION['title'] ?? '',
		description:$_SESSION['description'] ?? '',
		hcaptcha:$_SESSION['hcaptcha'] ?? '',
		hsecret:$_SESSION['hsecret'] ?? '',
		bot_key:$_SESSION['bot_key'] ?? '',
		bot_name:$_SESSION['bot_name'] ?? '',

		username:$_SESSION['username'] ?? '',
		password:$_SESSION['password'] ?? '',
		password2:$_SESSION['password2'] ?? '',

		errors:$errors
	);
}

/** Шаг 4: Создание таблиц */
function Step4():string
{global$T;

	#Переход к следующему шагу
	if(($_SESSION['step'] ?? 0)===4)
	{
		if(isset($_POST['back']))
		{
			unset($_SESSION['step4-done']);
			return Step3();
		}

		if(isset($_SESSION['step4-done']))
			return Step5();
	}

	#Установка значений
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=4;

	try{
		$Db=new MySQL($_SESSION['host'],$_SESSION['user'],$_SESSION['pass'],$_SESSION['db']);
	}catch(EM){
		return Step3(['MYSQL_CONNECT']);
	}

	if($Db->server_version<80000)
		return Step3(['MYSQL_LOW']);

	$status=[];
	$tables=AwareInclude(__DIR__.'/data/tables.php',['Db'=>$Db]);

	foreach($tables as $k=>$v)
	{
		$err=false;

		try{
			$Db->Query($v);
		}catch(EM$E){
			$err=(string)$E;
		}

		if(!is_int($k))
			$status[$k]=$err;
	}

	$ok=!array_any($status,fn($item)=>\is_string($item));

	if($ok)
		$_SESSION['step4-done']=true;

	return$T('Step4',$status,$ok);
}

/** Шаг 5: Запись значений */
function Step5():string
{global$T;

	#Переход к следующему шагу
	if(($_SESSION['step'] ?? 0)===5)
	{
		if(isset($_POST['back']))
		{
			unset($_SESSION['step4-done'],$_SESSION['step5-done']);
			return Step3();
		}

		if(isset($_SESSION['step5-done']))
			return Step6();
	}

	#Установка значений
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=5;

	try{
		$Db=new MySQL($_SESSION['host'],$_SESSION['user'],$_SESSION['pass'],$_SESSION['db']);
	}catch(EM){
		return Step3(['MYSQL_CONNECT']);
	}

	$status=[];
	$insert=AwareInclude(__DIR__.'/data/insert.php',['Db'=>$Db]);

	foreach($insert as $k=>$v)
	{
		$err=false;

		try{
			$Db->Query($v);
		}catch(EM$E){
			$err=(string)$E;
		}

		if(!is_int($k))
			$status[$k]=$err;
	}

	$ok=!array_any($status,fn($item)=>\is_string($item));

	if($ok)
	{
		$Db->Insert('users',[
			'id'=>1,
			'name'=>$_SESSION['username'],
			'groups'=>'[1]',
			'password_hash'=>\password_hash($_SESSION['password'],\PASSWORD_DEFAULT),
			'avatar'=>'a'
		]);

		$status['users']=false;
		$_SESSION['step5-done']=true;
	}

	return$T('Step5',$status,$ok);
}

/** Шаг 7: Запись значений */
function Step6():string
{global$T;
	$_SESSION['finished']??=true;
	$sitedir=\dirname(SITEDIR).'/';

	if($_SESSION['finished'])
	{
		#config of connection to database
		$db=\var_export($_SESSION['db'],true);
		$host=\var_export($_SESSION['host'],true);
		$user=\var_export($_SESSION['user'],true);
		$pass=\var_export($_SESSION['pass'],true);

		$config_db=<<<PHP
<?php
return[
	'host'=>{$host},
	'user'=>{$user},
	'pass'=>{$pass},
	'db'=>{$db},
];
PHP;
		\file_put_contents(BASE.'cms/config/db.php',$config_db);

		#robots.txt
		$protocol=\Eleanor\PROTOCOL;
		$domain=\Eleanor\DOMAIN;
		$config_robots=<<<TEXT
User-agent: *
Sitemap: {$protocol}{$domain}{$sitedir}sitemap.xml
TEXT;
		\file_put_contents(BASE.'robots.txt',$config_robots);

		#system constants
		$l10ns=\is_array($_SESSION['l10ns']) ? \join(',',\array_map(fn($item)=>\var_export($item,true),$_SESSION['l10ns'])) : '';
		$config=\file_get_contents(BASE.'cms/constants.php');
		$config=\preg_replace('#L10N=[^,]+#',"L10N='{$_SESSION['l10n']}'",$config);
		$config=\preg_replace('#L10NS=[^;]+#',$_SESSION['l10ns']===null ? 'L10NS=null' : "L10NS=[{$l10ns}]",$config);
		\file_put_contents(BASE.'cms/constants.php',$config);

		#system config
		$system=\json_encode([
			'maintenance'=>false,
			'captcha'=>false,
			'bot_name'=>$_SESSION['bot_name'],
			'bot_key'=>$_SESSION['bot_key'],
			'hcaptcha'=>$_SESSION['hcaptcha'],
			'hcaptcha_secret'=>$_SESSION['hsecret'],
		],JSON);
		\file_put_contents(BASE.'cms/config/system.json',$system);

		#config of main page
		$mono=$_SESSION['l10ns']===null;
		$mainpage=\json_encode([
			'name'=>$mono ? $_SESSION['title'] : [''=>$_SESSION['title']],
			'title'=>$mono ? '' : [''=>''],
			'description'=>$mono ? $_SESSION['description'] : [''=>$_SESSION['description']],
		],JSON);
		\file_put_contents(BASE.'cms/config/site.json',$mainpage);

		#locking the installer to prevent another installation
		\file_put_contents(__DIR__.'/install.lock',1);
	}

	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=6;

	#Erasing session
	foreach(['host','user','pass','db', 'title','description','hcaptcha','hsecret','bot_name','bot_key', 'username','password','password2'] as $f)
		unset($_SESSION[$f]);

	return$T('Step6',$sitedir);
}

\session_start([
	'name'=>'INSTALL',
	'use_cookies'=>true,
	'use_only_cookies'=>true,
	'cookie_path'=>SITEDIR,
	'cookie_httponly'=>true,
	'cookie_secure'=>($_SERVER['HTTPS'] ?? '')=='on'
]);

#Reset installation if lock file not found
if(isset($_SESSION['step']) and $_SESSION['step']===6 and !\is_file(LOCK))
	$_SESSION=[];

$T=new Template(__DIR__.'/template/install.php');
$out=match($_SESSION['step'] ?? 1){
	6=>Step6(),
	5=>Step5(),
	4=>Step4(),
	3=>Step3(),
	2=>Step2(),
	default=>Step1(),
};

$isa=\is_array($out);
Output::SendHeaders($isa ? Output::JSON : Output::HTML,200,0);
echo $isa ? \json_encode($out,JSON) : $out;