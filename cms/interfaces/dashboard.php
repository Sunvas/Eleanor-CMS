<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Unit, available for admin dashboard */
interface Dashboard
{
	function Dashboard(\CMS\Classes\UriDashboard$Uri):never;
}

return Dashboard::class;