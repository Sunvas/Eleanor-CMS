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

interface ControlsBase
{
	/*
		Получение настроек контрола. Для его настройки
	*/
	public static function GetSettings($Obj);

	/*Отображение контрола
		$a - массив данных, включая $a['options']
	*/
	public static function Control($a,$Obj);

	/*
		Сохранение контрола
	*/
	public static function Save($a,$Obj);

	/*
		Показ результата контрола
	*/
	public static function Result($a,$Obj,$controls);
}

class Controls extends BaseClass
{
	public
		$throw=true,#Выкидывать исключения в случае ошибок
		$errors=array(),#В случае, если $throw==false, в этот массив будут помещаться ошибки
		$arrname=array('controls'),#Название массива, в котором будут передаваться контролы. Внимаение! Если необходимо задать многомерные массив типа control[a][b][c] - необходимо присвоить массив array('a','b','c')
		$POST,#Откуда брать значения POST запроса. Если null - из $_POST-a, если нет - то из этого массива
		$langs=array();#Массив языков

	protected static
		$controls;#Массив доступных контролов.

	public function __construct()
	{
		if(Eleanor::$vars['multilang'])
			foreach(Eleanor::$langs as $k=>&$v)
				$this->langs[]=$k;
	}

	/*
		Функция должна отобразить результаты контролов
	*/
	public function DisplayResults(array$co,array$lco=array())
	{
		$ret=$this->errors=array();
		foreach($co as $k=>$v)
		{
			if(!is_array($v))
				continue;
			$v['multilang']=isset($v['multilang']) ? $v['multilang'] and $this->langs : false;
			$a=isset($lco[$k]) ? $lco[$k] : array();
			if(!isset($v['name']))
				$v['name']=array($k);
			$v['name']=(array)$v['name'];
			if($v['multilang'])
			{
				$rc=false;
				if(isset($v['load']) and is_callable($v['load']))
					$rc=call_user_func($v['load'],$a+$v+array('value'=>array()),$this);
				elseif(!empty($v['load_eval']))
				{
					ob_start();
					$f=create_function('$co,$Obj',$co[$k]['load_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();
						$e='Error in load eval ('.$k.'): '.$e;
						if($this->throw)
							throw new EE($e,EE::DEV,array('code'=>1));
						$this->errors[$k]=$e;
					}
					$rc=$f($a+$v+array('value'=>array()),$this);
					ob_end_clean();
				}
				if(is_array($rc))
					foreach($rc as $rck=>&$rcv)
						if(is_array($rcv))#isset($a[$rck]) and
							$a[$rck]=$rcv;
				unset($v['load_eval'],$v['load'],$v['result_eval'],$v['result']);
				$skip=false;
				foreach($this->langs as &$l)
				{
					$a=isset($lco[$k]) ? $lco[$k] : array();
					foreach($a as &$lv)
						$lv=Eleanor::FilterLangValues($lv,$l,'');
					$v['name']['lang']=$l ? $l : Language::$main;
					if(isset($v['default']))
						$a['default']=Eleanor::FilterLangValues($v['default'],$l,'');
					$a+=$v;
					if(null===$tmp=$this->DisplayResult($a,$ret))
						continue;
					if(isset($a['default']) and $a['default']===$tmp)
						$skip=true;
					$ret[$k][$l]=$tmp;
				}
				if(!isset($ret[$k]) or $skip)
					continue;
				if(isset($co[$k]['result']) and is_callable($co[$k]['result']))
					$ret[$k]=call_user_func($co[$k]['result'],array('value'=>$ret[$k],'multilang'=>$v['multilang'])+$co[$k],$this,$ret);
				elseif(!empty($co[$k]['result_eval']))
				{
					ob_start();
					$f=create_function('$co,$Obj',$co[$k]['result_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();

						$e='Error in result eval ('.$k.'): '.$e;
						if($this->throw)
							throw new EE($e,EE::DEV,array('code'=>1));
						$this->errors[$k]=$e;
					}
					$ret[$k]=$f(array('value'=>$ret[$k],'multilang'=>$v['multilang'])+$co[$k],$this,$ret);
					ob_end_clean();
				}
			}
			else
			{
				$a=isset($lco[$k]) ? $lco[$k] : array();
				if(!empty($co[$k]['multilang']))
				{
					foreach($a as &$lv)
						$lv=Eleanor::FilterLangValues($lv,false,'');
					if(isset($v['default']))
						$v['default']=Eleanor::FilterLangValues($v['default']);
				}

				try
				{
					if(null===$tmp=$this->DisplayResult($a+$v,$ret))
						continue;
				}
				catch(EE$E)
				{					if($this->throw)
						throw$E;				}
				$ret[$k]=$tmp;
			}
		}
		return$ret;
	}

	public function DisplayControls(array$co,array$lco=array())
	{
		$ret=$this->errors=array();
		foreach($co as $k=>$v)
		{
			if(!is_array($v))
				continue;
			$v['multilang']=isset($v['multilang']) ? $v['multilang'] and $this->langs : false;
			$a=isset($lco[$k]) ? $lco[$k] : array();
			if(!isset($v['name']))
				$v['name']=array($k);
			$v['name']=(array)$v['name'];
			if($v['multilang'])
			{
				$rc=false;
				if(isset($v['load']) and is_callable($v['load']))
					$rc=call_user_func($v['load'],$a+$v+array('value'=>array()),$this);
				elseif(!empty($v['load_eval']))
				{
					ob_start();
					$f=create_function('$co,$Obj',$co[$k]['load_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();

						$e='Error in load eval ('.$k.'): '.$e;
						if($this->throw)
							throw new EE($e,EE::DEV,array('code'=>1));
						$this->errors[$k]=$e;
					}
					$rc=$f($a+$v+array('value'=>array()),$this);
					ob_end_clean();
				}
				if(is_array($rc))
					foreach($rc as $rck=>&$rcv)
						if(is_array($rcv))
							$a[$rck]=$rcv;
				unset($v['load_eval'],$v['load']);
				foreach($this->langs as &$l)
				{
					$al=array();
					foreach($a as $lk=>&$lv)
						$al[$lk]=Eleanor::FilterLangValues($lv,$l,'');
					if(isset($v['default']))
						$al['default']=Eleanor::FilterLangValues($v['default'],$l,'');
					$v['name']['lang']=$l ? $l : Language::$main;
					if(null===$tmp=$this->DisplayControl($al+$v,$ret,$lco))
						continue;
					$ret[$k][$l]=$tmp;
				}
			}
			else
			{
				if(!empty($co[$k]['multilang']))
				{
					foreach($a as &$lv)
						$lv=Eleanor::FilterLangValues($lv,false,'');
					if(isset($v['default']))
						$v['default']=Eleanor::FilterLangValues($v['default']);
				}
				try
				{
					if(null===$tmp=$this->DisplayControl($a+$v,$ret,$lco))
						continue;
					$ret[$k]=$tmp;
				}
				catch(EE$E)
				{					if($this->throw)
						throw$E;				}
			}
		}
		return$ret;
	}

	public function SaveControls(array$co,array$lco=array())
	{
		$ret=array();
		foreach($co as $k=>$v)
		{
			if(!is_array($v))
				continue;
			$v['multilang']=isset($v['multilang']) ? $v['multilang'] and $this->langs : false;
			if(!isset($v['name']))
				$v['name']=array($k);
			$v['name']=(array)$v['name'];
			if($v['multilang'])
			{
				unset($v['save_eval'],$v['save']);
				$skip=false;
				foreach($this->langs as &$l)
				{
					$a=isset($lco[$k]) ? $lco[$k] : array();
					foreach($a as &$lv)
						$lv=Eleanor::FilterLangValues($lv,$l,'');
					$v['name']['lang']=$l ? $l : Language::$main;
					if(isset($v['default']))
						$a['default']=Eleanor::FilterLangValues($v['default'],$l,'');
					$a+=$v;
					if(null===$tmp=$this->SaveControl($a,$ret))
						continue;
					if(isset($a['default']) and $a['default']===$tmp)
						$skip=true;
					$ret[$k][$l]=$tmp;
				}

				if(!isset($ret[$k]) or $skip)
					continue;
				if(isset($co[$k]['save']) and is_callable($co[$k]['save']))
					$ret[$k]=call_user_func($co[$k]['save'],array('value'=>$ret[$k],'multilang'=>$v['multilang'])+$co[$k],$this,$ret);
				elseif(!empty($co[$k]['save_eval']))
				{
					ob_start();
					$f=create_function('$co,$Obj,$ret',$co[$k]['save_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();

						$e='Error in save eval ('.$k.'): '.$e;
						if($this->throw)
							throw new EE($e,EE::DEV,array('code'=>1));
						$this->errors[$k]=$e;
					}
					$ret[$k]=$f(array('value'=>$ret[$k],'multilang'=>$v['multilang'])+$co[$k],$this,$ret);
					ob_end_clean();
				}
			}
			else
			{
				$a=isset($lco[$k]) ? $lco[$k] : array();
				if(!empty($co[$k]['multilang']))
				{
					foreach($a as &$lv)
						$lv=Eleanor::FilterLangValues($lv,false,'');
					if(isset($v['default']))
						$v['default']=Eleanor::FilterLangValues($v['default']);
				}
				try
				{
					if(null===$tmp=$this->SaveControl($a+$v,$ret))
						continue;
				}
				catch(EE$E)
				{					if($this->throw)
						throw$E;				}
				$ret[$k]=$tmp;
			}
		}
		return$ret;
	}

	/*
		Функция отображающая контрол
		$v - параметры контрола
			type - Тип контрола

			#Два ключа, отвечающих за обработку значения при загрузке контрола
			load
			load_eval

			Пример:
			function($co,$Obj)
			{
				return$co['value'];
			}
			Эти же переменные получает и eval.


			#Два ключа, отвечающих за обработку значения при сохранении контрола
			save
			save_eval

			Пример:
			function($co,$Obj)
			{
				return$co['value'];
			}
			Эти же переменные получает и eval.


			default - Значение по-умолчанию
			value - Значение, полученное из БД. Имеет приоритет над default
			name - нужное имя контрола на странице

			options => array(#Дополнительные настройки для контролов из файлов. Кроме этого для типов select, items, item этот массив содержит значения пунктов
				#Пример некоторых полей
				'size' => 10,#Размер полей для типов items и item
				'extra' = > '',#Дополнительные параметры для простых типов типа edit,text,select и пр.
				'htmlsafe' => '',#Признак безопасности полученных ХТМЛ тегов. Это значит, значение, полученные из контрола будут пропущены через htmlspecialchars
				'explode' => false,#Способ обработки типа items. true - будет сохранятся в серилизированном виде. false - explode(',',array())
			),
			lang - Идентификатор языка, если мы используем эту опцию в качестве одной из языковых
			bypost - загрузить из POST запроса.
			prepend - строка, определяющая содержимое ПЕРЕД контролом
			append - строка, определяющая содержимое ПОСЛЕ контрола
			$controls - результат предыдущих контролов из DisplayControls
			$massarr - массив всех контролово
	*/
	public function DisplayControl(array$co,$controls=array(),$lco=array())
	{
		#Добавить недостающие ключи
		$co+=array(
			'type'=>'edit',
			'load'=>null,
			'load_eval'=>null,
			'default'=>null,
			'value'=>null,
			'name'=>'noname',
			'options'=>array(),
			'bypost'=>false,
			'append'=>'',
			'prepend'=>'',
		);
		$co['options']=(array)$co['options'];
		$co['controlname']=$this->GenName($co['name']);
		if(!isset($co['value']))
			$co['value']=$co['default'];
		$items=false;
		if(is_callable($co['load']))
			$col=call_user_func($co['load'],$co,$this,$controls);
		elseif($co['load_eval'])
		{
			ob_start();
			$f=create_function('$co,$Obj,$controls',$co['load_eval']);
			if($f===false)
			{
				$e=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in load eval: '.$e,EE::DEV,array('code'=>1));
			}
			$col=$f($co,$this,$controls);
			ob_end_clean();
		}
		if(isset($col))
			$co=$col+$co;
		switch($co['type'])
		{
			case'user':
			case'':#Alias
				$co['options']+=array('load'=>null,'load_eval'=>null);
				if(is_callable($co['options']['load']))
					$html=call_user_func($co['options']['load'],$co,$this,$controls);
				elseif($co['options']['load_eval'])
				{
					ob_start();
					$f=create_function('$co,$Obj,$controls',$co['options']['load_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();
						throw new EE('Error in load user eval: '.$e,EE::DEV,array('code'=>1));
					}
					$html=$f($co,$this,$controls);
					ob_end_clean();
				}
				elseif(array_key_exists('content',$co['options']))
					$html=$co['options']['content'];
				else
					throw new EE('Incorrect callback',EE::DEV);
			break;
			case'editor':
				if($co['bypost'])
					$co['value']=$this->GetPostVal($co['name'],$co['value']);
				if(is_array($co['value']))
					$co['value']=join(',',$co['value']);
				$E=new Editor;
				foreach($co['options'] as $k=>&$v)
					if($k=='type' and $v==-1)
						continue;
					elseif(property_exists($E,$k))
						$E->$k=$v;
					elseif($k=='4alt' and isset($lco[$v]['value']))
					{
						if(is_string($lco[$v]['value']))
							$alt=$lco[$v]['value'];
						elseif(is_array($lco[$v]['value']) and is_array($co['name']) and isset($co['name']['lang']))
							$alt=Eleanor::FilterLangValues($lco[$v]['value'],$co['name']['lang']);
						else
							continue;
						if($alt)
							$E->imgalt=$alt;
					}
				$html=$E->Area($co['controlname'],$co['value'],array('bypost'=>$co['bypost'])+(isset($co['extra']) ? $co['extra'] : array()));
			break;
			case'edit':
			case'text':
				$co['options']+=array('extra'=>array(),'htmlsafe'=>false);
				if($co['bypost'])
					$co['value']=$this->GetPostVal($co['name'],$co['value']);
				if(is_array($co['value']))
					$co['value']=join(',',$co['value']);
				$html=Eleanor::$co['type']($co['controlname'],$co['value'],$co['options']['extra'],$co['options']['htmlsafe']);
			break;
			case'items':
				$co['options']+=array('explode'=>false,'delim'=>',');
				if($co['bypost'])
					$value=$this->GetPostVal($co['name'],array());
				else
				{
					$value=array();
					if($co['value'])
						$value=$co['options']['explode'] ? explode($co['options']['delim'],Strings::CleanForExplode($co['value'],$co['options']['delim'])) : $co['value'];
				}
			case'item':
				$co['options']+=array('size'=>10);
				$items=true;
			case'select':
				$co['options']+=array('extra'=>array(),'strict'=>false,'options'=>array(),'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/);
				if(!is_array($co['options']['extra']))
					$co['options']['extra']=array();
				if(!is_array($co['options']['options']))
					$co['options']['options']=array();
				if(!isset($value))
					$value=$co['bypost'] ? $this->GetPostVal($co['name'],$co['value']) : $co['value'];
				$value=(array)$value;
				if(is_callable($co['options']['callback']) and (!isset($co['options']['type']) or $co['options']['type']=='callback'))
					$co['options']['options']=call_user_func($co['options']['callback'],array('value'=>$value)+$co,$this);
				elseif($co['options']['eval'] and (!isset($co['options']['type']) or $co['options']['type']=='eval'))
				{
					ob_start();
					$f=create_function('$co,$Obj,$controls',$co['options']['eval']);
					if($f===false)
					{
						$err=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();
						throw new EE('Error in options eval: '.$e,EE::DEV,array('code'=>1));
					}
					$co['options']['options']=$f(array('value'=>$value)+$co,$this,$controls);
					ob_end_clean();
				}
				$html='';
				if(is_array($co['options']['options']))
					foreach($co['options']['options'] as $k=>$v)
					{
						if(is_array($v))
						{
							$n=isset($v['title']) ? $v['title'] : '';
							if(isset($v['name']))
								$k=$v['name'];
							$extra=isset($v['extra']) ? $v['extra'] : array();
							$safe=isset($v['htmlsafe']) ? $v['htmlsafe'] : 0;
						}
						else
						{
							$n=$v;
							$extra=array();
							$safe=0;
						}
						$html.=Eleanor::Option($n,$k,in_array($k,$value,$co['options']['strict']),$extra,$safe);
					}
				else
					$html=$co['options']['options'];
				if($items)
					$html=Eleanor::$co['type']($co['controlname'],$html,$co['options']['size'],$co['options']['extra']);
				else
					$html=Eleanor::Select($co['controlname'],$html,$co['options']['extra']);
				unset($value);
			break;
			case'check':
				$co['options']+=array('extra'=>array());
				$html=Eleanor::Check($co['controlname'],$co['bypost'] ? $this->GetPostVal($co['name'],false) : $co['value'],$co['options']['extra']);
			break;
			case'input':
				$co['options']+=array('type'=>'edit','extra'=>array(),'htmlsafe'=>false);
				if($co['bypost'])
					$co['value']=$this->GetPostVal($co['name'],$co['value']);
				if(is_array($co['value']))
					$co['value']=join(',',$co['value']);
				$html=Eleanor::Control($co['controlname'],$co['options']['type'],$co['value'],$co['options']['extra'],$co['options']['htmlsafe']);
			break;
			case'date':
				$co['options']+=array('time'=>false,'extra'=>array());
				$html=Dates::Calendar($co['controlname'],$co['bypost'] ? $this->GetPostVal($co['name'],false) : $co['value'],$co['options']['time'],$co['options']['extra']);
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();
				if(!class_exists('Control'.$co['type'],false) and (!in_array($co['type'],self::$controls) or !include(Eleanor::$root.'core/controls/'.$co['type'].'.php')))
					throw new EE('Unknown control '.$co['type'],EE::DEV);
				$cl='Control'.$co['type'];
				$html=$cl::Control($co,$this,$controls);
		}
		return is_string($html) ? $co['prepend'].$html.$co['append'] : $html;
	}

	/*
		Функция сохранения контрола
		$co - параметры контрола
		$controls - результат предыдущих контролов из SaveControls
	*/
	public function SaveControl(array$co,array$controls=array())
	{
		#Добавить недостающие ключи
		$co+=array(
			'save'=>null,
			'save_eval'=>null,
			'multilang'=>false,#Необходимо для save_eval и save
			'options'=>array(),
			'type'=>'edit',
			'name'=>'noname',
			'default'=>null,
		);

		switch($co['type'])
		{
			case'user':
			case'':#Alias
				$co['options']+=array('save'=>null,'save_eval'=>null);
				if(is_callable($co['options']['save']))
					$res=call_user_func($co['options']['save'],$co,$this,$controls);
				elseif($co['options']['save_eval'])
				{
					ob_start();
					$f=create_function('$co,$Obj,$controls',$co['options']['save_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();
						throw new EE('Error in save user eval: '.$e,EE::DEV,array('code'=>1));
					}
					$res=$f($co,$this,$controls);
					ob_end_clean();
				}
				else
					$res=null;
			break;
			case'editor':
				$E=new Editor_Result;
				foreach($co['options'] as $k=>&$v)
					if($k=='type' and $v==-1)
						continue;
					elseif(property_exists($E,$k))
						$E->$k=$v;
					elseif($k=='4alt' and isset($controls[$v]))
					{
						if(is_string($controls[$v]))
							$alt=$controls[$v];
						elseif(is_array($controls[$v]) and is_array($co['name']) and isset($co['name']['lang']))
							$alt=Eleanor::FilterLangValues($controls[$v],$co['name']['lang']);
						else
							continue;
						if($alt)
							$E->imgalt=$alt;
					}
				$res=$E->GetHTML($this->GetPostVal($co['name'],$co['default']),true);
			break;
			case'check':
				$co+=array('default'=>false);
				$res=(bool)$this->GetPostVal($co['name']);
			break;
			case'edit':
			case'text':
			case'item':
			case'select':
			case'input':
			case'date':
				$co['options']+=array('htmlsafe'=>false);
				$res=$this->GetPostVal($co['name'],$co['default']);
				if($co['options']['htmlsafe'])
					$res=FilterArrays::Filter($res);
			break;
			case'items':
				$co+=array('default'=>array());
				$res=$this->GetPostVal($co['name'],array());
				if(!is_array($res))
					$res=array();
				$co['options']+=array('explode'=>false,'delim'=>',');
				if($co['options']['explode'])
					$res=$res ? $co['options']['delim'].join($co['options']['delim'],$res).$co['options']['delim'] : '';
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();
				if(!class_exists('Control'.$co['type'],false) and (!in_array($co['type'],self::$controls) or !include(Eleanor::$root.'core/controls/'.$co['type'].'.php')))
					throw new EE('Unknown control 1'.$co['type'],EE::DEV);
				$cl='Control'.$co['type'];
				$res=$cl::Save($co,$this,$controls);
		}
		if($res===$co['default'])
			return$res;
		if(is_callable($co['save']))
		{
			$co['value']=$res;
			$res=call_user_func($co['save'],$co,$this,$controls);
		}
		elseif($co['save_eval'])
		{
			$co['value']=$res;
			ob_start();
			$f=create_function('$co,$Obj,$controls',$co['save_eval']);
			if($f===false)
			{
				$e=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in save eval:'.$e,EE::DEV,array('code'=>1));
			}
			$res=$f($co,$this,$controls);
			ob_end_clean();
		}
		return$res;
	}

	/*
		Функция отображения результата контрола
		$co - параметры контрола
		$controls - результат предыдущих контролов из DisplayResults
	*/
	public function DisplayResult(array$co,array$controls=array())
	{
		#Добавить недостающие ключи
		$co+=array(
			'result'=>null,
			'result_eval'=>null,
			'load'=>null,
			'load_eval'=>null,
			'multilang'=>false,#Необходимо для save_eval и save
			'options'=>array(),
			'type'=>'edit',
			'default'=>null,
		);
		$co['options']=(array)$co['options'];
		if(!isset($co['value']))
			$co['value']=$co['default'];
		if(is_callable($co['load']))
			$col=call_user_func($co['load'],$co,$this,$controls);
		elseif($co['load_eval'])
		{
			ob_start();
			$f=create_function('$co,$Obj,$controls',$co['load_eval']);
			if($f===false)
			{
				$e=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in load eval: '.$e,EE::DEV,array('code'=>1));
			}
			$col=$f($co,$this,$controls);
			ob_end_clean();
		}
		if(isset($col))
			$co=$col+$co;
		switch($co['type'])
		{
			case'user':
				$co['options']+=array('result'=>null,'result_eval'=>null);
				if(is_callable($co['options']['result']))
					$res=call_user_func($co['options']['save'],$co,$this,$controls);
				elseif($co['options']['result_eval'])
				{
					ob_start();
					$f=create_function('$co,$Obj,$controls',$co['options']['result_eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();
						throw new EE('Error in save user eval: '.$e,EE::DEV,array('code'=>1));
					}
					$res=$f($co,$this,$controls);
					ob_end_clean();
				}
				else
					$res=$co['value'];
			break;
			case'editor':
				$co['options']+=array('ownbb'=>true);
				$res=$co['value'];
				if($co['options']['ownbb'])
					$res=OwnBB::Parse($res);
			break;
			case'check':
				$res=(bool)$co['value'];
			break;
			case'edit':
			case'text':
			case'input':
			case'date':
				$res=$co['value'];
			break;
			case'item':
			case'select':
				$single=true;
			case'items':
				$co['options']+=array('options'=>false,'explode'=>false,'delim'=>',','retvalue'=>false,'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/);
				if(!is_array($co['value']))
					$co['value']=$co['options']['explode'] ? explode($co['options']['delim'],$co['value']) : array($co['value']);
				if($co['options']['retvalue'])
				{
					$res=$co['options']['explode'] ? join($co['options']['delim'],$co['value']) : (isset($single) ? reset($co['value']) : $co['value']);
					break;
				}
				if(is_callable($co['options']['callback']) and (!isset($co['options']['type']) or $co['options']['type']=='callback'))
					$co['options']['options']=call_user_func($co['options']['callback'],array('value'=>$co['value'])+$co,$this);
				elseif($co['options']['eval'] and (!isset($co['options']['type']) or $co['options']['type']=='eval'))
				{
					ob_start();
					$f=create_function('$co,$Obj,$controls',$co['options']['eval']);
					if($f===false)
					{
						$e=ob_get_contents();
						ob_end_clean();
						Eleanor::getInstance()->e_g_l=error_get_last();
						throw new EE('Error in options eval: '.$e,EE::DEV,array('code'=>1));
					}
					$co['options']['options']=$f(array('value'=>$co['value'])+$co,$this,$controls);
					ob_end_clean();
				}
				if(!is_array($co['options']['options']))
				{
					$res=$co['options']['explode'] ? join($co['options']['delim'],$co['value']) : (isset($single) ? reset($co['value']) : $co['value']);
					break;
				}
				$r=array();
				foreach($co['value'] as &$v)
					if(isset($co['options']['options'][$v]))
						$r[]=$co['options']['options'][$v];
				$res=$co['options']['explode'] ? join($co['options']['delim'],$r) : (isset($single) ? reset($r) : $r);
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();
				if(!class_exists('Control'.$co['type'],false) and (!in_array($co['type'],self::$controls) or !include(Eleanor::$root.'core/controls/'.$co['type'].'.php')))
					throw new EE('Unknown control 1'.$co['type'],EE::DEV);
				$cl='Control'.$co['type'];
				$res=$cl::Result($co,$this,$controls);
		}
		if($res===$co['default'])
			return$res;
		if(is_callable($co['result']))
		{
			$co['value']=$res;
			$res=call_user_func($co['result'],$co,$this,$controls);
		}
		elseif($co['result_eval'])
		{
			$co['value']=$res;
			ob_start();
			$f=create_function('$co,$Obj,$controls',$co['save_eval']);
			if($f===false)
			{
				$e=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in save eval: '.$e,EE::DEV,array('code'=>1));
			}
			$res=$f($co,$this,$controls);
			ob_end_clean();
		}
		return$res;
	}

	public function GenName($n)
	{
		$name='';
		if($this->arrname)
		{
			$name=reset($this->arrname);
			$a=array_slice($this->arrname,1);
			foreach($a as &$v)
				if($v!==false)
					$name.='['.$v.']';
		}
		if(is_array($n))
		{
			if(!$this->arrname)
			{
				$name=reset($n);
				unset($n[key($n)]);
			}
			foreach($n as &$v)
				if($v)
					$name.='['.$v.']';
		}
		elseif($n!==false)
			$name=$name ? $name.'['.$n.']' : $n;
		return$name;
	}

	public function GetPostVal($n,$def=null)
	{
		$workarr=array_merge($this->arrname,(array)$n);
		$name=reset($workarr);
		if($pv=isset($this->POST) and !isset($this->POST[$name]) or !$pv and !isset($_POST[$name]))
			return$def;
		$p=isset($this->POST) ? $this->POST[$name] : $_POST[$name];
		$a=array_slice($workarr,1);
		foreach($a as &$v)
		{
			if($v===false)
				continue;
			if(!isset($p[$v]))
				return$def;
			$p=$p[$v];
		}
		return$p;
	}

	protected static function ScanControls()
	{
		self::$controls=array();
		$co=glob(Eleanor::$root.'core/controls/*.php');
		foreach($co as &$v)
			self::$controls[]=substr(basename($v),0,-4);
	}
}