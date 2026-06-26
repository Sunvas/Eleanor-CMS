<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units available from the command line. */
interface CLI
{
	/** Execute the requested action.
	 * @param string[] $argv Command-line arguments excluding the script name and unit.
	 * @return never */
	function CLI(array$argv):never;
}

# Not required here because interface name matches filename.
return CLI::class;