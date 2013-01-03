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

if(!defined('INSTALL') and !defined('UPDATE'))die;

define('CHARSET','utf-8');
define('DISPLAY_CHARSET','utf-8');
define('DB_CHARSET','utf8');
define('PROTOCOL','http://');
define('ELEANOR_VERSION',0.9);
define('ELEANOR_BUILD',6);
define('LANGUAGE','russian');
define('DEBUG',false);
define('GROUP_USER',2);#Пользовательская группа
define('GROUP_WAIT',5);#Группа ожидающих активации

#Возможно глупо потому что многие из России. Но разработчик живет в Николаеве, где время киевское :)
date_default_timezone_set('Europe/Kiev');

require'./core/core.php';
require'./../core/core.php';
$head=array();
error_reporting(E_ALL^E_NOTICE);
#ПРЕДУСТАНОВЛЕННЫЕ ЯЗЫКИ! PREINSTALLED LANGUAGES!
Eleanor::$langs=array(
	'russian'=>array('name'=>'Русский','uri'=>'рус','sel'=>'Выбрать основной язык системы русским','d'=>'ru','l'=>'ru_RU.'.CHARSET),
	'english'=>array('name'=>'English','uri'=>'eng','sel'=>'Select english main language of system','d'=>'en','l'=>'en.'.CHARSET),
	'ukrainian'=>array('name'=>'Українська','uri'=>'укр','sel'=>'Обрати основною мовою українську','d'=>'ua','l'=>'ua.'.CHARSET),
);
$Eleanor=Eleanor::getInstance(false);
Eleanor::$service='admin';
Eleanor::InitTemplate('template','');

#Разные настройки:
Eleanor::$vars=array(
	'log_maxsize'=>0,
	'cookie_prefix'=>'',
	'site_domain'=>'',
	'cookie_domain'=>Eleanor::$domain,
	'cookie_save_time'=>86400,
	'log_errors'=>'addons/logs/install.log',
	'log_db_errors'=>'addons/logs/install.log',
	'show_status'=>0,
	'time_zone'=>date_default_timezone_get(),
	'gzip'=>true,
	'parked_domains'=>true,
	'page_caching'=>false,
)+Eleanor::$vars;

#Поддержка IDN
if(strpos(Eleanor::$domain,'xn--')!==false)
{
	Eleanor::$punycode=Eleanor::$domain;
	Eleanor::$domain=Punycode::Domain(Eleanor::$domain,false);
}
elseif(preg_match('#^[a-z0-9\-\.]+$#i',Eleanor::$domain)==0)
	Eleanor::$punycode=Punycode::Domain(Eleanor::$domain);
else
	Eleanor::$punycode=&Eleanor::$domain;

Eleanor::$UsersDb=&Eleanor::$Db;

#Внимание! САМОВОЛЬНОЕ УБИРАНИЕ КОПИРАЙТОВ ЧРЕВАТО БЛОКИРОВКОЙ НА ОФИЦИАЛЬНОМ САЙТЕ СИСТЕМЫ И ПРЕСЛЕДУЕТСЯ ПО ЗАКОНУ!
#КОПИРАЙТЫ МЕНЯТЬ/ПРАВИТЬ НЕЛЬЗЯ! СОВСЕМ!! ОНИ ДОЛЖНЫ ОСТАВАТЬСЯ НЕИЗМЕННЫМИ ДО БИТА!
if(!defined('ELEANOR_COPYRIGHT'))
	define('ELEANOR_COPYRIGHT','<!-- ]]></script> --><a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © <!-- Eleanor CMS Team http://eleanor-cms.ru/copyright.php -->'.idate('Y'));