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
	var form=$("#multisite"),
		children="table",//Имя дочерних тегов, которые являются контернерами каждого сайта

		max=form.children(children).size(),
		waitsubmit=false,
		tosubmit=0;

	form
	.children(children).each(function(){
		var th=$(this),
			sites,
			F=function(){
				var empty=true;
				sites.each(function(){
					var v=$(this).is(":checkbox") ? ($(this).prop("checked") ? 1 : "") : $(this).val();
					if($.inArray(v,["",$(this).data("def")])==-1)
					{
						empty=false;
						return false;
					}
				});
				if(empty)
					th.addClass("empty");
				else
					th.removeClass("empty");
			}
		sites=th.on("checkempty",F).find("[name^=\"sites[\"]");
		F();
	}).end()

	//AddSite
	.on("click",".addsite",function(){
		form.find(children+":first").clone().find("script").remove().end()
		.find(":input").prop("name",function(ind,old){
				return old.replace(/sites\[[^\]]*\]$/,"["+max+"]");
			}).not("[type=button],[type=submit],[type=number]").val("").end()
			.prop("disabled",false).removeClass("redf greenf").end()
		.find("[id]").prop("id",function(ind,old){
			return old+"-"+max;
		}).end().appendTo(form)
		.find(".langtabs").each(function(){
			try
			{
				var actcl=false;
				$("a",this).each(function(){
					if($(this).hasClass("selected"))
						actcl=$(this);
					$(this).data("rel",$(this).data("rel")+"-"+max)
				}).Tabs();
				if(actcl)
					actcl.click();
			}
			catch(e){}
		}).end();
		max++;
		return false;
	})

	//DeleteSite
	.on("click",".delsite",function(){
		var t=$(this).closest(children);
		if(form.children(children).size()>1)
			t.remove();
		else
			t.find(".db").removeClass("redf greenf").end()
			.find(":input").not("[type=button],[type=submit],[type=number]").val("");
		return false;
	})

	//Check Db
	.on("click",".checkdb",function(){
		var can=true,
			data={},
			dbs=$(this).closest(children).find(".db:not(:disabled,[name$=\"[host]\"][value=\"\"])").removeClass("redf greenf")
			.filter("[name$=\"[host]\"],[name$=\"[db]\"],[name$=\"[user]\"]").each(function(){
				if($(this).val()=="")
				{
					$(this).addClass("redf");
					can=false;
				}
			}).end()
		if(!can)
			return false;
		dbs.each(function(){
			data[$(this).prop("name").match(/\[([^\]]+)\]$/)[1]]=$(this).is(":checkbox") ? $(this).prop("checked") ? 1 : 0 : $(this).val();
		})
		CORE.Ajax(
			{
				direct:"admin",
				file:"multisite",
				event:"checkdb",
				data:data
			},
			function(r)
			{
				if(r)
					switch(r)
					{
						case"connect":
							dbs.filter("[name$=\"[host]\"],[name$=\"[user]\"],[name$=\"[pass]\"],[name$=\"[db]\"]").addClass("redf");
						break;
						case"prefix":
							dbs.filter("[name$=\"[prefix]\"]").addClass("redf");
						break;
						case"prefix":
							dbs.filter("[name$=\"[prefix]\"],[name$=\"[db]\"]").addClass("redf");
						break;
						default:
							dbs.addClass("redf");
					}
				else
				{
					dbs.addClass("greenf");
					if(tosubmit>0 && --tosubmit==0 && waitsubmit)
						form.submit();
				}
			}
		);
		return false;
	})

	//Changing db fields
	.on("change",".db",function(){
		var th=$(this),
			dbs=th.closest(children).find(".db").removeClass("redf greenf");
		if(th.is("[name$=\"[host]\"]"))
			dbs.not(this).not("[name$=\"[prefix]\"]").prop("disabled",th.val()=="")
	})
	.find("[name$=\"[host]\"]").change().end()

	//Changing secret of site
	.on("change","[name$=\"[secret]\"]",function(){
		var th=$(this),
			trs=th.closest(children).find(".checkdb").closest("tr").nextAll().andSelf();
		if(th.val()=="")
			trs.show();
		else
			trs.hide();
	})
	.find("input[name$=\"[secret]\"]").change().end()

	//Default highlight errors
	.find(children+":not(.empty) .checkdb").change().end()

	//Form submit
	.submit(function(){
		var can=true;

		waitsubmit=false;
		tosubmit=0;
		$(this).find(children+":not(.empty)").each(function(){
			if($(this).find("input[name$=\"[secret]\"]").val()=="" && $(this).find(".greenf").size()==0)
			{
				tosubmit++;
				$(this).find(".checkdb:first").click();
			}
		})
		waitsubmit=true;
		can=tosubmit==0;

		return can;
	})
})