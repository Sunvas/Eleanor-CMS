<?php
/** Making sequence of HTML form template files in specified directory
 * @var string $dir to template files */

$files=scandir($dir);
$Seq=clone new \Eleanor\Classes\Template($dir);#Clone is used to turn on lazy fluent interface on 10th line

foreach($files as $item)
	if(str_ends_with($item,'.php'))
	{
		$name=strrchr($item,'.',true);
		$Seq->{$name}();
	}

return $Seq;
