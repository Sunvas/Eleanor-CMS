<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for external user authorization integration. */
interface External
{
	/** Return IDs of local users authenticated by an external system.
	 * The implementation may create missing users in the local `users` table when necessary.
	 * @return int[] IDs from the local `users` table. */
	function Get():array;

	/** Notify the external authorization system that a local user has signed in.
	 * @param int $id User ID from the local `users` table.
	 * @param bool $temp Temporary authorization flag ("Remember me" is not enabled). */
	function SignIn(int$id,bool$temp=false):void;

	/** Notify the external authorization system that one or more local users have signed out.
	 * @param int|int[] $ids One or more user IDs from the local `users` table. */
	function SignOut(int|array$ids):void;
}

# Not required here because interface name matches filename.
return External::class;