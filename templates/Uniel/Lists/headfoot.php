<?php
$base=include Eleanor::$root.'templates/Audora/Lists/headfoot.php';

#Реализация Open Graph для поддержки ссылок внутри соцсетей http://ogp.me/
return $base+array(
	'og'=>'<meta property="og:{0}" content="{1}" />',
);