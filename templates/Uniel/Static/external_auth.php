<?php
/*
	Ёлемент шаблона. ќтображает кнопки дл€ осуществлени€ внешней авторизации, использу€ систему идентификации Loginza. http://loginza.ru
*/
if(!defined('CMS'))die;?><a style="text-decoration:none" href="#" class="externals loginza">
<img src="http://loginza.ru/img/providers/vkontakte.png" title="¬ онтакте" />
<img src="http://loginza.ru/img/providers/facebook.png" title="Facebook" />
<img src="http://loginza.ru/img/providers/google.png" title="Google Accounts" />
<img src="http://loginza.ru/img/providers/twitter.png" title="Twitter" />
<img src="http://loginza.ru/img/providers/yandex.png" title="Yandex" />
<img src="http://loginza.ru/img/providers/mailru.png" title="Mail.ru" />
<img src="http://loginza.ru/img/providers/openid.png" title="OpenID" />
<img src="http://loginza.ru/img/providers/webmoney.png" title="WebMoney" /></a>
<script type="text/javascript">//<![CDATA[
if(typeof CORE.Loginza=="undefined")
{
	$(function(){
		$("a.externals").prop("href","https://loginza.ru/api/widget?token_url=<?php
if(!isset($GLOBALS['Eleanor']->loginzaurl))
{
	$ma=array_keys($GLOBALS['Eleanor']->modules['sections'],'account');
	$GLOBALS['Eleanor']->loginzaurl=urlencode(PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.($ma ? $GLOBALS['Eleanor']->Url->Construct(array('lang'=>(Eleanor::$vars['multilang'] and Language::$main!=LANGUAGE) ? Eleanor::$langs[Language::$main]['uri'] : false,'module'=>reset($ma),'do'=>'externals'),false,'') : ''));
}
echo$GLOBALS['Eleanor']->loginzaurl.'&amp;lang='.substr(Language::$main,0,3);?>");
		CORE.AddScript("http://loginza.ru/js/widget.js");
	})
	CORE.Loginza=true;
}//]]></script>