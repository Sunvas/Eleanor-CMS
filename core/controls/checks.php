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

class ControlChecks extends BaseClass implements ControlsBase
{
	/**
	 * Получение настроек контрола
	 *
	 * @param ControlsManager $Obj
	 */
	public static function GetSettings($Obj)
	{
		return$Obj->GetSettings('items');
	}

	/**
	 * Получение контрола
	 *
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Control($a,$Obj)
	{
		$a['options']+=array('extra'=>array(),'options'=>array(),'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/);
		$value=$a['bypost'] ? (array)$Obj->GetPostVal($a['name'],$a['value']) : (array)$a['value'];
		if(!is_array($a['options']['extra']))
			$a['options']['extra']=array();
		if(!is_array($a['options']['options']))
			$a['options']['options']=array();
		if(is_callable($a['options']['callback']) and (!isset($a['options']['type']) or $a['options']['type']=='callback'))
			$a['options']['options']=call_user_func($a['options']['callback'],array('value'=>$value)+$a,$Obj);
		elseif($a['options']['eval'] and (!isset($a['options']['type']) or $a['options']['type']=='eval'))
		{
			ob_start();
			$f=create_function('$a,$Obj',$a['options']['eval']);
			if($f===false)
			{
				$e=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				if($Obj->throw)
					throw new EE('Error in options eval: <br />'.$e,EE::DEV);
				$Obj->errors[__class__]='Error in options eval: <br />'.$e;
				return;
			}
			$a['options']['options']=$f(array('value'=>$value)+$a,$Obj);
			ob_end_clean();
		}
		if(!is_array($a['options']['options']))
		{
			if($Obj->throw)
				throw new EE('Incorrect options',EE::DEV);
			$Obj->errors[__class__]='Incorrect options';
			return;
		}
		$html=array();
		foreach($a['options']['options'] as $k=>&$v)
			$html[$k]=array(Eleanor::Check($a['controlname'].'[]',in_array($k,$value),array('value'=>$k)+$a['options']['extra']),$v);
		return Eleanor::$Template->ControlChecks($html,null);
	}

	/**
	 * Сохранение контрола
	 *
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Save($a,$Obj)
	{
		$a+=array('default'=>array());
		$res=$Obj->GetPostVal($a['name'],$a['default']);
		if(!is_array($res))
			$res=array();
		return$res;
	}

	/**
	 * Получение результата контрола
	 *
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Result($a,$Obj,$controls)
	{
		$a['options']+=array('retvalue'=>false,'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/);

		if($a['options']['retvalue'])
			return$a['value'];

		if(is_callable($a['options']['callback']) and (!isset($a['options']['type']) or $a['options']['type']=='callback'))
			$a['options']['options']=call_user_func($a['options']['callback'],array('value'=>$a['value'])+$a,$Obj);
		elseif($a['options']['eval'] and (!isset($a['options']['type']) or $a['options']['type']=='eval'))
		{
			ob_start();
			$f=create_function('$a,$Obj,$controls',$a['options']['eval']);
			if($f===false)
			{
				$err=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				if($Obj->throw)
					throw new EE('Error in options eval: <br />'.$err,EE::DEV);
				$Obj->errors[__class__]='Error in options eval: <br />'.$err;
				return;
			}
			$a['options']['options']=$f(array('value'=>$a['value'])+$a,$Obj,$controls);
			ob_end_clean();
		}
		if(!is_array($a['options']['options']))
			return$a['value'];

		$r=array();
		foreach($a['value'] as &$v)
			if(isset($a['options']['options'][$v]))
				$r[]=$a['options']['options'][$v];
		return$r;
	}
}