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
{	public
		$lid='cid',#название параметра на категорию в динамической ссылке
		$imgfolder='images/categories/',
		$dump;#Дамп БД категорий. Ведь очень часто приходиться выстраивать их в каком-то определенном порядке.

	public function Init($t,$hard=false,$nc=false)
	{		if($nc)
			$hard=true;
		$r=$hard ? false : Eleanor::$Cache->Get($t.'_'.Language::$main);
		if($r===false)
		{			$R=Eleanor::$Db->Query('SELECT * FROM `'.$t.'` INNER JOIN `'.$t.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')');
			$r=$this->GetDump($R);			if(!$nc)
				Eleanor::$Cache->Put($t.'_'.Language::$main,$r,86400,false);
		}
		return$this->dump=$r;
	}

	public function GetDump($R)
	{		$maxlen=0;
		$r=$to2sort=$to1sort=$db=array();
		while($a=$R->fetch_assoc())
		{			if($a['parents'])
			{				$cnt=substr_count($a['parents'],',');
				$to1sort[$a['id']]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}
			$db[$a['id']]=$a;
			$to2sort[$a['id']]=$a['pos'];
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
		{			$db[$k]['parents']=rtrim($db[$k]['parents'],',');
			$r[(int)$db[$k]['id']]=$db[$k];
		}

		return$r;
	}

	/*
		Функция возвращает одну запись для категории
	*/
	public function GetCategory($id,$tr=array())
	{
		if($id)
		{			if(isset($this->dump[$id]))
				return $this->dump[$id];
		}
		else
		{			$tr=(array)$tr;
			$cnt=count($tr)-1;
			$parent=0;
			$curr=array_shift($tr);
			foreach($this->dump as &$v)
				if($v['parent']==$parent and strcasecmp($v['uri'],$curr)==0)
				{
					if($cnt--==0)
						return $v;
					$curr=array_shift($tr);
					$parent=$v['id'];
				}
		}
	}

	/*
		Функция возвращает список категорий в виде option-ов, для select-a
		<option value="ID" selected="selected">VALUE</option>
		$sel - пункты, которые будут отмечены (массив, но может быть и число).
		$no - ИДы исключаемых категорий (не попадут и их дети). Может быть массивом, но может быть и числом.
	*/
	public function GetOptions($sel=array(),$no=array())
	{		$opts='';
		$sel=(array)$sel;
		$no=(array)$no;
		foreach($this->dump as &$v)
		{			$p=$v['parents'] ? explode(',',$v['parents']) : array();
			$p[]=$v['id'];
			if(array_intersect($no,$p))
				continue;
			$opts.=Eleanor::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')+1).'›&nbsp;' : '').$v['title'],$v['id'],in_array($v['id'],$sel),array(),2);
		}
		return$opts;	}

	public function GetUri($id)
	{		if(!isset($this->dump[$id]))
			return array();
		$params=array();
		$lastu=$this->dump[$id]['uri'];
		if($this->dump[$id]['parents'] and $lastu)
		{			foreach(explode(',',$this->dump[$id]['parents']) as $v)
				if(isset($this->dump[$v]))
					if($this->dump[$v]['uri'])
						$params[]=array($this->dump[$v]['uri']);
					else
					{						$params=array();
						$lastu='';
						break;					}
		}
		$params[]=array($lastu,$this->lid=>$id);
		return$params;
	}
}