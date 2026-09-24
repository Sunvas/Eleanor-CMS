// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** Sign-in form to admin panel with optional hCaptcha challenge. */
(({template,container,hcaptcha})=>Vue.createApp({
	template,
	data:()=>({
		l10n:Object.seal({
			recovered:{ru:"Доступ успешно восстановлен. Перед продолжением рекомендуется сменить пароль.",en:"Access has been successfully recovered. It is recommended to change your password before continuing."},
			recovery_info:{ru:"Чтобы восстановить доступ, заполните любые два из трёх полей: пароль, одноразовый код или резервный код.",en:"To recover access, fill in any two of the three fields: password, one-time passcode, or recovery code."},
			restore_password:{ru:"Перейдите в базу данных, откройте таблицу <code>users</code>, найдите своего пользователя и очистите у него поле <code>password_hash</code>.\nПосле этого сможете войти под любым паролем, который будет сохранён.",en:"Go to the database, open the <code>users</code> table, find your user and clear the <code>password_hash</code> field.\nAfter that, you will be able to sign in with any password that will be saved."},

			W8:{ru:n=>`Пожалуйста, подождите ${n} секунд(ы). Вы входите слишком часто.`,en:n=>`Please wait for ${n} seconds. You have been signing in too often.`},
			CAPTCHA:{ru:"Пожалуйста, решите капчу",en:"Please solve the captcha"},
			ACCESS_DENIED:{ru:"Доступ запрещён",en:"Access denied"},
			WRONG_CREDENTIALS:{ru:"Не удалось подтвердить восстановление доступа. Проверьте введённые данные и попробуйте ещё раз.",en:"Failed to confirm access recovery. Check the credentials you provided and try again."},
		}),

		// Form fields sent to the backend
		username:"",
		password:"",
		captcha:"",
		totp:"",
		recovery_code:"",

		wrong_username:false,
		wrong_password:false,
		wrong_totp:false,

		alert:"",
		alert_title:"",

		// hCaptcha stuff
		hwid:null,
		hcaptcha:false,

		loading:false,
		recovery:false,
		totp_field:false,
		totp_required:false,
		recovery_set:new Set,
	}),
	watch:{
		username(){
			this.wrong_username=false;
			this.totp_required=false;
		},
		password(n){
			this.wrong_password=false;
			n ? this.recovery_set.add("password") : this.recovery_set.delete("password");
		},
		totp(n){
			this.totp_field=true;
			this.wrong_totp=false;
			n ? this.recovery_set.add("totp") : this.recovery_set.delete("totp");
		},
		recovery_code(n){
			n ? this.recovery_set.add("recovery_code") : this.recovery_set.delete("recovery_code");
		}
	},
	computed:{
		required(){
			return this.recovery ? this.recovery_set.size<2 : false;
		}
	},
	methods:{
		async Alert(message,title=""){
			this.alert=message;
			this.alert_title=title;

			return new Promise(resolve=>{
				coreui.Modal.getOrCreateInstance(this.$refs.alert).show();

				$(this.$refs.alert)
					.one("hide.coreui.modal",()=>$(":focus",this.$refs.alert).blur())// Blur the focused element before hiding
					.one("hidden.coreui.modal",()=>resolve());
			});
		},

		async Submit(){
			if(this.hcaptcha && !this.captcha)
				return this.Alert(this.l10n.CAPTCHA,"🫵");

			const body=new URLSearchParams({
				username:this.username,
				password:this.password,
				captcha:this.captcha,
				totp:this.totp,
				...(this.recovery ? {recovery_code:this.recovery_code} : {})
			});

			this.loading=true;

			return fetch(location.pathname,{body,method:"post",headers:{accept:"application/json"}})
				.then(r=>r.ok ? r.json() : Promise.reject(r))
				.finally(()=>{
					this.loading=false;
				})
				.then(async r=>{
					if(r.ok)
					{
						if(this.totp_field && !this.totp)
							localStorage.removeItem("totp_field");

						if(r.recovery)
							await this.Alert(this.l10n.recovered,"☝️");

						return location.reload();
					}

					// Field errors
					switch(r.error)
					{
						case"NOT_FOUND":
							this.wrong_username=true;
							this.$refs.username.focus();
							this.$refs.username.select();
						return;
						case"WRONG_PASSWORD":
							this.wrong_password=true;
							this.$refs.password.focus();
							this.$refs.password.select();
						return;
						case"WRONG_TOTP":
							this.wrong_totp=true;
							this.$nextTick(()=>this.$refs.totp.select());
						case"TOTP":
							this.totp_field=true;
							this.totp_required=true;
							this.$nextTick(()=>this.$refs.totp.focus());

							localStorage.setItem("totp_field",1);
						return;
					}

					let error;

					if(r.error!=="W8")
					{
						error=this.l10n[r.error];
						this.CaptchaReset();
					}
					else if(hcaptcha)
					{
						error=this.l10n.CAPTCHA;
						this.ShowCaptcha();
					}
					else
						error=this.l10n.W8(r.remain);

					this.Alert(error ?? r.error,"⛔️");
				},r=>r.text().then(console.error));
		},

		/** Show recovery form */
		async Recovery(){
			await this.Alert(this.l10n.recovery_info,"☝️");

			this.recovery=true;
			this.totp_required=false;
		},

		/** For back to the sign-in form */
		Back(){
			this.recovery=false;
		},

		/** Show password reset instructions */
		Forgot(){
			this.Alert(this.l10n.restore_password,"☝️");
		},

		/** Reset hCaptcha challenge if it is already shown */
		CaptchaReset(){
			if(this.hwid!==null)
				window.hcaptcha.reset(this.hwid);

			this.captcha="";
		},

		/** Show or reset hCaptcha challenge */
		ShowCaptcha(){
			this.CaptchaReset();

			if(this.hcaptcha)
				return;

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

		this.totp_field=!!localStorage.getItem("totp_field");
	}
}).mount(container)
)(document.currentScript.dataset);