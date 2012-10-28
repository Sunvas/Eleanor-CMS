<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class Rating extends BaseClass
{	public
		$mid,#В случае, если необходимо работать с нетекущим модулем.
		$uid,#ИД пользователя
		$allow=true,#Позволить голосовать
		$once=false,#Голосовать можно только один раз. В случае true, гости не смогу голосовать вообще!
		$tremark='3d',#Количество секунд, по истечению которых можно голосовать снова
		$marks=array(-3,-2,-1,1,2,3),#Массив возможных оценок
		$Tc,

		#Названия полей
		$f_average='r_average',
		$f_total='r_total',
		$f_sum='r_sum',
		$f_id='id',
		$table;

	public function __construct($mid=0,$uid=false)
	{		$this->uid=$uid===false ? Eleanor::$Login->GetUserValue('id') : $uid;
		$this->mid=$mid;
	}

	public function Show(array$ids)
	{		if($this->allow)
		{
			$T=new TimeCheck($this->mid,false,$this->uid);
			$ch=$T->Check(array_keys($ids));
		}
		$r=array();
		foreach($ids as $k=>&$v)
			$r[$k]=Eleanor::$Template->Rating($v+array(
				'marks'=>$this->marks,
				'can'=>$this->allow and (!$this->once and (int)$this->tremark or $this->uid) and !isset($ch[$k]),
				'sum'=>0,
				'total'=>0,
				'average'=>0,
			));
		return$r;
	}

	public function Delete($contid,$mark)
	{		$T=new TimeCheck($this->mid,false,$this->uid);
		$T->Delete($contid);
		$R=Eleanor::$Db->Query('SELECT `'.$this->f_total.'`,`'.$this->f_average.'`,`'.$this->f_sum.'` FROM `'.$this->table.'` WHERE `'.$this->f_id.'`='.Eleanor::$Db->Escape($contid,true).' LIMIT 1');
		if(!list($total,$average,$sum)=$R->fetch_row())
			return;
		$average=self::SubMark($total,$average,$mark);
		$sum-=$mark;
		Eleanor::$Db->Update(
			$this->table,
			array(
				$this->f_total=>--$total,
				$this->f_average=>$average,
				$this->f_sum=>$sum,
			),
			'`'.$this->f_id.'`='.Eleanor::$Db->Escape($contid)
		);		return array(
			'total'=>$total,
			'average'=>$average,
			'sum'=>$sum,
		);
	}

	public static function AddMark($total,$average,$mark)
	{		return round((ceil($average*$total)+$mark)/++$total,2);	}

	public static function SubMark($total,$average,$mark)
	{
		return round((ceil($average*$total)-$mark)/--$total,2);
	}

	public static function ChangeMark($total,$average,$oldmark,$newmark)
	{
		return round((ceil($average*$total)-$oldmark+$newmark)/$total,2);
	}
}