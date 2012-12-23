<?php
$tables[]='SET FOREIGN_KEY_CHECKS=0;';

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks`";
$tables['blocks']="
CREATE TABLE `{$prefix}blocks` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`ctype` enum('text','file') NOT NULL,
`file` tinytext NOT NULL,
`user_groups` varchar(30) NOT NULL,
`showfrom` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`showto` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`textfile` tinyint(4) NOT NULL,
`template` varchar(20) NOT NULL,
`notemplate` tinyint(4) NOT NULL,
`vars` text NOT NULL,
`status` tinyint(4) NOT NULL,
PRIMARY KEY (`id`),
KEY `showfrom` (`status`,`showfrom`),
KEY `showto` (`status`,`showto`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks_groups`";
$tables['blocks_groups']="
CREATE TABLE `{$prefix}blocks_groups` (
`id` mediumint(8) unsigned NOT NULL,
`blocks` text NOT NULL,
`places` text NOT NULL,
`extra` text NOT NULL,
PRIMARY KEY (`id`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}blocks_ids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks_ids`";
$tables['blocks_ids']="
CREATE TABLE `{$prefix}blocks_ids` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`service` varchar(10) NOT NULL,
`title_l` text NOT NULL,
`code` text NOT NULL,
PRIMARY KEY (`id`),
KEY `service` (`service`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks_l`";
$tables['blocks_l']="
CREATE TABLE `{$prefix}blocks_l` (
`id` mediumint(8) unsigned NOT NULL,
`language` varchar(15) NOT NULL,
`title` tinytext NOT NULL,
`text` text NOT NULL,
`config` text NOT NULL,
PRIMARY KEY (`id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}cache`";
$tables['cache']="
CREATE TABLE `{$prefix}cache` (
`key` varchar(30) NOT NULL,
`value` text NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}comments`";
$tables['comments']="
CREATE TABLE `{$prefix}comments` (
`id` mediumint unsigned NOT NULL auto_increment,
`module` smallint unsigned NOT NULL,
`contid` varchar(15) NOT NULL,
`status` tinyint NOT NULL,
`sortdate` timestamp NOT NULL default '0000-00-00 00:00:00',
`parent` mediumint unsigned NOT NULL,
`parents` varchar(30) NOT NULL,
`answers` SMALLINT UNSIGNED NOT NULL,
`date` timestamp NOT NULL default '0000-00-00 00:00:00',
`author_id` mediumint unsigned NOT NULL,
`author` varchar(25) NOT NULL,
`ip` varchar(39) NOT NULL,
`text` text NOT NULL,
PRIMARY KEY (`id`),
KEY `module` (`module`,`contid`,`status`,`sortdate`,`parents`),
KEY `parents` (`parents`),
KEY `status` (`status`),
FOREIGN KEY (`author_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config`";
$tables['config']="
CREATE TABLE `{$prefix}config` (
`id` smallint unsigned NOT NULL auto_increment,
`group` smallint unsigned NOT NULL,
`type` varchar(15) NOT NULL,
`name` varchar(50) NOT NULL,
`protected` tinyint unsigned NOT NULL,
`pos` smallint unsigned NOT NULL,
`multilang` tinyint unsigned NOT NULL,
`eval_load` MEDIUMTEXT NOT NULL,
`eval_save` MEDIUMTEXT NOT NULL,
PRIMARY KEY  (`id`),
UNIQUE KEY `name` (`name`,`group`),
KEY `group` (`group`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config_l`";
$tables['config_l']="
CREATE TABLE `{$prefix}config_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`title` tinytext NOT NULL,
`descr` text NOT NULL,
`value` text NOT NULL,
`serialized` tinyint NOT NULL,
`default` text NOT NULL,
`extra` text NOT NULL,
`startgroup` varchar(100) NOT NULL,
PRIMARY KEY  (`id`,`language`),
FULLTEXT KEY `title` (`title`,`descr`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config_groups`";
$tables['config_groups']="
CREATE TABLE `{$prefix}config_groups` (
`id` smallint unsigned NOT NULL auto_increment,
`name` varchar(50) NOT NULL,
`protected` tinyint unsigned NOT NULL,
`keyword` varchar(100) NOT NULL,
`pos` smallint unsigned NOT NULL,
PRIMARY KEY  (`id`),
UNIQUE KEY `name` (`name`),
KEY `keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config_groups_l`";
$tables['config_groups_l']="
CREATE TABLE `{$prefix}config_groups_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`title` tinytext NOT NULL,
`descr` text NOT NULL,
PRIMARY KEY (`id`,`language`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}config_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}confirmation`";
$tables['confirmation']="
CREATE TABLE `{$prefix}confirmation` (
`id` mediumint unsigned NOT NULL auto_increment,
`hash` varchar(32) NOT NULL,
`user` mediumint unsigned default NULL,
`op` varchar(20) default NULL,
`date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`expire` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`data` text NOT NULL,
PRIMARY KEY  (`id`),
UNIQUE KEY `op` (`op`,`user`),
KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}context_links`";
$tables['context_links']="
CREATE TABLE `{$prefix}context_links` (
`id` smallint unsigned NOT NULL auto_increment,
`date_from` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`date_till` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`status` tinyint NOT NULL,
PRIMARY KEY  (`id`),
KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}context_links_l`";
$tables['context_links_l']="
CREATE TABLE `{$prefix}context_links_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`from` varchar(200) NOT NULL,
`regexp` tinyint NOT NULL,
`to` varchar(200) NOT NULL,
`url` varchar(200) NOT NULL,
`eval_url` varchar(200) NOT NULL,
`params` varchar(200) NOT NULL,
PRIMARY KEY (`id`,`language`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}context_links` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}drafts`";
$tables['drafts']="
CREATE TABLE `{$prefix}drafts` (
`key` varchar(50) NOT NULL,
`value` mediumtext NOT NULL,
`date` timestamp NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY (`key`),
KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}errors`";
$tables['errors']="
CREATE TABLE `{$prefix}errors` (
`id` smallint unsigned NOT NULL auto_increment,
`http_code` smallint unsigned NOT NULL,
`image` varchar(50) NOT NULL,
`mail` varchar(50) NOT NULL,
`log` tinyint unsigned NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}errors_l`";
$tables['errors_l']="
CREATE TABLE `{$prefix}errors_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`uri` varchar(100) NOT NULL,
`title` tinytext NOT NULL,
`text` mediumtext NOT NULL,
`meta_title` TINYTEXT NOT NULL,
`meta_descr` TINYTEXT NOT NULL,
`last_mod` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (`id`,`language`),
KEY `uri` (`uri`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}errors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}groups`";
$tables['groups']="
CREATE TABLE `{$prefix}groups` (
`id` smallint unsigned NOT NULL auto_increment,
`parents` VARCHAR(20) NOT NULL,
`title_l` text NOT NULL,
`html_pref` varchar(150) NULL,
`html_end` varchar(150) NULL,
`descr_l` text NULL,
`protected` tinyint unsigned NULL default '0',
`access_cp` tinyint unsigned NULL default '0',
`max_upload` int unsigned NULL,
`captcha` tinyint unsigned NULL,
`moderate` tinyint unsigned NULL,
`banned` tinyint unsigned NULL,
`flood_limit` smallint unsigned NULL,
`search_limit` smallint unsigned NULL,
`sh_cls` tinyint NULL,
PRIMARY KEY (`id`),
INDEX (`parents`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}mainpage`";
$tables['mainpage']="
CREATE TABLE `{$prefix}mainpage` (
`id` smallint UNSIGNED NOT NULL,
`pos` tinyint UNSIGNED NOT NULL,
PRIMARY KEY (`id`),
KEY `pos` (`pos`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}menu`";
$tables['menu']="
CREATE TABLE `{$prefix}menu` (
`id` smallint unsigned NOT NULL auto_increment,
`pos` tinyint unsigned NOT NULL,
`parents` varchar(50) NOT NULL,
`in_map` tinyint unsigned NOT NULL,
`status` tinyint NOT NULL,
PRIMARY KEY  (`id`),
KEY `parents` (`parents`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}menu_l`";
$tables['menu_l']="
CREATE TABLE `{$prefix}menu_l` (
`id` smallint UNSIGNED NOT NULL,
`language` VARCHAR( 15 ) NOT NULL DEFAULT '',
`title` tinytext NOT NULL,
`url` tinytext NOT NULL,
`eval_url` tinytext NOT NULL,
`params` tinytext NOT NULL,
PRIMARY KEY `id` (`id`,`language`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}modules`";
$tables['modules']="
CREATE TABLE `{$prefix}modules` (
`id` smallint unsigned NOT NULL auto_increment,
`services` varchar(100) NOT NULL,
`sections` text NOT NULL,
`title_l` text NOT NULL,
`descr_l` text NOT NULL,
`protected` tinyint unsigned NOT NULL default '0',
`path` varchar(100) NOT NULL,
`multiservice` tinyint unsigned NOT NULL default '0',
`file` varchar(50) NOT NULL,
`files` text NOT NULL,
`image` varchar(50) NOT NULL,
`active` tinyint NOT NULL default '0',
`api` varchar(50) NOT NULL,
PRIMARY KEY (`id`),
INDEX (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}multisite_jump`";
$tables['multisite_jump']="
CREATE TABLE `{$prefix}multisite_jump` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`type` enum('in','out') NOT NULL,
`signature` varchar(32) NOT NULL,
`expire` timestamp NOT NULL default '0000-00-00 00:00:00',
`uid` mediumint(8) unsigned NOT NULL,
`name` varchar(25) NOT NULL,
PRIMARY KEY  (`id`),
KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news`";
$tables['news']="
CREATE TABLE `{$prefix}news` (
`id` mediumint unsigned NOT NULL auto_increment,
`cats` varchar(100) NOT NULL,
`date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`enddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`pinned` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`author` varchar(25) NOT NULL,
`author_id` mediumint unsigned NOT NULL,
`show_detail` tinyint NOT NULL,
`show_sokr` tinyint NOT NULL,
`r_total` smallint unsigned NOT NULL,
`r_average` float(5,2) NOT NULL,
`r_sum` smallint NOT NULL,
`status` tinyint NOT NULL,
`reads` mediumint unsigned NOT NULL,
`comments` smallint unsigned NOT NULL,
`tags` tinytext NOT NULL,
`voting` MEDIUMINT UNSIGNED NOT NULL,
PRIMARY KEY  (`id`),
KEY `status` (`status`,`date`,`pinned`),
KEY `enddate` (`enddate`),
KEY `author_id` (`author_id`),
KEY `voting` (`status`,`voting`),
FOREIGN KEY (`author_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_l`";
$tables['news_l']="
CREATE TABLE `{$prefix}news_l` (
`id` mediumint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`uri` varchar(100) NOT NULL,
`lstatus` tinyint NOT NULL,
`ldate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`lcats` varchar(100) NOT NULL,
`title` tinytext NOT NULL,
`announcement` text NOT NULL,
`text` mediumtext NOT NULL,
`meta_title` tinytext NOT NULL,
`meta_descr` tinytext NOT NULL,
`last_mod` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY  (`id`,`language`),
KEY `uri` (`uri`),
KEY `lstatus` (`lstatus`,`ldate`,`lcats`),
FULLTEXT KEY `title` (`title`,`announcement`,`text`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_categories`";
$tables['news_categories']="
CREATE TABLE `{$prefix}news_categories` (
`id` smallint unsigned NOT NULL auto_increment,
`parent` smallint unsigned NOT NULL default '0',
`parents` varchar(100) NOT NULL,
`image` varchar(100) NOT NULL,
`pos` smallint unsigned NOT NULL,
PRIMARY KEY  (`id`),
KEY `parents` (`parents`),
KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_categories_l`";
$tables['news_categories_l']="
CREATE TABLE `{$prefix}news_categories_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`uri` varchar(100) NOT NULL,
`title` tinytext NOT NULL,
`description` text NOT NULL,
`meta_title` TINYTEXT NOT NULL,
`meta_descr` TINYTEXT NOT NULL,
PRIMARY KEY (`id`,`language`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}news_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_rt`";
$tables['news_rt']="
CREATE TABLE `{$prefix}news_rt` (
`id` mediumint unsigned NOT NULL,
`tag` mediumint unsigned NOT NULL,
PRIMARY KEY (`id`,`tag`),
KEY `tag` (`tag`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`tag`) REFERENCES `{$prefix}news_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_tags`";
$tables['news_tags']="
CREATE TABLE `{$prefix}news_tags` (
`id` mediumint unsigned NOT NULL auto_increment,
`language` varchar(15) NOT NULL,
`name` varchar(50) NOT NULL,
`cnt` smallint unsigned NOT NULL,
PRIMARY KEY  (`id`),
UNIQUE KEY `name` (`language`,`name`),
KEY `cnt` (`cnt`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}ownbb`";
$tables['ownbb']="CREATE TABLE `{$prefix}ownbb` (
`id` smallint unsigned NOT NULL auto_increment,
`pos` smallint unsigned NOT NULL,
`active` tinyint NOT NULL,
`handler` varchar(50) NOT NULL,
`tags` varchar(100) NOT NULL,
`no_parse` tinyint unsigned NOT NULL,
`special` tinyint unsigned NOT NULL,
`sp_tags` varchar(250) NOT NULL,
`gr_use` varchar(250) NOT NULL,
`gr_see` varchar(250) NOT NULL,
`sb` tinyint NOT NULL,
PRIMARY KEY  (`id`),
KEY `pos` (`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}ping`";
$tables['ping']="CREATE TABLE `{$prefix}ping` (
`id` varchar(20) NOT NULL,
`pinged` tinyint(4) NOT NULL,
`date` timestamp NOT NULL default '0000-00-00 00:00:00',
`result` tinytext NOT NULL,
`services` tinytext NOT NULL,
`exclude` tinytext NOT NULL,
`method` varchar(30) NOT NULL,
`site` tinytext NOT NULL COMMENT 'ping page',
`main` tinytext NOT NULL COMMENT 'ping page',
`changes` tinytext NOT NULL COMMENT 'ping page',
`rss` tinytext NOT NULL COMMENT 'ping page',
`categories` tinytext NOT NULL COMMENT 'ping',
PRIMARY KEY  (`id`),
KEY `pinged` (`pinged`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}qmenu`";
$tables['qmenu']="
CREATE TABLE `{$prefix}qmenu` (
`id` smallint unsigned NOT NULL auto_increment,
`type` varchar(10) NOT NULL,
`uid` mediumint unsigned NOT NULL,
`pos` smallint unsigned NOT NULL,
`mid` smallint unsigned NOT NULL,
`lid` tinyint unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `aid` (`uid`,`type`,`pos`),
FOREIGN KEY (`uid`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}services`";
$tables['services']="
CREATE TABLE `{$prefix}services` (
`name` varchar(10) NOT NULL,
`file` varchar(30) NOT NULL,
`protected` tinyint UNSIGNED NOT NULL,
`theme` varchar(30) NOT NULL,
`login` varchar(30) NOT NULL,
PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}sessions`";
$tables['sessions']="
CREATE TABLE `{$prefix}sessions` (
`type` enum('user','guest','bot') NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`enter` timestamp NOT NULL default '0000-00-00 00:00:00',
`expire` timestamp NOT NULL default '0000-00-00 00:00:00',
`ip_guest` varchar(39) NOT NULL,
`ip_user` varchar(39) NOT NULL,
`info` text NOT NULL,
`service` varchar(10) NOT NULL,
`browser` varchar(200) NOT NULL,
`location` varchar(150) NOT NULL,
`name` varchar(25) NOT NULL,
`extra` varchar(100) NOT NULL,
PRIMARY KEY  (`ip_guest`,`user_id`,`service`),
KEY `expire` (`expire`),
KEY `st` (`service`,`type`),
KEY `se` (`service`,`expire`),
FOREIGN KEY (`user_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}sitemaps`";
$tables['sitemaps']="
CREATE TABLE `{$prefix}sitemaps` (
`id` smallint unsigned NOT NULL auto_increment,
`title_l` tinytext NOT NULL,
`modules` varchar(100) NOT NULL,
`taskid` smallint unsigned NOT NULL,
`total` mediumint NOT NULL,
`already` mediumint NOT NULL,
`file` varchar(50) NOT NULL,
`compress` tinyint NOT NULL,
`per_time` mediumint unsigned NOT NULL,
`fulllink` tinyint NOT NULL,
`sendservice` tinytext NOT NULL,
`status` tinyint NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}smiles`";
$tables['smiles']="
CREATE TABLE `{$prefix}smiles` (
`id` smallint unsigned NOT NULL auto_increment,
`path` tinytext NOT NULL,
`emotion` varchar(50) NOT NULL,
`status` tinyint NOT NULL,
`show` tinyint NOT NULL,
`pos` smallint unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `emotion` (`emotion`),
KEY `status` (`status`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}static`";
$tables['static']="
CREATE TABLE `{$prefix}static` (
`id` smallint unsigned NOT NULL auto_increment,
`parents` varchar(100) NOT NULL,
`pos` smallint unsigned NOT NULL,
`status` tinyint NOT NULL,
PRIMARY KEY (`id`),
KEY `parents` (`parents`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}static_l`";
$tables['static_l']="
CREATE TABLE `{$prefix}static_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL default '',
`uri` varchar(100) NOT NULL,
`title` tinytext NOT NULL,
`text` MEDIUMTEXT NOT NULL,
`meta_title` TINYTEXT NOT NULL,
`meta_descr` TINYTEXT NOT NULL,
`last_mod` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (`id`,`language`),
KEY `uri` (`uri`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}static` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}spam`";
$tables['spam']="
CREATE TABLE `{$prefix}spam` (
`id` smallint unsigned NOT NULL auto_increment,
`sent` mediumint unsigned NOT NULL,
`total` mediumint unsigned NOT NULL,
`per_run` smallint unsigned NOT NULL,
`taskid` smallint unsigned NOT NULL,
`finame` varchar(25) NOT NULL,
`finamet` enum('b','e','c','m') NOT NULL,
`figroup` varchar(100) NOT NULL,
`figroupt` enum('and','or') NOT NULL,
`fiip` varchar(79) NOT NULL,
`firegisterb` date NOT NULL,
`firegistera` date NOT NULL,
`filastvisitb` date NOT NULL,
`filastvisita` date NOT NULL,
`figender` tinyint NOT NULL,
`fiemail` varchar(50) NOT NULL,
`fiids` varchar(10) NOT NULL,
`deleteondone` tinyint NOT NULL,
`status` enum('stopped','runned','paused','finished') NOT NULL,
`statusdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}spam_l`";
$tables['spam_l']="
CREATE TABLE `{$prefix}spam_l` (
`id` smallint unsigned NOT NULL,
`language` varchar(15) NOT NULL,
`innertitle` tinytext NOT NULL,
`title` tinytext NOT NULL,
`text` text NOT NULL,
PRIMARY KEY  (`id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}tasks`";
$tables['tasks']="
CREATE TABLE `{$prefix}tasks` (
`id` smallint unsigned NOT NULL auto_increment,
`task` varchar(50) NOT NULL,
`title_l` tinytext NOT NULL,
`name` varchar(30) NOT NULL,
`options` MEDIUMTEXT NOT NULL,
`free` tinyint NOT NULL DEFAULT 1 COMMENT 'not runned',
`locked` tinyint NOT NULL,
`nextrun` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`lastrun` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`ondone` varchar(20) NOT NULL,
`maxrun` smallint unsigned NOT NULL,
`alreadyrun` smallint unsigned NOT NULL,
`status` tinyint NOT NULL,
`run_year` varchar(30) NOT NULL,
`run_month` varchar(30) NOT NULL,
`run_day` varchar(30) NOT NULL,
`run_hour` varchar(30) NOT NULL,
`run_minute` varchar(30) NOT NULL,
`run_second` varchar(30) NOT NULL,
`do` MEDIUMINT( 5 ) NOT NULL COMMENT 'date offset',
`data` MEDIUMTEXT NOT NULL,
PRIMARY KEY  (`id`),
KEY `main` (`status`,`locked`,`free`,`nextrun`),
KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}timecheck`";
$tables['timecheck']="
CREATE TABLE `{$prefix}timecheck` (
`mid` smallint unsigned NOT NULL,
`contid` varchar(25) NOT NULL,
`author_id` mediumint unsigned NOT NULL,
`ip` varchar(39) NOT NULL,
`value` tinytext NOT NULL,
`timegone` tinyint NOT NULL,
`date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY  (`mid`,`contid`,`author_id`,`ip`),
KEY `timegone` (`timegone`,`date`),
FOREIGN KEY (`mid`) REFERENCES `{$prefix}modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`author_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}upgrade_hist`";
$tables['upgrade_hist']="
CREATE TABLE `{$prefix}upgrade_hist` (
`id` smallint UNSIGNED NOT NULL auto_increment,
`version` varchar(50) NOT NULL,
`date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`build` smallint UNSIGNED NOT NULL,
`uid` mediumint UNSIGNED NOT NULL,
`descr` text NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users`";
$tables['users']="
CREATE TABLE `{$prefix}users` (
`id` mediumint unsigned NOT NULL auto_increment,
`full_name` varchar(25) NOT NULL,
`name` varchar(25) NOT NULL,
`pass_salt` varchar(20) NOT NULL,
`pass_hash` varchar(32) NOT NULL,
`register` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`banned_until` timestamp default NULL DEFAULT '0000-00-00 00:00:00',
`ban_explain` text NOT NULL,
`language` varchar(30) NOT NULL,
`staticip` tinyint NOT NULL,
`timezone` varchar(25) NOT NULL,
`temp` tinyint NOT NULL,
`updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY  (`id`),
UNIQUE KEY `name` (`name`),
KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_updated`";
$tables['users_updated']="
CREATE TABLE `{$prefix}users_updated` (
`id` mediumint(9) unsigned NOT NULL,
`date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (`id`),
KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_external_auth`";
$tables['users_external_auth']="
CREATE TABLE `{$prefix}users_external_auth` (
`provider` varchar(30) NOT NULL,
`provider_uid` varchar(30) NOT NULL,
`id` mediumint unsigned NOT NULL,
`identity` tinytext NOT NULL,
PRIMARY KEY (`provider`,`provider_uid`),
KEY `id` (`id`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}users_site`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_site`";
$tables['users_site']="
CREATE TABLE `{$prefix}users_site` (
`id` mediumint unsigned NOT NULL,
`forum_id` mediumint unsigned NOT NULL,
`email` varchar(40) NULL,
`groups` varchar(50) NOT NULL,
`groups_overload` text NOT NULL,
`login_keys` text NOT NULL,
`failed_logins` text NOT NULL,
`ip` varchar(39) NOT NULL,
`full_name` varchar(25) NOT NULL,
`name` varchar(25) NOT NULL,
`register` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`language` varchar(30) NOT NULL,
`timezone` varchar(25) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `email` (`email`),
KEY `forum_id` (`forum_id`),
KEY `groups` (`groups`,`register`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_extra`";
$tables['users_extra']="
CREATE TABLE `{$prefix}users_extra` (
`id` mediumint UNSIGNED NOT NULL,
`theme` varchar(30) NOT NULL,
`editor` varchar(20) NOT NULL,
`jabber` varchar(50) NOT NULL,
`bio` text NOT NULL,
`icq` varchar(10) NOT NULL,
`vk` varchar(40) NOT NULL,
`facebook` varchar(40) NOT NULL,
`skype` varchar(30) NOT NULL,
`site` varchar(150) NOT NULL,
`twitter` varchar(40) NOT NULL,
`interests` text NOT NULL,
`gender` tinyint NOT NULL DEFAULT '-1',
`location` varchar(100) NOT NULL,
`signature` text NOT NULL,
`avatar_location` varchar(200) NOT NULL,
`avatar_type` enum('','upload','local','url') NOT NULL,
PRIMARY KEY (`id`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}users_site`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;
#'' в enum-е нужен потому что при вставке строки, где поле avatar_type не определено по-умолчанию берется первое значение

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting`";
$tables['voting']="
CREATE TABLE `{$prefix}voting` (
`id` mediumint unsigned NOT NULL AUTO_INCREMENT,
`begin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`onlyusers` tinyint NOT NULL,
`againdays` tinyint unsigned NOT NULL,
`votes` smallint unsigned NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_q`";
$tables['voting_q']="
CREATE TABLE `{$prefix}voting_q` (
`id` mediumint unsigned NOT NULL,
`qid` tinyint unsigned NOT NULL,
`multiple` tinyint NOT NULL,
`maxans` tinyint unsigned NOT NULL,
`answers` text NOT NULL,
PRIMARY KEY (`id`,`qid`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_q_l`";
$tables['voting_q_l']="
CREATE TABLE `{$prefix}voting_q_l` (
`id` mediumint unsigned NOT NULL,
`qid` tinyint unsigned NOT NULL,
`language` varchar(15) NOT NULL,
`title` tinytext NOT NULL,
`variants` text NOT NULL,
PRIMARY KEY (`id`,`language`,`qid`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_q_results`";
$tables['voting_q_results']="
CREATE TABLE `{$prefix}voting_q_results` (
`id` mediumint unsigned NOT NULL,
`qid` tinyint unsigned NOT NULL,
`vid` tinyint unsigned NOT NULL,
`uid` mediumint unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`,`qid`,`vid`,`uid`),
KEY `uid` (`uid`,`id`),
FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_results`";
$tables['voting_results']="
CREATE TABLE `{$prefix}voting_results` (
`id` mediumint unsigned NOT NULL,
`uid` mediumint unsigned NOT NULL DEFAULT '0',
`date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`answer` tinytext NOT NULL,
PRIMARY KEY (`id`,`uid`),
FOREIGN KEY (`uid`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET;