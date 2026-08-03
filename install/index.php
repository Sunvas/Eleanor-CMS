<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Classes\{EM,L10n,MySQL,Output,Template};
use const Eleanor\SITEDIR;
use function Eleanor\AwareInclude;

require __DIR__.'/includes/common.php';

/** Alias. Generate script nonce. It can be reused
 * @return string
 * @throws \Random\RandomException */
function Nonce():string
{
	return Output::Nonce();
}

/** Alias */
function Link(...$a):void
{
	Output::Link(...$a);
}

/** Step 1: select system language */
function Step1():string
{global$T;
	if($_SESSION['step']===1 and \in_array($_POST['l10n'] ?? 0,['ru','en'],true))
	{
		$_SESSION['l10n']=$_POST['l10n'];
		return Step2();
	}

	if(!$_POST)
		$_SESSION=[];

	$_SESSION['step']=1;

	return $T('Step1');
}

/** Step 2: license agreement or environment errors */
function Step2(?array$errors=null):string
{global$T;
	$errors??=CheckEnv();

	# Continue to the next step
	if($_SESSION['step']===2 and !$errors and $_POST)
	{
		if(isset($_POST['agree']))
			return Step3();

		if(isset($_POST['back']))
			return Step1();
	}

	# Set values
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=2;

	return $errors ? $T('Problems',$errors) : $T('Step2');
}

/** Step 3: database connection settings */
function Step3(array$errors=[]):string
{global$T;
	# Continue to the next step
	if($_SESSION['step']===3 and $_POST)
	{
		$next=true;

		foreach(['host','user','pass','db', 'title','description','hcaptcha','hsecret', 'username','password','password2'] as $f)
			if(\is_string($_POST[$f] ?? 0))
				$_SESSION[$f]=$_POST[$f];
			else
				$next=false;

		# PHP 8.6: migrate to pipe operator
		$_SESSION['multilang']=isset($_POST['multilang']);
		$_SESSION['l10ns']=\is_array($_POST['l10ns'] ?? 0) && $_SESSION['multilang']
			? \array_unique(\array_intersect(\array_diff($_POST['l10ns'],[$_SESSION['l10n']]),SUPPORTED_L10NS))
			: null;

		if($_SESSION['password']!==$_SESSION['password2'])
		{
			$next=false;
			$errors[]='PASS_MISMATCH';
		}

		if($next and isset($_POST['next']))
			return Step4();

		if(isset($_POST['back']))
			return Step2();
	}

	# Set values
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=3;

	return $T('Step3',
		host:$_SESSION['host'] ?? (\ini_get('mysqli.default_host') ?: 'p:localhost'),
		user:$_SESSION['user'] ?? \ini_get('mysqli.default_user'),
		pass:$_SESSION['pass'] ?? \ini_get('mysqli.default_pw'),
		db:$_SESSION['db'] ?? '',

		multilang:$_SESSION['multilang'] ?? false,
		l10ns:$_SESSION['l10ns'] ?? [],

		title:$_SESSION['title'] ?? '',
		description:$_SESSION['description'] ?? '',
		hcaptcha:$_SESSION['hcaptcha'] ?? '',
		hsecret:$_SESSION['hsecret'] ?? '',

		username:$_SESSION['username'] ?? '',
		password:$_SESSION['password'] ?? '',
		password2:$_SESSION['password2'] ?? '',

		errors:$errors
	);
}

/** Step 4: create tables */
function Step4():string
{global$T;
	# Continue to the next step
	if($_SESSION['step']===4)
	{
		if(isset($_POST['back']))
			return Step3();

		if($_SESSION['step4'])
			return Step5();
	}

	# Set values
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=4;

	try{
		$Db=new MySQL($_SESSION['host'],$_SESSION['user'],$_SESSION['pass'],$_SESSION['db']);
	}catch(EM){
		return Step3(['MYSQL_CONNECT']);
	}

	if($Db->server_version<80000)
		return Step3(['MYSQL_LOW']);

	$queries=[];
	$tables=AwareInclude(__DIR__.'/includes/tables.php',[
		'Db'=>$Db,
		'l10n'=>$_SESSION['l10n'],
		'l10ns'=>$_SESSION['l10ns'],
	]);

	foreach($tables as $table=>$query)
	{
		$err=false;

		try{
			$Db->Query($query);
		}catch(EM$E){
			$err=$E->getMessage();
		}

		if(!\is_int($table))
			$queries[$table]=$err;
	}

	# PHP 8.6: migrate to pipe operator
	$ok=!\array_any($queries,fn($item)=>\is_string($item));
	$_SESSION['step4']=$ok;

	return $T('Step4',$queries,$ok);
}

