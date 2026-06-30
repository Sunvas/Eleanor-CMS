// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** User account sign-up form. */
(({template,container})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				NAME_EXISTS:{ru:"Такой пользователь уже существует",en:"This username already exists"},
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"}
			}),

			name:"",
			display_name:"",
			password:"",

			name_error:false,
			password2:"",
			loading:false
		}),
		watch:{
			name:"CheckName",
			password:"ValidatePasswords",
			password2:"ValidatePasswords"
		},
		computed:{
			saved(){
				return !this.name && !this.display_name && !this.password && !this.password2;
			}
		},
		methods:{
			ValidatePasswords(){
				this.$refs.password2.setCustomValidity(this.password===this.password2 ? "" : this.l10n.PASS_MISMATCH);
			},

			async CheckName(n){
				if(this.name==="")
				{
					this.name_error=false;
					return this.$refs.name.setCustomValidity("");
				}

				const name=n ?? this.name;

				return fetch(location.pathname+"?"+new URLSearchParams({check_name:name}).toString(),{headers:{accept:"application/json"}})
					.then(J).then(({ok})=>{
						if(this.name!==name)
							return;

						this.name_error=!ok;
						this.$refs.name.setCustomValidity(ok ? "" : this.l10n.NAME_EXISTS);
					});
			},
			async Submit(){
				if(this.loading)
					return;

				const body=JSON.stringify({
					name:this.name,
					display_name:this.display_name,
					password:this.password,
				});

				this.loading=true;

				return fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
							location.reload();
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
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