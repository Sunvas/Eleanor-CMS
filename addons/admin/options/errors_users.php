<?php
return array(
	'load'=>function($co,$Obj)
	{
		$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';

		if($co['bypost'])
			$value=$Obj->GetPostVal($co['name'],'');
		else
		{
			$value='';
			if($co['value'])
			{
				$co['value']=explode(',,',trim($co['value'],','));
				$R=Eleanor::$UsersDb->Query('SELECT `name` FROM `'.USERS_TABLE.'` WHERE `id`'.Eleanor::$UsersDb->In($co['value']));
				while($a=$R->fetch_assoc())
					$value.=$a['name'].', ';
				$value=rtrim($value,', ');
			}
		}

		$u=uniqid();
		return Eleanor::Input($co['controlname'],$value,array('id'=>$u)).'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#'.$u.'").autocomplete({
		serviceUrl:CORE.ajax_file,
		minChars:2,
		delimiter:/,\s*/,
		params:{
			direct:"'.Eleanor::$service.'",
			file:"autocomplete",
			goal:"users"
		}
	});
})//]]></script>';
	},
	'save'=>function($co,$Obj)
	{
		$value=$Obj->GetPostVal($co['name'],'');
		if($value=='')
			return'';

		$value=explode(',',$value);
		foreach($value as &$v)
			$v=trim($v);

		$R=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`'.Eleanor::$UsersDb->In($value));
		$value='';
		while($a=$R->fetch_assoc())
			$value.=','.$a['id'].',';
		return$value;
	}
);