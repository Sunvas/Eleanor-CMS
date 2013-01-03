<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны управления опросом. Внутрь каждого метода подается ссылка на объект класса VotingManager, из которого необходимо брать контролы,
	переменные языка и прочее. Описание методов этого класса можно узнать, открыв файл core/others/votingmanager.php
*/
class TplVotingManager
{	public static
		$lang;
	/*
		Элемент шаблона: добавление/редактирование опроса
		$id - идентификатор редактируемого опроса, если $id==0 значит опрос добавляется
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
	*/
	public static function VmAddEdit($id,$controls,$values)
	{
		array_push($GLOBALS['jscripts'],'js/voting_manager.js','js/jquery.drag.js');

		$Lst=Eleanor::LoadListTemplate('table-form')->begin()->head(static::$lang['questions']);
		foreach($controls as $k=>&$v)
			if($k!='_questions' and $values[$k])
				if(is_array($v))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
				else
					$Lst->head($v);

		$u=uniqid('vo-');
		return'<div id="'.$u.'">'.$Lst->end().$values['_questions'].'</div><script type="text/javascript">/*<![CDATA[*/$(function(){VotingManager("'.$u.'");})//]]></script>';
	}

	/*
		Подэлемент шаблона: ввод всех вопросов опроса
		$questions - массив всех вопросов опроса. Формат: номер вопрса=>результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
	*/
	public static function VmQuestions($questions,$controls)
	{
		$Lst=Eleanor::LoadListTemplate('table-form');
		foreach($questions as $kq=>&$values)
		{
			$Lst->begin(array('class'=>'tabstyle tabform question','data-qn'=>$kq));
			foreach($controls as $k=>&$v)
				if(!empty($values[$k]))
					if(is_array($v))
						$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
					else
						$Lst->head($v);

			$Lst->button(Eleanor::Button(static::$lang['addq'],'button',array('class'=>'addquestion')).' '.Eleanor::Button(static::$lang['delq'],'button',array('class'=>'deletequestion')))->end();
		}
		return$Lst;
	}

	/*
		Элемент подэлемента шаблона: формирование вариантов ответа для каждого вопроса
		$variants - варианты ответов. Формат: номер варианта=>вариант ответа
		$vn - префикс имен контролов вариантов ответа
		$answers - количество ответов каждого варианта. Формат: номер варианта=>количество ответов. Может не отображаються, в случае, если это запрещено свойством $noans
		$an - префикс имен контролов вариантов ответа
		$ti - tabindex
		$real - число пользователей, которые реально проголосовали за этот пункт. Реально проголосовали значит, что не в админке вручную были набиты
			голоса для каждого варианта, а реальные пользователи выразили свою позицию. Формат: номер варианта=>число проголосовавших
		$noans - флаг запрета редактирования количества голосов для каждого варианта
	*/
	public static function VmVariants($variants,$vn,$answers,$an,$ti,$real,$noans)
	{
		$ltpl=Eleanor::$Language['tpl'];
		$lrvoters=static::$lang['rvoters'];
		$Lst=Eleanor::LoadListTemplate('table-list');
		$n1=$a1=$a2=$k=0;
		$v2=$v1='';
		$n2=$n=1;

		foreach($variants as $k=>&$va)
		{
			$ans=isset($answers[$k]) ? $answers[$k] : '';
			switch($n++)
			{
				case 1:
					$a1=$ans;
					$v1=$va;
					$n1=$k;
					$n2=$k+1;
				break;
				case 2:
					$a2=$ans;
					$v2=$va;
					$n2=$k;
				break;
				default:
					$Lst->item(array('<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" />','style'=>'width:1px'),Eleanor::Input($vn.'['.$k.']',$va,array('style'=>'width:100%','tabindex'=>$ti,'class'=>'variant'.$k)),$noans ? false : Eleanor::Input($an.'['.$k.']',$ans,array('type'=>'number','min'=>0,'tabindex'=>$ti,'style'=>'width:50px','class'=>'number'.$k,'data-class'=>'number'.$k,'title'=>isset($real[$k]) ? $lrvoters($real[$k]) : static::$lang['norv'])),Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus','title'=>$ltpl['delete']),2));
			}
		}
		$c=(string)$Lst->end();
		return$Lst->begin(
				array(static::$lang['va'],'colspan'=>2,'style'=>'min-width:170px','tableextra'=>array('class'=>'tabstyle variants','data-max'=>max($k,$n1,$n2))),
				$noans ? false : static::$lang['votes'],
				'&nbsp;'
			)
			->item(array('<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" />','style'=>'width:1px'),Eleanor::Input($vn.'['.$n1.']',$v1,array('style'=>'width:100%','tabindex'=>$ti,'class'=>'variant'.$n1)),$noans ? false : Eleanor::Input($an.'['.$n1.']',$a1,array('type'=>'number','min'=>0,'style'=>'width:50px','tabindex'=>$ti,'class'=>'number'.$n1,'title'=>isset($real[$n1]) ? $lrvoters($real[$n1]) : static::$lang['norv'])),Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus','title'=>$ltpl['delete']),2))
			->item(array('<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" />','style'=>'width:1px'),Eleanor::Input($vn.'['.$n2.']',$v2,array('style'=>'width:100%','tabindex'=>$ti,'class'=>'variant'.$n2)),$noans ? false : Eleanor::Input($an.'['.$n2.']',$a2,array('type'=>'number','min'=>0,'style'=>'width:50px','tabindex'=>$ti,'class'=>'number'.$n2,'title'=>isset($real[$n2]) ? $lrvoters($real[$n2]) : static::$lang['norv'])),Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus','title'=>$ltpl['delete']),2))
			.$c;
	}
}
TplVotingManager::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/voting_manager-*.php',false);