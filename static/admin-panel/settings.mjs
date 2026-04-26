// Eleanor CMS © 2025 --> https://eleanor-cms.com

/** Universal abstract module for pages of settings
 * @param template Placeholder element
 * @param data
 * @param l10n_keys list of config values with l10n contents (internal var)
 * @param config_l10n l10n values of config (internal var) */
export default (template,{config,L10N,L10NS},l10n_keys=[],config_l10n=new Map)=>({
	template,
	data:()=>({
		//Localization
		lang:document.documentElement.lang,
		l10n:Object.seal({
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},
		}),
		l10ns:[],

		//Copy of config where all keys are NULL (for reactivity purpose)
		config:Object.keys(config).reduce((a,key)=>Object.assign(a,{[key]:null}),Object.create(null)),

		changed:new Set,
		saving:false,
		loading:false,
	}),
	watch:{
		lang(lang){
			for(const[k,v] of config_l10n)
				this.config[k]=v[lang] ?? null;
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
			if(l10n_keys.includes(field))
				config_l10n.getOrInsert(field,{})[this.lang]=val;

			if(JSON.stringify(config[field])===JSON.stringify(config_l10n.get(field) ?? val))
				this.changed.delete(field);
			else
				this.changed.add(field);
		},

		/** Submitting modified form */
		async Submit(){
			const store=Object.create(null);

			for(const k of this.changed)
			{
				const cmp=config_l10n.get(k) ?? this.config[k];

				if(JSON.stringify(config[k])!==JSON.stringify(cmp))
					store[k]=cmp;
			}

			if(Object.keys(store).length<1)
				return;

			this.saving=true;

			await fetch(location.href,{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
				.then(J)
				.then(({ok,error})=>{
					if(ok){
						Object.assign(config,store);

						config_l10n.clear();
						this.changed.clear();
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
		if(L10NS?.length)
			import("./l10ns.mjs").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});

		for(const[k,v] of Object.entries(config))
		{
			if(l10n_keys.includes(k))
			{
				this.config[k]=v[this.lang] ?? null;
				config_l10n.set(k,{...v});
			}
			else
				this.config[k]=Array.isArray(v) ? v.slice() : v;

			this.$watch("config."+k,val=>this.Changed(k,val));
		}

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	}
});