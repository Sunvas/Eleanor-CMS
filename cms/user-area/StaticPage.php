<?php
namespace CMS;

/** Статическая страница
 * @var string $title Заголовок
 * @var string $description Meta описание
 * @var string $content_source EditorJS json */

$jsdelivr=',npm/editorjs-html@4/.build/edjsHTML.browser.js';
$scripts['editorjs']=<<<SCRIPT
L.then(()=>{
	const
		content={$content_source},
		html=content ? edjsHTML().parse(content) : '';

	if(html instanceof Error)
		throw html;
	else
		$("article").append(html);
});
SCRIPT;

#It is possible to echo entire HTML here, but we have "index" template with variables - lets use it
echo CMS::$T

	->Heading($title)
	->Container('<article></article>')
	->content->BaseBlock()

	->content->index(
		title:[$title],
		description:$description,
		jsdelivr:$jsdelivr,
		scripts:$scripts,
	);