// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** Sign-in form to user area with optional hCaptcha challenge. */
(({template,account,container,hcaptcha})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				recovered:{ru:"Доступ успешно восстановлен. Перед продолжением рекомендуется сменить пароль.",en:"Access has been successfully recovered. It is recommended to change your password before continuing."},
				recovery_info:{ru:"Чтобы восстановить доступ, заполните любые два из трёх полей: пароль, одноразовый код или резервный код.",en:"To recover access, fill in any two of the three fields: password, one-time passcode, or recovery code."},

				W8:{ru:n=>`Пожалуйста, подождите ${n} секунд(ы). Вы входите слишком часто.`,en:n=>`Please wait for ${n} seconds. You have been signing in too often.`},
				CAPTCHA:{ru:"Пожалуйста, решите капчу",en:"Please solve the captcha"},
				NOT_FOUND:{ru:"Пользователь не найден",en:"User not found"},
				WRONG_TOTP:{ru:"Неверный код",en:"Wrong code"},
				WRONG_PASSWORD:{ru:"Неверный пароль",en:"Wrong password"},
				WRONG_CREDENTIALS:{ru:"Не удалось подтвердить восстановление доступа. Проверьте введённые данные и попробуйте ещё раз.",en:"Failed to confirm access recovery. Check the credentials you provided and try again."},
			}),

			// Form fields sent to the backend
			username:"",
			password:"",
			captcha:"",
			totp:"",
			recovery_code:"",
			remember_me:!!localStorage.getItem("remember_me"),
			allow_cookie:!!localStorage.getItem("allow_cookie"),

			wrong_username:false,
			wrong_password:false,
			wrong_totp:false,

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
			},

			// It is allowed to store and restore user's own checkbox choices locally
			allow_cookie(checked){
				checked ? localStorage.setItem("allow_cookie",1) : localStorage.removeItem("allow_cookie");
			},
			remember_me(checked){
				checked ? localStorage.setItem("remember_me",1) : localStorage.removeItem("remember_me");
			}
		},
		computed:{
			required(){
				return this.recovery ? this.recovery_set.size<2 : false;
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
					totp:this.totp,
					...(this.recovery ? {recovery_code:this.recovery_code} : {temp:this.remember_me ? "" : 1})
				});

				this.loading=true;

				return fetch(account,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.finally(()=>{
						this.loading=false;
					})
					.then(r=>{
						if(r.ok)
						{
							if(this.totp_field && !this.totp)
								localStorage.removeItem("totp_field");

							if(r.recovery)
								alert(this.l10n.recovered);

							return this.Actor(r.id);
						}

						// Field errors
						switch(r.error)
						{
							case"NOT_FOUND":
								alert(this.l10n.NOT_FOUND);

								this.wrong_username=true;
								this.$refs.username.focus();
								this.$refs.username.select();
							return;
							case"WRONG_PASSWORD":
								alert(this.l10n.WRONG_PASSWORD);

								this.wrong_password=true;
								this.$refs.password.focus();
								this.$refs.password.select();
							return;
							case"WRONG_TOTP":
								alert(this.l10n.WRONG_TOTP);

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
							error=r.error.CAPTCHA;
							this.ShowCaptcha();
						}
						else
							error=this.l10n.W8(r.remain);

						alert(error ?? r.error);
					},r=>r.text().then(console.error));
			},

			/** Show recovery form */
			async Recovery(){
				alert(this.l10n.recovery_info);

				this.recovery=true;
				this.totp_required=false;
			},

			/** For back to the sign-in form */
			Back(){
				this.recovery=false;
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
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);