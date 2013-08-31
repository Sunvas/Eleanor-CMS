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

class VotingManager extends BaseClass
{
	public
		$tpl='VotingManager',#Имя класса шаблона оформления
		$name=array('voting'),#Массив вложенности имен контролов, в данном случае имена будут иметь вид votin[c1], votin[c2] и т.п.
		$langs=array(),#Языки. Пустое - не мультиязычно, '' - передалось мультиязычно, но нужно сохранить только Language::$main, массив - сохраняем только нужные языки
		$noans=false,#Запретить изменения количества проголосовавших
		$Language,#Языковые переменные
		$ti=1,#Tab Index
		$bypost=false,#By post
		$POST,#Откуда брать значения POST запроса. Если null - из $_POST-a, если нет - то из этого массива
		$controls=false;#Контролы

	protected
		$table;#Основная таблица. Остальные определяются автоматически путем добавления суффиксов

	/**
	 * Конструктор редактора опроса
	 *
	 * @param string|FALSE $t Имя основной таблицы
	 * @param string $l Путь к языковому файлу
	 */
	public function __construct($t=false,$l='voting_manager-*.php')
	{
		$this->table=$t ? $t : P.'voting';
		if(Eleanor::$vars['multilang'])
			foreach(Eleanor::$langs as $k=>&$v)
				$this->langs[]=$k;
		$this->Language=new Language;
		$this->Language->queue[]=$l;
	}

