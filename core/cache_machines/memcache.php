<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
 *
 * TODO: DELETE метод стоит сделать protected и разделить удаление кэша на 2 метода:
 * deleteById() - удаление по Id кэша
 * flush() - полная чистка кэша
 * Таким образом можно обезопаситься от случайной чистки всего кэша
*/
class CacheMachineMemCache implements CacheMachineInterface
{
    /**
     * Объект Memcache
     * @var Memcache
     */
    protected $_memcache;

	private
		$u,#Уникализация кэш машины
		$n=array(''=>true);#Массив имен того, что у нас есть в кеше.

	/**
	 * Конструктор кэш машины.
	 *
     * Строка уникализации кэша (на одной кэш машине может быть запущено несколько копий Eleanor CMS).
     *
	 * @param string $u
	 */
	public function __construct($u='')
	{
        if($this->_memcache === null)
            $this->_memcache = new Memcache();

		$this->u=$u;
		Eleanor::$nolog=true;

		$this->_memcache->connect('localhost');

		Eleanor::$nolog=false;

		if(!$this->_memcache)
			throw new Exception('MemCache failure '.dirname(__FILE__));

        // $this->_memcache->addServer('server', 'port');

        $this->_memcache->setCompressThreshold(20000,0.2);
		$this->n=$this->Get('');
		if(!$this->n or !is_array($this->n))
			$this->n=array();
	}

	public function __destruct()
	{
		$this->Put('',$this->n);
		if($this->_memcache)
            $this->_memcache->close();
	}

	/**
	 * Запись значения
	 *
	 * @param string $k Ключ. Обратите внимение, что ключи рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $value Значение
	 * @param int $t Время жизни этой записи кэша в секундах
	 */
	public function Put($k,$v,$t=0)
    {

		$r = $this->_memcache->set($this->u.$k, $v, is_bool($v) || is_int($v) || is_float($v) ? 0 : MEMCACHE_COMPRESSED, $t);

        if($r)
			$this->n[$k] = $t+time();

		return $r;
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

        $r = $this->_memcache->get($this->u.$k);

		if($r===false)
			unset($this->n[$k]);

		return $r;
	}

	/**
	 * Удаление записи из кэша
	 *
	 * @param string $k Ключ
	 */
	public function Delete($k)
	{
		unset($this->n[$k]);

        return $this->_memcache->delete($this->u.$k);
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