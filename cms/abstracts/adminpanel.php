<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Abstracts;

abstract class AdminPanel implements \CMS\Interfaces\AdminPanel
{
	/** @var string $name name of the unit */
	readonly string $name;

	function AdminPanel(\CMS\Classes\Uri4AdminPanel $Uri):never
	{
		$code=200;
		$cache=0;
		$output=require __DIR__."/../units/{$this->name}/admin-panel.php";

		\CMS\CMS::$json ? \CMS\JSON($output,$code,$cache) : \CMS\HTML($output,$code,$cache);
	}
}

return AdminPanel::class;