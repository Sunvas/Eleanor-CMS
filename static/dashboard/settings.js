// Eleanor CMS © 2025 --> https://eleanor-cms.com
export default (template,{config,L10N,L10NS})=>({
	template,
	data:()=>({
		config:Object.keys(config).reduce((a,key)=>Object.assign(a,{[key]:null}),{}),
		config_l10n:{},

		lang:document.documentElement.lang,
		l10n:{
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},
		},
		mono:L10NS===null,//Mono language
		l10ns:[],
		changed:new Set(),
		saving:false,
		loading:false,
	}),
	watch:{
		lang:"Load"
	},
	computed:{
		saved(){
			for(const k of this.changed.values())
				if(JSON.stringify(config[k])!==JSON.stringify(this.config_l10n[k] ?? this.config[k]))
					return false;
				else
					this.changed.delete(k);

			return true;
		},
		submit_text(){
			return this.saved ? this.l10n.saved : this.l10n.save;
		}
	},
	methods:{
		/** Should be called each time form control being changed by user real time */
		Changed(item){
			if(!this.mono && config[item]?.constructor?.name === "Object")
			{
				//Default language is always stored with empty key
				const lang=this.L10N===this.lang ? "" : this.lang;

				this.config_l10n[item]??={...config[item]};
				this.config_l10n[item][lang]=this.config[item];
			}

			this.changed.add(item);
		},

		/** Loading l10n version of values */
		Load(){
			for(const[k,v] of Object.entries(config))
				if(v?.constructor?.name === "Object")
					this.config[k]=this.config_l10n[k]?.[this.lang] ?? this.config_l10n[k]?.[""] ?? v[this.lang] ?? v[""];
				else
					this.config[k]??=v;
		},

		/** Submitting modified form */
		Submit(){
			const store={};

			for(const k of this.changed.values())
				if(JSON.stringify(config[k])!==JSON.stringify(this.config_l10n[k] ?? this.config[k]))
					store[k]=this.config_l10n[k] ?? this.config[k];

			if(Object.keys(store).length<1)
				return;

			this.saving=true;

			fetch(location.href,{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
				.then(J).then(({ok,error})=>{
				if(ok){
					Object.assign(config,store);

					this.changed.clear();
					this.config_l10n={};
				}
				else if(error)
					alert( this.l10n[error] ?? error );
			})
				.finally(()=>this.saving=false);
		}
	},
	created(){
		const {lang}=this;

		for(const[k,v] of Object.entries(this.l10n))
			if(v[lang])
				this.l10n[k]=v[lang];

		//Filling in the set of l10n
		if(!this.mono && L10NS?.length)
			import("./l10ns.js").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});

		Object.assign(this.config,config);

		if(!this.mono)
			this.Load();

		for(const k of Object.keys(config))
			this.$watch("config."+k,val=>this.Changed(k,val));

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	}
});