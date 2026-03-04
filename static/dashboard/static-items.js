// Eleanor CMS © 2026 --> https://eleanor-cms.com
(async({template,container,data})=>{
	const
		{L10N,L10NS,items,can_create,can_delete,...extra}=JSON.parse($(data).text()),
		items4=(await import('./4items.js')).default(extra);

	Vue.createApp({
		extends:items4,
		template,
		data:()=>({
			//L10n
			l10n:Object.seal({
				delete:{ru:"Вы действительно хотите удалить статическую страницу?",en:"Are you sure you want to delete the static page?"},
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
			name:"",
			slug:"",
			title:"",
			reset:['sort','order','id','name','slug','title'],//const for clearing purpose

			//Confirmation modal
			confirm:"",
			confirm_title:"",
			confirmed:false,
		}),
		computed:{
			/** It shows that there are some filters applied to the userlist */
			is_filtered(){
				return !!(this.id || this.name || this.slug || this.title);
			},
		},
		methods:{
			/** Is called by clicking on "Yes" button of confirmation modal */
			Confirmed(){
				this.confirmed=true;
			},

			/** Show confirmation modal */
			async Confirm(message,title){
				this.confirm=message;
				this.confirm_title=title;
				this.confirmed=false;

				return new Promise(resolve=>{
					coreui.Modal.getOrCreateInstance(this.$refs.confirm).show();

					$(this.$refs.confirm)
						.one("hidden.coreui.modal",()=>resolve(this.confirmed))
						.one("shown.coreui.modal",()=>$(this.$refs.confirm_dismiss).focus());
				});
			},

			/** User removal */
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

			ItemURL(id){
				const USP=this.Filter(this.reset,false);
				USP.set("item",id);
				return location.pathname+"?"+USP.toString();
			},
		},
		created(){
			const {lang}=this;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			//Filling in the set of l10n
			if(L10NS?.length)
				import("./l10ns.js").then(({default:l10ns})=>{
					this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
				});

			for(const f of ["id","name","slug","title"])
				this[f]=this.USP.has(f) ? this.USP.get(f) : "";
		}
	}).mount(container);
})(document.currentScript.dataset);