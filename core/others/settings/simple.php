<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Внимание! Это урезанный файл, который не позволяет создавать / редактировать опции.
	Для получения возможность создания и редактирования опций, пожалуйста. Воспользуйтесь файлом full.php
*/
class Settings extends BaseClass
{
	public
		$pp,#Префикс ключей GET параметров
		$sort_cb,#Callback функция сортировки опций
		$a_grs=array(),#Разрешенные группы для изменения настроек. Если false - группы редактировать запрещено
		$a_opts=array(),#Разрешенные настройки для изменения
		$only_opts=true,#Разрешить изменение только опций, относящихся к категориям выше
		$g_mod=false,#Разрешить создание, редактирование и удаление групп
		$opts_mod=true,#Разрешить создание, редактирование и удаление настроек
		$a_move=true,#Разрешить перемещение групп и настроек
		$a_search=false,#Разрешить поиск
		$opts_wg=false,#Показывать опции без групп (для админа)
		$a_import=false,#Разрешить импорт настроек
		$a_export=false;#Разрешить экспорт настроек

	final public function __construct()
	{
		Eleanor::$Template->queue[]='Settings';
	}

	/**
	 * Получение интерфейса настроек
	 *
	 * @param string $param Тип интерфейса, который необходимо получить. Допустимые значения:
	 * full - отобразить весь интерфейс настроек для админа (без ограничений)
	 * groups - отобразить интерфейс групп с учетом настроек ограничений выше
	 * group - отобразить настройки определенной группы при этом в $value следует передать идентификатор этой группы
	 * @param string $value Идентификатор интерфейса, который необходимо получить
	 */
	public function GetInterface($param,$value='')
	{
		if($param=='full')
		{
			$this->a_move=$this->a_export=$this->a_search=$this->a_import=$this->opts_wg=true;
			$this->only_opts=false;
			$this->a_grs=$this->a_opts=array();
		}
		$post=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$our_query;
		$lang=Eleanor::$Language['settings'];
		if(isset($_GET[$this->pp.'sg']))
			return$this->ShowGroup($_GET[$this->pp.'sg']=='no' ? 0 : (int)$_GET[$this->pp.'sg']);
		elseif(isset($_GET[$this->pp.'gdefault']))
		{
			$id=(int)$_GET[$this->pp.'gdefault'];
			$R=Eleanor::$Db->Query('SELECT `title` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
				return GoAway();
			if(isset($_POST['ok']))
			{
				Eleanor::$Db->Update(P.'config_l', array('!default'=>'`value`'),'`id`IN(SELECT `id` FROM `'.P.'config` WHERE `group`='.$id.')');
				return GoAway(true);
			}
			$GLOBALS['title'][]=$lang['setting_og'];
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
			return Eleanor::$Template->SettGrDefault($a,$back);
		}
		elseif(isset($_GET[$this->pp.'greset']))
		{
			$id=(int)$_GET[$this->pp.'greset'];
			$R=Eleanor::$Db->Query('SELECT `title` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
				return GoAway();
			if(isset($_POST['ok']))
			{
				Eleanor::$Db->Update(P.'config_l', array('!value'=>'`default`'),'`id`IN(SELECT `id` FROM `'.P.'config` WHERE `group`='.$id.')');
				$R=Eleanor::$Db->Query('SELECT `keyword` FROM `'.P.'config_groups` WHERE `id`='.$id.' LIMIT 1');
				if($temp=$R->fetch_row())
					Eleanor::LoadOptions($temp[0],true,false);
				return GoAway(true);
			}
			$GLOBALS['title'][]=$lang['reset_g_con'];
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
			return Eleanor::$Template->SettGrReset($a,$back);
		}
		elseif(isset($_GET[$this->pp.'odefault']))
		{
			if(Eleanor::$our_query and $this->opts_mod)
				Eleanor::$Db->Update(P.'config_l',array('!default'=>'`value`'),'`id`='.(int)$_GET[$this->pp.'odefault']);
			return GoAway();
		}
		elseif(isset($_GET[$this->pp.'oreset']))
		{
			$id=(int)$_GET[$this->pp.'oreset'];
			if(Eleanor::$our_query)
			{
				$R=Eleanor::$Db->Query('SELECT `gr`.`keyword` FROM `'.P.'config` `c` INNER JOIN `'.P.'config_groups` `gr` ON `c`.`group`=`gr`.`id` WHERE `c`.`id`='.$id.' LIMIT 1');
				$kw=$R->fetch_row();
				Eleanor::$Db->Update(P.'config_l',array('!value'=>'`default`'),'`id`='.$id);
				Eleanor::LoadOptions($kw[0],true,false);
			}
			return GoAway();
		}
		elseif(isset($_GET[$this->pp.'gup']))
		{
			$id=(int)$_GET[$this->pp.'gup'];
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'config_groups` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0 or !$this->a_move or !Eleanor::$our_query)
				return GoAway(true);
			list($posit)=$R->fetch_row();
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`),`pos` FROM `'.P.'config_groups` WHERE `pos`=(SELECT MAX(`pos`) FROM `'.P.'config_groups` WHERE `pos`<'.$posit.')');
			list($cnt,$np)=$R->fetch_row();
			if($cnt>0)
			{
				if($cnt>1 or $np+1!=$posit)
				{
					$this->OptimizeGroups(false);
					$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'config_groups` WHERE `id`='.$id.' LIMIT 1');
					list($posit)=$R->fetch_row();
				}
				$posit--;
				Eleanor::$Db->Update(P.'config_groups', array('!pos'=>'`pos`+1'),'`pos`='.$posit.' LIMIT 1');
				Eleanor::$Db->Update(P.'config_groups', array('!pos'=>'`pos`-1'),'`id`='.$id.' LIMIT 1');
			}
			return GoAway(true,301,'gr'.$id);
		}
		elseif(isset($_GET[$this->pp.'oup']))
		{
			$id=(int)$_GET[$this->pp.'oup'];
			$R=Eleanor::$Db->Query('SELECT `pos`,`group` FROM `'.P.'config` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0 or !$this->a_move or !Eleanor::$our_query)
				return GoAway();
			list($posit,$group)=$R->fetch_row();
			if($this->a_grs and !in_array($group,$this->a_grs))
				return GoAway();
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`),`pos` FROM `'.P.'config` WHERE `group`='.$group.' AND `pos`=(SELECT MAX(`pos`) FROM `'.P.'config` WHERE `group`='.$group.' AND `pos`<'.$posit.')');
			list($cnt,$np)=$R->fetch_row();
			if($cnt>0)
			{
				if($cnt>1 or $np+1!=$posit)
				{
					$this->OptimizeOptions($group,false);
					$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'config` WHERE `id`='.$id.' LIMIT 1');
					list($posit)=$R->fetch_row();
				}
				$posit--;
				Eleanor::$Db->Update(P.'config',array('!pos'=>'`pos`+1'),'`group`='.$group.' AND `pos`='.$posit.' LIMIT 1');
				Eleanor::$Db->Update(P.'config',array('!pos'=>'`pos`-1'),'`id`='.$id.' LIMIT 1');
			}
			return GoAway(false,301,'opt'.$id);
		}
		elseif(isset($_GET[$this->pp.'gdown']))
		{
			$id=(int)$_GET[$this->pp.'gdown'];
			$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'config_groups` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0 or !$this->a_move or !Eleanor::$our_query)
				return GoAway(true);
			list($posit)=$R->fetch_row();
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`),`pos` FROM `'.P.'config_groups` WHERE `pos`=(SELECT MIN(`pos`) FROM `'.P.'config_groups` WHERE `pos`>'.$posit.')');
			list($cnt,$np)=$R->fetch_row();
			if($cnt>0)
			{
				if($cnt>1 or $np-1!=$posit)
				{
					$this->OptimizeGroups(false);
					$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'config_groups` WHERE `id`='.$id.' LIMIT 1');
					list($posit)=$R->fetch_row();
				}
				$posit++;
				Eleanor::$Db->Update(P.'config_groups', array('!pos'=>'`pos`-1'),'`pos`='.$posit.' LIMIT 1');
				Eleanor::$Db->Update(P.'config_groups', array('!pos'=>'`pos`+1'),'`id`='.$id.' LIMIT 1');
			}
			return GoAway(true,301,'gr'.$id);
		}
		elseif(isset($_GET[$this->pp.'odown']))
		{
			$id=(int)$_GET[$this->pp.'odown'];
			$R=Eleanor::$Db->Query('SELECT `pos`, `group` FROM `'.P.'config` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0 or !$this->a_move or !Eleanor::$our_query)
				return GoAway();
			list($posit,$group)=$R->fetch_row();
			if($this->a_grs and !in_array($group,$this->a_grs))
				return GoAway();
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`),`pos` FROM `'.P.'config` WHERE `group`='.$group.' AND `pos`=(SELECT MIN(`pos`) FROM `'.P.'config` WHERE `group`='.$group.' AND `pos`>'.$posit.')');
			list($cnt,$np)=$R->fetch_row();
			if($cnt>0)
			{
				if($cnt>1 or $np-1!=$posit)
				{
					$this->OptimizeOptions($group,false);
					$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.P.'config` WHERE `id`='.$id.' LIMIT 1');
					list($posit)=$R->fetch_row();
				}
				$posit++;
				Eleanor::$Db->Update(P.'config', array('!pos'=>'`pos`-1'),'`group`='.$group.' AND `pos`='.$posit.' LIMIT 1');
				Eleanor::$Db->Update(P.'config', array('!pos'=>'`pos`+1'),'`id`='.$id.' LIMIT 1');
			}
			return GoAway(false,301,'opt'.$id);
		}
		elseif(isset($_GET[$this->pp.'ooptimize']))
			return$this->OptimizeOptions((int)$_GET[$this->pp.'ooptimize']);
		elseif(isset($_GET[$this->pp.'sdo']))
			switch($_GET[$this->pp.'sdo'])
			{
				case'goptimize';
					return$this->OptimizeGroups();
				break;
				case'search':
					if(!$this->a_search)
						return GoAway();
					$word=isset($_POST['search']) ? (string)Eleanor::$POST['search'] : '';
					if(strlen($word)<3)
					{
						$GLOBALS['title'][]=$lang['error'];
						return Eleanor::$Template->SettShowError($lang['s_phrase_len'],$back);
					}
					$R=Eleanor::$Db->Query('SELECT `c`.*,`cl`.*,`gl`.`title` `gtitle` FROM `'.P.'config` `c` INNER JOIN `'.P.'config_l` `cl` USING(`id`) LEFT JOIN `'.P.'config_groups_l` `gl` ON `c`.`group`=`gl`.`id` AND `gl`.`language` IN(\'\',\''.Language::$main.'\') WHERE `c`.`id` IN(SELECT `id` FROM `'.P.'config_l` WHERE `language`IN(\'\',\''.Language::$main.'\') AND MATCH(`title`,`descr`) AGAINST(\''.Eleanor::$Db->Escape($word,false).'\' IN BOOLEAN MODE))');
					if($R->num_rows==0)
					{
						$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
						$GLOBALS['title'][]=$lang['error'];
						return Eleanor::$Template->SettShowError(sprintf($lang['ops_not_found'],$word),$back);
					}
					$a=array();
					while($temp=$R->fetch_assoc())
						$this->PreControls($a,$temp);
					$GLOBALS['title'][]=sprintf($lang['cnt_seaop'],count($a));
					$this->DoNavigation();
					return$this->ShowListOptions($a,false);
				break;
				case'export':
					if($post)
					{
						$groups=isset($_POST['groups']) ? (array)$_POST['groups'] : array();
						$options=isset($_POST['options']) ? (array)$_POST['options'] : array();
						if(!$options and !$groups)
							return$this->Export();
						$update=(isset($_POST['update']) and in_array($_POST['update'],array('update','ignore','full','delete'))) ? $_POST['update'] : 'update';
						if($groups)
						{
							$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `id`'.Eleanor::$Db->In($groups).' ORDER BY `pos` ASC');
							$groups=array();
							while($temp=$R->fetch_assoc())
							{
								if(!$temp['language'])
									$temp['language']=Language::$main;

								if(isset($groups[$temp['name']]['title'][$temp['language']]))
									continue;

								if(isset($groups[$temp['name']]))
								{
									$groups[$temp['name']]['title'][$temp['language']]=$temp['title'];
									$groups[$temp['name']]['descr'][$temp['language']]=$temp['descr'];
								}
								else
									$groups[$temp['name']]=array(
										'protected'=>$temp['protected'],
										'keyword'=>$temp['keyword'],
										'onexists'=>$update,
										'title'=>array($temp['language']=>$temp['title']),
										'descr'=>array($temp['language']=>$temp['descr']),
									);
							}
							foreach($groups as $k=>&$v)
								if(count($v['title'])==1)
									if(isset($v['title'][Language::$main]))
									{
										$v['title']=reset($v['title']);
										$v['descr']=reset($v['descr']);
									}
									else
										unset($groups[$k]);
						}

						if($options)
						{
							$R=Eleanor::$Db->Query('SELECT `c`.*,`l`.*,`g`.`name` `gname` FROM `'.P.'config` `c` INNER JOIN `'.P.'config_l` `l` USING(`id`) LEFT JOIN `'.P.'config_groups` `g` ON `g`.`id`=`c`.`group` WHERE `c`.`id`'.Eleanor::$Db->In($options).' ORDER BY `c`.`pos` ASC');
							$opgdel=$options=array();
							while($temp=$R->fetch_assoc())
							{
								if(!$temp['language'])
									$temp['language']=Language::$main;

								if(isset($options[$temp['name']]['title'][$temp['language']]))
									continue;

								if(isset($options[$temp['name']]))
								{
									$options[$temp['name']]['title'][$temp['language']]=$temp['title'];
									$options[$temp['name']]['descr'][$temp['language']]=$temp['descr'];
									$options[$temp['name']]['value'][$temp['language']]=$temp['value'];
									$options[$temp['name']]['serialized'][$temp['language']]=$temp['serialized'];
									$options[$temp['name']]['default'][$temp['language']]=$temp['default'];
									$options[$temp['name']]['extra'][$temp['language']]=$temp['extra'];
									$options[$temp['name']]['startgroup'][$temp['language']]=$temp['startgroup'];
								}
								else
								{
									$opgdel[$temp['gname']]=isset($opgdel[$temp['gname']]) ? $opgdel[$temp['gname']]+1 : 1;
									$options[$temp['name']]=array(
										'group'=>$temp['gname'],
										'type'=>$temp['type'],
										'protected'=>$temp['protected'],
										'pos'=>$temp['pos'],
										'multilang'=>$temp['multilang'],
										'onexists'=>$update,
										'eval_load'=>$temp['eval_load'],
										'eval_save'=>$temp['eval_save'],
										'title'=>array($temp['language']=>$temp['title']),
										'descr'=>array($temp['language']=>$temp['descr']),
										'value'=>array($temp['language']=>$temp['value']),
										'serialized'=>array($temp['language']=>$temp['serialized']),
										'default'=>array($temp['language']=>$temp['default']),
										'extra'=>array($temp['language']=>$temp['extra']),
										'startgroup'=>array($temp['language']=>$temp['startgroup']),
									);
								}
							}
							foreach($options as $k=>&$v)
								if(count($v['title'])==1)
									if(isset($v['title'][Language::$main]))
									{
										$v['title']=reset($v['title']);
										$v['descr']=reset($v['descr']);
										$v['value']=reset($v['value']);
										$v['serialized']=reset($v['serialized']);
										$v['default']=reset($v['default']);
										$v['extra']=reset($v['extra']);
										$v['startgroup']=reset($v['startgroup']);
									}
									else
										unset($options[$k]);
						}
						$O=simplexml_load_string('<eleanoroptions version="1"></eleanoroptions>');
						if($update=='delete' and $options || $groups)
						{
							$candel=array();
							$R=Eleanor::$Db->Query('SELECT `g`.`name`, COUNT(`op`.`group`) `cnt` FROM `'.P.'config_groups` `g` INNER JOIN `'.P.'config` `op` ON `g`.`id`=`op`.`group` WHERE `g`.`name`'.Eleanor::$Db->In(array_keys($groups)));
							while($temp=$R->fetch_assoc())
								$candel[$temp['name']]=$temp['cnt'];
							$EO=$O->addChild('delete');
							foreach($groups as $k=>&$v)
								if(isset($opgdel[$k],$candel[$k]) and $opgdel[$k]==$candel[$k])
									$EO->addChild('group',$k);
							foreach($options as $k=>&$v)
							{
								$OP=$EO->addChild('option',$k);
								$OP->addAttribute('group',$v['group']);
							}
						}
						else
						{
							if(CHARSET!='utf-8')
							{
								foreach($groups as &$v)
									foreach($v as &$t)
										if(is_array($t))
											foreach($t as &$l)
												$l=mb_convert_encoding($l,'utf-8',CHARSET);
										else
											$t=mb_convert_encoding($t,'utf-8',CHARSET);
								foreach($options as &$v)
									foreach($v as &$t)
										if(is_array($t))
											foreach($t as &$l)
												$l=mb_convert_encoding($l,'utf-8',CHARSET);
										else
											$t=mb_convert_encoding($t,'utf-8',CHARSET);
							}
							foreach($groups as $name=>&$gr)
							{
								$GR=$O->addChild('group');
								$GR->addAttribute('name',$name);
								$GR->addAttribute('protected',$gr['protected']);
								$GR->addAttribute('keyword',$gr['keyword']);
								$GR->addAttribute('onexists',$gr['onexists']);
								if(is_array($gr['title']))
								{
									$T=$GR->addChild('title');
									foreach($gr['title'] as $lng=>&$d)
									{
										$DOM=dom_import_simplexml($T->addChild($lng));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
									}

									$T=$GR->addChild('descr');
									foreach($gr['descr'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
										}
								}
								else
								{
									if($gr['title'])
									{
										$DOM=dom_import_simplexml($GR->addChild('title'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($gr['title']));
									}
									if($gr['descr'])
									{
										$DOM=dom_import_simplexml($GR->addChild('descr'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($gr['descr']));
									}
								}
							}
							foreach($options as $name=>&$op)
							{
								$OP=$O->addChild('option');
								$OP->addAttribute('name',$name);
								$OP->addAttribute('protected',$op['protected']);
								$OP->addAttribute('pos',$op['pos']);
								$OP->addAttribute('group',$op['group']);
								$OP->addAttribute('type',$op['type']);
								$OP->addAttribute('multilang',$op['multilang']);
								$OP->addAttribute('onexists',$op['onexists']);
								if(is_array($op['title']))
								{
									$T=$OP->addChild('title');
									foreach($op['title'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
										}

									$T=$OP->addChild('serialized');
									foreach($op['serialized'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
										}

									$T=$OP->addChild('descr');
									foreach($op['descr'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
										}

									$T=$OP->addChild('value');
									foreach($op['value'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['serialized'][$lng] ? var_export(unserialize($d),true) : $d));
										}

									$T=$OP->addChild('default');
									foreach($op['default'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['serialized'][$lng] ? var_export(unserialize($d),true) : $d));
										}

									$T=$OP->addChild('extra');
									foreach($op['extra'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
										}

									$T=$OP->addChild('startgroup');
									foreach($op['startgroup'] as $lng=>&$d)
										if($d)
										{
											$DOM=dom_import_simplexml($T->addChild($lng));
											$DOM->appendChild($DOM->ownerDocument->createCDATASection($d));
										}
								}
								else
								{
									if($op['title'])
									{
										$DOM=dom_import_simplexml($OP->addChild('title'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['title']));
									}

									if($op['serialized'])
									{
										$DOM=dom_import_simplexml($OP->addChild('serialized'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['serialized']));
									}

									if($op['descr'])
									{
										$DOM=dom_import_simplexml($OP->addChild('descr'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['descr']));
									}

									if($op['value'])
									{
										$DOM=dom_import_simplexml($OP->addChild('value'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['serialized'] ? var_export(unserialize($op['value']),true) : $op['value']));
									}

									if($op['default'])
									{
										$DOM=dom_import_simplexml($OP->addChild('default'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['serialized'] ? var_export(unserialize($op['default']),true) : $op['default']));
									}

									if($op['extra'])
									{
										$DOM=dom_import_simplexml($OP->addChild('extra'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['extra']));
									}

									if($op['startgroup'])
									{
										$DOM=dom_import_simplexml($OP->addChild('startgroup'));
										$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['startgroup']));
									}
								}

								if($op['eval_save'])
								{
									$DOM=dom_import_simplexml($OP->addChild('evalsave'));
									$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['eval_save']));
								}

								if($op['eval_load'])
								{
									$DOM=dom_import_simplexml($OP->addChild('evalload'));
									$DOM->appendChild($DOM->ownerDocument->createCDATASection($op['eval_load']));
								}
							}
						}
						$xml=$O->asXML();
						$xml=preg_replace('#^<\?xml version="([0-9\.]+)"\?>#i','<?xml version="\1" encoding="'.CHARSET.'"?>',$xml);
						if(CHARSET!='utf-8')
							$xml=mb_convert_encoding($xml,CHARSET,'utf-8');
						return Files::OutputStream(array('data'=>$xml,'filename'=>'options_dump'.date('Y-m-d').'.txt'));
					}
					return$this->Export();
				break;
				case'import':
					if($post)
					{
						if($_SERVER['REQUEST_METHOD']!='POST')
							return$this->Import();
						if(!isset($_FILES['import']) or !is_uploaded_file($_FILES['import']['tmp_name']))
							return$this->Import(false,$lang['f_not_load']);
						$f=file_get_contents($_FILES['import']['tmp_name']);
						$error='';
						try
						{
							$a=$this->ProcessImport($f);
						}
						catch(EE$E)
						{
							$error=$E->getMessage();
							$a=array();
						}
						return$this->Import($a,$error);
					}
					return$this->Import();
				break;
				case'saveoptions':
					$ids=isset($_POST['ids']) ? explode(',',$_POST['ids']) : array();
					if(!$ids or !Eleanor::$our_query)
						return GoAway();
					if($this->a_opts)
						foreach($ids as $k=>&$v)
							if(!in_array($v,$this->a_opts))
								unset($ids[$k]);
					$R=Eleanor::$Db->Query('SELECT `id`,`group`,`type`,`name`,`language`,`multilang`,`eval_save`,`value`,`serialized`,`default`,`extra` FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `id`'.Eleanor::$Db->In($ids));
					$a=array();
					$oid=0;
					Eleanor::$nolog=true;#У unserialize иногда сносит башню.
					while($temp=$R->fetch_assoc())
					{
						$temp['multilang']&=Eleanor::$vars['multilang'];
						if($this->only_opts and $this->a_grs and !in_array($temp['group'],$this->a_grs) or $oid==$temp['id'] and !$temp['multilang'] or !$temp['multilang'] and $temp['language'] and $temp['language']!=Language::$main)#Не отображаем опции, недоступные для этого языка
							continue;
						$oid=$temp['id'];
						if(!isset($a[$temp['id']]) or $temp['multilang'])
						{
							$temp['extra']=$temp['extra'] ? eval('return '.$temp['extra'].';') : array();
							if($temp['serialized'])
							{
								$temp['value']=unserialize($temp['value']);
								$temp['default']=unserialize($temp['default']);
							}
							if(isset($a[$temp['id']]))
							{
								$a[$temp['id']]['value'][$temp['language']]=$temp['value'];
								$a[$temp['id']]['default'][$temp['language']]=$temp['default'];
								$a[$temp['id']]['extra'][$temp['language']]=$temp['extra'];
							}
							else
							{
								if($temp['multilang'])
								{
									$temp['value']=array($temp['language']=>$temp['value']);
									$temp['default']=array($temp['language']=>$temp['default']);
									$temp['extra']=array($temp['language']=>$temp['extra']);
								}
								$a[$temp['id']]=$temp;
							}
							$a[$temp['id']]['_bypost']=true;
						}
					}
					Eleanor::$nolog=false;
					$groups=$errors=array();
					$C=new Controls;
					foreach($a as &$v)
					{
						$groups[]=$v['group'];
						$values=array(
							'id'=>$v['id'],
							'group'=>$v['group'],
							'type'=>$v['type'],
							'save_eval'=>$v['eval_save'],
							'name'=>array($v['name']),
						);
						if($v['multilang'])
							foreach($v['value'] as $lng=>$lv)
							{
								$langs=array(
									'default'=>$v['default'][$lng],
									'value'=>$lv,
									'lang'=>$lng,
									'options'=>$v['extra'][$lng],
								);
								$values['name']['lang']=$lng;
								try
								{
									$res=$C->SaveControl($values+$langs);
								}
								catch(EE$E)
								{
									$errors[$v['id']][$lng]=isset($E->extra['code']) && $E->extra['code']==1 ? $lang['error_in_code'] : $E->getMessage();
								}
								if(!isset($errors[$v['id']][$lng]))
								{
									$s=false;
									if(!is_scalar($res))
									{
										$s=true;
										$res=serialize($res);
									}
									Eleanor::$Db->Update(P.'config_l',array('value'=>$res,'serialized'=>$s),'`id`='.$v['id'].' AND `language`'.($lng==LANGUAGE ? ' IN (\'\',\''.$lng.'\')' : '=\''.$lng.'\'').' LIMIT 1');
								}
							}
						else
						{
							$values+=array(
								'default'=>$v['default'],
								'value'=>$v['value'],
								'options'=>$v['extra'],
							);
							try
							{
								$res=$C->SaveControl($values);
							}
							catch(EE$E)
							{
								$errors[$v['id']]=(isset($E->extra['code']) and $E->extra['code']==1) ? $lang['error_in_code'] : $E->getMessage();
							}
							if(!isset($errors[$v['id']]))
							{
								$s=false;
								if(!is_scalar($res))
								{
									$s=true;
									$res=serialize($res);
								}
								Eleanor::$Db->Update(P.'config_l',array('value'=>$res,'serialized'=>$s),'`id`='.$v['id']);
							}
						}
					}
					if(!$errors)
					{
						if($groups)
						{
							$kw=array();
							$R=Eleanor::$Db->Query('SELECT `keyword` FROM `'.P.'config_groups` WHERE `id`'.Eleanor::$Db->In(array_unique($groups)));
							while($temp=$R->fetch_row())
								$kw[]=$temp[0];
							Eleanor::LoadOptions($kw,true,false);
						}
						return GoAway(empty($_GET['group']) ? false : array('sg'=>$_GET['group']));
					}
					$a=array();
					$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `id`'.Eleanor::$Db->In(array_keys($errors)));
					while($temp=$R->fetch_assoc())
					{
						$this->PreControls($a,$temp,true);
						if(isset($a[$temp['id']]))
							$a[$temp['id']]['error']=$errors[$temp['id']];
					}

					$GLOBALS['title'][]=$lang['op_errors'];
					return$this->ShowListOptions($a,false,0,$lang['op_errors']);
			}
		switch($param)
		{
			case'full':
			case'groups':
				if($this->a_grs===false)
					return GoAway();
				$GLOBALS['title'][]=$lang['grlist'];

				#Если мы показываем лишь определенные группы - двигать их бессмысленно.
				$this->a_move&=!$this->a_grs;

				$this->DoNavigation();
				$R=Eleanor::$Db->Query('SELECT `g`.`id`,`gl`.`title`,`gl`.`descr`,`g`.`protected`,`g`.`pos`,COUNT(`c`.`id`) `cnt` FROM `'.P.'config_groups` `g` INNER JOIN `'.P.'config_groups_l` `gl` USING(`id`) LEFT JOIN `'.P.'config` `c` ON `g`.`id`=`c`.`group` WHERE `gl`.`language`IN(\'\',\''.Language::$main.'\')'.($this->a_grs ? ' AND `g`.`id`'.Eleanor::$Db->In($this->a_grs) : '').' GROUP BY `g`.`id` ORDER BY '.($this->a_grs ? '`gl`.`title`' : '`g`.`pos`').' ASC');
				if($this->a_move)
					$nums=$R->num_rows-1;
				$items=array();
				$El=Eleanor::getInstance();
				while($a=$R->fetch_assoc())
				{
					$a['_buttons']=array(
						'reset'=>$El->Url->Construct(array($this->pp.'greset'=>$a['id'])),
						'show'=>$El->Url->Construct(array($this->pp.'sg'=>$a['id'])),
					);
					if($this->a_move)
					{
						if($a['pos']>1)
							$a['_buttons']['up']=$El->Url->Construct(array($this->pp.'gup'=>$a['id']));
						if($a['pos']<=$nums)
							$a['_buttons']['down']=$El->Url->Construct(array($this->pp.'gdown'=>$a['id']));
					}
					if($this->opts_mod)
						$a['_buttons']['default']=$El->Url->Construct(array($this->pp.'gdefault'=>$a['id']));
					if($this->g_mod)
						$a['_buttons']['delete']=$a['_buttons']['edit']=false;
					$items[$a['id']]=array_slice($a,1);
				}
				$links=array();
				if($this->a_search)
					$links['search']=$El->Url->Construct(array($this->pp.'sdo'=>'search'));
				return Eleanor::$Template->SettGroupsCover($items,$links);
			case'group':
				return$this->ShowGroup($value);
			break;
			case'options':
				$this->a_move=false;
				if(!$this->a_opts)
					throw new EE($lang['nooptions'],EE::DEV);
				$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`'.Eleanor::$Db->In($this->a_opts));
				if($R->num_rows==0)
					throw new EE($lang['nooptions'],EE::DEV);
				$a=array();
				while($temp=$R->fetch_assoc())
					$this->PreControls($a,$temp);
				return$this->ShowListOptions($a,false);
		}
	}

	/**
	 * Запись ссылок навигации в массив$Eleanor->module['links_settings']
	 *
	 * @param string $gid ID группы настроек
	 */
	protected function DoNavigation($gid=false)
	{
		$El=Eleanor::getInstance();
		$El->module['links_settings']=array(
			'opts'=>$gid ? $El->Url->Construct(array($this->pp.'sg'=>$gid)) : false,
			'grs'=>$El->Url->Prefix(),
			'import'=>$this->a_import ? $El->Url->Construct(array($this->pp.'sdo'=>'import')) : false,
			'export'=>$this->a_export ? $El->Url->Construct(array($this->pp.'sdo'=>'export')) : false,
			'addoption'=>false,
			'addgroup'=>false,
		);
	}

	/**
	 * Получение настроек группы
	 *
	 * @param int|string $id Идентификатор группы. Если $id число - это ID группы, нет - имя. Если равно -1, то настройки будут браться из $allowed_opts
	 */
	public function ShowGroup($id=0)
	{
		$lang=Eleanor::$Language['settings'];
		$show_grs=false;
		if($id==-1)
		{
			if(!$this->a_opts or !is_array($this->a_opts))
				return GoAway();
			$GLOBALS['title'][]=$lang['options'];
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `id`'.Eleanor::$Db->In($this->a_opts));
			$this->DoNavigation();
		}
		elseif($id)
		{
			$show_grs=true;
			$where=is_int($id) ? '`id`='.$id : '`name`='.Eleanor::$Db->Escape($id);
			$R=Eleanor::$Db->Query('SELECT `id`,`title`,`descr` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND '.$where.' LIMIT 1');
			if($R->num_rows==0)
				return false;
			list($id,$GLOBALS['title'][],Eleanor::getInstance()->module['descr'])=$R->fetch_row();
			$this->a_grs[]=$id;
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `group`='.$id.' ORDER BY `pos` ASC');
			$this->DoNavigation($id);
		}
		elseif($this->opts_wg)
		{
			$GLOBALS['title'][]=$lang['ops_without_g'];
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `group` NOT IN (SELECT `id` FROM `'.P.'config_groups`)');
			$this->DoNavigation();
		}
		else
			return GoAway();
		$a=array();
		while($temp=$R->fetch_assoc())
			$this->PreControls($a,$temp);
		return$this->ShowListOptions($a,$show_grs,$id);
	}

	/**
	 * Преварительная обработка контролов настроек
	 *
	 * @param array $a Ссылка на массив с результатом работы
	 * @param array $temp Дамп контрола из БД
	 * @param bool $bypost Признак того, что значение контрола нужно брать из POST запроса
	 */
	protected function PreControls(&$a,$temp,$bypost=false)
	{
		$temp['multilang']&=Eleanor::$vars['multilang'];
		#Не отображаем опции, недоступные для этого языка
		if(!$temp['multilang'] and $temp['language'] and $temp['language']!=Language::$main)
			return;
		Eleanor::$nolog=true;#Иногда у unserialize рвет крышу, когда перевод строки замыкает контакты
		if(!isset($a[$temp['id']]) or $temp['multilang'])
		{
			$temp['extra']=$temp['extra'] ? eval('return '.$temp['extra'].';') : array();
			if($temp['serialized'])
			{
				$temp['value']=unserialize($temp['value']);
				$temp['default']=unserialize($temp['default']);
			}
			if(isset($a[$temp['id']]))
			{
				$a[$temp['id']]['value'][$temp['language']]=$temp['value'];
				$a[$temp['id']]['default'][$temp['language']]=$temp['default'];
				if(!$temp['language'] or $temp['language']==Language::$main)
				{
					$a[$temp['id']]['title']=$temp['title'];
					$a[$temp['id']]['descr']=$temp['descr'];
					$a[$temp['id']]['extra']=$temp['extra'];
					$a[$temp['id']]['startgroup']=$temp['startgroup'];
				}
			}
			else
			{
				if($temp['multilang'])
				{
					$temp['value']=array($temp['language']=>$temp['value']);
					$temp['default']=array($temp['language']=>$temp['default']);
					$temp['extra']=array($temp['language']=>$temp['extra']);
				}
				$a[$temp['id']]=$temp;
			}
			$a[$temp['id']]['_bypost']=$bypost;
		}
		Eleanor::$nolog=false;
	}

	/**
	 * Получение интерфейса списка настроек
	 *
	 * @param array $a Массив контролов настроек
	 * @param bool $gshow Флаг отображения подгрупп опций
	 * @param int $group ID группы
	 * @param string $error Ошибка
	 */
	protected function ShowListOptions(array$a,$gshow=true,$group=0,$error='')
	{
		$controls=$langs=$errors=$values=array();
		$word=isset($_POST['search']) ? (string)Eleanor::$POST['search'] : false;
		$El=Eleanor::getInstance();
		$cnt=count($a);
		foreach($a as &$v)
		{
			$reset=$v['value']!=$v['default'];
			$controls[$v['name']]=array(
				'titles'=>array(
					'title'=>$v['title'],
					'descr'=>$v['descr'],
					'group'=>$gshow ? $v['startgroup'] : '',
					'gtitle'=>isset($v['gtitle']) ? $v['gtitle'] : '',#Заголовок группы (для поиска).
				),
				'id'=>$v['id'],
				'group'=>$v['group'],
				'pos'=>$v['pos'],
				'protected'=>$v['protected'],
				'type'=>$v['type'],
				'bypost'=>$v['_bypost'],
				'load_eval'=>$v['eval_load'],
				'multilang'=>$v['multilang'],
				'_aup'=>$this->a_move && $gshow && $v['pos']>1 ? $El->Url->Construct(array($this->pp.'oup'=>$v['id'])) : false,
				'_adown'=>$this->a_move && $gshow && $v['pos']<$cnt ? $El->Url->Construct(array($this->pp.'odown'=>$v['id'])) : false,
				'_areset'=>$reset ? $El->Url->Construct(array($this->pp.'oreset'=>$v['id'])) : false,
				'_adefault'=>$reset && $this->opts_mod ? $El->Url->Construct(array($this->pp.'odefault'=>$v['id'])) : false,
				'_aedit'=>false,
				'_adelete'=>false,
				'_agroup'=>isset($v['gtitle']) ? $El->Url->Construct(array($this->pp.'sg'=>$v['group'])) : false,
			);
			if(isset($v['error']))
				$errors[$v['name']]=$v['error'];
			if($v['multilang'])
				$langs[$v['name']]=array(
					'default'=>$v['default'],
					'value'=>$v['value'],
					'options'=>$v['extra'],
				);
			else
				$controls[$v['name']]+=array(
					'default'=>$v['default'],
					'value'=>$v['value'],
					'options'=>$v['extra'],
				);
		}
		if($controls)
		{
			if(is_callable($this->sort_cb))
				$controls=call_user_func($this->sort_cb,$controls);
			$C=new Controls;
			$C->throw=false;
			$values=$C->DisplayControls($controls,$langs);
		}
		$links=array(
			'form'=>$El->Url->Construct(array($this->pp.'sdo'=>'saveoptions',$this->pp.'group'=>$group)),
		);
		return Eleanor::$Template->SettOptionsList($controls,$values,$controls ? $C->errors : array(),$errors,$links,$word,$gshow,$error);
	}

	/**
	 * Оптимизация групп
	 *
	 * @param bool $redirect Флаг выполнения перехода в корень по завершению работы метода
	 */
	protected function OptimizeGroups($redirect=true)
	{
		if(!Eleanor::$our_query)
			GoAway();
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config_groups` ORDER BY `pos`');
		$cnt=1;
		while($a=$R->fetch_row())
			Eleanor::$Db->Update(P.'config_groups',array('pos'=>$cnt++),'`id`='.$a[0].' LIMIT 1');
		if($redirect)
			GoAway(true);
	}

	/**
	 * Оптимизация настроек группы
	 *
	 * @param int $group ID группы
	 * @param bool $redirect Флаг выполнения перехода в корень по завершению работы метода
	 */
	protected function OptimizeOptions($group,$redirect=true)
	{
		if(!Eleanor::$our_query)
			GoAway();
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config` WHERE `group`=\''.$group.'\' ORDER BY `pos` ASC');
		$cnt=1;
		while ($a=$R->fetch_row())
			Eleanor::$Db->Update(P.'config',array('pos'=>$cnt++),'`id`='.$a[0].' LIMIT 1');
		if($redirect)
			GoAway(array('sg'=>$group));
	}

	/**
	 * Интерфейс экспорта настроек
	 */
	protected function Export()
	{
		if(!$this->a_export)
			return '';
		$this->DoNavigation();

		$grs=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`title`,`descr` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') ORDER BY `pos` ASC');
		while($a=$R->fetch_assoc())
			$grs[$a['id']]=array_slice($a,1);

		$R=Eleanor::$Db->Query('SELECT `group`,`id`,`title`,`descr` FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `group`'.Eleanor::$Db->In(array_keys($grs)).' ORDER BY `pos` ASC');
		while($a=$R->fetch_assoc())
			$grs[$a['group']]['opts'][$a['id']]=array_slice($a,2);
		$groups=isset($_POST['groups']) ? (array)$_POST['groups'] : array();
		$options=isset($_POST['options']) ? (array)$_POST['options'] : array();
		$GLOBALS['title'][]=Eleanor::$Language['settings']['export'];
		return Eleanor::$Template->SettExport($grs,$groups,$options);
	}

	/**
	 * Интерфейс импорта настроек
	 *
	 * @param array|false $info Статус импорта, либо false, если импорт не произошел
	 * @param string $error Ошибка импорта
	 */
	protected function Import($info=false,$error='')
	{
		if(!$this->a_import)
			return '';
		$GLOBALS['title'][]=Eleanor::$Language['settings']['import'];
		$this->DoNavigation();
		return Eleanor::$Template->SettImport($info,$error);
	}

	/**
	 * Непосредственное осуществление импорта
	 *
	 * @param string $text Содержимое xml файла импорта
	 */
	public function ProcessImport($text)
	{
		$lang=Eleanor::$Language['settings'];
		try
		{
			$S=new SimpleXMLElement($text,LIBXML_NOCDATA | LIBXML_NOERROR);
		}
		catch(Exception $E)
		{
			throw new EE(sprintf($lang['incorrect_s_file'],$E->getMessage()),EE::UNIT,array('code'=>$E->getCode()));
		}

		$version=$S->attributes();
		$version=$version['version'];
		$odel=$gdel=$options=$groups=array();
		if(isset($S->delete))
		{
			if(isset($S->delete->option))
				foreach($S->delete->option as $v)
				{
					$ops=$v->attributes();
					if(isset($ops['group']))
						$odel[(string)$ops['group']][]=(string)$v;
					else
						$odel[]=(string)$v;
				}
			if(isset($S->delete->group))
				foreach($S->delete->group as $v)
					$gdel[]=(string)$v;
		}

		if(isset($S->group))
		{
			$exgrs=array();
			$nextpos=0;
			$R=Eleanor::$Db->Query('SELECT `id`,`name`,`protected`,`pos` FROM `'.P.'config_groups`');
			while($a=$R->fetch_assoc())
			{
				$exgrs[$a['name']]=array($a['id'],$a['protected']);
				if($a['pos']>$nextpos);
					$nextpos=$a['pos'];
			}

			#Да. Нужны такие извращения, чтобы получить значения. $grs=(array)$S->group - не канает, поскольку в случае если содержимого $S->group больше, чем 1 то мы получим только первый элемент
			$grs=array();
			foreach($S->group as $v)
				$grs[]=$v;
			foreach($grs as &$G)
			{
				$attrs=array();
				$temp=$G->attributes();
				foreach($temp as $k=>$v)
					$attrs[$k]=(string)$v;
				if(empty($attrs['name']))
					throw new EE($lang['im_nogrname'],EE::UNIT);
				$attrs+=array(
					'protected'=>0,
					'keyword'=>$attrs['name'],
					'onexists'=>'ignore',
					'pos'=>++$nextpos,
				);
				foreach(array('title','descr') as $need)
					if(!isset($G->{$need}))
						$attrs[$need]='';
					elseif(is_string($G->title))
						$attrs[$need]=$G->{$need};
					else
					{
						$attrs[$need]=array();
						$temp=(array)$G->{$need};
						foreach($temp as $k=>$v)
							if(isset(Eleanor::$langs[$k]))
								$attrs[$need][$k]=$v;
						if(!$attrs[$need])
							foreach($temp as $v)
							{
								$attrs[$need]=$v;
								break;
							}
						if(!$attrs[$need])
							$attrs[$need]='';
					}
				$groups[]=$attrs;
			}
			unset($grs);
		}
		if(isset($S->option))
		{
			#Да. Нужны такие извращения, чтобы получить значения. $grs=(array)$S->group - не канает, поскольку в случае если содержимого $S->group больше, чем 1 то мы получим только первый элемент
			$opts=array();
			foreach($S->option as $v)
				$opts[]=$v;
			foreach($opts as &$O)
			{
				$attrs=array();
				$temp=$O->attributes();
				foreach($temp as $k=>$v)
					$attrs[$k]=(string)$v;
				if(empty($attrs['name']))
					throw new EE($lang['im_noopname'],EE::UNIT);
				$attrs+=array(
					'protected'=>0,
					'onexists'=>'ignore',
					'group'=>'',
					'type'=>'input',
					'multilang'=>0,
					'eval_save'=>isset($O->evalsave) ? (string)$O->evalsave : '',
					'eval_load'=>isset($O->evalload) ? (string)$O->evalload : '',
				);
				foreach(array('title','descr','value','serialized','default','extra','startgroup') as $need)
					if(!isset($O->{$need}))
						$attrs[$need]='';
					elseif(is_string($O->title))
						$attrs[$need]=$O->{$need};
					else
					{
						$attrs[$need]=array();
						$temp=(array)$O->{$need};
						foreach($temp as $k=>$v)
							if(isset(Eleanor::$langs[$k]))
								$attrs[$need][$k]=$v;
						if(!$attrs[$need])
							foreach($temp as $v)
							{
								$attrs[$need]=$v;
								break;
							}
						if(!$attrs[$need])
							$attrs[$need]='';
					}
				$options[$attrs['group']][$attrs['name']]=$attrs;
			}
			unset($grs);
		}
		if(CHARSET!='utf-8')
		{
			foreach($groups as &$v)
				foreach($v as &$t)
					if(is_array($t))
						foreach($t as &$l)
							$l=mb_convert_encoding($l,CHARSET,'utf-8');
					else
						$t=mb_convert_encoding($t,CHARSET,'utf-8');
			foreach($options as &$gr)
				foreach($gr as &$v)
					foreach($v as &$t)
						if(is_array($t))
							foreach($t as &$l)
								$l=mb_convert_encoding($l,CHARSET,'utf-8');
						else
							$t=mb_convert_encoding($t,CHARSET,'utf-8');
		}

		$res=array(
			'odel'=>array(),
			'gdel'=>array(),
			'options_upd'=>array(),
			'groups_upd'=>array(),
			'options_ins'=>array(),
			'groups_ins'=>array(),
		);

		$toupdate=array();
		if($odel)
		{
			$todel=array();
			foreach($odel as $k=>$v)
			{
				$gr=false;
				if(is_array($v))
				{
					$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config_groups` WHERE `name`='.Eleanor::$Db->Escape($k).' LIMIT 1');
					list($gr)=$R->fetch_row();
				}
				$R=Eleanor::$Db->Query('SELECT `id`,`group` FROM `'.P.'config` WHERE `name`'.Eleanor::$Db->In($v).($gr ? ' AND `group`='.$gr : ''));
				while($a=$R->fetch_assoc())
				{
					$toupdate[]=$a['group'];
					$todel[]=$a['id'];
				}
			}
			if($todel)
			{
				Eleanor::$Db->Delete(P.'config','`id`'.Eleanor::$Db->In($todel));
				Eleanor::$Db->Delete(P.'config_l','`id`'.Eleanor::$Db->In($todel));
			}
			$res['odel']=$todel;
		}
		if($gdel)
		{
			$todel=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`name` FROM `'.P.'config_groups` WHERE `name`'.Eleanor::$Db->In($gdel));
			while($a=$R->fetch_assoc())
			{
				$toupdate[]=$a['name'];
				$todel[]=$a['id'];
			}
			Eleanor::$Db->Delete(P.'config_groups','`id`'.Eleanor::$Db->In($todel));
			Eleanor::$Db->Delete(P.'config_groups_l','`id`'.Eleanor::$Db->In($todel));
			$res['gdel']=$todel;
		}

		foreach($groups as &$v)
		{
			$isat=is_array($v['title']);
			if(isset($exgrs[$v['name']]))
			{
				switch($v['onexists'])
				{
					case'update':
						if(!$exgrs[$v['name']][1])
							Eleanor::$Db->Update(P.'config_groups',array('protected'=>$v['protected'],'keyword'=>$v['keyword']),'`id`='.$exgrs[$v['name']][0].' LIMIT 1');
						foreach(Eleanor::$langs as $lng=>&$_)
						{
							if(Eleanor::$vars['multilang'] and $isat and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
								continue;
							Eleanor::$Db->Update(P.
								'config_groups_l',
								array(
									'title'=>$isat ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
								),
								'`id`='.$exgrs[$v['name']][0].(Eleanor::$vars['multilang'] && $isat ? ' AND `language`=\''.$lng.'\'' : '')
							);
						}
						$res['groups_upd'][]=$v;
					break;
					case'full':
						Eleanor::$Db->Update(P.'config_groups',array('protected'=>$v['protected'],'keyword'=>$v['keyword']),'`id`='.$exgrs[$v['name']][0].' LIMIT 1');
						foreach(Eleanor::$langs as $lng=>&$_)
						{
							if(Eleanor::$vars['multilang'] and $isat and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
								continue;
							Eleanor::$Db->Update(P.
								'config_groups_l',
								array(
									'title'=>$isat ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
								),
								'`id`='.$exgrs[$v['name']][0].(Eleanor::$vars['multilang'] && $isat ? ' AND `language`=\''.$lng.'\'' : '')
							);
							if(Eleanor::$vars['multilang'])
								break;
						}
						$res['groups_upd'][]=$v;
					break;
					default:
						continue;
				}
			}
			else
			{
				$id=Eleanor::$Db->Insert(P.'config_groups',$ins=array('name'=>$v['name'],'protected'=>$v['protected'],'keyword'=>$v['keyword'],'pos'=>$v['pos']));
				if($id)
				{
					$exgrs[$v['name']]=array($id,$v['protected']);
					foreach(Eleanor::$langs as $lng=>&$_)
					{
						if(Eleanor::$vars['multilang'] and $isat and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
							continue;
						Eleanor::$Db->Insert(P.
							'config_groups_l',
							array(
								'id'=>$id,
								'language'=>Eleanor::$vars['multilang'] && $isat ? $lng : '',
								'title'=>$isat ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
								'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
							)
						);
						if(Eleanor::$vars['multilang'])
							break;
					}
					$res['groups_ins'][]=$v+array('id'=>$id);
				}
			}
		}

		$exopts=$grspos=array();
		if($options)
		{
			$onames=array();
			foreach($options as &$v)
				$onames=array_merge($onames,array_keys($v));
			$R=Eleanor::$Db->Query('SELECT `o`.`id`,`o`.`name`,`o`.`protected`,`g`.`name` `gname` FROM `'.P.'config` `o` INNER JOIN `'.P.'config_groups` `g` ON `g`.`id`=`o`.`group` WHERE `g`.`name`'.Eleanor::$Db->In(array_keys($options)).' AND `o`.`name`'.Eleanor::$Db->In($onames));
			while($a=$R->fetch_assoc())
				$exopts[$a['gname']][$a['name']]=array($a['id'],$a['protected']);
			if(count($exopts)<count($options))
			{
				$temp=array();
				foreach($options as $k=>&$v)
					if(!isset($exopts[$k]) and isset($exgrs[$k]))
						$temp[]=$exgrs[$k][0];
				if($temp)
				{
					$R=Eleanor::$Db->Query('SELECT `g`.`name`,MAX(`c`.`pos`) `pos` FROM `'.P.'config_groups` `g` LEFT JOIN `'.P.'config` `c` ON `c`.`group`=`g`.`id` WHERE `g`.`id`'.Eleanor::$Db->In($temp));
					while($a=$R->fetch_assoc())
						$grspos[$a['name']]=$a['pos'];
				}
			}
		}
		foreach($options as $grn=>&$gr)
			foreach($gr as &$v)
			{
				$isat=is_array($v['title']);
				if(isset($exopts[$grn][$v['name']]))
				{
					switch($v['onexists'])
					{
						case'update':
							if(!$exopts[$grn][$v['name']][1])
								Eleanor::$Db->Update(P.'config',array('type'=>$v['type'],'protected'=>$v['protected'],'eval_load'=>$v['eval_load'],'eval_save'=>$v['eval_save']),'`id`='.$exopts[$grn][$v['name']][0].' LIMIT 1');
							foreach(Eleanor::$langs as $lng=>&$_)
							{
								if(Eleanor::$vars['multilang'] and $isat and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
									continue;
								$ser=is_array($v['serialized']) ? Eleanor::FilterLangValues($v['serialized'],$lng) : $v['serialized'];
								$upd=array(
									'title'=>$isat ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
									'default'=>is_array($v['default']) ? Eleanor::FilterLangValues($v['default'],$lng) : $v['default'],
									'extra'=>is_array($v['extra']) ? Eleanor::FilterLangValues($v['extra'],$lng) : $v['extra'],
									'startgroup'=>is_array($v['startgroup']) ? Eleanor::FilterLangValues($v['startgroup'],$lng) : $v['startgroup'],
								);
								if($ser)
									$upd['default']=serialize(eval('return '.$upd['default'].';'));
								Eleanor::$Db->Update(P.'config_l',$upd,'`id`='.$exopts[$grn][$v['name']][0].(Eleanor::$vars['multilang'] && $isat ? ' AND `language`=\''.$lng.'\'' : ''));
								if(Eleanor::$vars['multilang'])
									break;
							}
							$res['options_upd'][]=$v;
						break;
						case'full':
							Eleanor::$Db->Update(P.'config',array('type'=>$v['type'],'protected'=>$v['protected'],'eval_load'=>$v['eval_load'],'eval_save'=>$v['eval_save']),'`id`='.$exopts[$grn][$v['name']][0].' LIMIT 1');
							foreach(Eleanor::$langs as $lng=>&$_)
							{
								if(Eleanor::$vars['multilang'] and $isat and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
									continue;
								$upd=array(
									'title'=>$isat ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
									'value'=>is_array($v['value']) ? Eleanor::FilterLangValues($v['value'],$lng) : $v['value'],
									'serialized'=>is_array($v['serialized']) ? Eleanor::FilterLangValues($v['serialized'],$lng) : $v['serialized'],
									'default'=>is_array($v['default']) ? Eleanor::FilterLangValues($v['default'],$lng) : $v['default'],
									'extra'=>is_array($v['extra']) ? Eleanor::FilterLangValues($v['extra'],$lng) : $v['extra'],
									'startgroup'=>is_array($v['startgroup']) ? Eleanor::FilterLangValues($v['startgroup'],$lng) : $v['startgroup'],
								);
								if($upd['serialized'])
								{
									$upd['value']=serialize(eval('return '.$upd['value'].';'));
									$upd['default']=serialize(eval('return '.$upd['default'].';'));
								}
								Eleanor::$Db->Update(P.'config_l',$upd,'`id`='.$exopts[$grn][$v['name']][0].(Eleanor::$vars['multilang'] && $isat ? ' AND `language`=\''.$lng.'\'' : ''));
								if(Eleanor::$vars['multilang'])
									break;
							}
							if(isset($exgrs[$v['group']]))
								$toupdate[]=$exgrs[$v['group']];
							$res['options_upd'][]=$v;
						break;
						default:
							continue;
					}
				}
				else
				{
					if(!isset($grspos[$v['group']]))
						$grspos[$v['group']]=1;
					$id=Eleanor::$Db->Insert(P.'config',$ins=array('name'=>$v['name'],'group'=>isset($exgrs[$v['group']]) ? $exgrs[$v['group']][0] : 0,'type'=>$v['type'],'protected'=>$v['protected'],'pos'=>$grspos[$v['group']]++,'multilang'=>$v['multilang'],'eval_load'=>$v['eval_load'],'eval_save'=>$v['eval_save']));
					if($id)
					{
						foreach(Eleanor::$langs as $lng=>&$_)
						{
							if(Eleanor::$vars['multilang'] and $isat and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
								continue;
							$upd=array(
								'id'=>$id,
								'language'=>Eleanor::$vars['multilang'] && $isat ? $lng : '',
								'title'=>$isat ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
								'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
								'value'=>is_array($v['value']) ? Eleanor::FilterLangValues($v['value'],$lng) : $v['value'],
								'serialized'=>is_array($v['serialized']) ? Eleanor::FilterLangValues($v['serialized'],$lng) : $v['serialized'],
								'default'=>is_array($v['default']) ? Eleanor::FilterLangValues($v['default'],$lng) : $v['default'],
								'extra'=>is_array($v['extra']) ? Eleanor::FilterLangValues($v['extra'],$lng) : $v['extra'],
								'startgroup'=>is_array($v['startgroup']) ? Eleanor::FilterLangValues($v['startgroup'],$lng) : $v['startgroup'],
							);
							if($upd['serialized'])
							{
								$upd['value']=serialize(eval('return '.$upd['value'].';'));
								$upd['default']=serialize(eval('return '.$upd['default'].';'));
							}
							Eleanor::$Db->Insert(P.'config_l',$upd);
							if(Eleanor::$vars['multilang'])
								break;
						}
						if(isset($exgrs[$v['group']]))
							$toupdate[]=$exgrs[$v['group']];
						$res['options_ins'][]=$v+array('id'=>$id);
					}
				}
			}
		if($toupdate)
		{
			$R=Eleanor::$Db->Query('SELECT `keyword` FROM `'.P.'config_groups` WHERE `id`'.Eleanor::$Db->In($toupdate));
			$toupdate=array();
			while($temp=$R->fetch_row())
				$toupdate[]=$temp[0];
			Eleanor::LoadOptions($toupdate,true,false);
		}
		return$res;
	}
}