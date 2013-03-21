<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны отображения опроса, готового опрашивать людей
*/
class TplVoting
{
	public static
		$lang;

	/*
		Вывод опроса
		$voting - массив параметров опроса, ключи:
			id - идентификатор опроса в БД
			begin - дата начала опроса, либо 0000-00-00, если опрос работал с момента добавления
			end - дата окончания опроса
			onlyusers - флаг опроса только для пользователей (не гостей)
			againdays - количество дней, по истечению которых можно снова голосовать
			votes - число опрошеных
		$qs - массив вопросов опроса. Формат: id=>array(), ключи внутреннего массива:
			title - название вопроса
			variants - миссив вариантов ответа, формат: id=>текст варианта
			answers - массив количества голосов за каждый вариант, формат: id=>число голосов
			multiple - флаг возможности множественного ответа на вопрос
			maxans - в случае возможности множественного ответа, этот ключ содержит максимальное число одновременно выбранных ответов
		$status - статус опроса. Возможны следующие значения:
			false (bool) - можно голосовать
			voted - уже проголосовали
			refused - голос не защитан
			confirmed - голос защитан
			guest - голосовать нельзя, потому что голосование только для пользователей
			wait - ожидает открытия
			finished - голосование завершено
	*/
	public static function Voting($voting,$qs,$status)
	{
		$r=sprintf(static::$lang['nums'],$voting['votes']).(!$status && (int)$voting['end']>0 ? '<br />'.sprintf(static::$lang['tlimit'],Eleanor::$Language->Date($voting['end'],'fdt')) : '');
		foreach($qs as $k=>&$v)
		{
			$qid=$v['multiple'] && !$status ? uniqid() : false;

			$sum=$v['multiple'] ? max($v['answers']) : array_sum($v['answers']);
			$div=$sum==0 ? 1 : $sum;
			$r.='<div class="question"><b>'.$v['title'].'</b><ul class="voting"'.($qid ? ' id="'.$qid.'"' : '').'>';
			foreach($v['variants'] as $vk=>&$vv)
			{
				$percent=round($v['answers'][$vk]/$div*100,1);
				$text=$vv.' - '.$percent.'% ('.$v['answers'][$vk].')';
				if($status)
					$variant=$text;
				else
					$variant='<label>'.($qid ? Eleanor::Check($k.'[]',false,array('value'=>$vk)) : Eleanor::Radio($k,$vk,false)).' '.$text.'</label>';
				$r.='<li>'.$variant.($percent ? '<div style="width:'.$percent.'%;"><div><div></div></div></div>' : '').'</li>';
			}
			$r.='</ul></div>'
				.($qid ? '<script type="text/javascript">/*<![CDATA[*/new Voting.ChecksLimit("#'.$qid.'",'.$v['maxans'].')//]]></script>' : '');
		}
		switch($status)
		{
			case'guest':
			case'voted':
				$r.='<span style="font-weight:bold;">'.static::$lang[$status].'</span>';
			break;
			case'finished':
				$r.='<span style="font-weight:bold;">'.sprintf(static::$lang['finished'],Eleanor::$Language->Date($voting['end'],'fdt')).'</span>';
			break;
			case'wait':
				$r.='<span style="font-weight:bold;">'.sprintf(static::$lang['wait'],Eleanor::$Language->Date($voting['begin'],'fdt')).'</span>';
			break;
			case'confirmed':
				$r.='<span style="color:green;font-weight:bold;">'.static::$lang['vc'].'</span>';
			break;
			case'rejected':
				$r.='<span style="color:red;font-weight:bold;">'.static::$lang['vr'].'</span>';
			break;
			default:
				$r.=Eleanor::Button(static::$lang['vote']);
		}
		return$r;
	}

	/*
		Вывод формы опроса, включающей и сам опрос
		Описание переменных $voting,$qs,$status смотрите в методе Voting
		$request - параметры AJAX запроса
	*/
	public static function VotingCover($voting,$qs,$status,$request)
	{
		$q=static::Voting($voting,$qs,$status);
		if($status=='voted')
			return$q;
		$u=uniqid('v');

		$GLOBALS['jscripts'][]='js/voting.js';
		return'<form id="'.$u.'">'.$q.'</form><script type="text/javascript">//<![CDATA[
$(function(){
	new Voting({
		form:"#'.$u.'",
		similar:".voting-'.$voting['id'].'",
		type:"'.$status.'",
		request:'.Eleanor::JsVars($request,false,true).',
		qcnt:'.count($qs).'
	});
})//]]></script>';
	}
}
TplVoting::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/voting-*.php',false);