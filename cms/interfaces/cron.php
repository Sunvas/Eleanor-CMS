<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units that can be executed as background tasks. */
interface Cron
{
	/** Execute the task.
	 * If a task is too heavy to finish in a single run, it should be split into stages.
	 * The result returned from the previous stage is passed to the next run as an array.
	 * This array must be JSON-serializable because it is stored in the database between runs.
	 * @param ?array $remnant Data returned by the previous run to continue an unfinished task.
	 * @return array|int Returning an array indicates that the task is not finished yet and should be resumed
	 * shortly. Returning an integer specifies the number of seconds until the next execution.
	 * @throws \Throwable Marks the task as failed. */
	function Cron(?array$remnant):array|int;
}

# Not required here because interface name matches filename.
return Cron::class;