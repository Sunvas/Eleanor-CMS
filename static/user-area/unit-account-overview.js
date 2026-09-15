// Eleanor CMS © 2026 --> https://eleanor-cms.com
/** Overview section of the user account. */
(({template,container,data},USP=new URLSearchParams(location.search),app=null)=>{
	app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				just_now:{ru:"👉 Только что",en:"👉 Just now"},
				password_changed:{ru:"✅ Пароль успешно изменён",en:"✅ Password changed successfully"},
				totp_configured:{ru:"✅ Одноразовые коды успешно настроены",en:"✅ Onetime passcodes are successfully configured"},
				totp_deleted:{ru:"❗️ Одноразовые успешно ОТКЛЮЧЕНЫ",en:"✅ Onetime passcodes are successfully DISABLED"},
				available:{ru:n=>"Доступно: "+n,en:n=>"Available: "+n},
				rc_ok:{ru:"✅ Этот код действителен.\nПроверка не использовала и не аннулировала его.",en:"✅ This code is valid.\nVerification has not used or invalidated it."},
				rc_invalid:{ru:"Этот код НЕ действителен",en:"This code is INVALID."},

				UNVERIFIED:{ru:"Проверка не пройдена",en:"Verification failed"},
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"},
				CURRENT_PASSWORD_USED:{ru:"Использован текущий пароль",en:"Current password is used"},
				INCORRECT_CODE:{ru:"Неправильный код",en:"Incorrect code"},
			}),

			me:{},

			//Recovery grace period
			to:null,
			verification:"",
			recovery_grace_remaining:0,

			//Change password stuff
			change_password:false,
			password_changed:false,
			password:"",
			password2:"",

			//TOTP stuff
			totp:null,
			totp_changed:false,
			totp_digits:6,
			totp_issuer:location.hostname,
			totp_secret:"",
			totp_code:"",
			totp_qr:null,

			//Recovery codes stuff
			rc:false,
			rc_hash:"",
			rc_check:"",
			rc_codes:"",
			rc_session_id:"",
			rc_rows:10,
			rc_created:false,

			loading:false
		}),
		computed:{
			recovery_grace_timer(){
				return [Math.trunc(this.recovery_grace_remaining/60),this.recovery_grace_remaining%60]
					.map(v=>v.toString().padStart(2,"0")).join(":");
			},
			verification_required(){
				return this.recovery_grace_remaining<1;
			},
			groups(){
				return this.me.groups.map(group=>`<span class="group-${group.id}">${group.title}</span>`).join(", ");
			}
		},
		watch:{
			password:"ValidatePasswords",
			password2:"ValidatePasswords",

			totp_digits:"TotpQr",
			totp_issuer:"TotpQr",
		},
		methods:{
			///////////////////////////
			// Change password stuff //
			///////////////////////////

			/** Show the change password form */
			ChangePassword(){
				this.rc=false;
				this.totp=false;
				this.change_password=true;
			},

			ValidatePasswords(){
				this.$refs.password2.setCustomValidity(this.password===this.password2 ? "" : this.l10n.PASS_MISMATCH);
			},

			/** Submit the change password form */
			async ChangePasswordSubmit(){
				if(this.loading)
					return;

				const body=new URLSearchParams({
					password:this.password,
					verification:this.verification,
				});

				USP.set("zone","change-password");
				this.loading=true;

				return fetch(location.pathname+"?"+USP.toString(),{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,way,error})=>{
						if(ok)
						{
							this.password="";
							this.password2="";
							this.verification="";
							this.change_password=false;
							this.password_changed=true;

							if(way==="recovery-code")
								this.me.available_recovery_codes--;

							alert(this.l10n.password_changed);
						}
						else
						{
							alert(this.l10n[error] ?? error);

							if(error==="UNVERIFIED")
							{
								this.verification="";
								this.$refs.verification.focus();
							}
							else if(error==="CURRENT_PASSWORD_USED")
							{
								this.password="";
								this.password2="";
								this.$refs.password.focus();
							}
						}
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			////////////////
			// TOTP stuff //
			////////////////

			/** Show the enable/change TOTP form */
			TotpEnable(){
				this.rc=false;
				this.change_password=false;
				this.totp="enable";
				this.TotpQr();
			},

			/** Show the disable TOTP form */
			TotpDisable(){
				this.rc=false;
				this.change_password=false;
				this.totp="disable";
			},

			/** Request TOTP secret and QR code data */
			async TotpQr(){
				if(this.loading || !this.totp || this.totp_issuer.length<1)
					return;

				const body=new URLSearchParams({
					issuer:this.totp_issuer,
					digits:this.totp_digits,
				});

				USP.set("zone","totp");
				this.loading=true;

				return fetch(location.pathname+"?"+USP.toString(),{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,uri,secret})=>{
						if(!ok)
							return;

						const qr = qrcode(0, 'M');
						qr.addData(uri);
						qr.make();

						this.totp_qr=qr.createSvgTag(4);
						this.totp_secret=secret;
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			/** Configure TOTP for the current user */
			async TotpSubmit(){
				if(this.loading || !this.totp)
					return;

				const body=new URLSearchParams({
					code:this.totp_code,
					secret:this.totp_secret,
					digits:this.totp_digits,
					verification:this.verification,
				});

				USP.set("zone","totp");
				this.loading=true;

				return fetch(location.pathname+"?"+USP.toString(),{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,way,error})=>{
						if(ok)
						{
							this.totp=false;
							this.totp_qr=null;
							this.totp_code="";
							this.totp_secret="";
							this.verification="";
							this.totp_changed=true;
							this.me.totp_enabled=true;

							if(way==="recovery-code")
								this.me.available_recovery_codes--;

							alert(this.l10n.totp_configured);
						}
						else
						{
							alert(this.l10n[error] ?? error);

							if(error==="UNVERIFIED")
							{
								this.verification="";
								this.$refs.verification.focus();
							}
							else if(error==="INCORRECT_CODE")
							{
								this.totp_code="";
								this.$refs.totp_code.focus();
							}
						}
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			/** Disable TOTP for the current user */
			async TotpDisableSubmit(){
				if(this.loading || !this.totp)
					return;

				USP.set("zone","totp");
				this.loading=true;

				return fetch(location.pathname+"?"+USP.toString(),{body:this.verification,method:"PATCH",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,way,error})=>{
						if(ok)
						{
							this.totp=false;
							this.verification="";
							this.totp_changed=true;
							this.me.totp_enabled=false;

							if(way==="recovery-code")
								this.me.available_recovery_codes--;

							alert(this.l10n.totp_deleted);
						}
						else
						{
							alert(this.l10n[error] ?? error);

							if(error==="UNVERIFIED")
							{
								this.verification="";
								this.$refs.verification.focus();
							}
						}
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			//////////////////////////
			// Recovery codes stuff //
			//////////////////////////

			/** Recovery codes request form */
			RecoveryCodes(){
				this.totp=false;
				this.change_password=false;
				this.rc=true;

				this.RecoveryCodesGenerate();
			},

			/** Generate a new set of recovery codes */
			async RecoveryCodesGenerate(){
				if(this.loading || !this.rc)
					return;

				USP.set("zone","recovery-codes");
				this.loading=true;
				this.rc_codes="";

				return fetch(location.pathname+"?"+USP.toString(),{headers:{accept:"application/json"}})
					.then(J)
					.then(json=>{
						if(!json.ok)
							return;

						this.rc_hash=json.hash;
						this.rc_rows=json.codes.length;
						this.rc_codes=json.codes.map(code=>code.replace(/(.{4})/g,"$1 ")).join("\n");
						this.rc_session_id=json.session_id;
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			/** Store the generated recovery codes */
			async RecoveryCodesSubmit(){
				if(this.loading || !this.rc)
					return;

				const body=new URLSearchParams({
					hash:this.rc_hash,
					session_id:this.rc_session_id,
					verification:this.verification,
				});

				USP.set("zone","recovery-codes");
				this.loading=true;

				return fetch(location.pathname+"?"+USP.toString(),{body,method:"POST",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(!ok)
						{
							if(error==="UNVERIFIED")
							{
								this.verification="";
								this.$refs.verification.focus();
							}

							return alert(this.l10n[error] ?? error);
						}

						this.me.available_recovery_codes=this.rc_codes.split("\n").length;
						this.rc=false;
						this.rc_hash="";
						this.rc_codes="";
						this.rc_session_id="";
						this.rc_created=true;
						this.verification="";
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			RecoveryCodesClick({target}){
				target.select();
				navigator.clipboard.writeText(target.value);
			},

			/** Check whether a recovery code is still valid without consuming it */
			async RecoveryCodesCheck(){
				if(this.loading)
					return;

				USP.set("zone","recovery-codes");
				this.loading=true;

				return fetch(location.pathname+"?"+USP.toString(),{body:this.rc_check,method:"PUT",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
							alert(this.l10n.rc_ok);
						else if(error)
							alert(this.l10n[error] ?? error);
						else
							alert(this.l10n.rc_invalid);
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			}
		},
		created(){
			const {lang}=document.documentElement;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			const{me,rgr}=JSON.parse(document.querySelector(data).textContent);

			this.recovery_grace_remaining=Math.max(0,rgr-Math.trunc(performance.now()/1000));

			if(this.recovery_grace_remaining>0)
				this.to=setInterval(()=>this.recovery_grace_remaining-->1 ? 0 : clearInterval(this.to),1e3);

			Object.assign(this.me,me);
		}
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);