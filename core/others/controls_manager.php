<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/
class Controls_Manager extends Controls
{
	/**
	 * Получение интерфейса конфигурирования контрола
	 *
	 * @param array $co Редактируемый контрол, ключи массива:
	 * Обязательные параметры:
	 * type - тип контрола
	 * Необязательные параметры
	 * bypost Указание брать значения из POST запроса
	 * template Название оформления таблицы
	 * controls_name Имя превьюшки
	 * settings_name Имя настроек контрола
	 * Следующие ключи могут быть мультиязычными в зависимости от $this->langs
	 * default значение по умолчанию каждого контрола для отображения
	 * options дополнительные параметры каждого контрола
	 * @param bool $ajax признак AJAX запроса.
	 * @param bool $onlyprev признак того, что нужно загрузить только превью контрола без загрузки самих настроек контрола
	 */
	public function ConfigureControl(array$co=array(),$ajax=false,$onlyprev=false)
	{
		if($ajax)
		{
			Eleanor::StartSession($co['session']);
			if(!isset($_SESSION[__class__]) or !is_array($_SESSION[__class__]))
				throw new EE('Session lost!',EE::USER);
			$co+=array('options'=>array())+$_SESSION[__class__];
			$co['bypost']=false;
		}
		else
		{
			Eleanor::StartSession();
			$_SESSION[__class__]=$co;
		}

		$co+=array(
			'type'=>'input',
			'bypost'=>false,
			'template'=>'EditControlTable',
			'controls_name'=>array('controls'),
			'settings_name'=>array('settings'),
			'value'=>null,
			'default'=>null,
			'options'=>array(),
			'load_eval'=>null,
		);
		$this->ScanControls();
		$types=array('input','text','items','select','editor','user','check','date');
		if(self::$controls)
			$types=array_merge($types,self::$controls);
		if(!in_array($co['type'],$types))
			$co['type']='input';

		$result=array();
		$oldname=$this->arrname;

		if($co['bypost'])
		{
			$this->arrname=$co['controls_name'];
			$co['type']=$this->SaveControl(array('type'=>'select','name'=>'type'));
		}
		elseif($ajax)
			$co['bypost']=true;

		$this->arrname=$co['settings_name'];
		$setts=$this->GetSettings($co['type']);
		$sgroup='';
		if(isset($setts[0]))
		{
			/*
				Контролы сгруппированы по общим признакам. Так, скажем, у input и text очень много схожих свойств, поэтому они помещаются в одну
				группу. Тогда при переключении между типами - параметры input-a будут переносится на text и наоборот.
			*/
			$sgroup=$setts[0];
			unset($setts[0]);
		}
		$error=false;
		if($co['bypost'])
			#Сохраняем контролы.
			try
			{
				$co['options']=$this->SaveControls($setts);
			}
			catch(EE$E)
			{
				$error=$E->getMessage();
			}
		$repopts=$settslv=array();

		$corrlo=$this->langs && !$co['bypost'];
		$fopts=Eleanor::FilterLangValues($co['options']);
		foreach($setts as $k=>&$v)
		{
			if($corrlo)
				if(empty($v['multilang']))
				{
					if(isset($fopts[$k]))
					{
						$settslv[$k]['value']=$fopts[$k];
						$repopts[$k]=$fopts[$k];
					}
				}
				else
				{
					$opts=array();
					foreach($this->langs as &$l)
					{
						$opts[$l]=Eleanor::FilterLangValues($co['options'],$l);
						$opts[$l]=isset($opts[$l][$k]) ? $opts[$l][$k] : null;
					}
					$settslv[$k]['value']=$opts;
					$repopts[$k]=$opts;
				}
			elseif(isset($co['options'][$k]))
				$settslv[$k]['value']=$co['options'][$k];

			$v['bypost']=$co['bypost'];
			$result['td'][$k]=array(isset($v['title']) ? $v['title'] : '',isset($v['descr']) ? $v['descr'] : '');#Title Descriptions
		}
		if($corrlo)
			$co['options']=$repopts;

		if(!$onlyprev)
			try
			{
				$result['settings']=$this->DisplayControls($setts,$settslv);
			}
			catch(EE$E)
			{
				$result['settings']=array();
				$error=$E->getMessage();
			}

		$this->arrname=$co['controls_name'];
		if(!$error)
			try
			{
				if($this->langs)
				{
					/*
						Поскольку $this->SaveControls() передает нам значения в виде name=>lang=>value, а в месте использоватения $options добавляется еще один ключ массива выше ('options'=>$co['options'])
						Таким образом нам необходимо переделать струкутуру options=>name=>lang=>value в options=>lang=>name=>value поскльку в $this->DisplayControl мы должны передать лишь name=>value.
					*/
					$options=array();
					foreach($co['options'] as $k=>&$v)
					{
						if(empty($setts[$k]['multilang']))
							foreach($this->langs as &$l)
								$options[$l][$k]=$v;
						else
							foreach($this->langs as &$l)
								if(isset($v[$l]))
									$options[$l][$k]=$v[$l];
					}
					$co['options']=$options;
					$result['preview']=$this->DisplayControls(array('preview'=>array('type'=>$co['type'],'bypost'=>$co['bypost'],'load_eval'=>$co['load_eval'],'multilang'=>true)),array('preview'=>array('value'=>$co['default'],'options'=>$co['options'])));
					$result['preview']=isset($result['preview']['preview']) ? $result['preview']['preview'] : '';
				}
				else
					$result['preview']=$this->DisplayControl(array('type'=>$co['type'],'name'=>'preview','bypost'=>$co['bypost'],'options'=>$co['options'],'value'=>$co['default'],'load_eval'=>$co['load_eval'],'multilang'=>false));
			}
			catch(EE $E)
			{
				$error=$E->getMessage();
			}
		if(!$ajax)
		{
			$options=array();
			foreach($types as &$v)
				$options[$v]=isset(Eleanor::$Language['controls'][$v]) ? Eleanor::$Language['controls'][$v] : $v;
			asort($options,SORT_STRING);
			$result['type']=$this->DisplayControl(array('type'=>'select','name'=>'type','bypost'=>$co['bypost'],'value'=>$co['type'],'options'=>array('options'=>$options,'extra'=>array('id'=>'type-selector','onchange'=>'EC.ChangeType()','style'=>'width:80%'))));
		}
		$this->arrname=$oldname;
		return Eleanor::$Template->$co['template']($result,$ajax,$error,$onlyprev,$sgroup,$co['type']);
	}

