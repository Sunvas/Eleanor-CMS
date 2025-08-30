// Eleanor CMS © 2025 --> https://eleanor-cms.com

(({template,container,data})=>{
	data=JSON.parse($(data).text());

	const app=Vue.createApp({
		template,
		data:()=>({
			l10n:{
				save:{ru:'Сохранить',en:"Save"},
				saved:{ru:'Сохранено',en:"Saved"},
				change:{ru:'Сменить...',en:"Change..."},
				upload:{ru:'Загрузить...',en:"Upload..."},
			},

			asia:data.timezones.filter(item=>item.startsWith("Asia")),
			europe:data.timezones.filter(item=>item.startsWith("Europe")),
			has_l10n:Object.hasOwn(data.settings,"l10n"),
			settings:{...data.settings},

			blob:null,
			avatar:"",
			max_width:250,
			max_height:250,

			changed:new Set(),
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

				for(const k of this.changed.values())
					if(data.settings[k]!==this.settings[k])
						return false;
					else
						this.changed.delete(k);

				return true;
			},
		},

		methods:{
			Changed(field){
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
			Submit(){
				const store={};

				for(const k of this.changed.values())
					if(data.settings[k]!==this.settings[k])
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

				fetch(location.href,{body,method:"post",headers:{accept:"application/json"}})
					.then(J)
					.then(({ok,error})=>{
						if(ok)
						{
							this.blob=null;
							Object.assign(data.settings,store);
							this.changed.clear();
						}
						else
							alert(this.l10n[error] ?? error);
					},r=>r.text().then(console.error))
					.finally(()=>this.saving=false);
			},
		},
		created(){
			const {lang}=document.documentElement;

			for(const[k,v] of Object.entries(this.l10n))
				if(v[lang])
					this.l10n[k]=v[lang];

			$(window).on("beforeunload",e=>void(this.saved || e.preventDefault()));
		}
	});

	L.then(()=>app.mount(container));
})(document.currentScript.dataset);