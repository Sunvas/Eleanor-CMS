<?php
namespace CMS;
use Eleanor\Classes\L10n;

/** Main page of the site
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

#Main page doesn't have subpages
Canonical('');

#Loading the config of the site
$config=CMS::$config['site'];

#Extraction of language values from the config (with l10n enabled)
if(\is_array(L10NS))
	foreach(['name','title','description'] as $f)
		$config[$f]=isset($config[$f]) ? L10n::Item($config[$f]) : null;

#Content from the dashboard
$file=$this->GetMainPageFile();
$content=\file_get_contents($file);

#Calling for "main" template and passing $config variable into it
return(CMS::$T)('main',
	title:$config['title'] ?: $config['name'],
	description:$config['description'],
	content:$content,
);