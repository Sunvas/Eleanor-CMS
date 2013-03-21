/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

$(function(){
	AppyDragAndDrop();
	$("input[name=multiservice]").change(function(){
		if($(this).prop("checked"))
		{
			$("tr .multitrue").show();
			$("tr .multifalse").hide();
		}
		else
		{
			$("tr .multifalse").show();
			$("tr .multitrue").hide();
		}
	}).change();

	$("select[name=\"services[]\"]").change(function(){
		if($("select[name=\"services[]\"] option:selected").size()==0)
		{
			$("select[name=\"services[]\"] option").prop("selected",true);
			$("#files li").show().find("input[type=\"text\"][name^=\"files[\"]").prop("disabled",false);
		}
		else
			$("select[name=\"services[]\"] option").each(function(){
				var inp=$("input[type=\"text\"][name=\"files["+this.value+"]\"]");
				if($(this).prop("selected"))
					inp.prop("disabled",false).closest("li").show();
				else
					inp.prop("disabled",true).closest("li").hide();
			});
	}).change();

	$("#addsession").click(function(){
		var n=prompt(CORE.Lang("modules_as"),"");
		if(!n)
			return false;

		if($("#sections input[name^=\"sections["+n+"]\"]").size()>0)
		{
			alert(CORE.Lang("modules_seex"));
			return false;
		}

		var newo=$("#sections li:first").clone(false),
			spn=newo.find(".name");
			old=spn.html();
		spn.html(n);

		$("input[name^=\"sections["+old+"]\"]",newo).attr("name",function(){
			return this.name.replace("sections["+old+"]","sections["+n+"]");
		});

		$(".langtabcont",newo).prop("id",function(){
			return this.id.replace(old+"-",n+"-");
		});

		$(".langtabs",newo).prop("id",function(){
			return this.id.replace("-"+old,"-"+n);
		});

		$("a",newo).each(function(){
			$(this).data("rel",($(this).data("rel")||"").replace(old+"-",n+"-"));
		});

		newo.find("input").val("").end().appendTo("#sections");
		try
		{
			$("#langs-"+n+" a").Tabs();
		}
		catch(e){}
		$("#sections .delete").show();
		AppyDragAndDrop();
		return false;
	})

	if($("#sections li").size()==1)
		$("#sections .delete").hide();

	$("#sections").on("click",".delete",function(){
		if($("#sections li").size()==1)
			return false;
		$(this).closest("li").remove();
		if($("#sections li").size()==1)
			$("#sections .delete").hide();
		AppyDragAndDrop();
		return false;
	})
	.on("click",".name",function(){
		var old=$(this).html(),
			n=prompt(CORE.Lang("modules_nn"),old);
		if(!n || n==old)
			return false;

		$(this).html(n);
		$("input[name^=\"sections["+old+"]\"]").prop("name",function(){
			return this.name.replace("sections["+old+"]","sections["+n+"]");
		})
	});

	$("input[name=path]").autocomplete({
		serviceUrl:CORE.ajax_file,
		minChars:2,
		delimiter: null,
		params:{
			direct:"admin",
			file:"autocomplete",
			filter:"onlydir"
		}
	});

	$("#image").change(function(){
		if(this.value && this.value.match(/\.(png|jpe?g|gif)$/))
			$("#preview").attr("src","images/modules/"+this.value.replace("*","small")).show();
		else
			$("#preview").hide();
	}).change().autocomplete({
		serviceUrl:CORE.ajax_file,
		minChars:2,
		delimiter: null,
		params:{
			direct:"admin",
			file:"autocomplete",
			path:"images/modules/",
			filter:"module-image"
		}
	});
});