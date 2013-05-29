/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
var EC={
	session:"",//Текущая сессия
	sett_group:[],//Группировка по сходным свойствам
	service:"",//Текущий сервис
	pref_sett:"",//Префикс имен контролов по настройке
	pref_prev:"",//Префикс имен контролов предварительного просмотра
	values:{},//Значения полей предпросмотра
	data:{},//Данные полей настройки
	intv:[],//Вспомагательный массив для серелизации значений с []
	type:"",//Текущий тип
	OnChange:function(){},
	SaveData:function(){
		var type=$("#type-selector").val(),
			group=(typeof this.sett_group[this.type]=="undefined") ? "" : this.sett_group[this.type],
			th=this,
			inputs=$("#edit-control-table").find(":input").filter(function(){
				return ((this.name.indexOf(th.pref_sett)==0 || this.name.indexOf(th.pref_prev)==0) && !$(this).is(".temp"));
			}),
			Merge=function(main,extra){
				$.each(extra,function(k,v){
					if(typeof main[k]=="object" && typeof v=="object")
						Merge(main[k],v);
					else
						main[k]=v;
				});
			},
			params={};

		if(typeof th.intv[group]=="undefined")
			th.intv[group]={};
		$.each(th.intv[group],function(i,v){
			if(i.indexOf("*")!=-1)
			{
				var todel=i.substr(0,i.indexOf("*"));
				eval("try{delete(th.intv[\""+group+"\"][\""+i+"\"]);delete(th.data[\""+group+"\"][\""+todel.replace(/\|/g,"\"][\"")+"\"]);}catch(e){}");
			}
		});
		th.values[group]={};

		if(typeof th.intv[group]=="undefined")
			th.intv[group]={};

		if(typeof th.data[group]=="undefined")
			th.data[group]={};

		if(typeof th.values[group]=="undefined")
			th.values[group]={};

		$.each(inputs.filter("[name^=\""+th.pref_sett+"\"]").serializeArray(),function(i,n){
			params[n.name.substr(th.pref_sett.length)+"+"+i]=n.value;
		});
		Merge(th.data[group],CORE.Inputs2object(params,th.intv[group]));

		params={};
		$.each(inputs.filter("[name^=\""+th.pref_prev+"\"]").serializeArray(),function(i,n){
			params[n.name.substr(th.pref_prev.length)+"+"+i]=n.value;
		});
		Merge(th.values[group],CORE.Inputs2object(params,th.intv[group]));
	},
	ChangeType:function(onlyprev){
		var th=this,
			newtype=$("#type-selector").val(),
			group=(typeof this.sett_group[newtype]=="undefined") ? "" : this.sett_group[newtype],
			d,
			o,
			g=false,
			data;

		this.SaveData();
		if(typeof th.values[group]=="undefined")
		{
			g=(typeof this.sett_group[th.type]=="undefined") ? "" : this.sett_group[th.type];
			d=(typeof th.values[g]=="undefined") ? [] : this.values[g];
		}
		else
			d=this.values[group];

		if(typeof th.data[group]=="undefined")
		{
			g=(typeof this.sett_group[th.type]=="undefined") ? "" : this.sett_group[th.type];
			o=(typeof th.data[g]=="undefined") ? [] : this.data[g];
		}
		else
			o=this.data[group];

		data={
			type:"controls",
			session:this.session,
			service:this.service,
			newtype:newtype,
			onlyprev:onlyprev ? 1 : 0
		};
		data[this.pref_sett]=o;
		data[this.pref_prev]=d;
		CORE.Ajax(
			data,
			function(result)
			{
				if(onlyprev)
					$("#edit-control-preview").html(result);
				else
					$("#edit-control-table").find("tr.temp").remove().end().append(result);
				th.OnChange(onlyprev);
				th.type=newtype;
			}
		);
	},
//Для внутреннего select-a
	Select:function(u)
	{
		$(function(){
			var t=$("#s-table-"+u),
				AppDaD=function()
				{
					t.DragAndDrop({
						items:"tr:has(td)",
						move:".updown",
						replace:"<tr style=\"height:35px\"><td colspan=\"4\">&nbsp;</td></tr>"
					}).find("tr").width(t.innerWidth());
				};

			t.on("click",".sb-plus",function(){
				var tr=$(this).closest("tr");
				tr.clone(false).find("[type=text]").val("").end().insertAfter(tr);
				AppDaD();
			}).on("click",".sb-minus",function(){
				var tr=$(this).closest("tr");
				if(t.find("tr").size()>2)
					tr.remove();
				else
					tr.find("[type=text]").val("");
			});

			AppDaD();

			$("#s-opts-"+u).change(function(){
				if(this.value=="eval")
				{
					$("#s-eval-"+u).show();
					t.hide();
				}
				else
				{
					$("#s-eval-"+u).hide();
					t.show();
				}
			}).change();
		});
	}
};