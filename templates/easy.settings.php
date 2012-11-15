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
	'creation'=>'2012-27-06',#Дата создания
	'author'=>'Eleanor CMS team',#Автор шаблона
	'name'=>'Стандартный шаблон первой версии',#Название шаблона
	'info'=><<<INFO
	Информация
INFO
,
	'license'=><<<LICENSE
Стандартная тема оформления пользовательской части. Пользоваться ею в составе Eleanor CMS можно бесплатно. Использовать этот шаблон или его части в сторонних разработках - строго запрещено!
LICENSE
,#Лицензия
	'options'=>array(#Опции в общесистемном виде
		'eleanor'=>array(
			'title'=>'Отображать рекламу Eleanor CMS',
			'descr'=>'',
			'default'=>true,
			'type'=>'check',
			'options'=>array(
				'extra'=>array('tabindex'=>1),
			),
		),
		'downtags'=>array(
			'title'=>'Отображать облако тего снизу',
			'descr'=>'',
			'default'=>true,
			'type'=>'check',
			'options'=>array(
				'extra'=>array('tabindex'=>2),
			),
		),
	)
);