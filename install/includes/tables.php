<?php # Eleanor CMS © 2025 · https://eleanor-cms.com
namespace CMS;

/** @var \Eleanor\Classes\MySQL $Db
 * @var string $l10n
 * @var ?array $l10ns */

if($l10ns!==null)
	$l10ns[]=$l10n;

# Order matters: `a11n` can't be dropped first
$tables[]='DROP TABLE IF EXISTS `a11n_userarea`';
$tables[]='DROP TABLE IF EXISTS `a11n_adminpanel`';
$tables[]='DROP TABLE IF EXISTS `a11n`';

$tables['a11n']=<<<'SQL'
CREATE TABLE `a11n` (
	`id` smallint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '2 bytes mean ~180 authentications per day. See description for A11N_TRUNCATE_AFTER constant in cms/constants.php',
	`bytes` binary(7) NOT NULL COMMENT 'Random session key part: 7 bytes provide about 2^56 variants.',
	`generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of last key generation. Should be regenerated weekly.',
	`used` timestamp NULL DEFAULT NULL COMMENT 'Last usage by the user.',
	`ip` varbinary(16) NOT NULL DEFAULT '' COMMENT 'Last IP used by the user.',
	`ua` char(140) NOT NULL DEFAULT '' COMMENT 'Last User Agent used by the user.',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization';
SQL;

$tables['a11n_adminpanel']=<<<'SQL'
CREATE TABLE `a11n_adminpanel` (
	`a11n_id` smallint UNSIGNED NOT NULL,
	`user_id` mediumint UNSIGNED NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`marker` varbinary(5) NOT NULL DEFAULT '' COMMENT 'Temporary cookie marker',
	`way` enum('recovery','sign-in') NOT NULL DEFAULT 'sign-in',
	PRIMARY KEY (`a11n_id`),
	KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization for admin panel';
SQL;

$tables['a11n_userarea']=<<<'SQL'
CREATE TABLE `a11n_userarea` (
	`a11n_id` smallint UNSIGNED NOT NULL,
	`user_id` mediumint UNSIGNED NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`marker` varbinary(5) NOT NULL DEFAULT '' COMMENT 'Temporary cookie marker',
	`way` enum('sign-in','recovery','sign-up','admin-panel') NOT NULL DEFAULT 'sign-in',
	PRIMARY KEY (`a11n_id`,`user_id`),
	KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Authorization for /index.php';
SQL;

$tables[]='DROP TABLE IF EXISTS `cron`';
$tables['cron']=<<<'SQL'
CREATE TABLE `cron` (
	`unit` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Cron-enabled unit filename without .php',
	`status` enum('OK','RUN','RERUN','OFF','FAIL') COLLATE utf8mb4_bin NOT NULL DEFAULT 'OK' COMMENT 'OK - ready or scheduled; RUN - currently running; OFF - disabled; FAIL - task failed, see the error field for details.',
	`error` varchar(250) NOT NULL DEFAULT '' COMMENT 'Error message for FAIL status',
	`run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'For OK: next run date. For RUN: run start date.',
	`triggers` set('user_created','user_signed_in','uri_created') NOT NULL DEFAULT '' COMMENT 'See cms/enums/events.php for details',
	`data` json DEFAULT NULL COMMENT 'Persistent data between task executions',
	PRIMARY KEY (`unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Cron tasks';
SQL;

$tables[]='DROP TABLE IF EXISTS `events`';
$tables['events']=<<<'SQL'
CREATE TABLE `events` (
	`happened` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
	`event` enum('user_created','user_signed_in','uri_created') NOT NULL COMMENT 'See cms/enums/events.php for details',
	`data` json DEFAULT NULL,
	PRIMARY KEY (`happened`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Events for asynchronous operations';
SQL;

$type=$l10ns===null ? 'varchar(25)' : 'json';
$tables[]='DROP TABLE IF EXISTS `groups`';
$tables['groups']=<<<SQL
CREATE TABLE `groups` (
	`id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
	`roles` set('root','team') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Must be the second field because all following fields are treated as rights. Defines system roles assigned to the group.',
	`title` $type NOT NULL COMMENT 'Special field defines the public title of a group.',
	`slow_mode` tinyint NOT NULL DEFAULT '0' COMMENT 'Defines the number of seconds between significant actions such as posting or commenting.',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

# Sitemap
$tables[]='DROP TABLE IF EXISTS `sitemaps`';
$tables['sitemaps']=<<<'SQL'
CREATE TABLE `sitemaps` (
	`name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`status` enum('OK','SCHEDULED','OFF','RUN','FAIL') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'For OK: next run date. For RUN: run start date.',
	`debounce` time NOT NULL DEFAULT '00:00:00' COMMENT 'Delay before sitemap generation; subsequent URI events are batched without resetting the timer',
	`units` json NOT NULL COMMENT 'Array of units',
	`data` json DEFAULT NULL COMMENT 'Object of persistent units data between executions in format unit_name:data',
	`number` smallint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Number of uris in a current file',
	`n` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'File number',
	PRIMARY KEY (`name`),
	CONSTRAINT `units_is_array` CHECK (JSON_TYPE(`units`) = 'ARRAY')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;


# Static pages
$tables[]='DROP TABLE IF EXISTS `static_backup`';
$tables[]='DROP TABLE IF EXISTS `static`';

if($l10ns===null)
{
	$tables['static']=<<<'SQL'
CREATE TABLE `static` (
	`id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` enum('ACTIVE','DRAFT') NOT NULL DEFAULT 'DRAFT',
	`slug` varchar(100) DEFAULT NULL,
	`title` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(250) NOT NULL DEFAULT '' COMMENT 'Meta description',
	`content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'HTML parsed from content_source.',
	`content_source` json DEFAULT NULL COMMENT 'Data saved from EditorJS.save()',
	`content_state` enum('OK','OK_PARTIAL','STALE','PARSING') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'OK' COMMENT 'Defines synchronization status between content_source and content.',
	`content_parsing_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Shows time of the last content parsing operation.',
	`files` json DEFAULT NULL COMMENT 'Array of filenames referenced by the page content.',
	`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Time when page was modified',
	PRIMARY KEY (`id`),
	UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

	$tables['static_backup']=<<<'SQL'
CREATE TABLE `static_backup` (
	`id` smallint UNSIGNED NOT NULL COMMENT 'ID of the page',
	`created_at` timestamp NOT NULL COMMENT 'Rounded to the minute',
	`content_source` json NOT NULL,
	`files` json DEFAULT NULL COMMENT 'Files attached to the page.',
	PRIMARY KEY (`id`,`created_at`),
	KEY `created_at` (`created_at`)
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

	$tables['static']=<<<SQL
CREATE TABLE `static` (
	`id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` enum('ACTIVE','DRAFT') NOT NULL DEFAULT 'DRAFT',
	`l10ns` set($set) DEFAULT '$l10n' COMMENT 'Empty means that the page is monolingual',
	$slug$title$description$content$files$modified
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

	$tables['static_backup']=<<<SQL
CREATE TABLE `static_backup` (
	`id` smallint UNSIGNED NOT NULL COMMENT 'ID of the page',
	`l10n` enum($set) DEFAULT '$l10n',
	`created_at` timestamp NOT NULL COMMENT 'Rounded to the minute',
	`content_source` json NOT NULL,
	`files` json DEFAULT NULL COMMENT 'Array of filenames referenced by the page content.',
	PRIMARY KEY (`id`,`l10n`,`created_at`),
	KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Page content backups generated by trigger. No more than once per hour.';
SQL;
}

$name_len=USERNAME_LENGTH;

# Order matters: `users` can't be dropped first
$tables[]='DROP TABLE IF EXISTS `users_signin_log`';
$tables[]='DROP TABLE IF EXISTS `users`';

# The longest timezone I've come across is "America/Argentina/Buenos_Aires" (30 characters).
$l10n_enum=$l10ns===null ? "'$l10n'" : "'".join("','",$l10ns)."'";
$tables['users']=<<<SQL
CREATE TABLE `users` (
	`id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar($name_len) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Unique username used as login',
	`groups` json NOT NULL COMMENT 'Array of group IDs. Each element is an integer representing a group ID.',
	`password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Empty hash means emergency sign-in without password (admin panel only)',
	`password_changed_at` timestamp NULL DEFAULT NULL COMMENT 'Time when the password was last changed',
	`totp_secret` binary(32) DEFAULT NULL COMMENT 'Secret for TOTP.',
	`totp_digits` tinyint NOT NULL DEFAULT '6' COMMENT 'Number of TOTP digits (6-8).',
	`totp_changed_at` timestamp NULL DEFAULT NULL COMMENT 'Time when TOTP secret was changed, enabled, or disabled.',
	`totp_used` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Last successfully used TOTP code.',
	`recovery_codes` json DEFAULT NULL COMMENT 'Array of recovery codes, each hashed with password_hash.',
	`recovery_codes_created_at` timestamp NULL DEFAULT NULL,
	`recovery_codes_last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Time when a recovery code was last used',
	`l10n` enum($l10n_enum) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '$l10n' COMMENT 'Localization',
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`activity` timestamp NULL DEFAULT NULL COMMENT 'Last user''s activity',
	`last_login_attempt` timestamp NULL DEFAULT NULL COMMENT 'Time when the last login attempt was made',
	`display_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Name to be displayed',
	`avatar` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Avatar salt. Avatars are stored as static/avatars/ID-SALT.webp.',
	`info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Any brief information by user',
	`comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'Comment for admin panel',
	`timezone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'User time zone used for date and time display.',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

$tables['users_signin_log']=<<<'SQL'
CREATE TABLE `users_signin_log` (
	`user_id` mediumint UNSIGNED NOT NULL,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`result` enum('OK','WRONG_PASSWORD','WRONG_TOTP','WRONG_RECOVERY_CODE') NOT NULL,
	`ip` varbinary(16) NOT NULL DEFAULT '',
	`ua` varchar(140) NOT NULL DEFAULT '',
	PRIMARY KEY (`user_id`,`date`),
	ADD KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
SQL;

$tables[]='DROP TABLE IF EXISTS `widgets`';
$tables['widgets']=<<<'SQL'
CREATE TABLE `widgets` (
	`place` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Placement identifier defined by the frontend developer.',
	`title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Title displayed in the admin panel.',
	`description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Detailed description displayed in the admin panel.',
	`file` varchar(25) COLLATE utf8mb4_bin NOT NULL COMMENT 'Widget file selected from the admin panel.',
	`content` text COLLATE utf8mb4_bin COMMENT 'Widget content entered in the admin panel.',
	PRIMARY KEY (`place`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Widgets assigned to predefined frontend placements.';
SQL;

# Static pages
if($l10ns!==null)
{
	$slug='';

	foreach($l10ns as $code)
		$slug.="ADD UNIQUE KEY `slug_$code` (`slug_$code`), ";

	$slug=\rtrim($slug,', ');
	$tables['static_primary']=<<<SQL
ALTER TABLE `static` $slug;
SQL;
}

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

$tables['users_signin_logs_constraints']=<<<'SQL'
ALTER TABLE `users_signin_log`
	ADD CONSTRAINT `users_signin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
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
			INSERT INTO `static_backup` (`id`,`created_at`,`content_source`,`files`) VALUES (OLD.`id`, DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00'), OLD.`content_source`, OLD.`files`);
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

$tables['UserCredentialsChanged']=<<<'SQL'
CREATE TRIGGER `UserCredentialsChanged`
BEFORE UPDATE ON `users`
FOR EACH ROW BEGIN
	-- Password was changed
	IF NOT (NEW.`password_hash` <=> OLD.`password_hash`) THEN
		SET NEW.`password_changed_at` = CURRENT_TIMESTAMP;
	END IF;

	-- OTP configuration was changed, enabled, or disabled
	IF NOT (NEW.`totp_secret` <=> OLD.`totp_secret`)
		OR NEW.`totp_digits` <> OLD.`totp_digits`
	THEN
		SET NEW.`totp_changed_at` = CURRENT_TIMESTAMP;
	END IF;

	-- Recovery codes were changed
	IF NOT (NEW.`recovery_codes` <=> OLD.`recovery_codes`) THEN
		IF OLD.`recovery_codes` IS NOT NULL
			AND NEW.`recovery_codes` IS NOT NULL
			AND JSON_LENGTH(NEW.`recovery_codes`) < JSON_LENGTH(OLD.`recovery_codes`)
		THEN
			-- A recovery code was used
			SET NEW.`recovery_codes_last_used_at` = CURRENT_TIMESTAMP;

		ELSEIF NEW.`recovery_codes` IS NOT NULL THEN
			-- A new set of recovery codes was generated
			SET NEW.`recovery_codes_created_at` = CURRENT_TIMESTAMP;
		END IF;
	END IF;
END
SQL;

return $tables;