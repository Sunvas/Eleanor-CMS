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
	var can=false;
	$(obj).find('input[type=checkbox]').each(function(){
		if(this.checked)
		{
			can=true;
			return false;
		}
	});
	if(!can)
		alert(CORE.Lang('nothing_selected'));
	return can;
}

function One2AllCheckboxes(parents,mains,subnames,and)
{	and=and||false;	var checks,
		check=$(mains).click(function(e,mcl){			if(typeof mcl=="undefined")
				mcl=true;
			if(!mcl)
				return;
			var main=$(this);
			checks.each(function(){
				if($(this).data("one2all"))
					$(this).prop("checked",main.prop("checked")).triggerHandler("click",[true,false]);
			});
		}),
		each=function(){			if($(this).data("one2all"))
				return;
			$(this).data("one2all",true);
			$(this).click(function(e,mcl,scl){
				if(typeof mcl=="undefined")
					mcl=true;
				if(typeof scl=="undefined")
					scl=true;
				if(!scl)
					return;
				var checked=and;
				checks.each(function(){
					if($(this).data("one2all"))
						checked=and ? checked && this.checked : checked || this.checked;
				});
				$(mains).prop("checked",checked).triggerHandler("click",[false,true]);
			})
		},
		Manage={			Rescan:function(){				checks=$(parents).find(subnames).each(each);
			}		};
	Manage.Rescan();
	return Manage;}