<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units available from the user area. */
interface UserArea
{
	/** @var string URI prefix of the unit */
	public string $slug{get;}

	/** User area entry point.
	 * @param ?string $uri Requested URI tail after the unit slug.
	 * @return never */
	function UserArea(?string$uri):never;
}

# Not required here because interface name matches filename.
return UserArea::class;