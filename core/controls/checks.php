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
	private
		$Obj;

	public function __construct($Obj)
	{
		$this->Obj=$Obj;
	}

	public function GetSettings()
	{
		return $this->Obj->GetSettings('items');
	}

	public function Control($a)
	{
		$a['options']+=array('explode'=>false,'delim'=>',','break'=>'<br />','addon'=>array(),'options'=>array(),'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/);
		if($a['bypost'])
			$value=(array)$this->Obj->GetPostVal($a['name'],$a['value']);
		else
		{
			$value=array();
			if($a['value'])
				$value=$a['options']['explode'] ? explode($a['options']['delim'],Strings::CleanForExplode($a['value'],$a['options']['explode'])) : (array)$a['value'];
		}
		if(!is_array($a['options']['addon']))
			$a['options']['addon']=array();
		if(!is_array($a['options']['options']))
			$a['options']['options']=array();
		if(is_callable($a['options']['callback']) and (!isset($a['options']['type']) or $a['options']['type']=='callback'))
			$a['options']['options']=call_user_func($a['options']['callback'],array('value'=>$value)+$a,$this);
		elseif($a['options']['eval'] and (!isset($a['options']['type']) or $a['options']['type']=='eval'))
		{
			ob_start();
			$f=create_function('$a,$Obj',$a['options']['eval']);
			if($f===false)
			{
				$err=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in options eval <br />'.$err,EE::DEV,array('code'=>1));
			}
			$a['options']['options']=$f(array('value'=>$value)+$a,$this);
			ob_end_clean();
		}
		$html=array();
		if(!is_array($a['options']['options']))
			throw new EE('Incorrect options!',EE::DEV,array('code'=>1));
		foreach($a['options']['options'] as $k=>&$v)
			$html[]='<label>'.Eleanor::Check($a['controlname'].'[]',in_array($k,$value),array('value'=>$k)+$a['options']['addon']).' '.$v.'</label>';
		return join($a['options']['break'],$html);
	}

	public function Save($a)
	{
		$a+=array('default'=>array());
		$res=$this->Obj->GetPostVal($a['name'],$a['default']);
		if(!is_array($res))
			$res=array();
		$a['options']+=array('explode'=>false,'delim'=>',');
		if($a['options']['explode'])
			$res=$a['options']['delim'].join($a['options']['delim'],$res).$a['options']['delim'];
		return$res;
	}

	public function Result($a,$controls)
	{
		$a['options']+=array('explode'=>false,'delim'=>',','retvalue'=>false,'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/);
		if(!is_array($a['value']))
			$a['value']=$a['options']['explode'] ? explode($a['options']['delim'],$a['value']) : array($a['value']);
		if($a['options']['retvalue'])
			return $a['options']['explode'] ? join($a['options']['delim'],$a['value']) : $a['value'];
		if(is_callable($a['options']['callback']) and (!isset($a['options']['type']) or $a['options']['type']=='callback'))
			$a['options']['options']=call_user_func($a['options']['callback'],array('value'=>$a['value'])+$a,$this);
		elseif($a['options']['eval'] and (!isset($a['options']['type']) or $a['options']['type']=='eval'))
		{
			ob_start();
			$f=create_function('$a,$Obj,$controls',$a['options']['eval']);
			if($f===false)
			{
				$err=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in options eval <br />'.$err,EE::DEV,array('code'=>1));
			}
			$a['options']['options']=$f(array('value'=>$a['value'])+$a,$this,$controls);
			ob_end_clean();
		}
		if(!is_array($a['options']['options']))
			return $a['options']['explode'] ? join($a['options']['delim'],$a['value']) : $a['value'];
		$r=array();
		foreach($a['value'] as &$v)
			if(isset($a['options']['options'][$v]))
				$r[]=$a['options']['options'][$v];
		return$a['options']['explode'] ? join($a['options']['delim'],$r) : $r;
	}
}