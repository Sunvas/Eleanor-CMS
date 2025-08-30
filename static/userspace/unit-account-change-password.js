// Eleanor CMS © 2025 --> https://eleanor-cms.com

(({template,container,old_required})=>{
	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:{
				ok:{ru:"✅ Пароль успешно изменен",en:"✅ Password was successfully changed"},
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"}
			},

			password:"",
			old_password:"",

			old_required,
			password2:"",

			saving:false
		}),
		watch:{
			password2(n){
				this.$refs.password2.setCustomValidity(this.password!==n ? this.l10n.PASS_MISMATCH : "");
			}
		},
		methods:{
			Submit(){
				if(this.saving)
					return;

				const body=JSON.stringify({
					old:this.old_password,
					new:this.password,
				});

				this.saving=true;
				fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
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

							if(error=="INCORRECT")
								this.old_required=true;
						}
					},r=>r.text().then(console.error))
					.finally(()=>this.saving=false);
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