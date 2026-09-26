<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units available from the command line. */
interface CLI
{
	/** Execute the requested command.
	 * @param string[] $argv Command-line arguments excluding the script name and unit. */
	function CLI(array$argv):void;
}

# Not required here because interface name matches filename.
return CLI::class;