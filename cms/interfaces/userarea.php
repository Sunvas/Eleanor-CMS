<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Unit, available for user area of the site (index.php) */
interface UserArea
{
	/** @var string URL prefix of unit */
	public string $slug{get;}

	/** Method to run unit in the user space (index.php)
	 * @param ?string $uri URL of unit */
	function UserArea(?string $uri):never;
}

#Not necessary here, since interface name equals filename
return UserArea::class;