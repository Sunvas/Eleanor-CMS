/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.Archive=function(opts)
{	var today=new Date();	opts=$.extend(
		{			module:"news",
			month:today.getMonth(),
			year:today.getFullYear(),
			event:"archive",
			container:".blockcalendar",
			yearp:".yearplus",
			yearm:".yearminus",
			monthp:".monthplus",
			monthm:".monthminus"		},
		opts	);
	var c=$(opts.container),
		Go=function()
		{
			CORE.Ajax(
				{
					module:opts.module,
					language:CORE.language,
					event:"archive",
					month:opts.month,
					year:opts.year
				},
				function(r)
				{
					c.html(r.archive);
					opts.month=r.month;
					opts.year=r.year;
				}
			)
		},
		YearP=function(){
			if(opts.year<today.getFullYear())
			{
				opts.year++;
				Go();
			}
			return false;
		},
		YearM=function(){
			if(opts.year>1991)
			{
				opts.year--;
				Go();
			}
			return false;
		}

	c.on("click",opts.yearp,YearP)
	.on("click",opts.yearm,YearM)
	.on("click",opts.monthp,function(){		if(opts.month>=12)
		{			opts.month=1;
			YearP();		}
		else
		{			opts.month++;
			Go();		}
		return false;	})
	.on("click",opts.monthm,function(){		if(opts.month<=1)
		{
			opts.month=12;
			YearM();
		}
		else
		{
			opts.month--;
			Go();
		}
		return false;	})
}