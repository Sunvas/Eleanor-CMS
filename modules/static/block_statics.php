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
if(!function_exists('BlockStatics'))
{
	function BlockStatics($Api,$upref,$a,$c='<ul>')
	{
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$n=-1;
		$nonp=true;
		foreach($a as $k=>&$v)
		{
			++$n;
			$nl=strlen($v['parents']);
			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif($nonp)
				{
					$c.=BlockStatics($Api,$upref,array_slice($a,$n));
					$nonp=false;
				}
				continue;
			}
			if($n>0)
				$c.='</li>';
			$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($upref+($GLOBALS['Eleanor']->Url->furl ? $Api->GetUri($k) : array('id'=>$k)),false).'">'.$v['title'].'</a>';
			$nonp=true;
		}
		return$c.'</li></ul>';
	}
}

if(!class_exists('ApiStatic',false))
	include dirname(__file__).'/api.php';
$Api=new ApiStatic;
$dump=$Api->GetOrderedList();
$u=uniqid();

$ma=array_keys($GLOBALS['Eleanor']->modules['sections'],'static');
if(!$ma)
	return false;
$ma=reset($ma);

return BlockStatics($Api,array('module'=>$ma),$dump,'<ul class="blockcategories" id="q'.$u.'">').'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#q'.$u.' li:has(ul)").addClass("subcat").each(function(){
		var img=$("<img>").css({cursor:"pointer","margin-right":"3px"}).prop({src:"'.Eleanor::$Template->default['theme'].'images/minus.gif",title:"+"}).prependTo(this).click(function(){
			if(CORE.GetCookie("bc")==1)
			{
				$(this).prop({src:"'.Eleanor::$Template->default['theme'].'images/plus.gif",title:"+"}).next().next().hide();
				CORE.SetCookie("bs",0)
			}
			else
			{
				$(this).prop({src:"'.Eleanor::$Template->default['theme'].'images/minus.gif",title:"-"}).next().next().show();
				CORE.SetCookie("bs","1")
			}
		});
		if(CORE.GetCookie("bs")==0)
			img.prop({src:"'.Eleanor::$Template->default['theme'].'images/plus.gif",title:"+"}).next().next().hide();
	}).find("ul").css("margin-left","4px");
});//]]></script>';