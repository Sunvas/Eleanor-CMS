/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
function BlocksList()
{	$(document).on("mousedown","div.fieldedit a",function(e){		if(e.which==1)
			var a=$(this).data("to",setTimeout(function(){
				$("<input type=\"text\">").val(a.text()).insertAfter(a).width("100%").focus();
				a.parent().data("a",a.detach());
			},100));
	})
	.on("blur","div.fieldedit input",function(){
		$(this).parent().data("a").insertAfter(this);
		$(this).remove();
	})
	.on("keypress","div.fieldedit input",function(e){
		if(e.which==13)
		{
			var th=$(this),
				t=th.val();
			if(th.parent().data("a").text()!=t)
			{
				CORE.Ajax(
					{
						direct:"admin",
						file:"blocks",
						event:"settitle",
						language:CORE.language,
						title:t,
						id:th.parent().data("id")
					},
					function(r)
					{
						th.parent().data("a").text(t).insertAfter(th);
						th.remove();
					}
				);
			}
			else
				th.blur();
			return false;
		}
	})
	.on("mouseup","div.fieldedit a",function(){
		var to=$(this).data("to");
		if(to)
			clearTimeout(to);
	})}

function AddEditBlock()
{	var file=$("input[name=file]"),
		tfch=$("input[name=textfile]"),
		files=[],
		configs=[],
		fval=file.val(),
		Detach=function(k)
		{			if(k!=-1)
				configs[k]=$("#block tr.trconf").find("script").remove().end().detach();		},
		canchange=true,
		ChangeFile=function(){			if(!canchange)
				return;			var v=file.val(),
				ok=$.inArray(fval,files),
				k=fval==v ? ok : $.inArray(v,files);
			if(!v || tfch.prop("checked"))
			{				Detach(ok);
				fval=v;
			}
			else if(v!=fval || k==-1)
			{				if(k==-1)
				{					setTimeout(function(){						if(!canchange)
							return;
						CORE.Ajax(
							{
								direct:"admin",
								file:"blocks",
								event:"tryconf",
								language:CORE.language,
								f:v
							},
							function(r)
							{								Detach(ok);
								configs.push(1);
								files.push(v);
								if(r)
								{
									$("#block tr.preconf").after(r);
									$("#block .labinfo").poshytip({
										className: "tooltip",
										offsetX: -7,
										offsetY: 16,
										allowTipHover: false
									});
								}
								fval=v;
							}
						);
					},100);
				}
				else
				{					Detach(ok);
					if(configs[k])
						configs[k].insertAfter("#block tr.preconf");
					fval=v;
				}
			}
			else if(configs[k])
				configs[k].insertAfter("#block tr.preconf");
		}
	tfch.change(ChangeFile);
	file.change(ChangeFile).change().autocomplete({
		serviceUrl:CORE.ajax_file,
		minChars:2,
		delimiter: null,
		onSelect: function(value,data){			canchange=false;
			setTimeout(function(){				canchange=true;
				tfch.change();			},100);
		},
		params:{
			direct:"admin",
			file:"autocomplete",
			filter:"blocks-files"
		}
	});
	if(file.val())
	{		configs.push(1);
		files.push(file.val());
		if(tfch.prop("checked"))
			Detach(0);	}

	$("input[name=notemplate]:checkbox").change(function(){		var tr=$("input[name=template]").closest("tr");		if($(this).prop("checked"))
			tr.hide();
		else
			tr.show();	}).change();

	$("[name=ctype]").change(function(){		if($(this).val()=="file")
		{			$(".trfile").show();			$(".trtext").hide();
		}
		else
		{			$(".trfile").hide();
			$(".trtext").show();		}	}).change();

	$("#vars").on("click",".sb-plus",function(){
		var tr=$(this).closest("tr");
		tr.clone(false).find("input[type=text],textarea").val("").end().insertAfter(tr);
	}).on("click",".sb-minus",function(){		var tr=$(this).closest("tr");
		if($("#vars tr").size()>2)
			tr.remove();
		else
			tr.find("input[type=text],textarea").val("");
	})}

function GetScrollWidth()
{	var d=$("<div style=\"position:absolute;height:50px;overflow-y:hidden;width:50px;visibility:hidden\">")
		.html("<div style=\"height:100px\"></div>")
		.appendTo("body"),
		w1=d.children(":first").prop("offsetWidth"),
		w2=d.css("overflowY","scroll").children(":first").prop("offsetWidth");
	d.remove();
	return w1-w2;}

