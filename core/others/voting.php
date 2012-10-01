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

class Voting extends BaseClass
{	public
		$mid,#ID модуля
		$uid,#uid
		$tpl='Voting',
		$status;#Статус пользователя по отношению к голосованию: [false] - можно голосовать, voted - уже проголосовали, refused - голос не защитан, confirmed - голос защитан, guest - голосовать нельзя, потому что голосование только для пользователей, wait - ожидает открытия, finished - голосование завершено
	protected
		$TC,
		$table,
		$voting;
	public function __construct($id,$t=false,$uid=false)
	{		$this->table=$t ? $t : P.'voting';
		$this->uid=$uid===false ? (int)Eleanor::$Login->GetUserValue('id') : $uid;
		$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'` WHERE `id`='.(int)$id.' LIMIT 1');
		$this->voting=$R->fetch_assoc();
	}

	public function Show(array$request=array())
	{		if(!$this->voting)
			return'';

		$qs=array();
		$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
		while($a=$R->fetch_assoc())
		{			$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
			$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();			$qs[$a['qid']]=array_slice($a,1);		}
		if(!isset($this->status))
			$this->Status();
		if($this->tpl)
			Eleanor::$Template->queue[]=$this->tpl;
		return Eleanor::$Template->VotingCover($this->voting,$qs,$this->status,$request);	}

	public function DoAjax()
	{		if(!isset($this->status))
			$this->Status();
		if($this->tpl)
			Eleanor::$Template->queue[]=$this->tpl;
		$data=array(
			'type'=>isset($_POST['voting']['type']) ? (string)$_POST['voting']['type'] : 'vote',
			'data'=>isset($_POST['voting']['data']) ? (array)$_POST['voting']['data'] : array(),
		);
		switch($data['type'])
		{			case'vote':
				if(!$this->status)
				{					$qa=$insr=$insqr=$ques=array();
					$error=false;					Eleanor::$Db->Transaction();
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers` FROM `'.$this->table.'_q` WHERE `id`='.$this->voting['id']);
					while($a=$R->fetch_assoc())
					{						$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
						if(!isset($data['data'][$a['qid']]) or $a['multiple'] and (!is_array($data['data'][$a['qid']]) or count($data['data'][$a['qid']])>$a['maxans'] or array_diff($data['data'][$a['qid']],array_keys($a['answers']))))
						{							$error=true;
							break;						}
						$qa[$a['qid']]=$a['answers'];					}
					if($error or count(array_intersect_key($qa,$data['data']))!=count($qa))
					{						Eleanor::$Db->RollBack();						Error();						return false;					}
					foreach($qa as $k=>&$q)
					{						$ddk=(array)$data['data'][$k];						foreach($ddk as &$v)
						{
							$q[$v]++;
							if($this->uid)
								$insqr[]=array(
									'id'=>$this->voting['id'],
									'qid'=>$k,
									'vid'=>$v,
									'uid'=>$this->uid,
								);
						}
						$qs=serialize($q);
						Eleanor::$Db->Update($this->table.'_q',array('answers'=>$qs),'`id`='.$this->voting['id'].' AND `qid`='.$k.' LIMIT 1');
						if($this->uid)
							$insr[]=array(
								'id'=>$this->voting['id'],
								'uid'=>$this->uid,
								'!date'=>'NOW()',
								'answer'=>$qs
							);
					}
					Eleanor::$Db->Update($this->table,array('!votes'=>'`votes`+1'),'`id`='.$this->voting['id'].' LIMIT 1');
					if($this->uid)
					{						Eleanor::$Db->Insert($this->table.'_q_results',$insqr);						Eleanor::$Db->Insert($this->table.'_results',$insr);
					}
					else
					{						if(!isset($this->TC))
							$this->TC=new TimeCheck($this->mid,false,$this->uid);
						$this->TC->Add('v'.$this->voting['id'],serialize($qa),false,$this->voting['againdays'].'d');					}					Eleanor::$Db->Commit();
					$this->status='confirmed';
					$this->voting['votes']++;
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
					while($a=$R->fetch_assoc())
					{						$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
						$a['answers']=$qa[$a['qid']];
						$ques[$a['qid']]=array_slice($a,1);
					}				}
				else
				{					$ques=array();					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`answers`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
					while($a=$R->fetch_assoc())
					{						$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
						$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
						$ques[$a['qid']]=array_slice($a,1);
					}
					$this->status='rejected';
				}
				Result(Eleanor::$Template->Voting($this->voting,$ques,$this->status));
				return true;
			break;
			default:
				Error();		}	}

	public function Status()
	{		if((int)$this->voting['begin']>0 and time()<strtotime($this->voting['begin']))
			return $this->status='wait';

		if((int)$this->voting['end']>0 and time()>strtotime($this->voting['end']))
			return $this->status='finished';
		if($this->voting['onlyusers'] or $this->uid)
			if($this->uid)
			{
				$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->table.'_results` WHERE `id`='.$this->voting['id'].' AND `uid`='.$this->uid.' LIMIT 1');
				$this->status=$R->num_rows==0 ? false : 'voted';
			}
			else
				$this->status='guest';
		else
		{			if(!isset($this->TC))
				$this->TC=new TimeCheck($this->mid,false,$this->uid);
			$this->status=$this->TC->Check('v'.$this->voting['id']) ? 'voted' : false;
		}	}}