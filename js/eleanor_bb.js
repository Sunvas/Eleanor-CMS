/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
	=================
	Based on TextAreaSelectionHelper by Sardar <Sardar@vingrad.ru>
	http://forum.vingrad.ru/forum/topic-35775.html
	http://forum.vingrad.ru/forum/topic-84449.html
*/
CORE.BBEditor=function(opts)
{	opts=$.extend({id:"",service:false,smiles:false,ownbb:false},opts);	var th=this,
		div=$("#div_"+opts.id),		textarea=div.find('textarea:first');
	div.find("a").each(function(){		var m=this.className.match(/bbe_([a-z0-9\-]+)/),
			f;
		if(!m)
			return;
		switch(m[1])
		{			case "bold":
				f=function(){th.Bold();return false}
			break;			case "italic":
				f=function(){th.Italic();return false}
			break;
			case "uline":
				f=function(){th.UnderLine();return false}
			break;
			case "strike":
				f=function(){th.Strike();return false}
			break;
			case "left":
				f=function(){th.Left();return false}
			break;
			case "center":
				f=function(){th.Center();return false}
			break;
			case "right":
				f=function(){th.Right();return false}
			break;
			case "justify":
				f=function(){th.Justify();return false}
			break;
			case "hr":
				f=function(){th.Hr();return false}
			break;
			case "url":
				f=function(){th.Url();return false}
			break;
			case "mail":
				f=function(){th.Mail();return false}
			break;
			case "img":
				f=function(){th.Img();return false}
			break;
			case "ul":
				f=function(){th.Ul();return false}
			break;
			case "ol":
				f=function(){th.Ol();return false}
			break;
			case "li":
				f=function(){th.Li();return false}
			break;
			case "tm":
				f=function(){th.Tm();return false}
			break;
			case "c":
				f=function(){th.C();return false}
			break;
			case "r":
				f=function(){th.R();return false}
			break;
			case "tab":
				f=function(){th.Tab();return false}
			break;
			case "nobb":
				f=function(){th.Nobb();return false}
			break;
			case "preview":
				f=function(){th.Preview();return false}
			break;
			case "splus":
				f=function(){th.Plus();return false}
			break;
			case "sminus":
				f=function(){th.Minus();return false}
			break;
			case "font":
				new DropDown({					selector:this,
					left:true,
					top:false,
					rel:"#div_"+opts.id+" .bb_fonts",
					limiter:textarea
				});
			break;
		}
		if(f)
			$(this).click(f);
	}).end().find("select").each(function(){		var m=this.className.match(/bbe_([a-z0-9\-]+)/),
			f;
		if(!m)
			return;
		switch(m[1])
		{
			case "color":
				f=function(){if(this.value){th.Color(this.value);$("option:first",this).prop("selected",true)}}
			break;
			case "font":
				f=function(){if(this.value){th.Font(this.value);$("option:first",this).prop("selected",true)}}
			break;
			case "background":
				f=function(){if(this.value){th.BackGround(this.value);$("option:first",this).prop("selected",true)}}
			break;
			case "size":
				f=function(){if(this.value){th.Size(this.value);$("option:first",this).prop("selected",true)}}
			break;
		}
		if(f)
			$(this).change(f);	});

	this.GetSelectedText=function()
	{
		if(document.selection)
			return document.selection.createRange().text;

		var start=textarea.prop("selectionStart"),
			end=textarea.prop("selectionEnd");

		//Хак для оперы :(
		if(CORE.browser.opera)
		{			var cnt=0,
				left=textarea.val().substring(0,start),
				overflow=start-left.length;

			for(var i=0;i<left.length;i++)
				if(left.charCodeAt(i)==10)
				{					cnt++;
					if(overflow>0)
						overflow--;
					else
						left=left.substr(0,left.length-1);
				}
			start-=cnt;

			left=textarea.val().substring(0,end-cnt);
			for(;i<left.length;i++)
				if(left.charCodeAt(i)==10)
				{					cnt++;					left=left.substr(0,left.length-1);				}
			end-=cnt;
		}
		return textarea.val().substring(start,end);
	}

	/*
		scorl и scorr - Корректировка выделения
	*/
	this.SetSelectedText=function(tag,secondtag,F,scorl,scorr)
	{		return SetSelectedText(textarea,tag,secondtag,F,scorl,scorr);
	}

	this.GetText=function()
	{
		return textarea.val();
	}

	this.SetText=function(text)
	{
		textarea.val(text);
	}

	this.Bold=function()
	{		this.SetSelectedText("[b]","[/b]");
	}

	this.Strike=function()
	{
		this.SetSelectedText("[s]","[/s]");
	}

	this.Italic=function()
	{
		this.SetSelectedText("[i]","[/i]");
	}

	this.Li=function()
	{
		this.SetSelectedText("[*]");
	}

	this.Ol=function()
	{
		this.SetSelectedText("[ol]","[/ol]",function(t){return t ? "\n[*]"+t.replace(/\n/g,"\n[*]")+"\n" : "\n[*]\n[*]\n[*]\n"});
	}

	this.Ul=function()
	{
		this.SetSelectedText("[ul]","[/ul]",function(t){return t ? "\n[*]"+t.replace(/\n/g,"\n[*]")+"\n" : "\n[*]\n[*]\n[*]\n"});
	}

	this.UnderLine=function()
	{
		this.SetSelectedText("[u]","[/u]");
	}

	this.Hr=function()
	{
		this.SetSelectedText("[hr]");
	}

	this.Tab=function()
	{
		this.SetSelectedText("\t");
	}

	this.Left=function()
	{
		this.SetSelectedText("[left]","[/left]");
	}

	this.Right=function()
	{
		this.SetSelectedText("[right]","[/right]");
	}

	this.Center=function()
	{
		this.SetSelectedText("[center]","[/center]");
	}

	this.Justify=function()
	{
		this.SetSelectedText("[justify]","[/justify]");
	}

	this.C=function()
	{
		this.SetSelectedText("[c]");
	}

	this.R=function()
	{
		this.SetSelectedText("[r]");
	}

	this.Nobb=function()
	{
		this.SetSelectedText("[nobb]","[/nobb]");
	}

	this.Tm=function()
	{
		this.SetSelectedText("[tm]");
	}

	this.Url=function()
	{
		var text=this.GetSelectedText(),
			link="http://";
		if(text.match(/^([a-z]{3,10}:\/\/[a-zа-я0-9\/\._\-:]+\.[a-z]{2,5}\/)?(?:[^\s@{}]*)?$/))
			link=text;
		link=prompt(CORE.Lang('enter_adress'),link);
		if(link==null)
			return;
		text=prompt(CORE.Lang('link_text'),text);
		if(text==null)
			return;
		this.SetSelectedText("[url="+link+"]"+text+"[/url]",null,null,("[url="+link+"]").length,-6);
	}

	this.Img=function()
	{
		var link=this.GetSelectedText();
		if(!link)
			link=prompt(CORE.Lang('enter_image_addr'),link);
		if(link==null)
			return;
		this.SetSelectedText("[img]"+link+"[/img]",null,null,5,-6);
	}

	this.Mail=function()
	{
		var link=this.GetSelectedText();
		var link=prompt(CORE.Lang('enter_adress'),link);
		if(link==null)
			return;
		if(!this.IsEmail(link) && !confirm(CORE.Lang('wrong_email')))
			return this.Mail();
		text=prompt(CORE.Lang('link_text'),link);
		if(text==null)
			return;
		this.SetSelectedText("[email="+link+"]"+text+"[/email]",null,null,("[email="+link+"]").length,-8);
	}

	this.Color=function(cn)
	{		if(cn)
			this.SetSelectedText("[color="+cn+"]","[/color]");
	}

	this.BackGround=function(cn)
	{
		if(cn)
			this.SetSelectedText("[background="+cn+"]","[/background]");
	}

	this.Size=function(s)
	{
		if(s)
			this.SetSelectedText("[size="+s+"]","[/size]");
	}

	this.Font=function(s)
	{
		if(s)
			this.SetSelectedText("[font="+s+"]","[/font]");
	}


	this.Plus=function()
	{		textarea.stop(true,true).animate({			height:"+=75"
		});	}

	this.Minus=function()
	{		var h=textarea.height();		if(h-75>100)
			h=75;
		else if(h>100)
			h-=100;
		else
			return;
		textarea.stop(true,true).animate({
			height:"-="+h
		});	}

	this.Preview=function()
	{		var req={type:"bbpreview",text:this.GetText()}
		if(opts.service)			req.service=opts.service;
		if(opts.smiles)
			req.smiles=true;		if(opts.ownbb)
			req.ownbb=true;
		CORE.Ajax(
			req,
			function(result)
			{				var pr=$("<div class=\"preview\">").width($("#div_"+opts.id).parent().width()).insertAfter($("#div_"+opts.id).parent().children("div.preview").remove().end().find("div.bb_yourpanel")),					button_hide=$("<br /><div style=\"text-align:center\"><input type=\"button\" class=\"button\" value=\""+CORE.Lang('hide')+"\" /></div>").find("input").click(function(){					pr.remove();				}).end();
				CORE.ResizeBigImages(pr.html(result).append(button_hide).show());
			}
		);
	}

	this.IsEmail=function(C)
	{
		var A=new RegExp("(@.*@)|(\\.\\.)|(@\\.)|(^\\.)"),B=new RegExp("^.+\\@(\\[?)[a-zA-Z0-9\\-\\.]+\\.([a-zA-Z]{2,4}|[0-9]{1,3})(\\]?)$");
		return(!A.test(C) && B.test(C))
	}

	textarea.focus(function(){EDITOR.Active(opts.id);EDITOR.activebb=th});
	EDITOR.New(
		opts.id,
		{
			Embed:function(type,data)
			{				if(type=="image")
					th.SetSelectedText("[img]"+data.src+"[/img]");
			},
			Insert:function(pre,after,F){ th.SetSelectedText(pre,after,F); },
			Get:function(){ return textarea.val(); },
			Set:function(text){ textarea.val(text); }
		}
	);
}

