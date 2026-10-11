<?php # Eleanor CMS © 2025 · https://eleanor-cms.com
namespace CMS\Enums;

/** Exclusive list of asynchronous system events. Each event is stored in the `events` table and can trigger a cron task.
 * Feel free to add your own events to this enum, but at the same time as modifying this file, you need to modify the
 * fields in the database: `event` field in the `events` table and `triggers` field in the `cron` table.
 * The maximum number of events is 64 because MySQL SET supports up to 64 elements. */
enum Events:string
{
	use \CMS\Traits\Events;# Separate file for methods to keep this file clean

	/** A new user has been added to the system. Trigger() data: int (user ID). */
	case UserCreated='user_created';

	/** A user signed in.
	 * Trigger() data: array{id: int, way: string, ip: ?string, ua: string, where: string}.
	 * Fields: id (user ID), way (authorization method), ip (user IP address, or null),
	 * ua (browser user agent), where (user area or admin panel). */
	case UserSignedIn='user_signed_in';

	/** A new public URI has become available. Trigger() data: string (unit name). */
	case UriCreated='uri_created';
}

# Not required here because enum name matches filename
return Events::class;