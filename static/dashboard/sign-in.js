// Eleanor CMS © 2025 --> https://eleanor-cms.com
(({template,container,hcaptcha})=>Vue.createApp({
	template,
	data:()=>({
		l10n:{
			ALREADY:{ru:"Вы уже вошли под этим пользователем",en:"You have already signed in into this account"},
			NOT_FOUND:{ru:"Пользователь не найден",en:"User not found"},
			WRONG_PASSWORD:{ru:"Неверный пароль",en:"Wrong password"},
			W8:{ru:n=>`Пожалуйста, подождите ${n} секунд(ы). Вы входите слишком часто.`,en:n=>`Please, wait for ${n} seconds. You have been signing in too often.`},
			W8C:{ru:"Пожалуйста, решите капчу",en:"Please, solve the captcha"},
			ACCESS_DENIED:{ru:"Доступ запрещён",en:"Access denied"},
			restore_password:{ru:"Перейдите в базу данных, откройте таблицу <code>users</code>, найдите своего пользователя и очистите у него поле <code>password_hash</code>.\nПосле этого сможете войти под любым паролем, который будет сохранён.",en:"Go to the database, open the <code>users</code> table, find your user and clear the <code>password_hash</code> field.\nAfter that, you will be able to sign in with any password that will be saved."},
		},

		username:"",
		password:"",
		captcha:"",

		alert:"",
		alert_title:"",

		hwid:null,
		loading:false,
		hcaptcha:false
	}),
	methods:{
		async Alert(message,title=""){
			this.alert=message;
			this.alert_title=title;

			return new Promise(resolve=>{
				coreui.Modal.getOrCreateInstance(this.$refs.alert).show();

				$(this.$refs.alert).one("hidden.coreui.modal",()=>resolve());
			});
		},

		Submit(){
			if(this.hcaptcha && !this.captcha)
				return this.Alert(this.l10n.W8C,"🫵");

			const body=JSON.stringify({
				username:this.username,
				password:this.password,
				captcha:this.captcha
			});

			this.loading=true;
			fetch(location.pathname,{body,method:"post",headers:{accept:"application/json"}})
				.then(r=>r.ok ? r.json() : Promise.reject(r))
				.then(r=>{
					if(r.ok)
						return location.reload();

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

					this.Alert(error ?? this.l10n[r.error] ?? r.error,"⛔️");
				},r=>r.text().then(console.error))
				.finally(()=>this.loading=false);
		},

		Forgot(){
			this.Alert(this.l10n.restore_password,"☝️");
		},

		CaptchaReset(){
			//Reset captcha
			if(this.hcaptcha)
				return window.hcaptcha.reset(this.hwid);
		},

		ShowCaptcha(){
			this.CaptchaReset();

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
}).mount(container)
)(document.currentScript.dataset);