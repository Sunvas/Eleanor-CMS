/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

var uagent=navigator.userAgent.toLowerCase(),
	CORE={	//Настройки	c_domain:"",
	c_prefix:"",
	c_time:"",
	site_path:"",
	ajax_file:"",
	site_host:window.location.protocol+"//"+window.location.host,

	//Языки
	language:"",
	lang:[],
	Lang:function(s,a)
	{		if(typeof s=="string")
		{
			var r=typeof CORE.lang[s]=="undefined" ? "" : CORE.lang[s];

			if(typeof r=="string")
			{
				a=a||[];
				for(var i in a)
					r=r.replace("{"+i+"}",a[i]);
			}
			return r;
		}
		else
		{			a=a||"";			for(var i in s)
				CORE.lang[a+i]=s[i];
		}
	},

	//Браузер
	browser:
	{
		safari:(uagent.indexOf('safari')!=-1 || navigator.vendor=="Apple Computer, Inc." || uagent.indexOf('konqueror')!=-1 || uagent.indexOf('khtml')!=-1),
		opera:uagent.indexOf('opera')!=-1,
		ie:(uagent.indexOf('msie')!=-1 && !this.opera && !this.safari),
		firefox:uagent.indexOf('firefox')!=-1,
		chrome:uagent.indexOf('chrome')!=-1
	},
	//Ajax обертка
	in_ajax:[],
	after_ajax:[],
	Ajax:function(arr,func,err)
	{		var info={};
		switch(typeof func)
		{			case "function":
				info.OnSuccess=func;
			break;
			case "object":
				info=func;		}
		if(typeof err!="undefined")
			info.OnFail=err;
		info=$.extend(
			{				OnBegin:function(){ CORE.ShowLoading() },
				OnEnd:function(){ CORE.HideLoading() },
				OnSuccess:function(){},
				OnFail:function(s)
				{					if($.isPlainObject(s))
					{						var r="";
						$.each(s,function(k,v){							r+=$.isNumeric(k) && CORE.Lang(v) ? CORE.Lang(v) : v;
							r+="\n";						})
						s=r;					}
					alert(s);
				}			},
			info
		);
		CORE.in_ajax.push(true);
		return $.ajax({			type:"POST",
			url:CORE.site_host+CORE.site_path+CORE.ajax_file,
			data:arr,
			beforeSend:info.OnBegin,
			success:function(r)
			{				function Soccess()
				{					try{info.OnSuccess(r.data)}catch(e){}
					$.each(r.head,function(i,H){ CORE.AddHead(i,H) });
				}
				if(!r || r.error || typeof r.data=="undefined")
					try{info.OnFail(r.error ? r.error : r||"No data")}catch(e){}
				else if($.isArray(r.scripts) && r.scripts.length>0)
					CORE.AddScript(r.scripts,Soccess);
				else
					Soccess();
			},
			dataType:"json",
			complete:function(jqXHR,status){				info.OnEnd(jqXHR,status);
				CORE.in_ajax.pop();
				if(CORE.in_ajax.length==0)
				{
					var len=CORE.after_ajax.length;
					$.each(CORE.after_ajax,function(i,F){ try{F()}catch(e){} });
					CORE.after_ajax.splice(0,len);
				}			},
			error:function(jqXHR,status,error){ try{info.OnFail(error,status,jqXHR)}catch(e){} }
		});	},
	QAjax:function()
	{		var a=arguments;		if(CORE.in_ajax.length)
			CORE.after_ajax.push(function(){				CORE.Ajax.apply(CORE,a);			});
		else
			CORE.Ajax.apply(CORE,a);	},
	Inputs2object:function(O,ef)//Empty filler
	{
		var R={};

		if(O instanceof jQuery)
		{			if(O.size()==0)
				return {};
			var params={};
			$.each(O.serializeArray(),function(i,n){
				params[n.name+"+"+i]=n.value;
			});
			O=params;
		}
		else if($.isEmptyObject(O))
			return {};

		$.each(O,function(k,v){			var emp="",
				LR=R;
			k=k.replace(/\+\d+$/,"");
			$.each(k ? k.replace(/^\[|\]/g,"").split("[") : [],function(kk,vv){				if(vv=="")
				{					emp+="*";
					if(typeof ef!="object")
						ef={};
					if(typeof ef[emp]=="undefined")
						ef[emp]=0;
					vv=ef[emp]++;
				}
				else
					emp+=vv+"|";

				if(typeof LR[vv]!="object")
					LR[vv]={};
				LR=LR[vv];			});
			LR[""]=v;
		});
		CORE.NormObj(R);
		return R;
	},
	NormObj:function(O)
	{		var i;		for(i in O)
			if(typeof O[i][""]!="undefined")
				O[i]=O[i][""];
			else if(typeof O[i]=="object")
				CORE.NormObj(O[i]);
	},

	loading:"#loading",
	ShowLoading:function()
	{		if(CORE.loading)
			$(CORE.loading).show().trigger("show");	},
	HideLoading:function()
	{		if(CORE.loading)
			$(CORE.loading).hide().trigger("hide");	},

	//Установка и удаление кук
	SetCookie:function(name,value,ctime)
	{
		var data=new Date();
		data.setTime(data.getTime()+(ctime ? ctime : CORE.c_time)*1000);
		document.cookie=escape(CORE.c_prefix+name)+"="+escape(value)+';expires='+data.toGMTString()+";domain="+CORE.c_domain+";path="+CORE.site_path;
	},
	GetCookie:function(name)
	{
		var res;
		if(res=document.cookie.match(new RegExp(escape(CORE.c_prefix+name)+"=([^;]+)","i")))
			return unescape(res[1]);
		return false;
	},

	//Переход по страницам
	JumpToPage:function(result,pages)
	{
		var s=prompt(CORE.Lang('page_jump'),'');
		if(!s || isNaN(s) || (s=parseInt(s))<=0)
			return;
		pages=parseInt(pages);
		if(s>pages)
			s=pages;
		if(typeof result=="function")
			result(s);
		else
			window.location.href=result.replace('{page}',s);
	},

	//Добавляем стиль
	head:[],//Дополнения в head
	AddHead:function(key,data)
	{		var m=false;		if(m=key.match(/^[0-9]+$/) || $.inArray(key,CORE.head)!=1)
		{
			$("head:first").append(data);
			if(!m)
				CORE.head.push(key);
		}
	},

	//Загружаемые скрипты
	scripts:[],
	AddScript:function(s,func)
	{		if(!$.isArray(s))
			s=[s];

		var num=0,
			texts={},
			F=function(){if(s.length==num){				$.each(s,function(i,n){					if(texts[i])
						$.globalEval(texts[i]);				});
				if($.isFunction(func))
					func();			}};
		$.each(s,function(i,n){			if(n && $.inArray(n,CORE.scripts)==-1)
			{				if(n.indexOf("://")>0)
					$.ajax({
						url:n,
						success:function(d){
							CORE.scripts.push(n);
							texts[i]=false;
							num++;
							F();
						},
						dataType:"script",
						async:false,
						cache:true
					});
				else
					$.get(n,{},function(d){						CORE.scripts.push(n);						texts[i]=d;
						num++;
						F();					},"text");			}
			else
			{				texts[i]=false;
				num++;
				F();			}		});
	},

	//Для манипуляции с историей
	history:false,
	//Opera bug :-(
	OB:function(){ if(CORE.browser.opera) with(window.location){ $("head base").prop("href",protocol+"//"+hostname+(port ? ":"+port : "")+CORE.site_path) } },
	HistoryInit:function(F,data)
	{		CORE.history=[];
		if($.isFunction(F))
			CORE.history.push(F);
		else
			F=false;
		try
		{
			history.replaceState({f:F ? CORE.history.length-1 : false,data:data||false},"",window.location.href);
			CORE.OB();
		}catch(e){}

		var OnPop=function(e){			var st=e.state||false;
			if(st && st.f!==false && typeof CORE.history[st.f]!="undefined")
				CORE.history[st.f](st.data);
		}
		if(window.addEventListener)
			window.addEventListener("popstate",OnPop,false);
		else
			window.attachEvent("onpopstat",OnPop);
	},
	HistoryPush:function(href,F,data)
	{		if($.isFunction(F))
			CORE.history.push(F);
		else
			F=false;
		try
		{			history.pushState({f:F ? CORE.history.length-1 : false,data:data||false},"",href);
			CORE.OB();
		}catch(e){}	},

	//MultiSite
	mssites:[],
	msisuser:false,
	msservice:"",
	MSQueue:1,
	MSLogin:function(sn)
	{		if(CORE.MSQueue.state()!="resolved" || typeof CORE.mssites[sn]=="undefined" || CORE.msisuser)
			return;

		CORE.ShowLoading();
		$.getJSON(CORE.mssites[sn].address+"ajax.php?direct=mslogin&type=getlogin&c=?&service="+CORE.msservice+(CORE.mssites[sn].secret ? "&secret=1" : ""),function(d){			CORE.HideLoading();
			if(d)
				CORE.Ajax(
					$.extend(
						d,
						{
							direct:"mslogin",
							type:"login",
							sn:sn,
							service:CORE.msservice
						}
					),
					function(r)
					{
						if(r)
							window.location.reload();
					}
				);		});
	},
	MSJump:function(sn)
	{		if(typeof CORE.mssites[sn]=="undefined" || !CORE.msisuser)
			return false;
		CORE.Ajax(
			{				direct:"mslogin",
				type:"prejump",
				sn:sn,
				service:CORE.msservice			},
			function(r)
			{				var form=$("<form method=\"post\">").prop("action",r.address+"ajax.php?direct=mslogin&type=jump&service="+CORE.msservice);
				$.each(r,function(k,v){					$("<input type=\"hidden\">").prop({						name:k,
						value:v					}).appendTo(form);				})
				form.appendTo("body").submit();			}
		);
	}
},
EDITOR=
{
	active:null,
	editors:[],

	//Вставка текста, возможно с обрамлением pre[,after][,F][,id]
	Insert:function(pre,after,F,id)
	{
		if(typeof id=="undefined")
			id=F||after||this.active;
		if(id && this.editors[id])
			try
			{
				return this.editors[id].Insert(pre,after||"",F||0);
			}catch(e){}
		return false;
	},

	//Вставка объектов
	Embed:function(type,data,id)
	{
		if(!id)
			id=this.active;
		if(id && this.editors[id])
			try
			{
				return this.editors[id].Embed(type,data,id);
			}catch(e){}
		return false;
	},

	//Получение значения
	Get:function(id)
	{
		if(!id)
			id=this.active;
		if(id && this.editors[id])
			try
			{
				return this.editors[id].Get();
			}catch(e){}
		return false;
	},

	//Установка значения
	Set:function(text,id)
	{
		if(!id)
			id=this.active;
		if(id && this.editors[id])
			return this.editors[id].Set(text);
		return false;
	},

	//Получение выделения
	Selection:function(id)
	{
		if(!id)
			id=this.active;
		if(id && this.editors[id])
			return this.editors[id].Selection();
		return false;
	},

	//Служебные функции: новый редактор
	New:function(id,cbs)
	{		var F=function(){ return false; };
		this.editors[id]=$.extend({
				Insert:F,//pre,after,PreFunc
				Get:F,
				Set:F,//Text
				Selection:F,
				Embed:F
			},cbs);
		this.active=id;
		return true;
	},

	//Установка активного редактора
	Active:function(id)
	{
		if(id && this.editors[id])
			this.active=id;
	}
}
CORE.MSQueue=$.Deferred();
$(function(){	if(CORE.mssites || CORE.msisuser)
	{		var n=0,
			logined=[];
		$.each(CORE.mssites,function(sn,site){			n++;
			$.getJSON(site.address+"ajax.php?direct=mslogin&type=check&c=?&service="+CORE.msservice,function(d){
				if(d)
					logined[sn]=d;
				if(--n==0)
					CORE.MSQueue.resolve(logined);
			});
		});
	}
	else
		CORE.MSQueue.reject();
	//Подсветим активные пункты меню
	var now="";
	with(window.location)
	{
		now+=protocol+"//"+hostname+(port ? ":"+port : "")+CORE.site_path;
		now=href.substr(now.length);
	}
	$("nav a").filter(function(){		return $(this).attr("href")==now;	}).addClass("active");

	//Определим какие скрипты подключены
	var cut=$("head base").attr("href");
	$("head script").each(function(){		if($(this).attr("src"))
			CORE.scripts.push(this.src.indexOf(cut)==-1 ? this.src : this.src.substr(cut.length));
	});

	//CTRL + Enter для всех форм
	$(document).on("keypress","form textarea",function(e){
		if(e.keyCode==13 && e.ctrlKey)
			$(this).closest("form").submit();
	});
});