//http://weblog.bocoup.com/using-datatransfer-with-jquery-events/
$.event.props.push('dataTransfer');

//http://blog.kron0s.com/drag-n-drop-in-html5
$.fn.EleanorVisualBlocks=function(opts)
{	opts=$.extend(
		{			//unique selector
			block:".block",//Селектор блоков в местах
			container:".bcontainer",//Селектор контейнера для блоков в месте
			moveblock:".block",//Селектор контрола для перемещения блока

			//selector к site
			place:".place",//Селектор мест для блоков
			placeresize:".place .resize",//Селектор для контрола по изменению размера места (place => )
			placemove:".place .title",//Селектор контрола для перемещения места

			//selector к place
			placetitleinput:"input[name^=\"place[\"]",//Селектор для языковых заголовков места
			placeinput:"input:last",//Селектор для определения контрола, где скрипт будет размещать параметры left,top,width,height,z-index
			placecaption:".caption:first",

			//selector к block
			blockinput:"input:first",
			blockcaption:".caption:first",

			//Дистрибутивы блоков и мест
			distribblock:false,
			distribplace:false,

			//Разное
			dragimg:"",//Иконка под мышкой при перемещении блоков
			dragimgx:-10,
			dragimgy:-10,

			//Callbacks
			Recount:function(){},//Пересчет элементов. Параметр 1: id=>cnt
			EditPlace:function(){},//Редактирование места. Параметры: имя, названи(е|я), функция сохранения
			Change:$.Callbacks("unique")//Функция вызывается когда что-то изменилось.
		},
		opts
	);
	var scrw=GetScrollWidth(),
		PlaceParams=function(pl,k,v)
		{
			var o=pl.children(opts.placeinput),
				val=o.val().split(","),
				i;

			k=$.inArray(k,["left","top","width","height","z-index"]);
			for(i=val.length;i<5;i++)
				val.push("-");
			if(k>-1)
				val[k]=parseInt(v);
			o.val(val.join(","));
			opts.Change.fire();
		};

	$.each(this,function(){
		var site=$(this),
			zindex=0,
			actplace,
			Activate=function(){
				var z=$(this).css("z-index");
				if(z!=zindex)
				{
					if(actplace)
					{
						actplace.css("z-index",z);
						PlaceParams(actplace,"z-index",z);
					}
					actplace=$(this).css("z-index",zindex);
					PlaceParams(actplace,"z-index",zindex);
				}
			},
			Recount=function(){				var blocks=[];				site.find(opts.block).find(opts.blockinput).each(function(){					var v=$(this).val();
					blocks[v]=typeof blocks[v]=="undefined" ? 1 : blocks[v]+1;				});
				opts.Recount(blocks);
			},
			Delete=function(e){				$(this).remove();
				e.stopPropagation();
				Recount();			};

		site.on("add",function(e,name,title){//Событие добавления места
			var newpl=opts.distribplace.clone(),
				titles=newpl.find(opts.placetitleinput).val("").prop("name",function(i,ov){					return ov.replace("[]","["+name+"]");				}),
				pltit;
			newpl.find(opts.placeinput).prop("name","placeinfo["+name+"]");
			if(typeof title=="string")
			{				pltit=title;
				titles.val(title).filter(":gt(1)").prop("disabled",true);
			}
			else
				titles.each(function(){
					var l=$(this).prop("name");
					l=l.indexOf("][")==-1 ? CORE.language : l.match(/\[([^\]]+)\]$/)[1];
					$(this).val(title[l]);
					if(l==CORE.language)
						pltit=title[l];
				})
			newpl.appendTo(this).find(opts.placecaption).text(pltit && pltit!=name ? pltit+" ("+name+")" : name);
			PlaceParams(newpl,"z-index",zindex);
			Recount();
		})
		.on("edit",opts.place,function(e){//Событие редактирования места
			var title=[],
				pl=$(this),
				titles=$(opts.placetitleinput,this).each(function(){					var l=$(this).prop("name");					if(l.indexOf("][")==-1)
					{						title=$(this).val();
						return false;					}
					l=l.match(/\[([^\]]+)\]$/)[1];
					title[l]=$(this).val();
				}),
				inp=$(opts.placeinput,this),
				oldname=inp.prop("name").match(/\[([^\]]+)\]$/)[1];
			opts.EditPlace(oldname,title,function(name,title){				var pltit;				if(typeof title=="string")
				{
					pltit=title;
					titles.val(title).filter(":gt(1)").prop("disabled",true);
				}
				else
					titles.each(function(){
						var l=$(this).prop("name");
						l=l.indexOf("][")==-1 ? CORE.language : l.match(/\[([^\]]+)\]$/)[1];
						$(this).val(title[l]);
						if(l==CORE.language)
							pltit=title[l];
					})
				pl.find(opts.placecaption).text(pltit && pltit!=name ? pltit+" ("+name+")" : name);
				if(oldname!=name)
					pl.find(opts.block).find(opts.blockinput)
					.add(pl.find(opts.placeinput))
					.add(pl.find(opts.placetitleinput))
					.prop("name",function(i,ov){						return ov ? ov.replace("["+oldname+"]","["+name+"]") : "block["+name+"][]";					});			});
		})
		.on("delete",opts.place,Delete)//Событие удаления места
		.on("delete",opts.block,Delete)//Событие удаление блока
		.on("focus click",opts.place,Activate)

		//Изменение размеров мест
		.on("mousedown",opts.placeresize,function(de){
			de.preventDefault();
			de.stopPropagation();

			var th=$(this),
				thh=th.outerHeight(),
				thw=th.outerWidth(),
				pl=th.closest(opts.place),
				w=pl.width(),
				h=pl.height(),
				l=pl.position().left,
				t=pl.position().top,
				iw=site.width(),
				ih=site.height(),
				to=false;
				Move=function(e){
					var dx=e.pageX-de.pageX+w,
						dy=e.pageY-de.pageY+h;
					if(iw<l+dx)
						dx=iw-l;
					if(ih<t+dy)
						dy=ih-t;
					if(dx<thw*3)
						dx=thw*3;
					if(dy<thh*3)
						dy=thh*3;

					pl.width(dx+"px").height(dy+"px");
					if(!to)
						to=setTimeout(function(){
							PlaceParams(pl,"width",dx);
							PlaceParams(pl,"height",dy);
							to=false;
						},100);
					dx=pl.innerWidth();
					dy=pl.innerHeight();

					var scl=pl.scrollLeft(),
						sct=pl.scrollTop(),
						scw=pl.prop("scrollWidth"),
						sch=pl.prop("scrollHeight");

					if(sch>dy)
						th.css({top:(scw+scrw>dx ? -scrw : 0)+dy-thh+sct+"px",bottom:""});
					else
						th.css({top:"",bottom:0});

					if(scw>dx)
						th.css({left:(sch+scrw>dy ? -scrw : 0)+dx-thw+scl+"px",right:""});
					else
						th.css({left:"",right:0});
				};
			$(document).mousemove(Move).mouseup(function(mue){
				$(document).off("mousemove",Move).off(mue);
			})
			Activate.call(pl);
		})

		//Перемещение места
		.on("mousedown",opts.placemove,function(de){
			if($(de.target).is("a,:input"))
				return;

			var pl=$(this).is(opts.place) ? $(this) : $(this).closest(opts.place),
				pos=pl.offset(),
				w=pl.outerWidth(),
				h=pl.outerHeight(),
				l=pl.position().left,
				t=pl.position().top,
				iw=site.innerWidth(),
				ih=site.innerHeight(),
				to=false,
				Move=function(e){
					var dx=e.pageX-de.pageX+l,
						dy=e.pageY-de.pageY+t;
					if(iw<dx+w)
						dx=iw-w;
					if(ih<dy+h)
						dy=ih-h;
					if(dx<1)
						dx=0;
					if(dy<1)
						dy=0;
					pl.css("left",dx+"px").css("top",dy+"px");
					if(!to)
						to=setTimeout(function(){
							PlaceParams(pl,"left",dx);
							PlaceParams(pl,"top",dy);
							to=false;
						},100);
				};

			if(
				pl.prop("offsetWidth")-pl.prop("clientWidth")>0 && de.pageX-pos.left>=pl.prop("clientWidth") && de.pageX-pos.left<=pl.prop("offsetWidth")
				||
				pl.prop("offsetHeight")-pl.prop("clientHeight")>0 && de.pageY-pos.top>=pl.prop("clientHeight") && de.pageY-pos.top<=pl.prop("offsetHeight")
			)
				return true;

			de.preventDefault();
			de.stopPropagation();
			$(document).mousemove(Move).mouseup(function(mue){
				$(document).off("mousemove",Move).off(mue);
			})
				Activate.call(pl);
		});

		zindex=site.children(opts.place).size();
		if(zindex>0)
		{
			var i,a=[];
			for(i=zindex;i>0;i--)
				a.push(i);
			site.children(opts.place).each(function(){				var pl=$(this),
					inp=pl.find(opts.placeinput),
					val=inp.val().split(","),
					z=false;

				$(this).find(opts.placetitleinput).each(function(){
					var name=$(this).prop("name"),
						v=$(this).val();
					if(name.indexOf("][")==-1 || name.match(/\[([^\]]+)\]$/)[1]==CORE.language)
					{						name=inp.prop("name").match(/\[([^\]]+)\]$/)[1];
						pl.find(opts.placecaption).text(v ? v+" ("+name+")" : name);
						return false;
					}
				});

				if(val.length==5)
				{					pl.css({left:val[0]+"px",top:val[1]+"px"}).width(val[2]+"px").height(val[3]+"px");
					z=$.inArray(a,val[4]);
					if(z!=-1)
					{
						delete a[z];
						z=val[4];
					}
					else
						z=a.pop();
				}
				else
				{
					z=a.pop();
					inp.val([pl.position().left,pl.position().top,pl.width(),pl.height(),z].join(","));
				}
				pl.css("z-index",z);
				if(zindex==z)
					actplace=pl;
			});
			Recount();
		}

		var dragged=false,
			DragOver=function(e){				var can=false;
				if(dragged)
				{					var ne=dragged.next(opts.block);
					if(this!=dragged.get(0) && (ne.size()>0 && this!=ne.get(0) || ne.size()==0 && this!=dragged.closest(opts.place).get(0)))
					{
						e.dataTransfer.dropEffect=e.ctrlKey ? "copy" : "move";
						can=true;
					}
				}
				else if($.inArray("blockid",e.dataTransfer.types)!=-1 && $.inArray("title",e.dataTransfer.types)!=-1)
				{
					e.dataTransfer.dropEffect="copy";
					can=true;
				}

				if(can)
				{					$(this).addClass("dragenter");
					Activate.call($(this).is(opts.place) ? this : $(this).closest(opts.place).get(0));
				}
				else
					e.dataTransfer.dropEffect="none";
				e.stopPropagation();
				return false;
			},
			DragLeave=function(e){
				$(this).removeClass("dragenter");
			},
			DragDrop=function(e){				var b;				if(dragged)
				{
					b=e.ctrlKey ? dragged.clone() : dragged;
					b.fadeTo("fast",1);
				}
				else
					b=opts.distribblock.clone().prop("draggable",true ).find(opts.blockinput).val(e.dataTransfer.getData("blockid")).end()
						.find(opts.blockcaption).text(e.dataTransfer.getData("title")).end();

				if($(this).removeClass("dragenter").is(opts.block))
				{
					var pl=$(this).closest(opts.place);
					$(this).before(b);
				}
				else
				{
					var pl=$(this);
					$(opts.container,this).append(b);
				}
				pl=pl.find(opts.placeinput).prop("name").match(/\[([^\]]+)\]/)[1];
				b.find(opts.blockinput).prop("name","block["+pl+"][]");

				e.stopPropagation();
				if(!dragged)
					Recount();
				opts.Change.fire();
				dragged=false;
			};
		site.on("dragstart",opts.moveblock,function(e){
			if($(e.target).is("a,:input"))
				return;

			dragged=$(this).is(opts.block) ? $(this) : $(this).closest(opts.block);
			dragged.fadeTo("fast",0.5);
			e.stopPropagation();
			e.dataTransfer.effectAllowed="copy move";
			e.dataTransfer.setData("text/html","Eleanor CMS Block");
			if(opts.dragimg)
				e.dataTransfer.setDragImage($("<img>").prop("src",opts.dragimg).get(0),opts.dragimgx,opts.dragimgy);
			Activate.call(dragged.closest(opts.place).get(0));
		}).on("dragend",opts.moveblock,function(e){			var block=$(this).is(opts.block) ? $(this) : $(this).closest(opts.block);
			block.fadeTo("fast",1);
			dragged=false;
		})

		.on("dragover",opts.block,DragOver)
		.on("dragover",opts.place,DragOver)

		.on("dragleave",opts.block,DragLeave)
		.on("dragleave",opts.place,DragLeave)

		.on("drop",opts.place,DragDrop)//"dragdrop"
		.on("drop",opts.block,DragDrop)

		.find(opts.moveblock).prop("draggable",true).end();
	})
	return this;
}

