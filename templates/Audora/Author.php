<?php
/*
	Элемент шаблона. Интерфейс выбора автора материала

	@var имя автора. Возможен формат array('input name'=>'имя автора'). По умолчанию input name для имени автора "author"
	@var ID автора. Возможен формат array('input name'=>'ID автора'). По умолчанию input name для имени автора "author_id"
	@var tabindex для инпутов выбора автора. Всего шаблон выдает 2 инпута, tabindex-ы которых будут равны переданному параметру и переданному параметру + 1
*/
if(!defined('CMS'))die;
global$Eleanor;
$a=isset($v_0) ? $v_0 : false;
$aid=isset($v_1) ? $v_1 : false;
$ti=isset($v_2) ? (int)$v_2 : false;

$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
$GLOBALS['head']['author']='<script type="text/javascript">//<![CDATA[
var SAI=[];
function AuthorSelected(name,id,wn)
{
	if(SAI[wn])
	{
		SAI[wn].val(name).next().val(id);
		delete SAI[wn];
	}
}

$(function(){
	$("div.author").on("clone",function(){
		$(this).find("a").click(function(){
			var h=380,
				w=360,
				wn=$(this).data("wn")||Math.random(),
				win=window.open("'.$Eleanor->Url->file.'?section=management&module=users&do=userlist",wn,"height="+h+",width="+w+",toolbar=no,menubar=no,location=no,scrollbars=no,focus=yes,top="+Math.round((screen.height-h)/2)+",left="+Math.round((screen.width-w)/2));
			SAI[wn]=$(this).data("wn",wn).closest(".author").find("input:first");
			return false;
		}).end()
		.find("input:first").autocomplete({
			serviceUrl:CORE.ajax_file,
			minChars:2,
			delimiter:null,
			params:{
				direct:"'.Eleanor::$service.'",
				file:"autocomplete",
				goal:"users"
			},
			onSelect: function(value,data,el){ el.next().val(data) }
		});
	}).addClass("cloneable").trigger("clone");
});//]]></script>';

if(is_array($a))
{
	$an=key($a);
	if(count($a)>1)
		$aid=array_splice($a,1,1);
	$a=reset($a);
}
else
	$an='author';

if(is_array($aid))
{
	$aidn=key($aid);
	$aid=reset($aid);
}
else
	$aidn='author_id';

return'<div class="author">'.Eleanor::Input($an,$a,array('tabindex'=>$ti ? $ti : false)).Eleanor::Input($aidn,$aid,array('title'=>'ID','style'=>'width: 25px','tabindex'=>$ti ? ++$ti : false))
	.'<a href="#" title="'.Eleanor::$Language['tpl']['select_user'].'"> <img src="'.Eleanor::$Template->default['theme'].'images/select_users.png" alt="" /></a></div>';