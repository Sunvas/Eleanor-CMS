<?php
# Namespace is intentionally omitted to show that direct files can work without it.

/** @var string $slug Page name
 * @var ?string $uri URI tail */

\Eleanor\Classes\Output::SendHeaders();
echo<<<TEXT
Demo of text page

This content is located in cms/direct/demo-text.php 😉
TEXT;