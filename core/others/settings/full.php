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

class Settings extends BaseClass
{
	public
		$pp,#Префикс параметров
		$sort_cb,#Функция сортировки опций
		$passed=true,#Признак того, что пройдена внутренняя проверка того, что пользователь действительно пришел со своим (а не с эмулированным запросом)
		$a_grs=array(),#Разрешенные группы для изменения настроек. Если равно false - группы редактировать запрещено
		$a_opts=array(),#Разрешенные настройки для изменения. Весьма редкая опция
		$only_opts=true,#Разрешить изменение только опций, относящихся к категориям выше.
		$g_mod=false,#Разрешить создание, редактирование и удаление групп.
		$opts_mod=false,#Разрешить создание, редактирование и удаление настроек.
		$a_move=true,#Разрешить перемещение групп и настроек
		$a_search=false,#Разрешить поиск
		$opts_wg=false,#Показывать опции без групп (для админа)
		$a_import=false,#Разрешить импорт настроек
		$a_export=false;#Разрешить экспорт настроек

	public static function AddTabs($text,$tabs=1)
	{
		$s=str_repeat("\t",$tabs);
		return$s.str_replace("\n","\n".$s,trim($text));
	}

	final public function __construct()
	{
		Eleanor::$Template->queue[]='Settings';

		$fatal=false;
		$message='';
		do
		{
			if(!defined('ELEANOR_COPYRIGHT'))
			{
				$fatal=true;
				$message='1';
				break;
			}
			$nu=CHARSET!='utf-8';
			$ec=$nu ? mb_convert_encoding(ELEANOR_COPYRIGHT,'utf-8') : ELEANOR_COPYRIGHT;
			$s=file_get_contents(Eleanor::$root.'js/core.js',false,null,0,300);
			$sd=md5(ELEANOR_VERSION.'-=CMS Eleanor=-'.md5(Eleanor::$domain.'-=Eleanor CMS=-'.Eleanor::$site_path));
			if(defined('RC_'.$sd))
			{
				if(strpos($ec,'<!-- ]]></script> --><a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © <!-- Eleanor CMS Team http://eleanor-cms.ru/copyright.php -->'.idate('Y'))!==0)
				{
					$fatal=true;
					$message='';
					break;
				}
			}
			elseif(strpos($ec,'<!-- ]]></script> --><a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © <!-- Eleanor CMS Team http://eleanor-cms.ru/copyright.php -->'.idate('Y'))!==0)
			{
				$fatal=true;
				$message='';
				break;
			}
			elseif(strpos($s,'Copyright © Eleanor CMS')===false or strpos($s,'URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su')===false)
			{
				$fatal=true;
				$message='js/core.js';
				break;
			}
			$s=file_get_contents(Eleanor::$root.'core/core.php',false,null,0,300);
			if($nu)
				$s=mb_convert_encoding($s,'utf-8');
			if(strpos($s,'Copyright © Eleanor CMS')===false or strpos($s,'URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su')===false)
			{
				$fatal=true;
				$message='core/core.php';
				break;
			}
		}while(false);
		if($fatal)
		{
			$html='<!DOCTYPE html><html><head><title>Violated copyrights!</title><meta http-equiv="Content-Type" content="text/html; charset='.DISPLAY_CHARSET.'" /><base href="'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'" /><style type="text/css">body, div { color:#1d1a15; font-size: 11px; font-family: Tahoma, Helvetica, sans-serif; } body { text-align: left; height: 100%; line-height: 142%; padding: 0; margin: 20px; background-color: #FFFFFF; } hr { height: 1px; border: solid #d8d8d8 0px; border-top-width: 1px; } .copyright {position:fixed; bottom:10px; right:10px; } a { text-decoration:none; } </style></head><body><span style="font-size:18px">Violated copyrights!</span><hr />Violated copyrights!<!-- '.$message.' --> You are using illegal copy of Eleanor CMS. Visit <a href="http://eleanor-cms.ru" title="CMS Eleanor">http://eleanor-cms.ru</a> to get original and legal copy of Eleanor CMS.<div class="copyright">Powered by <a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © '.idate('Y').'</div></body></html>';
			foreach(Eleanor::$services as &$v)
				if(is_file(Eleanor::$root.$v['file']))
				{
					rename(Eleanor::$root.$v['file'],Eleanor::$root.substr($v['file'],0,strrpos($v['file'],'.')+1).'bak');
					file_put_contents(Eleanor::$root.$v['file'],$html);
				}
			rename(Eleanor::$root.'core/core.php',Eleanor::$root.'core/core.bak');
			rename(Eleanor::$root.'config_general.php',Eleanor::$root.'config_general.bak');
			file_put_contents(Eleanor::$root.'index.html',$html);
			file_put_contents(Eleanor::$root.Eleanor::$filename,$html);
			Start('');
			die($html);
		}
	}

