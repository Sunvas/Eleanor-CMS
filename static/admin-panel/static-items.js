// Eleanor CMS © 2026 --> https://eleanor-cms.com
(async({template,container,data},l10ns_enabled=null)=>{
	const
		{L10N,L10NS,items,can_create,can_delete,...extra}=JSON.parse($(data).text()),
		items4=(await import('./4items.mjs')).default(extra);

	Vue.createApp({
		extends:items4,
		template,
		data:()=>({
			//L10n
			l10n:Object.seal({
				delete:{ru:"Вы действительно хотите удалить статическую страницу?",en:"Are you sure you want to delete the static page?"},
				ACTIVE:{ru:"ОК",en:"OK"},
				DRAFT:{ru:"Черновик",en:"Draft"},
			}),
			lang:document.documentElement.lang,
			l10ns:[],

			//List of static pages
			items,
			can_create,
			can_delete,
			default_sort:"id",

			//Filters
			id:"",
			slug:"",
			title:"",
			reset:['sort','order','id','slug','title'],//const for clearing purpose

			//Confirmation modal
			confirm:"",
			confirm_title:"",
			confirmed:false,

			//Creating
			creating_title:"",
			saving:false,
		}),
		computed:{
			/** It shows that there are some filters applied to the userlist */
			is_filtered(){
				return !!(this.id || this.slug || this.title);
			},
		},
		methods:{
			LangChanged(){
				if(l10ns_enabled)
					location.href=this.Filter({lang:this.lang===document.documentElement.lang ? "" : this.lang});
			},

			/** Is called by clicking on "Yes" button of confirmation modal */
			Confirmed(){
				this.confirmed=true;
			},

			/** Show confirmation modal dialog */
			async Confirm(message,title){
				this.confirm=message;
				this.confirm_title=title;
				this.confirmed=false;

				return new Promise(resolve=>{
					coreui.Modal.getOrCreateInstance(this.$refs.confirm).show();

					$(this.$refs.confirm)
						.one("hide.coreui.modal",()=>$(":focus",this.$refs.confirm).blur())//Hidden element should be focused
						.one("hidden.coreui.modal",()=>resolve(this.confirmed))
						.one("shown.coreui.modal",()=>$(this.$refs.confirm_dismiss).focus());
				});
			},

			/** Showing modal to create a page */
			Create(){
				this.saving=false;
				coreui.Modal.getOrCreateInstance(this.$refs.creating).show();
			},

			async CreateSubmit(){
				const
					tk=l10ns_enabled ? "title_"+this.lang : "title",
					body=JSON.stringify({
						[tk]:this.creating_title,
						...(l10ns_enabled ? {l10ns:this.lang} : {})
					});

				this.saving=true;

				await fetch(this.ItemURL(0),{method:"post",body,headers:{accept:"application/json"}})
					.then(J)
					.then(r=>{
						if(r.ok)
						{
							this.creating_title="";
							location.href=this.ItemURL(r.id);
						}
						else if(r.error)
							alert( this.l10n[r.error] ?? r.error );
					},r=>r.text().then(console.error));

				this.saving=false;
			},

			/** Static page removal */
			async Delete(item,index){
				if(!await this.Confirm(this.l10n.delete,item.title+" #"+item.id))
					return;

				fetch(this.ItemURL(item.id),{method:"delete",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
							this.items.splice(index,1);
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error));
			},

			/** Makes URL for static page in admin panel */
			ItemURL(id){
				const USP=this.Filter(this.reset,false);
				USP.set("item",id);
				return location.pathname+"?"+USP.toString();
			},

			/** Link to page on user area */
			Link2UserArea(item){
				return (l10ns_enabled ? this.lang+"/" : "")+item.slug.split("/").map(encodeURI).join("/");
			}
		},
		created(){
			const{lang}=this;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			//Flag defines how values are stored
			l10ns_enabled=Array.isArray(L10NS);

			//Filling in the set of l10n
			if(L10NS?.length)
				import("./l10ns.mjs").then(({default:l10ns})=>{
					this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
				});

			for(const f of ["id","slug","title","lang"])
				if(this.USP.has(f))
					this[f]=this.USP.get(f);
		}
	}).mount(container);
})(document.currentScript.dataset);