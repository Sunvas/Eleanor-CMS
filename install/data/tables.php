<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
$l10n=$_SESSION['l10n'];
$l10ns=$_SESSION['l10ns'];

if($l10ns!==null)
	$l10ns[]=$l10n;

# Order matters: `a11n` can't be dropped first
$tables[]='DROP TABLE IF EXISTS `a11n_userarea`';
$tables[]='DROP TABLE IF EXISTS `a11n_adminpanel`';
$tables[]='DROP TABLE IF EXISTS `a11n`';

$tables['a11n']=<<<'SQL'
CREATE TABLE `a11n` (
	`id` smallint UNSIGNED NOT NULL,
	`bytes` binary(7) NOT NULL COMMENT 'Random session key part: 7 bytes provide about 2^56 variants.',
	`generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of last key generation. Should be regenerated weekly.',
	`used` timestamp NOT NULL DEFAULT '1997-01-01' COMMENT 'Last usage by the user.',
	`ip` binary(16) NOT NULL DEFAULT 0x0 COMMENT 'Last IP used by the user.',
	`ua` char(140) NOT NULL DEFAULT '' COMMENT 'Last User Agent used by the user.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization';
SQL;

$tables['a11n_adminpanel']=<<<'SQL'
CREATE TABLE `a11n_adminpanel` (
	`a11n_id` smallint UNSIGNED NOT NULL,
	`user_id` mediumint UNSIGNED NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`marker` varbinary(5) NOT NULL DEFAULT '\0' COMMENT 'Temporary cookie marker',
	`way` enum('username') NOT NULL DEFAULT 'username'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization for admin panel';
SQL;

$tables['a11n_userarea']=<<<'SQL'
CREATE TABLE `a11n_userarea` (
	`a11n_id` smallint UNSIGNED NOT NULL,
	`user_id` mediumint UNSIGNED NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`marker` varbinary(5) NOT NULL DEFAULT '\0' COMMENT 'Temporary cookie marker',
	`way` enum('username','sign-up','admin-panel') NOT NULL DEFAULT 'username'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization for /index.php';
SQL;

$tables[]='DROP TABLE IF EXISTS `cron`';
$tables['cron']=<<<'SQL'
CREATE TABLE `cron` (
	`unit` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Cron-enabled unit filename without .php',
	`status` enum('OK','RUN','OFF','FAIL') COLLATE utf8mb4_bin NOT NULL DEFAULT 'OK' COMMENT 'OK - ready or scheduled; RUN - currently running; OFF - disabled; FAIL - task failed, see the error field for details.',
	`error` varchar(250) NOT NULL DEFAULT '' COMMENT 'Error message for FAIL status',
	`run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'For OK: next run date. For RUN: run start date.',
	`triggers` set('user_created','user_signed_in') NOT NULL DEFAULT '' COMMENT 'See cms/enums/events.php for details',
	`remnant` json DEFAULT NULL COMMENT 'Data for continuation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Cron tasks';
SQL;

$tables[]='DROP TABLE IF EXISTS `events`';
$tables['events']=<<<'SQL'
CREATE TABLE `events` (
	`happened` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
	`event` enum('user_created','user_signed_in') NOT NULL COMMENT 'See cms/enums/events.php for details',
	`data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Events for asynchronous operations';
SQL;

$type=$l10ns===null ? 'varchar(25)' : 'json';
$tables[]='DROP TABLE IF EXISTS `groups`';
$tables['groups']=<<<SQL
CREATE TABLE `groups` (
	`id` tinyint UNSIGNED NOT NULL,
	`roles` set('root','team') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Must be the second field because all following fields are treated as rights. Defines system roles assigned to the group.',
	`title` {$type} NOT NULL COMMENT 'Special field defines the public title of a group.',
	`slow_mode` tinyint NOT NULL DEFAULT '0' COMMENT 'Defines the number of seconds between significant actions such as posting or commenting.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

# Static pages
$tables[]='DROP TABLE IF EXISTS `static_backup`';
$tables[]='DROP TABLE IF EXISTS `static`';

if($l10ns===null)
{
	$tables['static']=<<<'SQL'
CREATE TABLE `static` (
	`id` smallint UNSIGNED NOT NULL,
	`status` enum('ACTIVE','DRAFT') NOT NULL DEFAULT 'DRAFT',
	`slug` varchar(100) DEFAULT NULL,
	`title` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(250) NOT NULL DEFAULT '' COMMENT 'Meta description',
	`content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'HTML parsed from content_source.',
	`content_source` json DEFAULT NULL COMMENT 'Data saved from EditorJS.save()',
	`content_state` enum('OK','OK_PARTIAL','STALE','PARSING') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'OK' COMMENT 'Defines synchronization status between content_source and content.',
	`content_parsing_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Shows time of the last content parsing operation.',
	`files` json NOT NULL COMMENT 'Array of filenames referenced by the page content.',
	`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Time when page was modified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

	$tables['static_backup']=<<<'SQL'
CREATE TABLE `static_backup` (
	`id` smallint UNSIGNED NOT NULL COMMENT 'ID of the page',
	`created_at` timestamp NOT NULL COMMENT 'Rounded to the minute',
	`content_source` json NOT NULL,
	`files` json NOT NULL COMMENT 'Files attached to the page.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Page content backups generated by trigger. No more than once per hour.';
SQL;
}
else
{
	$slug=$title=$content=$description=$files=$modified='';

	foreach($l10ns as $code)
	{
		$slug.="`slug_$code` varchar(100) NULL,";
		$title.="`title_$code` varchar(100) NOT NULL DEFAULT '',";
		$description.="`description_$code` varchar(250) NOT NULL DEFAULT '' COMMENT 'Meta description',";
		$content.="`content_$code` mediumtext NULL COMMENT 'HTML parsed from content_source_$code.',
`content_source_$code` json NULL COMMENT 'Data saved from EditorJS.save()',
`content_state_$code` enum('OK','OK_PARTIAL','STALE','PARSING') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'OK' COMMENT 'Defines synchronization status between content_source and content.',
`content_parsing_at_$code` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Shows time of the last content parsing operation.',";
		$files.="`files_$code` json DEFAULT NULL COMMENT 'Array of filenames referenced by the page content.',";
		$modified.="`modified_$code` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time when the page was modified',";
	}

	$set="'".join("','",$l10ns)."'";
	$modified=rtrim($modified,',');

	$tables['static']=<<<SQL
CREATE TABLE `static` (
	`id` smallint UNSIGNED NOT NULL,
	`status` enum('ACTIVE','DRAFT') NOT NULL DEFAULT 'DRAFT',
	`l10ns` set($set) DEFAULT '{$l10n}' COMMENT 'Empty means that the page is monolingual',
	{$slug}{$title}{$description}{$content}{$files}{$modified}
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

	$tables['static_backup']=<<<SQL
CREATE TABLE `static_backup` (
	`id` smallint UNSIGNED NOT NULL COMMENT 'ID of the page',
	`l10n` enum($set) DEFAULT '{$l10n}',
	`created_at` timestamp NOT NULL COMMENT 'Rounded to the minute',
	`content_source` json NOT NULL,
	`files` json NOT NULL COMMENT 'Array of filenames referenced by the page content.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Page content backups generated by trigger. No more than once per hour.';
SQL;
}

# The longest timezone I've come across is "America/Argentina/Buenos_Aires" (30 characters).
$tables[]='DROP TABLE IF EXISTS `users`';
$tables['users']=<<<SQL
CREATE TABLE `users` (
	`id` mediumint UNSIGNED NOT NULL,
	`name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Unique username used as login',
	`groups` json NOT NULL COMMENT 'Array of group IDs. Each element is an integer representing a group ID.',
	`password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
	`otp_secret` binary(32) DEFAULT NULL COMMENT 'Secret for OTP.',
	`otp_digits` tinyint NOT NULL DEFAULT '6' COMMENT 'Number of OTP digits (6-8).',
	`otp_step` tinyint NOT NULL DEFAULT '30' COMMENT 'Number of seconds between OTP digits regeneration.',
	`otp_reserve_codes` json DEFAULT NULL COMMENT 'OTP recovery codes hashed with SHA3-256.',
	`otp_changed_at` timestamp NOT NULL DEFAULT '1997-01-01' COMMENT 'Date when OTP secret was changed, enabled, or disabled.',
	`l10n` enum('en','ru') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'ru' COMMENT 'Localization',
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`activity` timestamp NOT NULL DEFAULT '1997-01-01' COMMENT 'Last user''s activity',
	`last_login_attempt` timestamp NOT NULL DEFAULT '1997-01-01',
	`display_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Name to be displayed',
	`avatar` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Avatar salt. Avatars are stored as static/avatars/ID-SALT.webp.',
	`info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Any brief information by user',
	`comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Comment for admin panel',
	`timezone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'User time zone used for date and time display.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

$tables[]='DROP TABLE IF EXISTS `widgets`';
$tables['widgets']=<<<'SQL'
CREATE TABLE `widgets` (
	`place` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Placement identifier defined by the frontend developer.',
	`title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Title displayed in the admin panel.',
	`description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Detailed description displayed in the admin panel.',
	`file` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Widget file selected from the admin panel.',
	`content` text COLLATE utf8mb4_bin COMMENT 'Widget content entered in the admin panel.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Widgets assigned to predefined frontend placements.';
SQL;

# Keys

$tables['a11n_primary']=<<<'SQL'
ALTER TABLE `a11n`
	ADD PRIMARY KEY (`id`);
SQL;

$tables['a11n_adminpanel_primary']=<<<'SQL'
ALTER TABLE `a11n_adminpanel`
	ADD PRIMARY KEY (`a11n_id`),
	ADD KEY `user_id` (`user_id`);
SQL;

$tables['a11n_userarea_primary']=<<<'SQL'
ALTER TABLE `a11n_userarea`
	ADD PRIMARY KEY (`a11n_id`,`user_id`),
	ADD KEY `user_id` (`user_id`);
SQL;

$tables['cron_primary']=<<<'SQL'
ALTER TABLE `cron`
	ADD PRIMARY KEY (`unit`),
	ADD KEY `status_run_at` (`status`,`run_at`);
SQL;

$tables['events_primary']=<<<'SQL'
ALTER TABLE `events`
	ADD PRIMARY KEY (`happened`,`event`);
SQL;

$tables['groups_primary']=<<<'SQL'
ALTER TABLE `groups`
	ADD PRIMARY KEY (`id`);
SQL;

# Static pages
if($l10ns===null)
{
	$tables['static_primary']=<<<'SQL'
ALTER TABLE `static`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `slug` (`slug`);
SQL;

	$tables['static_backup_primary']=<<<'SQL'
ALTER TABLE `static_backup`
	ADD PRIMARY KEY (`id`,`created_at`) USING BTREE;
SQL;
}
else
{
	$slug='';

	foreach($l10ns as $code)
		$slug.=", ADD UNIQUE KEY `slug_$code` (`slug_$code`)";

	$tables['static_primary']=<<<SQL
ALTER TABLE `static`
	ADD PRIMARY KEY (`id`)$slug;
SQL;

	$tables['static_backup_primary']=<<<'SQL'
ALTER TABLE `static_backup`
	ADD PRIMARY KEY (`id`,`l10n`,`created_at`) USING BTREE;
SQL;
}

$tables['users_primary']=<<<'SQL'
ALTER TABLE `users`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `name` (`name`);
SQL;

$tables['widgets_primary']=<<<'SQL'
ALTER TABLE `widgets`
	ADD PRIMARY KEY (`place`);
SQL;

# Autoincrements

$tables['a11n_autoincrement']=<<<'SQL'
ALTER TABLE `a11n`
	MODIFY `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '2 bytes mean ~180 authentications per day. See description for A11N_TRUNCATE_AFTER constant in cms/constants.php';
SQL;

$tables['groups_autoincrement']=<<<'SQL'
ALTER TABLE `groups`
	MODIFY `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT;
SQL;

$tables['static_autoincrement']=<<<'SQL'
ALTER TABLE `static`
	MODIFY `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT;
SQL;

$tables['users_autoincrement']=<<<'SQL'
ALTER TABLE `users`
	MODIFY `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT;
SQL;

# Constraints

$tables['a11n_adminpanel_constraints']=<<<'SQL'
ALTER TABLE `a11n_adminpanel`
	ADD CONSTRAINT `a11n_adminpanel_ibfk_1` FOREIGN KEY (`a11n_id`) REFERENCES `a11n` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `a11n_adminpanel_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;

$tables['a11n_userarea_constraints']=<<<'SQL'
ALTER TABLE `a11n_userarea`
	ADD CONSTRAINT `a11n_userarea_ibfk_1` FOREIGN KEY (`a11n_id`) REFERENCES `a11n` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `a11n_userarea_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;

$tables['static_backup_constraints']=<<<'SQL'
ALTER TABLE `static_backup`
	ADD CONSTRAINT `static_backup_ibfk_1` FOREIGN KEY (`id`) REFERENCES `static` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;

# Triggers
$tables[]='DROP TRIGGER IF EXISTS `StaticContentModified`';

if($l10ns===null)
{
	$tables['StaticContentModified']=<<<'SQL'
CREATE TRIGGER `StaticContentModified`
BEFORE UPDATE ON `static`
FOR EACH ROW
BEGIN
	DECLARE `need_backup` tinyint DEFAULT 1;

	IF NOT (NEW.`content_source` <=> OLD.`content_source` OR OLD.`content_source` IS NULL) THEN
		-- Modification of content marks content as stale
		SET NEW.`content_state`='STALE';

		-- Backup is created no more than once per hour
		SELECT IF(`created_at` < NOW() - INTERVAL 1 HOUR,1,0) INTO `need_backup` FROM `static_backup` WHERE `id`=NEW.`id` ORDER BY `created_at` DESC LIMIT 1;

		IF (`need_backup`=1) THEN
			INSERT INTO `static_backup` (`id`,`created_at`,`content_source`,`files`) VALUES (OLD.`id`, DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00'), OLD.`content_source`, COALESCE(OLD.`files`,'[]'));
		END IF;
	END IF;

	-- Update parsing timestamp for PARSING and OK states
	IF (NEW.`content_state` <> OLD.`content_state` AND NEW.`content_state` IN ('PARSING','OK')) THEN
		SET NEW.`content_parsing_at`=NOW();
	END IF;
END;
SQL;
}
else
{
	$trigger='';

	foreach($l10ns as $code)
		$trigger.=<<<SQL
	IF NOT (NEW.`content_source_$code` <=> OLD.`content_source_$code` OR OLD.`content_source_$code` IS NULL) THEN
		-- Modification of content marks content as stale
		SET NEW.`content_state_$code`='STALE';
		SET `need_backup`=1;

		-- Backup is created no more than once per hour
		SELECT IF(`created_at` < NOW() - INTERVAL 1 HOUR,1,0) INTO `need_backup` FROM `static_backup` WHERE `id`=NEW.`id` AND `l10n`='$code' ORDER BY `created_at` DESC LIMIT 1;

		IF (`need_backup`=1) THEN
			INSERT INTO `static_backup` (`id`,`l10n`,`created_at`,`content_source`,`files`) VALUES (OLD.`id`, '$code', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00'), OLD.`content_source_$code`, COALESCE(OLD.`files_$code`,'[]'));
		END IF;
	END IF;

	-- Update parsing timestamp for PARSING and OK states
	IF (NEW.`content_state_$code` <> OLD.`content_state_$code` AND NEW.`content_state_$code` IN ('PARSING','OK')) THEN
		SET NEW.`content_parsing_at_$code`=NOW();
	END IF;


SQL;

	$trigger=\rtrim($trigger,"\n");
	$tables['StaticContentModified']=<<<SQL
CREATE TRIGGER `StaticContentModified`
BEFORE UPDATE ON `static`
FOR EACH ROW
BEGIN
	DECLARE `need_backup` tinyint DEFAULT 1;

$trigger
END;
SQL;
}

return $tables;