function SetSelectedText(textarea,tag,secondtag,F,scorl,scorr)
{	textarea.focus();
	scorl=scorl||0;
	scorr=scorr||0;
	tag=tag||"";
	if(document.selection)
	{
		var iesel=document.selection.createRange();
		if(typeof(secondtag)=="string")
		{
			var text=$.isFunction(F) ? F(iesel.text) : iesel.text,
				l=text.replace(/\n/g,'').length;
			iesel.text=tag+text+secondtag;
			iesel.moveEnd("character",-secondtag.length);
			iesel.moveStart("character",-l);
		}
		else
			iesel.text=$.isFunction(F) ? F(text) : tag;
		iesel.select();
	}
	else if(textarea.prop("selectionStart")<=textarea.prop("selectionEnd"))
	{
		var start=textarea.prop("selectionStart"),
			end=textarea.prop("selectionEnd"),
			left=textarea.val().substring(0,start),
			content,
			right,
			sctop=textarea.scrollTop(),
			scleft=textarea.scrollLeft();

		//Хак для оперы :(
		if(CORE.browser.opera)
		{
			var cnt=0,
				overflow=start-left.length;
			for(var i=0;i<left.length;i++)
				if(left.charCodeAt(i)==10)
				{
					cnt++;
					if(overflow>0)
						overflow--;
					else
						left=left.substr(0,left.length-1);
				}

			content=textarea.val().substring(0,end-cnt);
			for(;i<content.length;i++)
				if(content.charCodeAt(i)==10)
				{
					scorr++;
					cnt++;
					content=content.substr(0,content.length-1);
				}
			content=textarea.val().substring(left.length,end-cnt);
			right=textarea.val().substring(left.length+content.length);
		}
		else
		{			content=textarea.val().substring(start,end);
			right=textarea.val().substring(end);
		}

		if($.isFunction(F))
			content=F(content);
		if(typeof secondtag=="string")
		{
			textarea.val(left+tag+content+secondtag+right);
			textarea.get(0).setSelectionRange(start+tag.length+scorl,start+tag.length+content.length+scorr);
		}
		else
		{
			if(typeof tag!="string")
				tag=content;
			textarea.val(left+tag+right);
			if(start!=end)
				textarea.get(0).setSelectionRange(start+scorl,start+tag.length+scorr);
			else
				textarea.get(0).setSelectionRange(start+tag.length+scorl,start+tag.length+scorr);
		}
		textarea.scrollTop(sctop).scrollLeft(scleft);
	}
	else
		textarea.get(0).value+=tag + ((typeof(secondtag)=="string") ? secondtag : "");
	textarea.change();
}

(function()
{	var iekeys={"1":65,"2":66,"4":68,"12":76,"16":80,"19":83,"20":84,"21":85,"26":90},
		keys=
		{
			"b":function()
			{				if(EDITOR.activebb)
					EDITOR.activebb.Bold();
			},
			"i":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Italic();
			},
			"u":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.UnderLine();
			},
			"t":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Tab();
			},
			"l":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Url();
			},
			"e":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Mail();
			},
			"I":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Img();
			},
			"S":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Strike();
			},
			"L":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Left();
			},
			"M":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Center();
			},
			"R":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Right();
			},
			"J":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Justify();
			},
			"h":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Hr();
			}
		};

	$(document).keypress(function(e){
		var key=e.keyCode ? e.keyCode : e.charCode;
		key=iekeys[String(key)] ? iekeys[String(key)] : key;
		if(e && e.ctrlKey)
		{
			key=e.shiftKey ? String.fromCharCode(key).toUpperCase() : String.fromCharCode(key).toLowerCase();
			if(typeof keys[key]=="function")
			{
				keys[key](e);
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}
	});
})();
EDITOR.activebb=false;