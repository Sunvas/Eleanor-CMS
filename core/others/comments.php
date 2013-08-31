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

class Comments extends BaseClass
{
	public
		$reverse=false,#Флаг обратного порядка сортировки комментариев
		$pp=10,#Количество комментариев на страницу
		$off=false,#Флаг выключения комментариев
		$template='Comments',#Имя класса оформления комментариев

		$baseurl,#Базовый URL страницы, где находятся комментарии
		$upref='c',#Префикс для параметров в GET запросах для комментариев (при переходе со страницы на страницу)
		$gc='gc',#Имя куки для идентификации гостя
		$gs=false,#Уникальная строчка для подписи гостевой куки

		$rights=array(#Права пользователя. Эти права можно "нарисовать" в табличке внизу страницы
			'edit'=>true,#Право редактировать свои комментарии. Если число - это количество секунд по истечению которых право теряется (считая от времени создания комментария).
			'delete'=>true,#Право удалять свои комментарии. Если число - это количество секунд по истечению которых право теряется (считая от времени создания комментария).
			'post'=>1,#Право создавать новые комментарии, свойство определяет статус новых комментариев: -1 - для перемодерации, 0 - для блокировки, 1 - без премодерации, false - для запрета публикации
			'medit'=>false,#Право редактировать чужие комментарии
			'mdelete'=>false,#Право удалять чужие комментарии
			'ip'=>false,#Право просматривать IP с которых были отправлены комментарии
			'status'=>false,#Право менять статусы постов
		);

	protected
		$table,#Таблица комментариев
		$ut=true,#Признак того, что мы работаем с общей таблицей комментариев, где есть поле module
		$cs;#Смотри GuestSign

	/**
	 * Конструктор комментариев
	 *
	 * @param string $table Таблица комментариев
	 * @param string Признак того, что мы работаем с общей таблицей комментариев, где есть поле module
	 */
	public function __construct($table=false,$ut=true)
	{
		if($table===false)
			$table=P.'comments';
		$this->table=$table;
		$this->ut=$ut;

		if(!isset(Eleanor::$vars['comments_pp']))
			Eleanor::LoadOptions('comments');
		$ug=Eleanor::GetUserGroups();
		$this->pp=(int)Eleanor::$vars['comments_pp'];
		$this->reverse=Eleanor::$vars['comments_sort'];
		$this->rights['edit']=$this->rights['delete']=Eleanor::$vars['comments_timelimit'];
		$this->off=!array_intersect($ug,Eleanor::$vars['comments_display_for']);
		$this->rights['post']=(bool)array_intersect($ug,Eleanor::$vars['comments_post_for']);
		$this->rights['status']=$this->rights['medit']=$this->rights['mdelete']=$this->rights['ip']=Eleanor::$Permissions->IsAdmin();
		if($this->rights['post'])
			$this->rights['post']=Eleanor::$Permissions->Moderate() ? -1 : 1;
	}

