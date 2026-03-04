// Eleanor CMS © 2025 --> https://eleanor-cms.com

(({template,container,data})=>{
	const{settings,timezones}=JSON.parse($(data).text());

	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:Object.seal({
				save:{ru:'Сохранить',en:"Save"},
				saved:{ru:'Сохранено',en:"Saved"},
				change:{ru:'Сменить...',en:"Change..."},
				upload:{ru:'Загрузить...',en:"Upload..."},
			}),

			asia:timezones.filter(item=>item.startsWith("Asia")),
			europe:timezones.filter(item=>item.startsWith("Europe")),
			has_l10n:Object.hasOwn(settings,"l10n"),
			settings:Object.create(null),

			blob:null,
			avatar:"",
			max_width:250,
			max_height:250,

			changed:new Set,
			saving:false
		}),
		computed:{
			has_avatar(){
				return this.avatar || this.settings.avatar;
			},
			avatar_button(){
				return this.has_avatar ? this.l10n.change : this.l10n.upload;
			},
			submit_text(){
				return this.saved ? this.l10n.saved : this.l10n.save;
			},

			saved(){
				if(this.blob)
					return false;

				return this.changed.size<1;
			},
		},

		methods:{
			Changed(field,val){
				if(JSON.stringify(settings[field])===JSON.stringify(val))
					this.changed.delete(field);
				else
					this.changed.add(field);
			},
			UploadAvatar(){
				const app=this;

				$("<input>").attr({type:"file",accept:"image/*"}).change(function(){
					if(this.files.length<1)
						return;

					const img=new Image();

					//Converting any image to webp
					$(img).on("load",function() {
						const
							canvas = document.createElement('canvas'),
							wr=app.max_width / img.width,
							hr=app.max_height / img.height,
							[width,height]=wr > hr ? [app.max_width,wr * img.height] : [hr * img.width,app.max_height];

						canvas.width = width;
						canvas.height = height;
						canvas.getContext("2d").drawImage(img, 0, 0, img.width, img.height, 0, 0, width, height);

						canvas.toBlob(function(blob){
							app.blob=blob;
							app.avatar=URL.createObjectURL(blob);
						}, "image/webp");
					});

					img.src=URL.createObjectURL(this.files[0]);
				}).get(0).click();
			},
			async Submit(){
				const store=Object.create(null);

				for(const k of this.changed)
					if(settings[k]!==this.settings[k])
						store[k]=this.settings[k];

				if(!this.blob && Object.keys(store).length<1)
					return;

				let body;

				//Uploading avatar
				if(this.blob)
				{
					const fd=new FormData();
					fd.append("avatar",this.blob);

					for(const[k,v] of Object.entries(store))
						fd.append(k,v);

					body=fd;
				}
				else
					body=JSON.stringify(store);

				this.saving=true;

				await fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
						{
							this.blob=null;
							Object.assign(settings,store);
							this.changed.clear();
						}
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error));

				this.saving=false;
			},
		},
		created(){
			const {lang}=document.documentElement;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			for(const[k,v] of Object.entries(settings))
			{
				this.settings[k]??=Array.isArray(v) ? v.slice() : v;
				this.$watch("settings."+k,val=>this.Changed(k,val));
			}

			$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
		}
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);