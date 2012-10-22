/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
var EleanorTabs=0;

$.fn.Tabs=function(opts)
{	opts=$.extend(
		{
			OnBeforeSwitch:function(a){return true},
			OnEndSwitch:function(a){return true},
			contents:[],
			classname:"selected"
		}
		,opts
	);

	var storekey=location.pathname+location.search+"#"+EleanorTabs++,
		n=0,
		act,
		tabs=new Array(),
		contents=new Array(),

		Switch=function(to,l)
		{
			var realn;
			if(typeof to=="string" && -1<(realn=$.inArray(to,contents)))
				to=realn;

			if(typeof contents[to]=="undefined" || !opts.OnBeforeSwitch(tabs[to]))
				return typeof l=="undefined" ? Switch(0,1) : null;

			for(var i in contents)
				if(i==to)
				{
					$(contents[to]).show();
					tabs[to].addClass(opts.classname);

					try
					{
						localStorage.setItem(storekey,to);
					}catch(e){}
				}
				else
				{
					$(contents[i]).hide();
					tabs[i].removeClass(opts.classname);
				}
			act=to;
			opts.OnEndSwitch(tabs[to]);
		},
		Remove=function(to)
		{
			var realn;
			if(typeof to=="string" && -1<(realn=$.inArray(to,contents)))
				to=realn;

			if(!contents[to])
				return;
			tabs[to].remove();
			$(contents[to]).remove();
			delete(contents[to]);
			delete(tabs[to]);
			if(act==to)
				for(var i in contents)
				{					Switch(i);
					break;				}
		},
		AddTab=function(tab)
		{			var content=$(tab).data("rel");
			if(opts.contents[n])
				content=opts.contents[n];
			else if(opts.contents[content])
				content=opts.contents[content];
			else if(content)
				content="#"+content;
			else
				return;
			contents[n]=content;
			tabs[n]=$(tab).click(function(){				Switch(this.n);
				return false;
			});
			tab.Switch=function()
			{				Switch(tab.n);
			}
			tab.Remove=function()
			{
				Remove(tab.n);
			}
			tabs[n].get(0).n=n++;
			$(content).hide();
		};

	this.each(function(){
		AddTab(this);
	});
	var g=localStorage.getItem(storekey);
	Switch(g ? g : 0);
	return this;
}