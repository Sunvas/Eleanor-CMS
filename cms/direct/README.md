[Русский язык](README.ru.md)

This directory contains direct endpoint files for the user area.

If a request slug matches `^[a-z\d\-_.]+$` and is not handled by a unit or a static page, Eleanor CMS includes the PHP file with the same name from this directory.

For example, the `/demo-json` request includes `cms/direct/demo-json.php`.

Direct files are useful for simple endpoints that do not need a full unit: plain text responses, JSON responses, callbacks, or small standalone pages.

This directory also contains demo files showing different response types:

- `demo-direct.php` — HTML page rendered through the CMS template.
- `demo-json.php` — JSON response.
- `demo-text.php` — plain text response.