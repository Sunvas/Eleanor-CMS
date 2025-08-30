<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Enums;

/** Exclusive list of roles of user groups. Feel free to add your own events to this enum, but at the same time
 * as modifying this file, you need to modify the fields in the database: `roles` fiend in `groups`.
 * Maximum amount of roles is 64, due to limitation of elements for SET type in MySQL */
enum Roles:string
{
	/** Unlimited (root) privileges */
	case Admin='admin';

	/** Member of site team. Can access to dashboard but with limited rights */
	case Team='team';
}