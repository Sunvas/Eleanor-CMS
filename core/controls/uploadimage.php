<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/

class ControlUploadImage extends BaseClass implements ControlsBase
{
	public static
		$Language;

	private static
		$bypost;

	public static function GetSettings()
	{
		$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		$ml=false;
		return array(
			'uploadimage',#Группа контрола
			'path'=>array(
				'title'=>static::$Language['path_to_save'],
				'descr'=>static::$Language['path_to_save_'],
				'type'=>'edit',
				'options'=>array(
					'addon'=>array(
						'class'=>'uploadfile-path',
					),
				),
				'default'=>$ml ? array(''=>'') : '',
				'save'=>function($a)
				{
					$uppath=Eleanor::$root.DIRECTORY_SEPARATOR.Eleanor::$uploads;
					if($a['multilang'])
					{
						foreach($a['value'] as &$v)
						{
							$path=Eleanor::FormatPath($v,Eleanor::$uploads);
							if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
								throw new EE(static::$Language['no_upload_path'],EE::INFO);
							$v=strpos($path,$uppath)===0 ? substr($path,strlen($uppath)) : substr($path,strlen(Eleanor::$root));
							$v=str_replace(DIRECTORY_SEPARATOR,'/',$v);
						}
					}
					else
					{
						$path=Eleanor::FormatPath($a['value'],Eleanor::$uploads);
						if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
							throw new EE(static::$Language['no_upload_path'],EE::INFO);
						$a['value']=strpos($path,$uppath)===0 ? substr($path,strlen($uppath)) : substr($path,strlen(Eleanor::$root));
						$a['value']=str_replace(DIRECTORY_SEPARATOR,'/',$a['value']);
					}
					return$a['value'];
				},
				'load'=>function($a)
				{
					if($a['multilang'])
						foreach($a['value'] as &$v)
							$v=preg_replace('#^'.Eleanor::$uploads.'/?#','',$v);
					else
						$a['value']=preg_replace('#^'.Eleanor::$uploads.'/?#','',$a['value']);
					return$a;
				},
				'append'=>'<script type="text/javascript">//<![CDATA[
$(function(){
	$(".uploadfile-path:first").removeClass("uploadfile-path").autocomplete({
		serviceUrl:"'.Eleanor::$services['ajax']['file'].'",
		minChars:2,
		delimiter: null,
		params:{
			direct:"'.Eleanor::$service.'",
			file:"autocomplete",
			path:"'.Eleanor::$uploads.'"
		}
	});
});//]]></script>'
			),
			'types'=>array(
				'title'=>static::$Language['file_types'],
				'descr'=>static::$Language['file_types_'],
				'type'=>'edit',
				'default'=>$ml ? array(''=>array('png','jpeg','jpg','bmp','gif')) : array('png','jpeg','jpg','bmp','gif'),
				'save'=>function($a)
				{
					if($a['multilang'])
					{
						foreach($a['value'] as &$v)
							if($v)
							{
								$v=explode(',',$v);
								foreach($v as $k=>&$val)
								{
									$val=trim($val);
									if(!$val)
										unset($v[$k]);
								}
							}
							else
								$v=array();
						return$a['value'];
					}
					else
					{
						if($a['value'])
						{
							$a['value']=explode(',',$a['value']);
							foreach($a['value'] as $k=>&$val)
							{
								$val=trim($val);
								if(!$val)
									unset($a['value'][$k]);
							}
						}
						else
							$a['value']=array();
						return$a['value'];
					}
				},
				'load'=>function($a)
				{
					if($a['multilang'])
						foreach($a['value'] as &$v)
							$v=$v ? join(',',$v) : '';
					else
						$a['value']=$a['value'] ? join(',',$a['value']) : '';
					return$a;
				},
			),
			'max_size'=>array(
				'title'=>static::$Language['max_size_f'],
				'descr'=>static::$Language['max_size_f_'],
				'type'=>'edit',
				'default'=>$ml ? array(''=>'') : '',
				'save'=>function($a)
				{
					if($a['multilang'])
					{
						foreach($a['value'] as &$v)
							$v=Files::BytesToSize($v);
					}
					else
						$a['value']=Files::BytesToSize($a['value']);
					return$a['value'];
				},
				'load'=>function($a)
				{
					if($a['multilang'])
					{
						foreach($a['value'] as &$v)
							if($v and !is_int($v))
								$v=Files::SizeToBytes($v);
					}
					elseif($a['value'] and !is_int($a['value']))
						$a['value']=Files::SizeToBytes($a['value']);
					return$a;
				},
			),
			'filename_eval'=>array(
				'title'=>static::$Language['filename'],
				'descr'=>static::$Language['filename_'],
				'default'=>$ml ? array(''=>'') : '',
				'type'=>'text',
				'save'=>function($a)
				{
					$val=$a['multilang'] ? $a['value'] : array(Language::$main=>$a['value']);
					foreach($val as &$v)
					{
						if(!$v)
							continue;
						ob_start();
						if(create_function('',$v)===false)
						{
							$err=ob_get_contents();
							ob_end_clean();
							Eleanor::getInstance()->e_g_l=error_get_last();
							throw new EE(static::$Language['error_eval'].$err,EE::INFO);
						}
						ob_end_clean();
					};
					return$a['value'];
				},
			),
			'max_image_size'=>array(
				'title'=>static::$Language['maximsize'],
				'descr'=>static::$Language['maximsize_'],
				'default'=>$ml ? array(''=>'0 0') : '0 0',
				'type'=>'text',
				'save'=>function($a)
				{
					if($a['multilang'])
					{
						foreach($a['value'] as &$v)
							if(preg_match('#^\d+ \d+$#',$v)==0)
								throw new EE('incorrect_format',EE::INFO,array('lang'=>true));
						return$a['value'];
					}
					else
					{
						if(preg_match('#^\d+ \d+$#',$a['value'])==0)
							throw new EE('incorrect_format',EE::INFO,array('lang'=>true));
						return$a['value'];
					}
				},
			),
			'nosmaller'=>array(
				'title'=>static::$Language['nosmaller'],
				'descr'=>static::$Language['nosmaller_'],
				'default'=>$ml ? array(''=>false) : false,
				'type'=>'check',
			),
			'resize'=>array(
				'title'=>static::$Language['onmaxupload'],
				'descr'=>static::$Language['onmaxupload_'],
				'default'=>$ml ? array(''=>false) : false,
				'type'=>'select',
				'options'=>array(
					'options'=>array(
						'd'=>static::$Language['disable_upload'],
						'b'=>static::$Language['bybigger'],
						's'=>static::$Language['bysmaller'],
						'w'=>static::$Language['bywidth'],
						'h'=>static::$Language['byheight'],
					),
				),
			),
			'source'=>array(
				'title'=>static::$Language['source'],
				'descr'=>'',
				'default'=>array('upload','address'),
				'type'=>'items',
				'save_eval'=>'if(count(array_intersect(array(\'upload\',\'address\'),$a[\'value\']))==0)throw new EE(\''.static::$Language['must1t'].'\',EE::INFO);',
				'options'=>array(
					'options'=>array(
						'address'=>static::$Language['address'],
						'upload'=>static::$Language['upload'],
					),
				),
			)
		);
	}

