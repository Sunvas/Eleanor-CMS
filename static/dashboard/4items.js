// Eleanor CMS © 2025 --> https://eleanor-cms.com

function Url(USP)
{
	//Filter empty values
	USP.forEach((v,k,o)=>v==="" && o.delete(k,v));

	return location.pathname+(USP.size>0 ? "?"+USP.toString() : "");
}

export default ({total,pp,sort,desc})=>({
	data:()=>({
		USP:new URLSearchParams(location.search),//For reading purposes only
		pps:[25,50,100,200],
		page:1,
		default_sort:"",
		default_order:1,

		pp,
		desc,
		sort,
		total,
	}),
	computed:{
		pages(){
			return Math.ceil(this.total/this.pp);
		},
	},
	methods:{
		/** For links of paginator */
		PP(pp){
			const USP=new URLSearchParams(location.search);

			USP.set("pp",pp);

			return Url(USP);
		},

		/** For links of paginator */
		Page(page){
			const USP=new URLSearchParams(location.search);

			if(page==1)
			{
				USP.delete("page");
				USP.delete("total");
			}
			else
			{
				USP.set("page",page);
				USP.set("total",this.total);
			}

			USP.delete("pp");

			return Url(USP);
		},

		/** Page by user input */
		InputPage(){
			let page=prompt(document.documentElement.lang=="ru" ? "Введите номер страницы" : "Input page number","");
			page=parseInt(page,10);

			if(Number.isInteger(page) && page<=this.pages)
				location.href=this.Page(page);
		},

		/** For links of sort */
		Sort(sort){
			const
				USP=new URLSearchParams(location.search),
				desc=USP.has("order") ? USP.get("order")=="desc" : this.desc;

			if(this.default_sort==sort)
			{
				USP.delete("sort");

				if(desc ^ this.default_order)
					USP.delete("order");
				else
					USP.set("order",this.desc ? "asc" : "desc");
			}
			else
			{
				USP.set("sort",sort);
				this.desc ? USP.delete("order") : USP.set("order","desc");
			}

			USP.delete("pp");

			return Url(USP);
		},

		/** For links of filter */
		Filter(filter,url=true){
			const USP=new URLSearchParams(location.search);

			if(Array.isArray(filter))
				for(const item of filter)
					USP.delete(item);
			else
				for(const[item,key] of Object.entries(filter))
					item=="" ? USP.delete(key) : USP.set(key,item);

			USP.delete("pp");
			USP.delete("page");
			USP.delete("sort");

			return url ? Url(USP) : USP;
		}
	},
	created(){
		this.USP.forEach((v,k,o)=>v==="" && o.delete(k,v));

		const page=this.USP.has("page") ? parseInt(this.USP.get("page")) || 0 : 0;

		if(page>0)
			this.page=page;
	}
});