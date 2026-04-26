<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Unit, available for admin panel */
interface AdminPanel
{
	function AdminPanel(\CMS\Classes\Uri4AdminPanel $Uri):never;
}

#Not necessary here, since interface name equals filename
return AdminPanel::class;