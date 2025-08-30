<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class implements Interfaces\UserSpace, Interfaces\Dashboard {
	readonly string
		/** @var string $slug URL prefix of the unit */
		$slug,
		/** @var string $name name of the unit */
		$name;

	function __construct()
	{
		$this->slug='blog';
		$this->name=basename(__FILE__,'.php');
	}

	function UserSpace(?string$uri):never
	{
		Canonical($this->slug);

		#ToDo! Currently it is just a demo. In future versions full-featured of blog unit will be developed.
		$output=CMS::$T->Heading('Blog')
			->Container(<<<HTML
<h1>Demo of blog unit</h1>
<p>Contents of this page is located in cms/units/{$this->name}.php</p>
HTML )

			->content->index('Demo blog unit');

		#Output: cache is off for users
		HTML($output,200,CMS::$A->current ? 0 : 86400);
	}

	function Dashboard(Classes\UriDashboard$Uri):never
	{
		$code=200;
		$cache=0;
		$output=require __DIR__."/{$this->name}/dashboard.php";

		CMS::$json ? JSON($output,$code,$cache) : HTML($output,$code,$cache);
	}
};