/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.Publications={	Init:function(where,module){		var th=this;		$(".getmore",where).filter(function(){			return $(this).data("id") && $(this).data("more");		}).click(function(){			var th=$(this),
				te=$($(this).data("more"));
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
					{						language:CORE.language,						module:module,
						event:"getmore",
						id:th.data("id")					},
					function(r)
					{						te.html(r).fadeIn();
						th.toggleClass("getmore getmore-active").data("has",true);
					}
				);
			return false;		})	}
}
