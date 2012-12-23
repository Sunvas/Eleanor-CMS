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

	#Места блоков
	'places'=>array(
		'left'=>array(
			'title'=>array(
				'russian'=>'Левые блоки',
				'english'=>'Left blocks',
				'ukrainian'=>'Ліві блоки',
			),
			'extra'=>'50,108,184,270,1',
		),
		'right'=>array(
			'title'=>array(
				'russian'=>'Правые блоки',
				'english'=>'Right blocks',
				'ukrainian'=>'Праві блоки',
			),
			'extra'=>'416,107,182,270,2',
		),
		'center_up'=>array(
			'title'=>array(
				'russian'=>'Верхние центрельные',
				'english'=>'Up central',
				'ukrainian'=>'Верхні центральні',
			),
			'extra'=>'50,0,548,101,3',
		),
		'center_down'=>array(
			'title'=>array(
				'russian'=>'Нижние центрельные',
				'english'=>'Down central',
				'ukrainian'=>'Нижні центральні',
			),
			'extra'=>'50,393,548,101,4',
		),
	),

	'options'=>array(#Опции в общесистемном виде
		'Группа 1: текстовые поля',
		'param1'=>array(
			'title'=>'Строка',
			'descr'=>'описание строки',
			'default'=>'значение по-умолчанию',
			'type'=>'input',
			'options'=>array(
				'extra'=>array('tabindex'=>1)
			),
		),
		'param2'=>array(
			'title'=>'Текстовое поле',
			'descr'=>'описание текстового поля',
			'default'=>'значение по-умолчанию',
			'type'=>'text',
			'options'=>array(
				'extra'=>array('tabindex'=>2),
			),
		),
		'param3'=>array(
			'title'=>'Текстовый редактор',
			'descr'=>'описание текстового редактора',
			'default'=>'значение по-умолчанию',
			'type'=>'editor',
			'extra'=>array(
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
				'extra'=>array('tabindex'=>4),
			),
			'type'=>'select',
		),
		'param5'=>array(
			'title'=>'Выбор2',
			'descr'=>'описание выбора 2',
			'default'=>2,
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'extra'=>array('tabindex'=>5),
			),
			'type'=>'item',
		),
		'param6'=>array(
			'title'=>'Множественный выбор',
			'descr'=>'описание множественного выбора',
			'default'=>array(0,1,2),
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'extra'=>array('tabindex'=>6),
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
				'extra'=>array('tabindex'=>7),
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