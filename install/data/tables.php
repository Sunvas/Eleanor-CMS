<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
$l10ns=$_SESSION['l10ns'];

$tables[]='DROP TABLE IF EXISTS `a11n`';

$tables['a11n']=<<<'SQL'
CREATE TABLE `a11n` (
	`id` smallint UNSIGNED NOT NULL,
	`bytes` binary(7) NOT NULL COMMENT 'Certainly 7 bytes is more than enough: that''s ~2^56 variants.',
	`generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of last generation. It should be regenerated  weekly.',
	`used` TIMESTAMP NOT NULL DEFAULT '1997-01-01' COMMENT 'Last usage by user. Being update only when used by user.',
	`ip` BINARY(16) NOT NULL DEFAULT 0x0 COMMENT 'Last IP by user. Being update only when used by user.',
	`ua` CHAR(140) NOT NULL DEFAULT '' COMMENT 'Last User Agent by user. Being update only when used by user.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization';
SQL;

$tables[]='DROP TABLE IF EXISTS `a11n_dashboard`';
$tables['a11n_dashboard']=<<<'SQL'
CREATE TABLE `a11n_dashboard` (
	`a11n_id` smallint UNSIGNED NOT NULL,
	`user_id` mediumint UNSIGNED NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`salt` varbinary(5) NOT NULL DEFAULT '\0' COMMENT 'Is used for temporary sessions',
	`way` ENUM('username') NOT NULL DEFAULT 'username'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization for dashboard';
SQL;

$tables[]='DROP TABLE IF EXISTS `a11n_userspace`';
$tables['a11n_userspace']=<<<'SQL'
CREATE TABLE `a11n_userspace` (
	`a11n_id` smallint UNSIGNED NOT NULL,
	`user_id` mediumint UNSIGNED NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`salt` varbinary(5) NOT NULL DEFAULT '\0' COMMENT 'Is used for temporary sessions',
	`way` ENUM('username','telegram','sign-up','dashboard') NOT NULL DEFAULT 'username'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization for /index.php';
SQL;

$tables[]='DROP TABLE IF EXISTS `cron`';
$tables['cron']=<<<'SQL'
CREATE TABLE `cron` (
	`unit` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'File without .php from cms/units/ folder',
	`status` enum('OK','RUN','OFF') COLLATE utf8mb4_bin NOT NULL DEFAULT 'OK',
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'For status OK - date for next run, for RUN - date of run start.',
	`triggers` SET('user_created','user_signed_in') NULL COMMENT 'See cms/enums/events.php for details',
	`remnant` json DEFAULT NULL COMMENT 'Data for continuation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Cron tasks';
SQL;

$tables[]='DROP TABLE IF EXISTS `events`';
$tables['events']=<<<'SQL'
CREATE TABLE `events` (
  `happened` timestamp(2) NOT NULL DEFAULT CURRENT_TIMESTAMP(2),
  `event` enum('user_created','user_signed_in') NOT NULL COMMENT 'See cms/enums/events.php for details',
  `data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Events for asynchronous operations';
SQL;

$type=$l10ns===null ? 'VARCHAR(25)' : 'JSON';
$tables[]='DROP TABLE IF EXISTS `groups`';
$tables['groups']=<<<SQL
CREATE TABLE `groups` (
	`id` tinyint UNSIGNED NOT NULL,
	`roles` set('admin','team') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Must be the second. Special field defines flags of self-sufficient roles.',
	`title` {$type} NOT NULL COMMENT 'Special field defines public title of a group',
	`slow_mode` tinyint NOT NULL DEFAULT '0' COMMENT 'Defines amount of seconds between significant actions like posting or commenting. '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

$l10n=$_SESSION['l10n'];
$tables[]='DROP TABLE IF EXISTS `users`';
$tables['users']=<<<SQL
CREATE TABLE `users` (
	`id` mediumint UNSIGNED NOT NULL,
	`name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Unique user''s name used as login',
	`groups` json NOT NULL COMMENT 'Array of IDs of groups: each element is an integer represents ID of group',
	`password_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
	`l10n` enum('en','ru') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{$l10n}' COMMENT 'Localization',
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`activity` timestamp NOT NULL DEFAULT '1997-01-01 00:00:00' COMMENT 'Last user''s activity',
	`last_login_attempt` timestamp NOT NULL DEFAULT '1997-01-01 00:00:00',
	`display_name` varchar(35) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Name to be displayed',
	`avatar` varchar(5) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Avatar''s salt. Avatars are located in static/avatars/ID-SALT.webp',
	`info` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Any brief information by user',
	`comment` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Comment from dashboard',
	`timezone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'User should see dates on site according to his location',
	`telegram_id` int UNSIGNED DEFAULT NULL,
	`telegram_username` varchar(25) COLLATE utf8mb4_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

$tables[]='DROP TABLE IF EXISTS `widgets`';
$tables['widgets']=<<<'SQL'
CREATE TABLE `widgets` (
	`place` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Is chosen by frontender',
	`title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Title for dashboard',
	`description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Detailed description for dashboard',
	`file` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Is specified from dashboard',
	`content` text COLLATE utf8mb4_bin COMMENT 'Is input from dashboard'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Contents of this table is edited manually via PhpMyAdmin';
SQL;

#Keys

$tables['a11n_primary']=<<<'SQL'
ALTER TABLE `a11n`
	ADD PRIMARY KEY (`id`);
SQL;

$tables['a11n_dashboard_primary']=<<<'SQL'
ALTER TABLE `a11n_dashboard`
	ADD PRIMARY KEY (`a11n_id`),
	ADD KEY `user_id` (`user_id`);
SQL;

$tables['a11n_userspace_primary']=<<<'SQL'
ALTER TABLE `a11n_userspace`
	ADD PRIMARY KEY (`a11n_id`,`user_id`),
	ADD KEY `user_id` (`user_id`);
SQL;

$tables['cron_primary']=<<<'SQL'
ALTER TABLE `cron`
	ADD PRIMARY KEY (`unit`),
	ADD KEY `status` (`status`,`date`);
SQL;

$tables['groups_primary']=<<<'SQL'
ALTER TABLE `groups`
	ADD PRIMARY KEY (`id`);
SQL;

$tables['users_primary']=<<<'SQL'
ALTER TABLE `users`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `name` (`name`),
	ADD KEY `telegram_id` (`telegram_id`);
SQL;

$tables['widgets_primary']=<<<'SQL'
ALTER TABLE `widgets`
	ADD PRIMARY KEY (`place`);
SQL;

#Autoincrements

$tables['a11n_autoincrement']=<<<'SQL'
ALTER TABLE `a11n`
	MODIFY `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '2 bytes mean ~180 authentihications per day. See description for A11N_TRUNCATE_AFTER constant in cms/constants.php';
SQL;

$tables['groups_autoincrement']=<<<'SQL'
ALTER TABLE `groups`
	MODIFY `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT;
SQL;

$tables['users_autoincrement']=<<<'SQL'
ALTER TABLE `users`
	MODIFY `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT;
SQL;

#Constraints

$tables['a11n_dashboard_constraints']=<<<'SQL'
ALTER TABLE `a11n_dashboard`
	ADD CONSTRAINT `a11n_dashboard_ibfk_1` FOREIGN KEY (`a11n_id`) REFERENCES `a11n` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `a11n_dashboard_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;

$tables['a11n_userspace_constraints']=<<<'SQL'
ALTER TABLE `a11n_userspace`
	ADD CONSTRAINT `a11n_userspace_ibfk_1` FOREIGN KEY (`a11n_id`) REFERENCES `a11n` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `a11n_userspace_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;

$tables['events_primary']=<<<'SQL'
ALTER TABLE `events`
	ADD PRIMARY KEY (`happened`,`event`);
SQL;

return$tables;