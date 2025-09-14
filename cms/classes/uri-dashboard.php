<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

use Eleanor\Classes\Uri;

/** URI generator for dashboard */
class UriDashboard extends \Eleanor\Basic
{
	/** @var string Basic prefix of site directory and dashboard file */
	static string $base="";

	/** @var bool Flag to use &amp; as param separator */
	public bool $amp=true;

	/** @var array Mandatory query params */
	readonly array $query;

	/** Params to $query are passed as named params */
	function __construct(...$query)
	{
		$this->query=$query;
	}

	/** Generator of URI
	 * Params are passed as named params
	 * @throws \InvalidArgumentException when params has an intersecting key with the internal query property
	 * @return string */
	function __invoke(...$query):string
	{
		$this->CheckIntersection($query);

		return static::$base.Uri::Query($this->query+$query,d:$this->amp ? '&amp;' : '&');
	}

	/** Generating URI of current query */
	function __toString():string
	{
		return static::$base.Uri::Query($this->query);
	}

	/** Checking keys intersection with internal query
	 * @param array $query Query parameters
	 * @throws \InvalidArgumentException when $query has an intersecting key with the internal query property */
	protected function CheckIntersection(array$query):void
	{
		$invalid=\array_intersect_key($query,$this->query);

		if($invalid)
			throw new \InvalidArgumentException('These query params are already in use: '.\join(', ',\array_keys($invalid)));
	}

	/** Making nested generator
	 * @param array $query Sub-mandatory query params
	 * @throws \InvalidArgumentException when $query has an intersecting key with the internal query property
	 * @return static */
	function Nested(array$query):static
	{
		$this->CheckIntersection($query);

		return new static($this->query+$query);
	}
}

#Not necessary here, since class name equals filename
return UriDashboard::class;