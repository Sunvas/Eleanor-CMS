/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.AddScript(["addons/codemirror/mode/xml/xml.js","addons/codemirror/mode/javascript/javascript.js","addons/codemirror/mode/css/css.js"]);
$(function(){
	CORE.AddHead("default-codemirror",$("<link>").attr({rel:"stylesheet",type:"text/css",href:"addons/codemirror/theme/default.css",media:"screen"}));
});