	/**
	 * Сохранение результатов конфигурируемого контрола
	 *
	 * @param array $co Редактируемый контрол
	 * $prevlang Сохранить настройку только с одним заданным языком и размножить ее для других языков.
	 */
	public function SaveConfigureControl(array$co=array(),$prevlang=false)
	{
		$co+=array(
			'controls_name'=>array('controls'),
			'settings_name'=>array('settings'),
		);

		$this->ScanControls();
		$types=array('input','text','items','select','editor','user','check');
		if(self::$controls)
			$types=array_merge($types,self::$controls);

		$oldname=$this->arrname;
		$this->arrname=$co['controls_name'];
		$result['type']=$this->SaveControl(array('type'=>'select','name'=>'type'));

		if(!in_array($result['type'],$types))
			throw new EE(Eleanor::$Language['controls']['type_not_found'],EE::USER);

		$setts=$this->GetSettings($result['type']);
		unset($setts[0]);
		#Сохраняем контролы.
		$this->arrname=$co['settings_name'];
		$result['options']=$this->SaveControls($setts);
		$this->arrname=$co['controls_name'];
		if($this->langs)
		{
			$options=array();
			foreach($result['options'] as $k=>&$v)
			{
				if(empty($setts[$k]['multilang']))
					foreach($this->langs as &$l)
						$options[$l][$k]=$v;
				else
					foreach($this->langs as &$l)
						if(isset($v[$l]))
							$options[$l][$k]=$v[$l];
			}
			$result['options']=$options;
			if($prevlang and in_array($prevlang,$this->langs))
			{
				$old=$this->langs;
				$this->langs=array($prevlang);
			}
			else
				$prevlang=false;
			try
			{
				$def=$this->SaveControls(array('preview'=>array('type'=>$result['type'],'multilang'=>true)+$co),array('preview'=>array('options'=>$options)));
			}
			catch(EE$E)
			{
				if(isset($old))
					$this->langs=$old;
				throw new EE($E->getMessage(),$E->getCode(),$E->extra);
			}
			$result['default']=isset($def['preview']) ? $def['preview'] : array($prevlang=>null);
			if($prevlang)
			{
				$this->langs=$old;
				foreach($this->langs as &$l)
					if(!isset($result['default'][$l]))
						$result['default'][$l]=$result['default'][$prevlang];
			}
		}
		else
			$result['default']=$this->SaveControl(array('type'=>$result['type'],'name'=>'preview','options'=>$result['options'])+$co);
		$this->arrname=$oldname;
		return$result;
	}

