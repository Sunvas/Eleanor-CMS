<?php
/** Making a sequence of HTML form template files in the specified directory
 * @var string $dir to template files */

use \Eleanor\Classes\Template;

# PHP 8.6: migrate to pipe operator
$files=array_filter(scandir($dir),fn($item)=>str_ends_with($item,'.php'));
$files=array_map(fn($item)=>strrchr($item,'.',true),$files);

return array_reduce(
	$files,
	fn(Template$T,string$item)=>$T->$item(),
	new Template($dir)
);
