<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Unit, available for admin dashboard */
interface DashboardMenu
{
	/** @var string Unit name displayed for humans */
	public string $caption{get;}
	function Dashboard():never;
}

return DashboardMenu::class;