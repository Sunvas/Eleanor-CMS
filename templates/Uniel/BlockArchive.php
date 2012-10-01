<?php
/*
	Шаблон блока "Архив публикаций". Преимущественно для модуля новостей

	@var массив дней, ключи
		m - месяц
		y - год
		pm - предыдущий месяц или false, если публикаций в предыдущем месяце нету
		nm - следующий месяц или false, если публикаций в следующим месяце нету
		py - предыдущий год или false, если публикаций в предыдущем году нету
		ny - следующий год или false, если публикаций в следующим месяце нету
		a - ссылка на просмотр новостей месяца
		calendar - массив неделя=>день недели=>число (если нет публикаций) или массив с ключами:
			day - число
			cnt - количество публикаций
			a - ссылка на публикации
	@var языковой массив
	@var имя модуля
	@var флаг AJAX запроса. При AJAX запросе возвращается только содержимое дней, поскольку переключается только календарь с днями
	@var массив месяцев, формат YYYYMM=>array(), ключи внутреннего массива:
		cnt - количество публикаций в месяце
		a - ссылка на просмотр публикаций
*/
$tday=date('j');
$tmon=$v_0['y']==date('Y') && $v_0['m']==date('n');

$yb='';
if($v_0['py'])
	$yb.='<a href="#" class="yearminus" title="'.$v_1['year-'].'"><img src="'.Eleanor::$Template->default['theme'].'images/year_minus.png" alt="" /></a>';
if($v_0['ny'])
	$yb.='<a href="#" class="yearplus" title="'.$v_1['year+'].'"><img src="'.Eleanor::$Template->default['theme'].'images/year_plus.png" alt="" /></a>';

$cnt=0;
$calendar='<div class="month"><h4>'.Eleanor::$Language->Date($v_0['y'].'-'.$v_0['m'],'my').'</h4>'
.($v_0['py'] ? '<span class="selyears">'.$yb.'</span>' : '')
.'<div class="clr"></div></div>
<table><tr class="c_days"><td>'.$v_1['mon'].'</td><td>'.$v_1['tue'].'</td><td>'.$v_1['wed'].'</td><td>'.$v_1['thu'].'</td><td>'.$v_1['fri'].'</td><td class="vday">'.$v_1['sat'].'</td><td class="vday">'.$v_1['sun'].'</td></tr>';
foreach($v_0['calendar'] as &$week)
{	$calendar.='<tr>';
	foreach($week as $k=>&$day)
	{		if(!$day)
			$td='&nbsp;';
		elseif(is_array($day))
		{			$cnt+=$day['cnt'];
			$td='<a href="'.$day['a'].'" title="'.$v_1['_cnt']($day['cnt']).'">'.$day['day'].'</a>';
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
	$arrows.='<a class="arrowleft monthminus" href="#">'.$p.'</a>';
}

if($v_0['nm'])
{
	$n=Eleanor::$Language->Date(array('n'=>$v_0['nm'],'d'=>1),'my');
	$n=substr($n,0,strpos($n,' '));
	$arrows.='<a class="arrowright monthplus" href="#">'.$n.'</a>';
}

$calendar.='</table>'.($arrows ? '<div class="arrows">'.$arrows.'<hr /></div>' : '')
.'<div style="text-align:center">'.($cnt>0 ? '<a href="'.$v_0['a'].'">'.$v_1['total']($cnt).'</a>' : $v_1['no_per']).'</div>';

if($v_3)#Ajax
	return$calendar;

foreach($v_4 as $k=>&$v)
	$v='<a href="'.$v['a'].'">'.Strings::UcFirst(Eleanor::$Language->Date($k,'my')).' ('.$v['cnt'].')</a>';

$u=uniqid('cal-');
echo'<div class="blockcalendar" id="'.$u.'">',
	$calendar,
	'</div>',
	$v_4 ? '<div id="'.$u.'d" style="display:none">'.join('<br />',$v_4).'</div>' : '',
	'<script type="text/javascript" src="js/block_archive.js"></script><script type="text/javascript">/*<![CDATA[*/
$(function(){
	new CORE.Archive({module:"'.$v_2.'",year:'.$v_0['y'].',month:'.$v_0['m'].',container:"#'.$u.'"});
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