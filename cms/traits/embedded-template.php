<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Traits;

/** Allows embedded template objects to access system templates through $this-> */
trait EmbeddedTemplate
{
	function __call(string$n,array$a):mixed
	{
		return (\CMS::$T)($n,...$a);
	}

	/** Return string as-is for appending to template storage.
	 * Extra parameters are ignored and accepted only for compatibility with the default variables mechanism. */
	function Append(string$s,...$_):string
	{
		return $s;
	}
}

# Not necessary here, since trait name equals filename
return EmbeddedTemplate::class;