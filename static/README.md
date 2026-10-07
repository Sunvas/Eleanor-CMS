[Русский язык](README.ru.md)

This directory contains public static files served directly by the web server: CSS, JavaScript, images, icons, fonts, uploaded files, and similar assets.

PHP script execution must be disabled in this directory.

For all files served from the `uploads/` directory, the web server must send the `Content-Security-Policy: sandbox` header. This is required to prevent user-uploaded content from executing in the security context of the main site.

Recommended layout:

- `admin-panel/` — static files used by the admin panel.
- `user-area/` — static files used by the public user area.
- `unit_name/` — static files used by a specific unit.
- `uploads/` — files uploaded through the CMS.
- `avatars/` — user avatars.