	public static function Control($a,$Obj)
	{
		$a['options']+=array(
			'types'=>array('jpg','png','gif','bmp','jpeg'),
			'path'=>Eleanor::$uploads,
			'max_size'=>false,
			'max_image_size'=>'0 0',
			'nosmaller'=>false,
			'resize'=>'b',
			'source'=>array('address','upload'),
			'preview'=>false,
			'prevsuff'=>'_preview',

			'old'=>$a['value'],
			'new'=>false,
		);
		$a['options']['max_image_size']=explode(' ',$a['options']['max_image_size']);

		$saddr=in_array('address',$a['options']['source']);
		$sup=in_array('upload',$a['options']['source']);
		if(!$saddr and !$sup)
			return'';

		if($a['bypost'] and $sessid=$Obj->GetPostVal($a['name'],false))
		{
			Eleanor::StartSession($sessid);
			if(isset($_SESSION[__class__][$a['controlname']]))
			{
				$sv=&$_SESSION[__class__][$a['controlname']];
				if($sv['new'])
					$a['value']=$sv['new'];
				if(isset($sv['moved']))
				{
					$av=(array)$a['value'];
					$fp=array();
					foreach($sv['moved'] as $k=>$v)
						if(isset($av[$k]))
							rename($v,$fp[$k]=Eleanor::FormatPath($av[$k]));
					unset($sv['moved']);
				}
			}
		}
		elseif(!isset($_SESSION))
			Eleanor::StartSession();

		if(!isset($av))
			$av=(array)$a['value'];#0 - картинка, 1 - превьюшка
		if(!isset($fp))
		{
			$fp=array();
			foreach($av as $k=>&$v)
			{
				if($v==basename($v))
					$v=rtrim($a['options']['path'],'\\/').'/'.$v;
				if(strpos($v,'://')===false)
					$fp[$k]=Eleanor::FormatPath($v);
			}
		}

		$writed=isset($av[0]) && !isset($fp[0]);
		$a['file']=isset($fp[0]) && is_file($fp[0]);

		array_walk_recursive($a['options'],function(&$v){			if(is_object($v))
				$v=null;		});

		$_SESSION[__class__][$a['controlname']]=$a['options'];
		$sid=session_id();

		array_push($GLOBALS['jscripts'],$saddr ? 'addons/autocomplete/jquery.autocomplete.js' : false,'addons/swfupload/swfupload.js','addons/colorbox/jquery.colorbox-min.js');
		$GLOBALS['head']['autocomplete|style']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';

		if($sup)
		{
			$types=$a['options']['types'] ? '*.*' : '*.'.join(';*.',$a['options']['types']).';';
			$maxsize=Files::SizeToBytes(ini_get('upload_max_filesize'));
			if($a['options']['max_size'] and $maxsize>$a['options']['max_size'])
				$maxsize=$a['options']['max_size'];
		}

		$id=uniqid();
		return'<script type="text/javascript">//<![CDATA[
		$(function(){
			var I=$("#i'.$id.'");
			//Max-width для div-a
			I.each(function(){
				var p=$(this).parent();
				setTimeout(function(){
					if(p.width()>0)
						p.end().width(p.width()+"px");
					'.($a['file'] || $writed
						? 'I.find(".aimage").show().prop("href","'.$av[0].'").find("img").prop("src","'.(isset($av[1]) ? $av[1] : $av[0]).'");
						$(".delete",I).show();'
						: '$(".screenblock",I).show();'
					).'
	 			},200);
			})

			.on("click",".delete",function(){
				CORE.Ajax(
					{
						type:"uploadimage",
						"do":"delete",
						session:"'.$sid.'",
						name:"'.$a['controlname'].'"
					},
					function(result)
					{
						$(".aimage,.delete",I).hide();
						$(".screenblock",I).show();
					}
				);
				return false;
			})

			.find(".aimage").colorbox({
				title:function(){
					var url=$(this).attr("href"),
						title=$(this).find("img").attr("title");
					return "<a href=\""+url+"\" target=\"_blank\">"+(title ? title : url)+"</a>";
				},
				maxWidth:Math.round(screen.width/1.5),
				maxHeight:Math.round(screen.height/1.5)
			});

			'.($sup ? 'var I'.$id.'=new SWFUpload({
				flash_url:"'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'addons/swfupload/swfupload.swf",
				upload_url:"'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'upload.php",
				file_post_name:"image",
				post_params:{type:"uploadimage",session:"'.$sid.'",name:"'.$a['controlname'].'"},
				file_size_limit:"'.$maxsize.' B",
				file_types:"'.$types.'",
				file_types_description:"Images",
				file_upload_limit:"0",
				file_queue_limit:"1",
				file_dialog_complete_handler:function(numFilesSelected,numFilesQueued)
					{
						try
						{
							if(numFilesQueued>0)
							{
								this.startUpload();
								$(".cancel",I).show();
							}
						}
						catch(ex){this.debug(ex)}
					},
				upload_progress_handler:function(file,bytesLoaded)
					{
						$(".cancel",I).text("'.static::$Language['cancel'].' ("+Math.ceil(bytesLoaded/file.size*100)+"%)");
					},
				upload_error_handler:function(file, errorCode, message)
					{
						$(".cancel",I).hide();
					},
				upload_success_handler:function(file,sd)
					{
						sd=$.parseJSON(sd);
						if(sd.error)
							alert(sd.error);
						else
						{
							$(".aimage",I).show().prop("href",sd.file).find("img").prop("src",sd.preview ? sd.preview : sd.file);
							$(".delete",I).show();
							$(".screenblock,.enterhere",I).hide();
						}
						$(".cancel",I).hide();
					},
				button_placeholder_id:"i'.$id.'-upload",
				button_image_url:"'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'images/uploader/uploadbtn.png",
				button_text:\'<span class="upbtntext">'.static::$Language['upload'].'</span>\',
				button_text_style:".upbtntext { font-size: 11px; color: #6D6A65; font-family: Tahoma, Arial, sans-serif; font-weight: bold; }",
				button_text_left_padding:16,
				button_text_top_padding:4,
				button_width:129,
				button_height:27,
				button_action:SWFUpload.BUTTON_ACTION.SELECT_FILE,
				button_window_mode:SWFUpload.WINDOW_MODE.OPAQUE,
				button_cursor:SWFUpload.CURSOR.HAND,
				debug:false
			});
			$("#i'.$id.'-cancel").click(function(){I'.$id.'.cancelUpload(); return false;});' : '')

			.($saddr ? '$(".enterhere input",I).autocomplete({
				serviceUrl:"'.Eleanor::$services['ajax']['file'].'",
				minChars:2,
				delimiter: null,
				params:{
					direct:"'.Eleanor::$service.'",
					file:"autocomplete",
					filter:"types",
					types:"'.join(',',$a['options']['types']).'"
				}
			});
			var DoWrited=function()
				{
					var text=$(".enterhere",I).find(":text");
					if(!$.trim(text.val()))
						return false;
					CORE.Ajax(
						{
							type:"uploadimage",
							"do":"write",
							session:"'.$sid.'",
							name:"'.$a['controlname'].'",
							image:text.val()
						},
						function(result)
						{
							$(".aimage",I).attr("href",text.val()).find("img").attr("src",text.val());
							text.val("");
							$(".enterhere,.screenblock",I).hide();
							$(".delete,.aimage",I).show();
						}
					);
				}
			$(".enterhere",I).hide().find(":text").keypress(function(e){
				if(e.keyCode==13)
				{
					e.preventDefault();
					DoWrited();
					return false;
				}
			}).end().find(":button").click(DoWrited);
			$(".enter",I).click(function(){
				$(".enterhere",I).toggle();
				return false;
			});' : '').'
		});//]]></script><div id="i'.$id.'">'
			.($sup ? '<span id="i'.$id.'-upload"></span>' : '')
			.($saddr ? '<a class="imagebtn enter" href="#">'.static::$Language['address'].'</a><div class="clr"></div><div class="enterhere">'.static::$Language['enter_address'].Eleanor::Edit('','',array('style'=>'width:70%')).' '.Eleanor::Button('OK','button').'</div>' : '')
			.'<div style="padding:5px 0;">
				<span style="width:'.($a['options']['max_image_size'][0] ? $a['options']['max_image_size'][0] : '180').'px;height:'.($a['options']['max_image_size'][1] ? $a['options']['max_image_size'][1] : '145').'px;text-decoration:none;max-height:100%;max-width:100%;display:none;" class="screenblock"><b>'.static::$Language['upload_image'].'</b><br /><span>'.sprintf('<b>%s</b> <small>x</small> <b>%s</b> <small>px</small>',$a['options']['max_image_size'][0] ? $a['options']['max_image_size'][0] : '&infin;',$a['options']['max_image_size'][1] ? $a['options']['max_image_size'][1] : '&infin;').'</span></span>
				<a href="#" class="aimage" style="display:none;"><img style="border:1px solid #c9c7c3;max-width:'.($a['options']['max_image_size'][0]>0 ? $a['options']['max_image_size'][0] : '100%').';max-height:'.($a['options']['max_image_size'][1]>0 ? $a['options']['max_image_size'][1] : '100%').'" src="images/spacer.png" /></a>
			</div>
			<a class="imagebtn delete" style="display:none;" href="#">'.static::$Language['delete'].'</a>'
			.($sup ? '<a class="imagebtn cancel" href="#" style="display:none;"></a>' : '')
			.Eleanor::Control($a['controlname'],'hidden',$sid).'</div>';
	}

	public static function Save($a,$Obj)
	{
		$a['options']+=array(
			'filename_eval'=>null,
			'filename'=>null,
		);
		if(!$sessid=$Obj->GetPostVal($a['name'],''))
			return'';

		$name=$Obj->GenName($a['name']);
		Eleanor::StartSession($sessid);
		if(!isset($_SESSION[__class__][$name]))
		{
			if($Obj->throw)
				throw new EE(static::$Language['session_lost'],EE::INFO);
			else
				$Obj->errors[]='SESSION_LOST';
			return;
		}
		$sarr=$_SESSION[__class__][$name];

		if(!$sarr['new'])
			return$sarr['old'];

		$aold=(array)$sarr['old'];
		$ofp=array();
		foreach($aold as $k=>&$v)
		{
			if($v==basename($v))
				$v=rtrim($sarr['path'],'\\/').'/'.$v;
			if(strpos($v,'://')===false)
				$ofp[$k]=Eleanor::FormatPath($v);
		}

		$ofile=isset($ofp[0]) && is_file($ofp[0]);
		$owrited=$ofile && dirname($aold[0])!=trim($sarr['path'],'/\\') || isset($aold[0]) && !isset($ofp[0]);

		if(!isset($ofp[1]) and $ofile and $sarr['preview'])
			$ofp[1]=substr_replace($ofp[0],$sarr['prevsuff'],strrpos($ofp[0],'.'),0);

		if($ofile and !$owrited)
			foreach($ofp as &$v)
				Files::Delete($v);

		$snew=is_array($sarr['new']) ? reset($sarr['new']) : $sarr['new'];#Single new

		$writed=strpos($snew,'://')!==false;
		$saddr=in_array('address',$sarr['source']);
		$sup=in_array('upload',$sarr['source']);
		if($snew===true or $writed and !$saddr or !$writed and !$sup or !$sup and !$saddr)
			return'';

		if($writed)
			return$sarr['new'];

		$path=($sarr['path'] ? Eleanor::FormatPath($sarr['path']) : Eleanor::$root.Eleanor::$uploads).DIRECTORY_SEPARATOR;
		if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
		{
			if($Obj->throw)
				throw new EE(static::$Language['no_upload_path'],EE::INFO);
			else
				$Obj->errors[]='NO_UPLOAD_PATH';
			return;
		}

		if(is_callable($a['options']['filename']))
			$filename=call_user_func($a['options']['filename'],array('filename'=>basename($snew))+$a,__class__);
		elseif($a['options']['filename_eval'])
		{
			ob_start();
			$func=create_function('$a,$Obj',$a['options']['filename_eval']);
			if($func===false)
			{
				$err=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				if($Obj->throw)
					throw new EE('Error in filename eval: <br />'.$err,EE::DEV,array('code'=>1));
				else
					$Obj->errors['ERROR_FILENAME']=$err;
			}
			$filename=$func(array('filename'=>basename($snew))+$a,__class__);
			ob_end_clean();
		}
		else
			$filename=basename($snew);

		$rpath=rtrim($sarr['path'] ? $sarr['path'] : Eleanor::$uploads,'/\\').'/';
		$r=array();
		foreach((array)$sarr['new'] as $k=>$s)
		{
			$s=Eleanor::$root.$s;
			$fn=$k==0 ? $filename : substr_replace($filename,$sarr['prevsuff'],strrpos($filename,'.'),0);
			$d=$path.$fn;
			$m=false;
			if(is_file($s) and ($s==$d or Files::Delete($d) and $m=rename($s,$d)))
			{
				if($m)
					$_SESSION[__class__][$name]['moved'][$k]=$d;
				$r[$k]=$rpath.$fn;
			}
			if(!$sarr['preview'])
				break;
		}
		return$sarr['preview'] ? $r : reset($r);
	}

	public static function Result($a,$Obj,$co)
	{
		$a['options']+=array('retempty'=>true,'onlyimage'=>true,'max_image_size'=>'0 0','alt'=>'');
		$a['options']['max_image_size']=explode(' ',$a['options']['max_image_size']);
		if(!$a['value'])
			return$a['options']['retempty'] ? null : '<span style="width:'.($a['options']['max_image_size'][0] ? $a['options']['max_image_size'][0] : '180').'px;height:'.($a['options']['max_image_size'][1] ? $a['options']['max_image_size'][1] : '145').'px;text-decoration:none;max-height:100%;max-width:100%;" class="screenblock"><b>'.static::$Language['noimage'].'</b></span>';
		if(is_array($a['value']))
			list($img,$prev)=$a['value'];
		else
			$img=$prev=$a['value'];
		if($a['options']['onlyimage'])
			return'<img style="border:1px solid #c9c7c3;max-width:'.($a['options']['max_image_size'][0]>0 ? $a['options']['max_image_size'][0] : '100%').';max-height:'.($a['options']['max_image_size'][1]>0 ? $a['options']['max_image_size'][1] : '100%').'" src="'.$img.'" alt="'.$a['options']['alt'].'" />';
		$GLOBALS['jscripts'][]='addons/colorbox/jquery.colorbox-min.js';
		$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';
		$u=uniqid();
		return'<a href="'.$img.'" id="img-'.$u.'"><img style="border:1px solid #c9c7c3;max-width:'.($a['options']['max_image_size'][0]>0 ? $a['options']['max_image_size'][0] : '100%').';max-height:'.($a['options']['max_image_size'][1]>0 ? $a['options']['max_image_size'][1] : '100%').'" src="'.$prev.'" alt="'.$a['options']['alt'].'" /></a><script type="text/javascript">//<![CDATA[
$(function(){
	$("#img-'.$u.'").colorbox({
		title: function(){
			var url=$(this).attr("href"),
				title=$(this).find("img").attr("title");
			return "<a href=\""+url+"\" target=\"_blank\">"+(title ? title : url)+"</a>";
		},
		maxWidth:Math.round(screen.width/1.5),
		maxHeight:Math.round(screen.height/1.5),
	});
});//]]></script>';
	}

	public static function DoAjax()
	{
		$session=isset($_POST['session']) ? (string)$_POST['session'] : '';
		$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
		Eleanor::StartSession($session);
		if(!isset($_SESSION[__class__][$name]))
			return Error(static::$Language['session_lost']);
		$a=$_SESSION[__class__][$name];
		$type=isset($_POST['do']) ? $_POST['do'] : '';
		switch($type)
		{
			case'write':
				if(!in_array('address',$a['source']))
					return Error();
				$a['new']=isset($_POST['image']) ? (string)$_POST['image'] : '';
				if(strpos($a['new'],'://')!==false && is_file($f=Eleanor::FormatPath($a['new'])))
				{
					if($a['types'] and !in_array(substr(strrchr($a['new'],'.'),1),$a['types']))
						return Error(sprintf(static::$Language['only_types'],join(', ',$a['types'])));
					if(!$sizes=@getimagesize($f))
						return Error(static::$Language['not_image']);
					list($w,$h)=$sizes;
					if($a['max_image_size'][0]>0 and $a['max_image_size'][0]<$w)
						return Error(sprintf(static::$Language['bigger_w'],$a['max_image_size'][0],$w));
					if($a['max_image_size'][1]>0 and $a['max_image_size'][1]<$h)
						return Error(sprintf(static::$Language['bigger_h'],$a['max_image_size'][1],$h));
					if($a['nosmaller'] and ($a['max_image_size'][1]>0 and $a['max_image_size'][1]>$h or $a['max_image_size'][0]>0 and $a['max_image_size'][0]>$w))
						return Error(sprintf(static::$Language['smaller'],$w,$h,$a['max_image_size'][0] ? $a['max_image_size'][0] : '&infin;',$a['max_image_size'][1] ? $a['max_image_size'][1] : '&infin;'));
				}
			break;
			case'delete':
				$a['new']=true;
			break;
			default:
				return Error();
		}
		$_SESSION[__class__][$name]=$a;
		Result(true);
	}

	public static function DoUpload()
	{
		$session=isset($_POST['session']) ? (string)$_POST['session'] : '';
		$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
		Eleanor::StartSession($session);
		if(!isset($_SESSION[__class__][$name]))
			return Error('No session!');
		$a=$_SESSION[__class__][$name];

		if(!isset($_FILES['image']) or !is_uploaded_file($_FILES['image']['tmp_name']) or !in_array('upload',$a['source']))
			return Error('No file!');

		if(!$sizes=@getimagesize($_FILES['image']['tmp_name']))
			return Error(static::$Language['not_image']);
		list($w,$h)=$sizes;
		if($a['nosmaller'] and ($a['max_image_size'][1]>0 and $a['max_image_size'][1]>$h or $a['max_image_size'][0]>0 and $a['max_image_size'][0]>$w))
			return Error(sprintf(static::$Language['smaller'],$w,$h,$a['max_image_size'][0] ? $a['max_image_size'][0] : '&infin;',$a['max_image_size'][1] ? $a['max_image_size'][1] : '&infin;'));

		$max_w=$a['max_image_size'][0]>0 && $a['max_image_size'][0]<$w;
		$max_h=$a['max_image_size'][1]>0 && $a['max_image_size'][1]<$h;
		if($a['resize']=='d')
		{
			if($max_w)
				return Error(sprintf(static::$Language['bigger_w'],$a['max_image_size'][0],$w));
			if($max_h)
				return Error(sprintf(static::$Language['bigger_h'],$a['max_image_size'][1],$h));
		}
		if(!is_dir(Eleanor::$root.DIRECTORY_SEPARATOR.Eleanor::$uploads.'/temp/'))
			Files::MkDir(Eleanor::$root.DIRECTORY_SEPARATOR.Eleanor::$uploads.'/temp/');
		$a['new']=Eleanor::$uploads.'/temp/'.uniqid().strrchr($_FILES['image']['name'],'.');
		$ufn=Eleanor::$root.$a['new'];
		if(!move_uploaded_file($_FILES['image']['tmp_name'],$ufn))
			return Error('File not loaded!');

		try
		{
			if($max_w or $max_h)
				Image::Preview(
					$ufn,
					array(
						'width'=>$a['max_image_size'][0],
						'height'=>$a['max_image_size'][1],
						'cut_first'=>false,
						'cut_last'=>$a['resize']!='b',
						'first'=>$a['resize'],
						'newname'=>$ufn,
					)
				);
			if($a['preview'])
				$a['new']=array(
					$a['new'],
					Eleanor::$uploads.'/temp/'.basename(Image::Preview($ufn,array('suffix'=>$a['prevsuff'])+(is_array($a['preview']) ? $a['preview'] : array())))
				);
		}
		catch(EE$E)
		{
			return Error($E->getMessage());
		}
		$_SESSION[__class__][$name]=$a;
		Result($a['preview'] ? array('file'=>$a['new'][0],'preview'=>$a['new'][1]) : array('file'=>$a['new']));
	}
}
ControlUploadImage::$Language=new Language;
ControlUploadImage::$Language->queue[]='uploadimage-*.php';