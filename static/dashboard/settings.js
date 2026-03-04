// Eleanor CMS © 2025 --> https://eleanor-cms.com
//Universal abstract base for pages of settings.
export default (template,{config,L10N,L10NS})=>({
	template,
	data:()=>({
		config:Object.keys(config).reduce((a,key)=>Object.assign(a,{[key]:null}),Object.create(null)),//Copy of config where all keys are NULL
		config_l10n:Object.create(null),

		lang:document.documentElement.lang,
		l10n:Object.seal({
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},
		}),
		mono:L10NS===null,//Mono language
		l10ns:[],

		changed:new Set,
		saving:false,
		loading:false,
	}),
	watch:{
		lang(lang){
			for(const[k,v] of Object.entries(this.config_l10n))
				this.config[k]=v[lang] ?? v[""];
		}
	},
	computed:{
		saved(){
			return this.changed.size<1;
		},
		submit_text(){
			return this.saved ? this.l10n.saved : this.l10n.save;
		}
	},
	methods:{
		/** Should be called each time form control being changed by user real time */
		Changed(field,val){
			//Multilingual values
			if(field in this.config_l10n)
			{
				//Default language is always stored with empty key
				const lang=this.L10N===this.lang ? "" : this.lang;

				this.config_l10n[field][lang]=val;
			}

			if(JSON.stringify(config[field])===JSON.stringify(this.config_l10n[field] ?? val))
				this.changed.delete(field);
			else
				this.changed.add(field);
		},

		/** Submitting modified form */
		async Submit(){
			const store=Object.create(null);

			for(const k of this.changed)
				if(JSON.stringify(config[k])!==JSON.stringify(this.config_l10n[k] ?? this.config[k]))
					store[k]=this.config_l10n[k] ?? this.config[k];

			if(Object.keys(store).length<1)
				return;

			this.saving=true;

			await fetch(location.href,{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
				.then(J)
				.then(({ok,error})=>{
					if(ok){
						Object.assign(config,store);

						this.changed.clear();
						this.config_l10n=Object.create(null);
					}
					else if(error)
						alert( this.l10n[error] ?? error );
				},r=>r.text().then(console.error));

			this.saving=false;
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

		for(const[k,v] of Object.entries(config))
		{
			if(!this.mono && v?.constructor?.name === "Object")
			{
				this.config[k]=v[this.lang] ?? v[""];
				this.config_l10n[k]=Object.seal({...v});
			}
			else
				this.config[k]=Array.isArray(v) ? v.slice() : v;

			this.$watch("config."+k,val=>this.Changed(k,val));
		}

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	}
});