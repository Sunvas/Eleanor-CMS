/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.UPLOADER=function(opts)
{	opts=$.extend(
		{			container:document.body,
			realpath:"",
			service:"",
			curpath:"",
			sess:"",
			uniq:"",
			page:1
		},
		opts
	);
	$.extend(
		opts,
		{			showpreviews:parseInt(CORE.GetCookie("showpreviews-"+opts.uniq)),
			dopreviews:parseInt(CORE.GetCookie("dopreviews-"+opts.uniq)),
			watermark:parseInt(CORE.GetCookie("watermark-"+opts.uniq))		}
	)
	this.UP=false;
	this.buttons=[];
	this.editor=false;

	var th=this;

	this.Get=function(n)
	{		return typeof opts[n]=="undefined" ? false : opts[n];	}

	this.CreateFolder=function()
	{
		var n=prompt(CORE.Lang("new_folder"),"");
		if(n)
			CORE.Ajax(
				{
					folder:n,
					goal:"new",
					type:"uploader",
					uniq:opts.uniq,
					session:opts.sess,
					path:opts.curpath,
					service:opts.service
				},
				function(result)
				{
					th.Update();
				}
			);
	}

	this.CreateFile=function()
	{		var n=prompt(CORE.Lang("enter_new_file"),"");
		if(!n)
			return;
		var w=Math.round(screen.width/3*2),h=Math.round(screen.height/3*2),
			win=window.open("","win"+n,"height="+h+",width="+w+",toolbar=no,directories=no,menubar=no,scrollbars=yes,status=no,top="+Math.round((screen.height-h)/2)+",left="+Math.round((screen.width-w)/2));
		CORE.Ajax(
			{
				file:n,
				type:"uploader",
				uniq:opts.uniq,
				goal:"new-file",
				session:opts.sess,
				path:opts.curpath,
				service:opts.service
			},
			function(result)
			{				win.document.open("text/html","replace");
				win.document.UPLOADER=th;
				win.document.write(result);
				win.document.close();
			},
			function(err)
			{
				win.close();
				alert(err);
			}
		);
	}

	this.EditFile=function(n)
	{		var w=Math.round(screen.width/3*2),h=Math.round(screen.height/3*2),
			win=window.open("","win"+n,"height="+h+",width="+w+",toolbar=no,directories=no,menubar=no,scrollbars=yes,status=no,top="+Math.round((screen.height-h)/2)+",left="+Math.round((screen.width-w)/2));
		CORE.Ajax(
			{
				what:n,
				type:"uploader",
				goal:"edit",
				uniq:opts.uniq,
				session:opts.sess,
				path:opts.curpath,
				service:opts.service
			},
			function(result)
			{				win.document.open("text/html","replace");
				win.document.UPLOADER=th;
				win.document.write(result);
				win.document.close();
			},
			function(err)
			{				win.close();
				alert(err);			}
		);
	}

	this.SaveFile=function(n,content,c)
	{
		CORE.Ajax(
			{
				what:n,
				goal:"save",
				type:"uploader",
				uniq:opts.uniq,
				content:content,
				session:opts.sess,
				service:opts.service
			},
			function(result)
			{
				if(c)
					c();
				th.Update();
			}
		);
	}

	this.Update=function()
	{		this.Go("");
	}

	this.GoPage=function(p)
	{		this.Go("",p);	}

	this.Go=function(to,page)
	{		if(typeof page=="undefined")
			page=opts.page;
		CORE.Ajax(
			{
				dest:to,
				page:page,
				type:"uploader",
				goal:"content",
				uniq:opts.uniq,
				session:opts.sess,
				path:opts.curpath,
				service:opts.service,
				showpreviews:opts.showpreviews ? 1 : 0
			},
			function(result)
			{
				$(".files",opts.container).html(result['content'])
				//Tip
				.find("img.type").poshytip({
					className: "tooltip",
					offsetX: -7,
					offsetY: 16,
					allowTipHover: true
				});
				$(".info",opts.container).html(result["info"]);
				opts.curpath=result["path"];
				opts.page=result["page"];
				opts.realpath=result["realpath"];
				if(result["pages"])
					$(".pages",opts.container).html(result["pages"]).show();
				else
					$(".pages",opts.container).hide();
				if(th.UP)
				{
					th.UP.addPostParam("path",result["path"]);
					if(result["upload"])
					{						$("crf-"+opts.uniq).show();
						th.UP.setButtonDisabled(false);
						th.UP.setFileSizeLimit(result["upload_limit"]);
						th.UP.setButtonText(CORE.Lang("upload_text"));
					}
					else
					{
						$("crf-"+opts.uniq).hide();
						th.UP.setButtonDisabled(true);
						th.UP.setButtonText(CORE.Lang("upload_text_dis"));
					}
				}
			}
		);
	}

	this.DeleteFile=function(n,F)
	{		if(!F&&!confirm(CORE.Lang("are_you_sure_delete",[n])))
			return;
		CORE.Ajax(
			{
				what:n,
				type:"uploader",
				goal:"delete",
				uniq:opts.uniq,
				session:opts.sess,
				path:opts.curpath,
				service:opts.service
			},
			function(result)
			{				if(F)
					F();
				th.Update();
			}
		);
	}

	this.Rename=function(n)
	{
		var nn=prompt(CORE.Lang("enter_new_name"),n);
		if(!nn || nn==n)
			return;
		CORE.Ajax(
			{
				what:n,
				to:nn,
				type:"uploader",
				goal:"rename",
				uniq:opts.uniq,
				session:opts.sess,
				path:opts.curpath,
				service:opts.service
			},
			function(result)
			{
				th.Update();
			}
		);
	}

	this.InsertLink=function(link,e)
	{
		e.preventDefault();
		link=opts.realpath+link;
		if (!e || !e.altKey)
		{			if(link.match(/\.(jpe?g|bmp|gif|ico|png)$/))
				EDITOR.Embed("image",{src:link},th.editor);
			else
				EDITOR.Insert(link,th.editor);
		}
		else
			prompt(CORE.Lang("copy_and_paste"),link);
	}

	this.InsertAttach=function(link,preview,e)
	{
		e.preventDefault();
		preview=preview ? " preview="+opts.realpath+preview : "";
		link="[attach="+opts.realpath+link+preview+"]";
		if (!e || !e.altKey)
			EDITOR.Insert(link,th.editor);
		else
			prompt(CORE.Lang("copy_and_paste"),link);
	}

	this.Open=function(url)
	{		window.open(window.location.protocol+"//"+window.location.hostname+CORE.site_path+opts.realpath+url);
	}

	this.ShowPreviews=function()
	{		CORE.SetCookie("showpreviews-"+opts.uniq,opts.showpreviews ? 1 : 0);
	}

	this.DoPreviews=function()
	{
		if(this.UP)
			this.UP.addPostParam("dopreviews",opts.dopreviews ? 1 : 0);
		CORE.SetCookie("dopreviews-"+opts.uniq,opts.dopreviews ? 1 : 0);
	}

	this.WaterMark=function()
	{
		if(this.UP)
			this.UP.addPostParam("watermark",opts.watermark ? 1 : 0);
		CORE.SetCookie("watermark-"+opts.uniq,opts.watermark ? 1 : 0);
	}

	$(opts.container).on("click",".up-create_file",function(){		th.CreateFile();		return false;	}).on("click",".up-create_folder",function(){		th.CreateFolder();
		return false;	}).on("click",".up-update",function(){		th.Update();
		return false;	}).on("click",".up-watermark",function(){		opts.watermark=!opts.watermark;
		$(this).toggleClass("active");		th.WaterMark();
		return false;
	}).on("click",".up-show_previews",function(){		opts.showpreviews=!opts.showpreviews;
		$(this).toggleClass("active");
		th.ShowPreviews();
		th.Update();
		return false;	}).on("click",".up-dopreviews",function(){
		opts.dopreviews=!opts.dopreviews;
		$(this).toggleClass("active");
		th.DoPreviews();
		return false;
	}).on("click",".up-go",function(){		th.Go($(this).data("goal"));
		return false;	}).on("click",".up-delete",function(){
		th.DeleteFile($(this).data("goal"));
		return false;
	}).on("click",".up-rename",function(){
		th.Rename($(this).data("goal"));
		return false;
	}).on("click",".up-attach",function(e){
		th.InsertAttach($(this).data("goal"),$(this).data("preview"),e);
		return false;
	}).on("click",".up-link",function(e){
		th.InsertLink($(this).data("goal"),e);
		return false;
	}).on("click",".up-edit",function(){
		th.EditFile($(this).data("goal"));
		return false;
	}).on("click",".up-open",function(){
		th.Open($(this).data("goal"));
		return false;
	})

	setTimeout(function(){
		if(opts.watermark)
		{			$(".up-watermark",opts.container).toggleClass("active");
			th.WaterMark();
		}
		if(opts.dopreviews)
		{			$(".up-dopreviews",opts.container).toggleClass("active");
			th.DoPreviews();
		}
		if(opts.showpreviews)
		{			$(".up-show_previews",opts.container).toggleClass("active");
			th.ShowPreviews();
		}
	},100);
}