	/**
	 * Получение массива настроек контрола определенного типа
	 *
	 * @param string $type Название контрола
	 */
	public function GetSettings($type)
	{
		$ml=(bool)$this->langs;
		$lang=Eleanor::$Language['controls'];
		$a=array(
			'title'=>$lang['extra_tag_params'],
			'descr'=>$lang['extra_tag_params_'],
			'type'=>'input',
			'multilang'=>$ml,
			'default'=>$ml ? array(''=>array()) : array(),
			'load'=>function($co)
			{
				if($co['multilang'])
				{
					$r=array();
					foreach($co['value'] as $l=>&$param)
						if(is_array($param))
						{
							$value='';
							foreach($param as $k=>&$v)
								$value.=' '.$k.'='.(strpos($v,'"')===false ? '"'.$v.'"' : "'".$v."'");
							$r[$l]=ltrim($value);
						}
					return$r;
				}
				elseif(is_array($co['value']))
				{
					$value='';
					foreach($co['value'] as $k=>&$v)
						$value.=' '.$k.'='.(strpos($v,'"')===false ? '"'.$v.'"' : "'".$v."'");
					return array('value'=>$value);
				}
			},
			'save'=>function($co)
			{
				if($co['multilang'])
				{
					foreach($co['value'] as &$v)
						$v=$v ? Strings::ParseParams($v) : array();
					return$co['value'];
				}
				return$co['value'] ? Strings::ParseParams($co['value']) : array();
			},
		);
		switch($type)
		{
			case'user':
				$res=array(
					'load_eval'=>array(
						'title'=>$lang['user_load_eval'],
						'descr'=>sprintf($lang['incoming_vars'],'$co,$Obj'),
						'type'=>'text',
						'multilang'=>$ml,
						'default'=>$ml ? array(''=>'return \'\';') : 'return \'\';',
						'options'=>array(
							'htmlsafe'=>false,
							'extra'=>array(
								'style'=>'height:250px;overflow:auto',
							),
						),
						'save'=>function($co) use ($lang)
						{
							$val=$co['multilang'] ? $co['value'] : array(Language::$main=>$co['value']);
							foreach($val as $k=>&$v)
							{
								$v=str_replace("\\r",'',$v);
								if(!$v)
									throw new EE(sprintf($lang['no_load_eval'],Eleanor::$langs[$k]['name']),EE::DEV);
								ob_start();
								if(create_function('',$v)===false)
								{
									$err=ob_get_contents();
									ob_end_clean();
									Eleanor::getInstance()->e_g_l=error_get_last();
									throw new EE(sprintf($lang['error_load_eval'],Eleanor::$langs[$k]['name']).'<br />'.$err,EE::DEV);
								}
								ob_end_clean();
							};
							return$co['value'];
						},
					),
					'save_eval'=>array(
						'title'=>$lang['user_save_eval'],
						'descr'=>sprintf($lang['incoming_vars'],'$co,$Obj'),
						'type'=>'text',
						'multilang'=>$ml,
						'default'=>$ml ? array(''=>'return \'\';') : 'return \'\';',
						'options'=>array(
							'htmlsafe'=>false,
							'extra'=>array(
								'style'=>'height:250px;overflow:auto',
							),
						),
						'save'=>function($co) use ($lang)
						{
							$val=$co['multilang'] ? $co['value'] : array(Language::$main=>$co['value']);
							foreach($val as $k=>&$v)
							{
								$v=str_replace("\\r",'',$v);
								if(!$v)
									throw new EE(sprintf($lang['no_save_eval'],Eleanor::$langs[$k]['name']),EE::DEV);
								ob_start();
								if(create_function('',$v)===false)
								{
									$err=ob_get_contents();
									ob_end_clean();
									Eleanor::getInstance()->e_g_l=error_get_last();
									throw new EE(sprintf($lang['error_save_eval'],Eleanor::$langs[$k]['name']).'<br />'.$err,EE::DEV);
								}
								ob_end_clean();
							};
							return$co['value'];
						},
					),
				);
			break;
			case'items':
			case'select':
				$res=array(
					'select',#Группа контрола
					'eval'=>array(
						'type'=>'user',
						'multilang'=>$ml,
						'param'=>'eval',
						'default'=>$ml ? array(''=>'return array();') : 'return array();',
						'options'=>array(
							'load'=>array($this,'SettingsSelectLoad'),
							'save'=>array($this,'SettingsSelectSave'),
						)
					),
					'options'=>array(
						'type'=>'user',
						'multilang'=>$ml,
						'param'=>'options',
						'default'=>$ml ? array(''=>array()) : array(),
						'options'=>array(
							'load'=>array($this,'SettingsSelectLoad'),
							'save'=>array($this,'SettingsSelectSave'),
						)
					),
					'type'=>array(
						'title'=>$lang['select_source'],
						'descr'=>'',
						'multilang'=>$ml,
						'type'=>'user',
						'param'=>'type',
						'options'=>array(
							'save'=>array($this,'SettingsSelectSave'),
							'load'=>array($this,'SettingsSelectLoad'),
						)
					),
					'extra'=>$a,
				);
				#Вверху - дополнение для функции обработки
			break;
			case'editor':
				$E=new Editor;
				$res=array(
					'type'=>array(
						'title'=>$lang['editor_type'],
						'multilang'=>$ml,
						'type'=>'select',
						'default'=>$ml ? array(''=>'') : '',
						'options'=>array('options'=>array(''=>$lang['editor_default'])+$E->editors),
					)
				);
			break;
			case'check':
				$res=array('extra'=>$a);
			break;
			case'text':
				$res=array(
					'text',#Группа контрола
					'extra'=>$a
				);
			break;
			case'input':
				$res=array(
					'text',#Группа контрола
					'type'=>array(
						'title'=>'Input type',
						'type'=>'select',
						'options'=>array(
							'options'=>array(
								'text'=>$lang['t_text'],
								'color'=>$lang['t_color'],
								'date'=>$lang['t_date'],
								'datetime'=>$lang['t_datetime'],
								'datetime-local'=>$lang['t_datetime-local'],
								'email'=>$lang['t_email'],
								'number'=>$lang['t_number'],
								'range'=>$lang['t_range'],
								'tel'=>$lang['t_tel'],
								'time'=>$lang['t_time'],
								'url'=>$lang['t_url'],
								'month'=>$lang['t_month'],
								'week'=>$lang['t_week'],
							),
						),
					),
					'extra'=>$a
				);
			break;
			case'date':
				$res=array(
					'date',#Группа контрола
					'time'=>array(
						'title'=>$lang['time_select'],
						'type'=>'check',
						'default'=>false,
					),
					'extra'=>$a
				);
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();
				if(!class_exists('Control'.$type,false) and (!in_array($type,self::$controls) or !include(Eleanor::$root.'core/controls/'.$type.'.php')))
					throw new EE('Unknown control '.$type,EE::DEV);
				$cl='Control'.$type;
				$res=$cl::GetSettings($this);
		}
		return$res;
	}

