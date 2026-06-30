[Русский язык](README.ru.md)

This directory contains templates for the public user area.

The public user area contains only a few common system templates. The `maintenance.php` file is used when the site is closed for maintenance, and `error.php` is used to render error pages. Requested content pages are rendered by units, and each unit decides which templates to use and where to store them.

Recommended layout:

* `index.php` — main page skeleton used by public user area templates.
* `includes/` — reusable template fragments included by other templates.
* `l10n/` — localization files used by common user area templates.
* `Widget*.php` — widget templates for places where site owners may insert custom content, such as ads, banners, notices, or other dynamic blocks.
* `unit_name/` — recommended location for templates used by a specific unit.