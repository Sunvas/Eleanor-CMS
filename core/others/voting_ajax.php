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

class Voting_Ajax extends Voting
{
	/**
	 * Осуществление действий по Ajax заспросу
	 */
	public function Process()
	{
		if(!isset($this->status))
			$this->Status();
		if($this->tpl)
			Eleanor::$Template->queue[]=$this->tpl;
		$data=array(
			'type'=>isset($_POST['voting']['type']) ? (string)$_POST['voting']['type'] : 'vote',
			'data'=>isset($_POST['voting']['data']) ? (array)$_POST['voting']['data'] : array(),
		);
		switch($data['type'])
		{
			case'vote':
				if(!$this->status)
				{
					$qa=$insr=$insqr=$ques=array();
					$error=false;
					Eleanor::$Db->Transaction();
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers` FROM `'.$this->table.'_q` WHERE `id`='.$this->voting['id']);
					while($a=$R->fetch_assoc())
					{
						$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
						if(!isset($data['data'][$a['qid']]) or $a['multiple'] and (!is_array($data['data'][$a['qid']]) or count($data['data'][$a['qid']])>$a['maxans'] or array_diff($data['data'][$a['qid']],array_keys($a['answers']))))
						{
							$error=true;
							break;
						}
						$qa[$a['qid']]=$a['answers'];
					}
					if($error or count(array_intersect_key($qa,$data['data']))!=count($qa))
					{
						Eleanor::$Db->RollBack();
						Error();
						return false;
					}
					foreach($qa as $k=>&$q)
					{
						$ddk=(array)$data['data'][$k];
						foreach($ddk as &$v)
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
					{
						Eleanor::$Db->Insert($this->table.'_q_results',$insqr);
						Eleanor::$Db->Insert($this->table.'_results',$insr);
					}
					else
					{
						if(!isset($this->TC))
							$this->TC=new TimeCheck($this->mid,false,$this->uid);
						$this->TC->Add('v'.$this->voting['id'],serialize($qa),false,$this->voting['againdays'].'d');
					}
					Eleanor::$Db->Commit();
					$this->status='confirmed';
					$this->voting['votes']++;
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
					while($a=$R->fetch_assoc())
					{
						$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
						$a['answers']=$qa[$a['qid']];
						$ques[$a['qid']]=array_slice($a,1);
					}
				}
				else
				{
					$ques=array();
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`answers`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
					while($a=$R->fetch_assoc())
					{
						$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
						$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
						$ques[$a['qid']]=array_slice($a,1);
					}
					$this->status='rejected';
				}
				Result(Eleanor::$Template->Voting($this->voting,$ques,$this->status));
				return true;
			break;
			default:
				Error();
		}
	}
}