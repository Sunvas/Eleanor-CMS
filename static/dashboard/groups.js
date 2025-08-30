// Eleanor CMS © 2025 --> https://eleanor-cms.com
(({template,container,data,group={}})=>Vue.createApp({
	template,
	data:()=>({
		//L10n
		l10n:{
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},
			create:{ru:"Создать",en:"Create"},
			creating_group:{ru:"Создание группы",en:"Creating the group"},

			delete_group:{ru:"Вы действительно хотите удалить группу?",en:"Are you sure you want to delete the group?"},
		},
		lang:document.documentElement.lang,
		mono:false,
		l10ns:[],
		L10N:null,

		//Groups list
		items:[],

		//Confirmation modal
		confirm:"",
		confirm_title:2,
		confirmed:false,

		//Group creation & modification modal
		roles:[],
		group_id:0,
		group_title:"",
		group:{
			title:"s",
			roles:[],
			slow_mode:0,
		},
		group_l10n:{},

		saving:false,
		changed:new Set(),
	}),
	watch:{
		lang:"Load"
	},
	computed:{
		/** It shows that there are no unsaved fields in modal of modifying group */
		saved(){
			//When modal is not shown
			if(this.group_title==="")
				return true;

			for(const k of this.changed.values())
				if(JSON.stringify(group[k])!==JSON.stringify(this.group_l10n[k] ?? this.group[k]))
					return false;
				else
					this.changed.delete(k);

			return true;
		},

		/** Text on submit button in modal of modifying group */
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

		/** Should be called each time form control input being changed by user real time */
		Changed(item){
			if(!this.mono && group[item]?.constructor?.name === "Object")
			{
				//Default language is always stored with empty key
				const lang=this.L10N===this.lang ? "" : this.lang;

				this.group_l10n[item]??={...group[item]};
				this.group_l10n[item][lang]=this.group[item];
			}

			this.changed.add(item);
		},

		/** Loading l10n version of values */
		Load(){
			for(const[k,v] of Object.entries(group))
				if(v?.constructor?.name === "Object")
					this.group[k]=this.group_l10n[k]?.[this.lang] ?? this.group_l10n[k]?.[""] ?? v[this.lang] ?? v[""];
				else
					this.group[k]??=v;
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
				},r=>r.text().then(console.error))
				.finally(()=>this.saving=false);
		},

		/** Showing modal to create the group */
		Create(){
			this.group_id=0;
			this.group_title=this.l10n.creating_group;

			Object.assign(group,{
				title:this.mono ? "" : this.l10ns.reduce((a,[code])=>Object.assign(a,{[code==this.L10N ? "" : code]:''}),{}),
				roles:[],
				slow_mode:10,
			});
			Object.assign(this.group,group);

			if(!this.mono)
				this.Load();

			coreui.Modal.getOrCreateInstance(this.$refs.group).show();
		},

		/** Modifying the group */
		Modify({id},index){
			fetch(this.URL(id),{headers:{accept:"application/json"}})
			.then(J).then(r=>{
				if(r.ok)
				{
					this.group_id=id;
					this.group_title=this.mono
						? (r.group.title!="" ? r.group.title : "#"+id)
						: (r.group.title[this.lang] ?? r.group.title[""] ?? "#"+id);

					Object.assign(group,r.group);
					Object.assign(this.group,group);

					if(!this.mono)
						this.Load();

					coreui.Modal.getOrCreateInstance(this.$refs.group).show();
				}
				else if(r.error)
					alert( this.l10n[r.error] ?? r.error );
			});
		},

		/** Submitting group modification form */
		async Submit(){
			const
				store=this.group_id ? {} : {...this.group,...this.group_l10n},
				USP=new URLSearchParams(location.search);

			if(this.group_id)
			{
				USP.set("group",this.group_id);

				for(const k of this.changed.values())
					if(JSON.stringify(group[k])!==JSON.stringify(this.group_l10n[k] ?? this.group[k]))
						store[k]=this.group_l10n[k] ?? this.group[k];
			}

			if(Object.keys(store).length<1)
				return;

			this.saving=true;

			fetch(location.pathname+"?"+USP.toString(),{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
			.then(J).then(({ok,error,id})=>{
				if(ok){
					Object.assign(group,store);

					const title=this.mono ? group.title : (group.title[this.lang] ?? group.title[""]);

					this.group_title=title;

					//Adding group
					if(id)
					{
						this.group_id=id;
						this.items.unshift({id,...store,...{title,deletable:true}});
					}
					else
					{
						const group=this.items.find(item=>item.id===this.group_id);

						if(group)
							Object.assign(group,store,{title});
					}

					this.changed.clear();
					this.group_l10n={};
				}
				else if(error)
					alert( this.l10n[error] ?? error );
			})
				.finally(()=>this.saving=false);
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

		const{L10N,L10NS,items,roles}=JSON.parse($(data).text());

		items.map(function(item){
			item.filter_users=item.filter_users.replace(/&amp;/g,"&");
		});

		this.mono=L10NS===null;
		this.L10N=L10N;
		this.items=items;
		this.roles=roles;

		//Filling in the set of l10n
		if(!this.mono && L10NS?.length)
			import("./l10ns.js").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});

		if(this.mono)
			Object.assign(this.group,group);
		else
			this.Load();

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	},
	mounted(){
		$(this.$refs.group).one("hidden.coreui.modal",()=>this.group_title="");
	}
}).mount(container)
)(document.currentScript.dataset);