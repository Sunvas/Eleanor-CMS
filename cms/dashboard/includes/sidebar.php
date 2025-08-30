<?php
$path=__DIR__.'/../sidebar/';
$files=scandir($path);
$Sidebar=clone new \Eleanor\Classes\Template($path);#Clone is used to turn on lazy fluent interface on 10th line

foreach($files as $item)
	if(str_ends_with($item,'.php'))
	{
		$name=strrchr($item,'.',true);
		$Sidebar->{$name}();
	}

return $Sidebar;
