<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class CacheMachineZend implements CacheMachineInterface
{
	private
		$u,#Уникализация кэш машины
		$n=array(''=>true);#Массив имен того, что у нас есть в кеше.

	/**
	 * Конструктор кэш машины
	 *
	 * @param string $u Строка уникализации кэша (на одной кэш машине может быть запущено несколько копий Eleanor CMS)
	 */
	public function __construct($u='')
	{
		$this->u=$u;
		$this->n=$this->Get('');
		if(!$this->n or !is_array($this->n))
			$this->n=array();
	}

	public function __destruct()
	{
		$this->Put('',$this->n);
	}

	/**
	 * Запись значения
	 *
	 * @param string $k Ключ. Обратите внимение, что ключи рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $value Значение
	 * @param int $t Время жизни этой записи кэша в секундах
	 */
	public function Put($k,$value,$t=0)
	{
		$r=output_cache_put($this->u.$k,$value,$t);
		if($r)
			$this->n[$k]=$t+time();
		return$r;
	}

	/**
	 * Получение записи из кэша
	 *
	 * @param string $k Ключ
	 */
	public function Get($k)
	{
		if(!isset($this->n[$k]))
			return false;
		$r=output_cache_get($this->u.$k);
		if($r===false)
			unset($this->n[$k]);
		return$r;
	}

	/**
	 * Удаление записи из кэша
	 *
	 * @param string $k Ключ
	 */
	public function Delete($k)
	{
		unset($this->n[$k]);
		return output_cache_remove_key($this->u.$k);
	}

	/**
	 * Удаление записей по тегу. Если имя тега пустое - удаляется вешь кэш.
	 *
	 * @param string $t Тег
	 */
	public function DeleteByTag($t)
	{
		foreach($this->n as $k=>&$v)
			if($t=='' or strpos($k,$t)!==false)
				$this->Delete($k);
	}
}