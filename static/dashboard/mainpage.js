// Eleanor CMS © 2025 --> https://eleanor-cms.com
(({template,container,data})=>Vue.createApp({
	template,
	data:()=>({
		l10n:{
			save:{ru:"Сохранить",en:"Save"},
			saved:{ru:"Сохранено",en:"Saved"},
			placeholder: {ru:"Введите содержимое главной страницы",en:"Enter the contents of the main page"}
		},
		lang:document.documentElement.lang,
		mono:false,
		l10ns:[],

		editor:null,
		content:{},

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
				this.content[old]=await this.editor.save();

			if(this.content[lang])
			{
				this.loading=false;
				return this.editor.render(this.content[lang]);
			}

			const url=new URL(location.href);
			url.searchParams.set("lang",lang);
			fetch(url.toString(),{headers:{accept:"application/json"}})
				.then(J)
				.then(r=>{
					this.content[lang]=r;
					return r ? this.editor.render(r) : this.editor.clear();
				},r=>r.text().then(console.error))
				.finally(()=>this.loading=false);
		}
	},
	methods:{
		async Submit(){
			this.content[this.lang]=await this.editor.save();

			const body=new FormData();

			for(const[k,v] of Object.entries(this.content))
			{
				const b=new Blob( [JSON.stringify(v)] ,{type:"application/json"});
				body.append(k,b,k+".json");
			}

			fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
				.then(J)
				.then(r=>{
					if(r.ok)
						this.saved=true;
				},r=>r.text().then(console.error))
				.finally(()=>this.saving=false);
		},
	},
	created(){
		const {lang}=this;

		for(const[k,v] of Object.entries(this.l10n))
			if(v[lang])
				this.l10n[k]=v[lang];

		const{L10N,L10NS,content}=JSON.parse($(data).text());

		this.mono=L10NS===null;
		this.content[lang]=content;

		//Filling in the set of l10n
		if(!this.mono && L10NS?.length)
			import("./l10ns.js").then(({default:l10ns})=>{
				this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
			});

		$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
	},
	mounted(){
		//https://github.com/codex-team/editor.js/blob/next/types/configs/editor-config.d.ts
		this.editor=new EditorJS({
			holder: this.$refs.editor,
			autofocus:true,
			placeholder:this.l10n.placeholder,
			onChange:()=>this.saved=false,
			...(this.content[this.lang] ? {data:this.content[this.lang]} : {}),
			tools: {
				header: Header,
				raw: RawTool,
				List: {
					class: EditorjsList,
					inlineToolbar: true,
					config: {
						defaultStyle: 'unordered'
					},
				},
			},
		});
	}
}).mount(container)
)(document.currentScript.dataset);