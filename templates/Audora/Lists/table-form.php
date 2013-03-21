<?php
$tfn=$tip=false;
return array(
	'form'=>function($a=array())
	{
		if(!is_array($a))
			$a=array('action'=>$a);
		$a+=array('method'=>'post');
		return'<form'.Eleanor::TagParams($a).'>';
	},
	'endform'=>function() use (&$tip)
	{
		$GLOBALS['jscripts'][]='js/jquery.poshytip.js';
		return'</form>'.($tip ? '<script type="text/javascript">//<![CDATA[
		$(function(){
			$("span.labinfo").poshytip({
				className: "tooltip",
				offsetX: -7,
				offsetY: 16,
				allowTipHover: false
			});
		});//]]></script>' : '');
	},
	'begin'=>function() use ($tfn)
	{global$Eleanor;
		$tfn=true;
		$a=func_get_args();
		if(!isset($a[0]) or !is_array($a[0]))
			$a[0]=array();
		$a[0]+=array('class'=>'tabstyle tabform');
		return'<table'.Eleanor::TagParams($a[0]).'>';
	},
	'head'=>function($a) use ($tfn)
	{
		$tfn=false;
		$a=func_num_args()>1 ? func_get_args() : (array)$a;
		$a+=array('tr'=>array());
		$a['tr']+=array('class'=>'infolabel first');
		return'<tr'.Eleanor::TagParams($a['tr']).'>'.(empty($a[1]) ? '<td colspan="2">'.$a[0].'</td>' : '<td>'.$a[0].'</td><td>'.$a[1].'</td>').'</tr>';
	},
	'item'=>function($a) use (&$tip)
	{
		if(func_num_args()>1)
			$a=func_get_args();
		if(!isset($a['tr']) or !is_array($a['tr']))
			$a['tr']=array();
		if(!isset($a['td1']) or !is_array($a['td1']))
			$a['td1']=array();
		$a['td1']+=array('class'=>'label');
		if(!isset($a['td2']) or !is_array($a['td2']))
			$a['td2']=array();
		$t=!empty($a['tip']);
		if($t)
			$tip=true;
		return'<tr'.Eleanor::TagParams($a['tr']).'><td'.Eleanor::TagParams($a['td1']).'>'.($t ? '<span class="labinfo" title="'.htmlspecialchars($a['tip'],ENT_COMPAT,CHARSET).'">(?)</span> ' : '').$a[0].(empty($a['imp']) ? '' : ' <span class="imp">*</span>').(empty($a['descr']) ? '' : '<br /><span class="small">'.$a['descr'].'</span>').'</td><td'.Eleanor::TagParams($a['td2']).'>'.$a[1].'</td></tr>';
	},
	'button'=>'<tr><td colspan="2" style="text-align:center">{0}</td></tr>',
	'submitline'=>'<div class="submitline">{0}</div>',
	'end'=>'</table>',
	'tabs'=>function()
	{static $n=0;
		$GLOBALS['jscripts'][]='js/tabs.js';
		$tabs=func_get_args();
		if(count($tabs)==1 and isset($tabs[0]) and is_array($tabs[0]))
			$tabs=$tabs[0];
		$top=$c='';
		$first=true;
		foreach($tabs as &$tab)
			if(is_array($tab) and isset($tab[0],$tab[1]))
			{
				$id=isset($tab['id']) ? $tab['id'] : 'tab'.$n++;
				$top.='<li><a href="#" data-rel="'.$id.'"'.($first ? ' class="selected"' : '').'><b>'.$tab[0].'</b></a></li>';
				$c.='<div id="'.$id.'" class="tabcontent">'.$tab[1].'</div>';
				$first=false;
			}
		$u=uniqid();
		return'<ul id="'.$u.'" class="linetabs">'.$top.'</ul>'.$c.'<script type="text/javascript">/*<![CDATA[*/$(function(){$("#'.$u.' a").Tabs();});//]]></script>';
	},
);