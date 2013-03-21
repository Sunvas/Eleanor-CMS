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

class Comments_Ajax extends Comments
{
	/**
	 * Обработка Ajax запроса комментариев
	 *
	 * @param array $post Массив переменных, переданных движком комментариев
	 * @param string $id Идентификатор контента, для которого выводятся комментарии
	 * @param int|FALSE $mid Числовой идентификатор модуля, к которому относятся комментарии
	 * @return false|array Возвращает false в случае неудачи либо массив с результатами выполнения в случае удачи
	 */
	public function Process(array$post,$id,$mid=false)
	{
		if($this->off)
			return Error();

		if(isset($post['reverse']))
			$this->reverse=(bool)$post['reverse'];
		if(isset($post['upref']))
			$this->upref=(string)$post['upref'];
		if(!isset($this->baseurl) and isset($post['baseurl']))
			$this->baseurl=(string)$post['baseurl'];

		list($uid,$where,$mid)=$this->GetUWM($id,$mid);
		$lang=Eleanor::$Language['comments'];
		$parent=isset($post['parent']) ? (int)$post['parent'] : false;
		if($parent)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`status`,`parents`,`answers`,`author_id` FROM `'.$this->table.'` WHERE '.$where.' AND `id`='.$parent.' LIMIT 1');
			if($parent=$R->fetch_assoc())
			{
				if($parent['status']!=1)
					$this->rights['post']=false;
				$out=!$this->rights['status'] && ($parent['status']==0 || $parent['status']==-1 && ($uid && $parent['author_id']!=$uid || !$uid && !in_array($parent['id'],$this->cs)));
				if(!$out and $parent['parents'])
				{
					$R=Eleanor::$Db->Query('SELECT `status`,`author_id` FROM `'.$this->table.'` WHERE '.$where.' AND `id`IN('.rtrim($parent['parents'],',').')');
					if($R->num_rows==0)
						$out=true;
					else
						while($a=$R->fetch_assoc())
						{
							if($a['status']!=1)
								$this->rights['post']=false;
							if(!$this->rights['status'] and ($a['status']==0 or $a['status']==-1 and ($uid and $a['author_id']!=$uid or !$uid and !in_array($a['id'],$this->cs))))
							{
								$out=true;
								break;
							}
						}
				}
				if($out)
				{
					Error();
					return false;
				}
				$where.=' AND `parents` LIKE \''.$parent['parents'].$parent['id'].',%\'';
			}
			else
			{
				Error();
				return false;
			}
		}

