<?php
namespace CMS;

/** Demo blogs star page. Available variables form cms/units/blog/admin-panel.php :
 * @var string $demo_date contains server current date
 * @var int $demo_time contains server current timestamp */

$title=['Blog demo 2'];
$content=<<<HTML
<section class="container-xl">
	<div class="card">
		<div class="card-body">
			<p>Hello! This is demo of the blog admin panel. This page is visible for administrators only.</p>
			<p>Server current date is <code title="Timestamp: {$demo_time}">{$demo_date}</code></p>
		</div>
	</div>
</section>
HTML;


return CMS::$T->index(
	title:$title,
	content:$content
);