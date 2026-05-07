<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

const
	/** @const string System version, contains 2 numbers separated by the dot (left and right).
	 * The number before the dot (left) increases when there were made breaking backwards compatibility changes. */
	VERSION='2.0',

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

	/** @const ?array List of available language localizations of the site. If the constant is empty ([] or null) - site
	 * will be monolingual as defined in L10N constant: if null is used, enabling multilingualism is impossible (data in
	 * DB is stored directly), but if [] is used, enabling multilingualism is possible (data is stored as JSON objects).
	 * The type (null or array) is set at the installation stage and determines the format of data storage in the
	 * database, so it cannot be simply changed manually null <-> array without reworking the database. It is possible
	 * to shorten the list, but before adding a new language, it is necessary to add the congruent translation files and
	 * fields to DB tables. */
	L10NS=[];