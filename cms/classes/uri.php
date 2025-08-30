<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

use CMS\CMS;
use const Eleanor\SITEDIR;

/** Генератор ссылок */
class Uri extends \Eleanor\Classes\Uri
{
	/** @var string Базовый префикс, содержит идентификатор языка */
	static string $base=SITEDIR;

	/** @var array query суффикс для всех генерируемых URI */
	public array $query=[];

	/** @var string Префикс для всех генерируемых URI */
	readonly string $prefix;

	/** @param string|array $slug Значение, которое будет использовано для префикса
	 * @param string $prefix Родительский префикс. Либо пустой, либо должен оканчиваться на / */
	function __construct(string|array$slug=[],string$prefix='')
	{
		$this->prefix=$prefix.static::Make((array)$slug,'/');
	}

	/** Конструктор URL-ов
	 * @param array|string $slugs ЧПУшная часть ссылки
	 * @param string $ending Окончание ссылки
	 * @param array $query request часть ссылки
	 * @return string */
	function __invoke(array|string$slugs=[],string$ending='',array$query=[]):string
	{
		return static::$base.$this->prefix.static::Make((array)$slugs,$ending,\array_merge($this->query,$query));
	}

	/** Возврат префикса */
	function __toString():string
	{
		return static::$base.$this->prefix.($this->query ? static::Query($this->query) : '');
	}

	/** Создание вложенного (дочернего) объекта
	 * @param string|array $slug Значение, которое будет использовано для префикса
	 * @return static */
	function Nested(string|array$slug):static
	{
		return new static($slug,$this->prefix);
	}

	/** Adding iam parameter into generated links (identifies user when multilogin is used)
	 * @param string $q query key */
	function IAM(string$q='iam'):self
	{
		$id=CMS::$A->current;

		if($id and CMS::$A->available)
			$this->query[$q]=$id;

		return$this;
	}
}

return Uri::class;