$.extend(
	CORE.UPLOADER,
	{
		Toggle:function(div,show,hide,a)
		{
			if(typeof a.showed=="undefined")
				a.showed=false;
			if(a.showed)
			{
				$(div).fadeOut("slow");
				$("b",a).text(show);
				a.showed=false;
			}
			else
			{
				$(div).fadeIn("slow");
				$("b",a).text(hide);
				a.showed=true;
			}
		},
		//Uploader handlers
		FileQueued:function(file)
		{
			try
			{
				var progress=new FileProgress(file,this.customSettings.progressTarget);
				progress.setStatus(CORE.Lang("upload_wait"));
				progress.toggleCancel(true,this);
			}
			catch(ex)
			{
				this.debug(ex);
			}
		},
		FileQueueError:function(file,code,mess)
		{
			try
			{
				if(code===SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED)
				{
					alert("You have attempted to queue too many files.\n"+(mess === 0 ? "You have reached the upload limit." : "You may select "+(mess > 1 ? "up to "+mess+" files." : "one file.")));
					return;
				}

				var progress=new FileProgress(file, this.customSettings.progressTarget);
				progress.setError();
				progress.toggleCancel(false);

				switch(code)
				{
					case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
						progress.setStatus(CORE.Lang("upload_filetoobig"));
						this.debug("Error Code: File too big, File name: "+file.name+", File size: "+file.size+", Message: "+mess);
					break;
					case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
						progress.setStatus("Cannot upload Zero Byte files.");
						this.debug("Error Code: Zero byte file, File name: "+file.name+", File size: "+file.size+", Message: "+mess);
					break;
					case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
						progress.setStatus(CORE.Lang("upload_invalidtype"));
						this.debug("Error Code: Invalid File Type, File name: "+file.name+", File size: "+file.size+", Message: "+mess);
					break;
					default:
						if(file!==null)
							progress.setStatus("Unhandled Error");
						this.debug("Error Code: "+code+", File name: "+file.name+", File size: "+file.size+", Message: "+mess);
				}
			}
			catch(ex)
			{
				this.debug(ex);
			}
		},
		FileDialogComplete:function(selected,queued)//Кол-во
		{
			try
			{
				if(selected>0)
				{
					$(this.customSettings.cancel_button).show();
					$(this.customSettings.infoid).hide();
				}
				this.startUpload();
			}
			catch(ex)
			{
				this.debug(ex);
			}
		},
		UploadStart:function(file)
		{
			try
			{
				var progress=new FileProgress(file,this.customSettings.progressTarget);
				progress.setStatus(CORE.Lang("upload_loading"));
				progress.toggleCancel(true,this);
			}
			catch(ex){}
			return true;
		},
		UploadProgress:function(file,loaded,total)
		{
			try
			{
				var progress=new FileProgress(file,this.customSettings.progressTarget);
				progress.setProgress(Math.ceil(loaded/total*100));
				progress.setStatus(CORE.Lang("upload_loading"));
			}
			catch(ex)
			{
				this.debug(ex);
			}
		},
		UploadError:function(file,code,mess)
		{
			try
			{
				var progress=new FileProgress(file,this.customSettings.progressTarget);
				progress.setError();
				progress.toggleCancel(false);
				switch(code)
				{
					case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
						progress.setStatus("Upload Error: "+mess);
						this.debug("Error Code: HTTP Error, File name: "+file.name+", Message: "+mess);
					break;
					case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
						progress.setStatus("Upload Failed.");
						this.debug("Error Code: Upload Failed, File name: "+file.name+", File size: "+file.size+", Message: "+mess);
					break;
					case SWFUpload.UPLOAD_ERROR.IO_ERROR:
						progress.setStatus("Server(IO) Error");
						this.debug("Error Code: IO Error, File name: "+file.name+", Message: "+mess);
					break;
					case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
						progress.setStatus("Security Error");
						this.debug("Error Code: Security Error, File name: "+file.name+", Message: "+mess);
					break;
					case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
						progress.setStatus("Upload limit exceeded.");
						this.debug("Error Code: Upload Limit Exceeded, File name: "+file.name+", File size: "+file.size+", Message: "+mess);
					break;
					case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
						progress.setStatus("Failed Validation.  Upload skipped.");
						this.debug("Error Code: File Validation Failed, File name: "+file.name+", File size: "+file.size+", Message: "+mess);
					break;
					case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
						if(this.getStats().files_queued === 0)
							$(this.customSettings.cancel_button).hide();
						progress.setStatus(CORE.Lang("upload_cancelled"));
						progress.setCancelled();
					break;
					case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
						progress.setStatus(CORE.Lang("upload_stopped"));
					break;
					default:
						progress.setStatus("Unhandled Error: "+code);
						this.debug("Error Code: "+code+", File name: "+file.name+", File size: "+file.size+", Message: "+mess);
				}
			}
			catch(ex)
			{
				this.debug(ex);
			}
		},
		UploadSuccess:function(file,answer)
		{
			try
			{
				var progress=new FileProgress(file,this.customSettings.progressTarget);
				progress.setComplete();
				progress.setStatus(CORE.Lang("upload_complete"));
				progress.toggleCancel(false);
			}
			catch(ex)
			{
				this.debug(ex);
			}
		},
		UploadComplete:function(file)
		{
			if(this.getStats().files_queued===0)
			{
				$(this.customSettings.cancel_button).hide();
				$(this.customSettings.infoid).show();
				this.customSettings.Update();
			}
		}
	}
)