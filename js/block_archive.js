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
			container:"",
			yearp:".y-prev",
			yearn:".y-next",
			monthp:".m-prev",
			monthn:".m-next"		},
		opts	);
	var c=$(opts.container),
		cache={},
		Go=function()
		{			var k=opts.year+"-"+opts.month;			if(typeof cache[k]=="undefined")
				CORE.Ajax(
					{
						module:opts.module,
						event:"archive",
						month:opts.month,
						year:opts.year
					},
					function(r)
					{						cache[r.year+"-"+r.month]=r.archive;
						c.html(r.archive);
						opts.month=r.month;
						opts.year=r.year;
					}
				);
			else
				c.html(cache[k]);
		},
		YearN=function(){
			if(opts.year<today.getFullYear())
			{
				opts.year++;
				Go();
			}
			return false;
		},
		YearP=function(){
			if(opts.year>1991)
			{
				opts.year--;
				Go();
			}
			return false;
		};
	cache[opts.year+"-"+opts.month]=c.html();

	c.on("click",opts.yearp,YearN)
	.on("click",opts.yearn,YearP)
	.on("click",opts.monthn,function(){		if(opts.month>=12)
		{			opts.month=1;
			YearN();		}
		else
		{			opts.month++;
			Go();		}
		return false;	})
	.on("click",opts.monthp,function(){		if(opts.month<=1)
		{
			opts.month=12;
			YearP();
		}
		else
		{
			opts.month--;
			Go();
		}
		return false;	})
}