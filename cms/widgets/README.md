[Русский язык](README.ru.md)

This directory contains optional widget files used to process custom content before it is inserted into predefined public user area placements.

Widget placements and their visual markup are defined by frontend templates. Each placement can be configured from the admin panel: it has a title, description, optional widget file, and optional content entered by the site owner.

If no widget file is selected, the widget content may be inserted directly into the placement. If a widget file is selected, it receives the `$content` variable and returns the content prepared for insertion. The page-level placement markup remains controlled by the frontend template.