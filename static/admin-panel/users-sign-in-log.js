// Eleanor CMS © 2025 --> https://eleanor-cms.com
(async({template,container,data})=>{
	const
		{L10N,L10NS,items,users,...extra}=JSON.parse($(data).text()),
		items4=(await import("./4items.mjs")).default(extra);

	Vue.createApp({
		template,
		extends:items4,
		data:()=>({
			// L10n
			l10n:Object.seal({
				WRONG_TOTP:{ru:"Неверный код",en:"Wrong code"},
				WRONG_PASSWORD:{ru:"Неверный пароль",en:"Wrong password"},
				WRONG_RECOVERY_CODE:{ru:"Неверный резервный код",en:"Wrong recovery code"},
			}),
			lang:document.documentElement.lang,
			l10ns:[],
			L10N:null,

			items,
			users:users.reduce((a,v)=>a.set(v.id,v),new Map),

			// Filters
			id:"",
			date:"",
			result:"",
			default_sort:"date",
			reset:["sort","order","id","date","result"],// Query keys to clear
			is_filtered:false,
			filtered_user:null,

			// Datepicker
			today_date:new Date().toLocaleDateString('sv-SE'),
			date_from:"",
			date_to:"",
		}),
		watch:{
			date_from:"MakeDate",
			date_to:"MakeDate",
		},
		computed:{
			max_date(){
				const date=new Date(this.date_to);
				return isNaN(date) || date>new Date() ? this.today_date : this.date_to;
			}
		},
		methods:{
			MakeDate(){
				this.date=this.date_from===this.date_to ? this.date_from : this.date_from+".."+this.date_to;
			},

			SelectAll({target}){
				target.select();
			},

			Link2User({user_id})
			{
				const USP=new URLSearchParams;

				USP.set("u",this.USP.get("u"));
				USP.set("id",user_id);

				return location.pathname+"?"+USP.toString();
			}
		},
		created(){
			const {lang}=this;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			for(const f of ["id","date","result"])
				if(this.USP.has(f))
					this[f]=this.USP.get(f);

			this.items.forEach(item=>{
				const user=this.users.get(item.user_id);

				item.user_name=user.name;
				item.group_id=user.groups[0] ?? 0;
			});

			if(this.id || this.date || this.result)
				this.is_filtered=true;

			if(this.id)
			{
				const user=this.users.get(+this.id);
				this.filtered_user=user ? {
					user_id:user.id,
					user_name:user.name,
					group_id:user.groups[0] ?? 0
				} : null;
			}

			if(this.date.includes(".."))
				[this.date_from,this.date_to]=this.date.split("..");
			else
				this.date_from=this.date_to=this.date;
		}
	}).mount(container);
})(document.currentScript.dataset);