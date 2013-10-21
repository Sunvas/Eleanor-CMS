<?php
/*
	Copyright © Eleanor CMS, developed by Alexander Sunvas*, interface created by Rumin Sergey.
	For details, visit the web site http://eleanor-cms.ru, emails send to support@eleanor-cms.ru .
	*Pseudonym
*/
if(!defined('CMS'))die;
$insert[]='SET FOREIGN_KEY_CHECKS=0;';
$domain=Eleanor::$domain;
$rus=in_array('russian',$languages);
$eng=in_array('english',$languages);
$ukr=in_array('ukrainian',$languages);

$insert['blocks']=<<<QUERY
INSERT INTO `{$prefix}blocks` (`id`, `ctype`, `file`, `user_groups`, `showfrom`, `showto`, `textfile`, `template`, `notemplate`, `vars`, `status`) VALUES
(1, 'file', 'addons/blocks/block_who_online.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(2, 'file', 'addons/blocks/block_tags_cloud.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(3, 'file', 'addons/blocks/block_archive.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(4, 'file', 'modules/news/block_lastvoting.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(5, 'file', 'addons/blocks/block_menu_single.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, 'a:1:{s:6:"parent";i:7;}', 1),
(6, 'file', 'modules/news/block_similar.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(7, 'file', 'addons/blocks/block_themesel.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1)
QUERY;

#Russian
if($rus)
	$insert['blocks_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}blocks_l` (`id`, `language`, `title`, `config`) VALUES
(1, 'russian', 'Кто онлайн', ''),
(2, 'russian', 'Облако тегов', ''),
(3, 'russian', 'Архив', ''),
(4, 'russian', 'Опрос', ''),
(5, 'russian', 'Вертикальное меню', 'a:1:{s:6:"parent";i:7;}'),
(6, 'russian', 'По теме', ''),
(7, 'russian', 'Выбор шаблона', '')
QUERY;
#[E] Russian

#English
if($eng)
	$insert['blocks_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}blocks_l` (`id`, `language`, `title`, `config`) VALUES
(1, 'english', 'Who online', ''),
(2, 'english', 'Tags cloud', ''),
(3, 'english', 'Archive', ''),
(4, 'english', 'Voting', ''),
(5, 'english', 'Vertical menu', 'a:1:{s:6:"parent";i:7;}'),
(6, 'english', 'By topic', ''),
(7, 'english', 'Select template', '')
QUERY;
#[E]English

#Ukrainian
if($ukr)
	$insert['blocks_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}blocks_l` (`id`, `language`, `title`, `config`) VALUES
(1, 'ukrainian', 'Хто онлайн', ''),
(2, 'ukrainian', 'Хмарка тегів', ''),
(3, 'ukrainian', 'Архів', ''),
(4, 'ukrainian', 'Опитування', ''),
(5, 'ukrainian', 'Вертикальне меню', 'a:1:{s:6:"parent";i:7;}'),
(6, 'ukrainian', 'По темі', ''),
(7, 'ukrainian', 'Вибір шаблону', '')
QUERY;
#[E]Ukrainian

$ser=array(
	1=>serialize(array(
		'russian'=>'Новости',
		'english'=>'News',
		'ukrainian'=>'Новини',
	)),
	serialize(array(
		'russian'=>'Главная страница',
		'english'=>'Mainpage',
		'ukrainian'=>'Головна сторінка',
	)),
);

$insert['blocks_ids']=<<<QUERY
INSERT INTO `{$prefix}blocks_ids` (`id`,`service`,`title_l`,`code`) VALUES
(1, 'user', '{$ser[1]}', 'return isset(\$GLOBALS[''Eleanor'']->module[''section'']) && !isset(\$GLOBALS[''Eleanor'']->module[''general'']) && \$GLOBALS[''Eleanor'']->module[''section'']==''news'';'),
(2, 'user', '{$ser[2]}', 'return isset(\$GLOBALS[''Eleanor'']->module[''general'']);')
QUERY;

$ser=array(
	'admin'=>serialize(array(
		'places'=>array(
			'right'=>array(''=>'276,10,160,229,0'),
		),
		'blocks'=>array(
			'right'=>array(1),
		),
	)),
	'user'=>serialize(array(
		'places'=>array(
			'left'=>array(''=>'50,30,184,242,1'),
			'right'=>array(''=>'415,19,182,260,2'),
		),
		'blocks'=>array(
			'left'=>array(5,7,1),
			'right'=>array(6,3,2,4),
		),
	)),
);

$insert['cache']=<<<QUERY
INSERT INTO `{$prefix}cache` (`key`,`value`) VALUES
('blocks-admin', '{$ser['admin']}'),
('blocks-user', '{$ser['user']}')
QUERY;

$ser=array(
	1=>serialize(array('russian'=>'Администраторы','english'=>'Administrators','ukrainian'=>'Адміністратори')),
	serialize(array('russian'=>'Пользователи','english'=>'Users','ukrainian'=>'Користувачі')),
	serialize(array('russian'=>'Гости','english'=>'Guests','ukrainian'=>'Гості')),
	serialize(array('russian'=>'Поисковые боты','english'=>'Search engine bots','ukrainian'=>'Пошукові боти')),
	serialize(array('russian'=>'Не активированные','english'=>'Not activated','ukrainian'=>'Не активовані')),
	serialize(array('russian'=>'Заблокированные','english'=>'Banned','ukrainian'=>'Заблоковані')),
);
$insert['groups']=<<<QUERY
INSERT INTO `{$prefix}groups` (`id`,`title_l`,`html_pref`,`html_end`,`protected`,`access_cp`,`max_upload`,`captcha`,`moderate`,`banned`) VALUES
(1, '{$ser[1]}', '<span style="color:red"><b>', '</b></span>', 1, 1, 1, 0, 0, 0),
(2, '{$ser[2]}', '', '', 1, 0, 2048, 0, 0, 0),
(3, '{$ser[3]}', '', '', 1, 0, 0, 1, 1, 0),
(4, '{$ser[4]}', '', '', 0, 0, 0, 1, 1, 0),
(5, '{$ser[5]}', '<span style="color:gray">', '</span>', 0, 0, 0, 1, 1, 0),
(6, '{$ser[6]}', '', '', 0, 0, 0, 1, 1, 1)
QUERY;

$insert['config_groups']=<<<QUERY
INSERT INTO `{$prefix}config_groups` (`id`,`name`,`protected`,`keyword`,`pos`) VALUES
(1, 'system', 1, 'system', 1),
(2, 'site', 1, 'site', 2),
(3, 'users-on-site', 1, 'users-on-site', 3),
(4, 'user-profile', 1, 'user-profile', 4),
(5, 'captcha', 1, 'captcha', 5),
(6, 'mailer', 1, 'mailer', 6),
(7, 'errors', 1, 'errors', 7),
(8, 'editor', 1, 'editor', 8),
(9, 'rss', 1, 'rss', 9),
(10, 'comments', 1, 'comments', 10),
(11, 'files', 1, 'files', 11),
(12, 'multisite', 1, 'multisite', 12),
(13, 'drafts', 1, 'drafts', 13),
(14, 'module_static', 1, 'module_static', 14),
(15, 'module_news', 0, 'module_news', 15);
QUERY;

#Russian
if($rus)
	$insert['config_groups_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}config_groups_l` (`id`,`language`,`title`,`descr`) VALUES
(1, 'russian', 'Системные настройки', 'Служебные настройки системы'),
(2, 'russian', 'Настройки сайта', 'Название, описание и другое'),
(3, 'russian', 'Пользователи на сайте', 'Глобальные настройки пользователей на сайте'),
(4, 'russian', 'Профиль пользователя', 'Персональные настройки пользователей на сайте'),
(5, 'russian', 'Капча', 'Настройки капчи'),
(6, 'russian', 'Настройки электронной почты', ''),
(7, 'russian', 'Отчеты ошибок сайта', 'Настройка отчетов об ошибках на сайте'),
(8, 'russian', 'Редактор', 'Настройки редактора'),
(9, 'russian', 'RSS ленты', 'Общие настройки RSS лент'),
(10, 'russian', 'Комментарии', ''),
(11, 'russian', 'Обработка файлов', 'Настройка загрузки и скачивания файлов'),
(12, 'russian', 'Мультисайт', 'Настройка системы для удобной работы на нескольких сайтах.'),
(13, 'russian', 'Черновики', ''),
(14, 'russian', 'Модуль "Статические страницы"', ''),
(15, 'russian', 'Настройки модуля "Новости"', '');
QUERY;
#[E] Russian

#Ukrainian
if($ukr)
	$insert['config_groups_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}config_groups_l` (`id`,`language`,`title`,`descr`) VALUES
(1, 'ukrainian', 'Службові налаштування', 'Службові налаштування системи'),
(2, 'ukrainian', 'Налаштування сайту', 'Назва, опис, та ін.'),
(3, 'ukrainian', 'Користувачі на сайті', 'Глобальні налаштування користувачів на сайті'),
(4, 'ukrainian', 'Профіль користувача', 'Персональні налаштування користувачів на сайті'),
(5, 'ukrainian', 'Капча', 'Налаштування капчі'),
(6, 'ukrainian', 'Налаштування електронної пошти', ''),
(7, 'ukrainian', 'Звіти помилок сайту', 'Налаштування звітів про помилки на сайті'),
(8, 'ukrainian', 'Редактор', 'Настройки редактора'),
(9, 'ukrainian', 'RSS стрічки', 'Загальні настройки RSS стрічок'),
(10, 'ukrainian', 'Комментарі', ''),
(11, 'ukrainian', 'Обробка файлів', 'Налаштування завантаження и скачування файлів'),
(12, 'ukrainian', 'Мультисайт', 'Налаштування системи для зручної роботи на кількох сайтах.'),
(13, 'ukrainian', 'Чернетки', ''),
(14, 'ukrainian', 'Модуль "Статичні сторінки"', ''),
(15, 'ukrainian', 'Установки модуля "Новини"', '');
QUERY;
#[E] Ukrainian

#English
if($eng)
	$insert['config_groups_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}config_groups_l` (`id`,`language`,`title`,`descr`) VALUES
(1, 'english', 'System settings', 'System configuration tools'),
(2, 'english', 'Site settings', 'Title, description and others'),
(3, 'english', 'Users on site', 'Global settings users on site'),
(4, 'english', 'Users profile', 'Personal settings users on site'),
(5, 'english', 'Captcha', 'Captcha options'),
(6, 'english', 'E-mail settings', ''),
(7, 'english', 'Reports of errors', 'Configuring error reports on the site'),
(8, 'english', 'Editor', 'Editor settings'),
(9, 'english', 'RSS feeds', 'General settings of RSS feeds'),
(10, 'english', 'Comments', ''),
(11, 'english', 'Proccessing files', 'Settings of uploading and downloading files'),
(12, 'english', 'Multisite', 'Configuring the system for easy operation at several sites.'),
(13, 'english', 'Drafts', ''),
(14, 'english', 'Static "Pages module"', ''),
(15, 'english', 'Module configuration "News"', '');
QUERY;
#English

$insert['config']=<<<QUERY
INSERT INTO `{$prefix}config` (`id`,`group`,`type`,`name`,`protected`,`pos`,`multilang`,`eval_load`,`eval_save`) VALUES
(1, 1, 'input', 'site_domain', 1, 1, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=preg_replace(''#^(?:[a-z]{2,}://)?([a-z0-9\\\\-\\\\.]+).*\$#i'',''\\\\1'',\$v);\\n	return\$co[''value''];\\n}\\nelse\\n	return preg_replace(''#^(?:[a-z]{2,}://)?([a-z0-9\\\\-\\\\.]+).*\$#i'',''\\\\1'',\$co[''value'']);'),
(2, 1, 'select', 'parked_domains', 1, 2, 0, '', ''),
(3, 1, 'input', 'page_caching', 1, 3, 0, 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs(round((int)\$v/60));\\n}\\nelse\\n	\$co[''value'']=abs(round((int)\$co[''value'']/60));\\nreturn\$co;', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v)*60;\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']*60);'),
(4, 1, 'check', 'gzip', 1, 4, 0, '', ''),
(5, 1, 'input', 'cookie_save_time', 1, 5, 0, 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=round(\$v/86400);\\nelse\\n	\$co[''value'']=round(\$co[''value'']/86400);\\nreturn\$co;', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v*86400);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']*86400);'),
(6, 1, 'input', 'cookie_domain', 1, 6, 0, '', ''),
(7, 1, 'input', 'cookie_prefix', 1, 7, 0, '', ''),
(8, 1, 'items', 'guest_group', 1, 8, 0, '', ''),
(9, 1, 'check', 'bots_enable', 1, 9, 0, '', ''),
(10, 1, 'items', 'bot_group', 1, 10, 0, '', ''),
(11, 1, 'text', 'bots_list', 0, 11, 0, 'if(\$co[''multilang''])\r\n	foreach(\$co[''value''] as &\$v)\r\n	{\r\n		foreach(\$v as \$k=>&\$bot)\r\n			\$bot=\$k.''=''.\$bot;\r\n		\$v=join("\\n",\$v);\r\n	}\r\nelse\r\n{\r\n	foreach(\$co[''value''] as \$k=>&\$bot)\r\n		\$bot=\$k.''=''.\$bot;\r\n	\$co[''value'']=join("\\n",\$co[''value'']);\r\n}\r\nreturn\$co;', 'if(\$co[''multilang''])\r\n{\r\n	foreach(\$co[''value''] as &\$v)\r\n	{\r\n		\$res=array();\r\n		\$v=str_replace("\\r",'''',\$v);\r\n		foreach(explode("\\n",\$v) as \$bot)\r\n			if(strpos(\$bot,''='')!==false)\r\n			{\r\n				list(\$uagent,\$name)=explode(''='',\$bot,2);\r\n				\$res[\$uagent]=\$name;\r\n			}\r\n		\$v=\$res;\r\n	}\r\n	return\$co[''value''];\r\n}\r\nelse\r\n{\r\n	\$v=str_replace("\\r",'''',\$co[''value'']);\r\n	\$res=array();\r\n	foreach(explode("\\n",\$v) as \$bot)\r\n		if(strpos(\$bot,''='')!==false)\r\n		{\r\n			list(\$uagent,\$name)=explode(''='',\$bot,2);\r\n			\$res[\$uagent]=\$name;\r\n		}\r\n	return \$res;\r\n}'),
(12, 1, 'check', 'multilang', 1, 12, 0, 'if(count(Eleanor::\$langs)==1)\\n	if(\$co[''multilang''])\\n	{\\n		\$co[''options''][''extra''][''disabled'']=''disabled'';\\n		foreach(\$co[''value''] as &\$v)\\n			\$v=0;\\n	}\\n	else\\n	{\\n		\$co[''value'']=0;\\n		\$co[''options''][''extra''][''disabled'']=''disabled'';\\n	}\\nreturn\$co;', ''),
(13, 1, 'select', 'time_zone', 1, 13, 0, '', ''),
(14, 1, 'text', 'blocked_ips', 1, 14, 0, '', ''),
(15, 1, 'input', 'blocked_message', 1, 15, 1, '', ''),

(16, 2, 'input', 'site_name', 1, 1, 1, '', ''),
(17, 2, 'input', 'site_defis', 1, 2, 1, '', ''),
(18, 2, 'text', 'site_description', 1, 3, 1, '', ''),
(19, 2, 'check', 'furl', 1, 4, 0, '', ''),
(20, 2, 'check', 'trans_uri', 1, 5, 0, '', ''),
(21, 2, 'input', 'url_static_delimiter', 1, 6, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$v);\\n		if(!\$v)\\n			\$v=''/'';\\n	}\\nelse\\n{\\n	\$co[''value'']=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$co[''value'']);\\n	if(!\$co[''value''])\\n		\$co[''value'']=''/'';\\n}\\n	return\$co[''value''];'),
(22, 2, 'input', 'url_static_defis', 1, 7, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$v);\\n		if(!\$v)\\n			\$v=''_'';\\n	}\\nelse\\n{\\n	\$co[''value'']=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$co[''value'']);\\n	if(!\$co[''value''])\\n		\$co[''value'']=''_'';\\n}\\nreturn\$co[''value''];'),
(23, 2, 'input', 'url_static_ending', 1, 8, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		if(preg_match(''/^[^a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']/i'',\$v)==0)\\n			\$v=''.''.\$v;\\n}\\nelse\\n{\\n	if(preg_match(''/^[^a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']/i'',\$co[''value''])==0)\\n		\$co[''value'']=''.''.\$co[''value''];\\n}\\nreturn\$co[''value''];'),
(24, 2, 'input', 'url_rep_space', 1, 9, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$v);\\n		if(!\$v)\\n			\$v=''-'';\\n	}\\nelse\\n{\\n	\$co[''value'']=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$co[''value'']);\\n	if(!\$co[''value''])\\n		\$co[''value'']=''-'';\\n}\\nreturn\$co[''value''];'),
(25, 2, 'select', 'prefix_free_module', 1, 10, 0, '', ''),
(26, 2, 'check', 'site_closed', 1, 11, 0, '', ''),
(27, 2, 'editor', 'site_close_mes', 1, 12, 1, '', ''),
(28, 2, 'select', 'show_status', 1, 13, 0, '', ''),
(29, 2, 'items', 'templates', 1, 14, 0, '', ''),

(30, 3, 'input', 'link_options', 1, 1, 1, '', ''),
(31, 3, 'input', 'link_register', 1, 2, 1, '', ''),
(32, 3, 'input', 'link_passlost', 1, 3, 1, '', ''),
(33, 3, 'user', 'time_online', 1, 4, 0, '', ''),
(34, 3, 'select', 'antibrute', 1, 5, 0, '', ''),
(35, 3, 'input', 'antibrute_cnt', 1, 6, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(36, 3, 'input', 'antibrute_time', 1, 7, 0, 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=round(\$v/60);\\nelse\\n	\$co[''value'']=round(\$co[''value'']/60);\\nreturn\$co;', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v*60);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']*60);'),

(37, 4, 'text', 'blocked_names', 1, 1, 0, '', ''),
(38, 4, 'text', 'blocked_emails', 1, 2, 0, '', ''),
(39, 4, 'select', 'reg_type', 1, 3, 0, '', ''),
(40, 4, 'input', 'reg_act_time', 1, 4, 0, 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs(round((int)\$v/3600));\\n}\\nelse\\n	\$co[''value'']=abs(round((int)\$co[''value'']/3600));\\nreturn\$co;', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v)*3600;\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']*3600);'),
(41, 4, 'select', 'reg_unactivated', 1, 5, 0, '', ''),
(42, 4, 'check', 'reg_off', 1, 6, 0, '', ''),
(43, 4, 'input', 'max_name_length', 1, 7, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=abs((int)\$v);\\n		if(\$v<5)\\n			\$v=5;\\n	}\\nelse\\n{\\n	\$co[''value'']=abs((int)\$co[''value'']);\\n	if(\$co[''value'']<5)\\n		\$co[''value'']=5;\\n}\\nreturn\$co[''value''];'),
(44, 4, 'input', 'min_pass_length', 1, 8, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(45, 4, 'input', 'avatar_bytes', 1, 9, 0, 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs(round((int)\$v/1024));\\n}\\nelse\\n	\$co[''value'']=abs(round((int)\$co[''value'']/1024));\\nreturn\$co;', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v)*1024;\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']*1024);'),
(46, 4, 'input', 'avatar_size', 1, 10, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		if(preg_match(''#^\\\\d+ \\\\d+\$#'',\$v)==0)\\n			throw new EE(''incorrect_format'',EE::USER,array(''lang''=>true));\\n}\\nelseif(preg_match(''#^\\\\d+ \\\\d+\$#'',\$co[''value''])==0)\\n	throw new EE(''incorrect_format'',EE::USER,array(''lang''=>true));\\nreturn\$co[''value''];'),
(47, 4, 'select', 'account_pass_rec_t', 1, 11, 0, '', ''),

(48, 5, 'input', 'captcha_length', 1, 1, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(49, 5, 'input', 'captcha_symbols', 1, 2, 0, '', ''),
(50, 5, 'input', 'captcha_width', 1, 3, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(51, 5, 'input', 'captcha_height', 1, 4, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(52, 5, 'input', 'captcha_fluctuation', 1, 5, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),

(53, 6, 'select', 'mail_method', 1, 1, 0, '', ''),
(54, 6, 'input', 'mail_from', 1, 2, 0, '', 'if(!Strings::CheckEmail(\$co[''value'']))\\n	throw new EE(''incorrect_email'',EE::USER,array(''lang''=>true));\\nreturn\$co[''value''];'),
(55, 6, 'select', 'mail_priority', 1, 3, 0, '', ''),
(56, 6, 'input', 'mail_reply', 1, 4, 0, '', 'if(!Strings::CheckEmail(\$co[''value''],false))\\n	throw new EE(''incorrect_email'',EE::USER,array(''lang''=>true));\\nreturn\$co[''value''];'),
(57, 6, 'input', 'mail_notice', 1, 5, 0, '', 'if(!Strings::CheckEmail(\$co[''value''],false))\\n	throw new EE(''incorrect_email'',EE::USER,array(''lang''=>true));\\nreturn\$co[''value''];'),
(58, 6, 'input', 'mail_smtp_user', 1, 6, 0, '', ''),
(59, 6, 'input', 'mail_smtp_pass', 1, 7, 0, '', ''),
(60, 6, 'input', 'mail_smtp_host', 1, 8, 0, '', ''),
(61, 6, 'input', 'mail_smtp_port', 1, 9, 0, '', ''),

(62, 7, 'user', 'errors_code_users', 1, 1, 0, '', ''),
(63, 7, 'input', 'errors_code_title', 1, 2, 1, '', ''),
(64, 7, 'editor', 'errors_code_text', 1, 3, 1, '', ''),
(65, 7, 'user', 'errors_db_users', 1, 4, 0, '', ''),
(66, 7, 'input', 'errors_db_title', 1, 5, 1, '', ''),
(67, 7, 'editor', 'errors_db_text', 1, 6, 1, '', ''),
(68, 7, 'user', 'errors_requests_users', 1, 7, 0, '', ''),
(69, 7, 'input', 'errors_requests_title', 1, 8, 1, '', ''),
(70, 7, 'editor', 'errors_requests_text', 1, 9, 1, '', ''),

(71, 8, 'select', 'editor_type', 1, 1, 0, '', ''),
(72, 8, 'text', 'bad_words', 1, 2, 0, '', ''),
(73, 8, 'input', 'bad_words_replace', 1, 3, 1, '', ''),
(74, 8, 'select', 'antidirectlink', 1, 4, 0, '', ''),
(75, 8, 'check', 'autoparse_urls', 1, 5, 0, '', ''),

(76, 9, 'uploadimage', 'rss_image', 1, 1, 0, '', ''),

(77, 10, 'select', 'comments_sort', 1, 1, 0, '', ''),
(78, 10, 'input', 'comments_pp', 1, 2, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=abs((int)\$v);\\n		if(\$v==0)\\n			\$v=10;\\n	}\\nelse\\n{\\n	\$co[''value'']=abs((int)\$co[''value'']);\\n	if(\$co[''value'']==0)\\n		\$co[''value'']=10;\\n}\\nreturn\$co[''value''];'),
(79, 10, 'input', 'comments_timelimit', 1, 3, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(80, 10, 'items', 'comments_display_for', 1, 5, 0, '', ''),
(81, 10, 'items', 'comments_post_for', 1, 6, 0, '', ''),

(82, 11, 'check', 'thumbs', 1, 1, 0, '', ''),
(83, 11, 'input', 'thumb_types', 1, 2, 0, '', ''),
(84, 11, 'input', 'thumb_width', 1, 3, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n}\\nelse\\n	\$co[''value'']=abs((int)\$co[''value'']);\\nreturn\$co[''value''];'),
(85, 11, 'input', 'thumb_height', 1, 4, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n}\\nelse\\n	\$co[''value'']=abs((int)\$co[''value'']);\\nreturn\$co[''value''];'),
(86, 11, 'select', 'thumb_reducing', 1, 5, 0, '', ''),
(87, 11, 'select', 'thumb_first', 1, 6, 0, '', ''),
(88, 11, 'check', 'watermark', 1, 7, 0, '', ''),
(89, 11, 'input', 'watermark_types', 1, 8, 0, '', ''),
(90, 11, 'input', 'watermark_alpha', 1, 9, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=abs((int)\$v);\\n		if(\$v>100)\\n			\$v=100;\\n	}\\n}\\nelse\\n{\\n	\$co[''value'']=abs((int)\$co[''value'']);\\n	if(\$co[''value'']>100)\\n		\$co[''value'']=100;\\n}\\nreturn\$co[''value''];'),
(91, 11, 'input', 'watermark_top', 1, 10, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=abs((int)\$v);\\n		if(\$v>100)\\n			\$v=100;\\n	}\\n}\\nelse\\n{\\n	\$co[''value'']=abs((int)\$co[''value'']);\\n	if(\$co[''value'']>100)\\n		\$co[''value'']=100;\\n}\\nreturn\$co[''value''];'),
(92, 11, 'input', 'watermark_left', 1, 11, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n	{\\n		\$v=abs((int)\$v);\\n		if(\$v>100)\\n			\$v=100;\\n	}\\n}\\nelse\\n{\\n	\$co[''value'']=abs((int)\$co[''value'']);\\n	if(\$co[''value'']>100)\\n		\$co[''value'']=100;\\n}\\nreturn\$co[''value''];'),
(93, 11, 'input', 'watermark_image', 1, 12, 0, '', ''),
(94, 11, 'input', 'watermark_string', 1, 13, 1, '', ''),
(95, 11, 'input', 'watermark_csa', 1, 14, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		if(preg_match(''#^\\\\d+,\\\\d+,\\\\d+,\\\\d+,\\\\d+\$#'',\$v)==0)\\n			throw new EE(''incorrect_format'',EE::USER,array(''lang''=>true));\\n}\\nelse\\n{\\n	if(preg_match(''#^\\\\d+,\\\\d+,\\\\d+,\\\\d+,\\\\d+\$#'',\$co[''value''])==0)\\n		throw new EE(''incorrect_format'',EE::USER,array(''lang''=>true));\\n}\\nreturn\$co[''value''];'),
(96, 11, 'check', 'download_antileech', 1, 15, 0, '', ''),
(97, 11, 'check', 'download_no_session', 1, 16, 0, '', ''),

(98, 12, 'input', 'multisite_secret', 0, 1, 0, '', ''),
(99, 12, 'input', 'multisite_ttl', 0, 2, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),

(100, 13, 'input', 'drafts_days', 0, 1, 0, '', 'if(\$co[''multilang''])\r\n{\r\n	foreach(\$co[''value''] as &\$v)\r\n		\$v=(int)\$v;\r\n	return\$co[''value''];\r\n}\r\nreturn(int)\$co[''value''];'),
(101, 13, 'input', 'drafts_autosave', 0, 2, 0, '', 'if(\$co[''multilang''])\r\n{\r\n	foreach(\$co[''value''] as &\$v)\r\n		\$v=(int)\$v;\r\n	return\$co[''value''];\r\n}\r\nreturn(int)\$co[''value''];'),

(102, 14, 'user', 'm_static_general', 1, 1, 0, '', ''),

(103, 15, 'input', 'publ_per_page', 0, 1, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(104, 15, 'input', 'publ_rss_per_page', 0, 3, 0, '', 'if(\$co[''multilang''])\\n{\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=abs((int)\$v);\\n	return\$co[''value''];\\n}\\nreturn abs((int)\$co[''value'']);'),
(105, 15, 'check', 'publ_add', 0, 4, 0, '', ''),
(106, 15, 'check', 'publ_catsubcat', 0, 5, 0, '', ''),
(107, 15, 'check', 'publ_ping', 0, 6, 0, '', ''),
(108, 15, 'check', 'publ_rating', 0, 7, 0, '', ''),
(109, 15, 'check', 'publ_mark_details', 0, 8, 0, '', ''),
(110, 15, 'check', 'publ_mark_users', 0, 9, 0, '', ''),
(111, 15, 'input', 'publ_remark', 0, 10, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=max((int)\$v,1);\\nelse\\n	\$co[''value'']=max((int)\$co[''value''],1);\\nreturn\$co[''value''];'),
(112, 15, 'input', 'publ_lowmark', 0, 11, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=min((int)\$v,0);\\nelse\\n	\$co[''value'']=min((int)\$co[''value''],0);\\nreturn\$co[''value''];'),
(113, 15, 'input', 'publ_highmark', 0, 12, 0, '', 'if(\$co[''multilang''])\\n	foreach(\$co[''value''] as &\$v)\\n		\$v=max((int)\$v,0);\\nelse\\n	\$co[''value'']=max((int)\$co[''value''],0);\\nreturn\$co[''value''];')
QUERY;

$multilang=count($languages)>1;
if($furl)
{
	$p=Language::$main=='russian' ? '' : '%D1%80%D1%83%D1%81/';
	$ac_a_r=$p.'%D0%B0%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82.html';
	$ac_r_r=$p.'%D0%B0%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82/register';
	$ac_p_r=$p.'%D0%B0%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82/lostpass';

	$p=Language::$main=='ukrainian' ? '' : '%D1%83%D0%BA%D1%80/';
	$ac_a_u=$p.'%D0%B0%D0%BA%D0%B0%D1%83%D0%BD%D1%82.html';
	$ac_r_u=$p.'%D0%B0%D0%BA%D0%B0%D1%83%D0%BD%D1%82/register';
	$ac_p_u=$p.'%D0%B0%D0%BA%D0%B0%D1%83%D0%BD%D1%82/lostpass';

	$p=Language::$main=='english' ? '' : 'eng/';
	$ac_a_e=$p.'account.html';
	$ac_r_e=$p.'account/register';
	$ac_p_e=$p.'account/lostpass';

	$e_403='index.php?module=errors&code=403';
}
else
{
	$ac_a='index.php?module=account';
	$ac_r='index.php?module=account&amp;do=register';
	$ac_p='index.php?module=account&amp;do=lostpass';

	$ac=urlencode('аккаунт');
	$p=Language::$main=='russian' ? '' : 'lang=%F0%F3%F1&amp;';
	$ac_a_r='index.php?'.$p.'module='.$ac;
	$ac_r_r='index.php?'.$p.'module='.$ac.'&amp;do=register';
	$ac_p_r='index.php?'.$p.'module='.$ac.'&amp;do=lostpass';

	$p=Language::$main=='ukrainian' ? '' : 'lang=%F3%EA%F0&amp;';
	$ac_a_u='index.php?'.$p.'module='.$ac;
	$ac_r_u='index.php?'.$p.'module='.$ac.'&amp;do=register';
	$ac_p_u='index.php?'.$p.'module='.$ac.'&amp;do=lostpass';

	$p=Language::$main=='english' ? '' : 'lang=eng&amp;';
	$ac_a_e='index.php?'.$p.'module=account';
	$ac_r_e='index.php?'.$p.'module=account&amp;do=register';
	$ac_p_e='index.php?'.$p.'module=account&amp;do=lostpass';
	$e_403='errors/403.html';
}

$ser=array(
	'errors'=>'include Eleanor::$root.\'\'addons/admin/options/errors_users.php\'\';',
	'groups'=>'include Eleanor::$root.\'\'addons/admin/options/groups.php\'\';',
	'bots'=>serialize(array(
		'googlebot'=>'Google Bot',
		'archive_org'=>'Archive.org',
		'YandexMetrika'=>'Yandex.Metrika',
		'Yandex'=>'Yandex',
		'rambler'=>'Rambler',
		'W3C_CSS_Validator'=>'W3C CSS Validator',
		'W3C_Validator'=>'W3C Validator',
	)),
	'templates'=>'include Eleanor::$root.\'\'addons/admin/options/templates.php\'\'',
	'tz'=>'include Eleanor::$root.\'\'addons/admin/options/tz.php\'\'',
	'lfm'=>'include Eleanor::$root.\'\'addons/admin/options/lfm.php\'\'',
	'time_online'=>'include Eleanor::$root.\'\'addons/admin/options/time_online.php\'\'',
	'sg'=>'include Eleanor::$root.\'\'modules/static/optionsg.php\'\'',
);

#Russian
$ser=array(
	'pd'=>'array(
		\'\'options\'\'=>array(
			\'\'ignore\'\'=>\'\'Игнорировать\'\',
			\'\'redirect\'\'=>\'\'Перенаправить все ссылки на основной домен\'\',
			\'\'rel\'\'=>\'\'Добавить rel canonical\'\',
		),
	)',
	'pg'=>'array(
		\'\'options\'\'=>array(\'\'Скрыть\'\',\'\'Отображать только администраторам\'\',\'\'Отображать всем\'\'),
	)',
	'ab'=>'array(
		\'\'options\'\'=>array(\'\'Отключена\'\',\'\'Лимит попыток за определенное время\'\',\'\'Отображать капчу после исчерпания лимита попыток входа\'\'),
	)',
	'pr'=>'array(
		\'\'options\'\'=>array(1=>\'\'Высочайшая\'\',\'\'Высокая\'\',\'\'Обычная\'\',\'\'Низкая\'\',\'\'Низшая\'\'),
	)',
	'aa'=>'array(
		\'\'options\'\'=>array(1=>\'\'Не требуется\'\',\'\'Через e-mail\'\',\'\'Вручную администратором\'\'),
	)',
	'ua'=>'array(
		\'\'options\'\'=>array(1=>\'\'Удалить\'\',\'\'Ничего не делать\'\'),
	)',
	'rp'=>'array(
		\'\'options\'\'=>array(\'\'Запрещено\'\',\'\'Позволить ввести новый пароль\'\',\'\'Сгенерировать и выслать пароль на e-mail\'\'),
	);',
	'or'=>'array(
		\'\'options\'\'=>array(1=>\'\'Обратный\'\',\'\'Прямой\'\'),
	)',
	'cu'=>'array(
		\'\'options\'\'=>array(
			\'\'cut\'\'=>\'\'Обрезать\'\',
			\'\'small\'\'=>\'\'Уменьшить\'\',
			\'\'cutsmall\'\'=>\'\'Обрезать и уменьшить\'\',
			\'\'smallcut\'\'=>\'\'Уменьшить и обрезать\'\',
		),
	)',
	'cf'=>'array(
		\'\'options\'\'=>array(
			\'\'w\'\'=>\'\'Ширины\'\',
			\'\'h\'\'=>\'\'Высоты\'\',
			\'\'b\'\'=>\'\'Наибольшей стороны\'\',
			\'\'s\'\'=>\'\'Наименьшей стороны\'\',
		),
	)',
)+$ser;
$secret=uniqid();

