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
class AccountSettings
{
	public static function Handler()
	{
		$event=isset($_POST['event']) ? $_POST['event'] : '';
		switch($event)
		{
			case'galleries':
				$galleries=array();
				$gals=glob(Eleanor::$root.'images/avatars/*',GLOB_MARK | GLOB_ONLYDIR);
				foreach($gals as &$v)
				{
					$descr=$name=basename($v);
					$image=false;
					if(is_file($v.'config.ini'))
					{
						$a=parse_ini_file($v.'config.ini',true);
						if(isset($a['title']))
							$descr=Eleanor::FilterLangValues($a['title'],'',$name);
						if(isset($a['options']['cover']) and is_file($v.$a['options']['cover']))
							$image='images/avatars/'.$name.'/'.$a['options']['cover'];
					}
					if(!$image and $temp=glob($v.'*.{jpg,png,jpeg,bmp,gif}',GLOB_BRACE))
						$image='images/avatars/'.$name.'/'.basename($temp[0]);
					if($image)
						$galleries[]=array('n'=>$name,'i'=>$image,'d'=>$descr);
				}
				Result(Eleanor::$Template->Galleries($galleries));
			break;
			case'avatars':
				$gallery=isset($_POST['gallery']) ? (string)$_POST['gallery'] : false;
				$files=$gallery ? glob(Eleanor::$root.'images/avatars/'.$gallery.'/*.{jpg,png,jpeg,bmp,gif}',GLOB_BRACE) : false;
				if(!$files)
					return Error();

				foreach($files as &$v)
					$v=array('p'=>'images/avatars/'.$gallery.'/','f'=>basename($v));

				Result(Eleanor::$Template->Avatars($files));
		}
	}
}