// Eleanor CMS © 2025 --> https://eleanor-cms.com
(async({template,container,data})=>{
	const
		{L10N,L10NS,items,groups,is_admin,my_id,...extra}=JSON.parse($(data).text()),
		items4=(await import('./4items.js')).default(extra),
		user={};

	Vue.createApp({
		extends:items4,
		template,
		data:()=>({
			//L10n
			l10n:{
				recently:{ru:"Менее часа назад",en:"Less then hour ago"},
				today:{ru:"Менее суток назад",en:"Less than a day ago"},
				long_ago:{ru:"Более суток назад",en:"More than a day ago"},
				never:{ru:"Не заходил",en:"Has not ever signed id"},
				empty_password:{ru:"У пользователя пустой пароль",en:"User has empty password"},
				create:{ru:"Создать",en:"Create"},

				just_now:{ru:"Только что",en:"Just now"},
				save:{ru:"Сохранить",en:"Save"},
				saved:{ru:"Сохранено",en:"Saved"},
				creating_user:{ru:"Создание пользователя",en:"Creating the user"},

				delete_user:{ru:"Вы действительно хотите удалить пользователя?",en:"Are you sure you want to delete the user?"},

				NAME_EXISTS:{ru:"Этот логин уже используется",en:"This login is already taken"},
			},
			lang:document.documentElement.lang,
			l10ns:[],

			//Userlist
			items,
			my_id,
			is_admin,
			groups:groups.toSorted((a,b)=>a.title-b.title),
			group2title:groups.reduce((a,v)=>Object.assign(a,{[v.id]:v.title}),{}),
			sort_default:"id",

			//Filters
			id:"",
			name:"",
			group:"",
			reset:['sort','order','id','name','group'],//const for clearing purpose

			//Confirmation modal
			confirm:"",
			confirm_title:2,
			confirmed:false,

			//User creation & modification modal
			user_id:0,
			user_title:"",
			user_name_error:null,
			user:{
				groups:[],

				l10n:"",
				name:"",
				info:"",
				comment:"",
				password:"",
				display_name:"",
			},

			saving:false,
			changed:new Set(),
		}),
		computed:{
			/** It shows that there are some filters applied to the userlist */
			is_filtered(){
				return !!(this.id || this.name || this.group);
			},

			/** It shows that there are no unsaved fields in modal of modifying user */
			saved(){
				//When modal is not shown
				if(this.user_title==="")
					return true;

				for(const k of this.changed.values())
					if(JSON.stringify(user[k])!==JSON.stringify(this.user[k]))
						return false;
					else
						this.changed.delete(k);

				return true;
			},

			/** Text on submit button in modal of modifying user */
			submit_text(){
				if(this.user_id)
					return this.saved ? this.l10n.saved : this.l10n.save;

				return this.l10n.create;
			}
		},
		watch:{
			"user.name":"CheckName"
		},
		methods:{
			/** Is called by clicking on "Yes" button of confirmation modal */
			Confirmed(){
				this.confirmed=true;
			},

			/** Show confirmation modal */
			async Confirm(message,title){
				this.confirm=message;
				this.confirm_title=title;
				this.confirmed=false;

				return new Promise(resolve=>{
					coreui.Modal.getOrCreateInstance(this.$refs.confirm).show();

					$(this.$refs.confirm)
						.one("hidden.coreui.modal",()=>resolve(this.confirmed))
						.one("shown.coreui.modal",()=>$(this.$refs.confirm_dismiss).focus());
				});
			},

			/** Copying user's id to the clipboard */
			async Copy({id},index){
				try {
					await navigator.clipboard.writeText(id.toString());
				} catch (error) {
					console.error(error.message);
				}
			},

			/** Singing in into user's account on userspace */
			SignIn({id},index){
				const USP=this.Filter(this.reset,false);
				USP.set("sign-in",id);
				fetch(location.pathname+"?"+USP.toString(),{headers:{accept:"application/json"}})
					.then(J).then(r=>{
						if(r.ok)
						{
							const url=new URL(document.baseURI);
							url.searchParams.set("iam",id);
							open(url.href);
						}
					},r=>r.text().then(console.error));
			},

			/** User removal */
			async Delete(item,index){
				if(item.id==this.my_id)
					return;

				if(!await this.Confirm(this.l10n.delete_user,item.name+" #"+item.id))
					return;

				fetch(this.URL(item.id),{method:"delete",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
							this.items.splice(index,1);
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error))
					.finally(()=>this.saving=false);
			},

			/** Showing modal to create the user */
			Create(){
				this.user_id=0;
				this.user_title=this.l10n.creating_user;

				Object.assign(user,{
					groups:[3],
					name:"",
					l10n:this.lang,
					info:"",
					comment:"",
					password:"",
					display_name:"",
				});
				Object.assign(this.user,user);

				coreui.Modal.getOrCreateInstance(this.$refs.user).show();
			},

			/** Modifying the user */
			Modify({id},index){
				fetch(this.URL(id),{headers:{accept:"application/json"}})
				.then(J).then(r=>{
					if(r.ok)
					{
						this.user_id=id;
						this.user_title=r.user.name;

						Object.assign(user,r.user,{password:""});

						Object.assign(this.user,user);

						coreui.Modal.getOrCreateInstance(this.$refs.user).show();
					}
					else if(r.error)
						alert( this.l10n[r.error] ?? r.error );
				});
			},

			/** Should be called each time form control being changed by user real time */
			Changed(item,is_mono){
				this.changed.add(item);
			},

			/** Checking user's new name */
			CheckName(){
				this.user_name_error=null;

				if(this.user.name===user.name)
					return this.$refs.user_name.setCustomValidity("");

				const USP=this.Filter(this.reset,false);

				USP.set("check_name",this.user.name);

				fetch(location.pathname+"?"+USP.toString(),{headers:{accept:"application/json"}})
					.then(J).then(({ok})=>{
					this.user_name_error=ok ? "" : this.l10n.NAME_EXISTS;
					this.$refs.user_name.setCustomValidity(this.user_name_error);
				});
			},

			/** Submitting user modification form */
			async Submit(){
				const
					store=this.user_id ? {} : this.user,
					USP=this.Filter(this.reset,false);

				if(this.user_id)
				{
					USP.set("user",this.user_id);

					for(const k of this.changed.values())
						if(JSON.stringify(user[k])!==JSON.stringify(this.user[k]))
							store[k]=this.user[k];
				}

				if(Object.keys(store).length<1)
					return;

				this.saving=true;

				fetch(location.pathname+"?"+USP.toString(),{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
				.then(J).then(({ok,error,id})=>{
					if(ok){
						Object.assign(user,store);

						this.user_title=user.name;

						//Adding user
						if(id)
						{
							this.NormalizeItem(store);
							store.created=this.l10n.just_now;

							this.user_id=id;
							this.items.unshift({id,...store});
						}
						else
						{
							const user=this.items.find(item=>item.id===this.user_id);

							if(user)
								Object.assign(user,store,store.password ? {empty_password:false} : {});
						}

						this.CheckName();
						this.changed.clear();
					}
					else if(error)
						alert( this.l10n[error] ?? error );
				})
				.finally(()=>this.saving=false);
			},

			URL(id){
				const USP=this.Filter(this.reset,false);
				USP.set("user",id);
				return location.pathname+"?"+USP.toString();
			},

			/** Adding special keys to each item */
			NormalizeItem(item){
				if(!item.activity_ts)
				{
					item.status_class='bg-secondary';
					item.status_hint="never";
					return;
				}

				const ts=Date.now()-item.activity_ts*1e3;

				//Less than hour ago
				if(ts<=36e5)
				{
					item.status_class='bg-success';
					item.status_hint="recently";
				}

				//Less than day ago
				else if(ts>36e5 && ts<=864e5)
				{
					item.status_class='bg-warning';
					item.status_hint="today";
				}

				//Long time ago
				else
				{
					item.status_class='bg-danger';
					item.status_hint="long_ago";
				}
			}
		},
		created(){
			const {lang}=this;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			//Filling in the set of l10n
			if(L10NS?.length)
				import("./l10ns.js").then(({default:l10ns})=>{
					this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
				});

			$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));

			this.items.map(this.NormalizeItem);

			for(const f of ["id","name","group"])
				this[f]=this.USP.has(f) ? this.USP.get(f) : "";
		},
		mounted(){
			$(this.$refs.user).one("hidden.coreui.modal",()=>this.user_title="");
		}
	}).mount(container);
})(document.currentScript.dataset);