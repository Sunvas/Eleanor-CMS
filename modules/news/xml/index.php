<?php
global$Eleanor;
$l=Eleanor::$Language->Load($Eleanor->module['path'].'xml-*.php',false);
$type=isset($_GET['type']) ? (string)$_GET['type'] : '';
switch($type)
{
	default:
		$ub=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
		$ua=Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']);
		$s=Eleanor::$Template->OpenSearch(array(
			'shortname'=>$l['n'],
			'search_url'=>$ub.Eleanor::$services['user']['file'].'?'.Url::Query($ua+array('do'=>'search')).'&amp;q={searchTerms}',
			'suggestions_url'=>$ub.Eleanor::$services['ajax']['file'].'?'.Url::Query($ua+array('do'=>'opensearch')).'&amp;q={searchTerms}',
		));
}
Start();
echo$s;