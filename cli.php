<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Classes\CLI;

require __DIR__.'/cms/core.php';

# Redirect to the main page when the script is called from the web
if(!CMS::$cli)
{
	\header('Location: '.\Eleanor\SITEDIR,false,308);
	die;
}

# If the system is not installed, redirect to installation setup. You can remove this block after installation
if(!\file_exists(__DIR__.'/cms/config/system.json'))
{
	new CLI('Eleanor CMS','CYAN')
		->reset(" is not installed.\nRun the installer: ")
		->green("php install/cli.php")
		->reset->write();
	die;
}

# If no command line argument is specified, display the basic information about the CLI interface
if(!isset($argv[1]))
{
	# PHP 8.6 - pipe operator
	$units=\scandir(CMS.'units');
	$units=\array_filter($units,fn($item)=>\str_ends_with($item,'.php'));
	$units=\array_filter($units,fn($item)=>(require (CMS.'units/'.$item)) instanceof Interfaces\CLI);
	$units=\array_map(fn($item)=>\strrchr($item,'.',true),$units);

	$site=\is_array(CMS::$config['site']['title']) ? L10n::Item(CMS::$config['site']['title']) : CMS::$config['site']['title'];
	$info=new CLI($site,'CYAN');

	if($units)
		$info->reset("\nCLI interface\n\nUsage:   ")->green("php $argv[0] <unit> [<action> [arguments]]")
			->reset("\nExample: ")->yellow("php $argv[0] users add Alice")
			->reset("\n\nAvailable CLI units: ")->GREEN(join(', ',$units))
			->reset("\nUse \"<unit> help\" to display unit-specific help.");
	else
		$info->reset("\nCLI interface\n\nUsage: ")->green("php $argv[0] <unit> [<action> [arguments]]")
			->reset("\n\nAvailable CLI units: (none)");

	$info->write();
	die;
}

if(\preg_match('#[^a-z\d\-_.]#i',$argv[1])==0 and \is_file($f=CMS."units/$argv[1].php"))
{
	$CMS=new CMS;

	/** @var Interfaces\CLI $U */
	$U=require$f;
	$CMS->{$argv[1]}=$U;

	if($U instanceof Interfaces\CLI)
		$U->CLI(\array_slice($argv,2));
	else
		new CLI('Unit ')->PURPLE($argv[1])->reset(' is not available from the CLI.')->write();
}
else
	new CLI('Unknown unit: ')->PURPLE($argv[1])
		->reset("\nUse \"php cli.php\" to list available CLI units.")
		->write();