<?php
/*
	Шаблон блока "Архив публикаций". Преимущественно для модуля новостей

	@var массив дней, ключи
		m - месяц
		y - год
		pm - предыдущий месяц или false, если публикаций в предыдущем месяце нет
		nm - следующий месяц или false, если публикаций в следующим месяце нет
		py - предыдущий год или false, если публикаций в предыдущем году нет
		ny - следующий год или false, если публикаций в следующим месяце нет
		a - ссылка на просмотр новостей месяца
		calendar - массив неделя=>день недели=>число (если нет публикаций) или массив с ключами:
			day - число
			cnt - количество публикаций
			a - ссылка на публикации
	@var имя модуля
	@var флаг AJAX-запроса. При AJAX-запросе возвращается только содержимое дней, поскольку переключается только календарь с днями
	@var массив месяцев, формат YYYYMM=>array(), ключи внутреннего массива:
		cnt - количество публикаций в месяце
		a - ссылка на просмотр публикаций
*/
$ltpl=Eleanor::$Language['tpl'];
$tday=date('j');
$tmon=$v_0['y']==date('Y') && $v_0['m']==date('n');

$yb='';
if($v_0['py'])
	$yb.='<a href="#" class="y-prev" title="'.$ltpl['year-'].'"><img src="'.$theme.'images/year_minus.png" alt="" /></a>';
if($v_0['ny'])
	$yb.='<a href="#" class="y-next" title="'.$ltpl['year+'].'"><img src="'.$theme.'images/year_plus.png" alt="" /></a>';

$cnt=0;
$calendar='<div class="month"><h4>'.Eleanor::$Language->Date($v_0['y'].'-'.$v_0['m'],'my').'</h4>'
.($yb ? '<span class="selyears">'.$yb.'</span>' : '')
.'<div class="clr"></div></div>
<table><tr class="c_days"><td>'.$ltpl['mon'].'</td><td>'.$ltpl['tue'].'</td><td>'.$ltpl['wed'].'</td><td>'.$ltpl['thu'].'</td><td>'.$ltpl['fri'].'</td><td class="vday">'.$ltpl['sat'].'</td><td class="vday">'.$ltpl['sun'].'</td></tr>';
foreach($v_0['calendar'] as &$week)
{	$calendar.='<tr>';
	foreach($week as $k=>&$day)
	{		if(!$day)
			$td='&nbsp;';
		elseif(is_array($day))
		{			$cnt+=$day['cnt'];
			$td='<a href="'.$day['a'].'" title="'.$ltpl['_cnt']($day['cnt']).'">'.$day['day'].'</a>';
			$day=$day['day'];
		}
		else
			$td=$day;

		$cl=$tmon && $day==$tday ? 'today' : false;
		if($k>4)
			$cl=$cl ? 'tovday' : 'vday';
		$calendar.='<td'.($cl ? ' class="'.$cl.'"' : '').'>'.$td.'</td>';	}
	$calendar.='</tr>';
}
$arrows='';
if($v_0['pm'])
{	$p=Eleanor::$Language->Date(array('n'=>$v_0['pm'],'d'=>1),'my');
	$p=substr($p,0,strpos($p,' '));
	$arrows.='<a class="arrowleft m-prev" href="#">'.$p.'</a>';
}

if($v_0['nm'])
{
	$n=Eleanor::$Language->Date(array('n'=>$v_0['nm'],'d'=>1),'my');
	$n=substr($n,0,strpos($n,' '));
	$arrows.='<a class="arrowright m-next" href="#">'.$n.'</a>';
}

$calendar.='</table>'.($arrows ? '<div class="arrows">'.$arrows.'<hr /></div>' : '')
.'<div style="text-align:center">'.($cnt>0 ? '<a href="'.$v_0['a'].'">'.$ltpl['total']($cnt).'</a>' : $ltpl['no_per']).'</div>';

if($v_2)#Ajax
	return$calendar;

foreach($v_3 as $k=>&$v)
	$v='<a href="'.$v['a'].'">'.Strings::UcFirst(Eleanor::$Language->Date($k,'my')).' ('.$v['cnt'].')</a>';

$GLOBALS['jscripts'][]='js/block_archive.js';
$u=uniqid('cal-');
echo'<div class="blockcalendar" id="'.$u.'">',
	$calendar,
	'</div>',
	$v_3 ? '<div id="'.$u.'d" style="display:none">'.join('<br />',$v_3).'</div>' : '',
	'<script type="text/javascript">/*<![CDATA[*/
$(function(){
	new CORE.Archive({module:"'.$v_1.'",year:'.$v_0['y'].',month:'.$v_0['m'].',container:"#'.$u.'"});
	var cl=localStorage.getItem("clndr","1"),
		ar=$("#'.$u.',#'.$u.'d");
	if(cl)
		ar.toggle();
	$("#'.$u.'").closest(".dcont").prev().css("cursor","pointer").click(function(){		ar.toggle();
		cl=!cl;
		if(cl)
			localStorage.setItem("clndr","1");
		else
			localStorage.removeItem("clndr");
		return false;	});
});//]]></script>';