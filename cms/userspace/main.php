<?php
namespace CMS;

/** Main page of the site
 * @var string $title Head title
 * @var string $description Meta description
 * @var string $content JSON content for main page (from editorJS) */

#Loading language values
$l10n=new \Eleanor\Classes\L10n('main',__DIR__.'/l10n/');
$jsdelivr=',npm/editorjs-html@4/.build/edjsHTML.browser.js';
$scripts['editorjs']=<<<SCRIPT
L.then(()=>{
	const
		content={$content},
		html=content ? edjsHTML().parse(content) : '';

	if(html instanceof Error)
		throw html;
	else
		$("#content").append(html);
});
SCRIPT;

#It is possible to echo entire HTML here, but we have "index" template with variables - lets use it
echo (CMS::$T)('index',
	title:$title,
	description:$description,
	jsdelivr:$jsdelivr,
	scripts:$scripts,
	content:<<<HTML
<div class="binner" id="content">
	{$l10n['content']}
</div>
HTML,
);