	/**
	 * Получение массива настроек для контролов select, item, items
	 *
	 * @param array $co Массив данных контрола
	 */
	public function SettingsSelectLoad(array$co)
	{static$data=array();
		$n=$co['controlname'];
		if($co['param']=='type')
		{
			$values=$co['multilang'] ? $data[$co['name']['lang']] : $data;
			if($co['multilang'])
				unset($data[$co['name']['lang']]);
			else
				$data=array();
			return Eleanor::$Template->SettingsSelectLoad(array('values'=>$values,'name'=>$n,'value'=>$co['value']));
		}
		elseif($co['multilang'])
			$data[$co['name']['lang']][$co['param']]=array($n,$co['value']);
		else
			$data[$co['param']]=array($n,$co['value']);
	}

	/**
	 * Сохранение настроек для контролов select, item, items
	 *
	 * @param array $co Массив данных контрола
	 */
	public function SettingsSelectSave($co)
	{
		switch($co['param'])
		{
			case'options':
				$t=$this->GetPostVal($co['name'],array('name'=>array(),'value'=>array()));
				if(is_array($t) and isset($t['name'],$t['value']) and is_array($t['name']) and is_array($t['value']) and count($t['name'])==count($t['value']) and $t['value'])
					return array_combine($t['name'],$t['value']);
				return array();
			break;
			case'eval':
				return$this->GetPostVal($co['name'],'');
			break;
			case'type':
				return$this->GetPostVal($co['name'],'eval');
		}
	}
}