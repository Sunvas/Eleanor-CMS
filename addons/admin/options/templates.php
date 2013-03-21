<?php
return array(
	'callback'=>function()
	{
		$themes=array();
		$fs=glob(Eleanor::$root.'templates/*',GLOB_ONLYDIR);
		if($fs)
			foreach($fs as &$v)
			{
				$temp=array();
				if(is_file($v.'.settings.php'))
				{
					$temp=(array)include$v.'.settings.php';
					if(!isset($temp['service']) or !in_array('user',$temp['service'],true))
						continue;
				}
				$tpl=basename($v);
				$themes[$tpl]=isset($temp['name']) ? (is_array($temp['name']) ? Eleanor::FilterLangValues($temp['name']) : $temp['name']) : $tpl;
			}
		return$themes;
	}
);