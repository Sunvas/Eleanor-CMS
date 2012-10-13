<?php
/*
	Страница добавления/редактирования статической страницы
	@var перечень контролов в соответствии с классом Controls. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
	@var результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
	@var ошибка, если ошибка пустая - значит ее нет
*/
$controls=&$v_0;
$values=&$v_1;

$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
foreach($controls as $k=>&$v)
	if(is_array($v))
		$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
	else
		$Lst->head($v);

$Lst->button(Eleanor::Button('OK','submit',array('tabindex'=>10)))->end()->endform();
return Eleanor::$Template->Cover($Lst,$v_2,'error');