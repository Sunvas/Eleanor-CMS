/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
var module;
$(function(){	$(this).on("click","div.base .getmore, div.base .getmore-active ",function(){		var th=$(this);		if(!th.data("id") || !th.data("more"))
			return;
		var te=$(th.data("more"));
		if(th.data("has"))
		{
			if(te.is(":visible"))
				te.fadeOut();
			else
				te.fadeIn();

			th.toggleClass("getmore getmore-active");
		}
		else
			CORE.Ajax(
				{
					language:CORE.language,
					module:module,
					event:"getmore",
					id:th.data("id")
				},
				function(r)
				{
					te.html(r).fadeIn();
					th.toggleClass("getmore getmore-active").data("has",true);
				}
			);
		return false;
	})});
