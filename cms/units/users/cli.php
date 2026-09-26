<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS\UnitUsers;

use CMS\CMS,
	Eleanor\Classes\CLI;
use const CMS\JSON;
use function CMS\{Error, SingleFetch};


/** Default group for newly created users */
const USER_GROUP=3;

/** @var $argv string[] Command-line arguments for the unit */

if(!isset($argv[0]))
	return new CLI('Users management','CYAN')
		->reset("\n\nRun \"")->yellow("php cli.php $this->name help")->reset("\" for details.\n")
		->Write();

match($argv[0])
{
	'create'=>Create(\array_slice($argv,1)),
	'update'=>Update(\array_slice($argv,1)),
	'delete'=>Delete(\array_slice($argv,1)),
	'show'=>Show(\array_slice($argv,1)),
	'help'=>Help($this->name),
	default=>\CMS\UnknownCommand($argv[0],$this->name),
};

/** Create user */
function Create(array$argv):void
{
	if(!isset($argv[0]))
		Error('Missing username');

	$R=CMS::$Db->Execute("SELECT `name` FROM `users` WHERE `name`=?",[$argv[0]]);

	if([$R->num_rows,$R->free()][0]>0)
		Error('User exists');

	$data=[
		'name'=>\array_shift($argv),
	];
	$options=GetOptions($argv);
	$unknown=\array_diff(\array_keys($options),[
		'groups','password',
		'comment','display_name','info','l10n','timezone'
	]);

	if($unknown)
		Error('Unknown options: --'.\join(', --',$unknown));

	if($argv)
		Error('Unexpected arguments: '.\join(', ',$argv));

	if(isset($options['groups']))
	{
		if(!\preg_match('#^\d+(,\s?\d+)*$#',$options['groups']))
			Error('Invalid groups');

		# PHP 8.6 migrate to pipe operator
		$data['groups']=\explode(',',$options['groups']);
		$data['groups']=\array_map('intval',$data['groups']);
	}
	else
		$data['groups']=[USER_GROUP];

	$data['groups']=\json_encode($data['groups'],JSON);

	if(\array_key_exists('password',$options))
	{
		$options['password']??=new CLI("Input password (input may be hidden):\n")->Write()
			|> ReadPassword(...);

		$data['password_hash']=\password_hash($options['password'],PASSWORD_DEFAULT);
	}

	foreach(['comment','display_name','info','l10n','timezone'] as $f)
		if(isset($options[$f]))
			$data[$f]=$options[$f];

	$id=CMS::$Db->Insert('users',$data);

	if($id)
		new CLI('User created. ID: '.$id,'green')->reset(\PHP_EOL)->Write();
	else
		Error('Failed to create user');
}

/** Update user */
function Update(array$argv):void
{
	$user=GetUserByName($argv[0] ?? null);
	\array_shift($argv);

	if(!isset($argv[0]))
		Error('Missing data');

	$data=[];
	$options=GetOptions($argv);
	$unknown=\array_diff(\array_keys($options),[
		'name','groups','password',
		'comment','display_name','info','l10n','timezone'
	]);

	if($unknown)
		Error('Unknown options: --'.\join(', --',$unknown));

	if($argv)
		Error('Unexpected arguments: '.\join(', ',$argv));

	if(isset($options['name']))
	{
		$R=CMS::$Db->Execute("SELECT `name` FROM `users` WHERE `name`=? AND `id`!=".$user['id'],[$options['name']]);

		if([$R->num_rows,$R->free()][0]>0)
			Error('User exists: '.$options['name']);

		$data['name']=$options['name'];
	}

	if(isset($options['groups']))
	{
		if(!\preg_match('#^\d+(,\s?\d+)*$#',$options['groups']))
			Error('Invalid groups');

		# PHP 8.6 migrate to pipe operator
		$data['groups']=\explode(',',$options['groups']);
		$data['groups']=\array_map('intval',$data['groups']);
		$data['groups']=\json_encode($data['groups'],JSON);
	}

	if(\array_key_exists('password',$options))
	{
		$options['password']??=new CLI("Input password (input may be hidden):\n")->Write()
				|> ReadPassword(...);

		$data['password_hash']=\password_hash($options['password'],PASSWORD_DEFAULT);
	}

	foreach(['comment','display_name','info','l10n','timezone'] as $f)
		if(isset($options[$f]))
			$data[$f]=$options[$f];

	$num=CMS::$Db->Update('users',$data,'`id`='.$user['id']);
	$cli=$num>0
		? new CLI('User updated','green')
		: new CLI('User NOT updated','yellow');

	$cli->reset(\PHP_EOL)->Write();
}

/** Delete user */
function Delete(array$argv):void
{
	$user=GetUserByName($argv[0] ?? null,'`id`,`name`,`avatar`');
	\array_shift($argv);

	$options=GetOptions($argv);
	$unknown=\array_keys($options);
	$unexpected=\array_diff($argv,['-y']);

	if($unknown)
		Error('Unknown options: --'.\join(', --',$unknown));

	if($unexpected)
		Error('Unexpected arguments: '.\join(', ',$unexpected));

	# Confirmation
	if(!\in_array('-y',$argv))
	{
		new CLI('Delete user "')->CYAN($user['name'])->reset('"? [y/N] ')->Write();

		$yes=\rtrim((string)\fgets(\STDIN),"\r\n");

		if(\strcasecmp($yes,'y')!==0)
			return;
	}

	$avatar=\CMS\STATIC_PATH."avatars/{$user['id']}-{$user['avatar']}.webp";

	if(\file_exists($avatar) and !\unlink($avatar))
		Error('Failed to delete avatar: '.$avatar);

	if(CMS::$Db->Delete('users','`id`='.$user['id']))
		new CLI('User deleted','green')->reset(\PHP_EOL)->Write();
	else
		Error('Failed to delete user');
}

