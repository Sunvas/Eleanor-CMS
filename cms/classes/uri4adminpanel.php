<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

use Eleanor\Classes\Uri;

/** URI generator for admin panel */
class Uri4AdminPanel extends \Eleanor\Basic
{
	/** @var string Base prefix containing site directory and admin panel file */
	static string $base="";

	/** @var bool Use &amp; as parameter separator */
	public bool $amp=true;

	/** @var array Mandatory query parameters */
	readonly array $query;

	/** Query parameters are passed as named parameters */
	function __construct(...$query)
	{
		$this->query=$query;
	}

	/** Generate URI. Parameters are passed as named parameters.
	 * @throws \InvalidArgumentException when parameters intersect with mandatory query parameters
	 * @return string */
	function __invoke(...$query):string
	{
		$this->CheckIntersection($query);

		return static::$base.Uri::Query($this->query+$query,d:$this->amp ? '&amp;' : '&');
	}

	/** Generate URI from mandatory query parameters */
	function __toString():string
	{
		return static::$base.Uri::Query($this->query,d:$this->amp ? '&amp;' : '&');
	}

	/** Check for key intersection with mandatory query parameters
	 * @param array $query Query parameters
	 * @throws \InvalidArgumentException when parameters intersect with mandatory query parameters */
	protected function CheckIntersection(array$query):void
	{
		$invalid=\array_intersect_key($query,$this->query);

		if($invalid)
			throw new \InvalidArgumentException('These query params are already in use: '.\join(', ',\array_keys($invalid)));
	}

	/** Create a nested URI generator
	 * @param array $query Additional mandatory query parameters
	 * @throws \InvalidArgumentException when parameters intersect with mandatory query parameters
	 * @return static */
	function Nested(array$query):static
	{
		$this->CheckIntersection($query);

		return new static(...($this->query+$query));
	}
}

# Not required here because class name matches filename
return Uri4AdminPanel::class;