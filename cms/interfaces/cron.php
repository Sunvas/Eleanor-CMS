<?php # Eleanor CMS © 2025 · https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units that can be executed as background tasks. */
interface Cron
{
	/** Execute the task.
	 * Task data is preserved between executions and can be modified by reference.
	 * The data must be JSON-serializable and is saved after successful execution.
	 * Long-running tasks can be split into stages by returning zero and storing progress in $data.
	 * @param mixed $data Persistent task data, or null if not initialized.
	 * @return int Seconds until the next execution. Zero or negative values request immediate execution.
	 * @throws \Throwable Marks the task as failed. */
	function Cron(mixed&$data):int;
}

# Not required here because interface name matches filename.
return Cron::class;