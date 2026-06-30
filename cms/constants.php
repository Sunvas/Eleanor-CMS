<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

const
	/** @const string System version containing 2 numbers separated by a dot.
	 * The number before the dot increases when backward-incompatible changes are made. */
	VERSION='3.0',

	/** @const string Path to static files */
	STATIC_PATH=__DIR__.'/../static/',

	/** @const string Authorization (a11n) cookie name */
	A11N_COOKIE='a',

	/** @const int After reaching this ID, unified authorization table will be truncated. The value is determined by the
	 * size of the id field in the `a11n` table. By default, it is UNSIGNED SMALLINT (2 bytes), which means ~180 entries
	 * per day. For 99% of projects, this is more than enough. */
	A11N_TRUNCATE_AFTER=65500,

	/** @const string Default language localization (l10n) code
	 * @url https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes
	 * @url https://ru.wikipedia.org/wiki/Коды_языков */
	L10N='ru',

	/** @const ?array List of available site localization codes. If the constant is empty ([] or null), the site is
	 * monolingual and uses the language defined by L10N. When null is used, multilingual mode cannot be enabled later
	 * without database migration because localized data is stored directly. When [] is used, multilingual mode can be
	 * enabled later because localized data is stored as JSON objects. The type (null or array) is selected during
	 * installation and defines the database storage format, so it must not be changed manually between null and array
	 * without updating the database. The list may be shortened, but before adding a new language, the corresponding
	 * translation files and database fields must be added. */
	L10NS=[];