// Eleanor CMS © 2025 --> https://eleanor-cms.com

(({template,container,display_name,name,avatar})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:{
				NAME_EXISTS:{ru:"Такой пользователь уже существует",en:"Such username is already exists"},
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"}
			},

			name,
			display_name,
			password:"",

			avatar,
			name_error:false,
			password2:"",

			saved:false,
			loading:false
		}),
		watch:{
			name:"CheckName",
			password2(n){
				this.$refs.password2.setCustomValidity(this.password!==n ? this.l10n.PASS_MISMATCH : "");
			}
		},
		methods:{
			CheckName(n){
				if(this.name==="")
				{
					this.name_error=false;
					return this.$refs.name.setCustomValidity("");
				}

				fetch(location.pathname+"?"+new URLSearchParams({check_name:n ?? this.name}).toString(),{headers:{accept:"application/json"}})
					.then(J).then(({ok})=>{
					this.name_error=!ok;
					this.$refs.name.setCustomValidity(ok ? "" : this.l10n.NAME_EXISTS);
				});
			},
			Submit(){
				if(this.loading)
					return;

				const body=JSON.stringify({
					name:this.name,
					display_name:this.display_name,
					password:this.password,
				});

				this.loading=true;
				fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
						{
							this.saved=true;
							location.reload();
						}
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error))
					.finally(()=>this.loading=false);
			},
		},
		created(){
			const {lang}=document.documentElement;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			this.CheckName();
			$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
		}
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);