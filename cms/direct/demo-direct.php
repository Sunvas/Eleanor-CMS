<?php
namespace CMS;

/** @var string $slug page name
 * @var ?string $uri subpage name */

if(CMS::$json)
	JSON(['ok'=>false],404);

Canonical($slug);

$output=(CMS::$T)('index',
	title:'Demo direct page',
	content:<<<HTML
<div class="binner">
	<h1>Demo of direct page</h1>
	<p>Contents of this page is located in cms/direct/demo-direct.php</p>
</div>
HTML,
);

#Cache is off for users
HTML($output,200,CMS::$A->current ? 0 : 86400);