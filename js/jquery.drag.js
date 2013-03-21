/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
$.fn.DragAndDrop=function(opts)
{
	opts=$.extend(
		{
			items:"li",//Пункты, которые нужно двигать
			move:false,//Объект, за который двигаются пункты (если пустой - двигать за сами пункты)
			exclude:"input,textarea,a[href],select",//Если move - пустой, при клике на этих объектах внутри items, движение не будет происходить,
			replace:"<li>",//Объект будет местом, куда нужно положить двигаемый пункт
			clean:false,//Флаг, при включени которого скрипт не делает список перемещающимся, а наоборот: убирает с объектов свои обработчики
			alpha:0.5,//Прозрачность при перемещении
			//Опции при перетаскивании из одного списка в другой
			between:false,//Флаг включения такой возможности.
			empty:"<li>",//Объект, который будет в списке в том случае, если из списка будут удалены все движимые пункты
			//Callbacks
			OnEnd:function(obj){}
		},
		opts
	);
	var th=this,
		lists,
		inmove=false,
		idr,
		MoveDown=function(e,el,i,l)
		{
			if($(e.target).is(opts.exclude) || e.which!=1)
				return;
			e.preventDefault();
			if(inmove)
				return false;
			inmove=true;
			idr=i;
			var r=$(opts.replace).height(el.height()).width(el.width()),
				mex=e.pageX-el.offset().left,
				mey=e.pageY-el.offset().top,
				dw=$(document).width(),
				dh=$(document).height(),
				DocMove=function(e){
					e.stopPropagation();
					var left=e.pageX-mex,
						top=e.pageY-mey,
						elw=el.width(),
						elh=el.height();
					if(left+elw>=dw)
						left=dw-elw;
					if(top+elh>=dh)
						top=dh-elh;
					r.height(elh).width(elw);
					el.css({
						left:left>0 ? left : 0,
						top:top>0 ? top : 0
					});
					$.each(lists,function(li,lv){
						var br=false;
						$.each(lv,function(ii,iv){
							if(ii==i)
								return;
							var pos=iv.o.offset();
							if(pos.left<e.pageX && pos.top<e.pageY && (pos.left+iv.o.width())>e.pageX && (pos.top+iv.o.height())>e.pageY)
							{
								if(li==l)
								{
									if(ii<idr)
									{
										if(e.pageY-pos.top>elh)
											return;
										do
										{
											if(idr==parseInt(idr))
												idr--;
											else
												idr=parseInt(idr);
											var tmp=$(opts.replace).hide();
											tmp.insertBefore(lists[li][idr].o);
											lists[li][idr].o.insertBefore(r);
											r.replaceAll(tmp);
										}while(ii<idr)
										idr-=0.5;
									}
									else if(ii>idr)
									{
										if(iv.o.height()-e.pageY+pos.top>elh)
											return;
										do
										{
											idr=idr>=0 ? parseInt(idr)+1 : 0;
											var tmp=$(opts.replace).hide();
											tmp.insertAfter(lists[li][idr].o);
											lists[li][idr].o.insertAfter(r);
											r.replaceAll(tmp);
										}while(ii>idr);
										idr+=0.5;
									}
									br=true;
									return false;
								}
								else if(opts.between)
								{
									//Здесь должен быть алгоритм перемещения пунктов между списками, но он недодуман ввиду отсутствия стимула. #ToDo! на будущее
									//r.insertAfter(iv.o);
								}
							}
						});
						if(br)
							return false;
					});
					return false;
				};

			el.stop(true,true).fadeTo("fast",opts.alpha).css({
				left:el.offset().left,
				top:el.offset().top,
				position:"absolute"
			}).after(r);
			$(document).mouseup(function(e){
				e.preventDefault();
				$(this).off("mousemove",DocMove).off(e);
				el.stop(true,true).animate({
					left:r.offset().left,
					top:r.offset().top
				},200,function(){
					$(this).replaceAll(r).css({
						position:"",
						left:"",
						top:""
					});
					inmove=false;
					opts.OnEnd(this);
					ScanItems();
				}).fadeTo("fast",1);
			}).mousemove(DocMove);
			return false;
		},
		ScanItems=function()
		{
			lists=[];
			$.each(th,function(){
				var items=[];
				$(opts.items,this).each(function(){
					var el=$(this),
						h=el,
						i=items.length,
						l=lists.length;
					if(opts.move)
						h=h.find(opts.move);
					h.off("mousedown");
					if(opts.clean)
						return;
					h.mousedown(function(e){
						return MoveDown(e,el,i,l);
					});
					items.push({
						o:el,
						l:l,
						i:i
					});
				});
				if(items.length>0)
					lists.push(items);
			});
		}
	ScanItems();
	return this;
}