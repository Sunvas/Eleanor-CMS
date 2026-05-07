// Eleanor CMS © 2026 --> https://eleanor-cms.com
(({template,container,data},l10n_keys=["slug","title","description","content_source"],form_l10ns=new Map,editor=null,l10ns_enabled=null,save_to=null)=>Vue.createApp({
	template,
	data:()=>({
		//L10n
		l10n:Object.seal({
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},

			delete_page_title:{ru:"‼️ Удаление страницы",en:"‼️ Page removal"},
			delete_page:{ru:"Вы действительно хотите удалить статическую страницу?",en:"Are you sure you want to remove the static page?"},
			delete_l10n:{ru:"Подтверждаете удаление локализации?",en:"Are you sure you want to remove localization?"},
			add_l10n:{ru:"Добавить локализацию?",en:"Add localization?"},
			mono:{ru:"Оставить единственную общую локализацию?",en:"Leave one common localization?"},
		}),
		lang:new URLSearchParams(location.search).get("lang") ?? document.documentElement.lang,
		l10ns:[],

		form:{
			slug:"",
			title:"",
			description:"",
			content_source:Object.create(null),

			status:"",
			l10ns:new Set,
		},

		//Confirmation modal
		confirm:"",
		confirm_title:"",
		confirmed:false,

		changed:new Set,
		saving:false,
		watchers:[]
	}),
	watch:{
		lang(code,old){
			if(!l10ns_enabled || old==='delete')
				return;

			//Loading another localization
			if(this.l10ns.some(item=>item[0]===code))
				return this.form.l10ns.has(code)
					? this.LoadL10N(code,old)
					: this.Confirm(this.l10n.add_l10n,this.LangTitle(code))
						.then(c=>c ? this.LoadL10N(code,old) : (this.lang=old));

			if(code==="delete")
			{
				//Removing the last l10n means removing the whole static page
				if(this.form.l10ns.size<2)
					return this.Confirm(this.l10n.delete_page,this.l10n.delete_page_title)
						.then(c=>c ? fetch(location.href,{method:"DELETE"}).then(J) : null)
						.then(r=>{
							if(!r?.ok)
							{
								this.lang=old;

								return alert(this.l10n[r.error] ?? r.error);
							}

							const url=new URL(location.href);
							url.searchParams.delete("item");
							location.href=url.href;
						});

				//Removing only one selected localization
				return this.Confirm(this.l10n.delete_l10n,this.LangTitle(old)).then(c=>{
					if(!c)
						return this.lang=old;

					this.form.l10ns.delete(old);

					return this.LoadL10N([...this.form.l10ns].pop());
				});
			}

			//Removing all other localizations
			if(this.form.l10ns.size>1)
				this.Confirm(this.l10n.mono,this.LangTitle(old)).then(c=>{
					if(!c)
						return this.lang=old;

					form_l10ns.clear();

					this.form.l10ns=new Set([old]);
				});
		}
	},
	computed:{
		/** Flag that some data is loading */
		loading(){
			return this.watchers.length<1;
		},

		/** Flag that data in from is saved */
		saved(){
			return this.changed.size<1;
		},

		/** Text on submit button */
		submit_text(){
			return this.saved ? this.l10n.saved : this.l10n.save;
		},
	},
	methods:{
		/** Extracting title by language code */
		LangTitle(lang){
			return this.l10ns.find(item=>item[0]===lang)[1];
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

		/** Is called by clicking on "Yes" button of confirmation modal */
		Confirmed(e){
			this.confirmed=e.shiftKey ? 1 : true;
		},

		/** Watchers monitor changing of fields in form object */
		SetWatchers(){
			for(const k of Object.keys(this.form))
				this.watchers.push( this.$watch("form."+k,(val,old)=>this.Changed(k,val,old)) );

			editor[Symbol.for("skip-onchange")]=1;
		},

		/** Stop watchers while data is loading into the form */
		UnSetWatchers(){
			this.watchers.forEach(unwatch=>unwatch());
			this.watchers.length=0;
		},

		/** Load l10n data into the form
		 * @param lang desired language code
		 * @param prev previous language code */
		async LoadL10N(lang,prev=null){
			this.UnSetWatchers();

			//Storing previous values
			if(prev!==null)
			{
				//Switching from monolingual
				if(prev==='')
				{
					prev=[...this.form.l10ns].pop();

					if(lang===prev)
						return;
				}

				this.form.content_source=await editor.save();

				for(const k of l10n_keys)
					form_l10ns.getOrInsert(k,new Map).set(prev,this.form[k]);
			}

			//Loading values from remote
			if(!form_l10ns.getOrInsert("title",new Map).get(lang))
			{
				const url=new URL(location.href);
				url.searchParams.set("lang",lang);

				const r=await fetch(url.href,{headers:{accept:"application/json"}}).then(J)
					.catch(e=>({ok:false,error:e}));

				if(!r?.ok)
				{
					this.SetWatchers();
					return alert(this.l10ns[e.error] ?? e.error);
				}

				for(const k of l10n_keys)
					form_l10ns.getOrInsert(k,new Map).set(lang,r.item[k]);

				this.form.l10ns.add(lang);
			}

			//Applying l10n values
			for(const[k,v] of form_l10ns)
			{
				this.form[k]=v.get(lang);
				v.delete(lang);
			}

			if(this.form.content_source)
				await editor.render(this.form.content_source);
			else
				editor.clear();

			this.lang=lang;
			this.SetWatchers();
		},

		/** Trigger saving process */
		Changed(field,val,old){
			//Don't apply changes while loading values
			if(!this.loading)
				this.changed.add(field);

			//Auto submit feature
			if(!this.saving && !this.saved)
			{
				clearTimeout(save_to);
				save_to=setTimeout(()=>this.Submit(),2e4);
			}
		},

		/** Send changes to the server */
		async Submit(){
			clearTimeout(save_to);

			if(this.changed.size<1)
				return;

			const
				body=new FormData,
				suffix=l10ns_enabled && this.lang ? "_"+this.lang : "";

			this.saving=true;

			if(l10ns_enabled)
			{
				body.set("l10ns",[...this.form.l10ns].join(","));
				this.changed.delete("l10ns");
			}

			//Storing content
			if(this.changed.has("content_source"))
			{
				const
					content=JSON.stringify(await editor.save()),
					regex=l10ns_enabled ? /[\da-f]{64}-[a-z]{2,3}-\d+\.[a-z\d]{3,4}/g : /[\da-f]{64}-\d+\.[a-z\d]{3,4}/g,
					files=JSON.stringify(content.match(regex) ?? []);

				body.set("content_source"+suffix,content);
				body.set("files"+suffix,files);

				this.changed.delete("content_source");
			}

			for(const k of this.changed)
				body.set(l10n_keys.includes(k) ? k+suffix : k,this.form[k]);

			return fetch(location.href,{method:"post",body,headers:{accept:"application/json"}})
				.then(J).then(r=>{
					if(r.ok)
						this.changed.clear();
					else if(r.error)
						alert(this.l10n[r.error] ?? r.error);
				}).finally(()=>{
					this.saving=false;
				});
		},
	},
	created(){
		const {lang}=document.documentElement;

		for(const[k,v] of Object.entries(this.l10n))
			if(v[lang])
				this.l10n[k]=v[lang];

		const{L10N,L10NS,item}=JSON.parse($(data).text());

		//Flag defines how values are stored
		l10ns_enabled=Array.isArray(L10NS);

		if(L10NS?.length)
		{
			if(item.l10ns)
			{
				const l10ns=Array.isArray(item.l10ns) ? item.l10ns : item.l10ns.split(",");

				if(!l10ns.includes(this.lang))
					this.lang=l10ns[0];

				item.l10ns=new Set(l10ns);
			}
			else
			{
				//When page is monolingual
				this.lang="";
				item.l10ns=new Set([L10N]);
			}

			//Filling in the set of l10n
			import("./l10ns.mjs").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});
		}
		else if(!l10ns_enabled)
			l10n_keys.length=0;

		Object.assign(this.form,item);

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	},
	async mounted(){
		const
			ejs=await import("./editorjs.mjs"),
			skip=Symbol.for("skip-onchange"),//Suppress OnChange event of EditorJS for several times
			uploadByFile=async(file)=>{
				const body=new FormData;

				body.set("attach",file);

				if(l10ns_enabled)
					body.set("l10n",this.lang);

				return fetch(location.href,{method:"post",body,headers:{accept:"application/json"}})
					.then(J)
					.then(function(r){
						if(r.ok)
							return{
								success:1,
								file:{
									url:r.path+r.filename
								}
							};

						console.error(r.error);
						return {success:0};
					});
			},
			extra_tools={
				attaches: {
					class: AttachesTool,
					config:{uploader:{uploadByFile}},
				},
				image:{
					class: ImageTool,
					config:{uploader:{
						uploadByFile,
						uploadByUrl:async(url)=>fetch(location.href,{method:"post",body:JSON.stringify({attach:url,l10n:l10ns_enabled ? this.lang : undefined}),headers:{accept:"application/json"}})
							.then(J)
							.then(function(r){
								if(r.ok)
									return{
										success:1,
										file:{
											url:r.path+r.filename
										}
									};

								console.error(r.error);
								return {success:0};
							})
					}}
				}
			};

		editor=await ejs.default(this.$refs.editor,{
			onChange:(api,event)=>editor[skip]-->0 ? void(0) : this.Changed("content_source",api,event),
			data:this.form.content_source
		},extra_tools);
		editor[skip]=1;

		this.SetWatchers();
	}
}).mount(container))
(document.currentScript.dataset);