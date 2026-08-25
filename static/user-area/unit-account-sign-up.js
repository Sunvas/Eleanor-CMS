// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** User account sign-up form. */
(({template,container,hcaptcha},headers={accept:"application/json"})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				NAME_EXISTS:{ru:"Такой пользователь уже существует",en:"This username already exists"},
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"},
				CAPTCHA:{ru:"Пожалуйста, решите капчу",en:"Please solve the captcha"},
			}),

			//Form fields sent to the backend
			name:"",
			display_name:"",
			password:"",
			captcha:"",

			//Form related stuff
			password2:"",
			name_error:false,

			//hCaptcha
			hwid:null,
			hcaptcha:false,

			//Other
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

				return fetch(location.pathname+"?"+new URLSearchParams({check_name:name}).toString(),{headers})
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

				const body=new URLSearchParams({
					name:this.name,
					display_name:this.display_name,
					password:this.password,
				});

				this.loading=true;

				return fetch(location.href,{body,method:"post",headers})
					.then(J)
					.then(({ok,redirect,error})=>{
						if(ok)
						{
							$(window).off("beforeunload",this.BeforeUnload);
							location.href=redirect;
						}
						else
						{
							if(error==="CAPTCHA")
								this.CaptchaReset();

							alert(this.l10n[error] ?? error);
						}
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			CaptchaReset(){
				// Reset captcha
				if(this.hwid!==null)
					window.hcaptcha.reset(this.hwid);

				this.captcha="";
			},

			BeforeUnload(e)
			{
				this.saved || e.preventDefault();
			}
		},
		created(){
			const {lang}=document.documentElement;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			$(window).on("beforeunload",this.BeforeUnload);
		},
		mounted(){
			// Show captcha
			if(hcaptcha)
			{
				this.hcaptcha=true;
				this.$nextTick(()=>{
					this.hwid=window.hcaptcha.render(this.$refs.hcaptcha,{
						sitekey:hcaptcha,
						callback:r=>{
							this.captcha=r;
						},
						"expired-callback":()=>{
							this.captcha="";
						},
					});
				});
			}
		}
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);