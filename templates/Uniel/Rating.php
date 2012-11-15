<?php
/*
	Ёлемент шаблона: вывод рейтинга материалов

	@var array(
		marks - массив возможных оценок
		can - флаг возможности установлени€ оценки
		sum - сумма всех оценок
		total - число оценок
		average - средн€€ оценка
		extra - массив дополнительных параметров дл€ запроса
	);
*/
if(!defined('CMS'))die;
$title=sprintf(Eleanor::$Language['tpl']['average_mark'],round($average,2),$total);

if($total>0)
{
	$prev=min($marks);
	$newa=0;
	foreach($marks as &$v)
	{		if($v>$average)
		{
			$newa+=($average-$prev)/($v-$prev);
			break;
		}
		$newa++;
		if($v==$average)
			break;
		$prev=$v;	}
	$average=round($newa/count($marks)*100,1);
}
else
	$average=0;

if($can)
{	$u=uniqid('r');
	$GLOBALS['jscripts'][]=$theme.'js/rating.js';
	echo'<div class="rate" title="',$title,'" id="',$u,'">
	<div class="noactive">
		<div class="active" style="width:',$average,'%;" data-now="',$average,'%"></div>
	</div>
</div><script type="text/javascript">/*<![CDATA[*/$(function(){new Rating("',$GLOBALS['Eleanor']->module['name'],'","#',$u,'",[',join(',',$marks),']',
	isset($extra) ? ','.Eleanor::JsVars($extra,false,true) : '',
	');});//]]></script>';
}
else
	echo'<div class="rate" title="'.$title.'">
	<div class="noactive">
		<div class="active" style="width:'.$average.'%;"></div>
	</div>
</div>';