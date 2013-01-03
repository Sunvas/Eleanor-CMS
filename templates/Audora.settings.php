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
	'service'=>array('admin'),#Шаблон для админки
	'creation'=>'2009-12-29',#Дата создания
	'author'=>'Eleanor CMS team',#Автор шаблона
	'name'=>'Стандартный шаблон панели администратора',#Название шаблона
	'info'=><<<INFO
	Информация
INFO
,
	'license'=><<<'LICENSE'
Стандартная тема оформления панели администратора. Пользоваться ею в составе Eleanor CMS можно бесплатно. Использовать этот шаблон или его части в сторонних разработках - строго запрещено!
LICENSE
,#Лицензия

	#Места блоков
	'places'=>array(
		'right'=>array(
			'title'=>array(
				'russian'=>'Правые блоки',
				'english'=>'Right blocks',
				'ukrainian'=>'Праві блоки',
			),
			'extra'=>'276,10,160,229,0',
		),
	),

	'options'=>array(
		'sizethm'=>array(
			'title'=>'Тип размера админчасти',
			'descr'=>'По умолчанию: Резиновый',
			'default'=>1,
			'options'=>array(
				'options'=>array('r'=>'Резиновый','f'=>'Фиксированный'),
				'tabindex'=>1,
			),
			'type'=>'select',
		),
		'colorbg'=>array(
			'title'=>'Цвет фона',
			'descr'=>'По умолчанию: #2d2f30',
			'default'=>'#2d2f30',
			'type'=>'input',
			'options'=>array(
				'tabindex'=>2,
			),
		),
		'imagebg'=>array(
			'title'=>'Фоновое изображение',
			'descr'=>'По умолчанию: templates/Audora/images/pagebg.png',
			'default'=>'templates/Audora/images/pagebg.png',
			'type'=>'input',
			'options'=>array(
				'tabindex'=>3,
			),
		),
		'positionimg'=>array(
			'title'=>'Позиция фонового изображения',
			'descr'=>'В формате X Y, где X - позиция по оси x, Y - позиция по оси y. Например: 50% 5px. По умолчанию: 0 0.',
			'default'=>'0 0',
			'type'=>'input',
			'options'=>array(
				'tabindex'=>4,
			),
		),
		'bgattachment'=>array(
			'title'=>'Прокрутка фонового изображения',
			'descr'=>'По умолчанию: отключена',
			'default'=>false,
			'type'=>'check',
			'options'=>array(
				'tabindex'=>5,
			),
		),
		'bgrepeat'=>array(
			'title'=>'Повтор фонового изображения',
			'descr'=>'По умолчанию: По горизонтали',
			'default'=>'repeat-x',
			'options'=>array(
				'options'=>array('repeat'=>'По горизонтали и вертикали','repeat-x'=>'По горизонтали','repeat-y'=>'По вертикали','no-repeat'=>'Не повторять'),
				'tabindex'=>6,
			),
			'type'=>'select',
		),
	),
);