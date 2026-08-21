<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

use CMS\CMS;
use const Eleanor\SITEDIR;

/** URI generator */
class Uri extends \Eleanor\Classes\Uri
{
	/** @var string Base prefix containing the language identifier */
	static string $base=SITEDIR;

	/** @var array Query suffix added to all generated URIs */
	public array $query=[];

	/** @var string Prefix added to all generated URIs */
	readonly string $prefix;

	/** @param string|string[] $slug Value used to build the prefix
	 * @param string $prefix Parent prefix. Must be empty or end with /
	 * @param bool $actor Add current user ID as actor @ parameter to the query (for multi-login mode) */
	function __construct(string|array$slug=[],string$prefix='',bool$actor=true)
	{
		$this->prefix=$prefix.static::Make((array)$slug,'/');

		if($actor and CMS::$A->available)
			$this->query[]='@='.CMS::$A->current;

	}

	/** Build URI
	 * @param string|string[] $slugs Human-readable URI path parts
	 * @param string $ending URI ending
	 * @param array $query Query parameters
	 * @return string */
	function __invoke(string|array$slugs=[],string$ending='',array$query=[]):string
	{
		return static::$base.$this->prefix.static::Make((array)$slugs,$ending,\array_merge($this->query,$query));
	}

	/** Return base URI with the prefix and default query */
	function __toString():string
	{
		return static::$base.$this->prefix.($this->query ? static::Query($this->query) : '');
	}

	/** Create a nested URI generator
	 * @param string|string[] $slug Value used to extend the prefix
	 * @return static */
	function Nested(string|array$slug):static
	{
		return new static($slug,$this->prefix);
	}
}

# Not required here because class name matches filename
return Uri::class;