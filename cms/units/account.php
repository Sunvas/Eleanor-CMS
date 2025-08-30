<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class implements Interfaces\UserSpace, Interfaces\Cron {
	readonly string
		/** @var string $slug URL prefix of the unit */
		$slug,
		/** @var string $name name of the unit */
		$name;

	function __construct()
	{
		$this->slug='account';
		$this->name=basename(__FILE__,'.php');
	}

	/** Userspace entry point */
	function UserSpace(?string$uri):never
	{
		$Uri=new Uri($this->slug)->IAM();
		$code=200;
		$cache=0;
		$output=require __DIR__."/{$this->name}/userspace.php";

		CMS::$json ? JSON($output,$code,$cache) : HTML($output,$code,$cache);
	}

	/** Cron task entry point. Is used to send notification via Telegram asynchronously. */
	function Cron(?array$remnant):array|int
	{
		return require __DIR__."/{$this->name}/cron.php";
	}
};