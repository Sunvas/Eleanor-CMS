<?php # Eleanor CMS © 2025 · https://eleanor-cms.com
namespace CMS;

return new class extends Abstracts\AdminPanel implements Interfaces\UserArea, Interfaces\Sitemap {
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

	function UserArea(?string $uri):never
	{
		if(CMS::$json)
			JSON([
				'ok'=>false,
				'unit'=>$this->name,
				'error'=>'JSON_OUTPUT_NOT_SUPPORTED',
			]);

		Canonical($this->slug);

		Alternate(fn(string$code,Uri$Uri)=>$Uri($this->slug));

		#ToDo! Currently it is just a demo. In future versions full-featured of blog unit will be developed.
		$output=CMS::$T->Heading('Blog')
			->Container(<<<HTML
<h1>Demo of blog unit</h1>
<p>Contents of this page is located in cms/units/$this->name.php</p>
HTML )

			->content->BaseBlock()
			->content->index('Demo blog unit');

		#Output: cache is off for users
		HTML($output,200,CMS::$A->current ? 0 : 86400);
	}

	function Sitemap(mixed&$data):\Generator
	{
		//ToDo!
		yield from [];
	}
};