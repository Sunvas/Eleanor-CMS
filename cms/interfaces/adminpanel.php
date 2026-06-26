<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units available from the admin panel. */
interface AdminPanel
{
	/** Admin panel entry point.
	 * @param \CMS\Classes\Uri4AdminPanel $Uri Current admin panel URI context.
	 * @return never */
	function AdminPanel(\CMS\Classes\Uri4AdminPanel$Uri):never;
}

# Not required here because interface name matches filename.
return AdminPanel::class;