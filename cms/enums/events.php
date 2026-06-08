<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Enums;

/** Exclusive list of asynchronous system events. Each event is stored in the `events` table and can trigger a cron task.
 * Feel free to add your own events to this enum, but at the same time as modifying this file, you need to modify the
 * fields in the database: `event` field in the `events` table and `triggers` field in the `cron` table.
 * The maximum number of events is 64 because MySQL SET supports up to 64 elements. */
enum Events:string
{
	use \CMS\Traits\Events;# Separate file for methods to keep this file clear

	/** A new user has been added to the system. Expected parameters: id */
	case UserCreated='user_created';

	/** A user signed in. Expected parameters: id (user id), way (authorization method), ip (IP of the user), ua (user agent of user's browser), where (user area, admin panel) */
	case UserSignedIn='user_signed_in';
}

# Not required here because the enum name matches filename
return Events::class;