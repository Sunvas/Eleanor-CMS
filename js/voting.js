/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
var votings=[];

function Voting(opts)
{	opts=$.extend(
		{			form:"#id",
			similar:false,
			AfterSwitch:$.Callbacks(),
			request:{},
			type:"",
			qcnt:0
		},
		opts
	);

	var types=[],
		th=this;
	this.Switch=function(type,content)
	{		if(opts.type==type)
			return;
		types[opts.type]=$(opts.form).children().detach();		if(typeof types[type]=="undefined")
			$(opts.form).html(content);
		else
			types[type].appendTo(opts.form);
		opts.AfterSwitch.fire(type,opts);
		opts.type=type;
		$.each(votings[opts.similar],function(k,v){
			if(v!=th)
				v.Switch(type,content);
		})
	}

	this.Load=function(type,data)
	{		if(typeof types[type]=="undefined")			CORE.Ajax(
				$.extend(
					{						language:CORE.language,
						voting:{
							data:data,
							type:type
						}					},
					opts.request
				),
				function(r)
				{					th.Switch(type,r);				}
			);
		else
			th.Switch(type);	}

	if(typeof votings[opts.similar]=="undefined")
		votings[opts.similar]=[];
	votings[opts.similar].push(this);
	$(opts.form).submit(function(){		if(typeof types["vote"]!="undefined")
			th.Switch("vote",types["vote"]);
		else
		{			var da={},
				cnt=0;
			$.each($(this).serializeArray(),function(k,v){				if(typeof da[v.name]=="undefined")
					cnt++;
				da[v.name]=true;			})
			if(opts.qcnt==cnt)
				th.Load("vote",CORE.Inputs2object($(this)));
			else
				alert(CORE.Lang("noaq"));		}
		return false;	})
}

Voting.ChecksLimit=function(container,max)
{
	var sels=0,
		bl=false,
		Bl,
		checks=$(":checkbox",container).click(function(){			if($(this).prop("checked"))
			{				if(sels>=max)
					return false;				sels++;
			}
			else if(sels>0)
				sels--;
			Bl();		});
	Bl=function(){		if(sels>=max)
			checks.filter(function(){				return !$(this).prop("checked");			}).prop("disabled",true);
		else
			checks.prop("disabled",false);	};
	checks.triggerHandler("click");
}