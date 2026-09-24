// Eleanor CMS © 2025 --> https://eleanor-cms.com
/** User account sessions list with termination action. */
(async({template,container,data})=>{
	const
		{items,...extra}=JSON.parse($(data).text()),
		items4=(await import('./../admin-panel/4items.mjs')).default(extra),

		app=Vue.createApp({
			template,
			extends:items4,
			data:()=>({
				l10n:Object.seal({
					WRONG_TOTP:{ru:"Неверный код",en:"Wrong code"},
					WRONG_PASSWORD:{ru:"Неверный пароль",en:"Wrong password"},
					WRONG_RECOVERY_CODE:{ru:"Неверный резервный код",en:"Wrong recovery code"},
				}),

				items,
				date:"",
				is_filtered:false
			}),
			methods:{
				FilterSubmit(){
					this.$refs.filter.submit();
				}
			},
			created(){
				const {lang}=document.documentElement;

				for(const[k,v] of Object.entries(this.l10n))
					if(v[lang])
						this.l10n[k]=v[lang];

				if(this.USP.has("date"))
				{
					this.date=this.USP.get("date");
					this.is_filtered=true;
				}
			}
		});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);