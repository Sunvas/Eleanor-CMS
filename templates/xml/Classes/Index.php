<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class TplIndex
{	public static function OpenSearch(array$data)
	{		$data+=array(
			'shortname'=>Eleanor::$vars['site_name'],
			'description'=>isset($GLOBALS['Eleanor']->module['description']) ? $GLOBALS['Eleanor']->module['description'] : Eleanor::$vars['site_description'],
			'image'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'favicon.ico',

			'search_url'=>false,
			'suggestions_url'=>false,
		);		return'<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName>'.htmlspecialchars($data['shortname'],ELENT,CHARSET,false).'</ShortName>
<Description>'.htmlspecialchars($data['description'],ELENT,CHARSET,false).'</Description>
<Image height="16" width="16" type="image/vnd.microsoft.icon">'.$data['image'].'</Image>
<InputEncoding>'.DISPLAY_CHARSET.'</InputEncoding>'
.($data['search_url'] ? '<Url type="text/html" template="'.$data['search_url'].'" />' : '')
.($data['suggestions_url'] ? '<Url type="application/x-suggestions+json" template="'.$data['suggestions_url'].'" />' : '')
.'</OpenSearchDescription>';	}}