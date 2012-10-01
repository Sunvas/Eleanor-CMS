/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

function MultilangChecks(opts)
{	this.opts=$.extend(
		{
			mainlang:"russian",
			general:"input[name=\"_onelang\"]:first",
			langs:"input[name=\"lang[]\"]",
			where:document,
			Switch:function(show,hide,where){				for(var i in show)
					show[i]="."+show[i];				for(var i in hide)
					hide[i]="."+hide[i];
				$(show.join(","),where).show().filter(show[0]).each(function(){
					try
					{
						this.Switch();
					}catch(e){}
				});
				$(hide.join(","),where).hide();
			}
		},
		opts
	);

	var th=this;
	this.Click=function()
	{		var arr=[],
			deac=[],
			mainch=$(th.opts.general).prop("checked");

		$(th.opts.langs).each(function(){
			if(!mainch && this.checked)
				arr.push(this.value);
			else
				deac.push(this.value);
		});

		if(arr.length==0 || arr.length==1 && $.inArray(th.opts.mainlang,arr)!=-1)
			$(th.opts.langs).filter("[value="+th.opts.mainlang+"]").prop("disabled",true).prop("checked",true);
		else
			$(th.opts.langs).filter("[value="+th.opts.mainlang+"]").prop("disabled",false);

		if(arr.length==0)
		{
			if(!mainch)
				delete deac[$.inArray(th.opts.mainlang,deac)];
			th.opts.Switch([th.opts.mainlang],deac,th.opts.where);
		}
		else
			th.opts.Switch(arr,deac,th.opts.where);
	};
	$(th.opts.langs).click(th.Click);
	$(th.opts.general).click(function(){		th.Click();
		if(this.checked)
			$(th.opts.langs).parents("div:first").fadeOut("fast");
		else
			$(th.opts.langs).parents("div:first").fadeIn("fast");	});
	th.Click();
}