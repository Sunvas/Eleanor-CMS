<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class Uploader_Upload extends Uploader
{
	/**
	 * Осуществление Upload запроса
	 */
	public function Process()
	{
		if(!isset($_POST['session']))
			return false;
		Eleanor::LoadOptions('site');
		Eleanor::StartSession((string)$_POST['session']);
		if(!$this->LoadOptions(isset($_POST['uniq']) ? (string)$_POST['uniq'] : '') or $this->max_size===false)
			return false;
		$fname=Url::Decode($_FILES[self::FILENAME]['name']);
		if(Eleanor::$vars['trans_uri'] and method_exists(Language::$main,'Translit'))
			$fname=call_user_func(array(Language::$main,'Translit'),$fname);
		#Заменяем все [ и ] потому что они криво вставляются в тег [attach]
		$fname=preg_replace('#[\s\'"%\]\[/\\\-]+#',Eleanor::$vars['url_rep_space'],$fname);
		$type=strpos($fname,'.')===false ? '' : pathinfo($fname,PATHINFO_EXTENSION);
		$path=$this->GetPath(isset($_POST['path']) ? Url::Decode((string)$_POST['path']) : '');

		if($this->max_size!==true or $this->max_files>0)
		{
			list($cursize,$cnt)=$this->FilesSize($path);
			$cursize+=$_FILES[self::FILENAME]['size'];
			if($this->max_size!==true and $cursize>$this->max_size or $this->max_files>0 and $this->max_files<++$cnt)
				return false;
		}
		$path.=Files::Windows($fname);
		if($this->types and !in_array($type,$this->types) or !move_uploaded_file($_FILES[self::FILENAME]['tmp_name'],$path))
			return false;

		if($this->vars['watermark_types'] and $this->vars['watermark'] and (!isset($this->watermark) and !empty($_POST['watermark']) or $this->watermark))
		{
			list($r,$g,$b,$s,$a)=explode(',',$this->vars['watermark_csa']);
			Image::WaterMark(
				$path,
				array(
					'types'=>explode(',',Strings::CleanForExplode($this->vars['watermark_types'])),
					'alpha'=>(int)$this->vars['watermark_alpha'],
					'top'=>(int)$this->vars['watermark_top'],
					'left'=>(int)$this->vars['watermark_left'],

					#Если задана картинка - нарисуем картинку
					'image'=>$this->vars['watermark_image'] ? Eleanor::FormatPath($this->vars['watermark_image']) : '',

					#Если картинка - false, наприсуем текст
					'text'=>$this->vars['watermark_string'],
					'size'=>$s,
					'angle'=>$a,
					'r'=>$r,
					'g'=>$g,
					'b'=>$b,
				)
			);
		}

		if((!isset($this->previews) and !empty($_POST['dopreviews']) or $this->previews) and in_array(strtolower(pathinfo($path,PATHINFO_EXTENSION)),$this->preview))
			try
			{
				if($this->vars['thumb_width']==0)
					$first='h';
				elseif($this->vars['thumb_height']==0)
					$first='w';
				else
					$first=$this->vars['thumb_first'];

				Image::Preview(
					$path,
					array(
						'width'=>$this->vars['thumb_width'],
						'height'=>$this->vars['thumb_height'],
						'cut_first'=>in_array($this->vars['thumb_reducing'],array('cut','cutsmall')),
						'cut_last'=>in_array($this->vars['thumb_reducing'],array('cut','smallcut')),
						'first'=>$first,#Что будет уменьшаться первое: высота или ширина. w,h . Автоматически: b - по наибольше стороне, s - по наименьше стороне.
						'suffix'=>$this->prevsuff,
					)
				);
			}
			catch(EE$E){file_put_contents(Eleanor::$root.'tttt.txt',$E->getMessage());}
		Result('Ok');
		return true;
	}
}