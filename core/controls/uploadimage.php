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
		$Language;#Языковой объект

	private static
		$bypost;#Флаг чтения данных из POST

	/**
	 * Получение настроек контрола
	 * @param ControlsManager $Obj
	 */
	public static function GetSettings($Obj)
	{
		$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		$ml=false;
		return array(
			'uploadimage',#Группа контрола
			'path'=>array(
				'title'=>static::$Language['path_to_save'],
				'descr'=>static::$Language['path_to_save_'],
				'type'=>'input',
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
		serviceUrl:CORE.ajax_file,
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
				'type'=>'input',
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
				'type'=>'input',
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
								throw new EE('incorrect_format',EE::USER,array('lang'=>true));
						return$a['value'];
					}
					else
					{
						if(preg_match('#^\d+ \d+$#',$a['value'])==0)
							throw new EE('incorrect_format',EE::USER,array('lang'=>true));
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
				'save_eval'=>'if(count(array_intersect(array(\'upload\',\'address\'),$a[\'value\']))==0)throw new EE(\''.static::$Language['must1t'].'\',EE::DEV);',
				'options'=>array(
					'options'=>array(
						'address'=>static::$Language['address'],
						'upload'=>static::$Language['upload'],
					),
				),
			)
		);
	}

	/**
	 * Получение контрола
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
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
		);
		$a['options']['max_image_size']=explode(' ',$a['options']['max_image_size'],2);

		$write=in_array('address',$a['options']['source']);
		$upload=in_array('upload',$a['options']['source']);
		if(!$write and !$upload)
			return'';

		if($a['bypost'] and $sessid=$Obj->GetPostVal($a['name'],false))
		{
			Eleanor::StartSession($sessid);
			if(isset($_SESSION[__class__][$a['controlname']]))
			{
				$sv=&$_SESSION[__class__][$a['controlname']];
				if(isset($sv['moved']))
				{
					$temp=Eleanor::$uploads.'/'.uniqid().'/';
					if(!is_dir($temp))
						Files::MkDir($temp);
					$a['value']=array();
					foreach($sv['moved'] as $k=>&$v)
					{
						$bn=$temp.basename($v);
						if(rename($v,Eleanor::$root.$bn))
							$a['value'][$k]=$bn;
					}
					unset($sv['moved']);
				}
				elseif($sv['value'])
					$a['value']=$sv['value'];

				if(isset($sv['deleted']))
				{
					$path=Eleanor::FormatPath($a['options']['path']).DIRECTORY_SEPARATOR;
					foreach($sv['deleted'] as &$v)
					{
						$bn=basename($v);
						rename($v,$path.$bn);
					}
				}

			}
		}
		elseif(!isset($_SESSION))
			Eleanor::StartSession();

		#Сохранение старого значения для сессии
		$a['options']['value']=$a['value'];

		$a['options']['path']=rtrim($a['options']['path'],'\\/');
		$a['value']=$a['value'] ? (array)$a['value'] : array();
		$full=array();
		foreach($a['value'] as $k=>&$v)
			if($v and strpos($v,'://')===false)
			{
				if($v==basename($v))
					$v=$a['options']['path'].'/'.$v;
				$full[$k]=Eleanor::FormatPath($v);
			}

		$writed=isset($a['value'][0]);
		$uploaded=isset($full['image']) && is_file($full['image']);

		array_walk_recursive($a['options'],function(&$v){
			if(is_object($v))
				$v=null;
		});
		$_SESSION[__class__][$a['controlname']]=$a['options'];

		if($upload)
		{
			$types=$a['options']['types'];
			$maxsize=Files::SizeToBytes(ini_get('upload_max_filesize'));
			if($a['options']['max_size'] and $maxsize>$a['options']['max_size'])
				$maxsize=$a['options']['max_size'];
		}
		else
			$types=$maxsize=false;

		return Eleanor::$Template->ControlUploadImage(session_id(),$upload,$write,$types,$maxsize,$a['controlname'],$a['options']['max_image_size'][0],$a['options']['max_image_size'][1],array(
			'write'=>$writed ? $a['value'][0] : '',
			'image'=>isset($a['value']['image']) ? $a['value']['image'] : '',
			'preview'=>isset($a['value']['preview']) ? $a['value']['preview'] : '',
		));
	}

	/**
	 * Сохранение контрола
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Save($a,$Obj)
	{
		$a+=array('value'=>'');
		$a['options']+=array(
			'path'=>Eleanor::$uploads,
			'filename_eval'=>null,
			'filename'=>null,
			'preview'=>null,
			'prevsuff'=>'_preview',
		);
		if(!$sessid=$Obj->GetPostVal($a['name'],''))
			return'';

		$name=$Obj->GenName($a['name']);
		Eleanor::StartSession($sessid);
		if(!isset($_SESSION[__class__][$name]))
		{
			if($Obj->throw)
				throw new EE(static::$Language['session_lost'],EE::USER);
			else
				$Obj->errors[]='SESSION_LOST';
			return;
		}
		$sess=&$_SESSION[__class__][$name];

		if($sess['value']==$a['value'])
			return$a['value'];

		$a['options']['path']=rtrim($a['options']['path'],'\\/');
		$a['value']=$a['value'] ? (array)$a['value'] : array();
		if($a['value'])
		{
			$u=uniqid();
			$temp=Eleanor::$root.Eleanor::$uploads.'/temp/'.$u.'/';
			$delete=!is_dir($temp) && !Files::MkDir($temp);
		}

		foreach($a['value'] as $k=>&$v)
			if(strpos($v,'://')===false)
			{
				if($v==basename($v))
					$v=$a['options']['path'].'/'.$v;
				elseif(dirname($v)!=$a['options']['path'])
				{
					unset($a['value'][$k]);
					continue;
				}
				$v=Eleanor::FormatPath($v);
				if($delete)
					Files::Delete($v);
				else
				{
					$del=$temp.basename($v);
					if(is_file($v) and rename($v,$del))
						$sess['deleted'][]=$del;
				}
			}
			else
				unset($a['value'][$k]);

		if(!isset($a['value']['preview']) and isset($a['value'][0]) and $a['options']['preview'])
		{
			$preview=substr_replace($a['value'][0],$a['prevsuff'],strrpos($a['value'][0],'.'),0);
			Files::Delete($preview);
		}

		if(is_string($sess['value']))
			return$sess['value'];
		if(!$sess['value'])
			return'';

		$path=($a['options']['path'] ? Eleanor::FormatPath($a['options']['path']).'/' : Eleanor::$root.Eleanor::$uploads).DIRECTORY_SEPARATOR;
		if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
		{
			if($Obj->throw)
				throw new EE(static::$Language['no_upload_path'],EE::ENV);
			else
				$Obj->errors[]='NO_UPLOAD_PATH';
			return;
		}

		if(is_callable($a['options']['filename']))
			$filename=call_user_func($a['options']['filename'],array('filename'=>basename($sess['value']['image']))+$a,$Obj);
		elseif($a['options']['filename_eval'])
		{
			ob_start();
			$func=create_function('$a,$Obj',$a['options']['filename_eval']);
			if($func===false)
			{
				$e=ob_get_contents();
				ob_end_clean();
				Eleanor::getInstance()->e_g_l=error_get_last();
				if($Obj->throw)
					throw new EE('Error in filename eval: <br />'.$e,EE::DEV);
				else
					$Obj->errors['ERROR_FILENAME']=$err;
			}
			$filename=$func(array('filename'=>basename($sess['value']['image']))+$a,$Obj);
			ob_end_clean();
		}
		else
			$filename=basename($sess['value']['image']);

		$r=$sess['moved']=array();
		$a['options']['path'].='/';

		$f=Eleanor::$root.$sess['value']['image'];
		$t=$path.$filename;
		if(is_file($f) and Files::Delete($t) and rename($f,$t))
		{
			$sess['moved']['image']=$t;
			$r['image']=$a['options']['path'].$filename;
		}

		if($a['options']['preview'])
		{
			$filename=substr_replace($filename,$a['options']['prevsuff'],strrpos($filename,'.'),0);
			$f=Eleanor::$root.$sess['value']['preview'];
			$t=$path.$filename;
			if(is_file($f) and Files::Delete($t) and rename($f,$t))
			{
				$sess['moved']['preview']=$t;
				$r['preview']=$a['options']['path'].$filename;
			}
		}
		return$a['options']['preview'] ? $r : reset($r);
	}

	/**
	 * Получение результата контрола
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Result($a,$Obj,$co)
	{
		$a['options']+=array('retempty'=>true,'onlyimage'=>true,'max_image_size'=>'0 0','alt'=>'');
		$a['options']['max_image_size']=explode(' ',$a['options']['max_image_size']);
		if(!$a['value'])
			return$a['options']['retempty'] ? null : '<span style="width:'.($a['options']['max_image_size'][0] ? $a['options']['max_image_size'][0] : '180').'px;height:'.($a['options']['max_image_size'][1] ? $a['options']['max_image_size'][1] : '145').'px;text-decoration:none;max-height:100%;max-width:100%;" class="screenblock"><b>'.static::$Language['noimage'].'</b></span>';
		if(is_array($a['value']))
		{
			$image=isset($a['value']['image']) ? $a['value']['image'] : '';
			$preview=isset($a['value']['preview']) ? $a['value']['preview'] : '';
		}
		else
			$image=$preview=$a['value'];

		if($a['options']['onlyimage'])
			return'<img style="border:1px solid #c9c7c3;max-width:'.($a['options']['max_image_size'][0]>0 ? $a['options']['max_image_size'][0] : '100%').';max-height:'.($a['options']['max_image_size'][1]>0 ? $a['options']['max_image_size'][1] : '100%').'" src="'.$image.'" alt="'.$a['options']['alt'].'" />';
		$GLOBALS['jscripts'][]='addons/colorbox/jquery.colorbox-min.js';
		$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';
		$u=uniqid();
		return'<a href="'.$image.'" id="img-'.$u.'"><img style="border:1px solid #c9c7c3;max-width:'.($a['options']['max_image_size'][0]>0 ? $a['options']['max_image_size'][0] : '100%').';max-height:'.($a['options']['max_image_size'][1]>0 ? $a['options']['max_image_size'][1] : '100%').'" src="'.$preview.'" alt="'.$a['options']['alt'].'" /></a><script type="text/javascript">//<![CDATA[
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

	/**
	 * Обработка Ajax запроса контрола
	 */
	public static function DoAjax()
	{
		$session=isset($_POST['session']) ? (string)$_POST['session'] : '';
		$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
		Eleanor::StartSession($session);
		if(!isset($_SESSION[__class__][$name]))
			return Error(static::$Language['session_lost']);
		$sess=$_SESSION[__class__][$name];

		switch(isset($_POST['do']) ? (string)$_POST['do'] : '')
		{
			case'write':
				if(!in_array('address',$sess['source']))
					return Error();
				$value=isset($_POST['image']) ? (string)$_POST['image'] : false;
				if($value and (strpos($value,'://')!==false or is_file($f=Eleanor::FormatPath($value))))
				{
					if($sess['types'] and !in_array(substr(strrchr($value,'.'),1),$sess['types']))
						return Error(sprintf(static::$Language['only_types'],join(', ',$sess['types'])));

					if(isset($f))
					{
						if(!$sizes=@getimagesize($f))
							return Error(static::$Language['not_image']);
						list($w,$h)=$sizes;
						if($sess['max_image_size'][0]>0 and $sess['max_image_size'][0]<$w)
							return Error(sprintf(static::$Language['bigger_w'],$sess['max_image_size'][0],$w));
						if($sess['max_image_size'][1]>0 and $sess['max_image_size'][1]<$h)
							return Error(sprintf(static::$Language['bigger_h'],$sess['max_image_size'][1],$h));
						if($sess['nosmaller'] and ($sess['max_image_size'][1]>0 and $sess['max_image_size'][1]>$h or $sess['max_image_size'][0]>0 and $sess['max_image_size'][0]>$w))
							return Error(sprintf(static::$Language['smaller'],$w,$h,$sess['max_image_size'][0] ? $sess['max_image_size'][0] : '&infin;',$sess['max_image_size'][1] ? $sess['max_image_size'][1] : '&infin;'));
					}
					$sess['value']=$value;
				}
				else
					$sess['value']='';
			break;
			case'delete':
				$sess['value']='';
			break;
			default:
				return Error();
		}
		$_SESSION[__class__][$name]=$sess;
		Result(true);
	}

	/**
	 * Обработка Upload запроса контрола
	 */
	public static function DoUpload()
	{
		$session=isset($_POST['session']) ? (string)$_POST['session'] : '';
		$name=isset($_POST['name']) ? (string)$_POST['name'] : '';

		Eleanor::StartSession($session);
		if(!isset($_SESSION[__class__][$name]))
			return Error('Session lost');

		$sess=$_SESSION[__class__][$name];

		if(!isset($_FILES['image']) or !is_uploaded_file($_FILES['image']['tmp_name']) or !in_array('upload',$sess['source']))
			return Error('No file');

		if(!$sizes=@getimagesize($_FILES['image']['tmp_name']))
			return Error(static::$Language['not_image']);
		list($w,$h)=$sizes;

		if($sess['nosmaller'] and ($sess['max_image_size'][1]>0 and $sess['max_image_size'][1]>$h or $sess['max_image_size'][0]>0 and $sess['max_image_size'][0]>$w))
			return Error(sprintf(static::$Language['smaller'],$w,$h,$sess['max_image_size'][0] ? $sess['max_image_size'][0] : '&infin;',$sess['max_image_size'][1] ? $sess['max_image_size'][1] : '&infin;'));

		$max_w=$sess['max_image_size'][0]>0 && $sess['max_image_size'][0]<$w;
		$max_h=$sess['max_image_size'][1]>0 && $sess['max_image_size'][1]<$h;
		if($sess['resize']=='d')
		{
			if($max_w)
				return Error(sprintf(static::$Language['bigger_w'],$sess['max_image_size'][0],$w));
			if($max_h)
				return Error(sprintf(static::$Language['bigger_h'],$sess['max_image_size'][1],$h));
		}

		$to=Eleanor::$uploads.'/temp/';
		if(!is_dir(Eleanor::$root.DIRECTORY_SEPARATOR.$to))
			Files::MkDir(Eleanor::$root.DIRECTORY_SEPARATOR.$to);

		$to.=uniqid().strrchr($_FILES['image']['name'],'.');
		$ufn=Eleanor::$root.$to;
		if(!move_uploaded_file($_FILES['image']['tmp_name'],$ufn))
			return Error('File not loaded');

		try
		{
			if($max_w or $max_h)
				Image::Preview(
					$ufn,
					array(
						'width'=>$sess['max_image_size'][0],
						'height'=>$sess['max_image_size'][1],
						'cut_first'=>false,
						'cut_last'=>$sess['resize']!='b',
						'first'=>$sess['resize'],
						'newname'=>$ufn,
					)
				);
			$sess['value']=array(
				'image'=>$to,
				'preview'=>$sess['preview'] ? Eleanor::$uploads.'/temp/'.basename(Image::Preview($ufn,array('suffix'=>$sess['prevsuff'])+(is_array($sess['preview']) ? $sess['preview'] : array()))) : null
			);
		}
		catch(EE$E)
		{
			return Error($E->getMessage());
		}
		$_SESSION[__class__][$name]=$sess;

		Result($sess['preview'] ? array('file'=>$sess['value']['image'],'preview'=>$sess['value']['preview']) : array('file'=>$sess['value']['image']));
	}
}
ControlUploadImage::$Language=new Language;
ControlUploadImage::$Language->queue[]='uploadimage-*.php';