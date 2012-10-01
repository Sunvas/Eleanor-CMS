/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
$.fn.MainMenu=function(opts)
{	opts=$.extend(
		{
			contents:[],
			selclass:"selected",
			delay:100
		}
		,opts
	);

	var n=0,
		entered=-1,
		act=-1,
		timers=[],
		tabs=[],
		contents=[],
		GetRealN=function(to)
		{			var realn;
			if(typeof to=="string" && -1<(realn=$.inArray(to,contents)))
				return realn;

			if(!contents[to])
				return -1;
			return to;		},
		HideMenu=function(to)
		{			to=GetRealN(to);
			if(to==-1)
				return;
			$(contents[to]).fadeOut("fast");
			tabs[to].removeClass("selected");
			if(to==act)
				act=-1;		},
		MenuLeave=function(to)
		{			to=GetRealN(to);
			if(to==-1)
				return;
			entered=-1;		},
		AddMenu=function(obj)
		{			var content=$(obj).data("rel");
			if(opts.contents[n])
				content=opts.contents[n];
			else if(opts.contents[content])
				content=opts.contents[content];
			else if(!content)
				return;
			contents[n]=content;
			tabs[n]=$(obj).mouseenter(function(){				var to=GetRealN(this.n);
				if(to==-1)
					return;
				if(timers[to])
				{
					clearTimeout(timers[to]);
					timers[to]=false;
				}
				$(contents[to]).css('left',tabs[to].position().left).css('top',tabs[to].position().top+tabs[to].outerHeight(true)+'px');
				if(act!=-1)
				{
					$(contents[act]).hide();
					tabs[act].removeClass("selected");
					$(contents[to]).show();
				}
				else
					$(contents[to]).fadeIn("fast");
				tabs[to].addClass("selected");
				act=to;
			}).mouseleave(function(){				var to=GetRealN(this.n);
				if(to==entered)
					return;
				timers[to]=setTimeout(function(){					HideMenu(to);				},opts.delay);			});
			$(content).mouseenter(function(){
				var to=GetRealN(this.n);
				if(to==-1)
					return;
				if(timers[to])
				{
					clearTimeout(timers[to]);
					timers[to]=false;
				}
				entered=to;
			}).mouseleave(function(){				var to=GetRealN(this.n);
				if(to==-1)
					return;
				if(entered==to)
					entered=-1;				timers[to]=setTimeout(function(){
					HideMenu(to);
				},opts.delay);
			}).get(0).n=n;

			tabs[n].get(0).n=n++;
			$(content).hide();		}
	this.each(function(){
		AddMenu(this);
	});	return this;}

function ProgressList(m,cron)
{	var progr={},
		ids=[];
	$("progress[data-id]").each(function(){
		progr[$(this).data("id")]=$(this);
		ids.push($(this).data("id"));
	});

	if(ids.length==0)
		return;
	$("<img>").on("load",function(){
		var img=$(this);
		CORE.Ajax(
			{
				direct:"admin",
				file:m,
				event:"progress",
				ids:ids
			},
			function(res)
			{
				if(!res)
				{
					window.location.reload();
					return;
				}
				var emp=true;
				for(var i in res)
				{
					if(typeof progr[i]=="undefined")
						continue;
					progr[i].val(res[i].val).attr("max",res[i].total).attr("title",res[i].percent+"%").find("span").text(res[i].percent);
					if(res[i].done)
						delete(progr[i]);
					else
						emp=false;
				}
				if(emp)
				{
					window.location.reload();
					return;
				}
				setTimeout(
					function(){
						img.attr("src",cron+"?rand="+Math.random())
					},
					10000
				);
			}
		);
	}).attr("src",cron+"?rand="+Math.random());}