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

class TimeCheck extends BaseClass
{
	public
		$table,
		$cp='',#Cookie prefix
		$mid,#Для корректной работы вне одного модуля
		$uid;#Для корректной работы на благо другого пользователя

	public function __construct($mid=0,$table=false,$uid=false)
	{
		$this->uid=$uid===false ? Eleanor::$Login->GetUserValue('id') : $uid;
		$this->mid=$mid;
		if($mid)
			$this->cp=$mid.'-';
		$this->table=$table ? $table : P.'timecheck';
	}

	public function Check($ids,$booly=true)
	{		if(!$ids)
			return false;		$isa=is_array($ids);
		$r=array();
		if($booly)
		{			$ids=(array)$ids;
			foreach($ids as $k=>&$v)
				if(Eleanor::GetCookie($this->cp.$v))
				{					$r[$v]=true;
					unset($ids[$k]);
				}
		}

		if($ids)
		{			$t=time();
			$R=Eleanor::$Db->Query('SELECT `contid`,`author_id`,`ip`,`value`,`timegone`,`date` FROM `'.$this->table.'` WHERE '.($this->mid ? '`mid`='.Eleanor::$Db->Escape($this->mid).' AND ' : '').'`contid`'.Eleanor::$Db->In($ids).' AND `author_id`='.(int)$this->uid.($this->uid ? '' : ' AND `ip`=\''.Eleanor::$ip.'\''));
			while($a=$R->fetch_assoc())
				if($t<$a['_datets']=strtotime($a['date']) or !$a['timegone'])
				{					if($booly and !isset($r[$a['contid']]))
						Eleanor::SetCookie($this->cp.$a['contid'],1,$a['_datets'].'t');
					$r[$a['contid']]=array_slice($a,1);
				}
		}
		return $isa ? $r : reset($r);
	}

	public function Add($contid,$value='',$timegone=false,$t=3)
	{		$plus='';
		if(!$this->uid)
			$timegone=true;		if($timegone)
		{			if((int)$t==0)
				return;			$plus=' + INTERVAL ';
			switch(substr($t,-1))
			{
				case'm':
					$plus.=(int)$t.' MINUTE';
				break;
				case'h':
					$plus.=(int)$t.' HOUR';
				break;
				case'd':
					$plus.=(int)$t.' DAY';
				break;
				case'M':
					$plus.=(int)$t.' MONTH';
				break;
				case'y':
					$plus.=(int)$t.' YEAR';
				break;
				default:
					$plus.=(int)$t.' SECOND';
			}
		}
		Eleanor::SetCookie($this->cp.$contid,1,$t);
		return Eleanor::$Db->Replace(
			$this->table,
			($this->mid ? array('mid'=>$this->mid) : array())
			+array(
				'contid'=>$contid,
				'author_id'=>$this->uid,
				'ip'=>$this->uid ? '' : Eleanor::$ip,
				'value'=>$value,
				'timegone'=>$timegone,#Может истечь срок голосования?
				'!date'=>'NOW()'.$plus,
			)
		);
	}

	public function Delete($value)
	{		Eleanor::$Db->Delete($this->table,($this->mid ? '`mid`'.Eleanor::$Db->Escape($this->mid,true).' AND ' : '').'`contid`'.Eleanor::$Db->Escape($value,true).' AND `author_id`'.Eleanor::$Db->Escape($this->uid,true).($this->uid ? '' : ' AND `ip`='.Eleanor::$Db->Escape(Eleanor::$ip,true)));	}
}