/** Show user */
function Show(array$argv):void
{
	$user=GetUserByName($argv[0] ?? null,'`id`,`name`,`groups`,`password_changed_at`,`display_name`,IF(`totp_secret` IS NULL,0,1) `totp_enabled`,IFNULL(JSON_LENGTH(`recovery_codes`),0) `recovery_codes`,`created`,`activity`,`last_login_attempt`,`comment`');
	\array_shift($argv);

	$options=GetOptions($argv);
	$unknown=\array_keys($options);
	$unexpected=\array_diff($argv,['-j']);

	if($unknown)
		Error('Unknown options: --'.\join(', --',$unknown));

	if($unexpected)
		Error('Unexpected arguments: '.\join(', ',$unexpected));

	$user['groups']=\json_decode($user['groups'],true);
	$user['totp_enabled']=(bool)$user['totp_enabled'];

	# JSON output
	if(\in_array('-j',$argv))
	{
		echo \json_encode($user,JSON),\PHP_EOL;
		return;
	}

	$dn=$user['display_name']==='' ? '' : "\nDisplay name: ".$user['display_name'];

	new CLI($user['name'],'CYAN')
		->reset("\nID: ")->purple($user['id'])
		->reset($dn."\nGroups: ")->purple(\join(', ',$user['groups']))
		->reset("\nTOTP: ")->{$user['totp_enabled'] ? 'green' : 'red'}($user['totp_enabled'] ? 'enabled' : 'disabled')
		->reset("\nRecovery codes: ")->purple($user['recovery_codes'])
		->reset("\nCreated: ")->yellow($user['created'])
		->reset("\nLast activity: ")->yellow($user['activity'])
		->reset("\nPassword changed: ")->yellow($user['password_changed_at'])
		->reset("\nLast login attempt: ")->yellow($user['last_login_attempt'])
		->reset($user['comment'] ? "\nComment: ".$user['comment'] : '')
		->Write();
}

/** Show help */
function Help(string$unit):void
{
	new CLI("Users management",'CYAN')
		->reset("\n\nCREATE\n")->yellow("  php cli.php $unit create <username> [options]")
		->purple("\n  --password[=<password>]")->reset(" Set password; prompt if value is omitted")
		->purple("\n  --groups=<ids>         ")->reset(" Group IDs separated by commas")
		->purple("\n  --display_name=<name>  ")->reset(" Display name")
		->purple("\n  --comment=<text>       ")->reset(" Admin comment")
		->purple("\n  --info=<text>          ")->reset(" Publicly available information")
		->purple("\n  --l10n=<language>      ")->reset(" Localization (en, ru)")
		->purple("\n  --timezone=<timezone>  ")->reset(" Timezone")
		->Write()

		->Concat("\n\nUPDATE\n")->yellow("  php cli.php $unit update <username> <options>")
		->purple("\n  --name=<username>      ")->reset(" Change username")
		->purple("\n  --password[=<password>]")->reset(" Change password; prompt if value is omitted")
		->purple("\n  --groups=<ids>         ")->reset(" Replace group IDs; comma-separated")
		->purple("\n  --display_name=<name>  ")->reset(" Display name")
		->purple("\n  --comment=<text>       ")->reset(" Admin comment")
		->purple("\n  --info=<text>          ")->reset(" Publicly available information")
		->purple("\n  --l10n=<language>      ")->reset(" Localization (en, ru)")
		->purple("\n  --timezone=<timezone>  ")->reset(" Timezone")
		->Write()

		->Concat("\n\nDELETE\n")->yellow("  php cli.php $unit delete <username> [-y]")
		->purple("\n  -y  ")->reset(" Skip confirmation")

		->Concat("\n\nSHOW\n")->yellow("  php cli.php $unit show <username> [-j]")
		->purple("\n  -j  ")->reset(" Output as JSON\n")
		->Write();
}

/** Get user by name
 * @param ?string $name Username
 * @param string $fields Fields list for SELECT query
 * @throws \Throwable
 * @return array */
function GetUserByName(?string$name,string$fields='`id`'):array
{
	if($name===null)
		Error('Missing username');

	$R=CMS::$Db->Execute("SELECT $fields FROM `users` WHERE `name`=?",[$name]);
	$user=SingleFetch($R);

	if(!$user)
		Error('User not found');

	return $user;
}

function GetOptions(array&$argv):array
{
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

	return $options;
}

/** Read a password from the command line */
function ReadPassword(CLI$cli,bool$repeat=true):string
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

		if($repeat)
		{
			$cli->Concat("Repeat password: \n")->Write();
			$hidden2=\rtrim((string)\fgets(\STDIN),"\r\n");
			Asterisks($cli,\strlen($hidden2));

			\system('stty echo');

			if(\strcmp($hidden2,$hidden)!==0)
			{
				$cli->red("Passwords mismatch. Process stopped.")->reset(\PHP_EOL)->Write();
				exit(1);
			}
		}
		else
			\system('stty echo');
	}

	return $hidden;
}

/** Display the password length as asterisks */
function Asterisks(CLI$cli,int$len):void
{
	$cli->purple(\str_repeat('*',$len))->reset(\PHP_EOL)->Write();
}