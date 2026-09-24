// Eleanor CMS © 2025 --> https://eleanor-cms.com
(async({template,container,data})=>{
	const
		{L10N,L10NS,items,groups,is_root,my_id,...extra}=JSON.parse($(data).text()),
		items4=(await import("./4items.mjs")).default(extra),
		user=Object.create(null);

	Vue.createApp({
		template,
		extends:items4,
		data:()=>({
			// L10n
			l10n:Object.seal({
				recently:{ru:"Менее часа назад",en:"Less than an hour ago"},
				today:{ru:"Менее суток назад",en:"Less than a day ago"},
				long_ago:{ru:"Более суток назад",en:"More than a day ago"},
				never:{ru:"Не заходил",en:"Has never signed in"},
				empty_password:{ru:"У пользователя пустой пароль",en:"User has empty password"},
				create:{ru:"Создать",en:"Create"},

				just_now:{ru:"Только что",en:"Just now"},
				save:{ru:"Сохранить",en:"Save"},
				saved:{ru:"Сохранено",en:"Saved"},
				creating_user:{ru:"Создание пользователя",en:"Creating the user"},

				totp_title:{ru:name=>"Одноразовые коды для "+name,en:name=>"Onetime passcodes for "+name},
				rc_title:{ru:name=>"Резервные коды для "+name,en:name=>"Recovery codes for "+name},

				delete_user:{ru:"Вы действительно хотите удалить пользователя?",en:"Are you sure you want to delete the user?"},

				NAME_EXISTS:{ru:"Этот логин уже используется",en:"This login is already taken"},
			}),
			lang:document.documentElement.lang,
			l10ns:[],

			// Userlist
			items,
			my_id,
			is_root,
			groups:groups.toSorted((a,b)=>a.title.localeCompare(b.title)),
			group2title:groups.reduce((a,v)=>a.set(v.id,v.title),new Map),
			default_sort:"id",

			// Filters
			id:"",
			name:"",
			group:"",
			reset:["sort","order","id","name","group"],// Query keys to clear
			is_filtered:false,

			// Confirmation modal
			confirm:"",
			confirm_title:"",
			confirmed:false,

			// User creation & modification modal
			user_id:null,
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

			// TOTP stuff
			totp_code:"",
			totp_title:"",
			totp_action:"",
			totp_secret:"",
			totp_qr:null,
			totp_date:null,
			totp_state:null,
			totp_issuer:location.hostname,
			totp_digits:6,
			totp_wrong:false,

			// Recovery codes
			rc_date:null,
			rc_state:null,
			rc_rows:10,
			rc_hash:"",
			rc_codes:"",
			rc_title:"",
			rc_action:"",
			rc_session_id:"",

			saving:false,
			loading:false,
			changed:new Set,
		}),
		computed:{
			/** Whether user modal has no unsaved fields */
			saved(){
				// true when modal is not shown
				return this.user_id===null || this.changed.size<1;
			},

			/** Text on submit button in modals related to the user */
			submit_text(){
				if(this.user_id)
					return this.saved ? this.l10n.saved : this.l10n.save;

				return this.l10n.create;
			}
		},
		watch:{
			"user.name":"CheckName",

			totp_digits:"TotpQr",
			totp_issuer:"TotpQr",
			totp_action(val){
				if(val)
				{
					this.TotpQr();
					this.changed.add("action");
				}
				else
				{
					this.totp_qr=null;
					this.changed.clear();
				}
			},
			totp_code(){
				this.totp_wrong=false;
			},

			rc_action(val){
				if(val)
				{
					this.RecoveryCodesGenerate();
					this.changed.add("action");
				}
				else
				{
					this.totp_qr=null;
					this.changed.clear();
				}
			},
		},
		methods:{
			/** Is called by clicking on "Yes" button of confirmation modal */
			Confirmed(){
				this.confirmed=true;
			},

			/** Show confirmation modal dialog */
			async Confirm(message,title){
				this.confirm=message;
				this.confirm_title=title;
				this.confirmed=false;

				return new Promise(resolve=>{
					coreui.Modal.getOrCreateInstance(this.$refs.confirm).show();

					$(this.$refs.confirm)
						.one("hide.coreui.modal",()=>$(":focus",this.$refs.confirm).blur())// Blur the focused element before hiding
						.one("hidden.coreui.modal",()=>resolve(this.confirmed))
						.one("shown.coreui.modal",()=>$(this.$refs.confirm_dismiss).focus());
				});
			},

			ModalFormShow(form){
				this.changed.clear();

				coreui.Modal.getOrCreateInstance(this.$refs[form]).show();

				$(this.$refs[form])
					.one("hide.coreui.modal",()=>$(":focus",this.$refs[form]).blur())// Blur the focused element before hiding
					.one("hidden.coreui.modal",()=>this.user_id=null);
			},

			ModalFormHide(form){
				coreui.Modal.getOrCreateInstance(this.$refs[form]).hide();
			},

			/** Copying user's id to the clipboard */
			async Copy({id},index){
				try{
					await navigator.clipboard.writeText(id.toString());
				}catch(e){
					console.error(e.message);
				}
			},

			/** Sign in to then user area as selected user */
			async SignIn({id},index){
				return fetch(this.UserURL(id,"sign-in"),{headers:{accept:"application/json"}})
					.then(J).then(r=>{
						if(r.ok)
						{
							const url=new URL(document.baseURI);
							url.searchParams.set("@",id);
							open(url.href);
						}
					},r=>r.text().then(console.error));
			},

			/** User removal */
			async Delete(item,index){
				if(item.id===this.my_id)
					return;

				if(!await this.Confirm(this.l10n.delete_user,item.name+" #"+item.id))
					return;

				return fetch(this.UserURL(item.id),{method:"delete",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
							this.items.splice(index,1);
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error));
			},

			/** Show TOTP modal */
			TOTP(item,index){
				this.user_id=item.id;
				this.totp_date=item.totp_changed_at;
				this.totp_title=this.l10n.totp_title(item.name);
				this.totp_state=item.totp_changed_at ? item.totp_enabled : null;
				this.totp_wrong=false;
				this.totp_action="";

				this.ModalFormShow("totp");
			},

			/** Request TOTP secret and QR code data */
			async TotpQr(){
				if(this.loading || this.totp_issuer.length<1)
					return;

				const body=new URLSearchParams({
					issuer:this.totp_issuer,
					digits:this.totp_digits,
				});
				this.loading=true;

				return fetch(this.UserURL(this.user_id,"totp"),{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,uri,secret})=>{
						if(!ok)
							return;

						const qr = qrcode(0, 'M');
						qr.addData(uri);
						qr.make();

						this.totp_qr=qr.createSvgTag(4,0);
						this.totp_secret=secret;
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.loading=false;
					});
			},

			/** Configure TOTP for the user */
			async TOTPSubmit(){
				if(this.saving)
					return;

				if(this.totp_action==="disable")
				{
					this.saving=true;
					return fetch(this.UserURL(this.user_id,"delete-totp"),{headers:{accept:"application/json"}})
						.then(J)
						.then(r=>{
							if(r.ok)
							{
								const item=this.items.find(item=>item.id===this.user_id);

								item.totp_enabled=false;
								item.totp_changed_at="now";

								this.ModalFormHide("totp");
							}
						},r=>r.text().then(console.error))
						.finally(()=>{
							this.saving=false;
						});
				}

				const body=new URLSearchParams({
					code:this.totp_code,
					secret:this.totp_secret,
					digits:this.totp_digits,
				});
				this.saving=true;

				return fetch(this.UserURL(this.user_id),{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
						{
							const item=this.items.find(item=>item.id===this.user_id);

							item.totp_enabled=true;
							item.totp_changed_at="now";

							this.totp_qr=null;
							this.totp_code="";
							this.totp_secret="";
							this.ModalFormHide("totp");
						}
						else if(error==="INCORRECT_CODE")
						{
							this.totp_wrong=true;
							this.$refs.totp_code.select();
							this.$refs.totp_code.focus();
						}
						else if(error)
							alert( this.l10n[error] ?? error );
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.saving=false;
					});
			},

			/** Show Recovery Codes modal form */
			RecoveryCodes(item){
				this.user_id=item.id;
				this.rc_date=item.recovery_codes_date;
				this.rc_title=this.l10n.rc_title(item.name);
				this.rc_state=item.recovery_codes_date ? item.available_recovery_codes : null;
				this.rc_action="";

				this.ModalFormShow("rc");
			},

			/** Generate a new set of recovery codes for the user */
			async RecoveryCodesGenerate(){
				if(this.loading)
					return;

				this.loading=true;
				this.rc_codes="";

				return fetch(this.UserURL(this.user_id,"recovery-codes"),{headers:{accept:"application/json"}})
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

			/** Store Recovery Codes for the user */
			RecoveryCodesSubmit(){
				if(this.saving)
					return;

				const body=new URLSearchParams({
					recovery_codes:this.rc_hash,
					session_id:this.rc_session_id
				});
				this.saving=true;

				return fetch(this.UserURL(this.user_id),{body,method:"POST",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
						{
							const item=this.items.find(item=>item.id===this.user_id);

							item.available_recovery_codes=this.rc_rows;
							item.recovery_codes_date="now";

							this.rc_hash="";
							this.rc_codes="";
							this.rc_session_id="";
							this.ModalFormHide("rc");
						}
						else if(error)
							alert( this.l10n[error] ?? error );
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.saving=false;
					});
			},

			RecoveryCodesClick({target}){
				target.select();
				navigator.clipboard.writeText(target.value);
			},

			/** Showing modal to create the user */
			Create(){
				this.user_id=0;
				this.user_title=this.l10n.creating_user;

				this.loading=true;
				Object.assign(user,{
					groups:[3],
					name:"",
					l10n:this.lang,
					info:"",
					comment:"",
					password:"",
					display_name:"",
				});
				this.Load();
				this.loading=false;

				this.ModalFormShow("user");
			},

			/** Modifying the user */
			async Modify({id},index){
				this.loading=true;

				return fetch(this.UserURL(id),{headers:{accept:"application/json"}}).then(J)
				.then(r=>{
					if(r.ok)
					{
						this.user_id=id;
						this.user_title=r.user.name;

						Object.assign(user,r.user,{password:""});

						this.Load();
						this.ModalFormShow("user");
					}
					else if(r.error)
						alert( this.l10n[r.error] ?? r.error );
				})
				.finally(()=>{
					this.loading=false;
				});
			},

			/** Loading values to the local variable */
			Load(){
				for(const[k,v] of Object.entries(user))
					this.user[k]=Array.isArray(v) ? v.slice() : v;
			},

			/** Track real-time changes in form controls */
			Changed(field,val,ref){
				// Don't apply changes while loading values
				if(this.loading)
					return;

				if(JSON.stringify(ref[field])===JSON.stringify(val))
					this.changed.delete(field);
				else
					this.changed.add(field);
			},

			/** Checking user's new name */
			async CheckName(){
				this.user_name_error=null;

				if(this.user.name===user.name)
					return this.$refs.user_name.setCustomValidity("");

				const USP=this.Filter(this.reset,false);

				USP.set("check_name",this.user.name);

				return fetch(location.pathname+"?"+USP.toString(),{headers:{accept:"application/json"}})
					.then(J).then(({ok})=>{
						if(this.user.name!==USP.get("check_name"))
							return;

						this.user_name_error=ok ? "" : this.l10n.NAME_EXISTS;
						this.$refs.user_name.setCustomValidity(this.user_name_error);
				});
			},

			/** Submitting the user modification form */
			async Submit(){
				const store=this.user_id ? Object.create(null) : this.user;

				if(this.user_id)
					for(const k of this.changed)
						if(JSON.stringify(user[k])!==JSON.stringify(this.user[k]))
							store[k]=this.user[k];

				if(Object.keys(store).length<1)
					return;

				this.saving=true;

				return fetch(this.UserURL(this.user_id),{method:"post",body:JSON.stringify(store),headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error,id})=>{
						if(ok){
							Object.assign(user,store);

							this.user_title=user.name;

							// Adding user
							if(id)
							{
								const item={id,...store};
								delete item.password;

								this.NormalizeItem(item);
								item.created=this.l10n.just_now;

								this.user_id=id;
								this.items.unshift(item);
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
					},r=>r.text().then(console.error))
					.finally(()=>{
						this.saving=false;
					});
			},

			/** User replated URL for AJAX requests */
			UserURL(id,action){
				const USP=this.Filter(this.reset,false);
				USP.set("user",id);
				action && USP.set("action",action);
				return location.pathname+"?"+USP.toString();
			},

			/** Adding special keys to each item */
			NormalizeItem(item){
				if(!item.activity_ts)
				{
					item.status_class="bg-secondary";
					item.status_hint="never";
					return;
				}

				const ts=Date.now()-item.activity_ts*1e3;

				// Less than hour ago
				if(ts<=36e5)
				{
					item.status_class="bg-success";
					item.status_hint="recently";
				}

				// Less than day ago
				else if(ts>36e5 && ts<=864e5)
				{
					item.status_class="bg-warning";
					item.status_hint="today";
				}

				// Long time ago
				else
				{
					item.status_class="bg-danger";
					item.status_hint="long_ago";
				}
			},

			Link2SignInLog({id})
			{
				const USP=new URLSearchParams;

				USP.set("u",this.USP.get("u"));
				USP.set("zone","sign-in-log");
				USP.set("id",id);

				return location.pathname+"?"+USP.toString();
			}
		},
		created(){
			const {lang}=this;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			// Filling in the set of l10n
			if(L10NS?.length)
				import("./l10ns.mjs").then(({default:l10ns})=>{
					this.l10ns=[L10N,...L10NS].map(item=>[item,l10ns[item] ?? item]);
				});

			// Watchers for user modification form
			for(const k of Object.keys(this.user))
				this.$watch("user."+k,val=>this.Changed(k,val,user));

			// Watcher for TOTP form
			this.$watch("totp_code",val=>this.Changed("code",val,{code:""}));

			this.items.forEach(this.NormalizeItem);

			for(const f of ["id","name","group"])
				if(this.USP.has(f))
					this[f]=this.USP.get(f);

			if(this.id || this.name || this.group)
				this.is_filtered=true;

			$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
		}
	}).mount(container);
})(document.currentScript.dataset);