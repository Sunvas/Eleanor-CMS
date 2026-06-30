// Eleanor CMS © 2025 --> https://eleanor-cms.com
(({template,container,data},l10n_keys=["title"],group_l10n=new Map,group=Object.create(null),l10ns_enabled=null)=>Vue.createApp({
	template,
	data:()=>({
		// L10n
		l10n:Object.seal({
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},
			create:{ru:"Создать",en:"Create"},
			creating_group:{ru:"Создание группы",en:"Creating the group"},

			delete_group:{ru:"Вы действительно хотите удалить группу?",en:"Are you sure you want to delete the group?"},
		}),
		lang:document.documentElement.lang,
		l10ns:[],
		L10N:null,

		// Groups list
		items:[],

		// Confirmation modal
		confirm:"",
		confirm_title:"",
		confirmed:false,

		// Group creation & modification modal
		roles:[],
		group_id:0,
		group_title:"",
		group:{
			title:"",
			roles:[],
			slow_mode:0,
		},

		saving:false,
		loading:false,
		changed:new Set,
	}),
	watch:{
		lang(lang){
			for(const[k,v] of group_l10n)
				this.group[k]=v[lang] ?? null;

			if(this.group_id && group_l10n.has("title"))
				this.group_title=group_l10n.get("title")[lang] ?? "#"+this.group_id;
		}
	},
	computed:{
		/** Whether group modal has no unsaved fields */
		saved(){
			// When modal is not shown
			if(this.group_title==="")
				return true;

			return this.changed.size<1;
		},

		/** Text on submit button in group modal */
		submit_text(){
			if(this.group_id)
				return this.saved ? this.l10n.saved : this.l10n.save;

			return this.l10n.create;
		}
	},
	methods:{
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
					.one("hide.coreui.modal",()=>$(":focus",this.$refs.confirm).blur())// Blur focused element before hiding
					.one("hidden.coreui.modal",()=>resolve(this.confirmed))
					.one("shown.coreui.modal",()=>$(this.$refs.confirm_dismiss).focus());
			});
		},

		/** Track real-time group changes in form controls */
		Changed(field,val,old){
			// Don't apply changes while loading values
			if(this.loading)
				return;

			if(l10n_keys.includes(field))
				group_l10n.getOrInsert(field,{})[this.lang]=val;

			if(JSON.stringify(group[field])===JSON.stringify(group_l10n.get(field) ?? val))
				this.changed.delete(field);
			else
				this.changed.add(field);
		},

		/** Loading values from model object (group) to the local variable */
		Load(){
			for(const[k,v] of Object.entries(group))
				if(l10n_keys.includes(k))
				{
					this.group[k]=v[this.lang] ?? null;
					group_l10n.set(k,{...v});
				}
				else
					this.group[k]=Array.isArray(v) ? v.slice() : v;
		},

		/** Group removal */
		async Delete(item,index){
			if(item.id<5)
				return;

			if(!await this.Confirm(this.l10n.delete_group,item.title+" #"+item.id))
				return;

			fetch(this.URL(item.id),{method:"delete",headers:{accept:"application/json"}})
				.then(J)
				.then(({ok,error})=>{
					if(ok)
						this.items.splice(index,1);
					else
						alert(this.l10n[error] ?? error);
				},r=>r.text().then(console.error));
		},

		/** Showing modal to create the group */
		Create(){
			this.group_id=0;
			this.group_title=this.l10n.creating_group;

			this.loading=true;
			Object.assign(group,{
				title:l10ns_enabled ? this.l10ns.reduce((a,[code])=>Object.assign(a,{[code]:""}),{}) : "",
				roles:[],
				slow_mode:10,
			});
			this.Load();
			this.loading=false;

			coreui.Modal.getOrCreateInstance(this.$refs.group).show();
		},

		/** Modifying the group */
		async Modify({id},index){
			this.loading=true;

			return fetch(this.URL(id),{headers:{accept:"application/json"}}).then(J)
			.then(r=>{
				if(r.ok)
				{
					this.group_id=id;
					this.group_title=l10ns_enabled
						? (r.group.title[this.lang] ?? "#"+id)
						: (r.group.title!=="" ? r.group.title : "#"+id);

					Object.assign(group,r.group);
					this.Load();

					coreui.Modal.getOrCreateInstance(this.$refs.group).show();
				}
				else if(r.error)
					alert( this.l10n[r.error] ?? r.error );
			})
			.finally(()=>{
				this.loading=false;
			});
		},

		/** Submitting group modification form */
		async Submit(){
			const
				store=this.group_id ? {} : {...this.group,...Object.fromEntries(group_l10n)},
				USP=new URLSearchParams(location.search);

			if(this.group_id)
			{
				USP.set("group",this.group_id);

				for(const k of this.changed)
				{
					const cmp=group_l10n.get(k) ?? this.group[k];

					if(JSON.stringify(group[k])!==JSON.stringify(cmp))
						store[k]=cmp;
				}
			}

			if(Object.keys(store).length<1)
				return;

			this.saving=true;

			return fetch(location.pathname+"?"+USP.toString(),{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
				.then(J)
				.then(({ok,error,id})=>{
					if(ok){
						Object.assign(group,store);

						const title=l10ns_enabled ? (group.title[this.lang] ?? group.title[this.L10N]) : group.title;

						this.group_title=title;

						// Adding group
						if(id)
						{
							const item={id,...store,title,deletable:true};

							this.group_id=id;
							this.items.unshift(item);
						}
						else
						{
							const group=this.items.find(item=>item.id===this.group_id);

							if(group)
								Object.assign(group,store,{title});
						}

						group_l10n.clear();
						this.changed.clear();
					}
					else if(error)
						alert( this.l10n[error] ?? error );
				},
				r=>r.text().then(console.error))
				.finally(()=>{
					this.saving=false;
				});
		},

		URL(id){
			const USP=new URLSearchParams(location.search);
			USP.set("group",id);
			return location.pathname+"?"+USP.toString();
		},
	},
	created(){
		const {lang}=this;

		for(const[k,v] of Object.entries(this.l10n))
			if(v[lang])
				this.l10n[k]=v[lang];

		const
			url=new URL(location.href),
			{L10N,L10NS,items,roles}=JSON.parse(document.querySelector(data).textContent);

		// Link to users filtered by group
		url.searchParams.delete("zone");
		items.forEach(function(item){
			url.searchParams.set("group",item.id);
			item.filter_users=url.href;
		});

		this.L10N=L10N;
		this.items=items;
		this.roles=roles;

		// Flag defines how values are stored
		l10ns_enabled=Array.isArray(L10NS);

		// Filling in the set of l10n
		if(L10NS?.length)
			import("./l10ns.mjs").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});
		else if(!l10ns_enabled)
			l10n_keys.length=0;

		for(const k of Object.keys(this.group))
			this.$watch("group."+k,(val,old)=>this.Changed(k,val,old));

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	},
	mounted(){
		$(this.$refs.group).on("hidden.coreui.modal",()=>this.group_title="");
	}
}).mount(container))
(document.currentScript.dataset);