<?php

/** @var string $slug page name
 * @var ?string $uri subpage name */

\Eleanor\Classes\Output::SendHeaders();
echo<<<TEXT
Demo of text page

This contents is located in cms/direct/demo-text.php file 😉
TEXT;