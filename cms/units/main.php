<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class extends Abstracts\AdminPanel implements Interfaces\UserArea {
	readonly string
		/** @var string $slug URL prefix of the unit */
		$slug,
		/** @var string $name name of the unit */
		$name;

	function __construct()
	{
		$this->slug='';#Mustn't be changed
		$this->name=basename(__FILE__,'.php');
	}

	function UserArea(?string $uri):never
	{
		$cache=0;

		# Page should be cached for guests only
		if(!CMS::$A->current)
		{
			$mtime=\filemtime($this->GetMainPageFile());

			if($mtime)
			{
				$cache=$this->name.($mtime-\Eleanor\BASE_TIME);

				Return304($cache) && die;
			}
		}

		$code=200;
		$output=require __DIR__."/{$this->name}/user-area.php";

		CMS::$json ? JSON($output,$code,$cache) : HTML($output,$code,$cache);
	}

	/** Get file with contents of main page
	 * @param ?string $code L10N code
	 * @return string */
	function GetMainPageFile(?string$code=null):string
	{
		$code??=L10n::$code;
		return __DIR__."/{$this->name}/mainpage-{$code}.json";
	}
};