if($rus)
	$insert['config_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}config_l` (`id`,`language`,`title`,`descr`,`value`,`serialized`,`default`,`extra`,`startgroup`) VALUES
(1, 'russian', 'Основной домен', 'Вводится без приставки http://', '{$domain}', 0, '{$domain}', '', 'Домен'),
(2, 'russian', 'Поддержка паркованных доменов', 'При запуске сайта на паркованном домене', 'redirect', 0, 'redirect', '{$ser['pd']}', ''),
(3, 'russian', 'Кеширование страниц браузером', 'Введите стандартный срок кеширования страницы в минутах. 0 - отключить кеширование.', '600', 0, '600', '', 'Оптимизация нагрузки'),
(4, 'russian', 'Включать GZIP сжатие?', 'Включение этой опции позволит сэкономить трафик', '1', 0, '1', '', ''),
(5, 'russian', 'Срок хранения cookie (в днях)', '', '31536000', 0, '31536000', '', 'Cookies'),
(6, 'russian', 'Домен cookie', 'Используйте .example.com для глобальных cookie. Обратите внимание на точку перед именем домена. Вместо "example.com" используейте ваше доменное имя. Если включена поддержка паркованных домено, вместо имени домена используйте *.', '.*', 0, '.*', '', ''),
(7, 'russian', 'Префикс сookie', 'Данная опция позволяет избежать конфликтов, если на домене кроме системы расположены и другие скрипты, использующие cookies', 'el', 0, 'el', '', ''),
(8, 'russian', 'Группа гостей', 'Наделить гостей правами группы...', 'a:1:{i:0;s:1:"3";}', 1, 'a:1:{i:0;s:1:"3";}', '{$ser['groups']}', 'Права по умолчанию'),
(9, 'russian', 'Отслеживать поисковых ботов?', '', '1', 0, '1', '', ''),
(10, 'russian', 'Группа поисковых ботов', 'Наделить поисковых ботов правами группы...', 'a:1:{i:0;s:1:"4";}', 1, 'a:1:{i:0;s:1:"4";}', '{$ser['groups']}', ''),
(11, 'russian', 'Список поисковых ботов', 'Здесь хранятся данные о поисковых ботах. Формат ввода: по одному с каждой строки в виде <b>user agent=имя бота</b>.', '{$ser['bots']}', 1, '{$ser['bots']}', '', ''),
(12, 'russian', 'Включить многоязыковую поддержку?', '', '{$multilang}', 0, '{$multilang}', '', 'Локализация'),
(13, 'russian', 'Часовой пояс по-умолчанию', '', '{$timezone}', 0, '{$timezone}', '{$ser['tz']}', ''),
(14, 'russian', 'Заблокированные IP адреса', 'Каждый адрес — с новой строки. Допускаются маски вида 87.183.*.*, а так же диапазоны вида 79.224.60.1-79.224.60.255 с поддержкой масок. Чтобы указать уникальную причину бана после введённого IP адреса, поставьте = и напишите причину. Например: 87.183.*.*=Отбросам здесь не место!', '', 0, '', 'array(''extra''=>array(''style''=>''word-wrap:normal''));', 'Бан по IP'),
(15, 'russian', 'Сообщение для заблокированных', 'Это сообщение увидят пользователи, для которых не введена причина.', ':-p', 0, ':-p', '', ''),

(16, 'russian', 'Название сайта', '', '{$sitename}', 0, '{$sitename}', '', 'Заголовки сайта'),
(17, 'russian', 'Разделитель заголовков', '', ' - ', 0, ' - ', '', ''),
(18, 'russian', 'Описание сайта', '', 'Сайт построен на системе управления сайтом Eleanor', 0, 'Сайт построен на системе управления сайтом Eleanor', '', ''),
(19, 'russian', 'Включить статические ссылки?', '', '{$furl}', 0, '{$furl}', '', 'Настройка ссылок'),
(20, 'russian', 'Транслитерировать статические ссылки?', 'Статические ссылки контента, а также имена загружаемых файлов будут автоматически транслитерированы.', '0', 0, '0', '', ''),
(21, 'russian', 'Разделитель параметров', 'В ссылке <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q> разделителем параметров является косая черта / (слеш).\r\nДля ввода допускаются любые не буквенные символы.', '/', 0, '/', '', ''),
(22, 'russian', 'Разделитель значений', 'В ссылке <q>news/category/news/page<b>_</b>1.html</q> разделителем значений является нижний прочерк _.\r\nДля ввода допускаются любые не буквенные символы.', '_', 0, '_', '', ''),
(23, 'russian', 'Окончание статических ссылок', 'В ссылке <q>news/category/news/page_1<b>.html</b></q> окончанием является <q>.html</q>.\r\nОбратите внимание, что начинаться это поле должно с не буквенного символа!', '.html', 0, '.html', '', ''),
(24, 'russian', 'Автозамена недопустимых символов в статичных ссылках', 'Значение не должно совпадать ни с разделителем параметров, ни с разделителем значений, ни с окончанием статических ссылок и так же должно начинаться с не буквенного символа.', '-', 0, '-', '', ''),
(25, 'russian', 'Модуль без префикса', 'Ссылки этого модуля будут работать без указания префикса-идентификатора модуля', '2', 0, '2', '{$ser['lfm']}', ''),
(26, 'russian', 'Выключить сайт?', 'Сайт будет доступен только группам, для которых включена опция просмотра закрытого сайта', '0', 0, '0', '', 'Выключение сайта'),
(27, 'russian', 'Причина выключения сайта', '', '', 0, '', 'array(''type''=>-1)', ''),
(28, 'russian', 'Информация о генерации страницы', 'Информация внизу страницы, содержащая скорость генерации страницы, количество использованных запросов в базу данных, статус GZIP сжатия и количество затраченной памяти.', '2', 0, '2', '{$ser['pg']}', 'Дополнительная информация'),
(29, 'russian', 'Шаблоны на выбор', 'Укажите шаблоны, которые пользователи смогут выбирать в качестве оформления сайта.', 'a:1:{i:0;s:5:"Uniel";}', 1, 'a:1:{i:0;s:5:"Uniel";}', '{$ser['templates']}', 'Разное'),

(30, 'russian', 'Ссылка личного кабинета', 'Обратите внимание, что запись <q>param1=value1<b>&amp;</b>param2=value2</q> некорректна. Корректная запись: <q>param1=value1<b>&amp;amp;</b>param2=value2</q>', '{$ac_a_r}', 0, '{$ac_a_r}', '', 'Ссылки'),
(31, 'russian', 'Ссылка на регистрацию', 'Обратите внимание, что запись <q>param1=value1<b>&amp;</b>param2=value2</q> некорректна. Корректная запись: <q>param1=value1<b>&amp;amp;</b>param2=value2</q>', '{$ac_r_r}', 0, '{$ac_r_r}', '', ''),
(32, 'russian', 'Ссылка на восстановление пароля', 'Обратите внимание, что запись <q>param1=value1<b>&amp;</b>param2=value2</q> некорректна. Корректная запись: <q>param1=value1<b>&amp;amp;</b>param2=value2</q>', '{$ac_p_r}', 0, '{$ac_p_r}', '', ''),
(33, 'russian', 'Длительность сессий', 'Количество секунд во время которых пользователь считается онлайн после проявления активности.', 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', 1, 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', '{$ser['time_online']}', 'Параметры аутентификации и авторизации'),
(34, 'russian', 'Тип защиты', '', '1', 0, '1', '{$ser['ab']}', 'Защита от подбора пароля'),
(35, 'russian', 'Максимальное число неудачных попыток аутентификации', '', '5', 0, '5', '', ''),
(36, 'russian', 'Максимальный промежуток времени в минутах, во время которого допускаются неудачные попытки аутентификации', 'Пример: если максимальное число неудачных попыток аутентификации равно 5, а заданное значение равно 15, то пользователь будет заблокирован в случае если за последние 15 минут было 5 неудачных попыток входа.', '600', 0, '600', '', ''),

(37, 'russian', 'Заблокированные ники', 'Через запятую. Допустимые спецсимволы: * - любая последовательность символов, ? - любой один символ.', '', 0, '', '', 'Блокировки'),
(38, 'russian', 'Заблокированные e-mail', 'Через запятую. Допустимые спецсимволы: * - любая последовательность символов, ? - любой один символ.', '', 0, '', '', ''),
(39, 'russian', 'Активация новосозданного пользователя', '', '1', 0, '1', '{$ser['aa']}', 'Регистрация'),
(40, 'russian', 'Срок активации', 'Количество часов отведенных для активации учетной записи', '86400', 0, '86400', 'array(''type''=>''number'',''extra''=>array(''min''=>1));', ''),
(41, 'russian', 'Как поступать с неактивированными учетными записями', '', '1', 0, '1', '{$ser['ua']}', ''),
(42, 'russian', 'Отключить регистрацию?', '', '0', 0, '0', '', ''),
(43, 'russian', 'Максимальная длина ника', '', '15', 0, '15', '', ''),
(44, 'russian', 'Минимальная длина пароля', '', '7', 0, '7', '', ''),
(45, 'russian', 'Максимальный размер загружаемого аватара в KB', '', '307200', 0, '307200', '', 'Профиль'),
(46, 'russian', 'Максимальный размер аватара', 'Вводить нужно в формате: ширина[пробел]высота.', '100 100', 0, '100 100', '', ''),
(47, 'russian', 'Восстановление пароля', '', '1', 0, '1', '{$ser['rp']}', ''),

(48, 'russian', 'Количество символов в captcha', '', '5', 0, '5', '', 'Настройки captcha'),
(49, 'russian', 'Алфавит captcha', 'Символы, используемые в captcha (желательно исключить похожие символы, как 0— цифра и o — буква)', '23456789abcdeghkmnpqsuvxyz', 0, '23456789abcdeghkmnpqsuvxyz', '', ''),
(50, 'russian', 'Ширина captcha', '', '120', 0, '120', '', ''),
(51, 'russian', 'Высота captcha', '', '60', 0, '60', '', ''),
(52, 'russian', 'Разброс символов по вертикали', 'Максимальное отклонение символов по вертикали от центра.', '5', 0, '5', '', ''),

(53, 'russian', 'Способ отправки e-mail', '', 'mail', 0, 'mail', 'array(''options''=>array(''php''=>''PHP mail'',''smtp''=>''SMTP'',))', 'Общие настройки'),
(54, 'russian', 'E-mail отправителя', '', '{$email}', 0, '{$email}', '', ''),
(55, 'russian', 'Важность', '', '3', 0, '3', '{$ser['pr']}', ''),
(56, 'russian', 'E-mail для ответа', 'В случае, если ответы от пользователей необходимо принимать на другой e-mail, заполните это поле.', '', 0, '', '', ''),
(57, 'russian', 'E-mail для подтверждения о прочтении', '', '', 0, '', '', ''),
(58, 'russian', 'Логин', 'Пользователь', '', 0, '', '', 'Настройки SMTP'),
(59, 'russian', 'Пароль', '', '', 0, '', '', ''),
(60, 'russian', 'Хост', 'Сервер', '', 0, '', '', ''),
(61, 'russian', 'Порт', '', '25', 0, '25', '', ''),

(62, 'russian', 'Ответственные пользователи', 'Введите имена пользователей, разделяя их запятыми', 1, 0, 1, '{$ser['errors']}', 'Ошибки в коде'),
(63, 'russian', 'Тема письма', '{site} - название сайта<br />{link} - ссылка на сайт<br />{full} - ссылка в админку на просмотр всего лога<br />{cnt} - число ошибок<br />{errors} - лог ошибок<br />{name} - имя пользователя', 'Новые ошибки на {site}', 0, 'Новые ошибки на {site}', 'array(''checkout''=>false)', ''),
(64, 'russian', 'Текст письма', '{site} - название сайта<br />{link} - ссылка на сайт<br />{full} - ссылка в админку на просмотр всего лога<br />{cnt} - число ошибок<br />{errors} - лог ошибок<br />{name} - имя пользователя', 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайте "{site}" [cnt=plural]произошла {cnt} ошибка|произошли {cnt} ошибки|произошло {cnt} ошибок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендуется срочно их исправить. <br />\\n<br />\\nС наилучшими пожеланиями,<br />\\nкоманда сайта {site} .', 0, 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайте "{site}" [cnt=plural]произошла {cnt} ошибка|произошли {cnt} ошибки|произошло {cnt} ошибок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендуется срочно их исправить. <br />\\n<br />\\nС наилучшими пожеланиями,<br />\\nкоманда сайта {site} .', 'array(''checkout''=>false)', ''),
(65, 'russian', 'Ответственные пользователи', 'Введите имена пользователей, разделяя их запятыми', 1, 0, 1, '{$ser['errors']}', 'Ошибки базы данных'),
(66, 'russian', 'Тема письма', '{site} - название сайта<br />{link} - ссылка на сайт<br />{full} - ссылка в админку на просмотр всего лога<br />{cnt} - число ошибок<br />{errors} - лог ошибок<br />{name} - имя пользователя', 'Новые ошибки на {site}', 0, 'Новые ошибки на {site}', 'array(''checkout''=>false)', ''),
(67, 'russian', 'Текст письма', '{site} - название сайта<br />{link} - ссылка на сайт<br />{full} - ссылка в админку на просмотр всего лога<br />{cnt} - число ошибок<br />{errors} - лог ошибок<br />{name} - имя пользователя', 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайте "{site}" [cnt=plural]произошла {cnt} ошибка|произошли {cnt} ошибки|произошло {cnt} ошибок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендуется срочно их исправить. <br />\\n<br />\\nС наилучшими пожеланиями,<br />\\nкоманда сайта {site} .', 0, 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайте "{site}" [cnt=plural]произошла {cnt} ошибка|произошли {cnt} ошибки|произошло {cnt} ошибок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендуется срочно их исправить. <br />\\n<br />\\nС наилучшими пожеланиями,<br />\\nкоманда сайта {site} .', 'array(''checkout''=>false)', ''),
(68, 'russian', 'Ответственные пользователи', 'Введите имена пользователей, разделяя их запятыми', 1, 0, 1, '{$ser['errors']}', 'Ошибочные запросы'),
(69, 'russian', 'Тема письма', '{site} - название сайта<br />{link} - ссылка на сайт<br />{full} - ссылка в админку на просмотр всего лога<br />{cnt} - число ошибок<br />{errors} - лог ошибок<br />{name} - имя пользователя', 'Новые ошибки на {site}', 0, 'Новые ошибки на {site}', 'array(''checkout''=>false)', ''),
(70, 'russian', 'Текст письма', '{site} - название сайта<br />{link} - ссылка на сайт<br />{full} - ссылка в админку на просмотр всего лога<br />{cnt} - число ошибок<br />{errors} - лог ошибок<br />{name} - имя пользователя', 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайте "{site}" [cnt=plural]произошла {cnt} ошибка|произошли {cnt} ошибки|произошло {cnt} ошибок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендуется срочно их исправить. <br />\\n<br />\\nС наилучшими пожеланиями,<br />\\nкоманда сайта {site} .', 0, 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайте "{site}" [cnt=plural]произошла {cnt} ошибка|произошли {cnt} ошибки|произошло {cnt} ошибок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендуется срочно их исправить. <br />\\n<br />\\nС наилучшими пожеланиями,<br />\\nкоманда сайта {site} .', 'array(''checkout''=>false)', ''),

(71, 'russian', 'Редактор по умолчанию', '', 'bb', 0, 'bb', 'array(''eval''=>''return Eleanor::getInstance()->Editor->editors;'')', ''),
(72, 'russian', 'Запрещенные слова', 'Маты и ругательства. Через запятую.', 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', 0, 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', '', ''),
(73, 'russian', 'Автозамена запрещенных слов', '', '*Цензура*', 0, '*Цензура*', '', ''),
(74, 'russian', 'Включить защиту от прямых ссылок?', '', 'go', 0, 'go', 'array(''options''=>array(''нет'',''go''=>''Редирект через go.php'',''nofollow''=>''rel="nofollow"'',))', ''),
(75, 'russian', 'Включить автоопределение ссылок в тексте?', 'При включении этой опции, все ссылки опубликованные как текст - будут обработаны как ссылки.', '1', 0, '1', '', ''),

(76, 'russian', 'Логотип RSS', '', 'images/rss.png', 0, 'images/rss.png', 'array(''path''=>''uploads/'',''types''=>array(0=>''jpeg'',1=>''jpg'',2=>''png'',3=>''bmp'',4=>''gif'',),''max_size''=>''307200'',''filename_eval''=>''return \\\\''rss\\\\''.strrchr(\$a[\\\\''filename\\\\''],\\\\''.\\\\'');'',)', ''),

(77, 'russian', 'Порядок сортировки комментариев', '', '1', 0, '1', '{$ser['or']}', ''),
(78, 'russian', 'Комментариев на страницу', '', '10', 0, '10', '', ''),
(79, 'russian', 'Ограничение изменения по времени', 'Введите количество секунд, по истечению которых пользователи не смогут удалять / править свои комментарии. Отсчет времени осуществляется с момента публикации комментария.', '86400', 0, '86400', '', ''),
(80, 'russian', 'Отображать комментарии для', '', 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', 1, 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', '{$ser['groups']}', 'Права'),
(81, 'russian', 'Публикация комментариев доступна для', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '{$ser['groups']}', ''),

(82, 'russian', 'Включить создание превью для загружаемых изображений?', '', '1', 0, '1', '', 'Превью изображений'),
(83, 'russian', 'Типы файлов для которых создавать превью', '', 'png,jpg,bmp', 0, 'png,jpg,bmp', '', ''),
(84, 'russian', 'Ширина превью', 'Введите 0 для сохранения исходной ширины изображения', '200', 0, '200', '', ''),
(85, 'russian', 'Высота превью', 'Введите 0 для сохранения исходной высоты изображения', '0', 0, '0', '', ''),
(86, 'russian', 'Способ уменьшения изображения', '', 'small', 0, 'small', '{$ser['cu']}', ''),
(87, 'russian', 'Создание превью начать с', '', 'b', 0, 'b', '{$ser['cf']}', ''),
(88, 'russian', 'Включить водяной знак?', 'Наложить водяной знак на загружаемые картинки?', '1', 0, '1', '', 'Настройки водяного знака'),
(89, 'russian', 'Типы файлов для водяного знака', 'На эти типы файлов будет ставиться водяной знак. Указывайте, разделяя запятыми.', 'jpg,jpeg,png,bmp', 0, 'jpg,jpeg,png,bmp', '', ''),
(90, 'russian', 'Прозрачность водяного знака (в процентах от 0 до 100)', '100 - не видно водяного знака', '50', 0, '50', '', ''),
(91, 'russian', 'Положение по вертикали (в процентах от 0 до 100)', '', '50', 0, '50', '', ''),
(92, 'russian', 'Положение по горизонтали (в процентах от 0 до 100)', '', '50', 0, '50', '', ''),
(93, 'russian', 'Файл водяного знака', 'Введите путь к рисунку на сервере, который будет использован в качества водяного знака (например: images/watermrak.jpg). Обратите внимание, что водяной знак не будет применён на изображение если по размерам оно меньше, чем водяной знак. Имеет приоритет над текстом водяного знака.', 'images/watermark.png', 0, 'images/watermark.png', '', ''),
(94, 'russian', 'Текст водяного знака', 'Этот текст будет наложен на изображение в качестве водяного знака, если изображение водяного знака недоступно.', '© {$sitename}', 0, '© {$sitename}', '', ''),
(95, 'russian', 'Цвет, размер и угол текста водяного знака', 'Задается в формате red,green,blue,size,angle', '1,1,1,15,0', 0, '1,1,1,15,0', '', ''),
(96, 'russian', 'Запретить скачивание с других сайтов?', 'При включении этой опции, при попытке скачать файл будет проверяться адрес страницы, с которой пришел пользователь. Если это будет чужая страница, пользователь не сможет скачать файл.', '1', 0, '1', '', 'Скачивание файлов'),
(97, 'russian', 'Запретить скачивание без сессии?', 'При включении этой опции, пользователь, IP адрес которого не присутствует в списке сессий, не сможет скачать файл.', '1', 0, '1', '', ''),

(98, 'russian', 'Секрет сайта', 'Случайная секретная строка при помощи которой будут подписываться данные для кросс-доменной коммутации.', '{$secret}', 0, '{$secret}', '', ''),
(99, 'russian', 'Срок жизни данных кросс-доменной коммутации', 'В секундах.', '100', 0, '100', '', ''),

(100, 'russian', 'Сколько дней хранить черновики?', '', '10', 0, '10', '', ''),
(101, 'russian', 'Интервал автосохранения в секундах', '', '20', 0, '20', '', ''),

(102, 'russian', 'Страницы, выводимые на главной', 'Оставьте пустым для вывода содержания', '', 0, '', '{$ser['sg']}', ''),

(103, 'russian', 'Публикаций на страницу', '', '10', 0, '10', '', 'Основные'),
(104, 'russian', 'Публикаций на страницу в RSS', '', '30', 0, '30', '', ''),
(105, 'russian', 'Добавление новостей пользователями', '', '1', 0, '1', '', ''),
(106, 'russian', 'Выводить содержимое подкатегорий при просмотре категории', '', '1', 0, '1', '', ''),
(107, 'russian', 'Включить ping', 'Уведомление поисковых систем об обновлении на сайте', '1', 0, '1', '', ''),
(108, 'russian', 'Включить рейтинг', '', '1', 0, '1', '', 'Настройки рейтинга'),
(109, 'russian', 'Оценка только при подробном просмотре?', 'Разрешить оценивать публикации только при их подробном просмотре?', '0', 0, '0', '', ''),
(110, 'russian', 'Рейтинг только для пользователей', 'При включении этой опции, выставлять новости оценку смогут только авторизованные пользователи и только 1 раз.', '0', 0, '0', '', ''),
(111, 'russian', 'Период между оцениваниями в днях', 'Если выставлять оценку могут не только пользователи, но и гости, эта опция определяет время, по истечению которого гость сможет вновь выставить оценку.', '3', 0, '3', '', ''),
(112, 'russian', 'Минимальная негативная оценка', 'Значение не может быть выше нуля. Для отключения негативных оценок, введите 0.', '-3', 0, '-3', '', ''),
(113, 'russian', 'Максимальная позитивная оценка', 'Значение не может быть ниже нуля. Для отключения позитивных оценок, введите 0.', '3', 0, '3', '', '')
QUERY;
#[E] Russian

#Ukrainian
$ser=array(
	'pd'=>'array(
		\'\'options\'\'=>array(
			\'\'ignore\'\'=>\'\'Ігнорувати\'\',
			\'\'redirect\'\'=>\'\'Перенаправити всі посилання на основний домен\'\',
			\'\'rel\'\'=>\'\'Додати rel canonical\'\',
		),
	)',
	'pg'=>'array(
		\'\'options\'\'=>array(\'\'Приховати\'\',\'\'Відображати тільки адміністраторам\'\',\'\'Відображати всім\'\'),
	)',
	'ab'=>'array(
		\'\'options\'\'=>array(\'\'Відключено\'\',\'\'Ліміт спроб за певний час\'\',\'\'Відображати капчу після вичерпання ліміту спроб входу.\'\'),
	)',
	'pr'=>'array(
		\'\'options\'\'=>array(1=>\'\'Найвища\'\',\'\'Висока\'\',\'\'Нормальна\'\',\'\'Низька\'\',\'\'Найнижча\'\'),
	)',
	'aa'=>'array(
		\'\'options\'\'=>array(1=>\'\'Не потрібно\'\',\'\'Через e-mail\'\',\'\'Вручну адміністратором\'\'),
	)',
	'ua'=>'array(
		\'\'options\'\'=>array(1=>\'\'Видалити\'\',\'\'Нічого не робити\'\'),
	)',
	'rp'=>'array(
		\'\'options\'\'=>array(\'\'Заборонено\'\',\'\'Дозволити ввести новий пароль\'\',\'\'Згенерувати і вислати пароль на e-mail\'\'),
	)',
	'or'=>'array(
		\'\'options\'\'=>array(1=>\'\'Зворотний\'\',\'\'Прямий\'\'),
	)',
	'cu'=>'array(
		\'\'options\'\'=>array(
			\'\'cut\'\'=>\'\'Обрізати\'\',
			\'\'small\'\'=>\'\'Зменшити\'\',
			\'\'cutsmall\'\'=>\'\'Обрізати та зменшити\'\',
			\'\'smallcut\'\'=>\'\'Зменшити та обрізати\'\',
		),
	)',
	'cf'=>'array(
		\'\'options\'\'=>array(
			\'\'w\'\'=>\'\'Ширини\'\',
			\'\'h\'\'=>\'\'Висоти\'\',
			\'\'b\'\'=>\'\'Найбільшої сторони\'\',
			\'\'s\'\'=>\'\'Найменшої сторони\'\',
		),
	)',
)+$ser;

