// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** Sign-in form to user area with optional hCaptcha challenge. */
(({template,account,container,hcaptcha})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				ALREADY:{ru:"Вы уже вошли под этим пользователем",en:"You have already signed in to this account"},
				NOT_FOUND:{ru:"Пользователь не найден",en:"User not found"},
				WRONG_PASSWORD:{ru:"Неверный пароль",en:"Wrong password"},
				W8:{ru:n=>`Пожалуйста, подождите ${n} секунд(ы). Вы входите слишком часто.`,en:n=>`Please wait for ${n} seconds. You have been signing in too often.`},
				CAPTCHA:{ru:"Пожалуйста, решите капчу",en:"Please solve the captcha"},
				restore_password:{ru:"Для восстановления пароля, пожалуйста, обратитесь к администратору.",en:"To reset your password, please contact site administrator."},
			}),

			//Form fields sent to the backend
			username:"",
			password:"",
			captcha:"",
			allow_cookie:!!localStorage.getItem("allow_cookie"),
			remember_me:!!localStorage.getItem("remember_me"),

			//hCaptcha stuff
			hwid:null,
			hcaptcha:false,

			//Other
			loading:false,
		}),
		watch:{
			// It is allowed to store and restore user's own checkbox choices locally
			allow_cookie(checked){
				checked ? localStorage.setItem("allow_cookie",1) : localStorage.removeItem("allow_cookie");
			},
			remember_me(checked){
				checked ? localStorage.setItem("remember_me",1) : localStorage.removeItem("remember_me");
			}
		},
		methods:{
			Actor(id){
				const url=new URL(location.pathname.match(/(sign-out|sign-in)$/) ? document.baseURI : location.href);
				url.searchParams.set("@",id);
				location.href=url.href;
			},

			async Submit(){
				if(this.hcaptcha && !this.captcha)
					return alert(this.l10n.CAPTCHA);

				const body=new URLSearchParams({
					username:this.username,
					password:this.password,
					captcha:this.captcha,
					temp:!this.remember_me
				});

				this.loading=true;

				return fetch(account,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(r=>{
						if(r.ok)
							return this.Actor(r.id);

						let error=null;

						if(r.error==="W8")
							if(hcaptcha)
							{
								r.error="CAPTCHA";
								this.ShowCaptcha();
							}
							else
								error=this.l10n.W8(r.remain);
						else
							this.CaptchaReset();

						alert(error ?? this.l10n[r.error] ?? r.error);

						// Focus input based on error
						switch(r.error)
						{
							case"NOT_FOUND":
								// Wait until browser returns focus after alert
								setTimeout(()=>this.$refs.username.focus(),250);
							break;
							case"WRONG_PASSWORD":
								setTimeout(()=>this.$refs.password.focus(),250);
						}
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			Forgot(){
				alert(this.l10n.restore_password);
			},

			CaptchaReset(){
				// Reset captcha
				if(this.hwid!==null)
					window.hcaptcha.reset(this.hwid);

				this.captcha="";
			},

			ShowCaptcha(){
				this.CaptchaReset();

				if(this.hcaptcha)
					return;

				// Show captcha
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