		$ev=isset($post['event']) ? $post['event'] : false;
		switch($ev)
		{
			case'post':
				if($this->rights['post']===false)
				{
					Error();
					return false;
				}
				$TC=new TimeCheck($mid);
				$ch=$TC->Check('add',false);
				if($ch)
				{
					Error($lang['flood_limit']($ch['_datets']-time()));
					return false;
				}

				OwnBB::$replace['quote']='CommentsQoute';
				if(!class_exists('CommentsQoute',false))
					include Eleanor::$root.'core/others/comments/ownbb-quote.php';
				$THIS=$this;#PHP 5.4 Убрать этот костыль
				CommentsQoute::$findlink=function($id) use ($THIS){ return$THIS->Url(array($this->upref.'find'=>$id)); };

				$El=Eleanor::getInstance();
				$text=isset($post['text']) ? $El->Editor_result->GetHtml($post['text'],true) : '';
				if(mb_strlen($text)<5)
				{
					Error();
					return false;
				}
				if($uid)
					$name=Eleanor::$Login->GetUserValue('name');
				else
				{
					$name=isset($post['name']) ? htmlspecialchars((string)$post['name'],ELENT,CHARSET) : false;
					if(!$name)
					{
						Error();
						return false;
					}
					Eleanor::SetCookie($this->gc.'-name',$name);
				}
				$cach=$El->Captcha->Check(isset($post['captcha']['check']) ? (string)$post['captcha']['check'] : '','captcha',isset($post['captcha']) ? $post['captcha'] : array());
				$El->Captcha->Destroy();
				if(!$cach)
				{
					Error(array('error'=>$lang['WRONG_CAPTCHA'],'captcha'=>true));
					break;
				}
				$R=Eleanor::$Db->Query('SELECT `id`,`date`,`author_id`,`text` FROM `'.$this->table.'` WHERE '.$where.' AND `status`='.$this->rights['post'].' ORDER BY `sortdate` DESC LIMIT 1');
				if($last=$R->fetch_assoc() and ($uid and $last['author_id']==$uid or !$uid and in_array($last['id'],$this->cs)))
				{
					$now=preg_split('#\D+#',date('Y-m-d H:i:s'));
					$diff=preg_split('#\D+#',$last['date']);
					$corr=array(0,12,idate('t',mktime(1,1,1,$diff[1],1,$diff[0])),24,60,60);
					foreach($diff as $k=>&$v)
					{
						$v=$now[$k]-$v;
						if($v<0)
						{
							for($i=$k-1;$i>=0;$i--)
								if($diff[$i]==0)
									$diff[$i]=$corr[$i]-1;
								else
								{
									$diff[$i]--;
									break;
								}
							$v+=$corr[$k];
						}
					}
					$last['text'].=Eleanor::$Template->CommentsAddedAfter($diff).$text;
					Eleanor::$Db->Update($this->table,array('text'=>$last['text']),'`id`='.$last['id'].' LIMIT 1');
					$merged=true;
				}
				else
				{
					if($parent)
						$parent['answers']++;
					$merged=false;
					$cid=Eleanor::$Db->Insert(
						$this->table,
						($this->ut ? array('module'=>$mid) : array())+array(
							'contid'=>$id,
							'status'=>$this->rights['post'],
							'!sortdate'=>'NOW()',
							'parent'=>$parent ? $parent['id'] : 0,
							'parents'=>$parent ? $parent['parents'].$parent['id'].',' : '',
							'!date'=>'NOW()',
							'author_id'=>$uid,
							'author'=>$name,
							'ip'=>Eleanor::$ip,
							'text'=>$text,
						)
					);
					if($this->rights['post']==1 and $parent)
						Eleanor::$Db->Update($this->table,array('!answers'=>'`answers`+1'),'`id`='.$parent['id'].' LIMIT 1');
					if($flood=Eleanor::$Permissions->FloodLimit())
						$TC->Add('add','',true,$flood);
					if(!$uid)
						$this->GuestSign($cid);
				}

				$st=$this->GetStatuses($where,$uid);
				$cnt=$this->rights['post']==-1 ? array_sum($st) : $st[0]+$st[1];
				if($this->reverse)
				{
					$np=$cnt % $this->pp;
					if($this->rights['post']!=-1 and $st[-1]+$np>$this->pp)
						$np=0;
				}
				else
					$np=0;
				$page=max(ceil($cnt/$this->pp)-($np>0 ? 1 : 0),1);

				if($merged)
				{
					Result(array(
						'merged'=>$last['id'],
						'text'=>OwnBB::Parse($last['text']),
						'page'=>$page,
					));
					return array('merged'=>true,'event'=>$ev);
				}

				if(empty($post['loadcomments']))
				{
					Result(array(
						'gotopage'=>$page,
						'cid'=>$cid,
					));
					return array('merged'=>false,'event'=>$ev);
				}
			case'lnc':
				$lastpost=isset($post['lastpost']) ? (int)$post['lastpost'] : 0;
				$nextn=isset($post['nextn']) ? (int)$post['nextn'] : 1;
				$where.=' AND `sortdate`>FROM_UNIXTIME('.$lastpost.')';
				$st=$this->GetStatuses($where,$uid);
				if(array_sum($st)==0)
				{
					Result(false);
					break;
				}
				$rparent=isset($post['rparent']) ? (int)$post['rparent'] : false;
				$parents=$parent && $parent['parents'] ? explode(',',rtrim($parent['parents'],',')) : array();
				if($parent and $rparent==$parent['id'])
					$qpinch=$parents+array('l'=>$parent['id']);
				else
					$qpinch=in_array($rparent,$parents) ? $parents : array();

				$pagpq=$this->GetPAGPQ($where,$st,0,$this->pp,$this->reverse ? $nextn+min($this->pp,$st[1]+$st[0]) : $nextn,false,$uid,$qpinch);
				if(!$pagpq[0])
				{
					Result(false);
					break;
				}
				$this->reverse ? end($pagpq[0]) : reset($pagpq[0]);
				$firstid=key($pagpq[0]);

				$cnt=count($pagpq[0]);
				if($cnt<$this->pp)
					$lastpost=time();
				else
				{
					$lastpost=$this->reverse ? reset($pagpq[0]) : end($pagpq[0]);
					$lastpost=strtotime($lastpost['sortdate']);
				}
				Result(array(
					'template'=>Eleanor::$Template->CommentsLNC($this->rights,$pagpq),
					'lastpost'=>$lastpost,
					'first'=>$firstid,
					'sortdate'=>$lastpost,
					'nextn'=>$nextn+max(0,$cnt-$st[-1]),
				)+($parent ? array('answers'=>$parent['answers'],'parent'=>$parent['id']) : array()));
				return array('merged'=>false,'event'=>$ev);
			break;
			case'page':
				$st=$this->GetStatuses($where,$uid);
				$cnt=array_sum($st);

				$pspol=$this->CalcOffsetPage($post,$cnt);#list($pages,$page,$offset,$limit)
				$ps=isset($post['pages']) ? (int)$post['pages'] : 0;
				if(!empty($post['nochangepages']) and $pspol[0]>$ps)
				{
					$pspol[0]=$ps;
					$cnt=$ps*$this->pp;
				}

				if(empty($post['onlypages']))
				{
					$actcnt=$st[0]+$st[1];
					$pagpq=$this->GetPAGPQ($where,$st,$pspol[2],$this->reverse ? $pspol[3] : $actcnt,$this->reverse ? $actcnt-max(0,$pspol[2]-$st[-1]) : $pspol[2],false,$uid);
				}
				else
					$pagpq=false;

				$THIS=$this;#PHP 5.4
				$links=array(
					'first_page'=>$this->Url(),
					'pages'=>function($n)use($THIS){ return$THIS->Url(array($THIS->upref.'page'=>$n)); },
				);

				Result(array(
					'cnt'=>$cnt,
					'url'=>$this->Url(array($this->upref.'page'=>$this->reverse && $pspol[1]==$pspol[0] || !$this->reverse && $pspol[1]==1 ? false : $pspol[1])),
					'page'=>$pspol[1],
					'pages'=>$pspol[0],
					'template'=>Eleanor::$Template->CommentsLoadPage($this->rights,$pagpq,$cnt,$this->pp,$pspol[0],$pspol[1],$parent,$links),
				));
			break;
			case'delete':
				$ids=isset($post['ids']) ? (array)$post['ids'] : array();
				if(!$ids)
				{
					Error();
					return false;
				}
				$R=Eleanor::$Db->Query('SELECT `id`,`status`,`parent`,`parents`,`date`,`author_id` FROM `'.$this->table.'` WHERE `id`'.Eleanor::$Db->In($ids).' AND '.$where.' LIMIT '.($this->pp*2));
				$qids=$qdels=$ids=$pids=$pupd=array();
				while($a=$R->fetch_assoc())
				{
					list(,$can)=$this->CanEditDel($a,$uid);
					if($can)
					{
						$ids[]=$a['id'];
						$pids[]=$a['parents'].$a['id'].',';
					}
					if($a['status']==1)
						$pupd[$a['parent']]=isset($pupd[$a['parent']]) ? $pupd[$a['parent']]+1 : 1;
				}
				if(!$ids)
				{
					Error();
					return false;
				}
				foreach($pids as &$v)
				{
					$qids[]='(SELECT `id` FROM `'.$this->table.'` WHERE `parents` LIKE \''.$v.'%\')';
					$qdels[]='`parents` LIKE \''.$v.'%\'';
				}
				Eleanor::$Db->Transaction();
				$R=Eleanor::$Db->Query(join('UNION ALL',$qids));
				while($t=$R->fetch_row())
					$ids[]=$t[0];
				$sum=Eleanor::$Db->Delete($this->table,'`id`'.Eleanor::$Db->In($ids));
				foreach($qdels as &$v)
					$sum+=Eleanor::$Db->Delete($this->table,$v);
				foreach($pupd as $k=>&$v)
					Eleanor::$Db->Update($this->table,array('!answers'=>'GREATEST(0,`answers`-'.$v.')'),'`id`='.$k.' LIMIT 1');
				Eleanor::$Db->Commit();
				$r=array(
					'ids'=>$ids,
					'deleted'=>$sum,
				);
				Result($r);
				return$r+array('event'=>$ev);
			break;
			case'moderate':
				$ids=isset($post['ids']) ? (array)$post['ids'] : array();
				$status=isset($post['status']) ? (int)$post['status'] : array();
				if(!$this->rights['status'] or !$ids or !in_array($status,array(-1,0,1)))
				{
					Error();
					return false;
				}
				$act=$status==1;
				$pars=',';
				$R=Eleanor::$Db->Query('SELECT `id`,`status`,`parent`,`parents` FROM `'.$this->table.'` WHERE `id`'.Eleanor::$Db->In($ids).' AND '.$where.' LIMIT '.($this->pp*2));
				$ids=$sids=$pupd=$addids=array();
				while($a=$R->fetch_assoc())
				{
					$pars.=$a['parents'];
					if(strpos($pars,','.$a['id'].',')===false)
						$addids[]=$a['parents'].$a['id'].',';
					$ids[]=$a['id'];
					$sids[$a['status']][]=$a['id'];
					if($act and $a['status']!=1)
						$pupd[$a['parent']]=isset($pupd[$a['parent']]) ? $pupd[$a['parent']]+1 : 1;
					elseif(!$act and $a['status']==1)
						$pupd[$a['parent']]=0;
				}

				foreach($addids as &$v)
				{
					$R=Eleanor::$Db->Query('SELECT `id`,`status`,`parent` FROM `'.$this->table.'` WHERE `parents` LIKE \''.$v.'%\' AND `id`'.Eleanor::$Db->In($ids,true));
					while($a=$R->fetch_assoc())
					{
						$ids[]=$a['id'];
						$sids[$a['status']][]=$a['id'];
						if($act and $a['status']!=1)
							$pupd[$a['parent']]=isset($pupd[$a['parent']]) ? $pupd[$a['parent']]+1 : 1;
						elseif(!$act and $a['status']==1)
							$pupd[$a['parent']]=0;
					}
				}

				$affected=0;
				Eleanor::$Db->Transaction();
				foreach($sids as $k=>&$v)
				{
					$in=Eleanor::$Db->In($v);
					if($k==-1)
					{
						if($act)
						{
							$R=Eleanor::$Db->Query('SELECT `date` FROM `'.$this->table.'` WHERE `id`'.$in.' ORDER BY `sortdate` ASC LIMIT 1');
							if($a=$R->fetch_assoc())
								$affected+=Eleanor::$Db->Update($this->table,array('!date'=>'FROM_UNIXTIME('.(time()-strtotime($a['date'])).'+UNIX_TIMESTAMP(`date`))','status'=>1),'`id`'.$in);
						}
						elseif($status==0)
							$affected+=Eleanor::$Db->Update($this->table,array('status'=>0),'`id`'.$in);
					}
					else
						$affected+=Eleanor::$Db->Update($this->table,array('status'=>$status),'`id`'.$in);
				}
				foreach($pupd as $k=>&$v)
					Eleanor::$Db->Update($this->table,$v>0 ? array('!answers'=>$act ? '`answers`+'.$v : 'GREATEST(0,`answers`-'.$v.')') : array('answers'=>0),'`id`='.$k.' LIMIT 1');
				Eleanor::$Db->Commit();
				Result('ok');
				return array('activated'=>$act ? $affected : -$affected,'event'=>$ev);
			break;
			case'edit':
				$id=isset($post['id']) ? (int)$post['id'] : 0;
				$R=Eleanor::$Db->Query('SELECT `id`,`status`,`date`,`author_id`,`author`,`text` FROM `'.$this->table.'` WHERE `id`='.$id.' AND '.$where.' LIMIT 1');
				if(!$a=$R->fetch_assoc() or !list($can)=$this->CanEditDel($a,$uid) or !$can)
				{
					Error();
					return false;
				}
				Result(Eleanor::$Template->CommentsEdit($a));
			break;
			case'save':
				$id=isset($post['id']) ? (int)$post['id'] : false;

				OwnBB::$replace['quote']='CommentsQoute';
				if(!class_exists('CommentsQoute',false))
					include Eleanor::$root.'core/others/comments/ownbb-quote.php';
				$THIS=$this;#PHP 5.4 Убрать этот костыль
				CommentsQoute::$findlink=function($id) use ($THIS){ return$THIS->Url(array($this->upref.'find'=>$id)); };

				$El=Eleanor::getInstance();
				$text=isset($post['text'.$id]) ? $El->Editor_result->GetHtml($post['text'.$id],true) : '';
				if(mb_strlen($text)<5 or !$id)
				{
					Error();
					return false;
				}
				$R=Eleanor::$Db->Query('SELECT `id`,`status`,`parents`,`date`,`author_id` FROM `'.$this->table.'` WHERE `id`='.$id.' AND '.$where.' LIMIT 1');
				if(!$a=$R->fetch_assoc() or !list($can)=$this->CanEditDel($a,$uid) or !$can)
				{
					Error();
					return false;
				}
				Eleanor::$Db->Update($this->table,array('text'=>$text),'`id`='.$a['id'].' LIMIT 1');

				if($parent)
				{
					$parents=$parent['parents'] ? explode(',',rtrim($parent['parents'],',')) : array();
					$parents[]=$parent['id'];
				}
				else
					$parents=array();
				$quotes=$a['parents'] ? explode(',',rtrim($a['parents'],',')) : array();
				if($parents)
					$quotes=array_diff($quotes,$parents);

				if($quotes)
				{
					if($uid)
						$wq='(`status`=1 OR `status`=-1 AND `author_id`='.$uid.')';
					elseif($this->cs)
						$wq='(`status`=1 OR `status`=-1 AND `id`'.Eleanor::$Db->In($this->cs).')';
					else
						$wq='`status`=1';
					$R=Eleanor::$Db->Query('SELECT `id`,`date`,`author`,`text` FROM `'.$this->table.'` WHERE `id`'.Eleanor::$Db->In($quotes).($this->rights['status'] ? '' : ' AND '.$wq).' ORDER BY `id` ASC');
					$quotes=array();
					while($a=$R->fetch_assoc())
						$quotes[$a['id']]=OwnBB::Parse('[quote date="'.$a['date'].'" name="'.$a['author'].'" c='.$a['id'].']<!-- SUBQUOTE -->'.$a['text'].'[/quote]');
				}
				$text=OwnBB::Parse($text);

				Result(Eleanor::$Template->CommentsAfterEdit($text,$quotes));
			break;
			default:
				Error();
				return false;
		}
		return array('event'=>$ev);
	}
}