if($ukr)
	$insert['config_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}config_l` (`id`,`language`, `title`, `descr`, `value`, `serialized`, `default`, `extra`, `startgroup`) VALUES
(1, 'ukrainian', 'Основний домен', 'Вводиться без приставки http://', '{$domain}', 0, '{$domain}', '', 'Домен'),
(2, 'ukrainian', 'Підтримка паркованих доменів', 'При запуску сайту на паркованому домені', 'redirect', 0, 'redirect', '{$ser['pd']}', ''),
(3, 'ukrainian', 'Кешування сторінок браузером', 'Введіть стандартний термін кешування сторінки у хвилинах. 0 - відключити кешування.', '600', 0, '600', '', 'Оптимізація навантаження'),
(4, 'ukrainian', 'Включати GZIP стиснення?', 'Включення цієї опції дозволить заощадити трафік', '1', 0, '', '', ''),
(5, 'ukrainian', 'Термін зберігання cookie (у днях)', '', '31536000', 0, '31536000', '', 'Cookies'),
(6, 'ukrainian', 'Домен cookie', 'Використовуйте .example.com для глобальних cookie. Зверніть увагу на крапку перед ім''ям домену. Замість "example.com" використовуючи ваше доменне ім''я. Якщо включена підтримка паркованих домено, замість імені домену використовуйте *.', '.*', 0, '.*', '', ''),
(7, 'ukrainian', 'Префікс сookie', 'Дана опція дозволяє уникнути конфліктів, якщо на домені крім системи розташовані також інші скрипти, що використовують cookies.', 'el', 0, 'el', '', ''),
(8, 'ukrainian', 'Група гостей', 'Наділити гостей правами групи...', 'a:1:{i:0;s:1:"3";}', 1, 'a:1:{i:0;s:1:"3";}', '{$ser['groups']}', 'Права по замовчуванню'),
(9, 'ukrainian', 'Відслідковувати пошукових роботів?', '', '1', 0, '1', '', ''),
(10, 'ukrainian', 'Група пошукових ботів', 'Наділити пошукових роботів правами групи...', 'a:1:{i:0;s:1:"4";}', 1, 'a:1:{i:0;s:1:"4";}', '{$ser['groups']}', ''),
(11, 'ukrainian', 'Список пошукових ботів', 'Тут зберігаються дані про пошукових ботах. Формат вводу: по одному з кожного рядка у вигляді <b>user agent=ім''я бота</ b>', '{$ser['bots']}', 1, '{$ser['bots']}', '', ''),
(12, 'ukrainian', 'Увімкнути багатомовну підтримку?', '', '{$multilang}', 0, '{$multilang}', '', 'Локалізація'),
(13, 'ukrainian', 'Часовий пояс за замовчуванням', '', '{$timezone}', 0, '{$timezone}', '{$ser['tz']}', ''),
(14, 'ukrainian', 'Заблоковані IP адреси', 'Кожна адреса - з нового рядка. Допускаються маски виду 87.183 .*.*, а так само діапазони виду 79.224.60.1-79.224.60.255 з підтримкою масок. Щоб вказати унікальну причину бана після введеного IP адреси, поставте = і напишіть причину. Наприклад: 87.183 .*.*=Покидькам тут не місце!', '', 0, '', 'array(''extra''=>array(''style''=>''word-wrap:normal''));', 'Бан по IP'),
(15, 'ukrainian', 'Повідомлення для заблокованих', 'Це повідомлення побачать користувачі, для яких не введена причина.', ':-p', 0, ':-p', '', ''),

(16, 'ukrainian', 'Назва сайту', '', '{$sitename}', 0, '{$sitename}', '', 'Заголовки сайту'),
(17, 'ukrainian', 'Роздільник заголовків', '', ' - ', 0, ' - ', '', ''),
(18, 'ukrainian', 'Опис сайту', '', 'Сайт побудований на системі управління сайтом Eleanor', 0, 'Сайт побудований на системі управління сайтом Eleanor', '', ''),
(19, 'ukrainian', 'Включити статичні посилання?', '', '{$furl}', 0, '{$furl}', '', 'Налаштування посилань'),
(20, 'ukrainian', 'Транслітерувати статичні посилання?', 'Статичні посилання контенту, а також імена файлів, що завантажуються будуть автоматично транслітеровані.', '0', 0, '', '', ''),
(21, 'ukrainian', 'Роздільник параметрів', 'У посиланні <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q> роздільником параметрів є коса риска / (слеш).\r\nДля введення допускаються будь-які не літерні символи.', '/', 0, '/', '', ''),
(22, 'ukrainian', 'Роздільник значень', 'У посиланні <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q> роздільником значень є нижній прочерк _.\r\nДля введення допускаються будь-які не літерні символи.', '_', 0, '_', '', ''),
(23, 'ukrainian', 'Закінчення статичних посилань', 'У посиланні <q>news/category/news/page_1<b>.html</b></q> закінченням є <q>.html</ q>.\r\nЗверніть увагу, що починатися це поле повинне з не літерного символу!', '.html', 0, '.html', '', ''),
(24, 'ukrainian', 'Автозаміна неприпустимих символів у статичних посиланнях', 'Значення не повинно збігатися ні з роздільником параметрів, ні з роздільником значень, ні з закінченням статичних посилань і так само повинно починатися з не літерного символу.', '-', 0, '-', '', ''),
(25, 'ukrainian', 'Модуль без префіксу', 'Посилання цього модуля будуть працювати без вказівки префіксу-ідентифікатора модуля', '2', 0, '2', '{$ser['lfm']}', ''),
(26, 'ukrainian', 'Вимкнути сайт?', 'Сайт буде доступний тільки групам, для яких включена опція перегляду закритого сайту', '0', 0, '0', '', 'Вимкнення сайту'),
(27, 'ukrainian', 'Причина вимкнення сайту', '', '', 0, '', 'array(''type''=>-1)', ''),
(28, 'ukrainian', 'Інформація про генерацію сторінки', 'Інформація внизу сторінки, що містить швидкість генерації сторінки, кількість використаних запитів до бази даних, статус GZIP стиснення і кількість витраченої пам''яті.', '2', 0, '2', '{$ser['pg']}', 'Додаткова інформація'),
(29, 'ukrainian', 'Шаблони на вибір', 'Вкажіть шаблони, які користувачі зможуть вибирати в якості оформлення сайту.', 'a:1:{i:0;s:5:"Uniel";}', 1, 'a:1:{i:0;s:5:"Uniel";}', '{$ser['templates']}', 'Різне'),

(30, 'ukrainian', 'Посилання особистого кабінету', 'Зверніть увагу, що запис <q>param1=value1<b>&</b>param2=value2</q> некоректний. Коректний запис: <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_a_u}', 0, '{$ac_a_u}', '', 'Посилання'),
(31, 'ukrainian', 'Посилання на реєстрацію', 'Зверніть увагу, що запис <q>param1=value1<b>&</b>param2=value2</q> некоректний. Коректний запис: <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_r_u}', 0, '{$ac_r_u}', '', ''),
(32, 'ukrainian', 'Посилання на відновлення пароля', 'Зверніть увагу, що запис <q>param1=value1<b>&</b>param2=value2</q> некоректний. Коректний запис: <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_p_u}', 0, '{$ac_p_u}', '', ''),
(33, 'ukrainian', 'Тривалість сесій', 'Кількість секунд під час яких користувач вважається онлайн після прояву активності.', 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', 1, 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', '{$ser['time_online']}', 'Параметри аутентифікації та авторизації'),
(34, 'ukrainian', 'Тип захисту', '', '1', 0, '', '{$ser['ab']}', 'Захист від підбору паролів'),
(35, 'ukrainian', 'Максимальна кількість невдалих спроб аутентифікації', '', '5', 0, '5', '', ''),
(36, 'ukrainian', 'Максимальний проміжок часу в хвилинах, під час якого допускаються невдалі спроби аутентифікації', 'Приклад: якщо максимальне число невдалих спроб аутентифікації дорівнює 5, а задане значення дорівнює 15, то користувач буде заблокований у разі якщо за останні 15 хвилин було 5 невдалих спроб входу.', '600', 0, '600', '', ''),

(37, 'ukrainian', 'Заблоковані прізвиська', 'Через кому. Допустимі спецсимволи: * - будь-яка послідовність символів,? - будь-який один символ.', '', 0, '', '', 'Блокування'),
(38, 'ukrainian', 'Заблоковані e-mail', 'Через кому. Допустимі спецсимволи: * - будь-яка послідовність символів,? - будь-який один символ.', '', 0, '', '', ''),
(39, 'ukrainian', 'Активація новоствореного користувача', '', '1', 0, '1', '{$ser['aa']}', 'Реєстрація'),
(40, 'ukrainian', 'Термін активації', 'Кількість годин відведених для активації облікового запису.', '86400', 0, '86400', 'array(''type''=>''number'',''extra''=>array(''min''=>1));', ''),
(41, 'ukrainian', 'Як поступати з неактивованими обліковими записами', '', '1', 0, '1', '{$ser['ua']}', ''),
(42, 'ukrainian', 'Вимкнути реєстрацію?', '', '0', 0, '0', '', ''),
(43, 'ukrainian', 'Максимальна довжина імені', '', '15', 0, '15', '', ''),
(44, 'ukrainian', 'Мінімальна довжина пароля', '', '7', 0, '7', '', ''),
(45, 'ukrainian', 'Максимальний розмір завантажуваного аватара в KB', '', '307200', 0, '307200', '', 'Профіль'),
(46, 'ukrainian', 'Максимальний розмір аватара', 'Вводити потрібно в форматі: ширина[пробіл]висота.', '100 100', 0, '100 100', '', ''),
(47, 'ukrainian', 'Відновлення пароля', '', '1', 0, '1', '{$ser['rp']}', ''),

(48, 'ukrainian', 'Кількість символів в captcha', '', '5', 0, '5', '', 'Налаштування captcha'),
(49, 'ukrainian', 'Алфавіт captcha', 'Символи що використовуються в captcha (бажано вилучити подібні символи, такі як 0 - цифра і o - буква)', '23456789abcdeghkmnpqsuvxyz', 0, '23456789abcdeghkmnpqsuvxyz', '', ''),
(50, 'ukrainian', 'Ширина captcha', '', '120', 0, '120', '', ''),
(51, 'ukrainian', 'Висота captcha', '', '60', 0, '60', '', ''),
(52, 'ukrainian', 'Розкид символів по вертикалі', 'Максимальне відхилення символів по вертикалі від центру.', '5', 0, '5', '', ''),

(53, 'ukrainian', 'Спосіб відправки e-mail', '', 'mail', 0, 'mail', 'array(''options''=>array(''php''=>''PHP mail'',''smtp''=>''SMTP'',))', 'Загальні налаштування'),
(54, 'ukrainian', 'E-mail відправника', 'С какого ящика будут приходить письма?', '{$email}', 0, '{$email}', '', ''),
(55, 'ukrainian', 'Важливість', '', '3', 0, '3', '{$ser['pr']}', ''),
(56, 'ukrainian', 'E-mail для відповіді', 'У разі, якщо відповіді від користувачів необхідно приймати на інший e-mail, заповніть це поле.', '', 0, '', '', ''),
(57, 'ukrainian', 'E-mail для підтвердження про прочитання', '', '', 0, '', '', ''),
(58, 'ukrainian', 'Логін', 'Користувач', '', 0, '', '', 'Установки SMTP'),
(59, 'ukrainian', 'Пароль', '', '', 0, '', '', ''),
(60, 'ukrainian', 'Хост', 'Сервер', '', 0, '', '', ''),
(61, 'ukrainian', 'Порт', '', '25', 0, '25', '', ''),

(62, 'ukrainian', 'Відповідальні користувачі', 'Введіть імена користувачів, розділяючи їх комами', 1, 0, 1, '{$ser['errors']}', 'Помилки у коді'),
(63, 'ukrainian', 'Тема листа', '{site} - назва сайту<br />{link} - посилання на сайт<br />{full} - посилання в адмінку на перегляд всього логу<br />{cnt} - число помилок<br />{errors} - лог помилок<br />{name} - им\\\\''я користувача', 'Нові помилки на {site}', 0, 'Нові помилки на {site}', 'array(''checkout''=>false)', ''),
(64, 'ukrainian', 'Текст листа', '{site} - назва сайту<br />{link} - посилання на сайт<br />{full} - посилання в адмінку на перегляд всього логу<br />{cnt} - число помилок<br />{errors} - лог помилок<br />{name} - им\\\\''я користувача', 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайті "{site}" [cnt=plural]сталася {cnt} помилка|сталися {cnt} помилки|сталося {cnt} помилок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендується терміново їх виправити.<br />\\n<br />\\nЗ найкращими побажаннями,<br />\\nкоманда сайту {site} .', 0, 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайті "{site}" [cnt=plural]сталася {cnt} помилка|сталися {cnt} помилки|сталося {cnt} помилок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендується терміново їх виправити.<br />\\n<br />\\nЗ найкращими побажаннями,<br />\\nкоманда сайту {site} .', 'array(''checkout''=>false)', ''),
(65, 'ukrainian', 'Відповідальні користувачі', 'Введіть імена користувачів, розділяючи їх комами', 1, 0, 1, '{$ser['errors']}', 'Помилки баз даних'),
(66, 'ukrainian', 'Тема листа', '{site} - назва сайту<br />{link} - посилання на сайт<br />{full} - посилання в адмінку на перегляд всього логу<br />{cnt} - число помилок<br />{errors} - лог помилок<br />{name} - им\\\\''я користувача', 'Нові помилки на {site}', 0, 'Нові помилки на {site}', 'array(''checkout''=>false)', ''),
(67, 'ukrainian', 'Текст листа', '{site} - назва сайту<br />{link} - посилання на сайт<br />{full} - посилання в адмінку на перегляд всього логу<br />{cnt} - число помилок<br />{errors} - лог помилок<br />{name} - им\\\\''я користувача', 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайті "{site}" [cnt=plural]сталася {cnt} помилка|сталися {cnt} помилки|сталося {cnt} помилок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендується терміново їх виправити.<br />\\n<br />\\nЗ найкращими побажаннями,<br />\\nкоманда сайту {site} .', 0, 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайті "{site}" [cnt=plural]сталася {cnt} помилка|сталися {cnt} помилки|сталося {cnt} помилок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендується терміново їх виправити.<br />\\n<br />\\nЗ найкращими побажаннями,<br />\\nкоманда сайту {site} .', 'array(''checkout''=>false)', ''),
(68, 'ukrainian', 'Відповідальні користувачі', 'Введіть імена користувачів, розділяючи їх комами', 1, 0, 1, '{$ser['errors']}', 'Помилкові запити'),
(69, 'ukrainian', 'Тема листа', '{site} - назва сайту<br />{link} - посилання на сайт<br />{full} - посилання в адмінку на перегляд всього логу<br />{cnt} - число помилок<br />{errors} - лог помилок<br />{name} - им\\\\''я користувача', 'Нові помилки на {site}', 0, 'Нові помилки на {site}', 'array(''checkout''=>false)', ''),
(70, 'ukrainian', 'Текст листа', '{site} - назва сайту<br />{link} - посилання на сай<br />{full} - посилання в адмінку на перегляд всього логут<br />{cnt} - число помилок<br />{errors} - лог помилок<br />{name} - им\\\\''я користувача', 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайті "{site}" [cnt=plural]сталася {cnt} помилка|сталися {cnt} помилки|сталося {cnt} помилок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендується терміново їх виправити.<br />\\n<br />\\nЗ найкращими побажаннями,<br />\\nкоманда сайту {site} .', 0, 'Здравствуйте, {name}!<br />\\n<br />\\nНа сайті "{site}" [cnt=plural]сталася {cnt} помилка|сталися {cnt} помилки|сталося {cnt} помилок[/cnt]:<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nРекомендується терміново їх виправити.<br />\\n<br />\\nЗ найкращими побажаннями,<br />\\nкоманда сайту {site} .', 'array(''checkout''=>false)', ''),

(71, 'ukrainian', 'Редактор по замовчуванню', '', 'bb', 0, 'bb', 'array(''eval''=>''return Eleanor::getInstance()->Editor->editors;'')', ''),
(72, 'ukrainian', 'Заборонені слова', 'Мати й лайки. Через кому.', 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', 0, 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', '', ''),
(73, 'ukrainian', 'Автозаміна заборонених слів', '', '*Цензура*', 0, '*Цензура*', '', ''),
(74, 'ukrainian', 'Активувати захист від прямих посилань?', '', 'go', 0, 'go', 'array(''options''=>array(''ні'',''go''=>''Редірект через go.php'',''nofollow''=>''rel="nofollow"'',))', ''),
(75, 'ukrainian', 'Включити автовизначення посилань у тексті?', 'При включенні цієї опції, всі посилання опубліковані як текст - будуть оброблені як посилання.', '1', 0, '1', '', ''),

(76, 'ukrainian', 'Логотип RSS', '', 'images/rss.png', 0, 'images/rss.png', 'array(''path''=>''uploads/'',''types''=>array(0=>''jpeg'',1=>''jpg'',2=>''png'',3=>''bmp'',4=>''gif'',),''max_size''=>''307200'',''filename_eval''=>''return \\\\''rss\\\\''.strrchr(\$a[\\\\''filename\\\\''],\\\\''.\\\\'');'',)', ''),

(77, 'ukrainian', 'Порядок сортування коментарів', '', '1', 0, '1', '{$ser['or']}', ''),
(78, 'ukrainian', 'Коментарів на сторінку', '', '10', 0, '10', '', ''),
(79, 'ukrainian', 'Обмеження зміни за часом', 'Введіть кількість секунд, після завершення яких користувачі не зможуть видаляти / редагувати свої коментарі. Відлік часу здійснюється з моменту публікації коментаря.', '86400', 0, '86400', '', ''),
(80, 'ukrainian', 'Відображати коментарі для', '', 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', 1, 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', '{$ser['groups']}', 'Права'),
(81, 'ukrainian', 'Публікація коментарів доступна для', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '{$ser['groups']}', ''),

(82, 'ukrainian', 'Включити створення прев''ю для завантажуваних зображень?', '', '1', 0, '1', '', 'Превью зображень'),
(83, 'ukrainian', 'Типи файлів для яких створювати превью', 'Укажите типы файлов для которых будут создаваться превью (через запятую).', 'png,jpg,bmp', 0, 'png,jpg,bmp', '', ''),
(84, 'ukrainian', 'Ширина превью', 'Введіть 0 для збереження вихідної ширини зображення', '200', 0, '200', '', ''),
(85, 'ukrainian', 'Висота прев''ю', 'Введіть 0 для збереження вихідної висоти зображення', '0', 0, '0', '', ''),
(86, 'ukrainian', 'Спосіб зменшення зображення', '', 'small', 0, 'small', '{$ser['cu']}', ''),
(87, 'ukrainian', 'Створення превью почати з', '', 'b', 0, 'b', '{$ser['cf']}', ''),
(88, 'ukrainian', 'Включити водяний знак?', 'Накласти водяний знак на завантажувані картинки?', '1', 0, '1', '', 'Настройки ватермарка'),
(89, 'ukrainian', 'Типи файлів для водяного знака', 'На ці типи файлів буде ставитися водяний знак. Вказуйте, розділяючи комами.', 'jpg,jpeg,png,bmp', 0, 'jpg,jpeg,png,bmp', '', ''),
(90, 'ukrainian', 'Прозорість водяного знаку (у відсотках від 0 до 100)', '100 - не видно водяного знаку', '50', 0, '50', '', ''),
(91, 'ukrainian', 'Положення по вертикалі (у відсотках від 0 до 100)', '', '50', 0, '50', '', ''),
(92, 'ukrainian', 'Положення по горизонталі (у відсотках від 0 до 100)', '', '50', 0, '50', '', ''),
(93, 'ukrainian', 'Файл водяного знаку', 'Введіть шлях до малюнка на сервері, який буде використаний в якості водяного знака (наприклад: images / watermrak.jpg). Зверніть увагу, що водяний знак не буде застосований на зображення якщо за розмірами вона менша, ніж водяний знак. Має пріоритет над текстом водяного знака.', 'images/watermark.png', 0, 'images/watermark.png', '', ''),
(94, 'ukrainian', 'Текст водяного знака', 'Цей текст буде накладено на зображення в якості водяного знака, якщо зображення водяного знака недоступне.', '© {$sitename}', 0, '© {$sitename}', '', ''),
(95, 'ukrainian', 'Колір, розмір і кут тексту водяного знака', 'Задається в форматі red, green, blue, size, angle', '1,1,1,15,0', 0, '1,1,1,15,0', '', ''),
(96, 'ukrainian', 'Заборонити скачування з інших сайтів?', 'При включенні цієї опції, при спробі завантажити файл буде перевірятися адресу сторінки, з якою прийшов користувач. Якщо це буде чужа сторінка, користувач не зможе завантажити файл.', '1', 0, '1', '', 'Завантаження файлів'),
(97, 'ukrainian', 'Заборонити скачування без сесії?', 'При включенні цієї опції, користувач, IP-адреса якого не присутній у списку сесій, не зможе завантажити файл.', '1', 0, '1', '', ''),

(98, 'ukrainian', 'Секрет сайту', 'Випадковий секретний рядок за допомогою якого будуть підписуватися дані для міждоменної комутації.', '{$secret}', 0, '{$secret}', '', ''),
(99, 'ukrainian', 'Термін життя даних крос-доменної комутації', 'У секундах.', '100', 0, '100', '', ''),

(100, 'ukrainian', 'Скільки днів зберігати чернетки?', '', '10', 0, '10', '', ''),
(101, 'ukrainian', 'Інтервал автозбереження в секундах', '', '20', 0, '20', '', ''),

(102, 'ukrainian', 'Сторінки, що виводиться на головній', 'Залиште пустим для виведення змісту', '', 0, '', '{$ser['sg']}', ''),

(103, 'ukrainian', 'Публікацій на сторінку', '', '10', 0, '10', '', 'Загальні'),
(104, 'ukrainian', 'Публікацій на сторінку в RSS', '', '30', 0, '30', '', ''),
(105, 'ukrainian', 'Додавання новин користувачами', '', '1', 0, '1', '', ''),
(106, 'ukrainian', 'Виводити вміст підкатегорій при перегляді категорії', '', '1', 0, '1', '', ''),
(107, 'ukrainian', 'Включити ping', 'Повідомлення пошукових систем про оновлення на сайті', '1', 0, '1', '', ''),
(108, 'ukrainian', 'Включити рейтинг', '', '1', 0, '1', '', 'Настройки рейтингу'),
(109, 'ukrainian', 'Оцінка тільки при детальному перегляді?', 'Дозволити оцінювати публікації тільки при їх детальному перегляді?', '0', 0, '0', '', ''),
(110, 'ukrainian', 'Рейтинг тільки для користувачів', 'При включенні цієї опції, виставляти новини оцінку зможуть лише авторизовані користувачі і лише 1 раз.', '0', 0, '0', '', ''),
(111, 'ukrainian', 'Період між оцінювання у днях', 'Якщо виставляти оцінку можуть не лише користувачі, але й гості, ця опція визначає час, по закінченню якого гість зможе знову виставити оцінку.', '3', 0, '3', '', ''),
(112, 'ukrainian', 'Мінімальна негативна оцінка', 'Значення не може бути більше нуля. Для відключення негативних оцінок, введіть 0.', '-3', 0, '-3', '', ''),
(113, 'ukrainian', 'Максимальна позитивна оцінка', 'Значення не може бути нижче нуля. Для відключення позитивних оцінок, введіть 0', '3', 0, '3', '', '')
QUERY;
#[E] Ukrainian

#English
$ser=array(
	'pd'=>'array(
		\'\'options\'\'=>array(
			\'\'ignore\'\'=>\'\'Ignore\'\',
			\'\'redirect\'\'=>\'\'Redirect all links to the primary domain\'\',
			\'\'rel\'\'=>\'\'Add rel canonical\'\',
		),
	)',
	'pg'=>'array(
		\'\'options\'\'=>array(\'\'Hide\'\',\'\'Display only for administrators\'\',\'\'Display for all\'\'),
	)',
	'ab'=>'array(
		\'\'options\'\'=>array(\'\'Disabled\'\',\'\'Limit of attempts within a certain time\'\',\'\'Display the captcha, after exhausting the limit of login attempts\'\'),
	);',
	'pr'=>'array(
		\'\'options\'\'=>array(1=>\'\'Highest\'\',\'\'High\'\',\'\'Normal\'\',\'\'Low\'\',\'\'Lowest\'\'),
	);',
	'aa'=>'array(
		\'\'options\'\'=>array(1=>\'\'Not required\'\',\'\'By e-mail\'\',\'\'Manually by an administrator\'\'),
	)',
	'ua'=>'array(
		\'\'options\'\'=>array(1=>\'\'Delete\'\',\'\'Nothing\'\'),
	)',
	'rp'=>'array(
		\'\'options\'\'=>array(\'\'Disabled\'\',\'\'Allow enter a new password\'\',\'\'Generate and send the password to the e-mail\'\'),
	)',
	'or'=>'array(
		\'\'options\'\'=>array(1=>\'\'Reverse\'\',\'\'Direct\'\'),
	)',
	'cu'=>'array(
		\'\'options\'\'=>array(
			\'\'cut\'\'=>\'\'Crop\'\',
			\'\'small\'\'=>\'\'Reduce\'\',
			\'\'cutsmall\'\'=>\'\'Crop and reduce\'\',
			\'\'smallcut\'\'=>\'\'Reduce and crop\'\',
		),
	)',
	'cf'=>'array(
		\'\'options\'\'=>array(
			\'\'w\'\'=>\'\'Width\'\',
			\'\'h\'\'=>\'\'Height\'\',
			\'\'b\'\'=>\'\'Biggest side\'\',
			\'\'s\'\'=>\'\'Smallest side\'\',
		),
	)',
)+$ser;

