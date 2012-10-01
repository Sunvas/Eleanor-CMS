<?php
$s=Eleanor::$Template->OpenSearch(array(
	'shortname'=>Eleanor::$vars['site_name'],
	'search_url'=>'http://google.com/search?q=site:'.Eleanor::$domain.'%20{searchTerms}',
));Start();
echo$s;