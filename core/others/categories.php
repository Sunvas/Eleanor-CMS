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

class Categories extends BaseClass
{
	public
		$lid='cid',#Название параметра категории в динамической ссылке
		$imgfolder='images/categories/',#Каталог с логотипами категорий
		$dump;#Дамп БД категорий, в удобном упорядоченном виде

	/**
	 * Конструктор, самый обыкновенный, ничем не приметный конструктор. Все входящие переменные передаются методу Init
	 */
	public function __construct()
	{
		$a=func_get_args();
		if($a)
			call_user_func_array(array($this,'Init'),$a);
	}

	/**
	 * Инициализация класса, здесь задается имя таблицы, откуда будут формироваться категории
	 *
	 * @param string $t Имя основной (не языковой) таблицы
	 * @param int|FALSE $cache Флаг, определяющий время кэширования дампа таблицы, передача FALSE отключает кэширование
	 */
	public function Init($t,$cache=86400)
	{
		$r=$cache ? Eleanor::$Cache->Get($t.'_'.Language::$main) : false;
		if($r===false)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.$t.'` INNER JOIN `'.$t.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')');
			$r=$this->GetDump($R);
			if($cache)
				Eleanor::$Cache->Put($t.'_'.Language::$main,$r,86400,false);
		}
		return$this->dump=$r;
	}

	/**
	 * Формирование дампа таблицы в удобном иерархическом виде
	 *
	 * @param mysqli_result $R Результат выполнения дамп-запроса из базы данных
	 */
	public function GetDump($R)
	{
		$maxlen=0;
		$r=$to2sort=$to1sort=$db=array();
		while($a=$R->fetch_assoc())
		{
			if($a['parents'])
			{
				$cnt=substr_count($a['parents'],',');
				$to1sort[ $a['id'] ]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}
			$db[ $a['id'] ]=$a;
			$to2sort[ $a['id'] ]=$a['pos'];
		}
		asort($to1sort,SORT_NUMERIC);

		foreach($to1sort as $k=>&$v)
			if($db[$k]['parents'])
				if(isset($to2sort[$db[$k]['parent']]))
					$to2sort[$k]=$to2sort[$db[$k]['parent']].','.$to2sort[$k];
				else
					unset($to2sort[$db[$k]['parent']]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

		natsort($to2sort);
		foreach($to2sort as $k=>&$v)
			$r[ (int)$db[$k]['id'] ]=array_slice($db[$k],1);

		return$r;
	}

	/**
	 * Функция осуществляет поиск по дампу категорий исходя из переданного ID или последовательности URI категории
	 *
	 * @param int|array $id Числовой идентификатор категории либо массив последовательности URI
	 */
	public function GetCategory($id)
	{
		if(is_array($id))
		{
			$cnt=count($id)-1;
			$parent=0;
			$curr=array_shift($id);
			foreach($this->dump as $k=>&$v)
				if($v['parent']==$parent and strcasecmp($v['uri'],$curr)==0)
				{
					if($cnt--==0)
					{
						$id=$k;
						break;
					}
					$curr=array_shift($id);
					$parent=$k;
				}
		}
		if(is_scalar($id) and isset($this->dump[$id]))
		{
			$this->dump[$id]['description']=OwnBB::Parse($this->dump[$id]['description']);
			return$this->dump[$id]+array('id'=>$id);
		}
	}

	/**
	 * Получение списка категорий в виде option-ов, для select-a: <option value="ID" selected>VALUE</option>
	 *
	 * @param int|array $sel Пункты, которые будут отмечены
	 * @param int|array $no ИДы исключаемых категорий (не попадут и их дети)
	 */
	public function GetOptions($sel=array(),$no=array())
	{
		$opts='';
		$sel=(array)$sel;
		$no=(array)$no;
		foreach($this->dump as $k=>&$v)
		{
			$p=$v['parents'] ? explode(',',$v['parents']) : array();
			$p[]=$k;
			if(array_intersect($no,$p))
				continue;
			$opts.=Eleanor::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')+1).'›&nbsp;' : '').$v['title'],$k,in_array($k,$sel),array(),2);
		}
		return$opts;
	}

	/**
	 * Получение массива URI для дальнейшего передачи его в класс URL с последующей генерации ссылки
	 *
	 * @param int $id Числовой идентификатор категории
	 */
	public function GetUri($id)
	{
		if(!isset($this->dump[$id]))
			return array();
		$params=array();
		$lastu=$this->dump[$id]['uri'];
		if($this->dump[$id]['parents'] and $lastu)
		{
			foreach(explode(',',$this->dump[$id]['parents']) as $v)
				if(isset($this->dump[$v]))
					if($this->dump[$v]['uri'])
						$params[]=array($this->dump[$v]['uri']);
					else
					{
						$params=array();
						$lastu='';
						break;
					}
		}
		$params[]=array($lastu,$this->lid=>$id);
		return$params;
	}
}