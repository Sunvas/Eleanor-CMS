<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class implements Interfaces\Dashboard {
	/** This special method is called directly from /index.php Should return never is page exists.
	 * @param string $slug page name
	 * @param ?string $uri subpage name	**/
	function Try(string$slug,?string$uri):void
	{
		#ToDo! In future versions all static pages will be stored in DB. Currently it is just a demo
		if($slug==='demo-static')
		{
			Canonical($slug);

			$output=CMS::$T->Container(<<<'HTML'
<h1>Demo of static page</h1>
<p>Contents of this page is located in cms/units/static.php</p>
HTML)
			->content->index(title:'Demo static page');

			#Output: cache is off for users
			HTML($output,200,CMS::$A->current ? 0 : 86400);
		}
	}

	function Dashboard(Classes\UriDashboard$Uri):never
	{
		//ToDo!
		$output=CMS::$T->index(
			title:['Static pages demo'],
			content:'Hello! This is demo of the static pages dashboard'
		);
		Html($output);
	}
};