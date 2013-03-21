<?php
return array(
	'load'=>function($co)
	{
		if(!is_array($co['value']))
			$co['value']=array();
		$r='';
		$f=glob(Eleanor::$root.'core/login/*.php');
		if($f)
			foreach($f as &$v)
			{
				$v=str_replace('.php','',basename($v));
				$uses='';
				foreach(Eleanor::$services as $kk=>&$vv)
					if($vv['login']==$v)
						$uses.=$kk.', ';
				if($uses)
				{
					$uses=rtrim($uses,', ');
					$cl='Login'.ucfirst($v);
					$r.='<li style="margin-top:5px"><b>'.$uses.'</b>'.($uses==$v ? '' : ' ('.$v.')').':<br />'
						.Eleanor::Input($co['controlname'].'['.$cl.']',isset($co['value'][$cl]) ? $co['value'][$cl] : 900).'</li>';
				}
			}
		return$r ? '<ul>'.$r.'</ul>' : '';
	},
	'save'=>function($co,$Obj)
	{
		$r=array();
		$data=$Obj->GetPostVal($co['name'],array());

		$f=glob(Eleanor::$root.'core/login/*.php');
		if($f)
			foreach($f as &$v)
			{
				$v=str_replace('.php','',basename($v));
				$v='Login'.ucfirst($v);
				$r[$v]=isset($data[$v]) ? (int)$data[$v] : 900;
			}
		return$r;
	}
);