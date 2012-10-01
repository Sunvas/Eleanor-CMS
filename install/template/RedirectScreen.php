<?php
$href=PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$v_0;
$GLOBALS['head']['redirect']='<meta http-equiv="refresh" content="'.$v_1.'; url='.$href.'" />';
echo'<script type="text/javascript">//<![CDATA[
$((function(){	if($("meta[http-equiv=refresh]").size()==0)		setTimeout(function(){window.location.href="'.$href.'"},'.$v_1.'*1000);
});//]]></script>';