/** Step 5: insert initial data */
function Step5():string
{global$T;

	# Continue to the next step
	if($_SESSION['step']===5)
	{
		if(isset($_POST['back']))
			return Step3();

		if($_SESSION['step5'])
			return Step6();

		return Step4();
	}

	# Set values
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=5;

	try{
		$Db=new MySQL($_SESSION['host'],$_SESSION['user'],$_SESSION['pass'],$_SESSION['db']);
	}catch(EM){
		return Step3(['MYSQL_CONNECT']);
	}

	$queries=[];
	$insert=AwareInclude(__DIR__.'/includes/insert.php',[
		'Db'=>$Db,
		'l10n'=>$_SESSION['l10n'],
		'l10ns'=>$_SESSION['l10ns'],
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

		if(!\is_int($key))
			$queries[$key]=$err;
	}

	# PHP 8.6: migrate to pipe operator
	$ok=!\array_any($queries,fn($item)=>\is_string($item));

	if($ok)
	{
		try{
			$Db->Insert('users',[
				'id'=>1,
				'name'=>$_SESSION['username'],
				'groups'=>'[1]',
				'password_hash'=>\password_hash($_SESSION['password'],\PASSWORD_DEFAULT),
				'avatar'=>'a'
			]);

			$queries['users']=false;
		}catch(EM$E){
			$queries['users']=(string)$E;
			$ok=false;
		}
	}

	$_SESSION['step5']=$ok;

	return $T('Step5',$queries,$ok);
}

/** Step 6: write configuration files */
function Step6():string
{global$T;

	# Continue to the next step
	if($_SESSION['step']===6)
	{
		if(isset($_POST['back']))
			return Step3();

		if($_SESSION['step6'])
			return Step7();
	}

	# Set values
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=6;

	$sitedir=\rtrim(\dirname(SITEDIR),'/').'/';
	$files=[];
	$mono=$_SESSION['l10ns']===null;
	$stack=[$_SESSION['l10n'],...($_SESSION['l10ns'] ?? [])];
	$items=[
		# Database connection configuration
		fn()=>[PutDbConfig(...),PutDbConfig($_SESSION['db'],$_SESSION['host'],$_SESSION['user'],$_SESSION['pass'])],

		# robots.txt
		fn()=>[PutRobotsTxt(...),PutRobotsTxt($sitedir)],

		# Constants
		fn()=>[PutConstants(...),PutConstants($_SESSION['l10n'],$_SESSION['l10ns'])],

		# System config
		fn()=>[PutSystemConfig(...),PutSystemConfig($_SESSION['hcaptcha'],$_SESSION['hsecret'])],

		# Main page config
		fn()=>[PutSiteConfig(...),PutSiteConfig(
			$mono ? $_SESSION['title'] : \array_fill_keys($stack,$_SESSION['title']),
			$mono ? $_SESSION['description'] : \array_fill_keys($stack,$_SESSION['description'])
		)],
	];

	foreach($items as $item)
	{
		[$F,$ok]=$item();

		$file=ModifiedFile::Get($F);
		$files[$file]=!$ok;
	}

	# PHP 8.6: migrate to pipe operator
	$ok=\array_all($files,fn($item)=>!$item);

	if($ok)
	{
		PutCache();

		# Deleting unused l10n files
		DeleteUnusedL10n([$_SESSION['l10n'],...($_SESSION['l10ns'] ?? [])]);

		# Lock the installer to prevent another installation
		$ok=LockInstaller();
		$file=ModifiedFile::Get(LockInstaller(...));
		$files[$file]=!$ok;

		# Clear sensitive session values
		if($ok)
			foreach(['host','user','pass','db', 'title','description','hcaptcha','hsecret', 'username','password','password2', 'step4','step5'] as $f)
				unset($_SESSION[$f]);
	}

	$_SESSION['step6']=$ok;

	return $T('Step6',$files,$ok);
}

/** Step 7: installation is completed */
function Step7():string
{global$T;
	# Set values
	L10n::$code=$_SESSION['l10n'];
	$_SESSION['step']=7;

	$sitedir=\rtrim(\dirname(SITEDIR),'/').'/';

	return $T('Step7',$sitedir);
}

\session_start([
	'name'=>'INSTALL',
	'use_cookies'=>true,
	'use_only_cookies'=>true,
	'cookie_path'=>SITEDIR,
	'cookie_httponly'=>true,
	'cookie_secure'=>($_SERVER['HTTPS'] ?? '')=='on'
]);

# Reset installation if lock file not found
if(isset($_SESSION['step']) and $_SESSION['step']===7 and !\is_file(LOCK))
	$_SESSION=[];

$T=new Template(__DIR__.'/template/install.php');
$_SESSION['step']??=1;

$out=match($_SESSION['step']){
	7=>Step7(),
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