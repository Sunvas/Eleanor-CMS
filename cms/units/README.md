[Русский язык](README.ru.md)

This directory contains CMS units — components that implement website functionality.

Each unit is represented by a PHP file named `unit_name.php`. This file must return an object. The standard CMS entry points are defined by interfaces such as:

- `CMS\Interfaces\UserArea` — the unit is available from the public user area.
- `CMS\Interfaces\AdminPanel` — the unit is available from the admin panel.
- `CMS\Interfaces\Cron` — the unit can be executed as a background task.
- `CMS\Interfaces\CLI` — the unit can be executed from the command line.

A unit object may implement several standard interfaces at the same time. Projects may also define additional interfaces and entry points when custom execution contexts are needed.

The `unit_name.php` file should usually stay small and contain only the unit object definition and entry-point dispatching. More complex logic may be placed in the optional `unit_name/` directory. This directory may contain admin panel handlers, user area handlers, cron logic, configuration files, templates, demo data, or any other files required by the unit.

For example:

```text
blog.php
blog/
	admin-panel.php
	user-area.php
```

After a unit is loaded, its object is stored in the global `$CMS` variable as a dynamic property with the same name as the unit.

Templates used by a unit should be placed in context-specific template directories:

- `cms/admin-panel/unit_name/` — templates for the admin panel.
- `cms/user-area/unit_name/` — templates for the public user area.

Static files used by a unit must be placed in the `static/unit_name/` directory.