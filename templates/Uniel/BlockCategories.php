<?php
/*
	Шаблон блока категорий

	@var строка категорий, без начального <ul>, представляет собой последовательность <li><a...>...</a><ul><li>...</li></ul></li></ul>
*/
if(!defined('CMS'))die;
$u=uniqid();
echo'<ul class="blockcategories" id="',$u,'">',$v_0.'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#'.$u.' li:has(ul)").addClass("subcat").each(function(i){
		var img=$("<img>").css({cursor:"pointer","margin-right":"3px"}).prop({src:"'.$theme.'images/minus.gif",title:"+"}).prependTo(this).click(function(){
			if(localStorage.getItem("bc"+i))
			{
				$(this).prop({src:"'.$theme.'images/plus.gif",title:"+"}).next().next().fadeOut("fast");
				localStorage.removeItem("bc"+i);
			}
			else
			{
				$(this).prop({src:"'.$theme.'images/minus.gif",title:"&minus;"}).next().next().fadeIn("fast");
				try
				{
					localStorage.setItem("bc"+i,"1");
				}catch(e){}
			}
		});
		if(!localStorage.getItem("bc"+i))
			img.prop({src:"'.$theme.'images/plus.gif",title:"+"}).next().next().hide();
	}).find("ul").css("margin-left","4px");
});//]]></script>';