/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
$.fn.MultiLevelMenu=function(opts)
{
	opts=$.extend(
		{
			type:"row",//col
			event:"mouseenter",//click
			delay:500,
			rtl:false,
			MarkSubMenu:function(obj)
			{
				$(this).addClass("hassubmenu").children("a:first").addClass("hassubmenu");
			}
		},
		opts
	);

	var AttachSubMenu=function(sett,obj)
	{
		var last,
			timeout;
		obj.children("li:has(ul)").on(sett.event,function(){
			if(timeout)
				clearTimeout(timeout);
			var ul=$(this).stop().children("ul:first");
			if(ul.data("showed") && sett.event=="click")
				last.fadeOut().data("showed",false);
			else if(!last || ul.get(0)!=last.get(0))
			{
				var rx=$(this).offset().left+$(this).width()+ul.width(),
					bodyw=$("body").width();
				if(sett.type=="row")
				{
					var x=$(this).position().left,
						y=$(this).position().top+$(this).height();
					if(!opts.rtl && bodyw<rx || opts.rtl)
					{
						var tx=x-ul.outerWidth()+$(this).width();
						if(!opts.rtl || opts.rtl && tx>0)
							x=tx;
					}
				}
				else
				{
					var x=$(this).position().left+$(this).width(),
						y=$(this).position().top;
					if(!opts.rtl && bodyw<rx || opts.rtl)
					{						var tx=ul.width();
						if(!opts.rtl || opts.rtl && $(this).offset().left-tx>0)
							x-=tx+$(this).width();
					}
				}
				ul.css({left:x,top:y});
				if(last)
				{
					last.children("li:has(ul)").children("ul").data("showed",false).hide().end().end().data("showed",false).hide();
					ul.show();
				}
				else
					ul.stop(true,true).fadeIn();
				last=ul.data("showed",true);
			}
		}).mouseleave(function(){
			timeout=setTimeout(function(){
				last.children("li:has(ul)").children("ul").data("showed",false).fadeOut().end().end().data("showed",false).fadeOut();
				last=false;
			},sett.delay);
		}).children("ul").each(function(){
			AttachSubMenu($.extend({},opts,{type:"column",event:"mouseenter"}),$(this));
		})
	}

	$.each(this,function(){
		if($(this).data("menu"))
			return;
		AttachSubMenu(opts,$(this).data("menu",true).find("li:has(ul)").each(opts.MarkSubMenu).end());
	});

	return this;
}