function BlocksMain(Change)
{	$(".blocks .available li").prop("draggable",true);

	var av=$(".blocks .available"),
		all=$(".blocks .all"),
		ver=$(".blocks .ver"),
		site=$(".blocks .site-c"),
		hora=$(".blocks .hor"),
		deffalliw=all.innerWidth(),
		GSW=function(){			return deffalliw//длина всего места
				-(av.is(":visible") ? av.outerWidth(true) : 0)-ver.outerWidth(true)//ширина доступных блоков и вертикального скрола
				-site.outerWidth(true)+site.width();//border		},
		pp=$("#verhor"),
		pps=pp.val().split(","),
		PlaceParams=function(k,v){
			var val=pp.val().split(","),
				i;
			k=$.inArray(k,["width","height","hidden","scrollaw","scrollah","scrollsw","scrollsh"]);
			for(i=val.length;i<7;i++)
				val.push("-");
			if(k>-1)
				val[k]=parseInt(v);
			pp.val(val.join(","));
			Change.fire();
		},
		cancl=true;

	if(pps[0])
	{		pps[0]=parseInt(pps[0]);
		if(pps[0]>0 && pps[0]<deffalliw-100)
			av.width(pps[0]);	}
	if(pps[1])
	{
		pps[1]=parseInt(pps[1]);
		if(pps[1]>100 && pps[1]<5000)
			all.height(pps[1]);
	}
	if(parseInt(pps[2]))
		av.hide();
	if(pps[3])
		av.scrollLeft(pps[3]);
	if(pps[4])
		av.scrollTop(pps[4]);
	if(pps[5])
		site.scrollLeft(pps[5]);
	if(pps[6])
		site.scrollTop(pps[6]);
	site.width(GSW());

	$(".blocks .ver").mousedown(function(e){		if(!av.is(":visible"))
			return;
		cancl=true;		var avw=av.width(),
			sw=GSW(),
			MM=function(em){				cancl=false;				var diff=em.pageX-e.pageX,
					navw=avw+diff;
				if(navw<0)
				{					diff=-avw;					navw=0;				}
				else if(navw>deffalliw-100)
				{					navw=deffalliw-100;					diff=navw-avw;
				}
				av.width(navw);
				site.width(sw-diff);
				PlaceParams("width",navw);
			};
		e.stopPropagation();
		e.preventDefault();

		$(document).mousemove(MM).mouseup(function(eu){			$(this).off("mousemove",MM).off(eu);
		})	}).click(function(){		if(cancl)
		{			av.toggle();
			site.width(GSW());
			PlaceParams("hidden",av.is(":visible") ? 0 : 1);
		}
	});

	$(".blocks .hor").mousedown(function(e){		var allh=all.height(),
			MM=function(em){
				var nallh=allh+em.pageY-e.pageY;
				if(nallh<100)
					nallh=100;

				if(deffalliw!=all.innerWidth())
				{					var diff=deffalliw-all.innerWidth();
					deffalliw-=diff;					site.width(function(i,w){						return w-diff;					});				}
				all.height(nallh);
				PlaceParams("height",nallh);
			};
		e.stopPropagation();
		e.preventDefault();

		$(document).mousemove(MM).mouseup(function(eu){
			$(this).off("mousemove",MM).off(eu);
		})
	})

	var toav=false,
		tosite=false;
	av.scroll(function(){		if(!toav)
			toav=setTimeout(function(){				PlaceParams("scrollaw",av.scrollLeft());
				PlaceParams("scrollah",av.scrollTop());
				toav=false;
			},100);
	});
	site.scroll(function(){		if(!tosite)
			tosite=setTimeout(function(){
				PlaceParams("scrollsw",site.scrollLeft());
				PlaceParams("scrollsh",site.scrollTop());
				tosite=false;
			},100);
	});
}