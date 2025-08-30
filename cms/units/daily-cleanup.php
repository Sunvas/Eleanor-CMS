<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Daily cleanup */
return new readonly class implements Interfaces\Cron {
	function Cron(?array$remnant):int
	{
		CMS::$Db->Delete('events','`happened`<NOW() - INTERVAL 1 DAY');

		return 86400;
	}
};