<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
if(!defined('CMS'))die;
return array(
	'service'=>array('user'),#Шаблон для пользователей
	'creation'=>'2009-03-07',#Дата создания
	'author'=>'Eleanor CMS team',#Автор шаблона
	'name'=>'Стандартный шаблон',#Название шаблона
	'info'=><<<INFO
	Информация
INFO
,
	'license'=><<<'LICENSE'
Стандартная тема оформления пользовательской части. Пользоваться ею в составе Eleanor CMS можно бесплатно. Использовать этот шаблон или его части в сторонних разработках - строго запрещено!
LICENSE
,#Лицензия
	'options'=>array(#Опции в общесистемном виде
		'Группа 1: текстовые поля',
		'param1'=>array(
			'title'=>'Строка',
			'descr'=>'описание строки',
			'default'=>'значение по-умолчанию',
			'type'=>'edit',
			'options'=>array(
				'addon'=>array('tabindex'=>1)
			),
		),
		'param2'=>array(
			'title'=>'Текстовое поле',
			'descr'=>'описание текстового поля',
			'default'=>'значение по-умолчанию',
			'type'=>'text',
			'options'=>array(
				'addon'=>array('tabindex'=>2),
			),
		),
		'param3'=>array(
			'title'=>'Текстовый редактор',
			'descr'=>'описание текстового редактора',
			'default'=>'значение по-умолчанию',
			'type'=>'editor',
			'addon'=>array(
				'no'=>array('tabindex'=>3),
			)
		),
		'Группа 2: select-ы',
		'param4'=>array(
			'title'=>'Выбор1',
			'descr'=>'описание выбора 1',
			'default'=>1,
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'addon'=>array('tabindex'=>4),
			),
			'type'=>'select',
		),
		'param5'=>array(
			'title'=>'Выбор2',
			'descr'=>'описание выбора 2',
			'default'=>2,
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'addon'=>array('tabindex'=>5),
			),
			'type'=>'item',
		),
		'param6'=>array(
			'title'=>'Множественный выбор',
			'descr'=>'описание множественного выбора',
			'default'=>array(0,1,2),
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'addon'=>array('tabindex'=>6),
			),
			'type'=>'items',
		),
		'Группа 3: Chechbox',
		'param8'=>array(
			'title'=>'Checkbox',
			'descr'=>'описание checkbox-a',
			'default'=>true,
			'type'=>'check',
			'options'=>array(
				'addon'=>array('tabindex'=>7),
			),
		),
		'param9'=>array(
			'title'=>'Checkboxes',
			'descr'=>'описание множественного выбора',
			'default'=>array(0,1,2),
			'options'=>array(
				'options'=>array(1,2,3,4,5),
			),
			'type'=>'checks',
		),
		'Группа 4: Что угодно',
		'param10'=>array(
			'title'=>'Что угодно',
			'descr'=>'описание чего угодно',
			'options'=>array(
				'content'=>'Что угодно здесь можно поместить',
			),
			'type'=>'',
		),
	)
);