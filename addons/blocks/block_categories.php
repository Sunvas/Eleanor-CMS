<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
if(isset($GLOBALS['Eleanor']->Categories))
{	$u=uniqid();
	$Fbc=function($a,$c='<ul>') use ($Fbc)
	{
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$n=-1;
		$nonp=true;
		foreach($a as &$v)
		{
			++$n;
			$nl=strlen($v['parents']);
			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif($nonp)
				{
					$c.=$Fbc(array_slice($a,$n));
					$nonp=false;
				}
				continue;
			}
			if($n>0)
				$c.='</li>';
			$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($v['id']),true,'/').'">'.$v['title'].'</a>';
			$nonp=true;
		}
		return$c.'</li></ul>';
	};
	$mn=isset($GLOBALS['Eleanor']->module['name']) ? $GLOBALS['Eleanor']->module['name'] : '';

	return$Fbc($GLOBALS['Eleanor']->Categories->dump,'<ul class="blockcategories" id="q'.$u.'">').'<script type="text/javascript">//<![CDATA[
$(function(){	$("#q'.$u.' li:has(ul)").addClass("subcat").each(function(i){		var img=$("<img>").css({cursor:"pointer","margin-right":"3px"}).prop({src:"'.Eleanor::$Template->default['theme'].'images/minus.gif",title:"+"}).prependTo(this).click(function(){			if(localStorage.getItem("bc-'.$mn.'"+i))
			{				$(this).prop({src:"'.Eleanor::$Template->default['theme'].'images/plus.gif",title:"+"}).next().next().hide();
				localStorage.removeItem("bc-'.$mn.'"+i);			}			else
			{
				$(this).prop({src:"'.Eleanor::$Template->default['theme'].'images/minus.gif",title:"-"}).next().next().show();
				try
				{
					localStorage.setItem("bc-'.$mn.'"+i,"1");
				}catch(e){}
			}
		});
		if(!localStorage.getItem("bc-'.$mn.'"+i))
			img.prop({src:"'.Eleanor::$Template->default['theme'].'images/plus.gif",title:"+"}).next().next().hide();
	}).find("ul").css("margin-left","4px");});//]]></script>';}