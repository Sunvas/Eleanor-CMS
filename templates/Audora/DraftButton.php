<?php
/*
	Элемент шаблона. Кнопка "Сохранить черновик".

	@var URL, куда отправлять сохраняемые данные
*/
$url=isset($v_0) ? $v_0 : array();
$GLOBALS['head']['draft']='<script type="text/javascript">//<![CDATA[
CORE.drafts=[];
$(function(){
	var first=true,
		lnk="",
		cnt,
		After=function(){
			if(--cnt==0)
				window.location.href=lnk;
		};

	$("div.hlang a").click(function(){//Кнопки переключения языков
		if(first)
		{
			$.each(CORE.drafts,function(i,v){
				v.OnSave.add(After);
			});
			first=false;
		}
		cnt=CORE.drafts.length;
		lnk=$(this).prop("href");
		$.each(CORE.drafts,function(i,v){
			if(v.changed)
				v.Save();
			else
				cnt--;
		});
		return cnt<=0;
	});
})//]]></script>';

if(!isset(Eleanor::$vars['drafts_autosave']))
	Eleanor::LoadOptions('drafts');
array_push($GLOBALS['jscripts'],'js/eleanor_drafts.js','js/eleanor_drafts-'.Language::$main.'.js');
$u=uniqid();

echo Eleanor::Button(' ','button',array('id'=>$u,'style'=>'color:lightgray;display:none')),'<script type="text/javascript">//<![CDATA[
$(function(){
	var D',$u,'=new CORE.DRAFT({
		form:$("#',$u,'").closest("form"),
		url:"'.$url.'",
		enabled:false,
		interval:'.Eleanor::$vars['drafts_autosave'].',
		OnSave:function(){
			$("#',$u,'").val(CORE.Lang("draftsaved")).css("color","lightgray");
		},
		OnChange:function(){
			$("#',$u,'").val(CORE.Lang("savedraft")).css("color","");
		}
	});
	CORE.drafts.push(D',$u,');
	$("#',$u,'").click(function(){
		D',$u,'.Save();
	}).val(CORE.Lang("draftsaved")).show();
	//После того, как пройдут все события формы
	setTimeout(function(){
		D',$u,'.enabled=true;
	},2500);
});//]]></script>';