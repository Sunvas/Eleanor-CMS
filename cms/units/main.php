<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class implements Interfaces\UserSpace, Interfaces\Dashboard {
	readonly string
		/** @var string $slug URL prefix of the unit */
		$slug,
		/** @var string $name name of the unit */
		$name;

	function __construct()
	{
		$this->slug='';#Mustn't be changed
		$this->name=basename(__FILE__,'.php');
	}

	function UserSpace(?string$uri):never
	{
		$code=200;
		$cache=CMS::$A->current ? 0 : 86400;//Page should be cached for guests only
		$output=require __DIR__."/{$this->name}/userspace.php";

		static::OutPut($output,$code,$cache);
	}

	function Dashboard(Classes\UriDashboard$Uri):never
	{
		$code=200;
		$cache=0;
		$output=require __DIR__."/{$this->name}/dashboard.php";

		static::OutPut($output,$code,$cache);
	}

	static function OutPut($output,...$a):never
	{
		if(CMS::$json)
			JSON($output,...$a);
		elseif($output)
			HTML($output,...$a);
		else
		{
			header('Cache-Control: no-store',true,204);
			die;
		}
	}

	/** Get file with contents of main page
	 * @param ?string $code L10N code
	 * @return string */
	function GetMainPageFile(?string$code=null):string
	{
		$code??=\Eleanor\Classes\L10n::$code;
		return __DIR__."/{$this->name}/mainpage-{$code}.json";
	}
};