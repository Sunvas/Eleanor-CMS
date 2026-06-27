<?php
namespace CMS\Classes;

/** Image utilities */
class Image extends \Eleanor\Basic
{
	/** Optimize image file
	 * @param string $dir Directory containing the image
	 * @param string $name Image filename
	 * @return string Optimized image filename */
	static function Optimize(string$dir,string$name):string
	{
		//ToDo!
		return$name;
	}
}

# Not required here because class name matches filename
return Image::class;