	/**
	 * Получение интерфейса комментирования
	 *
	 * @param string $id Идентификатор за которым будут закреплены комментарии. Обычно это идентификатор контентины модуля.
	 * @param string|FALSE $postquery Этот массив параметров будет передан в POST запросе при AJAX запросе
	 * @param string|FALSE $dataquery Очередность ключей $_POST массива, в которых будет передано содержимое AJAX запроса
	 * @param int|FALSE $mid ID модуля
	 */
	public function Show($id,$postquery=false,$mid=false,$dataquery=array('comments'))
	{
		if($this->off)
			return'';
		$El=Eleanor::getInstance();

		if($postquery===false)
			$postquery=isset($El->module['name']) ? array('event'=>'comments','module'=>$El->module['name'],'id'=>$id) : array();

		list($uid,$where)=$this->GetUWM($id,$mid);
		$q=$this->GET(true);
		$parent=isset($q['parent']) ? (int)$q['parent'] : false;
		if($parent)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`status`,`parents`,`date`,`answers`,`author_id`,`author`,'.($this->rights['ip'] ? '`ip`,' : '').'`text` FROM `'.$this->table.'` WHERE '.$where.' AND `id`='.$parent.' LIMIT 1');
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
					ExitPage();
					return false;
				}
				$where.=' AND `parents` LIKE \''.$parent['parents'].$parent['id'].',%\'';
			}
			else
			{
				GoAway($this->Url());
				return false;
			}
		}
		$st=$this->GetStatuses($where,$uid);
		$cnt=array_sum($st);

		if(isset($q['find']))
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`status`,`sortdate`,`author_id` FROM `'.$this->table.'` WHERE '.$where.' AND `id`='.(int)$q['find'].' LIMIT 1');
			if($a=$R->fetch_assoc())
				switch($a['status'])
				{
					case 1:
						$R=Eleanor::$Db->Query('SELECT COUNT(`contid`) `cnt` FROM `'.$this->table.'` WHERE '.$where.' AND `status`'.($this->rights['status'] ? '>=0' : '=1').' AND `sortdate`<\''.$a['sortdate'].'\'');
						list($a['cnt'])=$R->fetch_row();
						if($this->reverse)
							$a['cnt']+=$st[-1];
					break;
					case -1:
						if(!$this->rights['status'] and ($uid and $a['author_id']!=$uid or !$uid and !in_array($a['id'],$this->cs)))
						{
							$a=false;
							break;
						}
						if($this->rights['status'])
							$w='';
						else
							$w=' AND '.($uid ? '`author_id`='.$uid : '`id`'.Eleanor::$Db->In($this->cs));
						$R=Eleanor::$Db->Query('SELECT COUNT(`contid`) `cnt` FROM `'.$this->table.'` WHERE '.$where.' AND `status`=-1 AND `sortdate`<\''.$a['sortdate'].'\''.$w);
						list($a['cnt'])=$R->fetch_row();
						if(!$this->reverse)
							$a['cnt']+=$st[1]+$st[0];
					break;
					default:
						if(!$this->rights['status'])
						{
							$a=false;
							break;
						}
						$R=Eleanor::$Db->Query('SELECT COUNT(`contid`) `cnt` FROM `'.$this->table.'` WHERE '.$where.' AND `status`>=0 AND `sortdate`<\''.$a['sortdate'].'\'');
						list($a['cnt'])=$R->fetch_row();
				}
			if($a)
			{
				$page=(int)($a['cnt']/$this->pp)+1;
				if($this->reverse)
					$page=$page==((int)($cnt/$this->pp)+1) ? false : $page;
				else
					$page=$page>1 ? $page : false;
				GoAway($this->Url(array($this->upref.'page'=>$page)),301,'comment'.$a['id']);
			}
			else
				GoAway($this->Url());
			return false;
		}

		$pspol=$this->CalcOffsetPage($q,$cnt);#list($pages,$page,$offset,$limit)

		$actcnt=$st[0]+$st[1];
		$pagpq=$this->GetPAGPQ($where,$st,$pspol[2],$this->reverse ? $pspol[3] : $actcnt,$this->reverse ? $actcnt-max(0,$pspol[2]-$st[-1]) : $pspol[2],$parent,$uid,$parent ? array($parent['id']) : array());

		$THIS=$this;#PHP 5.4
		$links=array(
			'first_page'=>$this->Url(),
			'pages'=>function($n)use($THIS){ return$THIS->Url(array($THIS->upref.'page'=>$n)); },
		);
		return Eleanor::$Template->ShowComments($this->rights,$pagpq,$postquery,$dataquery,$cnt,$this->pp,$pspol[1],$pspol[0],$st,$uid ? false : (string)Eleanor::GetCookie($this->gc.'-name'),$El->Captcha->disabled ? false : $El->Captcha->GetCode(),$links);
	}

