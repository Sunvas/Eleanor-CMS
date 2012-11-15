/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.DRAFT=function(opts)
{	opts=$.extend({			url:"",//URL для сохранения
			interval:10,//Интеревал сохранения в секундах
			form:false,//Форма, которую нужно сохранять
			enabled:true,//Флаг включенности
			OnSave:false,//Событие после сохранения
			OnChange:false,//Событие после изменения какого-нибудь контрола		},
		opts
	);
	opts.url=$("<textarea>").html(opts.url).val();

	var th=this,
		to=false,
		id=false,
		inload=false,
		ClearTO=function()
		{			if(to)
				clearInterval(to);
			to=false;
		},
		frame,oa,ot;

	this.enabled=opts.enabled;
	this.OnSave=$.Callbacks("unique");
	this.OnChange=$.Callbacks("unique");
	if(opts.OnSave)
		this.OnSave.add(opts.OnSave);
	if(opts.OnChange)
		this.OnChange.add(opts.OnChange);

	this.Change=function()//Функция насильного уведомления о том, что содержимое изменилось
	{
		if(th.enabled)
		{
			th.OnChange.fire();
			ClearTO();
			to=setTimeout(th.Save,opts.interval*1000);
		}
	}

	this.Save=function()//Функция насильного сохранения черновика
	{		ClearTO();
		if(inload)
			return;

		var f=$(opts.form);		if(!id)
		{			id=new Date().getTime();
			frame=$("<iframe name=\"f"+id+"\">").css({"position":"absolute","left":"-1000px","top":"-1000px"}).width("1px").height("1px").appendTo("body").load(function(){				inload=false;
				th.OnSave.fire($(this.contentWindow.document.body).text());
				frame.remove();
			});
			oa=f.attr("action")||"";
			ot=f.attr("target")||"";
			f.submit(ClearTO);
		}
		f.prop({action:opts.url,target:"f"+id}).submit().prop({action:oa,target:ot});
	}

	opts.form.on("change",":input",th.Change);
}