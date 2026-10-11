<?php # Eleanor CMS © 2026 · https://eleanor-cms.com
namespace CMS\Interfaces;

/** Interface for units that contribute new URLs to sitemap.xml incrementally. */
interface Sitemap
{
	/** Yield new URI paths for incremental sitemap generation. Previously added URIs should not be yielded again.
	 * Persistent state is preserved between calls and can be modified by reference. The data must be JSON-serializable
	 * because it is stored between calls. The generator receives true when execution must stop;
	 * update the state for the last yielded URI before returning.
	 * @param mixed $data Persistent generation state, or null if not initialized.
	 * @return \Generator<int,string> New URI paths relative to the site base URI, without scheme, host, or base path.
	 * @throws \Throwable If URI generation fails. */
	function Sitemap(mixed&$data):\Generator;
}

# Not required here because interface name matches filename.
return Sitemap::class;