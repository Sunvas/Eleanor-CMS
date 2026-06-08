<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Enums;

/** Exclusive list of user group roles. Feel free to add your own roles to this enum, but at the same time
 * as modifying this file, you need to modify the `roles` field in the `groups` table.
 * The maximum number of roles is 64 because MySQL SET supports up to 64 elements. */
enum Roles:string
{
	/** Unlimited privileges. */
	case Root='root';

	/** Member of the site team with limited access to the admin panel. */
	case Team='team';
}

# Not required here because the enum name matches filename
return Roles::class;