	/**
	 * Вывод интерфейса правки опроса
	 *
	 * @param int|FALSE Идентификатор редактируемого опроса
	 */
	public function AddEdit($id=false)
	{
		if(!$this->controls)
			$this->controls=$this->Controls();
		if($id)
		{
			$id=(int)$id;
			if($this->bypost)
				$values=array();
			else
			{
				$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
				if($values=$R->fetch_assoc())
				{
					$values['_addvoting']=true;
					if((int)$values['begin']==0)
						$values['begin']='';
					if((int)$values['end']==0)
						$values['end']='';

					$values['_questions']=$brids=array();
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers` FROM `'.$this->table.'_q` WHERE `id`='.$id);
					while($a=$R->fetch_assoc())
					{
						$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
						$values['_questions'][$a['qid']]=array_slice($a,1);
					}

					$R=Eleanor::$Db->Query('SELECT `qid`,`language`,`title`,`variants` FROM `'.$this->table.'_q_l` WHERE `id`='.$id);
					while($a=$R->fetch_assoc())
					{
						if(isset($brids[$a['qid']]))
							continue;
						$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
						if(!$this->langs and (!$a['language'] or $a['language']==Language::$main))
						{
							foreach(array_slice($a,1) as $tk=>$tv)
								$values['_questions'][$a['qid']][$tk]=$tv;
							if(!$a['language'])
							{
								$brids[$a['qid']]=true;
								continue;
							}
						}
						elseif(!$a['language'] and $this->langs)
						{
							foreach(array_slice($a,1) as $tk=>$tv)
								$values['_questions'][$a['qid']][$tk][Language::$main]=$tv;
							$brids[$a['qid']]=true;
							continue;
						}
						elseif($this->langs and isset(Eleanor::$langs[$a['language']]))
							foreach(array_slice($a,1) as $tk=>$tv)
								$values['_questions'][$a['qid']][$tk][$a['language']]=$tv;
					}

					if($this->langs)
						foreach($values['_questions'] as &$v)
						{
							$dt=Eleanor::FilterLangValues($v['title']);
							$dv=Eleanor::FilterLangValues($v['variants']);

							foreach($this->langs as $k=>&$_)
								if(!isset($v['title'][$k]))
								{
									$v['title'][$k]=$dt;
									$v['variants'][$k]=$dv;
								}
						}
				}
				else
					$id=0;
			}
			$R=Eleanor::$Db->Query('SELECT `qid`,`vid`,COUNT(`vid`) `cnt` FROM `'.$this->table.'_q_results` WHERE `id`='.$id.' GROUP BY `vid`');
			while($a=$R->fetch_row())
				if(isset($values['_questions'][$a[0]]))
					$values['_questions'][$a[0]]['_real'][$a[1]]=$a[2];
		}
		if($this->bypost)
			$values=array();
		else
		{
			if(!$id)
				$values=array(
					'_addvoting'=>false,
					'begin'=>'',
					'end'=>'',
					'onlyusers'=>0,
					'againdays'=>10,
					'votes'=>0,
					'_questions'=>array(),
				);
			foreach($values as &$v)
				$v=array('value'=>$v);
		}
		$C=new Controls;
		$C->arrname=$this->name;
		$C->POST=&$this->POST;

		Eleanor::$Template->queue[]=$this->tpl;
		$c=Eleanor::$Template->VmAddEdit($id,$this->controls,$C->DisplayControls($this->controls,$values)+$values);
		return$c;
	}

	/**
	 * Сохранение опроса в БД
	 * @param int|FALSE Идентификатор сохраняемого опроса
	 * @param bool $nosave Флаг, который показывается, что в форме уже есть ошибки, таким образом сохранять не нужно, а только проверить на ошибки.
	 */
	public function Save($id=false,$nosave=false)
	{
		$C=new Controls;
		$C->langs=$this->langs;

		if(!$this->controls)
			$this->controls=$this->Controls();

		if($id)
		{
			$R=Eleanor::$Db->Query('SELECT `votes` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
			if($temp=$R->fetch_assoc())
				foreach($temp as $k=>$v)
					$this->controls['votes']['default']=$temp['votes'];
		}

		$C->arrname=(array)$this->name;
		$C->POST=&$this->POST;
		$values=$C->SaveControls($this->controls);
		if(!$values['_addvoting'] or !$values['_questions'])
		{
			if($id)
				$this->Delete($id);
			return false;
		}
		unset($values['_addvoting']);

		$errors=array();
		if(isset($this->controls['_questions']['controls'],$values['_questions']))
			foreach($this->controls['_questions']['controls'] as $k=>&$v)
				if(isset($v['check']) and is_callable($v['check']))
					foreach($values['_questions'] as &$vv)
					{
						$r=call_user_func($v['check'],$vv[$k],in_array('',$this->langs) ? array(Language::$main) : $this->langs);
						if($r)
							$errors+=$r;
					}

		foreach($this->controls as $k=>&$v)
			if(isset($v['check']) and is_callable($v['check']))
			{
				$r=call_user_func($v['check'],$values[$k],in_array('',$this->langs) ? array(Language::$main) : $this->langs);
				if($r)
					$errors+=$r;
			}

		if($this->noans)
		{
			foreach($values['_questions'] as &$v)
				$v['answers']=array();
			if($id)
			{
				$R=Eleanor::$Db->Query('SELECT `qid`,`answers` FROM `'.$this->table.'_q` WHERE `id`='.$id.' AND `qid`'.Eleanor::$Db->In(array_keys($values['_questions'])).' LIMIT 1');
				while($a=$R->fetch_assoc())
					$values['_questions'][$a['qid']]['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
			}
		}

		$lqv=array();
		foreach($values['_questions'] as $qk=>&$qv)
		{
			if($this->langs)
				foreach($this->langs as &$l)
				{
					foreach($qv as $k=>&$v)
						if(!empty($this->controls['_questions']['controls'][$k]['multilang']))
						{
							$lqv[$qk][$k][$l]=isset($v[$l]) && is_array($v) ? $v[$l] : array();
							unset($v[$l]);
							if(!$v)
								unset($qv[$k]);
						}
				}
			else
				foreach($qv as $k=>&$v)
					if(isset($this->controls['_questions']['controls'][$k]['multilang']))
					{
						$lqv[$qk][$k]['']=$v;
						unset($qv[$k]);
						if(!$v)
							unset($qv[$k]);
					}

			$erri=array('ERROR_INPUT'=>$this->Language['errorva']);
			if(!isset($qv['answers'],$lqv[$qk]['variants']) or !is_array($lqv[$qk]['variants']) or !is_array($qv['answers']) or $nosave)
				return$erri;

			if($this->noans)
			{
				$vals=Eleanor::FilterLangValues($lqv[$qk]['variants']);
				if(!is_array($vals))
					return$erri;
				$todel=array_keys(array_diff_key($qv['answers'],$vals));
				foreach($todel as &$v)
					unset($qv['answers'][$v]);
				$toins=array_keys(array_diff_key($vals,$qv['answers']));
				foreach($toins as &$v)
					$qv['answers'][$v]=0;
			}

			if(2>$cnt=count($qv['answers']))
				return$erri;
			if($this->langs)
				foreach($this->langs as &$l)
				{
					if(!isset($lqv[$qk]['variants'][$l]) or !is_array($lqv[$qk]['variants'][$l]) or count($lqv[$qk]['variants'][$l])!=$cnt)
						return$erri;

					foreach($qv['answers'] as $k=>&$a)
					{
						$a=abs((int)$a);
						if(!isset($lqv[$qk]['variants'][$l][$k]))
							return$erri;
						$lqv[$qk]['variants'][$l][$k]=trim($lqv[$qk]['variants'][$l][$k]);
						if($lqv[$qk]['variants'][$l][$k]=='')
						{
							$er=strtoupper('empty_variant'.($l ? '_'.$l : ''));
							$errors[$er]=$this->Language['EMPTY_VARIANT']($l);
						}
					}
					unset($a);
				}

			$qv['maxans']=(int)$qv['maxans'];
			if($qv['maxans']>$cnt)
				$qv['maxans']=$cnt;
			elseif($qv['maxans']<2)
				$qv['maxans']=2;
		}
		unset($l);

		$tsb=strtotime((string)$values['begin']);
		$tse=strtotime((string)$values['end']);

		if(!$tsb)
			$values['begin']='0000-00-00 00:00:00';
		if($tse and $tsb and $tse<=$tsb)
			$errors['DATES']=$this->Language['DATES'];

		if($errors)
			return$errors;
		if(!$tse)
			$values['end']='0000-00-00 00:00:00';

		$quests=$values['_questions'];
		unset($values['_questions']);

		$dbl=$this->langs ? $this->langs : array('');
		if($id)
		{
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0)
				$id=false;
			else
			{
				$todel=array();
				$R=Eleanor::$Db->Query('SELECT `qid`,`vid` FROM `'.$this->table.'_q_results` WHERE `id`='.$id.' GROUP BY `qid`,`vid`');
				while($a=$R->fetch_assoc())
					$todel[$a['qid']][]=$a['vid'];

				$nin=' AND `id`='.$id.' AND `language`'.Eleanor::$Db->In($dbl,true);
				foreach($quests as $k=>&$v)
				{
					if(isset($todel[$k]))
					{
						$todel[$k]=array_diff($todel[$k],array_keys($v['answers']));
						if($todel[$k])
							Eleanor::$Db->Delete($this->table.'_q_results','`id`='.$id.' AND `qid`='.$k.' AND `vid`'.Eleanor::$Db->In($todel[$k]));
					}
					$v['id']=$id;
					$v['qid']=$k;
					$v['answers']=serialize($v['answers']);
					Eleanor::$Db->Delete($this->table.'_q_l','`qid`='.$k.$nin);
				}
				$nin='`id`='.$id.' AND `qid`'.Eleanor::$Db->In(array_keys($quests),true);
				Eleanor::$Db->Delete($this->table.'_q',$nin);
				Eleanor::$Db->Delete($this->table.'_q_l',$nin);
				Eleanor::$Db->Delete($this->table.'_q_results',$nin);
				Eleanor::$Db->Replace($this->table.'_q',array_values($quests));
				Eleanor::$Db->Update($this->table,$values);
				foreach($lqv as $lk=>&$lv)
					foreach($dbl as &$v)
						Eleanor::$Db->Replace(
							$this->table.'_q_l',
							$this->langs
							? array(
								'id'=>$id,
								'qid'=>$lk,
								'language'=>$v,
								'title'=>$lv['title'][$v],
								'variants'=>serialize($lv['variants'][$v]),
							)
							: array(
								'id'=>$id,
								'qid'=>$lk,
								'language'=>'',
								'title'=>$lv['title'][''],
								'variants'=>serialize($lv['variants']['']),
							)
						);
			}
		}
		if(!$id)
		{
			$id=Eleanor::$Db->Insert($this->table,$values);
			$k=0;
			foreach($quests as &$v)
			{
				$v['id']=$id;
				$v['qid']=$k++;
				$v['answers']=serialize($v['answers']);
			}
			Eleanor::$Db->Insert($this->table.'_q',array_values($quests));#array_values - потому что пришедшие от пользователя ключи - текстовые
			$values=array('id'=>array(),'qid'=>array(),'language'=>array(),'title'=>array(),'variants'=>array());
			foreach($lqv as $lk=>&$lv)
				foreach($dbl as &$v)
				{
					$values['id'][]=$id;
					$values['qid'][]=$quests[$lk]['qid'];
					if($this->langs)
					{
						$values['language'][]=$v;
						$values['title'][]=$lv['title'][$v];
						$values['variants'][]=serialize($lv['variants'][$v]);
					}
					else
					{
						$values['language'][]='';
						$values['title'][]=$lv['title'][''];
						$values['variants'][]=serialize($lv['variants']['']);
						break;
					}
				}
			Eleanor::$Db->Insert($this->table.'_q_l',$values);
		}
		return$id;
	}

	/**
	 * Удаление опроса
	 *
	 * @param int|array Идентификатор удаляемого опроса
	 */
	public function Delete($ids)
	{
		$ids=Eleanor::$Db->In($ids);
		Eleanor::$Db->Delete($this->table,'`id`'.$ids);
		Eleanor::$Db->Delete($this->table.'_q','`id`'.$ids);
		Eleanor::$Db->Delete($this->table.'_q_l','`id`'.$ids);
		Eleanor::$Db->Delete($this->table.'_results','`id`'.$ids);
		Eleanor::$Db->Delete($this->table.'_q_results','`id`'.$ids);
	}

	/**
	 * Получение контролов опроса по умолчанию
	 */
	public function Controls()
	{
		$THIS=$this;#ToDo! PHP 5.4 убрать этот костыль (смотри ниже) use ($THIS)
		$ans=array();
		$vaload=function($a,$Obj) use (&$ans,$THIS)
		{
			if($a['bypost'])
				$a['value']=$Obj->GetPostVal($a['name']);
			if(!is_array($a['value']))
				$a['value']=array();
			if(isset($a['nodisplay']))
				$ans=array($a['controlname'],$a['value']);
			else
				return Eleanor::$Template->VmVariants($a['value'],$a['controlname'],$ans[1],$ans[0],$a['tabindex'],$a['real'],$THIS->noans);
		};

		return array(
			'_addvoting'=>array(
				'title'=>$this->Language['addvoting'],
				'descr'=>'',
				'type'=>'check',
				'default'=>false,
				'bypost'=>&$this->bypost,
				'options'=>array(
					'tabindex'=>$this->ti++,
				),
			),
			'begin'=>array(
				'title'=>$this->Language['dbegin'],
				'descr'=>$this->Language['lblank'],
				'type'=>'date',
				'bypost'=>&$this->bypost,
				'options'=>array(
					'time'=>true,
				)
			),
			'end'=>array(
				'title'=>$this->Language['dend'],
				'descr'=>'',
				'type'=>'date',
				'bypost'=>&$this->bypost,
				'options'=>array(
					'time'=>true,
				)
			),
			'onlyusers'=>array(
				'title'=>$this->Language['onlyusers'],
				'descr'=>$this->Language['onlyusers_'],
				'type'=>'check',
				'bypost'=>&$this->bypost,
				'options'=>array(
					'tabindex'=>$this->ti++,
				),
			),
			'againdays'=>array(
				'title'=>$this->Language['againdays'],
				'descr'=>$this->Language['againdays_'],
				'type'=>'input',
				'default'=>2,
				'bypost'=>&$this->bypost,
				'options'=>array(
					'type'=>'number',
					'tabindex'=>$this->ti++,
				),
			),
			'votes'=>array(
				'title'=>$this->Language['votes'],
				'descr'=>'',
				'type'=>'input',
				'bypost'=>&$this->bypost,
				'default'=>0,
				'options'=>array(
					'type'=>'number',
					'tabindex'=>$this->ti++,
					'extra'=>array(
						'class'=>'sic',
						'min'=>0,
					),
				),
			),
			'_questions'=>array(
				'type'=>'',
				'bypost'=>&$this->bypost,
				'options'=>array(
					'load'=>function($a,$Obj) use ($THIS)
					{
						$C=new Controls;
						$C->arrname=array_merge($Obj->arrname,$a['name']);
						$r=array();
						if($a['bypost'])
						{
							$keys=$Obj->GetPostVal($a['name']);
							$keys=$keys ? array_keys($keys) : array();
							foreach($keys as &$v)
							{
								$C->arrname[]=$v;
								$a['controls']['variants']['real']=isset($a['value'][$v]['_real']) ? $a['value'][$v]['_real'] : array();
								$r[$v]=$C->DisplayControls($a['controls'],array());
								array_pop($C->arrname);
							}
						}
						else
						{
							if(!$a['value'])
								$a['value']=array(array(
									'title'=>$THIS->langs ? array(''=>'') : '',
									'variants'=>$THIS->langs ? array(''=>array()) : array(),
									'multiple'=>false,
									'maxans'=>2,
									'answers'=>array(),
									'_real'=>array(),
								));
							foreach($a['value'] as $k=>&$v)
							{
								$C->arrname[]=$k;
								$values=array();
								foreach($v as $vk=>&$vv)
									if(isset($a['controls'][$vk]))
										$values[$vk]['value']=$vv;
								$a['controls']['variants']['real']=isset($v['_real']) ? $v['_real'] : array();
								$r[$k]=$C->DisplayControls($a['controls'],$values);
								array_pop($C->arrname);
							}
						}
						return Eleanor::$Template->VmQuestions($r,$a['controls']);
					},
					'save'=>function($a,$Obj) use ($THIS)
					{
						$keys=$Obj->GetPostVal($a['name']);
						if(!$keys)
							return;
						$keys=$keys ? array_keys($keys) : array();
						$C=new Controls;
						$C->arrname=array_merge($Obj->arrname,$a['name']);
						$C->langs=$THIS->langs;
						$r=array();
						foreach($keys as &$v)
						{
							$C->arrname[]=$v;
							$r[$v]=$C->SaveControls($a['controls']);
							array_pop($C->arrname);
						}
						return$r;
					},
				),
				'controls'=>array(
					'title'=>array(
						'title'=>$this->Language['question'],
						'descr'=>'',
						'type'=>'input',
						'check'=>function($value,$langs) use ($THIS)
						{
							$errors=array();
							if($THIS->langs)
								foreach($value as $k=>&$v)
								{
									if($v=='' and in_array($k,$langs))
									{
										$uk=strtoupper($k);
										$errors['EMPTY_TITLE_'.$uk]=$THIS->Language['EMPTY_TITLE']($k);
									}
								}
							elseif($value=='')
								$errors['EMPTY_TITLE']=$THIS->Language['EMPTY_TITLE']();
							return$errors;
						},
						'bypost'=>&$this->bypost,
						'multilang'=>(bool)$this->langs,
						'options'=>array(
							'htmlsafe'=>true,
							'extra'=>array(
								'tabindex'=>$this->ti++,
							),
						),
					),
					'answers'=>array(
						'type'=>'',
						'bypost'=>&$this->bypost,
						'nodisplay'=>true,
						'options'=>array(
							'load'=>$vaload,
							'save'=>function($a,$Obj)
							{static $z;
								$ans=(array)$Obj->GetPostVal($a['name'],array());
								foreach($ans as &$v)
									$v=(int)$v;
								return$ans;
							},
						),
					),
					'variants'=>array(
						'title'=>$this->Language['vv'],
						'descr'=>'',
						'type'=>'',
						'bypost'=>&$this->bypost,
						'multilang'=>(bool)$this->langs,
						'tabindex'=>$this->ti++,
						'options'=>array(
							'load'=>$vaload,
							'save'=>function($a,$Obj)
							{
								return$Obj->GetPostVal($a['name']);
							},
						),
					),
					'multiple'=>array(
						'title'=>$this->Language['multiple'],
						'descr'=>'',
						'type'=>'check',
						'bypost'=>&$this->bypost,
						'options'=>array(
							'tabindex'=>$this->ti++,
						),
					),
					'maxans'=>array(
						'title'=>$this->Language['maxa'],
						'descr'=>'',
						'type'=>'input',
						'bypost'=>&$this->bypost,
						'default'=>2,
						'options'=>array(
							'type'=>'number',
							'tabindex'=>$this->ti++,
							'extra'=>array(
								'min'=>2,
							),
						),
					),
				),
			),
		);
	}
}