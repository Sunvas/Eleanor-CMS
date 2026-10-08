<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Daily cleanup */
return new readonly class implements Interfaces\Cron {
	/** @const Path to the uploads trash directory */
	const string UPLOADS_TRASH=__DIR__.'/../../static/uploads/trash/';

	function Cron(mixed&$data):int
	{
		# Deleting events happened yesterday and earlier
		CMS::$Db->Delete('events','`happened`<NOW() - INTERVAL 1 DAY');

		# Delete old files from the uploads trash
		self::TrashCleanup();

		return 86400;
	}

	/** Delete old files from the uploads trash directory */
	private static function TrashCleanup():void
	{
		$path=\realpath(self::UPLOADS_TRASH);

		if(!$path)
			return;

		$exit=-1;

		if(\function_exists('exec'))
		{
			$arg=\escapeshellarg($path.\DIRECTORY_SEPARATOR);
			\exec("find $arg -maxdepth 1 -type f -mtime +30 -delete 2>/dev/null",$output,$exit);
		}

		if($exit===0)
			return;

		# Fallback: pure PHP implementation when shell execution is unavailable or failed
		$limit=\time()-31*86400;

		foreach(new \DirectoryIterator($path) as $f)
			if($f->isFile() and $f->getMTime()<$limit)
				@\unlink($f->getPathname());
	}
};