<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/
class Dates
{
	/**
	 * Вывод календаря: контрола выбора даты и времени
	 *
	 * @param string $name Имя контрола
	 * @param string $value Значение контрола
	 * @param bool $time Флаг возможности выбирать время (минуты и секунды)
	 * @param array $a Дополнительные параметры контрола
	 */
	public static function Calendar($name,$value='',$time=false,array$a=array())
	{
		if(strncmp('0000-00-00',$value,10)==0)
			$value='';
		else
			$value=preg_replace('#(\d{1,2}):(\d{1,2}):00#','\1:\2',$value);
		$ba=array();
		if(isset($a['tabindex']))
			$ba['tabindex']=$a['tabindex']+1;
		array_push($GLOBALS['jscripts'],'addons/calendar/calendar.js','addons/calendar/lang/'.substr(Language::$main,0,3).'.js');
		$GLOBALS['head'][__class__.__function__]='<link media="screen" href="addons/calendar/style.css" type="text/css" rel="stylesheet" />';
		if(!isset($a['id']))
			$a['id']=preg_replace('#[^a-z0-9\-_]+#i','',$name);
		return Eleanor::Input($name,$value,$a).Eleanor::Button('...','button',$ba)
		.'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#'.$a['id'].'").on("clone",function(){
		this.Calendar=new Calendar({
			inputField:this,
			trigger:$(this).next().get(0),
			showTime:'.($time ? 'true' : 'false').',
			dateFormat:"%Y-%m-%d'.($time ? ' %H:%M' : '').'",
			weekNumbers:true,
			minuteStep:1,
			onSelect:function(C){
				var d=C.selection.get();
				if(d)
				{
					d=Calendar.intToDate(d);
					'.($time ? 'd.setMinutes(C.getMinutes());d.setHours(C.getHours());' : '').'
					$(C.args.inputField).val(Calendar.printDate(d,C.args.dateFormat));
				}
			},
			onTimeChange:function(C){
				var d=C.selection.get();
				if(d)
				{
					d=Calendar.intToDate(d);
					'.($time ? 'd.setMinutes(C.getMinutes());d.setHours(C.getHours());' : '').'
					$(C.args.inputField).val(Calendar.printDate(d,C.args.dateFormat));
				}
			}
		});
	}).data("cloneable",true).triggerHandler("clone");
});//]]></script>';
	}

	/**
	 * Генерация "календаря" в виде array[week][day], где week это от 1 до 5 или 6, а day от 1 до 7.
	 *
	 * @param int $y Год
	 * @param int $m Месяц
	 * @param bool $pn Флаг дописывания в начало и конец календаря числа следующих месяцев, если FALSE - вставляются нули
	 */
	public static function BuildCalendar($y,$m,$pn=true)
	{
		$mt=mktime(0,0,0,$m,1,$y);
		$t=idate('w',$mt);
		if($t==0)
			$t=7;
		$p=$t>1 ? idate('t',$mt-172800)-$t+2 : 1;
		$c=array();
		$t=idate('t',$mt);
		for($week=0;$week<6;$week++)
			for($day=0;$day<7;$day++)
			{
				$d=($week==0 and $p>20 or $week>=4 and $p<10) && !$pn ? 0 : $p;
				if($day==0 and $week>=4 and $d<10)
					break;
				$c[$week][] = $d;
				if($week==0 and $p==idate('t',$mt-172800) or $week>0 and $p==$t)
					$p=0;
				++$p;
			}
		return$c;
	}
}