<?php # Eleanor CMS © 2026 · https://eleanor-cms.com
namespace CMS;

/** Weekly cleanup */
return new readonly class implements Interfaces\Cron {
	function Cron(mixed&$data):int
	{
		# Delete obsolete a11n sessions
		CMS::$Db->Delete('a11n','`generated`<NOW() - INTERVAL 1 YEAR');

		return 86400*7;
	}
};