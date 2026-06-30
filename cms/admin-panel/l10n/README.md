[Русский язык](README.ru.md)

This directory contains localization files used by common admin panel templates.

File names must follow one of these formats:

- `[name]-[code].php` — localization group with a language code.
- `[code].php` — common localization file without a group name.

Each file must return an array. Values may be strings or Closures for dynamic localized text.