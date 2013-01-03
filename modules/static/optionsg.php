<?php
return array(
	'load'=>function($co){		$config=include(Eleanor::$root.'modules/static/config.php');
		if(!class_exists($config['api'],false))
			include Eleanor::$root.'modules/static/api.php';
		$Plug=new $config['api']($config);
		$tmp=$Plug->GetOrderedList();
		$now=$items='';
		foreach($tmp as $k=>&$v)
			$items.=Eleanor::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')+1).'›&nbsp;' : '').$v['title'],$k,false,array(),2);

		$value=$co['value'] ? explode(',',trim($co['value'],',')) : array();
		foreach($value as &$v)
			if(isset($tmp[$v]))
				$now.=Eleanor::Option($tmp[$v]['title'],$v,false,array(),2);

		$u=uniqid();
		return'<div>'.Eleanor::Select('',$items,array('id'=>'sel-'.$u,'style'=>'float:left'))
	.'<a href="#" id="add-'.$u.'" style="float:left;margin:0px 5px"><img src="'.Eleanor::$Template->default['theme'].'images/add.png" alt="" /></a></div>'
	.Eleanor::Input($co['controlname'],$co['value'],array('id'=>'input-'.$u,'type'=>'hidden'))
	.Eleanor::Select('',$now,array('id'=>'res-'.$u,'style'=>'float:left','size'=>14))
	.'<div style="float:left;padding:0px 5px;width:16px"><a href="#" id="up-'.$u.'"><img src="'.Eleanor::$Template->default['theme'].'images/up.png" alt="" /></a><a href="#" id="down-'.$u.'"><img src="'.Eleanor::$Template->default['theme'].'images/down.png" alt="" /></a><a href="#" id="del-'.$u.'"><img src="'.Eleanor::$Template->default['theme'].'images/delete.png" alt="" /></a></div>
<script type="text/javascript">//<![CDATA[
$(function(){
	var sel=$("#sel-'.$u.'"),
		add=$("#add-'.$u.'"),
		res=$("#res-'.$u.'"),
		input=$("#input-'.$u.'"),
		UpdateInput=function(){
			var arr=[];
			res.find("option").each(function(){
				arr.push($(this).val());
			});
			input.val(arr.join(","));
		},
		butt=$("#up-'.$u.',#down-'.$u.',#del-'.$u.'");

	sel.change(function(){
		if( res.find("[value="+$(this).val()+"]").prop("selected",true).size()>0 )
			add.hide();
		else
			add.show();
	}).change();

	res.change(function(){
		butt.hide();
		if($(this).val())
		{			if($("option",this).size()==1)
				$("#del-'.$u.'").show();
			else if($("option:last",this).prop("selected"))
				$("#up-'.$u.',#del-'.$u.'").show();
			else if($("option:last",this).prop("selected"))
				$("#up-'.$u.',#del-'.$u.'").show();
			else if($("option:first",this).prop("selected"))
				$("#down-'.$u.',#del-'.$u.'").show();
			else
				butt.show();
		}
	}).change();

	add.click(function(){		if(res.find("[value="+sel.val()+"]").size()==0)
			sel.find("option:selected:first").clone().each(function(){
				$(this).html($(this).html().replace(/^(&nbsp;|›)+/g,""));
			}).prop("selected",false).appendTo(res);
		res.change();
		add.hide();
		UpdateInput();
		return false;
	});

	$("#del-'.$u.'").click(function(){
		res.find("option:selected:first").remove();
		add.show();
		res.change();
		UpdateInput();
		return false;
	});

	$("#up-'.$u.'").click(function(){
		res.find("option:selected").each(function(){
			var th=$(this);
			if(th.prev().size()==0)
				return false;
			th.insertBefore(th.prev());
			UpdateInput();
		}).end().change();
		return false;
	});

	$("#down-'.$u.'").click(function(){
		res.find("option:selected").each(function(){
			var th=$(this);
			if(th.next().size()==0)
				return false;
			th.insertAfter(th.next());
			UpdateInput();
		}).end().change();
		return false;
	});

	res.filter("[disabled]").prop("disabled",false).find("option").remove();
	UpdateInput();
});//]]></script>';
	},
	'save'=>function($co,$Obj)
	{		return$Obj->GetPostVal($co['name'],$co['default']);
	},
);