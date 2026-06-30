[Русский язык](README.ru.md)

This directory contains admin panel templates.

Template files are PHP files that prepare and render HTML markup for the admin panel. They may use CMS variables, localization files, shared includes, and context data passed by admin units.

Recommended layout:

- `index.php` — main admin panel layout.
- `SignIn.php` — sign-in page template.
- `error.php` — error page template.
- `app.php` — wrapper template for Vue applications.
- `includes/` — reusable template fragments.
- `l10n/` — localization files used by common admin panel templates.
- `sidebar/` — sidebar menu item templates.
- `unit_name/` — templates used by a specific unit in the admin panel.