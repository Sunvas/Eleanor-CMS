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
			'uploadfile',#Группа контрола
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
			$writed=$a['value'] && strpos($a['value'],'://')!==false;
		if($a['value'] and !$writed and basename($a['value'])==$a['value'])
			$a['value']=rtrim($a['options']['path'],'/\\').DIRECTORY_SEPARATOR.$a['value'];
		$uploaded=$a['value'] && !$writed && is_file(Eleanor::FormatPath($a['value'])) && dirname($a['value'])==($a['options']['path'] ? trim($a['options']['path'],'/\\') : Eleanor::$uploads);

		return Eleanor::$Template->ControlUploadFile('control',$uploaded,$writed,$a['value'],$a['controlname'],$a['options']);
	}

	/**
	 * Сохранение контрола
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Save($a,$Obj)
	{
		$a['options']+=array(
			'types'=>array(),
			'path'=>'',
			'filename_eval'=>null,
			'filename'=>null,
		);

		$a+=array(
			'value'=>'',
		);

		$writed=$a['value'] && strpos($a['value'],'://')!==false;
		$single=$writed ? false : basename($a['value'])==$a['value'];#Значение подано без каталога

		$full=$a['value'] && !$writed ? Eleanor::FormatPath($a['value'],$single ? $a['options']['path'] : '') : false;
		$uploaded=$full && is_file($full) && ($single or dirname($a['value'])==($a['options']['path'] ? trim($a['options']['path'],'/\\') : Eleanor::$uploads));


		if($uploaded and $Obj->GetPostVal(array_merge($a['name'],array('delete')),false))
		{
			Files::Delete($full);
			$a['value']='';
		}

		$a['name']=(array)$a['name'];
		if($Obj->GetPostVal(array_merge($a['name'],array('type')),'w')=='w' and $text=$Obj->GetPostVal(array_merge($a['name'],array('text')),false))
		{
			if($a['options']['types'] and preg_match('#\.('.join('|',$a['options']['types']).')$#i',$text)==0)
			{
				if($Obj->throw)
					throw new EE(sprintf(static::$Language['error_ext'],join(', ',$a['options']['types'])),EE::USER);
				$Obj->errors[__class__]=sprintf(static::$Language['error_ext'],join(', ',$a['options']['types']));
				return;
 			}
 			if($uploaded and $a['value'])
 				Files::Delete($full);
 			return$text;
		}

		$Obj->POST=$_FILES;
		$tmp=$Obj->GetPostVal(array_merge(array('tmp_name'),$a['name'],array('file')),false);
		$name=$Obj->GetPostVal(array_merge(array('name'),$a['name'],array('file')),false);
		$Obj->POST=null;
		if(!$tmp or !$name or !is_uploaded_file($tmp))
			return$a['value'];

		$path=$a['options']['path'] ? Eleanor::FormatPath($a['options']['path']).'/' : Eleanor::$root.Eleanor::$uploads.'/';
		if(!is_dir($path) and !Files::MkDir($path) or !is_writeable($path))
		{
			if($Obj->throw)
				throw new EE(static::$Language['no_upload_path'],EE::ENV);
			$Obj->errors[__class__]=static::$Language['no_upload_path'];
			return;
		}

		if($a['options']['types'] and preg_match('#\.('.join('|',$a['options']['types']).')$#i',$name)==0)
		{
			if($Obj->throw)
				throw new EE(sprintf(static::$Language['error_ext'],join(', ',$a['options']['types'])),EE::USER);
			$Obj->errors[__class__]=sprintf(static::$Language['error_ext'],join(', ',$a['options']['types']));
			return;
		}

		if(is_callable($a['options']['filename']))
			$filename=call_user_func($a['options']['filename'],array('filename'=>$name)+$a,$Obj);
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
			$filename=$f($a,$Obj);
			ob_end_clean();
		}
		else
			$filename=$name;

		if(!$a['options']['path'])
			$a['options']['path']=Eleanor::$uploads;

		if($uploaded and $a['value'])
			Files::Delete($full);

		$fullfile=$path.$filename;
		if(is_file($fullfile))
			Files::Delete($fullfile);

		if(move_uploaded_file($tmp,$fullfile))
		{
			if(substr($a['options']['path'],-1)!='/')
				$a['options']['path'].='/';
			$a['options']['path'].=$filename;
			static::$bypost[ $Obj->GenName($a['name']) ]=$a['options']['path'];
			return$a['options']['path'];
		}
		return'';
	}

	/**
	 * Получение результата контрола
	 * @param array $a Опции контрола
	 * @param ControlsManager $Obj
	 */
	public static function Result($a,$Obj,$co)
	{
		return Eleanor::$Template->ControlUploadFile('result',$a['value']);
	}
}
ControlUploadFile::$Language=new Language;
ControlUploadFile::$Language->queue[]='uploadfile-*.php';