<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Enums;

/** Exclusive list of asynchronous system events. Each event is stored in `events` table and can trigger cron task.
 * Feel free to add your own events to this enum, but at the same time as modifying this file, you need to modify the
 * fields in the database: `event` fiend in `events` table and `triggers` field in `cron` table.
 * Maximum amount of events is 64, due to limitation of elements for SET type in MySQL */
enum Events:string
{
	use \CMS\Traits\Events;#Separate file for methods to keep this file clear

	/** New user has been added to the system. Expected parameters: id */
	case UserCreated='user_created';

	/** Users signed in. Expected parameters: id (user id), way (username, telegram), ip (IP of the user), ua (user agent of user's browser), where (userspace, dashboard) */
	case UserSignedIn='user_signed_in';
}