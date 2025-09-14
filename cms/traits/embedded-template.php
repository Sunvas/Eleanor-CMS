<?php
/**
	Eleanor CMS © 2025
	https://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Traits;

/** Используя это трейт, шаблон на объекте сможет через $this-> получить доступ к шаблонам системы */
trait EmbeddedTemplate
{
	function __call(string$n,array$a):mixed
	{
		return (\CMS::$T)($n,$a);
	}

	/** Easy way to append any string storage of Template */
	function Append(string$s,...$d):string
	{
		return$s;
	}
}

#Not necessary here, since trait name equals filename
return EmbeddedTemplate::class;