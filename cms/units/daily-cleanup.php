<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Daily cleanup */
return new readonly class implements Interfaces\Cron {
	/** @const Relative path to the uploads trash directory, where files older than 30 days are automatically deleted */
	const string UPLOADS_TRASH=__DIR__.'/../../static/uploads/trash/';

	function Cron(?array$remnant):int
	{
		#Deleting events happened yesterday and earlier
		CMS::$Db->Delete('events','`happened`<NOW() - INTERVAL 1 DAY');

		#Trash bin cleanup (currently for linux only)
		$path=\realpath(self::UPLOADS_TRASH);

		if($path)
			\shell_exec("find $path/ -type f -mtime +30 -delete");

		return 86400;
	}
};