<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class implements Interfaces\Dashboard {
	/** @var string $name name of the unit */
	readonly string $name;

	function __construct()
	{
		$this->name=basename(__FILE__,'.php');
	}

	function Dashboard(Classes\UriDashboard$Uri):never
	{
		$code=200;
		$cache=0;
		$output=require __DIR__."/{$this->name}/dashboard.php";

		CMS::$json ? JSON($output,$code,$cache) : HTML($output,$code,$cache);
	}
};