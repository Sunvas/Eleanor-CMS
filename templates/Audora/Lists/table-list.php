<?php
$size=isset($v_0) ? (int)$v_0 : 2;
return array(
	'form'=>function($a=array())
	{
		if(!is_array($a))
			$a=array('action'=>$a);
		$a+=array('method'=>'post');
		return'<form'.Eleanor::TagParams($a).'>';
	},
	'endform'=>'</form>',
	'begin'=>function()
	{
		$a=func_get_args();
		if(isset($a[0]) and is_array($a[0]))
		{
			if(!isset($a[0]['tableextra']) or !is_array($a[0]['tableextra']))
				$a[0]['tableextra']=array();
			if(!isset($a[0]['trextra']) or !is_array($a[0]['trextra']))
				$a[0]['trextra']=array();
			$a[0]['tableextra']+=array('class'=>'tabstyle');
			$a[0]['trextra']+=array('class'=>'first tablethhead');
			$ret='<table'.Eleanor::TagParams($a[0]['tableextra']).'><tr'.Eleanor::TagParams($a[0]['trextra']).'>';
		}
		else
			$ret='<table class="tabstyle"><tr class="first tablethhead">';
		foreach($a as &$v)
		{
			if($v===false)
				continue;
			$add=array('style'=>false);
			$val='';
			if(is_array($v))
			{
				foreach($v as $name=>&$param)
					if($param!==false)
						switch((string)$name)
						{
							case'0':#Числовые значения не ставить! Иначе не обработается default, при $v=array('title','colspan'=>'2'),
								$val=$param;
							break;
							case'1':
								$add['style'].='width:'.(substr($param,-1)=='%' ? $param.'%' : (int)$param.'px;');
							break;
							case'href':
								if(!isset($v['hrefextra']) or !is_array($v['hrefextra']))
									$v['hrefextra']=array();
								$v['hrefextra']+=array('href'=>$param);
								$val='<a'.Eleanor::TagParams($v['hrefextra']).'>'.$val.'</a>';
							break;
							case'sort':
								$add['class']=$param;
							case'hrefextra':
							case'trextra':
							case'tableextra':
							break;
							default:
								$add[$name]=$param;
						}
			}
			else
				$val=$v;
			$ret.='<th'.Eleanor::TagParams($add).'>'.$val.'</th>';
		}
		return$ret.'</tr>';
	},
	'empty'=>'<tr class="empty"><td colspan="'.$size.'" style="font-weight:bold;text-align:center">{0}</td></tr>',
	'item'=>function()
	{static$n=0;
		$a=func_get_args();
		if(isset($a[0]) and is_array($a[0]))
		{
			if(!isset($a[0]['trextra']) or !is_array($a[0]['trextra']))
				$a[0]['trextra']=array();
			$a[0]['trextra']+=array('class'=>$n++ % 2 ? 'tabletrline1' : 'tabletrline2');
			$ret='<tr'.Eleanor::TagParams($a[0]['trextra']).'>';
		}
		else
			$ret='<tr class="'.($n++ % 2 ? 'tabletrline1' : 'tabletrline2').'">';
		foreach($a as &$v)
		{
			if($v===false)
				continue;
			$add=array('style'=>false);
			$val='';
			if(is_array($v))
			{
				foreach($v as $name=>&$param)
					if($param!==false)
						switch((string)$name)
						{
							case'0':#Числовые значения не ставить!
								$val=$param;
							break;
							case'1':
								$add['style'].='text-align:'.$param.';';
							break;
							case'href':
								if(!isset($v['hrefextra']) or !is_array($v['hrefextra']))
									$v['hrefextra']=array();
								$v['hrefextra']+=array('href'=>$param);
								$val='<a'.Eleanor::TagParams($v['hrefextra']).'>'.$val.'</a>';
							break;
							case'hrefextra':
							case'trextra':
							break;
							default:
								$add[$name]=$param;
						}
			}
			else
				$val=$v;
			$ret.='<td'.Eleanor::TagParams($add).'>'.$val.'</td>';
		}
		return$ret.'</tr>';
	},
	'func'=>function()
	{
		$a=func_get_args();
		$ret='';
		foreach($a as &$v)
			if(is_array($v))
			{
				if(!isset($v['extra']) or !is_array($v['extra']))
					$v['extra']=array();
				$v['extra']+=array('href'=>$v[0],'title'=>$v[1]);
				$ret.='<a'.Eleanor::TagParams($v['extra']).'><img src="'.$v[2].'" alt="" /></a>';
			}
			else
				$ret.=$v;
		return array($ret,'class'=>'function');
	},
	'end'=>'</table>',
	'bottom'=>function($b,$left=false)
	{
		return'<div class="submitline" style="text-align:right">'.$b.($left ? '<div style="float:left">'.$left.'</div>' : '').'</div>';
	},
	'perpage'=>function($pp,$Furl,$p=array(30,50,100,500))
	{
		$pps='';
		foreach($p as &$v)
			$pps.=$v==$pp ? ' '.$v.' |' : ' <a href="'.$Furl($v).'">'.$v.'</a> |';
		return rtrim($pps,'|');
	},
);