if($eng)
	$insert['config_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}config_l` (`id`,`language`,`title`,`descr`,`value`,`serialized`,`default`,`extra`,`startgroup`) VALUES
(1, 'english', 'Primary domain', 'Enter without prefix http://', '{$domain}', 0, '{$domain}', '', 'Domain'),
(2, 'english', 'Support for parked domains', 'When site running on a parked domain', 'redirect', 0, 'redirect', '{$ser['pd']}', ''),
(3, 'english', 'Browser page caching', 'Enter the standard term caching pages in minutes. 0 - disable caching.', '600', 0, '600', '', 'Optimizing workload'),
(4, 'english', 'Enamble GZIP compression?', 'Enabling this option will save bandwidth', '1', 0, '', '', ''),
(5, 'english', 'Cookie lifetime (in days)', '', '31536000', 0, '31536000', '', 'Cookies'),
(6, 'english', 'Cookie domain', 'Use .example.com for the global cookie. Note the dot before the domain name. Instead of "example.com" use your domain name. If enabled parked domain names, instead of the domain name use *.', '.*', 0, '.*', '', ''),
(7, 'english', 'Cookie prefix', 'This option allows you to avoid conflicts if a domain other than the system are located and other scripts that use cookies.', 'el', 0, 'el', '', ''),
(8, 'english', 'Guests group', 'Give guest the rights of group...', 'a:1:{i:0;s:1:"3";}', 1, 'a:1:{i:0;s:1:"3";}', '{$ser['groups']}', 'Permissions by default'),
(9, 'english', 'Track search engine bots?', '', '1', 0, '1', '', ''),
(10, 'english', 'Search engine bots group', 'Give the search bots rights of group...', 'a:1:{i:0;s:1:"4";}', 1, 'a:1:{i:0;s:1:"4";}', '{$ser['groups']}', ''),
(11, 'english', 'List of search engine bots', 'Here, the data is stored on the search bots. Input format: one on each line in the form <b>user agent=bot</ b>.', '{$ser['bots']}', 1, '{$ser['bots']}', '', ''),
(12, 'english', 'Enable multilingual support?', '', '{$multilang}', 0, '{$multilang}', '', 'Localization'),
(13, 'english', 'Timezone by default', '', '{$timezone}', 0, '{$timezone}', '{$ser['tz']}', ''),
(14, 'english', 'Blocked IP addresses', 'Each address - with a new line. Wildcards like 87.183.*.* are enabled, where * is any value. That would indicate a unique reason for the ban imposed after the IP address, put "=" and write the reason. For example:<br />87.183.*.*=Fuck you!', '', 0, '', 'array(''extra''=>array(''style''=>''word-wrap:normal''));', 'Blocking by IP'),
(15, 'english', 'Message to the blocked', 'This message will appear to users that do not enter a reason.', ':-p', 0, ':-p', '', ''),

(16, 'english', 'Site name', '', '{$sitename}', 0, '{$sitename}', '', 'Site headers'),
(17, 'english', 'Separator titles', '', ' - ', 0, ' - ', '', ''),
(18, 'english', 'Site description', '', 'The site is built on a content management system Eleanor', 0, 'The site is built on a content management system Eleanor', '', ''),
(19, 'english', 'Enable static links?', '', '{$furl}', 0, '{$furl}', '', 'Links options'),
(20, 'english', 'Transliterate static links?', 'Static links content and the names of uploaded files will be automatically transliterated.', '0', 0, '', '', ''),
(21, 'english', 'Delimiter parameters', 'In link <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q>  delimiter parameters is / (slash).\r\nAllowed to enter any non-alphabetic characters.', '/', 0, '/', '', ''),
(22, 'english', 'Delimiter values', 'In exile, <q>news/category/news/page<b>_</b>1.html</q> separator value is lower dash _.\r\nAllowed to enter any non-alphabetic characters.', '_', 0, '_', '', ''),
(23, 'english', 'End of static links', 'In link <q>news/category/news/page_1<b>.html</b></q> ending is <q>.html</ q>.\r\nNote that this field should begin with an alphabetic character is not!', '.html', 0, '.html', '', ''),
(24, 'english', 'Autocorrect invalid characters in static links', 'Value should not coincide with delimiter parameters or delimited values, or with the end of static links and the same should not begin with an alphabetic character.', '-', 0, '-', '', ''),
(25, 'english', 'Module without prefix', 'Links of this module will work without module prefix-identifier', '2', 0, '2', '{$ser['lfm']}', ''),
(26, 'english', 'Turn off the site?', 'Site will be available to groups for which the option view site closing', '0', 0, '0', '', 'Turning site off'),
(27, 'english', 'The reason for the turning off the site', '', '', 0, '', 'array(''type''=>-1)', ''),
(28, 'english', 'Information about the generation of page', 'Information at the bottom of the page containing the speed of page generation, the number of used database queries, GZIP compression status and number of memory consumed.', '2', 0, '2', '{$ser['pg']}', 'Addon information'),
(29, 'english', 'Templates to choose', 'Specify a templates that users can choose as a site design.', 'a:1:{i:0;s:5:"Uniel";}', 1, 'a:1:{i:0;s:5:"Uniel";}', '{$ser['templates']}', 'Others'),

(30, 'english', 'Link to personal cabinet', 'Please note that the record <q>param1=value1<b>&</b>param2=value2</q> is incorrect. Correct record is <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_a_e}', 0, '{$ac_a_e}', '', 'Links'),
(31, 'english', 'Link to registration', 'Please note that the record <q>param1=value1<b>&</b>param2=value2</q> is incorrect. Correct record is <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_r_e}', 0, '{$ac_r_e}', '', ''),
(32, 'english', 'Link to password recovery', 'Please note that the record <q>param1=value1<b>&</b>param2=value2</q> is incorrect. Correct record is <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_p_e}', 0, '{$ac_p_e}', '', ''),
(33, 'english', 'Duration of sessions', 'Number of seconds during which user considered as online after his last activity.', 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', 1, 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', '{$ser['time_online']}', 'The authentication and authorization'),
(34, 'english', 'Protection type', '', '1', 0, '', '{$ser['ab']}', 'Protection against guessing password'),
(35, 'english', 'The maximum number of unsuccessful authentication attempts', '', '5', 0, '5', '', ''),
(36, 'english', 'The maximum amount of time in minutes, during which allowed failed authentication attempts', 'Example: If the maximum number of failed authentication attempts is 5, and the set value is 15, the user will be blocked if the last 15 minutes were 5 failed login attempts.', '600', 0, '600', '', ''),

(37, 'english', 'Blocked nicknames', 'Separated by commas. Allowable special characters: * - any sequence of characters,? - any one character.', '', 0, '', '', 'Bans'),
(38, 'english', 'Blocked e-mail', 'Separated by commas. Allowable special characters: * - any sequence of characters,? - any one character.', '', 0, '', '', ''),
(39, 'english', 'Activation of newly created user', '', '1', 0, '1', '{$ser['aa']}', 'Register'),
(40, 'english', 'Activation term', 'The number of hours allotted to activate account.', '86400', 0, '86400', 'array(''type''=>''number'',''extra''=>array(''min''=>1));', ''),
(41, 'english', 'How to deal with nonactivated accounts', '', '1', 0, '1', '{$ser['ua']}', ''),
(42, 'english', 'Disable registration?', '', '0', 0, '0', '', ''),
(43, 'english', 'The maximum length of nickname', '', '15', 0, '15', '', ''),
(44, 'english', 'The minimum length of password', '', '7', 0, '7', '', ''),
(45, 'english', 'Maximum size of uploaded avatars in KB', '', '307200', 0, '307200', '', 'Profile'),
(46, 'english', 'Maximum size of avatar', 'Need to enter in the format: width[space]height.', '100 100', 0, '100 100', '', ''),
(47, 'english', 'Password recovery', '', '1', 0, '1', '{$ser['rp']}', ''),

(48, 'english', 'Number of characters in captcha', '', '5', 0, '5', '', 'Captcha options'),
(49, 'english', 'Captcha alphabet', 'Symbols used in the captcha (preferably exclude similar characters like 0 - number and o - letter)', '23456789abcdeghkmnpqsuvxyz', 0, '23456789abcdeghkmnpqsuvxyz', '', ''),
(50, 'english', 'Сaptcha width', '', '120', 0, '120', '', ''),
(51, 'english', 'Captcha height', '', '60', 0, '60', '', ''),
(52, 'english', 'The scatter symbols on the vertical', 'The maximum deviation of the symbols on the vertical line from the center.', '5', 0, '5', '', ''),

(53, 'english', 'Way to send e-mail', '', 'mail', 0, 'mail', 'array(''options''=>array(''php''=>''PHP mail'',''smtp''=>''SMTP'',))', 'General settiongs'),
(54, 'english', 'Sender e-mail', 'From what email letters will send?', '{$email}', 0, '{$email}', '', 'Общие настройки'),
(55, 'english', 'Importance', '', '3', 0, '3', '{$ser['pr']}', ''),
(56, 'english', 'E-mail for response', 'If the response from the user should be taken to another e-mail, fill out this field.', '', 0, '', '', ''),
(57, 'english', 'E-mail to confirm a read', '', '', 0, '', '', ''),
(58, 'english', 'Login', 'User', '', 0, '', '', 'SMTP settings'),
(59, 'english', 'Password', '', '', 0, '', '', ''),
(60, 'english', 'Host', 'Server', '', 0, '', '', ''),
(61, 'english', 'Port', '', '25', 0, '25', '', ''),

(62, 'english', 'Responsible users', 'Enter user names, separated by commas', 1, 0, 1, '{$ser['errors']}', 'Code errors'),
(63, 'english', 'Subject email', '{site} - site name<br />{link} - link to site<br />{full} - link in the admin panel to view the entire log<br />{cnt} - number of errors<br />{errors} - error log<br />{name} - user name', 'New errors on {site}', 0, 'New errors on {site}', 'array(''checkout''=>false)', ''),
(64, 'english', 'Text email', '{site} - site name<br />{link} - link to site<br />{full} - link in the admin panel to view the entire log<br />{cnt} - number of errors<br />{errors} - error log<br />{name} - user name', 'Hello, {name}!<br />\\n<br />\\n{cnt} errors happend on site "{site}".<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nImmediately recommended to fix them.<br />\\n<br />\\nWith best wishes,<br />\\nteam {site} .', 0, 'Hello, {name}!<br />\\n<br />\\n{cnt} errors happend on site "{site}".<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nImmediately recommended to fix them.<br />\\n<br />\\nWith best wishes,<br />\\nteam {site} .', 'array(''checkout''=>false)', ''),
(65, 'english', 'Responsible users', 'Enter user names, separated by commas', 1, 0, 1, '{$ser['errors']}', 'Database errors'),
(66, 'english', 'Subject email', '{site} - site name<br />{link} - link to site<br />{full} - link in the admin panel to view the entire log<br />{cnt} - number of errors<br />{errors} - error log<br />{name} - user name', 'New errors on {site}', 0, 'New errors on {site}', 'array(''checkout''=>false)', ''),
(67, 'english', 'Text email', '{site} - site name<br />{link} - link to site<br />{full} - link in the admin panel to view the entire log<br />{cnt} - number of errors<br />{errors} - error log<br />{name} - user name', 'Hello, {name}!<br />\\n<br />\\n{cnt} errors happend on site "{site}".<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nImmediately recommended to fix them.<br />\\n<br />\\nWith best wishes,<br />\\nteam {site} .', 0, 'Hello, {name}!<br />\\n<br />\\n{cnt} errors happend on site "{site}".<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nImmediately recommended to fix them.<br />\\n<br />\\nWith best wishes,<br />\\nteam {site} .', 'array(''checkout''=>false)', ''),
(68, 'english', 'Responsible users', 'Enter user names, separated by commas', 1, 0, 1, '{$ser['errors']}', 'Errors request'),
(69, 'english', 'Subject email', '{site} - site name<br />{link} - link to site<br />{full} - link in the admin panel to view the entire log<br />{cnt} - number of errors<br />{errors} - error log<br />{name} - user name', 'New errors on {site}', 0, 'New errors on {site}', 'array(''checkout''=>false)', ''),
(70, 'english', 'Text email', '{site} - site name<br />{link} - link to site<br />{full} - link in the admin panel to view the entire log<br />{cnt} - number of errors<br />{errors} - error log<br />{name} - user name', 'Hello, {name}!<br />\\n<br />\\n{cnt} errors happend on site "{site}".<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nImmediately recommended to fix them.<br />\\n<br />\\nWith best wishes,<br />\\nteam {site} .', 0, 'Hello, {name}!<br />\\n<br />\\n{cnt} errors happend on site "{site}".<br />\\n[html]<pre><code>{errors}</code></pre>[/html]<br />\\nImmediately recommended to fix them.<br />\\n<br />\\nWith best wishes,<br />\\nteam {site} .', 'array(''checkout''=>false)', ''),

(71, 'english', 'Editor by default', '', 'bb', 0, 'bb', 'array(''eval''=>''return Eleanor::getInstance()->Editor->editors;'')', ''),
(72, 'english', 'Swear words', 'Mats and abuse. Separated by commas.', 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', 0, 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', '', ''),
(73, 'english', 'Autocorrect banned words', '', '*Censorship*', 0, '*Censorship*', '', ''),
(74, 'english', 'Enable protection from direct links?', '', 'bb', 0, 'bb', 'array(''options''=>array(''no'',''go''=>''Redirect via go.php'',''nofollow''=>''rel="nofollow"'',))', ''),
(75, 'english', 'Enable autoparse links in the text?', 'If you enable this option, all links published as text - will be treated as links.', '1', 0, '1', '', ''),

(76, 'english', 'RSS logo', '', 'images/rss.png', 0, 'images/rss.png', 'array(''path''=>''uploads/'',''types''=>array(0=>''jpeg'',1=>''jpg'',2=>''png'',3=>''bmp'',4=>''gif'',),''max_size''=>''307200'',''filename_eval''=>''return \\\\''rss\\\\''.strrchr(\$a[\\\\''filename\\\\''],\\\\''.\\\\'');'',)', ''),

(77, 'english', 'Order displaying comments', '', '1', 0, '1', '{$ser['or']}', ''),
(78, 'english', 'Comments per page', '', '10', 0, '10', '', ''),
(79, 'english', 'Limitation of time changes', 'Enter the number of seconds after which users can not delete / edit your comments. The countdown is carried out since the publication of comments.', '86400', 0, '86400', '', ''),
(80, 'english', 'Display comments for', '', 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', 1, 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', '{$ser['groups']}', 'Rights'),
(81, 'english', 'Post comments available for', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '{$ser['groups']}', ''),

(82, 'english', 'Enable the creation of thumbs for uploaded images?', '', '1', 0, '1', '', 'Preview images'),
(83, 'english', 'File types for which to create thumbs', 'Specify the file types for which to create a preview (separated by comma).', 'png,jpg,bmp', 0, 'png,jpg,bmp', '', ''),
(84, 'english', 'Thumb width', 'Enter 0 to keep the original width of the image', '200', 0, '200', '', ''),
(85, 'english', 'Height preview', 'Enter 0 to keep the original height of the image', '0', 0, '0', '', ''),
(86, 'english', 'Method of reducing the image', '', 'small', 0, 'small', '{$ser['cu']}', ''),
(87, 'english', 'Creating a thumb begin with', '', 'b', 0, 'b', '{$ser['cf']}', ''),
(88, 'english', 'Enable a watermark?', 'Apply a watermark to your uploaded images?', '1', 0, '1', '', 'Настройки ватермарка'),
(89, 'english', 'Filetypes for watermark', 'These types of files will be put watermark. Specify, separated by commas.', 'jpg,jpeg,png,bmp', 0, 'jpg,jpeg,png,bmp', '', ''),
(90, 'english', 'Transparency of the watermark (as a percentage from 0 to 100)', '100 - not visible watermark', '50', 0, '50', '', ''),
(91, 'english', 'Vertical position (as a percentage from 0 to 100)', '', '50', 0, '50', '', ''),
(92, 'english', 'Horizontal position (as a percentage from 0 to 100)', '', '50', 0, '50', '', ''),
(93, 'english', 'File of the watermark', 'Enter the path to the picture on the server, which will be used as a watermark (eg: images / watermrak.jpg). Please note that the watermark will not be applied to the image if the size is less than the watermark. Takes precedence over the text watermark.', 'images/watermark.png', 0, 'images/watermark.png', '', ''),
(94, 'english', 'Text watermark', 'This text will be superimposed on the image as a watermark, if the watermark image is not available.', '© {$sitename}', 0, '© {$sitename}', '', ''),
(95, 'english', 'Color, size and angle of text watermark', 'Given in the format of red, green, blue, size, angle', '1,1,1,15,0', 0, '1,1,1,15,0', '', ''),
(96, 'english', 'Prohibit downloads from other sites?', 'When this option when you try to download the file will be checked to indicate the address to which the user came from. If it is someone else''s page, the user can not download the file.', '1', 0, '1', '', 'Downloading files'),
(97, 'english', 'Prohibit downloading without session?', 'When this option is enabled, the user, IP address is not on the list of sessions will not be able to download the file.', '1', 0, '1', '', ''),

(98, 'english', 'Site secret', 'A random secret string with which to sign the data for cross-domain switching.', '{$secret}', 0, '{$secret}', '', ''),
(99, 'english', 'The life of these cross-domain switching', 'In seconds.', '100', 0, '100', '', ''),

(100, 'english', 'Days to keep drafts?', '', '10', 0, '10', '', ''),
(101, 'english', 'Autosave interval in seconds', '', '20', 0, '20', '', ''),

(102, 'english', 'The pages displayed on the main', 'Leave empty to display contents', '', 0, '', '{$ser['sg']}', ''),

(103, 'english', 'Publications per page', '', '10', 0, '10', '', 'General'),
(104, 'english', 'Publications per page in RSS', '', '30', 0, '30', '', ''),
(105, 'english', 'Adding news by users', '', '1', 0, '1', '', ''),
(106, 'english', 'Display the contents of subcategories when viewing category', '', '1', 0, '1', '', ''),
(107, 'english', 'Enable ping', 'Notification search engines about updating on the site', '1', 0, '1', '', ''),
(108, 'english', 'Enable rating', '', '1', 0, '1', '', 'Rating options'),
(109, 'english', 'Score only in the detailed view?', 'Allow to assess publication only when they are detailed viewing?', '0', 0, '0', '', ''),
(110, 'english', 'Rating users only', 'When this option is on, rate news can only authorized users and only 1 time.', '0', 0, '0', '', ''),
(111, 'english', 'Period between marks in days', 'If rate can not only users but also the guests, this option specifies the time after which guests will be able to remark.', '3', 0, '3', '', ''),
(112, 'english', 'Low negative mark', 'Value can not be greater than zero. To turn off the negative marks, enter 0.', '-3', 0, '-3', '', ''),
(113, 'english', 'The maximum positive mark', 'Value can not be below zero. To disable the positive ratings, please enter 0.', '3', 0, '3', '', '')
QUERY;
#[E] English

$insert['errors']=<<<QUERY
INSERT INTO `{$prefix}errors` VALUES
(1, 404, 'warning.png', '', 1),
(2, 403, 'hand.png', '', 1)
QUERY;

#Russian
if($rus)
	$insert['errors_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}errors_l` VALUES
(1, 'russian', '404', 'Страница не найдена', 'Страница, которую Вы запросили, не существует либо она временно не доступна.<br /><br />Возможно, вы перешли по устаревшей ссылке с другой страницы или случайно ошиблись, набирая адрес вручную.','','',NOW()),
(2, 'russian', '403', 'Доступ запрещен', 'Вам запрещен доступ к этой странице!','','',NOW())
QUERY;
#[E] Russian

#English
if($eng)
	$insert['errors_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}errors_l` VALUES
(1, 'english', '404', 'Page not found', 'The page you have requested does not exist or is temporarily unavailable.','','',NOW()),
(2, 'english', '403', 'Access denied', 'You haven''t permisson to visit this page!','','',NOW())
QUERY;
#[E] English

#Ukrainian
if($ukr)
	$insert['errors_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}errors_l` VALUES
(1, 'ukrainian', '404', 'Сторінку не знайдено', 'Сторінка, яку Ви викликали, не існує або вона тимчасово не доступна.<br /><br />Можливо, ви перейшли по застарілому посиланню з іншої сторінки або випадково помилилися, набираючи адресу вручну.','','',NOW()),
(2, 'ukrainian', '403', 'Доступ заборонено', 'Вам заборонений доступ до цієї сторінки!','','',NOW())
QUERY;
#[E] Ukrainian

$insert['mainpage']=<<<QUERY
INSERT INTO `{$prefix}mainpage` VALUES (1,1)
QUERY;

$insert['menu']=<<<QUERY
INSERT INTO `{$prefix}menu` (`id`,`pos`,`parents`,`in_map`,`status`) VALUES
(1, 1, '', 1, 1),
(2, 2, '', 1, 1),
(3, 3, '', 1, 1),
(4, 4, '', 1, 1),
(5, 5, '', 1, 1),
(6, 6, '', 1, 1),
(7, 7, '', 1, 1),
(8, 1, '7,', 1, 1),
(9, 2, '7,', 1, 1)
QUERY;

#Russian
if($rus)
	$insert['menu_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}menu_l` (`id`, `language`, `title`, `url`, `eval_url`, `params`) VALUES
(1, 'russian', 'Личный кабинет', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''аккаунт''),false,false);', ''),
(2, 'russian', 'Новости', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''новости''),false,false);', ''),
(3, 'russian', 'Поиск', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''поиск''),false);', ' rel="search"'),
(4, 'russian', 'Карта сайта', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''карта сайта''),false);', ''),
(5, 'russian', 'Информация', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''страницы''),false,false);', ''),
(6, 'russian', 'Обратная связь', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''обратная связь''),false);', ' rel="contact"'),
(7, 'russian', 'Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(8, 'russian', 'Официальный сайт Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(9, 'russian', 'Форум поддержки', 'http://eleanor-cms.ru/%D1%84%D0%BE%D1%80%D1%83%D0%BC/', '', '')
QUERY;
#[E] Russian

#English
if($eng)
	$insert['menu_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}menu_l` (`id`, `language`, `title`, `url`, `eval_url`, `params`) VALUES
(1, 'english', 'Personal cabinet', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''account''),false,false);', ''),
(2, 'english', 'News', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''news''),false,false);', ''),
(3, 'english', 'Search', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''search''),false);', ' rel="search"'),
(4, 'english', 'Sitemap', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''sitemap''),false);', ''),
(5, 'english', 'Information', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''pages''),false,false);', ''),
(6, 'english', 'Contacts', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''contacts''),false);', ' rel="contact"'),
(7, 'english', 'Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(8, 'english', 'Official site Eleanor CMS', 'http://eleanor-cms.ru/eng/', '', ''),
(9, 'english', 'Supporting forum', 'http://eleanor-cms.ru/eng/forum/', '', '')
QUERY;
#[E] English

#Ukrainian
if($ukr)
	$insert['menu_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}menu_l` (`id`, `language`, `title`, `url`, `eval_url`, `params`) VALUES
(1, 'ukrainian', 'Особистий кабінет', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''аккаунт''),false,false);', ''),
(2, 'ukrainian', 'Новини', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''новини''),false,false);', ''),
(3, 'ukrainian', 'Пошук', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''пошук''),false);', ' rel="search"'),
(4, 'ukrainian', 'Мапа сайту', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''мапа сайту''),false);', ''),
(5, 'ukrainian', 'Інформація', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''сторінки''),false,false);', ''),
(6, 'ukrainian', 'Зворотній зв''язок', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''зворотній зв\\\\''язок''),false);', ' rel="contact"'),
(7, 'ukrainian', 'Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(8, 'ukrainian', 'Офіційний сайт Eleanor CMS', 'http://eleanor-cms.ru/%D1%83%D0%BA%D1%80/', '', ''),
(9, 'ukrainian', 'Форум підтримки', 'http://eleanor-cms.ru/%D1%83%D0%BA%D1%80/%D1%84%D0%BE%D1%80%D1%83%D0%BC/', '', '')
QUERY;
#[E]Ukrainian

$ser=array(
	1=>array(
		serialize(array(
			'news'=>array(
				'russian'=>array('новости','news'),#Russian
				'english'=>array('news'),#English
				'ukrainian'=>array('новини','news'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Новости',#Russian
			'english'=>'News',#English
			'ukrainian'=>'Новини',#Ukrainian
		)),
		serialize(array(
			'russian'=>'Управление новостями Вашего сайта',#Russian
			'english'=>'Management news your site',#English
			'ukrainian'=>'Керування новинами Вашого сайту',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'static'=>array(
				'russian'=>array('страницы','pages'),#Russian
				'english'=>array('pages'),#English
				'ukrainian'=>array('сторінки','pages'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Статические страницы',#Russian
			'english'=>'Static pages',#English
			'ukrainian'=>'Статичні сторінки',#Ukrainian
		)),
		serialize(array(
			'russian'=>'Модуль для создания статических страниц',#Russian
			'english'=>'Module for configurating static pages',#English
			'ukrainian'=>'Модуль для створення статичних сторінок',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'section'=>array(
				''=>array('_mainpage'),
			),
		)),
		serialize(array(
			'russian'=>'Главная страница',#Russian
			'english'=>'Main page',#English
			'ukrainian'=>'Головна сторінка',#Ukrainian
		)),
		serialize(array(
			'russian'=>'Конструктор главной страницы сайта',#Russian
			'english'=>'Constructor homepage site',#English
			'ukrainian'=>'Конструктор головної сторінки сайту',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'errors'=>array(
				'russian'=>array('ошибки','errors'),#Russian
				'english'=>array('errors'),#English
				'ukrainian'=>array('помилки','errors'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Страницы ошибок',#Russian
			'english'=>'Error pages',#English
			'ukrainian'=>'Сторінки помилок',#Ukrainian
		)),
		serialize(array(
			'russian'=>'Настройка страниц ошибок Вашего сайта (404,403,...)',#Russian
			'english'=>'Configuring error pages your site (404,403, etc...)',#English
			'ukrainian'=>'Налаштування сторінок помилок Вашого сайту (404,403,...)',#Ukrainian
		)),
	),
	array(
		str_replace('\'','\'\'',serialize(array(
			'contacts'=>array(
				'russian'=>array('обратная связь','contacts'),#Russian
				'english'=>array('contacts'),#English
				'ukrainian'=>array('зворотній зв\'язок','contacts'),#Ukrainian
			),
		))),
		serialize(array(
			'russian'=>'Обратная связь',#Russian
			'english'=>'Feedback',#English
			'ukrainian'=>'Зворотній зв&#039;язок',#Ukrainian
		)),
		serialize(array(
			'russian'=>'Настройка обратной связи',#Russian
			'english'=>'Settings of feedback',#English
			'ukrainian'=>'Налаштування зворотнього зв&#039;язку',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'search'=>array(
				'russian'=>array('поиск','search'),#Russian
				'english'=>array('search'),#English
				'ukrainian'=>array('пошук','search'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Google поиск',#Russian
			'english'=>'Google search',#English
			'ukrainian'=>'Google пошук',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'menu'=>array(
				'russian'=>array('карта сайта','меню','menu','sitemap'),#Russian
				'english'=>array('sitemap','menu'),#English
				'ukrainian'=>array('мапа сайту','меню','menu','sitemap'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Меню сайта',
			'english'=>'Menu',#English
			'ukrainian'=>'Меню сайту',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'account'=>array(
				'russian'=>array('аккаунт','account'),#Russian
				'english'=>array('account'),#English
				'ukrainian'=>array('аккаунт','account'),#Ukrainian
			),
			'groups'=>array(
				'russian'=>array('группы','groups'),#Russian
				'english'=>array('groups'),#English
				'ukrainian'=>array('групи','groups'),#Ukrainian
			),
			'user'=>array(
				'russian'=>array('пользователь','user'),#Russian
				'english'=>array('user'),#English
				'ukrainian'=>array('user','користувач'),#Ukrainian
			),
			'online'=>array(
				'russian'=>array('кто-онлайн','online'),#Russian
				'english'=>array('online'),#English
				'ukrainian'=>array('хто-онлайн','online'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Аккаунт пользователя',#Russian
			'english'=>'User account',#English
			'ukrainian'=>'Аккаунт користувача',#Ukrainian
		)),
	),
	array(
		serialize(array(
			'context'=>array(
				'russian'=>array('контекстные ссылки','context links'),#Russian
				'english'=>array('context links'),#English
				'ukrainian'=>array('контекстні посилання','context links'),#Ukrainian
			),
		)),
		serialize(array(
			'russian'=>'Контекстные ссылки',#Russian
			'english'=>'Сontext links',#English
			'ukrainian'=>'Контекстні посилання',#Ukrainian
		)),
	),
);
$insert['modules']=<<<QUERY
INSERT INTO `{$prefix}modules` (`services`,`sections`,`title_l`,`descr_l`,`protected`,`path`,`multiservice`,`file`,`files`,`image`,`active`,`api`) VALUES
(',ajax,,admin,,cron,,user,,rss,,xml,', '{$ser[1][0]}', '{$ser[1][1]}', '{$ser[1][2]}', 0, 'modules/news', 1, 'index.php', 'a:4:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";s:4:"ajax";s:8:"ajax.php";s:3:"rss";s:7:"rss.php";}', 'news-*.png', 1, 'api.php'),
(',admin,,user,,rss,,download,', '{$ser[2][0]}', '{$ser[2][1]}', '{$ser[2][2]}', 1, 'modules/static', 1, 'index.php', '', 'static-*.png', 1, 'api.php'),
(',admin,,user,', '{$ser[3][0]}', '{$ser[3][1]}', '{$ser[3][2]}', 1, 'modules/mainpage', 1, 'index.php', '', 'mainpage-*.png', 1, ''),
(',admin,,user,', '{$ser[4][0]}', '{$ser[4][1]}', '{$ser[4][2]}', 1, 'modules/errors', 1, 'index.php', '', 'errors-*.png', 1, 'api.php'),
(',admin,,user,', '{$ser[5][0]}', '{$ser[5][1]}', '{$ser[5][2]}', 0, 'modules/contacts', 1, 'index.php', 'a:2:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";}', 'contacts-*.png', 1, ''),
(',admin,,user,,xml,', '{$ser[6][0]}', '{$ser[6][1]}', 'a:0:{}', 0, 'modules/search', 0, 'index.php', 'a:3:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";s:3:"xml";s:7:"xml.php";}', '', 1, ''),
(',admin,,user,', '{$ser[7][0]}', '{$ser[7][1]}', 'a:0:{}', 0, 'modules/menu', 1, 'index.php', 'a:2:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";}', 'menu-*.png', 1, ''),
(',admin,,user,,ajax,', '{$ser[8][0]}', '{$ser[8][1]}', 'a:0:{}', 0, 'modules/account', 1, 'index.php', 'a:3:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";s:4:"ajax";s:8:"ajax.php";}', 'account-*.png', 1, 'api.php'),
(',admin,', '{$ser[9][0]}', '{$ser[9][1]}', 'a:0:{}', 0, 'modules/context-links', 1, 'index.php', 'a:2:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";}', 'links-*.png', 1, '')
QUERY;

$ndate=date('Y-m-d H:i:s');
$insert['news']=<<<QUERY
INSERT INTO `{$prefix}news` (`id`,`cats`,`date`,`pinned`,`author`,`author_id`,`show_detail`,`show_sokr`,`status`,`voting`) VALUES
(1, ',1,', '{$ndate}' + INTERVAL 1 MONTH, '{$ndate}' + INTERVAL 2 SECOND, 'Eleanor CMS', 0, 1, 1, 1, 1),
(2, ',1,', '{$ndate}' + INTERVAL 1 SECOND, 0, 'Eleanor CMS', 0, 0, 1, 1, 0),
(3, ',1,', '{$ndate}', 0, 'Eleanor CMS', 0, 0, 1, 1, 0)
QUERY;

$insert['news_categories']=<<<QUERY
INSERT INTO `{$prefix}news_categories` (`id`,`image`,`pos`) VALUES (1, 'bomb.png', 1)
QUERY;

#Russian
if($rus)
	$insert['news_categories_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}news_categories_l` (`id`,`language`,`uri`,`title`,`description`,`meta_descr`) VALUES
(1, 'russian', 'наши-новости', 'Наши новости', 'Тестовая категория новостей', 'Новости нашего проекта[page], страница {page}[/page].')
QUERY;
#[E] Russian

#English
if($eng)
	$insert['news_categories_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}news_categories_l` (`id`,`language`,`uri`,`title`,`description`,`meta_descr`) VALUES
(1, 'english', 'our-news', 'Our news', 'News test category', 'News of our project[page], page {page}[/page].')
QUERY;
#[E] English

#Ukrainian
if($ukr)
	$insert['news_categories_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}news_categories_l` (`id`,`language`,`uri`,`title`,`description`,`meta_descr`) VALUES
(1, 'ukrainian', 'наші-новини', 'Наші новини', 'Тестова категорія новин', 'Новини нашого проекту[page], сторінка {page}[/page].')
QUERY;
#[E] Ukrainian

$version=ELEANOR_VERSION;
$insert['news_l']=<<<QUERY
INSERT INTO `{$prefix}news_l` (`id`,`uri`,`lstatus`,`ldate`,`lcats`,`title`,`announcement`,`text`,`last_mod`) VALUES
(1, 'eleanor-cms', 1, '{$ndate}' + INTERVAL 2 SECOND, ',1,', 'Eleanor CMS {$version}', 'Благодарим вас за инсталляцию Eleanor CMS {$version}. Мы надеемся, что работа с Eleanor CMS оставит у вас только положительные эмоции. Если же у вас возникнут какие-либо вопросы, пожелания, или же вы найдёте ошибки в системе, мы всё это с радостью выслушаем на официальном форуме системы <a href="http://forum.eleanor-cms.ru" target="_blank">forum.eleanor-cms.ru</a>', '<br /><br />Демонстрация опроса:', '{$ndate}'),
(2, 'sim-networks-профессиональный-хостинг-для-eleanor-cms', 1, '{$ndate}' + INTERVAL 1 SECOND, ',1,', 'SIM-Networks - профессиональный хостинг для Eleanor CMS', '<div style="text-align:center"><img src="uploads/news/2/sim-networks.png" alt="SIM-Networks" title="SIM-Networks" /></div><br />Компания SIM-Networks является хостинг партнёром системы управления сайтами  Eleanor CMS. Надежность технической площадки, быстрая и безопасная работа, а также компетентная техническая поддержка - вот ключевые моменты, которые следует учитывать  при выборе хостинга для размещения Вашего сайта.<br /><br />Все эти и многие другие преимущества вы получите разместив ваш проект на хостинге компании SIM-Networks, вот некоторые из них:<br /><br /><ul><li>Полная совместимость с Eleanor CMS</li><li>Скидки и специальные акции, связанные с Eleanor CMS</li><li>Размещение в Германии на собственных серверах в <a href="http://www.sim-networks.com/datacenter?pid=98" rel="nofollow" target="_blank">дата-центре премиум класса</a></li><li>Круглосуточная и компетентная техническая <a href="http://www.sim-networks.com/support?pid=98" rel="nofollow" target="_blank">поддержка</a></li><li>Индивидуальные  конфигурации хостинг-услуг для любых задач</li><li>Возможность полного администрирования и сопровождения проектов</li></ul><br />В продолжении подробная информация об услугах и ссылки.', '[html]<br /><br /><strong>Виртуальный хостинг и регистрация доменов</strong><br />Виртуальный хостинг предполагает размещение вашего ресурса на одном из наших мощных и отказоустойчивых серверах совместно с другими клиентами. Отличительная особенность этой услуги - низкая цена и высокая надежность. Данная услуга идеальна для начинающих и развивающихся проектов. В качестве панели управления используется ISPManager.<br>Также мы с удовольствием зарегестрируем для Вас доменные имена в любых из более 300 доменных зон<br /><a href="http://www.sim-networks.com/webhosting/linux?pid=98" rel="nofollow" target="_blank">Подробнее о виртуальном хостинге</a><br /><a href="http://www.sim-networks.com/domain/search?pid=98" rel="nofollow" target="_blank">Подробнее о регистрации доменов</a><br /><br /><strong>Выделенные и виртуальные серверы</strong><br />Виртуальные (VDS) и выделенные серверы - идеальное решение для  размещения средних и крупных проектов, которым необходимы гарантированные ресурсы с возможностью их динамического расширения, индивидуальные настройки и возможность администрирования с полным доступом к операционной системе вашего сервера.<br /><a href="http://www.sim-networks.com/vds/xen?pid=98" rel="nofollow" target="_blank">Подробнее о виртуальных серверах (VDS)</a><br /><a href="http://www.sim-networks.com/dedicated?pid=98" rel="nofollow" target="_blank">Подробнее о выделенных серверах</a><br /><br /><strong>3. Администрирование и мониторинг</strong><br />Если у вас нет времени на администрирование сервера, или Ваши познания в *NIX системах недостаточно глубоки для обеспечения безопасности и надежности работы сервера, то мы с радостью предоставим Вам полное администрирование или разовые услуги по запросу. Сюда входит и круглосуточный мониторинг состояния вашего сервера и резервное копирование данных на внешние хранилища, анализ нагрузки, установка обновлений и многое другое.<br /><a href="http://www.sim-networks.com/support?pid=98" rel="nofollow" target="_blank">Постоянное администрирование и мониторинг</a><br/><br/>Подробнее ознакомиться с нашими технологиями и услугами вы можете посетив <a href="http://www.sim-networks.com/index.php?pid=98" rel="nofollow" target="_blank">наш сайт</a>, на котором Вы найдете в том числе и наши <a href="http://www.sim-networks.com/contact?pid=98" target="_blank">контактные данные</a>, краткий рассказ <a href="http://www.sim-networks.com/about?pid=98" target="_blank">о компании</a>, а также сможете задать интересующие Вас вопросы в вебчате.<br /><span class="small">* Текст предоставлен партнером</span>[/html]', '{$ndate}'),
(3, 'centroarts', 1, '{$ndate}', ',1,', 'Centroarts', '<div style="text-align:center"><img src="uploads/news/3/centroarts.png" alt="Centroarts" title="Centroarts" /></div><br />[html]<p>Партнером по оказанию услуг для системы Eleanor CMS является студия <a href="http://centroarts.com">CENTROARTS.com</a>. Предупреждаем, что при нажатии на ссылку заказать услугу, вы попадете на страницу оформления заказа на сайте centroarts.com.</p>\r\n<p>&nbsp;</p>[/html]', '[html]\r\n<h3>Шаблон оформления.<br /></h3>\r\n<p><img class="left" title="Шаблоны" src="uploads/news/3/ca_template.png" alt="Шаблоны" width="90" height="92" />Разработка уникального шаблона для оформления Eleanor CMS. Шаблон отвечает за то, как будет выглядеть ваш сайт. В шаблоне учитывается расположение и оформление блоков, навигации, поиска, и пр. В шаблоне можно учесть специфичные формы отображения информации, например, главная страница будет отличаться от страниц с контентом. Шаблон не включает в себя разработку структуры сайта, текстов, и т.п. Услуга предполагает собой создание индивидуального образа для вашего сайта на базе Eleanor CMS, валидную верстку HTML+CSS.</p><br />\r\n<h3>Создание сайта на базе Eleanor CMS.<br /></h3>\r\n<img class="left" title="Web-сайт на базе Eleanor CMS" src="uploads/news/3/ca_site.png" alt="Web-сайт на базе Eleanor CMS" width="90" height="92" />Создание сайта на базе Eleanor CMS. Кроме разработки шаблона для сайта, разрабатывается также структура сайта, формируется подробное техническое задание, на основании которого ведется работа по созданию уникальных программных модулей для удовлетворения потребностей клиента в функционировании системы. Eleanor CMS полностью настраивается под конкретную задачу, поставленную заказчиком.<br /><br /><br />\r\n<h3>Разработка скриптов для Eleanor CMS.</h3>\r\n<img class="left" title="Разработка скриптов для Eleanor CMS" src="uploads/news/3/ca_scripts.png" alt="Разработка скриптов для Eleanor CMS" width="90" height="92" />Разработка php-скриптов для Eleanor CMS. PHP-скрипты - это программная часть сайта, которая позволяет расширить функционал вашего сайта. Наиболее распространенные виды скриптов: блоки, модули. Блок - это часть интерфейса (например, автоматическое меню). Модуль - это программная единица для реализации специфических функций на сайте (например, фотогалерея). Разработка PHP скриптов ведется по индивидуальным заказам с учетом всех технических нюансов в соответствии с вашей потребностью.<br /><br /><br />\r\n<h3>Разработка иконок для вашего сайта.</h3>\r\n<img class="left" title="Разработка иконок для сайта" src="uploads/news/3/ca_icons.png" alt="Разработка иконок для сайта" width="90" height="92" />На сегодняшний день иконки являются неотъемлемой частью программ, веб-сайтов, презентаций. Иконка - это наглядное и удобное средство для восприятия информации, поэтому их часто используют в качестве элементов управления для создания удобной и красивой навигации. Разработка иконок ведется в индивидуальном порядке. Возможно создание иконок любой сложности от простых линейно-векторных иконок - до объемных, детально-прорисованных иконок. Распространенные размеры иконок: 16x16px, 32x32px, 48x48px, 64x64px и 128x128px.<br /><p><a href="http://centroarts.com/feedback.html" target="_blank"><strong>Заказать услуги</strong></a></p>\r\n\r\n<ul>\r\n<li>Портфолио работы вы можете посмотреть по адресу: <a href="http://centroarts.com/portfolio/">http://centroarts.com/portfolio.html</a></li>\r\n<li>Подробную информацию об услугах можно посмотреть по адресу: <a href="http://centroarts.com/info/">http://centroarts.com/info.html</a></li>\r\n</ul>\r\n[/html]', '{$ndate}')
QUERY;

$insert['ownbb']=<<<QUERY
INSERT INTO `{$prefix}ownbb` (`pos`,`active`,`handler`,`tags`,`no_parse`,`special`,`sp_tags`,`gr_use`,`gr_see`,`sb`) VALUES
(1, 1, 'url.php', 'url', 0, 0, '', '', '', 0),
(2, 1, 'nobb.php', 'nobb', 1, 0, '', '', '', 0),
(3, 1, 'code.php', 'code', 1, 0, 'csel', '', '', 1),
(4, 1, 'hide.php', 'hide', 0, 0, '', '', '1,4,2,3', 1),
(5, 1, 'quote.php', 'quote', 0, 0, '', '', '', 1),
(6, 1, 'script.php', 'script', 1, 0, '', '1', '', 1),
(7, 1, 'php.php', 'php', 1, 0, '', '1', '', 1),
(8, 1, 'html.php', 'html', 1, 0, '', '1', '', 1),
(9, 1, 'attach.php', 'attach', 1, 0, '', '', '', 0),
(10, 1, 'csel.php', 'csel', 0, 1, '', '', '', 1),
(11, 1, 'onlinevideo.php', 'onlinevideo', 1, 0, '', '', '', 1),
(12, 1, 'spoiler.php', 'spoiler', 0, 0, '', '', '', 1)
QUERY;

$insert['services']=<<<QUERY
INSERT INTO `{$prefix}services` VALUES
('admin', 'admin.php', 1, 'Audora', 'admin'),
('user', 'index.php', 2, 'Uniel', 'base'),
('ajax', 'ajax.php', 1, '', 'no'),
('upload', 'upload.php', 2, '', 'no'),
('download', 'download.php', 2, '', 'no'),
('rss', 'rss.php', 0, '', 'no'),
('cron', 'cron.php', 0, '', 'no'),
('xml', 'xml.php', 0, 'xml', 'no'),
('moder', 'moder.php', 0, '', 'moder');
QUERY;

$insert['smiles']=<<<QUERY
INSERT INTO `{$prefix}smiles` (`path`,`emotion`,`status`,`show`,`pos`) VALUES
('images/smiles/alien.png', ',:alien:,', 1, 1, 0),
('images/smiles/andy.png', ',:andy:,', 1, 1, 1),
('images/smiles/angel.png', ',:angel:,', 1, 1, 2),
('images/smiles/angry.png', ',:angry:,', 1, 1, 3),
('images/smiles/bandit.png', ',:bandit:,', 1, 1, 4),
('images/smiles/blushing.png', ',:blushing:,', 1, 1, 5),
('images/smiles/cool.png', ',:cool:,', 1, 1, 6),
('images/smiles/crying.png', ',:crying:,', 1, 1, 7),
('images/smiles/devil.png', ',:devil:,', 1, 1, 8),
('images/smiles/grin.png', ',:D,', 1, 1, 9),
('images/smiles/happy.png', ',:happy:,', 1, 1, 10),
('images/smiles/heart.png', ',:heart:,', 1, 1, 11),
('images/smiles/joyful.png', ',:joyful:,', 1, 1, 12),
('images/smiles/kissing.png', ',:kissing:,', 1, 1, 13),
('images/smiles/lol.png', ',:lol:,', 1, 1, 14),
('images/smiles/love.png', ',:love:,', 1, 1, 15),
('images/smiles/ninja.png', ',:ninja:,', 1, 1, 16),
('images/smiles/pinched.png', ',:pinched:,', 1, 1, 17),
('images/smiles/policeman.png', ',:policeman:,', 1, 1, 18),
('images/smiles/pouty.png', ',:pouty:,', 1, 1, 19),
('images/smiles/sad.png', ',:sad:,', 1, 1, 20),
('images/smiles/sick.png', ',:sick:,', 1, 1, 21),
('images/smiles/sideways.png', ',:sideways:,', 1, 1, 22),
('images/smiles/sleeping.png', ',:sleeping:,', 1, 1, 23),
('images/smiles/smile.png', ',:),', 1, 1, 24),
('images/smiles/surprised.png', ',:surprised:,', 1, 1, 25),
('images/smiles/tongue.png', ',:tongue:,', 1, 1, 26),
('images/smiles/uncertain.png', ',:uncertain:,', 1, 1, 27),
('images/smiles/unsure.png', ',:unsure:,', 1, 1, 28),
('images/smiles/w00t.png', ',:w00t:,', 1, 1, 29),
('images/smiles/whistling.png', ',:whistling:,', 1, 1, 30),
('images/smiles/wink.png', ',:wink:,', 1, 1, 31),
('images/smiles/wizard.png', ',:wizard:,', 1, 1, 32),
('images/smiles/wondering.png', ',:wondering:,', 1, 1, 33)
QUERY;

$insert['upgrade_hist']="INSERT INTO `{$prefix}upgrade_hist` VALUES (1, '".ELEANOR_VERSION."', NOW(), '".ELEANOR_BUILD."', 1, 'Install')";
$insert['users_site']="INSERT INTO `{$prefix}users_site` (`id`) VALUES (0)";

$ser=array(
	1=>serialize(array(
		'russian'=>'Дневная очистка',#Russian
		'english'=>'Daytime cleaning',#English
		'ukrainian'=>'Щоденна очистка',#Ukrainian
	)),
	serialize(array(
		'russian'=>'Дневной ping',#Russian
		'english'=>'Daytime ping',#English
		'ukrainian'=>'Щоденний ping',#Ukrainian
	)),
	serialize(array(
		'russian'=>'Информер',#Russian
		'english'=>'Informer',#English
		'ukrainian'=>'Інформер',#Ukrainian
	)),
);

$dateo=date_offset_get(date_create());
$insert['tasks']=<<<QUERY
INSERT INTO `{$prefix}tasks` (`task`, `title_l`, `name`, `free`, `ondone`, `status`, `run_year`, `run_month`, `run_day`, `run_hour`, `run_minute`, `run_second`, `do`) VALUES
('mainclean.php', '{$ser[1]}', 'mainclean', 1, 'deactivate', 1, '*', '*', '*', '0', '0', '0', {$dateo}),
('ping.php', '{$ser[2]}', 'ping', 1, 'deactivate', 1, '*', '*', '*', '0', '0', '0', {$dateo}),
('informer.php', '{$ser[3]}', 'informer', 1, 'deactivate', 1, '*', '*', '*', '0', '0', '0', {$dateo});
QUERY;

$insert['voting']="INSERT INTO `{$prefix}voting` (`id`,`begin`,`end`,`onlyusers`,`againdays`,`votes`) VALUES (1,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,10,0)";

$insert['voting_q']="INSERT INTO `{$prefix}voting_q` (`id`,`qid`,`multiple`,`maxans`,`answers`) VALUES
(1,0,0,2,'a:3:{i:0;i:0;i:1;i:0;i:2;i:0;}'),
(1,1,1,2,'a:3:{i:0;i:0;i:1;i:0;i:2;i:0;}')";

#Russian
if($rus)
{
	$ser=array(
		serialize(array('Вариант 1','Вариант 2','Вариант 3')),
		serialize(array('Вариант - 1','Вариант - 2','Вариант - 3')),
	);
	$insert['voting_q_l(rus)']="INSERT INTO `{$prefix}voting_q_l` (`id`, `qid`, `language`, `title`, `variants`) VALUES
(1, 0, 'russian', 'Вопрос с одиночным ответом', '{$ser[0]}'),
(1, 1, 'russian', 'Вопрос с множественными ответами', '{$ser[1]}')";
};
#[E] Russian

#English
if($eng)
{
	$ser=array(
		serialize(array('Variant 1','Variant 2','Variant 3')),
		serialize(array('Variant - 1','Variant - 2','Variant - 3')),
	);
	$insert['voting_q_l(eng)']="INSERT INTO `{$prefix}voting_q_l` (`id`, `qid`, `language`, `title`, `variants`) VALUES
(1, 0, 'english', 'Question with single answer', '{$ser[0]}'),
(1, 1, 'english', 'Question with multiple answers', '{$ser[1]}')";
};
#[E] English

#Ukrainian
if($ukr)
{
	$ser=array(
		serialize(array('Варіант 1','Варіант 2','Варіант 3')),
		serialize(array('Варіант - 1','Варіант - 2','Варіант - 3')),
	);
	$insert['voting_q_l(ukr)']="INSERT INTO `{$prefix}voting_q_l` (`id`, `qid`, `language`, `title`, `variants`) VALUES
(1, 0, 'ukrainian', 'Питання з одиночною відповіддю', '{$ser[0]}'),
(1, 1, 'ukrainian', 'Питання з множинними відповідями', '{$ser[1]}')";
};
#[E] Ukrainian