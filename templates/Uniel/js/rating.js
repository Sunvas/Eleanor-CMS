﻿/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

function Rating(module,control,id,marks)
{
		posx,
		rate=div.children(":first"),
		pstep=100/marks.length,
		width=div.hover(function(){
			rate.toggleClass("active hover");
			posx=div.offset().left;
		},function(){
			rate.toggleClass("active hover").width(rate.data("now"));
		}).mousemove(function(e){
			p=Math.ceil(p/pstep);
			rate.data("p",p).width(p*pstep+"%");
			rate.toggleClass("active hover");
			CORE.Ajax(
					{
						module:module,
						event:"rating",
						mark:marks[rate.data("p")-1],
						id:id
					},
					function(res)
					{
						control.replaceWith(res);
					}
			);
}