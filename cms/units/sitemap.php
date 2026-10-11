<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

/** Sitemap generator */
return new readonly class implements Interfaces\Cron {
	function Cron(mixed&$data):int
	{
		# Separate file to keep this file tiny
		return require __DIR__."/$this->name/cron.php";
	}
};