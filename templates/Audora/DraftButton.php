<?php
/*
	Ёлемент шаблона.  нопка "—охранить черновик".

	@var URL, куда отправл€ть сохран€емые данные
*/
$url=isset($v_0) ? $v_0 : array();
if(is_array($url))
	$url=$GLOBALS['Eleanor']->Url->Construct($url);

$GLOBALS['head']['draft']='<script type="text/javascript">//<![CDATA[
CORE.drafts=[];
$(function(){	var first=true,
		lnk="",
		cnt,
		After=function(){			if(--cnt==0)
				window.location.href=lnk;
		};

	$("div.hlang a").click(function(){// нопки переключени€ €зыков		if(first)
		{			$.each(CORE.drafts,function(i,v){				v.OnSave.add(After);			});			first=false;		}
		cnt=CORE.drafts.length;
		lnk=$(this).prop("href");
		$.each(CORE.drafts,function(i,v){			if(v.changed)
				v.Save();
			else
				cnt--;
		});
		return cnt<=0;	});})//]]></script>';

if(!isset(Eleanor::$vars['drafts_autosave']))
	Eleanor::LoadOptions('drafts');
array_push($GLOBALS['jscripts'],'js/eleanor_drafts.js','js/eleanor_drafts-'.Language::$main.'.js');
$u=uniqid();

echo Eleanor::Button(' ','button',array('id'=>$u,'style'=>'color:lightgray;display:none')).'<script type="text/javascript">//<![CDATA[
$(function(){
	var D'.$u.'=new CORE.DRAFT({		form:$("#'.$u.'").closest("form"),
		url:"'.$url.'",
		interval:'.Eleanor::$vars['drafts_autosave'].',
		OnSave:function(){			$("#'.$u.'").val(CORE.Lang("draftsaved")).css("color","lightgray");		},
		OnChange:function(){
			$("#'.$u.'").val(CORE.Lang("savedraft")).css("color","");
		}
	});
	CORE.drafts.push(D'.$u.');
	$("#'.$u.'").click(function(){		D'.$u.'.Save();	}).val(CORE.Lang("draftsaved")).show();
});//]]></script>';