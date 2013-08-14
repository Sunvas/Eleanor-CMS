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
{
	this.opts=$.extend(
		{
			mainlang:CORE.language,
			general:"input[name=\"_onelang\"]:first",
			langs:"input[name=\"_langs[]\"]",
			where:document,
			Switch:function(show,hide,where){
				for(var i in show)
					show[i]="."+show[i];
				for(var i in hide)
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
	{
		var act=[],
			deac=[],
			mainch=th.opts.general ? $(th.opts.general).prop("checked") : false;

		$(th.opts.langs).each(function(){
			if(!mainch && this.checked)
				act.push(this.value);
			else
				deac.push(this.value);
		});

		if(act.length==0 || act.length==1 && $.inArray(th.opts.mainlang,act)!=-1)
			$(th.opts.langs).filter("[value="+th.opts.mainlang+"]").prop("disabled",true).prop("checked",true);
		else
			$(th.opts.langs).filter("[value="+th.opts.mainlang+"]").prop("disabled",false);

		if(act.length==0)
		{
			if(!mainch)
				deac.splice( $.inArray(th.opts.mainlang,deac) ,1);
			th.opts.Switch([th.opts.mainlang],deac,th.opts.where);
		}
		else
			th.opts.Switch(act,deac,th.opts.where);
	};
	$(th.opts.langs).click(th.Click);
	if(th.opts.general)
		$(th.opts.general).click(function(){
			th.Click();
			if(this.checked)
				$(th.opts.langs).parents("div:first").fadeOut("fast");
			else
				$(th.opts.langs).parents("div:first").fadeIn("fast");
		});
	
	setTimeout(th.Click,50);
}