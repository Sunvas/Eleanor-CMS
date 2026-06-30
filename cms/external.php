<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** External authorization adapter.
 * Fill this object when Eleanor CMS should synchronize authorization state with another system. */
return new class implements \CMS\Interfaces\External
{
	/** Return IDs of local users authenticated by an external system.
	 * Create missing local users in the `users` table here when necessary.
	 * @return int[] IDs from the local `users` table */
	function Get():array
	{
		# Put your code here
		return [];
	}

	/** Notify the external authorization system that a local user has signed in.
	 * @param int $id User ID from the local `users` table
	 * @param bool $temp Temporary authorization flag; true when "remember me" is not enabled */
	function SignIn(int$id,bool$temp=false):void
	{
		# Put your code here
	}

	/** Notify the external authorization system that one or more local users have signed out.
	 * @param int|int[] $ids One or more user IDs from the local `users` table */
	function SignOut(int|array$ids):void
	{
		# Put your code here
	}
};