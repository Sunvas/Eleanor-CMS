/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

function CheckGroup(obj)
{
	if($(obj).find(":checked").size()==0)
	{
		alert(CORE.Lang('nothing_selected'));
		return false;
	}
	return true;
}

function One2AllCheckboxes(parents,main,subnames,and)
{
	and=and||false;
	var checks,
		Rescan=function(){
			checks=$(parents).find(subnames).each(function(){
				if($(this).data("one2all"))
					return;
				$(this).data("one2all",true).change(function(e,mcl,scl){
					if(typeof scl=="undefined")
						scl=true;
					if(scl)
					{
						var checked=checks.filter(":checked").size();
						main.prop("checked",and ? checked==checks.size() : checked>0).triggerHandler("change",[false,true]);
					}
				})
			});
			checks.filter(":first").triggerHandler("change");
		};
	main=$(main).change(function(e,mcl){
		if(typeof mcl=="undefined")
			mcl=true;
		if(mcl)
			checks.each(function(){
				if($(this).data("one2all"))
					$(this).prop("checked",main.prop("checked")).triggerHandler("change",[true,false]);
			});
	});
	Rescan();
	return Rescan;
}