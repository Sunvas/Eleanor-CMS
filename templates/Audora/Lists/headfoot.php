<?php
return array(
	'title'=>'<title>{0}</title>',
	'meta'=>'<meta name="{0}" content="{1}" />',
	'metahttp'=>'<meta http-equiv="content-type" content="{0}" />',
	'base'=>'<base href="{0}" />',
	'script'=>'<script type="text/javascript" src="{0}"></script>',
	'link'=>function($a)
	{		return'<link'.Eleanor::TagParams($a).' />';
	},
	'debug'=>'<div class="debug"><b>{0}</b> - <span title="{1}">{2}</span></div>',
);