<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Abstracts;

abstract class Dashboard implements \CMS\Interfaces\Dashboard
{
	/** @var string $name name of the unit */
	readonly string $name;

	function Dashboard(\CMS\Classes\UriDashboard$Uri):never
	{
		$code=200;
		$cache=0;
		$output=require __DIR__."/../units/{$this->name}/dashboard.php";

		\CMS\CMS::$json ? \CMS\JSON($output,$code,$cache) : \CMS\HTML($output,$code,$cache);
	}
}

return Dashboard::class;