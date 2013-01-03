<?php
/*
	Внешний вид контрола "загрузка изображения" (uploadimage).

	@var string идентификатор сессии
	@var bool возможность загрузки
	@var bool возможность вписывания адреса к изображению
	@var array() - массив возможных типов файлов, доступных для загрузки
	@var int - максимальный размер файла, доступного для загрузки
	@var string - параметр name для инпутов
	@var int - максимальная ширина изображения
	@var int - максимальная высота изображения
	@var array - массив с ключами:
		write - прописанное значение
		image - путь к загруженной картинке
		preview - путь к превьюшке
*/
$sid=&$v_0;
$upload=&$v_1;
$write=&$v_2;
$types=&$v_3;
$maxsize=&$v_4;
$name=&$v_5;
$iw=&$v_6;
$ih=&$v_7;
$values=&$v_8;
$lang=Eleanor::$Language->Load($theme.'langs/uploadimage-*.php',false);

array_push($GLOBALS['jscripts'],$write ? 'addons/autocomplete/jquery.autocomplete.js' : false,'addons/swfupload/swfupload.js','addons/colorbox/jquery.colorbox-min.js');
$GLOBALS['head']['autocomplete|style']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';

$image=$values['image'] ? $values['image'] : $values['write'];
$u=uniqid();
echo'<script type="text/javascript">//<![CDATA[
$(function(){
	var I=$("#i'.$u.'");
	//Max-width для div-a
	I.each(function(){
		var p=$(this).parent();
		setTimeout(function(){
			if(p.width()>0)
				p.end().width(p.width()+"px");
			'.($image
				? 'I.find(".aimage").show().prop("href","'.$image.'").find("img").prop("src","'.($values['preview'] ? $values['preview'] : $image).'");
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
				name:"'.$name.'"
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

	'.($upload ? 'var I'.$u.'=new SWFUpload({
		flash_url:"'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'addons/swfupload/swfupload.swf",
		upload_url:"'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'upload.php",
		file_post_name:"image",
		post_params:{type:"uploadimage",session:"'.$sid.'",name:"'.$name.'"},
		file_size_limit:"'.$maxsize.' B",
		file_types:"'.($types ? '*.'.join(';*.',$types).';' : '*.*').'",
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
				$(".cancel",I).text("'.$lang['cancel'].' ("+Math.ceil(bytesLoaded/file.size*100)+"%)");
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
		button_placeholder_id:"i'.$u.'-upload",
		button_image_url:"'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'images/uploader/uploadbtn.png",
		button_text:\'<span class="upbtntext">'.$lang['upload'].'</span>\',
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
	$("#i'.$u.'-cancel").click(function(){I'.$u.'.cancelUpload(); return false;});' : '')

	.($write ? '$(".enterhere input",I).autocomplete({
		serviceUrl:CORE.ajax_file,
		minChars:2,
		delimiter: null,
		params:{
			direct:"'.Eleanor::$service.'",
			file:"autocomplete",
			filter:"types",
			types:"'.join(',',$types).'"
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
					name:"'.$name.'",
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
});//]]></script><div id="i'.$u.'">'
	.($upload ? '<span id="i'.$u.'-upload"></span>' : '')
	.($write ? '<a class="imagebtn enter" href="#">'.$lang['address'].'</a><div class="clr"></div><div class="enterhere">'.$lang['enter_address'].Eleanor::Input('',$values['write'],array('style'=>'width:70%')).' '.Eleanor::Button('OK','button').'</div>' : '')
	.'<div style="padding:5px 0;">
		<span style="width:'.($iw ? $iw : '180').'px;height:'.($ih ? $ih : '145').'px;text-decoration:none;max-height:100%;max-width:100%;display:none;" class="screenblock"><b>'.$lang['upload_image'].'</b><br /><span>'.sprintf('<b>%s</b> <small>x</small> <b>%s</b> <small>px</small>',$iw ? $iw : '&infin;',$ih ? $ih : '&infin;').'</span></span>
		<a href="#" class="aimage" style="display:none;"><img style="border:1px solid #c9c7c3;max-width:'.($iw>0 ? $iw : '100%').';max-height:'.($ih>0 ? $ih : '100%').'" src="images/spacer.png" /></a>
	</div>
	<a class="imagebtn delete" style="display:none;" href="#">'.$lang['delete'].'</a>'
	.($upload ? '<a class="imagebtn cancel" href="#" style="display:none;"></a>' : '')
	.Eleanor::Input($name,$sid,array('type'=>'hidden')).'</div>';