	/*
		Основная функция
		Допустимые значения $param :
			full - отобразить весь интерфейс настроек для админа (без ограничений)
			groups - отобразить интерфейс групп с учетом настроек ограничений выше
			group - отобразить настройки определенной группы при этом в $value следует передать идентификатор этой группы
	*/
	public function GetInterface($param,$value='')
	{
		if($param=='full')
		{
			$this->a_move=$this->a_export=$this->a_search=$this->a_import=$this->opts_wg=$this->opts_mod=$this->g_mod=true;
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
		elseif(isset($_GET[$this->pp.'gedit']) and $this->g_mod)
		{
			$id=(int)$_GET[$this->pp.'gedit'];
			if($post)
				return$this->SaveGroup($id);
			return$this->AddEditGroup($id);
		}
		elseif(isset($_GET[$this->pp.'oedit']) and $this->opts_mod)
		{
			$id=(int)$_GET[$this->pp.'oedit'];
			if($post)
				return$this->SaveOption($id);
			return$this->AddEditOption($id);
		}
		elseif(isset($_GET[$this->pp.'gdelete']))
		{
			$id=(int)$_GET[$this->pp.'gdelete'];
			$R=Eleanor::$Db->Query('SELECT `title`,`keyword`,`pos` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`='.$id.' AND `protected`=0 LIMIT 1');
			if(!$a=$R->fetch_assoc() or !$this->g_mod or !Eleanor::$our_query)
				return GoAway(true);
			if(isset($_POST['ok']))
			{
				Eleanor::$Db->Update(P.'config_groups',array('!pos'=>'`pos`-1'),'`pos`>'.$a['pos']);
				Eleanor::$Db->Delete(P.'config_l','`id`IN(SELECT `id` FROM `'.P.'config` WHERE `group`='.$id.')');
				Eleanor::$Db->Delete(P.'config','`group`='.$id);
				Eleanor::$Db->Delete(P.'config_groups','`id`='.$id);
				Eleanor::$Db->Delete(P.'config_groups_l','`id`='.$id);
				Eleanor::LoadOptions($a['keyword'],true,false);
				return GoAway(true);
			}
			$this->DoNavigation($id);
			$GLOBALS['title'][]=$lang['delc'];
			if(isset($_GET['noback']))
				$back='';
			else
				$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
			return Eleanor::$Template->SettGroupDelete($a,$back);
		}
		elseif(isset($_GET[$this->pp.'odelete']))
		{
			$id=(int)$_GET[$this->pp.'odelete'];
			$R=Eleanor::$Db->Query('SELECT `title`,`group`,`protected` FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc() or $a['protected'] or !$this->opts_mod or !Eleanor::$our_query)
				return GoAway(true);

			if(isset($_POST['ok']))
			{
				Eleanor::$Db->Delete(P.'config','`id`='.$id);
				Eleanor::$Db->Delete(P.'config_l','`id`='.$id);
				$R=Eleanor::$Db->Query('SELECT `keyword` FROM `'.P.'config_groups` WHERE `id`='.$a['group'].' LIMIT 1');
				if($temp=$R->fetch_row())
					Eleanor::LoadOptions($temp[0],true,false);
				return GoAway(empty($_POST['back']) ? array('sg'=>$a['group']) : $_POST['back']);
			}
			$GLOBALS['title'][]=$lang['delc'];
			$this->DoNavigation($a['group']);
			if(isset($_GET['noback']))
				$back='';
			else
				$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
			return Eleanor::$Template->SettDelete($a,$back);
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
				case'addgroup';
					if(!$this->g_mod)
						return GoAway();
					if($post)
						return$this->SaveGroup(0);
					return$this->AddEditGroup(0);
				break;
				case'addoption';
					if(!$this->opts_mod)
						return GoAway();
					if($post)
						return$this->SaveOption(0);
					return$this->AddEditOption(0,array(),isset($_GET[$this->pp.'parent']) ? (int)$_GET[$this->pp.'parent'] : 0);
				break;
				case'search':
					if(!$this->a_search)
						return GoAway();
					$word=isset($_POST['search']) ? Eleanor::$POST['search'] : '';
					if(strlen($word)<3)
					{						$GLOBALS['title'][]=$lang['error'];
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
										'pos'=>$temp['pos'],
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
								$GR->addAttribute('pos',$gr['pos']);
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
							return$this->Import('',$lang['f_not_load']);
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
						return$this->Import($a ? $lang['import_result'](count($a['gdel']),count($a['odel']),count($a['groups_ins']),count($a['groups_upd']),count($a['options_ins']),count($a['options_upd'])) : '',$error);
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
									$errors[$v['id']][$lng]=isset($E->addon['code']) && $E->addon['code']==1 ? $lang['error_in_code'] : $E->getMessage();
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
								$errors[$v['id']]=(isset($E->addon['code']) and $E->addon['code']==1) ? $lang['error_in_code'] : $E->getMessage();
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
					{
						$a['_buttons']['edit']=$El->Url->Construct(array($this->pp.'gedit'=>$a['id']));
						if(!$a['protected'])
							$a['_buttons']['delete']=$El->Url->Construct(array($this->pp.'gdelete'=>$a['id']));
					}
					$items[$a['id']]=array_slice($a,1);
				}
				$links=array();
				if($this->a_search)
					$links['search']=$El->Url->Construct(array($this->pp.'sdo'=>'search'));
				if($this->a_search)
					$links['wg']=$El->Url->Construct(array($this->pp.'sg'=>'no'));
				return Eleanor::$Template->SettGroupsCover($items,$links);
			case'group':
				return$this->ShowGroup($value);
			break;
			case'options':
				$this->a_move=false;
				if(!$this->a_opts)
					throw new EE($lang['nooptions'],EE::INFO);
				$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` INNER JOIN `'.P.'config_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`'.Eleanor::$Db->In($this->a_opts));
				if($R->num_rows==0)
					throw new EE($lang['nooptions'],EE::INFO);
				$a=array();
				while($temp=$R->fetch_assoc())
					$this->PreControls($a,$temp);
				return$this->ShowListOptions($a,false);
		}
	}

	protected function DoNavigation($gid=false)
	{		$El=Eleanor::getInstance();
		$El->module['links_settings']=array(
			'grs'=>$El->Url->Prefix(),
			'opts'=>$gid ? $El->Url->Construct(array($this->pp.'sg'=>$gid)) : false,
			'addoption'=>$this->opts_mod ? $El->Url->Construct(array($this->pp.'sdo'=>'addoption',$this->pp.'parent'=>$gid)) : false,
			'addgroup'=>$this->g_mod ? $El->Url->Construct(array($this->pp.'sdo'=>'addgroup')) : false,
			'import'=>$this->a_import ? $El->Url->Construct(array($this->pp.'sdo'=>'import')) : false,
			'export'=>$this->a_export ? $El->Url->Construct(array($this->pp.'sdo'=>'export')) : false,
		);
	}

	/*
		Показать настройки из группы
		Если $id - числов - это ИД группы, нет - имя.
		Если задать $id=-1, то будут браться настройки из $allowed_opts
	*/
	public function ShowGroup($id=0)
	{		$lang=Eleanor::$Language['settings'];
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

	protected function PreControls(&$a,$temp,$bypost=false)
	{
		$temp['multilang']&=Eleanor::$vars['multilang'];
		#Не отображаем опции, недоступные для этого языка
		if(!$temp['multilang'] and $temp['language'] and $temp['language']!=Language::$main)
			return;
		Eleanor::$nolog=true;#Иногда у unserialize рвет крышу, когда перевод строки замыкает контакты
		if(!isset($a[$temp['id']]) or $temp['multilang'])
		{			$temp['extra']=$temp['extra'] ? eval('return '.$temp['extra'].';') : array();
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

	protected function ShowListOptions(array$a,$gshow=true,$group=0,$error='')
	{
		$controls=$langs=$errors=array();
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
				'_aedit'=>$this->opts_mod ? $El->Url->Construct(array($this->pp.'oedit'=>$v['id'])) : false,
				'_adelete'=>!$v['protected'] && $this->opts_mod ? $El->Url->Construct(array($this->pp.'oedit'=>$v['id'])) : false,
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

	protected function OptimizeGroups($redirect=true)
	{
		if(!Eleanor::$our_query)
			GoAway();
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config_groups` ORDER BY `pos`');
		$cnt=1;
		while($a=$R->fetch_row($result))
			Eleanor::$Db->Update(P.'config_groups',array('pos'=>$cnt++),'`id`='.$a[0].' LIMIT 1');
		if($redirect)
			GoAway(true);
	}

	protected function AddEditGroup($id,$errors=array())
	{
		$lang=Eleanor::$Language['settings'];
		if($id)
		{
			$this->DoNavigation($id);
			if(!$errors)
			{
				$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config_groups` WHERE id='.$id.' LIMIT 1');
				if(!$values=$R->fetch_assoc())
					return GoAway(true);
				$values['_onelang']=false;
				$values['title']=$values['descr']=array();
				$R=Eleanor::$Db->Query('SELECT `language`,`title`,`descr` FROM `'.P.'config_groups_l` WHERE `id`='.$id);
				while($temp=$R->fetch_assoc())
					if(!Eleanor::$vars['multilang'] and (!$temp['language'] or $temp['language']==Language::$main))
					{
						$values['title']=$temp['title'];
						$values['descr']=$temp['descr'];
						if(!$temp['language'])
							break;
					}
					elseif(!$temp['language'] and Eleanor::$vars['multilang'])
					{
						$values['title'][Language::$main]=$temp['title'];
						$values['descr'][Language::$main]=$temp['descr'];
						$values['_onelang']=true;
						break;
					}
					elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$temp['language']]))
					{
						$values['title'][$temp['language']]=$temp['title'];
						$values['descr'][$temp['language']]=$temp['descr'];
					}
				if(!is_array($values['title']) or count($values['title'])==1 and isset($values['title'][LANGUAGE]))
					$values['_onelang']=true;
				/* Этот участок нужен для напоминания о том, что если нужного языка нет в БД - то его следует добавить очищенным
					foreach(Eleanor::$langs as $k=>&$v)
						if(!isset($values['title'][$k]))
							$values['title'][$k]=$values['descr'][$k]='';
				*/
			}
			$GLOBALS['title'][]=$lang['editing_g'];
		}
		else
		{
			$GLOBALS['title'][]=$lang['adding_g'];
			$values=array(
				'name'=>'',
				'keyword'=>'',
				'pos'=>'',
				'protected'=>1,
				'_onelang'=>Eleanor::$vars['multilang'],
			);
			$values['title']=$values['descr']=Eleanor::$vars['multilang'] ? array_combine(array_keys(Eleanor::$langs),array_fill(0,count(Eleanor::$langs),'')) : '';
			$this->DoNavigation();
		}
		if($errors)
		{
			if($errors===true)
				$errors=array();
			$bypost=true;
			$values['pos']=isset($_POST['pos']) ? (int)$_POST['pos'] : 1;
			$values['_onelang']=isset($_POST['_onelang']);
			if(Eleanor::$vars['multilang'])
				foreach(Eleanor::$langs as $k=>$v)
				{
					$values['title'][$k]=isset($_POST['title'][$k]) ? $_POST['title'][$k] : '';
					$values['descr'][$k]=isset($_POST['descr'][$k]) ? $_POST['descr'][$k] : '';
				}
			else
			{
				$values['title']=isset($_POST['title']) ? $_POST['title'] : '';
				$values['descr']=isset($_POST['descr']) ? $_POST['descr'] : '';
			}
			if($id)
			{
				$R=Eleanor::$Db->Query('SELECT `name`,`keyword`,`protected` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `id`='.$id.' LIMIT 1');
				list($values['name'],$values['keyword'],$values['protected'])=$R->fetch_row();
			}
			else
			{
				$values['protected']=isset($_POST['protected']);
				$values['keyword']=isset($_POST['keyword']) ? $_POST['keyword'] : '';
				$values['name']=isset($_POST['name']) ? $_POST['name'] : '';
			}
		}
		else
			$bypost=false;
		if(isset($_GET['noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
		$links=array(
			'delete'=>!$id || $values['protected'] ? false : Eleanor::getInstance()->Url->Construct(array($this->pp.'gdelete'=>$id)),
		);
		return Eleanor::$Template->SettAddEditGroup($id,$values,$links,$errors,$bypost,$back);
	}

	protected function SaveGroup($id)
	{
		$protected=false;
		if(is_int($id))
		{
			$R=Eleanor::$Db->Query('SELECT `protected`,`pos` FROM `'.P.'config_groups` WHERE `id`='.$id.' LIMIT 1');
			list($protected,$pos)=$R->fetch_row();
		}
		$values=array('pos'=>isset($_POST['pos']) ? $_POST['pos'] : 1);
		$lang=Eleanor::$Language['settings'];
		$errors=array();
		if(!$protected)
		{
			$values+=array(
				'name'=>isset($_POST['name']) ? trim($_POST['name']) : '',
				'keyword'=>isset($_POST['keyword']) ? $_POST['keyword'] : '',
				'protected'=>isset($_POST['protected']),
			);
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config_groups` WHERE `name`='.Eleanor::$Db->Escape($values['name']).($id ? ' AND `id`!='.$id : '').' LIMIT 1');
			if($R->num_rows>0)
				$errors[]='GROUP_EXISTS';
			$values['keyword']=Strings::CleanForExplode($values['keyword']);
			if($values['keyword'])
			{
				$values['keyword']=explode(',',$values['keyword']);
				foreach($values['keyword'] as $k=>&$v)
				{
					$v=trim($v);
					if(!$v)
						unset($values['keyword'][$k]);
				}
				$values['keyword']=join(',',$values['keyword']);
			}
		}
		if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
		{
			$langs=(empty($_POST['lang']) or !is_array($_POST['lang'])) ? array() : $_POST['lang'];
			$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
			if(!$langs)
				$langs=array(Language::$main);
		}
		else
			$langs=array('');
		if(Eleanor::$vars['multilang'])
		{
			$title=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : array();
			foreach($langs as &$v)
				if(empty($title[$v ? $v : Language::$main]))
				{					$er='_'.strtoupper($v);
					$errors['EMPTY_GROUP_NAME'.$er]=$lang['empty_gt']($v);
				}
			unset($v);#Необходимо. Поскольку в в месте 1 (см ниже) после >In($langs), значение получается в пастрофах ($lang['english']=="'english'"
		}
		else
		{
			$title=isset($_POST['title']) ? (string)Eleanor::$POST['title'] : '';
			if($title=='')
				$errors['EMPTY_GROUP_NAME']=$lang['empty_gt']();
		}

		if($errors)
			return$this->AddEditGroup($id,$errors);

		if($id)
		{
			$values['pos']=(int)$values['pos'];
			if($values['pos']<=0)
				$values['pos']=1;
			if($pos!=$values['pos'])
			{
				Eleanor::$Db->Update(P.'config_groups',array('!pos'=>'`pos`-1'),'`pos`>'.$pos);
				Eleanor::$Db->Update(P.'config_groups',array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos']);
			}
			Eleanor::$Db->Update(P.'config_groups',$values,'id='.$id.' LIMIT 1');
			Eleanor::$Db->Delete(P.'config_groups_l','`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
			#Место 1. Смотри выше!
			foreach($langs as &$v)
			{
				$lng=$v ? $v : Language::$main;
				if(Eleanor::$vars['multilang'])
					$values=array(
						'id'=>$id,
						'language'=>$v,
						'title'=>$title[$lng],
						'descr'=>isset($_POST['descr'][$lng]) ? (string)$_POST['descr'][$lng] : '',
					);
				else
					$values=array(
						'id'=>$id,
						'language'=>$v,
						'title'=>$title,
						'descr'=>isset($_POST['descr']) ? (string)$_POST['descr'] : '',
					);
				Eleanor::$Db->Replace(P.'config_groups_l',$values);
			}
		}
		else
		{
			if(!$values['name'])
				return$this->AddEditGroup($id,$lang['empty_pn']);
			if($values['pos']=='')
			{
				$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.P.'config_groups`');
				list($pos)=$R->fetch_row();
				$values['pos']=$pos===null ? 1 : $pos+1;
			}
			else
			{
				if($values['pos']<=0)
					$values['pos']=1;
				Eleanor::$Db->Update(P.'config_groups',array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos']);
			}
			$id=Eleanor::$Db->Insert(P.'config_groups',$values);
			$values=array('id'=>array(),'language'=>array(),'title'=>array(),'descr'=>array());
			foreach($langs as &$v)
			{
				$values['id'][]=$id;
				$values['language'][]=$v;
				$lng=$v ? $v : Language::$main;
				if(Eleanor::$vars['multilang'])
				{
					$values['descr'][]=isset($_POST['descr'][$lng]) ? (string)$_POST['descr'][$lng] : '';
					$values['title'][]=$title[$lng];
				}
				else
				{
					$values['descr'][]=isset($_POST['descr']) ? (string)$_POST['descr'] : '';
					$values['title'][]=$title;
					break;
				}
			}
			Eleanor::$Db->Insert(P.'config_groups_l',$values);
		}
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}

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

	/*
<?xml version="1.0" encoding="cp1251"?>
<eleanoroptions version="1">
<group name="gname" protected="1" keyword="keywords" onexists="ignore" pos="1">
	<title>
		<english>Title</english>
		<russian>Заголовок</russian>
	</title>
	<descr>
		<english>Descr</english>
		<russian>Описание</russian>
	</descr>
</group>
<option name="opname" protected="1" group="grname" type="checkbox" cache="1" multilang="1" onexists="ignore" pos="1">
	<title>
		<english>Title</english>
		<russian>Заголовок</russian>
	</title>
	<descr>
		<english>Descr</english>
		<russian>Описание</russian>
	</descr>
	<value>
		<english>Value</english>
		<russian>Значение</russian>
	</value>
	<serialized>
		<english>1</english>
		<russian>1</russian>
	</serialized>
	<default>
		<english>Default</english>
		<russian>По умолчанию</russian>
	</default>
	<extra>
		<english>Extra</english>
		<russian>Экстра</russian>
	</extra>
	<extra>
		<english>Startgroup</english>
		<russian>Новая группа</russian>
	</extra>
	<evalsave>
	11111111111111111111111
	</evalsave>
	<evalload>
	222222222222222
	</evalload>
</option>
</eleanoroptions>
	*/

	protected function Import($message='',$error='')
	{
		if(!$this->a_import)
			return '';
		$GLOBALS['title'][]=Eleanor::$Language['settings']['import'];
		$this->DoNavigation();
		return Eleanor::$Template->SettImport($message,$error);
	}

	public function ProcessImport($text)
	{		$lang=Eleanor::$Language['settings'];
		try
		{
			$S=new SimpleXMLElement($text,LIBXML_NOCDATA | LIBXML_NOERROR);
		}
		catch(Exception $E)
		{
			throw new EE(sprintf($lang['incorrect_s_file'],$E->getMessage()),EE::INFO,array('code'=>$E->getCode()));
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
					throw new EE($lang['im_nogrname'],EE::INFO);
				$attrs+=array(
					'protected'=>0,
					'keyword'=>$attrs['name'],
					'onexists'=>'ignore',
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
					throw new EE($lang['im_noopname'],EE::INFO);
				$attrs+=array(
					'protected'=>0,
					'onexists'=>'ignore',
					'group'=>'',
					'type'=>'edit',
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
		$exgrs=array();
		$grspos=array(-1);
		$R=Eleanor::$Db->Query('SELECT `id`,`name`,`protected`,`pos` FROM `'.P.'config_groups`');
		while($a=$R->fetch_assoc())
		{
			$exgrs[$a['name']]=array($a['id'],$a['protected']);
			$grspos[$a['id']]=$a['pos'];
		}
		foreach($groups as &$v)
		{
			if(isset($exgrs[$v['name']]))
			{
				switch($v['onexists'])
				{
					case'update':
						if(!$exgrs[$v['name']][1])
							Eleanor::$Db->Update(P.'config_groups',array('protected'=>$v['protected'],'keyword'=>$v['keyword']),'`id`='.$exgrs[$v['name']][0].' LIMIT 1');
						foreach(Eleanor::$langs as $lng=>&$_)
						{
							if(Eleanor::$vars['multilang'] and is_array($v['title']) and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
								continue;
							Eleanor::$Db->Update(P.
								'config_groups_l',
								array(
									'title'=>is_array($v['title']) ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
								),
								'`id`='.$exgrs[$v['name']][0].(Eleanor::$vars['multilang'] ? ' AND `language`=\''.$lng.'\'' : '')
							);
						}
						$res['groups_upd'][]=$v;
					break;
					case'full':
						Eleanor::$Db->Update(P.'config_groups',array('protected'=>$v['protected'],'keyword'=>$v['keyword']),'`id`='.$exgrs[$v['name']][0].' LIMIT 1');
						foreach(Eleanor::$langs as $lng=>&$_)
						{
							if(Eleanor::$vars['multilang'] and is_array($v['title']) and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
								continue;
							Eleanor::$Db->Update(P.
								'config_groups_l',
								array(
									'title'=>is_array($v['title']) ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
								),
								'`id`='.$exgrs[$v['name']][0].(Eleanor::$vars['multilang'] ? ' AND `language`=\''.$lng.'\'' : '')
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
				$id=Eleanor::$Db->Insert(P.'config_groups',$ins=array('name'=>$v['name'],'protected'=>$v['protected'],'keyword'=>$v['keyword'],'pos'=>(in_array($v['pos'],$grspos) and max($grspos)>$v['pos']) ? $v['pos'] : max($grspos)+1));
				if($id)
				{
					$exgrs[$v['name']]=array($id,$v['protected']);
					$grspos[$v['name']]=$ins['pos'];
					foreach(Eleanor::$langs as $lng=>&$_)
					{
						if(Eleanor::$vars['multilang'] and is_array($v['title']) and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
							continue;
						Eleanor::$Db->Insert(P.
							'config_groups_l',
							array(
								'id'=>$id,
								'language'=>Eleanor::$vars['multilang'] ? $lng : '',
								'title'=>is_array($v['title']) ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
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
				if(isset($exopts[$grn][$v['name']]))
				{
					switch($v['onexists'])
					{
						case'update':
							if(!$exopts[$grn][$v['name']][1])
								Eleanor::$Db->Update(P.'config',array('type'=>$v['type'],'protected'=>$v['protected'],'eval_load'=>$v['eval_load'],'eval_save'=>$v['eval_save']),'`id`='.$exopts[$grn][$v['name']][0].' LIMIT 1');
							foreach(Eleanor::$langs as $lng=>&$_)
							{
								if(Eleanor::$vars['multilang'] and is_array($v['title']) and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
									continue;
								$ser=is_array($v['serialized']) ? Eleanor::FilterLangValues($v['serialized'],$lng) : $v['serialized'];
								$upd=array(
									'title'=>is_array($v['title']) ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
									'descr'=>is_array($v['descr']) ? Eleanor::FilterLangValues($v['descr'],$lng) : $v['descr'],
									'default'=>is_array($v['default']) ? Eleanor::FilterLangValues($v['default'],$lng) : $v['default'],
									'extra'=>is_array($v['extra']) ? Eleanor::FilterLangValues($v['extra'],$lng) : $v['extra'],
									'startgroup'=>is_array($v['startgroup']) ? Eleanor::FilterLangValues($v['startgroup'],$lng) : $v['startgroup'],
								);
								if($ser)
									$upd['default']=serialize(eval('return '.$upd['default'].';'));
								Eleanor::$Db->Update(P.'config_l',$upd,'`id`='.$exopts[$grn][$v['name']][0].(Eleanor::$vars['multilang'] ? ' AND `language`=\''.$lng.'\'' : ''));
								if(Eleanor::$vars['multilang'])
									break;
							}
							$res['options_upd'][]=$v;
						break;
						case'full':
							Eleanor::$Db->Update(P.'config',array('type'=>$v['type'],'protected'=>$v['protected'],'eval_load'=>$v['eval_load'],'eval_save'=>$v['eval_save']),'`id`='.$exopts[$grn][$v['name']][0].' LIMIT 1');
							foreach(Eleanor::$langs as $lng=>&$_)
							{
								if(Eleanor::$vars['multilang'] and is_array($v['title']) and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
									continue;
								$upd=array(
									'title'=>is_array($v['title']) ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
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
								Eleanor::$Db->Update(P.'config_l',$upd,'`id`='.$exopts[$grn][$v['name']][0].(Eleanor::$vars['multilang'] ? ' AND `language`=\''.$lng.'\'' : ''));
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
					$id=Eleanor::$Db->Insert(P.'config',$ins=array('name'=>$v['name'],'group'=>isset($exgrs[$v['group']]) ? $exgrs[$v['group']][0] : 0,'type'=>$v['type'],'protected'=>$v['protected'],'pos'=>(isset($grspos[$v['group']]) ? ++$grspos[$v['group']] : 1),'multilang'=>$v['multilang'],'eval_load'=>$v['eval_load'],'eval_save'=>$v['eval_save']));
					if($id)
					{
						foreach(Eleanor::$langs as $lng=>&$_)
						{
							if(Eleanor::$vars['multilang'] and is_array($v['title']) and isset($v['title'][LANGUAGE]) and $lng!=LANGUAGE)
								continue;
							$upd=array(
								'id'=>$id,
								'language'=>Eleanor::$vars['multilang'] ? $lng : '',
								'title'=>is_array($v['title']) ? Eleanor::FilterLangValues($v['title'],$lng) : $v['title'],
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

	protected function AddEditOption($id,$errors=array(),$group=0)
	{
		$lang=Eleanor::$Language['settings'];
		if($id)
		{
			if(!$errors)
			{
				$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'config` WHERE id='.$id.' LIMIT 1');
				if(!$values=$R->fetch_assoc())
					return GoAway(true);
				$values['_onelang']=false;
				$values['title']=$values['descr']=array();
				$R=Eleanor::$Db->Query('SELECT `language`,`title`,`descr`,`serialized`,`default`,`extra`,`startgroup` FROM `'.P.'config_l` WHERE `id`='.$id);
				while($temp=$R->fetch_assoc())
					if(!Eleanor::$vars['multilang'] and (!$temp['language'] or $temp['language']==Language::$main))
					{
						$values['title']=$temp['title'];
						$values['descr']=$temp['descr'];
						$values['extra']=$temp['extra'] ? eval('return '.$temp['extra'].';') : array();
						$values['default']=$temp['serialized'] ? unserialize($temp['default']) : $temp['default'];
						$values['startgroup']=$temp['startgroup'];
						if(!$temp['language'])
							break;
					}
					elseif(!$temp['language'] and Eleanor::$vars['multilang'])
					{
						$values['title'][Language::$main]=$temp['title'];
						$values['descr'][Language::$main]=$temp['descr'];
						$values['extra'][Language::$main]=$temp['extra'] ? eval('return '.$temp['extra'].';') : array();
						$values['default'][Language::$main]=$temp['serialized'] ? unserialize($temp['default']) : $temp['default'];
						$values['startgroup'][Language::$main]=$temp['startgroup'];
						$values['_onelang']=true;
						break;
					}
					elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$temp['language']]))
					{
						$values['title'][$temp['language']]=$temp['title'];
						$values['descr'][$temp['language']]=$temp['descr'];
						$values['extra'][$temp['language']]=$temp['extra'] ? eval('return '.$temp['extra'].';') : array();
						$values['default'][$temp['language']]=$temp['serialized'] ? unserialize($temp['default']) : $temp['default'];
						$values['startgroup'][$temp['language']]=$temp['startgroup'];
					}
				if(!is_array($values['title']) or count($values['title'])==1 and isset($values['title'][LANGUAGE]))
					$values['_onelang']=true;
				/* Этот участок нужен для напоминания о том, что если нужного языка нет в БД - то его следует добавить очищенным
					foreach(Eleanor::$langs as $k=>&$v)
						if(!isset($values['title'][$k]))
							$values['title'][$k]=$values['descr'][$k]=$values['value'][$k]=$values['default'][$k]=$values['serialized'][$k]=$values['extra'][$k]=$values['startgroup'][$k]='';
				*/
				$this->DoNavigation($values['group']);
			}
			$GLOBALS['title'][]=$lang['editing_opt'];
		}
		else
		{
			$GLOBALS['title'][]=$lang['adding_opt'];
			$values=array(
				'name'=>'',
				'type'=>'edit',
				'pos'=>'',
				'protected'=>0,
				'group'=>$group,
				'multilang'=>false,
				'_onelang'=>Eleanor::$vars['multilang'],
				'eval_load'=>'',
				'eval_save'=>'',
				'extra'=>array(),
			);
			$values['title']=$values['descr']=$values['value']=$values['serialized']=$values['default']=$values['startgroup']=Eleanor::$vars['multilang'] ? array_combine(array_keys(Eleanor::$langs),array_fill(0,count(Eleanor::$langs),'')) : '';
			$this->DoNavigation($values['group']);
		}
		if($errors)
		{
			if($errors===true)
				$errors=array();
			$bypost=true;
			$values['_onelang']=isset($_POST['_onelang']);
			$values['group']=isset($_POST['group']) ? (int)$_POST['group'] : 0;
			if($id!==false)
				$this->DoNavigation($values['group']);
			$values['pos']=isset($_POST['pos']) ? $_POST['pos'] : 1;
			if(Eleanor::$vars['multilang'])
				foreach(Eleanor::$langs as $k=>$v)
				{
					$values['title'][$k]=isset($_POST['title'][$k]) ? $_POST['title'][$k] : '';
					$values['descr'][$k]=isset($_POST['descr'][$k]) ? $_POST['descr'][$k] : '';
					$values['startgroup'][$k]=isset($_POST['startgroup'][$k]) ? $_POST['startgroup'][$k] : '';
				}
			else
			{
				$values['title']=isset($_POST['title']) ? $_POST['title'] : '';
				$values['descr']=isset($_POST['descr']) ? $_POST['descr'] : '';
				$values['startgroup']=isset($_POST['startgroup']) ? $_POST['startgroup'] : '';
			}
			$protected=false;
			if(is_int($id))
			{
				$R=Eleanor::$Db->Query('SELECT `name`,`protected`,`multilang`,`eval_load`,`eval_save` FROM `'.P.'config` WHERE `id`='.$id.' LIMIT 1');
				if($temp=$R->fetch_assoc() and $protected=$temp['protected'])
					$values=$temp+$values;
			}
			if(!$protected)
			{
				$values['name']=isset($_POST['name']) ? $_POST['name'] : '';
				$values['protected']=isset($_POST['protected']);
				$values['multilang']=isset($_POST['multilang']);
				$values['eval_load']=isset($_POST['eval_load']) ? $_POST['eval_load'] : '';
				$values['eval_save']=isset($_POST['eval_save']) ? $_POST['eval_save'] : '';
			}
		}
		else
			$bypost=false;
		$control='';
		$groups=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.P.'config_groups` INNER JOIN `'.P.'config_groups_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') ORDER BY `pos` ASC');
		while($temp=$R->fetch_assoc())
			$groups[$temp['id']]=$temp['title'];

		if(!$values['protected'] or !$id)
			$control=Eleanor::getInstance()->Controls_Manager->ConfigureControl(array(
				'type'=>isset($values['type']) ? $values['type'] : null,
				'bypost'=>$bypost,
				'default'=>isset($values['default']) ? $values['default'] : (Eleanor::$vars['multilang'] ? array() : null),
				'options'=>isset($values['extra']) ? $values['extra'] : array(),
				'load_eval'=>$errors ? '' : $values['eval_load'],
			));

		if(isset($_GET['noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
		$links=array(
			'delete'=>!$id || $values['protected'] ? false : Eleanor::getInstance()->Url->Construct(array($this->pp.'odelete'=>$id)),
		);
		return Eleanor::$Template->SettAddEditOption($id,$values,$groups,$control,$links,$bypost,$back,$errors);
	}

	protected function SaveOption($id)
	{		$lang=Eleanor::$Language['settings'];
		$protected=false;
		if($id)
		{
			$R=Eleanor::$Db->Query('SELECT `group`,`protected`,`pos` FROM `'.P.'config` WHERE `id`='.$id.' LIMIT 1');
			list($group,$protected,$pos)=$R->fetch_row();
		}
		$values=array(
			'pos'=>isset($_POST['pos']) ? $_POST['pos'] : 1,
			'multilang'=>isset($_POST['multilang']),
		);
		$lvalues=$errors=array();
		if(!$protected)
		{
			$values+=array(
				'group'=>isset($_POST['group']) ? (int)$_POST['group'] : 0,
				'name'=>isset($_POST['name']) ? trim((string)$_POST['name']) : '',
				'eval_load'=>isset($_POST['eval_load']) ? trim((string)$_POST['eval_load']) : '',
				'eval_save'=>isset($_POST['eval_save']) ? trim((string)$_POST['eval_save']) : '',
				'protected'=>$id ? 0 : isset($_POST['protected']),
			);
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config` WHERE `name`='.Eleanor::$Db->Escape($values['name']).' AND `group`='.$values['group'].($id ? ' AND `id`!='.$id : '').' LIMIT 1');
			if($R->num_rows>0)
				$errors[]='OPTION_EXISTS';
			try
			{
				$El=Eleanor::getInstance();
				$control=$El->Controls_Manager->SaveConfigureControl(empty($values['eval_save']) ? array() : array('save_eval'=>$values['eval_save']),$values['multilang'] ? false : Language::$main);
				$El->Controls_Manager->DisplayControls(
					array(
						'm'=>array(
							'type'=>$control['type'],
							'load_eval'=>$values['eval_load'],
							'multilang'=>Eleanor::$vars['multilang'],
						)
					),
					array(
						'm'=>array(
							'default'=>$control['default'],
							'options'=>$control['options'],
						)
					)
				);
				$values['type']=$control['type'];
				if(Eleanor::$vars['multilang'])
				{
					foreach($control['default'] as $k=>&$v)
						if($values['multilang'])
							$lvalues[$k]['default']=$v;
						else
							$lvalues[$k]['default']=isset($control['default'][Language::$main]) ? $control['default'][Language::$main] : '';
					foreach($control['options'] as $k=>&$v)
						foreach($v as $l=>&$d)
							$lvalues[$l]['extra'][$k]=$d;
				}
				else
					$lvalues=array(
						'default'=>$control['default'],
						'extra'=>$control['options'],
					);
			}
			catch(EE$E)
			{				$errors['ERROR']=$E->getMessage();
			}
		}

		if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
		{
			$langs=(empty($_POST['lang']) or !is_array($_POST['lang'])) ? array() : $_POST['lang'];
			$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
			if(!$langs)
				$langs=array(Language::$main);
		}
		else
			$langs=array('');
		if(Eleanor::$vars['multilang'])
		{			$title=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : array();
			foreach($langs as &$v)
				if(empty($title[$v ? $v : Language::$main]))
				{
					$er='_'.strtoupper($v);
					$errors['EMPTY_OPTION_TITLE'.$er]=$lang['empty_ot']($v);
				}
			unset($v);#Необходимо. Поскольку в в месте 1 (см ниже) после >In($langs), значение получается в пастрофах ($lang['english']=="'english'"
		}
		else
		{
			$title=isset($_POST['title']) ? (string)Eleanor::$POST['title'] : '';
			if($title=='')
				$errors['EMPTY_OPTION_TITLE']=$lang['empty_ot']();
		}

		if($errors)
			return$this->AddEditOption($id,$errors);

		if($id)
		{
			$values['pos']=(int)$values['pos'];
			if($values['pos']<=0)
				$values['pos']=1;
			if($pos!=$values['pos'] or (!$protected and $values['group']!=$group))
			{
				Eleanor::$Db->Update(P.'config',array('!pos'=>'`pos`-1'),'`pos`>'.$pos.' AND `group`='.$group);
				Eleanor::$Db->Update(P.'config',array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos'].' AND `group`='.($protected ? $group : $values['group']));
			}
			Eleanor::$Db->Update(P.'config',$values,'id='.$id.' LIMIT 1');
			Eleanor::$Db->Delete(P.'config_l','`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
			#Место 1. Смотри выше!
			foreach($langs as &$v)
			{
				$lng=$v ? $v : Language::$main;
				if(Eleanor::$vars['multilang'])
				{
					$a=array(
						'title'=>$title[$lng],
						'descr'=>isset($_POST['descr'][$lng]) ? (string)$_POST['descr'][$lng] : '',
						'startgroup'=>isset($_POST['startgroup'][$lng]) ? (string)Eleanor::$POST['startgroup'][$lng] : '',
					);
					if(!$protected)
					{
						$s=is_scalar($lvalues[$lng]['default']);
						$a+=array(
							'serialized'=>!$s,
							'default'=>$s ? $lvalues[$lng]['default'] : serialize($lvalues[$lng]['default']),
							'extra'=>empty($lvalues[$lng]['extra']) ? '' : var_export($lvalues[$lng]['extra'],true),
						);
					}
				}
				else
				{
					$a=array(
						'title'=>$title,
						'descr'=>isset($_POST['descr']) ? (string)$_POST['descr'] : '',
						'startgroup'=>isset($_POST['startgroup']) ? (string)Eleanor::$POST['startgroup'] : '',
					);
					if(!$protected)
					{
						$s=is_scalar($lvalues['default']);
						$a+=array(
							'serialized'=>!$s,
							'default'=>$s ? $lvalues['default'] : serialize($lvalues['default']),
							'extra'=>empty($lvalues['extra']) ? '' : var_export($lvalues['extra'],true),
						);
					}
				}
				#Сюда Replace нельзя лепить по той причине, что есть поле value, которое нельзя обнулять!
				$cnt=Eleanor::$Db->Update(P.'config_l',$a,'`id`='.$id.' AND `language`=\''.$v.'\' LIMIT 1');
				if($cnt==0)
				{
					$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'config_l` WHERE `id`='.$id.' AND `language`=\''.$v.'\' LIMIT 1');
					if($R->num_rows==0)
						Eleanor::$Db->Insert(P.'config_l',$a+array('id'=>$id,'language'=>$v));
				}
			}
		}
		else
		{
			if($values['pos']=='')
			{
				$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.P.'config` WHERE `group`='.$values['group']);
				list($pos)=$R->fetch_row();
				$values['pos']=$pos===null ? 1 : $pos+1;
			}
			else
			{
				if($values['pos']<=0)
					$values['pos']=1;
				Eleanor::$Db->Update(P.'config',array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos'].' AND `group`='.$values['group']);
			}
			$id=Eleanor::$Db->Insert(P.'config',$values);
			$a=array('id'=>array(),'language'=>array(),'title'=>array(),'descr'=>array(),'value'=>array(),'serialized'=>array(),'default'=>array(),'extra'=>array(),'startgroup'=>array());
			foreach($langs as &$v)
			{
				$a['id'][]=$id;
				$a['language'][]=$v;
				$lng=$v ? $v : Language::$main;
				if(Eleanor::$vars['multilang'])
				{
					$s=is_scalar($lvalues[$lng]['default']);
					$a['serialized'][]=!$s;
					$a['value'][]=$a['default'][]=$s ? $lvalues[$lng]['default'] : serialize($lvalues[$lng]['default']);
					$a['extra'][]=empty($lvalues[$lng]['extra']) ? '' : var_export($lvalues[$lng]['extra'],true).';';

					$a['descr'][]=isset($_POST['descr'][$lng]) ? (string)$_POST['descr'][$lng] : '';
					$a['title'][]=$title[$lng];
					$a['startgroup'][]=isset($_POST['startgroup'][$lng]) ? (string)Eleanor::$POST['startgroup'][$lng] : '';
				}
				else
				{
					$s=is_scalar($lvalues['default']);
					$a['serialized'][]=!$s;
					$a['value'][]=$a['default'][]=$s ? $lvalues['default'] : serialize($lvalues['default']);
					$a['extra'][]=empty($lvalues['extra']) ? '' : var_export($lvalues['extra'],true).';';

					$a['descr'][]=isset($_POST['descr']) ? $_POST['descr'] : '';
					$a['title'][]=$title;
					$a['startgroup'][]=isset($_POST['startgroup']) ? $_POST['startgroup'] : '';
					break;
				}
			}
			Eleanor::$Db->Insert(P.'config_l',$a);
		}

		$kw=array();
		$R=Eleanor::$Db->Query('SELECT `keyword` FROM `'.P.'config_groups` WHERE `id`='.Eleanor::$Db->Escape(isset($values['group']) ? $values['group'] : $group));
		while($temp=$R->fetch_row())
			$kw[]=$temp[0];
		Eleanor::LoadOptions($kw,true,false);
		if(empty($_POST['back']))
			GoAway(empty($values['group']) ? true : array('sg'=>$values['group']),301,'opt'.$id);
		else
			GoAway($_POST['back']);
	}
}