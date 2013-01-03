<?php
/*
	Шаблон страницы. Редактор быстрого меню (в админке слева)

	@var массив с ключами:
		big - флаг больших иконок
		modules - модули
		lang - массив языковых значений
*/
if(!defined('CMS'))die;
$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/qmenu-*.php',false);
$c='<script src="js/jquery.drag.js" type="text/javascript"></script>
<script type="text/javascript">/*<![CDATA[*/';

if($_SERVER['REQUEST_METHOD']=='POST')
	$c.='window.opener.location.reload();window.close();';

$c.='$(function(){'	.Eleanor::JsVars(array(
		'tr'=>'<tr class="drag"><th><img src="images/spacer.png" alt="" title="'.$lang['updown'].'" class="updown" style="cursor:move;" /></th><td><a href="#"></a></td><td>'.Eleanor::Button('X','button',array('name'=>'del','title'=>Eleanor::$Language['tpl']['delete'],'style'=>'float:right')).'<input type="hidden" value="" name="mid[]" /><input type="hidden" value="" name="lid[]" /></td></tr>',
	),false)
	.'var mpos={},
		i,n=1;
	$("input[type=\"checkbox\"]","#menu").click(function(){		var th=$(this);		if(th.prop("checked"))
		{			if(!th.data("tr"))
				th.data(
					"tr",
					$(tr)
					.find("img:first").prop("src",th.closest("li").find("img").attr("src")).end()
					.find("a:first").prop("href",th.data("url")).text(th.attr("title")).end()
					.find("input[type=\"button\"]:first").click(function(){ th.prop("checked",false).triggerHandler("click"); }).end()
					.find("input[name=\"mid[]\"]").val(th.data("mid")).end()
					.find("input[name=\"lid[]\"]").val(th.val()).end()
				)
			th.data("tr").appendTo("#menutable");
			$("#menutable tr.empty").hide();
		}
		else
		{			th.data("tr").detach();			if($("#menutable .drag").size()==0)
				$("#menutable tr.empty").show();		}
		$("#menutable").DragAndDrop({
			items:"tr.drag",
			move:".updown",
			replace:"<tr><td colspan=\"3\"></td></tr>"
		});	}).each(function(){		if($(this).data("pos"))
		{			mpos[$(this).data("pos")]=$(this);
			n++;
		}
	});
	for(i=1;i<n;i++)
		mpos[i].triggerHandler("click");})
//]]></script>
<div class="column">
<form method="post">
<table class="table" id="menutable">
<tr class="empty"><td colspan="3" style="text-align:center;font-weight:bold">'.$lang['nomenu'].'</td></tr>
</table>
<div><label>'.$lang['bigicons'].Eleanor::Check('bigicons',$big).'</label> '.Eleanor::Button().'</div>
</form>
</div>
<div class="column">';

if($modules)
{	$c.='<ul id="menu">';
	foreach($modules as $k=>$v)
	{		$c.='<li><img src="images/modules/'.$v['image'].'" /> <b>'.$v['title'].'</b>';
		foreach($v['menu'] as $mk=>$mv)
			$c.='<br /><label><span>'.Eleanor::Check(false,isset($mv['_act']),array('value'=>$mk,'data-pos'=>isset($mv['_act']) ? $mv['_act'] : false,'data-mid'=>$k,'data-url'=>$mv['href'],'title'=>$mv['title'])).' '.$mv['title'].'</label></span>';		$c.='</li>';
	}
	$c.='</ul>';
}
$c.='</div>';
echo Eleanor::$Template->SimplePage($c);