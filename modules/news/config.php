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
	'n'=>'news',#Модуля
	't'=>P.'news',#Имя таблицы с контентом
	'tl'=>P.'news_l',#Имя таблицы с языковым контентом
	'tt'=>P.'news_tags',#Имя таблицы с тегами
	'rt'=>P.'news_rt',#Имя таблицы с тегами => новостями (Related tags)
	'c'=>P.'news_categories',#Имя таблицы с категориями
	'admintpl'=>'AdminNews',#Класс администраторского оформления
	'usertpl'=>'UserNews',#Класс пользовательского оформления
	'usercorrecttpl'=>'UserNewsCorrect',#Класс пользовательского оформления
	'opts'=>'module_news',#Название группы опций
	'pv'=>'m_news_',#Префикс настроек
	'api'=>'ApiNews',#Название класса
	'secret'=>crc32(__file__),#Для подписи новостей, добавленных гостями
);