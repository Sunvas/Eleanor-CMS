/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

function DropDown(opts)
{	opts=$.extend(
		{			selector:0,
			top:false,
			left:true,
			event:"click",
			rel:false,
			limiter:false//Контентер, правая или левая граница которого будет ограничителем
		},
		opts	);

	var th=$(opts.selector),
		rel=$(opts.rel||th.data("rel")),
		showed=false,
		limiter=opts.limiter ? $(opts.limiter) : false,
		TopLeft=function()
		{
			rel.css("left",-1000).css("top",-1000).show();
			var h=rel.outerHeight(),
				w=rel.outerWidth(),
				left=opts.left ? th.position().left : th.position().left+th.outerWidth()-w;

			if(limiter)
			{				var rb=limiter.position().left+limiter.outerWidth();
				if(left+w>rb && rb-w>0)
					left=rb-w;			}
			rel.hide()
			.css("left",left+"px")
			.css("top",(opts.top ? th.position().top-h : th.position().top+th.height())+"px");
		},
		Hide=function(im)
		{			if(!showed)
				return;
			if(im)
				rel.hide();
			else
				rel.fadeOut("fast");
			DropDown.current=false;
			showed=false;
		},
		RetObj={			rel:rel,			hide:Hide		},
		Show=function(im)
		{
			if(showed)
				return;
			if(DropDown.current)
			{
				DropDown.current.hide();
				DropDown.current=false;
			}

			TopLeft();
			if(im)
				rel.show();
			else
				rel.fadeIn("fast");
			DropDown.current=RetObj;
			showed=true;
		};

	switch(opts.event)
	{
		case "mouseover":
			th.hover(Show,Hide);
		break;
		default:
			th.click(function(e){				e.stopPropagation();
				showed ? Hide() : Show();
				return false;
			});
	}	return RetObj;
}
DropDown.current=false;

$(document).click(function(e){
	if(DropDown.current && !DropDown.current.rel.is(e.target) && DropDown.current.rel.find(e.target).size()==0)
	{		DropDown.current.hide(true);
		DropDown.current=false;
	}
});