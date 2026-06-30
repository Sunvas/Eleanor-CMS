<?php
/** Attaching all necessary editorjs files */

$scripts??=[];
array_push($scripts,
	'https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest',# Main editor
	'https://cdn.jsdelivr.net/npm/@editorjs/attaches@latest',# https://github.com/editor-js/attaches
	'https://cdn.jsdelivr.net/npm/@editorjs/code@latest',# https://github.com/editor-js/code
	'https://cdn.jsdelivr.net/npm/@editorjs/embed@latest',# https://github.com/editor-js/embed
	'https://cdn.jsdelivr.net/npm/@editorjs/header@latest',# https://github.com/editor-js/header
	'https://cdn.jsdelivr.net/npm/@editorjs/image@latest',# https://github.com/editor-js/image
	'https://cdn.jsdelivr.net/npm/@editorjs/list@latest',# https://github.com/editor-js/list
	'https://cdn.jsdelivr.net/npm/@editorjs/quote@latest',# https://github.com/editor-js/quote
	'https://cdn.jsdelivr.net/npm/@editorjs/raw@latest',# https://github.com/editor-js/raw
);

$head[]=<<<'HTML'
<style>.codex-editor .ce-block__content, .codex-editor .ce-toolbar__content { max-width: calc(100% - 8rem); }</style>
HTML;