#Служебное
	/**
	 * Получение числа страниц, страницы и смещения для секции LIMIT в запросе извлечения комментариев
	 *
	 * @param array $get Массив с GET запросом для комментариев
	 * @param int $cnt Количество комментариев
	 * @return array $pages,$page,$offset,$limit
	 */
	protected function CalcOffsetPage(array$get,$cnt)
	{
		$limit=$this->pp;
		if($this->reverse)
		{
			$np=$cnt % $this->pp;
			$pages=max(ceil($cnt/$this->pp)-($np>0 ? 1 : 0),1);
			$page=isset($get['page']) ? (int)$get['page'] : $pages;
			if($page<1)
				$page=$pages;
			$intpage=$pages - $page + 1;
			$offset=max(0,$intpage-1)*$this->pp;

			if($offset==0)
				$limit+=$np;
			else
				$offset+=$np;
			$page=-$page;
		}
		else
		{
			$pages=$cnt>0 ? ceil($cnt/$this->pp) : 1;
			$page=isset($get['page']) ? (int)$get['page'] : 1;
			if($page<1)
				$page=1;
			$offset=($page-1)*$this->pp;
			$np=0;
		}
		if($cnt and $offset>=$cnt)
			$offset=max(0,$cnt-$limit);
		return array($pages,$page,$offset,$limit);
	}

	/**
	 * Получение количества комментариев каждого статуса отдельно
	 *
	 * @param string $w WHERE секция SQL запроса по извлечению комментариев без ключевого слова WHERE
	 * @param int $uid ID пользователя
	 */
	protected function GetStatuses($w,$uid)
	{
		$st=array(-1=>0,0,0);
		if($this->rights['status'])
		{
			$R=Eleanor::$Db->Query('SELECT `status`,COUNT(`status`) `cnt` FROM `'.$this->table.'` WHERE '.$w.' GROUP BY `status`');
			while($a=$R->fetch_row())
				$st[$a[0]]=$a[1];
		}
		else
		{
			$fq='SELECT COUNT(`status`) `cnt` FROM `'.$this->table.'` WHERE '.$w.' AND `status`=1';
			if($uid)
				$lq='SELECT COUNT(`status`) `cnt` FROM `'.$this->table.'` WHERE '.$w.' AND `status`=-1 AND `author_id`='.$uid;
			elseif($this->cs)
				$lq='SELECT COUNT(`id`) `cnt` FROM `'.$this->table.'` WHERE '.$w.' AND `status`=-1 AND `id`'.Eleanor::$Db->In($this->cs);
			else
				$lq=false;
			$R=Eleanor::$Db->Query($lq ? '('.$fq.')UNION ALL('.$lq.')' : $fq);
			list($st[1])=$R->fetch_row();
			if($a=$R->fetch_row())
				$st[-1]=$a[0];
		}
		return$st;
	}

	/**
	 * Получение постов, пользователей, групп, данных комментария-родителя, цитат
	 *
	 * @param string $where WHERE секция SQL запроса по извлечению комментариев без ключевого слова WHERE
	 * @param array $st Количество комментариев каждого статуса отдельно (возвращает метод GetStatuses)
	 * @param int $offset Отступ выборки комментариев
	 * @param int $limit Предел выборки
	 * @param int $cbn Начальный номер комментария +1
	 * @param int $parent ID комментария-родителя
	 * @param int $uid ID пользователя
	 * @param array $qpinch Массив идентификаторов комментарив, цитаты которых не нужно отображать
	 */
	protected function GetPAGPQ($where,$st,$offset,$limit,$cbn,$parent,$uid,array$qpinch=array())
	{
		$lq=$fq='';
		if($this->reverse)
		{
			if($offset<$st[-1])
			{
				if($uid)
					$fq=' AND `status`=-1'.($this->rights['status'] ? '' : ' AND `author_id`='.$uid).' ORDER BY `sortdate` DESC LIMIT '.$offset.','.min($limit,$st[-1]-$offset);
				elseif($this->cs)
					$fq=' AND `status`=-1 AND `id`'.Eleanor::$Db->In($this->cs).' ORDER BY `sortdate` DESC LIMIT '.$offset.','.min($this->pp,$st[-1]-$offset);
			}
			if($offset+$this->pp>=$st[-1])
				$lq=' AND `status`'.($this->rights['status'] ? '>=0' : '=1').' ORDER BY `sortdate` DESC LIMIT '.max(0,$offset-$st[-1]).','.min($limit,$offset+$limit-$st[-1]);
		}
		else
		{
			$actcnt=$st[0]+$st[1];
			if($offset+$this->pp>$actcnt)
			{
				if($uid)
					$lq=' AND `status`=-1'.($this->rights['status'] ? '' : ' AND `author_id`='.$uid).' ORDER BY `sortdate` ASC LIMIT '.max(0,$offset-$actcnt).','.min($this->pp,$offset+$this->pp-$actcnt);
				elseif($this->cs)
					$lq=' AND `status`=-1 AND `id`'.Eleanor::$Db->In($this->cs).' ORDER BY `sortdate` ASC LIMIT '.max(0,$offset-$actcnt).','.min($actcnt,$offset+$this->pp-$actcnt);
			}
			if($offset<=$actcnt)
				$fq=' AND `status`'.($this->rights['status'] ? '>=0' : '=1').' ORDER BY `sortdate` ASC LIMIT '.$offset.','.$this->pp;
		}

		$posts=$authors=$groups=$quotes=array();
		$ip=$this->rights['ip'] ? '`ip`,' : '';

		$oldqr=isset(OwnBB::$replace['quote']) ? OwnBB::$replace['quote'] : null;
		OwnBB::$replace['quote']='CommentsQoute';
		if(!class_exists('CommentsQoute',false))
			include Eleanor::$root.'core/others/comments/ownbb-quote.php';

		$THIS=$this;#PHP 5.4 Убрать этот костыль
		CommentsQoute::$findlink=function($id) use ($THIS){ return$THIS->Url(array($THIS->upref.'find'=>$id)); };

		if($parent)
		{
			if($parent['author_id'])
				$authors[]=$parent['author_id'];
			$parent['parents']=$parent['parents'] ? explode(',',rtrim($parent['parents'],',')) : array();
			if(isset($parent['text']))
			{
				list($parent['_edit'],$parent['_delete'])=$this->CanEditDel($parent,$uid);
				$parent['_afind']=$this->Url(array($this->upref.'find'=>$parent['id']));
				$parent['_n']=$parent['_achilden']=false;
				$parent['text']=OwnBB::Parse($parent['text']);
			}
		}

		$q='SELECT `id`,`status`,`parents`,`sortdate`,`date`,`answers`,`author_id`,`author`,'.$ip.'`text` FROM `'.$this->table.'` WHERE ';
		if($fq)
			$fq=$q.$where.$fq;
		if($lq)
			$lq=$q.$where.$lq;
		$R=Eleanor::$Db->Query($fq && $lq ? '('.$fq.')UNION ALL('.$lq.')' : $fq.$lq);
		while($a=$R->fetch_assoc())
		{
			if($a['author_id'])
				$authors[]=$a['author_id'];
			$a['parents']=$a['parents'] ? explode(',',rtrim($a['parents'],',')) : array();
			if($a['parents'])
				$quotes=array_merge($quotes,$a['parents']);
			$a['text']=OwnBB::Parse($a['text']);
			if($a['status']==-1)
				$a['_n']='?';
			else
				$a['_n']=$this->reverse ? $cbn-- : ++$cbn;
			$a['_afind']=$this->Url(array($this->upref.'find'=>$a['id']));
			$a['_achilden']=$a['answers']>0 ? $this->Url(array($this->upref.'parent'=>$a['id'])) : false;
			list($a['_edit'],$a['_delete'])=$this->CanEditDel($a,$uid);
			$posts[$a['id']]=array_slice($a,1);
		}

		if($qpinch)
			$quotes=array_diff($quotes,$qpinch);

		if($quotes)
		{
			if($uid)
				$wq='(`status`=1 OR `status`=-1 AND `author_id`='.$uid.')';
			elseif($this->cs)
				$wq='(`status`=1 OR `status`=-1 AND `id`'.Eleanor::$Db->In($this->cs).')';
			else
				$wq='`status`=1';
			$R=Eleanor::$Db->Query('SELECT `id`,`date`,`author`,`text` FROM `'.$this->table.'` WHERE `id`'.Eleanor::$Db->In($quotes).($this->rights['status'] ? '' : ' AND '.$wq));
			$quotes=array();
			while($a=$R->fetch_assoc())
				$quotes[$a['id']]=OwnBB::Parse('[quote date="'.$a['date'].'" name="'.$a['author'].'" c='.$a['id'].']<!-- SUBCOMMENT -->'.$a['text'].'[/quote]');
		}
		OwnBB::$replace['quote']=$oldqr;

		if($authors)
		{
			$lcl=get_class(Eleanor::$Login);
			$R=Eleanor::$Db->Query('SELECT `id`,`groups` `_group`,`login_keys`,`name`,`signature`,`avatar_location`,`avatar_type` FROM `'.P.'users_site` INNER JOIN `'.P.'users_extra` USING (`id`) WHERE `id`'.Eleanor::$Db->In($authors));
			$authors=array();
			$t=time();
			while($a=$R->fetch_assoc())
			{
				if(isset($party[$a['id']]))
					$a['_party']=true;
				$a['_group']=explode(',,',trim($a['_group'],','));
				$groups[]=$a['_group']=(int)reset($a['_group']);
				$a['login_keys']=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
				$a['_online']=false;
				if(isset($a['login_keys'][$lcl]))
					foreach($a['login_keys'][$lcl] as &$v)
						if($v[0]>$t)
						{
							$a['_online']=true;
							break;
						}
				unset($a['login_keys']);
				$authors[$a['id']]=array_slice($a,1);
			}

			if($groups)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
				$groups=array();
				while($a=$R->fetch_assoc())
				{
					$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
					$groups[$a['id']]=array_slice($a,1);
				}
			}
		}
		return array($posts,$authors,$groups,$parent,$quotes);
	}

	/**
	 * Определение возможности править и удалять каждый конкретный комментарий
	 *
	 * @param array $a Дамп-массив конкретного комментария
	 * @param int $uid ID пользователя
	 */
	protected function CanEditDel(array$a,$uid)
	{
		$e=$this->rights['medit'];
		$d=$this->rights['mdelete'];
		if($a['author_id']==$uid and $uid or !$uid and in_array($a['id'],$this->cs))
		{
			$tl=time()-strtotime($a['date']);
			if(!$e)
				$e=is_bool($this->rights['edit']) ? ($this->rights['edit'] ? true : $e) : ($tl<$this->rights['edit'] ? $this->rights['edit']-$tl : $e);
			if(!$d)
				$d=is_bool($this->rights['delete']) ? ($this->rights['delete'] ? true : $d) : ($tl<$this->rights['delete'] ? $this->rights['delete']-$tl : $e);
		}
		return array($e,$d);
	}

	/**
	 * Получения всех GET параметров, относящихся к комментариям. Определение производится по наличию префикса $this->upref в каждом ключе GET запроса
	 *
	 * @param bool $cut Указывает обрезать ли префиксы в возвращаемом массиве
	 */
	public function GET($cut=false)
	{
		$r=array();
		$l=strlen($this->upref);
		foreach($_GET as $k=>&$v)
			if(strpos($k,$this->upref)===0)
				$r[$cut ? substr($k,$l) : $k]=$v;
		return$r;
	}

	/**
	 * Генерация ссылок для интерфейса комментарие
	 *
	 * @param array $u Массив параметров ссылки
	 */
	protected function Url(array$u=array())
	{
		if(!$this->baseurl)
		{
			$this->baseurl=ltrim($_SERVER['QUERY_STRING'],'!');
			$this->baseurl=preg_replace('#([^/=&\?]+)(/|\?|=|&|$)#e','urlencode(\'\1\').\'\2\';',$this->baseurl);
			if(0===$pling=strpos($_SERVER['QUERY_STRING'],'!') and false!==$ap=strpos($this->baseurl,'&'))
				$this->baseurl=substr_replace($this->baseurl,'?',$ap,1);
			elseif($pling===false)
				$this->baseurl=Eleanor::getInstance()->Url->file.($this->baseurl ? '?'.$this->baseurl : '');
			$this->baseurl=rtrim(preg_replace('#'.preg_quote($this->upref).'([a-z0-9]+)=.+?(&|$)#','',$this->baseurl),'?');
		}
		elseif(is_array($this->baseurl))
		{
			foreach($u as $k=>&$v)
				$v=array($k=>$v);
			return Eleanor::getInstance()->Url->Construct($this->baseurl+$u,false);
		}
		$u=$u ? $this->baseurl.(strpos($this->baseurl,'?')===false ? '?' : '&amp;').Url::Query($u) : $this->baseurl;
		return rtrim($u,'?&');
	}

	/**
	 * Запись / считывание ID комментариев, которые принадлежат гостю (записывается в массив $this->cs)
	 *
	 * @param bool $add Флаг записи комментариев (хранятся в куках)
	 */
	protected function GuestSign($add=false)
	{
		if(!isset($this->cs))
		{
			$gc=Eleanor::GetCookie($this->gc);
			$gcs=Eleanor::GetCookie($this->gc.'-s');
			$this->cs=$gc && $gcs && $gcs===md5($gc.$this->gs) ? explode(',',$gc) : array();
		}
		if($add and !in_array($add,$this->cs))
		{
			$this->cs[]=$add;
			$this->cs=array_slice($this->cs,-30);

			sort($this->cs,SORT_NUMERIC);
			$gc=join(',',$this->cs);
			Eleanor::SetCookie($this->gc,$gc);
			Eleanor::SetCookie($this->gc.'-s',md5($gc.$this->gs));
		}
	}

	/**
	 * Получение ID пользователя, секции WHERE для SQL запроса по извлечению комментариев без слова WHERE и ID модуля
	 *
	 * @param string $id Идентификатор, за которым закреплены комментарии
	 * @param int $mid ID модуля
	 */
	protected function GetUWM($id,$mid=false)
	{
		if(Eleanor::$Login->IsUser())
			$uid=(int)Eleanor::$Login->GetUserValue('id');
		else
		{
			if($this->gs===false)
				$this->gs=md5(__file__);
			$this->GuestSign();
			$uid=false;
		}

		if($mid===false and $this->ut)
		{
			$El=Eleanor::getInstance();
			$mid=isset($El->module['id']) ? (int)$El->module['id'] : 0;
		}
		if($this->template)
			Eleanor::$Template->queue[]=$this->template;
		return array($uid,($this->ut ? '`module`='.$mid.' AND ' : '').'`contid`='.Eleanor::$Db->Escape($id),$mid);
	}
}