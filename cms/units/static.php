<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class extends Abstracts\AdminPanel {
	function __construct()
	{
		$this->name=\basename(__FILE__,'.php');
	}

	/** This special method is called directly from /index.php Should return never if page exists.
	 * @param string $slug Page name
	 * @param ?string $uri URI tail **/
	function Try(string$slug,?string$uri):void
	{
		if(!CMS::$A->current and Return304())
			die;

		if(L10NS===null)
			$query=<<<SQL
SELECT `title`, `description`, `content_source`, `modified` FROM `static` WHERE `slug`=? AND `status`='ACTIVE' LIMIT 1
SQL ;
		else
		{
			$def=L10N;
			$code=L10n::$code;
			$query=<<<SQL
SELECT
	COALESCE(`title_$code`,`title_$def`) `title`,
	COALESCE(`description_$code`,`description_$def`) `description`,
	COALESCE(`content_source_$code`,`content_source_$def`) `content_source`,
	COALESCE(`modified_$code`,`modified_$def`) `modified`,
	`slug_ru`, `slug_en`
FROM `static` WHERE `slug_$code`=? AND `status`='ACTIVE' LIMIT 1
SQL;
		}

		if($uri!==null)
			$slug.='/'.$uri;

		$R=CMS::$Db->Execute($query,[$slug]);
		$item=SingleFetch($R);

		if($item)
		{
			#Links to alternative localizations
			Alternate(fn(string$code,Uri$Uri)=>isset($item['slug_'.$code]) ? $Uri(explode('/',$item['slug_'.$code])) : null);

			#Canonical link
			Canonical(new Uri,\explode('/',$slug));

			#Generating HTML of the page
			$output=(CMS::$T)('StaticPage',$item);

			#Output HTML to browser. Cache is disabled for signed-in users.
			HTML($output,200,CMS::$A->current ? 0 : 86400);
		}
	}
};