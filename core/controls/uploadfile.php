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
class ControlUploadFile extends BaseClass implements ControlsBase
{
	public static
		$Language;

	private static
		$bypost;

	public static function GetSettings($Obj)
	{
		$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		$ml=false;
		return array(
			'uploadfile',#Группа контрола
			'path'=>array(
				'title'=>static::$Language['path_to_save'],
				'descr'=>static::$Language['path_to_save_'],
				'type'=>'edit',
				'options'=>array(
					'extra'=>array(
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
								throw new EE(static::$Language['no_upload_path'],EE::ENV);
							$v=strpos($path,$uppath)===0 ? substr($path,strlen($uppath)) : substr($path,strlen(Eleanor::$root));
							$v=str_replace(DIRECTORY_SEPARATOR,'/',$v);
						}
					}
					else
					{
						$path=Eleanor::FormatPath($a['value'],Eleanor::$uploads);
						if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
							throw new EE(static::$Language['no_upload_path'],EE::ENV);
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
				'default'=>$ml ? array(''=>array()) : array(),
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
				'descr'=>static::$Language['max_size_fd'],
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
							throw new EE(static::$Language['error_eval'].$err,EE::DEV);
						}
						ob_end_clean();
					};
					return$a['value'];
				},
			),
		);
	}

	public static function Control($a,$Obj)
	{
		$a['options']+=array(
			'types'=>array(),
			'path'=>Eleanor::$uploads,
			'max_size'=>false,
		);
		$a['options']['types']=$a['options']['types'] ? (array)$a['options']['types'] : array();

		if($a['bypost'] and $value=$Obj->GetPostVal($a['name'],false))
		{
			$writed=isset($value['type']) and $value['type']=='w';
			$a['value']=isset(static::$bypost[$a['controlname']]) ? static::$bypost[$a['controlname']] : $a['default'];
		}
		else
			$writed=strpos($a['value'],'://')!==false;
		$f=!$writed && is_file(Eleanor::FormatPath($a['value'])) && dirname($a['value'])==($a['options']['path'] ? trim($a['options']['path'],'/\\') : Eleanor::$uploads);
		$img=(($f or $writed) and preg_match('#\.(png|jpe?g|bmp|gif)$#i',$a['value'])>0);
		$scripts='';
		$r='<ul style="list-style-type:none">';
		$id=uniqid();
		if($f or $writed)
		{
			$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
			$GLOBALS['head']['autocomplete|style']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
			$scripts.='<script type="text/javascript">//<![CDATA[
			';
			if($img)
			{
				$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';
				$GLOBALS['jscripts'][]='addons/colorbox/jquery.colorbox-min.js';
				$scripts.='$(function(){
					$("#a-'.$id.'").colorbox({
						title: function(){
							var url=$(this).attr("href"),
								title=$(this).find("img").attr("title");
							return "<a href=\""+url+"\" target=\"_blank\">"+(title ? title : url)+"</a>";
						},
						maxWidth:Math.round(screen.width/1.5),
						maxHeight:Math.round(screen.height/1.5),
					});
				});';
			}
			$scripts.='$(function(){
				$("#text-'.$id.'").autocomplete({
					serviceUrl:"'.Eleanor::$services['ajax']['file'].'",
					minChars:2,
					delimiter: null,
					params:{
						direct:"'.Eleanor::$service.'",
						file:"autocomplete"
					}
				});
			});
			//]]></script>';
			$r.='<li><span style="vertical-align:15%">'.sprintf($f ? static::$Language['uploaded_file'] : static::$Language['writed_file'],'<a href="'.$a['value'].'" target="_blank"'.($img ? ' id="a-'.$id.'"' : '').'>'.basename($a['value']).'</span></a>');
			if($f)
				$r.='<label style="margin:0px 15px">'.Eleanor::Check($a['controlname'].'[delete]').'<span style="vertical-align:15%"> '.static::$Language['delete'].'</span></label>';
			$r.='</li>';
		}
		return$scripts.$r.'<li class="upload"'.($writed ? ' style="display:none"' : '').'>'.Eleanor::Control($a['controlname'].'[file]','file',false,array('onchange'=>'$(this).closest(\'form\').attr(\'enctype\',\'multipart/form-data\')')).'<br /><a class="small" href="#" onclick="$(\'li.upload\').hide();$(\'li.write\').show();$(\'#type-'.$id.'\').val(\'w\');return false">'.static::$Language['write'].'</a></li>'
				.'<li class="write"'.($writed ? '' : ' style="display:none"').'>'.Eleanor::Edit($a['controlname'].'[text]','',array('id'=>'text-'.$id)).'<br /><a class="small" href="#" onclick="var f=$(this).closest(\'form\');f.find(\'li.upload\').show();f.find(\'li.write\').hide();$(\'#type-'.$id.'\').val(\'u\');return false">'.static::$Language['upload'].'</a></li>'
				.($a['options']['max_size'] ? '<li class="upload"'.($writed ? ' style="display:none"' : '').'><span class="small" style="font-weight:bold">'.sprintf(static::$Language['max_size'],Files::BytesToSize($a['options']['max_size'])).'</span></li>' : '')
				.($a['options']['types'] ? '<li><span class="small" style="font-weight:bold">'.sprintf(static::$Language['allowed_types'],join(', ',$a['options']['types'])).'</span></li>' : '')
				.'</ul>'.Eleanor::Control($a['controlname'].'[type]','hidden',$writed ? 'w' : 'u',array('id'=>'type-'.$id));
	}

	public static function Save($a,$Obj)
	{
		$a['options']+=array(
			'types'=>array(),
			'path'=>'',
			'filename_eval'=>null,
			'filename'=>null,
		);
		$newv='';

		$a+=array(
			'value'=>'',
		);

		$writed=strpos($a['value'],'://')!==false;
		$vpath=$a['value'] ? Eleanor::FormatPath($a['value']) : false;
		$f=$vpath && !$writed && is_file($vpath) && dirname($a['value'])==($a['options']['path'] ? trim($a['options']['path'],'/\\') : Eleanor::$uploads) && strpos('://',$vpath)===false;
		if($f and $Obj->GetPostVal(array_merge($a['name'],array('delete')),false))
		{
			Files::Delete($vpath);
			$newv='';
		}

		$a['name']=(array)$a['name'];
		$a['options']['types']=(array)$a['options']['types'];
		if($Obj->GetPostVal(array_merge($a['name'],array('type')),'w')=='w' and $text=$Obj->GetPostVal(array_merge($a['name'],array('text')),false))
		{
			if($a['options']['types'] and preg_match('#\.('.join('|',$a['options']['types']).')$#i',$text)==0)
				throw new EE(static::$Language['error_ext'],EE::USER);
 			return$text;
		}

		$Obj->POST=&$_FILES;
		$tmp=$Obj->GetPostVal(array_merge(array('tmp_name'),$a['name'],array('file')),false);
		$name=$Obj->GetPostVal(array_merge(array('name'),$a['name'],array('file')),false);
		if(!$tmp or !$name or !is_uploaded_file($tmp))
			return$newv;

		$path=$a['options']['path'] ? Eleanor::FormatPath($a['options']['path']) : Eleanor::$root.Eleanor::$uploads.'/';
		if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
			throw new EE(static::$Language['no_upload_path'],EE::ENV);
		if($a['options']['types'] and preg_match('#\.('.join('|',$a['options']['types']).')$#i',$name)==0)
			throw new EE(static::$Language['error_ext'],EE::ENV);
		if(is_callable($a['options']['filename']))
			$filename=call_user_func($a['options']['filename'],array('filename'=>$name)+$a,$this);
		elseif($a['options']['filename_eval'])
		{
			ob_start();
			$f=create_function('$a,$Obj',$a['options']['filename_eval']);
			if($f===false)
			{
				$err=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				throw new EE('Error in filename eval: <br />'.$err,EE::DEV,array('code'=>1));
			}
			$filename=$f($a,$this);
			ob_end_clean();
		}
		else
			$filename=$name;
		if(!$a['options']['path'])
			$a['options']['path']=Eleanor::$uploads;
		if(move_uploaded_file($tmp,$path.$filename))
		{
			if(substr($a['options']['path'],-1)!='/')
				$a['options']['path'].='/';
			$a['options']['path'].=$filename;
			static::$bypost[$Obj->GenName($a['name'])]=$a['options']['path'];
			return$a['options']['path'];
		}
		return$newv;
	}

	public static function Result($a,$Obj,$co)
	{
		Eleanor::LoadOptions('editor');
		$h=$a['value'];
		if(Eleanor::$vars['anti_directlink'] and 0!==strpos($h,PROTOCOL.Eleanor::$domain) and false!==$pos=strpos($h,'://') and $pos<7)
			$h='go.php?'.$h;
		return'<a href="'.$h.'">'.$a['value'].'</a>';
	}
}
ControlUploadFile::$Language=new Language;
ControlUploadFile::$Language->queue[]='uploadfile-*.php';