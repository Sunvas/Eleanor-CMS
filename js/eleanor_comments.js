/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.Comments=function(opts)
{
	opts=$.extend(
		{
			lastpost:0,
			postquery:{},
			dataquery:["comments"],
			nextn:0,
			reverse:false,
			page:1,
			pages:1,
			parent:0,
			baseurl:window.location.href,
			container:"#comments",
			nc:"#newcomment",
			autoupdate:15000,
			editor:"text",
		},
		opts
	);
	opts.dataquery=opts.dataquery.reverse();

	var container=$(opts.container),
		updateskip=0,//Задержка автообновления
		autoupdate=opts.autoupdate && opts.pages==opts.page,//Автообновление комментариев
		nochangepages=opts.reverse,//Признак того, что количество страниц менять не нужно
		loadtopage=opts.pages,//Идентификатор страницы, на которую необходимо грузить содержимое
		comments={},//Кэш загруженных комментариев
		paginators={},//Кэш загруженных страниц (листалок)
		urls={},//Кэш ссылок
		oldanswer=false,//Старый ответ
		Prepare=function(q)//Подготовка запроса
		{
			$.each(opts.dataquery,function(i,n){
				var z={};
				z[n]=q;
				q=z;
			})
			return q;
		},
		NewHash=function(h)
		{
			with(window.location)
			{
				if(hash.replace(/#/g,"")==h)
					hash="";
				hash=h;
			}
		},
		HistoryGo,
		ModerateDo=function()
		{
			if(container.children(".moderate").size()>0 && !container.children(".moderate").is(":empty"))
			{
				var ch=container.children(".comments").find("[name=\"mass[]\"]").unbind("click").data("one2all",false);
				$("#masscheck").unbind("click").prop("checked",false);
				One2AllCheckboxes(container,"#masscheck","[name=\"mass[]\"]",true);
				ch.filter(":first").triggerHandler("click");
			}
		},
		GoToPage=function(p,cid,nocache,noh)
		{
			p=p||0;
			cid=cid||false;
			noh=noh||false;
			nocache=nocache||false;

			var noc=typeof comments[p]=="undefined",
				nop=typeof paginators[p]=="undefined",
				onlypages=!noc && nop,

				ops=opts.pages,//old pages
				op=opts.page,//old page
				samep=p==op,//same page

				Fin=function()
				{
					opts.page=p;
					if(!samep)
					{
						comments[op].detach();
						paginators[op].detach();
					}
					ModerateDo();
					if(opts.pages==p)
					{
						loadtopage=p;
						if(opts.autoupdate)
							autoupdate=true;
					}
					if(!noh && !samep)
						CORE.HistoryPush(urls[p]+"#comment"+(cid ? cid : "s"),HistoryGo,p);
				};

			if(!nocache && !noc && !nop)
			{
				if(samep)
					return;
				comments[p].insertAfter(comments[op]);
				paginators[p].insertAfter(paginators[op]);
				return Fin();
			}

			CORE.QAjax(
				$.extend(
					opts.postquery,
					Prepare({
						event:"page",
						baseurl:opts.baseurl,
						reverse:opts.reverse ? 1 : 0,
						onlypages:onlypages ? 1 : 0,
						nochangepages:nochangepages ? 1 :0,
						page:p,
						parent:opts.parent,
						pages:ops
					})
				),
				function(r)
				{
					$(".cnt",container).text(r.cnt);
					opts.pages=r.pages;
					if(p!=r.page)
					{
						if(r.page==op)
							return;
						p=r.page;
						samep=p==op;
					}
					$.each(r.template,function(i,v){
						switch(i)
						{
							case "comments":
								container.children(".nocomments").empty().hide();
								if(samep)
									comments[p].html(v).find("script").remove();
								else
									comments[p]=comments[op].clone().empty().insertAfter(comments[op]).html(v).find("script").remove().end();
								onlypages=false;
							break;
							case "paginator":
								if(samep)
									paginators[p].html(v).find("script").remove();
								else
									paginators[p]=paginators[op].clone().empty().insertAfter(paginators[op]).html(v).find("script").remove().end();
							break;
							case "nocomments":
								container.children(".comments,.paginator,.moderate").empty().hide();
							opts.nextn=0;
							default:
								var ex=container.children("."+i);
								if(v)
									ex.show().html(v);
								else
									ex.empty().hide();
						}
					});
					if(!samep)
					{
						if(onlypages)
							comments[p].insertAfter(comments[op]);
						with(window.location)
						{
							urls[p]=protocol+"//"+hostname+(port ? ":"+port : "")+CORE.site_path+r.url;
						}
					}
					Fin();
					if(r.pages!=ops)
					{
						nochangepages=false;
						var O=paginators[p];
						paginators={};
						paginators[p]=O;
						if(r.pages<ops)
						{
							O=comments[p];
							comments={};
							comments[p]=O;
						}
					}
				}
			);
		},

		//Загрузка новых постов
		to,
		Finish=function(tl)
		{
			clearTimeout(to);
			to=setTimeout(function(){
				container.children(".status").fadeOut("slow",function(){
					$(this).removeClass("load ok error")
				});
			},tl||3000);
		},
		LoadNewComments=function(r)
		{
			if(r)
			{
				loadtopage=opts.page;
				opts.lastpost=r.lastpost;
				opts.nextn=r.nextn;
				$.each(r.template,function(i,v){
					var ex=container.children("."+i);
					switch(i)
					{
						case "comments":
							container.children(".nocomments").empty().hide();
							nochangepages=true;

							if(ex.is(":hidden"))
								ex.show().html(v);
							else if(opts.reverse)
								ex.prepend(v);
							else
								ex.append(v);
							if(r.first)
								NewHash("#comment"+r.first);
							ModerateDo();
						break;
						default:
							if(v)
								ex.show().html(v);
							else
								ex.empty().hide();
					}
				});
				$(".cnt",container).text(opts.nextn);
			}
			$(".status",container).show().removeClass("load error").addClass("ok").text(CORE.Lang(r ? "comments_loaded" : "comments_nonew"));
			Finish();
		},
		DoLNC=function(auto)
		{
			CORE.QAjax(
				$.extend(
					opts.postquery,
					Prepare({
						event:"lnc",
						lastpost:opts.lastpost,
						nextn:opts.nextn,
						baseurl:opts.baseurl,
						reverse:opts.reverse ? 1 : 0,
						parent:opts.parent
					})
				),
				{
					OnBegin:function()
					{
						if(!auto)
						{
							if(autoupdate)
								updateskip=1;
							$(".status",container).removeClass("ok error").addClass("load").text(CORE.Lang("comments_ln")).show();
							clearTimeout(to);
						}
					},
					OnSuccess:function(r)
					{
						if(r || !auto)
						{
							if(auto)
								delete r["first"];
							LoadNewComments(r);
						}
					},
					OnFail:function(s)
					{
						if(auto)
							autoupdate=false;
						else
						{
							$(".status",container).removeClass("load ok").addClass("error").text(s);
							Finish(10000);
						}
					}
				}
			);
		},
		DeleteComments=function(ids,reload)
		{
			CORE.QAjax(
				$.extend(
					opts.postquery,
					Prepare({
						event:"delete",
						ids:ids
					})
				),
				function(r)
				{
					if(r.ids)
					$.each(ids,function(i,v){
						$("#comment"+v).remove();
					});
					if(reload)
						window.location.reload();
					else if(container.children(".comments").children(".comment").size()==0)
						GoToPage(opts.page,false,true);
				}
			);
		};

	ModerateDo();
	urls[opts.page]=window.location.href.replace(/#.+/,"");
	HistoryGo=function(p){ GoToPage(p,false,false,true) }
	this.GoToPage=GoToPage;
	comments[opts.page]=container.children(".comments").find("script").remove().end();
	paginators[opts.page]=container.children(".paginator").find("script").remove().end();
	CORE.HistoryInit(HistoryGo,opts.page);

	container.on("click",".cb-lnc",function(){ DoLNC();return false; }).on("click",".cb-findcomment",function(){
		prompt(CORE.Lang("comments_copy_link"),$(this).prop("href"));
		return false;
	}).on("click",".cb-gocomment",function(){
		var id=$(this).data("id");
		if($("#comment"+id).size()>0)
		{
			NewHash("#comment"+id);
			return false;
		}
		return true;
	}).on("click",".cb-insertnick",function(){
		EDITOR.Insert("[b]"+$(this).text()+"[/b], ");
		return false;
	}).on("click",".cb-delete",function(){
		if(confirm(CORE.Lang("comments_del",[$(this).closest(".comment").find(".cb-findcomment").text(),$(this).data("answers")+1])))
			DeleteComments([$(this).data("id")]);
		return false;
	}).on("click",".cb-edit",function(){
		var th=$(this);
		CORE.QAjax(
			$.extend(
				opts.postquery,
				Prepare({
					event:"edit",
					id:th.data("id")
				})
			),
			function(r)
			{
				var comm=th.closest(".comment");
				comm.find("form").remove().end().find(".text,.signature,.buttons").hide().end().find(".text").after(r).end()
				.find("form").submit(function(){
					var params=CORE.Inputs2object($(this));

					if(params["text"+th.data("id")].length<5)
					{
						alert(CORE.Lang("comments_mintext"));
						return false;
					}

					CORE.Ajax(
						$.extend(
							opts.postquery,
							Prepare($.extend(params,{
								event:"save",
								id:th.data("id"),
								parent:opts.parent
							}))
						),
						function(rs)
						{
							comm.find("form").remove().end().find(".text").html(rs).end().find(".text,.signature,.buttons").not(":empty").show();
							EDITOR.Active("text");
						}
					);
					return false;
				}).end()
				.on("click",".cb-cancel",function(){
					th.closest(".comment").find("form").remove().end().find(".text,.signature,.buttons").not(":empty").show();
					EDITOR.Active("text");
					return false;
				});
			}
		);
		return false;
	}).on("click",".cb-qquote",function(){
		var o=$(this),
			name=o.data("name"),
			text=o.closest(".comment").find(".text:first").html(),
			sel,sele,m;

		if(!o.data("id") || !o.data("date") || !name)
			return true;

		if(window.getSelection)
			sel=window.getSelection().toString();
		else if(document.getSelection)
			sel=document.getSelection().toString();
		else if(document.selection)
			sel=document.selection.createRange().text;
		if(!sel)
			return false;

		if(CORE.browser.firefox)
			while(m=text.match(/<img[^>]+>/))
				text=text.replace(m[0],m[0].indexOf("alt=")==-1 ? "" : m[0].match(/alt="([^"]+)"/)[1]);

		text=text.replace(/<[^>]+>/g,"");
		//Это аналог функции html_entity_decode :)
		text=$("<textarea>").html(text).val();
		sele=sel;
 		while(sele.match(/(\r|\n|\s){2,}/))
	 		sele=sele.replace(/(\r|\n|\s)+/g," ");
 		while(text.match(/(\r|\n|\s){2,}/))
	 		text=text.replace(/(\r|\n|\s)+/g," ");

		if(text.indexOf(sele)!=-1)
		{
			sel=sel.replace(/\s+\n/g,"\n");
			EDITOR.Insert("[quote name=\""+name+"\" date=\""+o.data("date")+"\" c="+o.data("id")+"]\r\n"+sel+"\r\n[/quote]\r\n");
		}
		else
			alert(CORE.Lang("comments_qqe",[name]));
		return false;
	}).on("click",".cb-answer",function(){
		var o=$(this),
			id=o.data("id"),
			p=o.closest(".comment");
		if(oldanswer)
			oldanswer.show();
		oldanswer=id ? p.find(".cb-qquote,.cb-answer").hide() : false;
		$(opts.nc).find("[name=parent]").val(id ? id : opts.parent).end().find(".answerto").html(id ? CORE.Lang("comments_answer",["<a href=\""+window.location.href+"#comment"+id+"\">"+p.find(".cb-findcomment").text()+"</a>"])+" <a href=\"#\" class=\"cb-answer\">X</a>" : CORE.Lang("comments_addc"))
		return false;
	});

	$(opts.nc).submit(function(){
		var name=$("input[name=name]",this),
			text=EDITOR.Get("text"),
			captcha={},
			oau=autoupdate;
		if(name.size()>0 && name.val()=="")
		{
			alert(CORE.Lang("comments_introduce"));
			return false;
		}

		if(text.length<5)
		{
			alert(CORE.Lang("comments_mintext"));
			return false;
		}

		$.each($(this).find("input[name=check]").closest("tr").find(":input").serializeArray(),function(i,n){
			captcha[n.name]=n.value;
		});

		CORE.QAjax(
			$.extend(
				opts.postquery,
				Prepare({
					event:"post",
					lastpost:opts.lastpost,
					nextn:opts.nextn,
					baseurl:opts.baseurl,
					reverse:opts.reverse ? 1 : 0,
					loadcomments:typeof comments[loadtopage]!="undefined",
					name:name.val(),
					text:text,
					captcha:captcha,
					parent:$("input[name=parent]",this).val(),
					rparent:opts.parent
				})
			),
			{
				OnBegin:function()
				{
					autoupdate=false;
					clearTimeout(to);
					$(".status",container).removeClass("ok error").addClass("load").text(CORE.Lang("comments_waitpost")).show();
				},
				OnSuccess:function(r)
				{
					if(r.gotopage)
						GoToPage(r.gotopage,r.cid,true);
					else
					{
						if(loadtopage!=opts.page)
							GoToPage(loadtopage);
						if(r.merged)
						{
							NewHash("comment"+r.merged);
							$("#comment"+r.merged+" .text:first").html(r.text);
							$(".status",container).removeClass("load error").addClass("ok").text(CORE.Lang("comments_merged"));
							Finish();
						}
						else
							LoadNewComments(r);
						$("#captcha").click();
						$("input[name=\"check\"]").val("");
						EDITOR.Set("",opts.editor);
					}
					autoupdate=oau;
				},
				OnFail:function(r)
				{
					autoupdate=oau;
					if(typeof r!="string")
					{
						if(r.captcha)
							$("#captcha").click();
						r=r.error;
					}
					NewHash("commentsinfo");
					$(".status",container).removeClass("load ok").addClass("error").text(r);
					Finish(10000);
				}
			}
		);
		return false;
	});

	container.on("change",".moderate select.modevent",function(){
		var ids=[],nums=[],reload=false;
		container.children(".comments,.parent").find("[name=\"mass[]\"]:checked").each(function(){
			ids.push($(this).val());
			if($(this).closest(".parent").size()==0)
				nums.push("#"+$(this).closest(".comment").find(".cb-findcomment").text());
			else
				reload=true;
		});
		if(ids.length)
			if($(this).val()=="delete")
			{
				if(confirm(CORE.Lang(reload ?  "comments_threaddel" : "comments_del",[nums.join(", ")])))
					DeleteComments(ids,reload);
			}
			else
			{
				CORE.QAjax(
					$.extend(
						opts.postquery,
						Prepare({
							event:"moderate",
							status:$(this).val(),
							ids:ids
						})
					),
					function(r)
					{
						var p=opts.page,
							O=comments[p];
						comments={};
						comments[p]=O;
						nochangepages=false;
						if(reload)
							window.location.reload();
						else
							GoToPage(p,false,true);
					}
				);
			}
		$(this).val("");
		return false;
	})

	setInterval(function(){
		if(autoupdate && updateskip--==0)
		{
			DoLNC(true);
			updateskip=0;
		}
	},opts.autoupdate);
}
