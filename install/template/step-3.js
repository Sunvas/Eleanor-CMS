// Eleanor CMS © 2026 --> https://eleanor-cms.com
(({template,container,data})=>Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				PASS_MISMATCH:{ru:"Пароли не совпадают",en:"Passwords don't match"}
			}),
			lang:document.documentElement.lang,

			//MySQL data
			host:"",
			user:"",
			pass:"",
			db:"",

			//Site settings
			title:"",
			description:"",
			hcaptcha:"",
			hsecret:"",

			//Language settings
			translations:new Set(["ru","en"]),
			multilang:false,
			l10ns:[],

			//Administrator
			username:"",
			password:"",
			password2:"",

			//Back form
			back:new Map
		}),
		watch:{
			password:"ValidatePasswords",
			password2:"ValidatePasswords"
		},
		methods:{
			ValidatePasswords(){
				this.$refs.p2.setCustomValidity(this.password===this.password2 ? "" : this.l10n.PASS_MISMATCH);
			},

			Back(){
				this.back.clear();

				for(const k of ["host","user","pass","db","title","description","hcaptcha","hsecret","username","password","password2"])
					this.back.set(k,this[k]);

				if(this.multilang)
					this.back.set("multilang","");
			}
		},
		created(){
			for(const[k,v] of Object.entries(this.l10n))
				if(v[this.lang])
					this.l10n[k]=v[this.lang];

			data=JSON.parse(document.querySelector(data).textContent);
			this.translations.delete(this.lang);
		},
		mounted(){
			Object.assign(this,data);
		}
	}).mount(container)
)(document.currentScript.dataset);