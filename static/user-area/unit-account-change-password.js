// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** Password change form for user account. */
(({template,container,old_required})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				ok:{ru:"✅ Пароль успешно изменён",en:"✅ Password changed successfully"},
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"}
			}),

			password:"",
			password2:"",
			old_password:"",

			old_required,
			saving:false
		}),
		watch:{
			password:"ValidatePasswords",
			password2:"ValidatePasswords"
		},
		methods:{
			ValidatePasswords(){
				this.$refs.password2.setCustomValidity(this.password===this.password2 ? "" : this.l10n.PASS_MISMATCH);
			},

			async Submit(){
				if(this.saving)
					return;

				const body=JSON.stringify({
					old:this.old_password,
					new:this.password,
				});

				this.saving=true;
				return fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
						{
							this.password="";
							this.password2="";
							this.old_password="";

							alert(this.l10n.ok);
						}
						else
						{
							alert(this.l10n[error] ?? error);

							if(error==="INCORRECT")
								this.old_required=true;
						}
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.saving=false;
					});
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