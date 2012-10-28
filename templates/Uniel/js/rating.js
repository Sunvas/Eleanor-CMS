/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

function Rating(module,control,marks,addon)
{	var div=$(control).children(":first"),
		posx,
		rate=div.children(":first"),
		pstep=100/marks.length,
		width=div.hover(function(){
			rate.toggleClass("active hover");
			posx=div.offset().left;
		},function(){
			rate.toggleClass("active hover").width(rate.data("now"));
		}).mousemove(function(e){			var p=Math.round((e.pageX-posx)/width*100,2);
			p=Math.ceil(p/pstep);
			rate.data("p",p).width(p*pstep+"%");		}).click(function(){			div.off("mousemove mouseleave click");
			rate.toggleClass("active hover");
			CORE.Ajax(
					$.extend({
							module:module,
							language:CORE.language,
							rating:{
								mark:marks[rate.data("p")-1]
							}
						},addon),
					function(res)
					{
						$(control).replaceWith(res);
					}
			);		}).width();
}