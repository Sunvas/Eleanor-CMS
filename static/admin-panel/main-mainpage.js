// Eleanor CMS © 2025 --> https://eleanor-cms.com
(({template,container,data},editor=null,content=new Map)=>Vue.createApp({
	template,
	data:()=>({
		l10n:Object.seal({
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"}
		}),
		lang:document.documentElement.lang,
		l10ns:[],

		loading:false,
		saving:false,
		saved:true,
	}),
	computed:{
		submit_text(){
			return this.saved ? this.l10n.saved : this.l10n.save;
		}
	},
	watch:{
		async lang(lang,old){
			this.loading=true;

			if(!this.saved)
				content.set(old,await editor.save());

			if(content.has(lang))
			{
				this.loading=false;
				return editor.render(content.get(lang));
			}

			const url=new URL(location.href);
			url.searchParams.set("lang",lang);
			await fetch(url.toString(),{headers:{accept:"application/json"}})
				.then(J)
				.then(r=>{
					content.set(lang,r);
					return r ? editor.render(r) : editor.clear();
				},r=>r.text().then(console.error));

			this.loading=false;
		}
	},
	methods:{
		async Submit(){
			content.set(this.lang,await editor.save());

			//ToDo! Convert to put
			const body=new FormData;

			for(const[k,v] of content)
			{
				const b=new Blob( [JSON.stringify(v)] ,{type:"application/json"});
				body.append(k,b,k+".json");
			}

			this.saving=true;

			await fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
				.then(J)
				.then(r=>{
					if(r.ok)
						this.saved=true;
				},r=>r.text().then(console.error));

			this.saving=false;
		},
	},
	created(){
		const {lang}=this;

		for(const[k,v] of Object.entries(this.l10n))
			if(v[lang])
				this.l10n[k]=v[lang];

		const{L10N,L10NS,...extra}=JSON.parse($(data).text());

		content.set(lang,extra.content);

		//Filling in the set of l10n
		if(L10NS?.length)
			import("./l10ns.mjs").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	},
	async mounted(){
		editor=(await import("./editorjs.mjs")).default(this.$refs.editor,{
			onChange:()=>this.saved=false,
			...(content.has(this.lang) ? {data:content.get(this.lang)} : {}),
		});
	}
}).mount(container))
(document.currentScript.dataset);