// Eleanor CMS © 2025 --> https://eleanor-cms.com

(({template,container,current,data})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:{
				telegram:{ru:"Телеграм",en:"Telegram"},
				username:{ru:"Логин",en:"Login"},
				current:{ru:"Текущая сессия",en:"Current session"},
				ays:{ru:"Вы уверены?",en:"Are you sure?"},
			},

			current:+current,
			sessions:JSON.parse($(data).text()).toSorted((a,b)=>b.sort-a.sort),
		}),
		methods:{
			Terminate(index,id){
				if(!confirm(this.l10n.ays))
					return;

				const url=new URL(location.href);
				url.searchParams.set("id",id);

				fetch(url.href,{method:"delete",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
							this.sessions.splice(index,1);
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error))
					.finally(()=>this.saving=false);
			},
		},
		created(){
			const {lang}=document.documentElement;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];
		}
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);