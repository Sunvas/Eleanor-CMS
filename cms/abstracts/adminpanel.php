<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Abstracts;

/** Base implementation for units available from the admin panel */
abstract class AdminPanel implements \CMS\Interfaces\AdminPanel
{
	/** @var string Unit name */
	readonly string $name;

	/** Admin panel entry point.
	 * Loads units/{name}/admin-panel.php and sends HTML or JSON response.
	 * @param \CMS\Classes\Uri4AdminPanel $Uri Current admin panel URI context.
	 * @return never */
	function AdminPanel(\CMS\Classes\Uri4AdminPanel$Uri):never
	{
		$code=200;
		$cache=0;
		$output=require __DIR__."/../units/{$this->name}/admin-panel.php";

		\CMS\CMS::$json ? \CMS\JSON($output,$code,$cache) : \CMS\HTML($output,$code,$cache);
	}
}

return AdminPanel::class;