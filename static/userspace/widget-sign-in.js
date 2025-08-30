// Eleanor CMS © 2025 --> https://eleanor-cms.com

(({template,account,container,hcaptcha})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:{
				ALREADY:{ru:"Вы уже вошли под этим пользователем",en:"You have already signed in into this account"},
				NOT_FOUND:{ru:"Пользователь не найден",en:"User not found"},
				WRONG_PASSWORD:{ru:"Неверный пароль",en:"Wrong password"},
				W8:{ru:n=>`Пожалуйста, подождите ${n} секунд(ы). Вы входите слишком часто.`,en:n=>`Please, wait for ${n} seconds. You have been signing in too often.`},
				W8C:{ru:"Пожалуйста, решите капчу",en:"Please, solve the captcha"},
				restore_password:{ru:"Для входа в учётную запись без пароля используйте Телеграм.",en:"Use Telegram to log into your account without a password."},
			},

			username:"",
			password:"",
			captcha:"",
			allow_cookie:!!localStorage.getItem("allow_cookie"),
			remember_me:!!localStorage.getItem("remember_me"),

			hwid:null,
			loading:false,
			hcaptcha:false
		}),
		watch:{
			//It is not prohibited to store and restore user choices on checkboxes
			allow_cookie(checked){
				checked ? localStorage.setItem("allow_cookie",1) : localStorage.removeItem("allow_cookie");
			},
			remember_me(checked){
				checked ? localStorage.setItem("remember_me",1) : localStorage.removeItem("remember_me");
			}
		},
		methods:{
			Iam(id){
				const url=new URL(location.pathname.endsWith("sign-out") ? document.baseURI : location.href);
				url.searchParams.set("iam",id);
				location.href=url.href;
			},
			Submit(){
				if(this.hcaptcha && !this.captcha)
					return alert(this.l10n.W8C);

				const body=JSON.stringify({
					username:this.username,
					password:this.password,
					captcha:this.captcha,
					temp:!this.remember_me
				});

				this.loading=true;
				fetch(account,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(r=>{
						if(r.ok)
							return this.Iam(r.id);

						let error=null;

						if(r.error==="W8")
							if(hcaptcha)
							{
								r.error="W8C";
								this.ShowCaptcha();
							}
							else
								error=this.l10n.W8(r.remain);
						else
							this.CaptchaReset();

						alert(error ?? this.l10n[r.error] ?? r.error);
					},r=>r.text().then(console.error))
					.finally(()=>this.loading=false);
			},

			Forgot(){
				alert(this.l10n.restore_password);
			},

			CaptchaReset(){
				//Reset captcha
				if(this.hcaptcha)
					return window.hcaptcha.reset(this.hwid);
			},

			ShowCaptcha(){
				this.CaptchaReset();
				//Reset captcha
				if(this.hcaptcha)
					return window.hcaptcha.reset(this.hwid);

				//Show captcha
				this.hcaptcha=true;
				this.$nextTick(()=>{
					this.hwid=window.hcaptcha.render(this.$refs.hcaptcha,{
						sitekey:hcaptcha,
						callback:r=>{
							this.captcha=r;
						},
						'expired-callback':()=>{
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

	let instance;
	L.then(()=>{
		instance=app.mount(container);
	});

	//Via telegram
	window.TelegramAuth=function(telegram){
		const body=JSON.stringify({telegram});

		fetch(account,{body,method:"post",headers:{accept:"application/json"}})
		.then(r=>r.ok ? r.json() : Promise.reject(r))
		.then(r=>{
			if(!r.ok)
				alert(instance.l10n[r.error] ?? r.error);
			else if(r.sign_up)
				location.href=r.sign_up;
			else
				instance.Iam(r.id);
		},r=>r.text().then(console.error));
	};
})(document.currentScript.dataset);