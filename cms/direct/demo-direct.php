<?php
namespace CMS;

/** @var string $slug Page name
 * @var ?string $uri URI tail */

if(CMS::$json)
	JSON(['ok'=>false],404);

Canonical($slug);
Alternate(fn(string$code,Uri$Uri)=>$Uri($slug));

$output=CMS::$T
	->Container(<<<'HTML'
<article>
	<h1>Demo of direct page</h1>
	<p>This page content is located in cms/direct/demo-direct.php</p>
</article>
HTML )
	->content->BaseBlock()
	->content->index(title:'Demo direct page');

# Cache is disabled for signed-in users
HTML($output,200,CMS::$A->current ? 0 : 86400);