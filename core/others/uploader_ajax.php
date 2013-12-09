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

class Uploader_Ajax extends Uploader
{
	/**
	 * Осуществление Ajax запроса
	 */
	public function Process()
	{
		$uniq=isset($_POST['uniq']) ? (string)$_POST['uniq'] : '';
		if(isset($_POST['session']))
			Eleanor::StartSession((string)$_POST['session']);
		$lang=Eleanor::$Language['uploader'];
		$path=isset($_POST['path']) ? $_POST['path'] : '';
		if(!$this->LoadOptions($uniq) or !isset($_POST['goal']) or $this->uid!==Eleanor::$Login->GetUserValue('id'))
			return Error($lang['lost_session']);

		switch($_POST['goal'])
		{
			case'new':
				$folder=isset($_POST['folder']) ? (string)$_POST['folder'] : '';
				if($folder=='')
					return Error();
				if(self::EF($folder))
					return Error($lang['error_name']);
				$path=$this->GetPath($path).Files::Windows($folder);
				if(!is_dir($path) and !Files::MkDir($path))
					return Error($lang['error_folder']);
				Result(true);
			break;
			case'content':
				$dest=isset($_POST['dest']) ? (string)$_POST['dest'] : '';
				$page=isset($_POST['page']) ? (int)$_POST['page'] : 1;
				if($page<1)
					$page=1;
				$path=$this->GetPath($path,$dest);
				$result=$previews=$dirs=array();
				$files=glob($path.'/*',GLOB_MARK | GLOB_NOSORT);
				if(!$files)
					$files=array();
				$cnt=count($files);
				foreach($files as $k=>&$v)
				{
					$v=Files::Windows($v,true);
					if(substr($v,-1)==DIRECTORY_SEPARATOR)
					{
						$dirs[]=basename($v);
						unset($files[$k]);
					}
					else
						$files[$k]=basename($v);
				}
				natsort($dirs);
				natsort($files);
				#Превьюшки...
				if($this->preview and empty($_POST['showpreviews']))
				{
					$oldk=-1;
					foreach($files as $k=>&$v)
						if($oldk>=0 and $v==substr_replace($files[$oldk],$this->prevsuff,strrpos($files[$oldk],'.'),0))
						{
							$previews[]=$files[$oldk];
							unset($files[$k]);
							$cnt--;
						}
						else
							$oldk=$k;
				}
				if($this->pp<$cnt and $page<=ceil($cnt/$this->pp))
				{
					$cnt_dir=count($dirs);
					$offset=($page-1)*$this->pp;
					if(($offset+$this->pp)<=$cnt_dir)
					{
						$dirs=array_slice($dirs,$offset,$this->pp);
						$files=array();
					}
					elseif($offset<$cnt_dir)
					{
						$dirs=array_slice($dirs,$offset,$this->pp);
						$files=array_slice($files,0,$this->pp-count($dirs));
					}
					else
					{
						$dirs=array();
						$files=array_slice($files,$offset-$cnt_dir,$this->pp);
					}
					$result['pages']=Eleanor::$Template->UplPages($cnt,$this->pp,$page,$uniq);
					$result['page']=$page;
				}
				else
				{
					$result['pages']='';
					$result['page']=1;
				}

				$short=substr($path,strlen($this->allow_walk ? $this->pathlimit : $this->path));
				$short=Files::Windows(rtrim(str_replace(DIRECTORY_SEPARATOR,'/',$short),'/'),true);

				$fshort=substr($path,strlen(Eleanor::$root));
				$fshort=Files::Windows(str_replace(DIRECTORY_SEPARATOR,'/',$fshort),true);
				if(!$this->allow_delete)
					$this->buttons_item['folder_delete']=$this->buttons_item['folder_file']=false;
				foreach($files as &$entry)
				{
					$type=strpos($entry,'.')===false ? '' : strtolower(pathinfo($entry,PATHINFO_EXTENSION));
					$image=false;
					$ico=$type ? file_exists(Eleanor::$root.'images/uploader/file_types/'.$type.'.png') : false;
					if(in_array($type,array('jpeg','jpg','png','bmp','gif','ico')))
					{
						if(!$ico)
							$type='type_image';
						$image=true;
					}
					elseif(!$ico)
						$type='type_file';
					$entry=array(
						'file'=>$entry,
						'edit'=>in_array($type,$this->editable),
						'date'=>filemtime($path.$entry),
						'size'=>filesize($path.$entry),
						'image'=>$image,
						'type'=>$type,
					);
				}
				$max_upload=Files::SizeToBytes(ini_get('upload_max_filesize'));

				if($this->max_size!==true or $this->max_files>0)
					list($cursize,$cur_cnt)=$this->FilesSize($path);

				$result['upload']=true;
				if($this->max_size===true)
					$result['info']=sprintf($lang['maxu_unl'],Files::BytesToSize($max_upload));
				else
				{
					$result['upload']&=($freesize=$this->max_size-$cursize)>0;
					if($freesize<0)
						$freesize=0;
					$result['info']=sprintf($lang['maxu_lim'],Files::BytesToSize(min($max_upload,$freesize)),Files::BytesToSize($freesize));
				}
				if($this->max_files>0)
				{
					$cur_cnt=$this->max_files-$cur_cnt;
					$result['info'].=sprintf($lang['lim_files'],$cur_cnt<0 ? 0 : $cur_cnt);
					$result['upload']&=$cur_cnt>0;
				}
				$result+=array(
					'content'=>Eleanor::$Template->UplContent($this->buttons_item,$short,$fshort,$dirs,$files,$previews,$this->prevsuff),
					'path'=>Files::ShortPath($this->path,$path),
					'realpath'=>$fshort,
					'upload_limit'=>$max_upload,
				);
				Result($result);
			break;
			case'rename':
				$path=$this->GetPath($path);
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';
				$to=isset($_POST['to']) ? trim((string)$_POST['to'],'. ') : '';

				do
				{
					if($what=='' or $to=='' or $to==$what or self::EF($to))
						break;
					$type=strtolower(pathinfo($what,PATHINFO_EXTENSION));
					$new_type=strtolower(pathinfo($to,PATHINFO_EXTENSION));
					if($this->types and !in_array($new_type,$this->types))
						break;
					$to=$path.$to;
					$path.=$what;
					if(!file_exists($path) or file_exists($to))
						break;
					if(in_array($type,$this->preview) and in_array($new_type,$this->preview) and is_file($path))
					{
						$pq=preg_quote($this->prevsuff,'#');
						$prev=substr_replace($path,$this->prevsuff,strrpos($path,'.'),0);
						if(is_file($prev))
							rename($prev,substr_replace($to,$this->prevsuff,strrpos($to,'.'),0));
						elseif(preg_match('#'.$pq.'\.[a-z0-9_]+$#',$path)>0)
							rename(preg_replace('#'.$pq.'(\.[a-z0-9_]+)$#','\1',$path),preg_replace('#'.$pq.'(\.[a-z0-9_]+)$#','\1',$to));
					}
					if(rename($path,$to))
						return Result('ok');
				}while(false);
				Error($lang['error_rename']);
			break;
			case'delete':
				do
				{
					$what=isset($_POST['what']) ? (string)$_POST['what'] : '';
					if(!$this->allow_delete or !$what)
						break;
					$p=dirname($what)=='.' ? $this->GetPath($path,$what) : $this->GetPath($what);
					if($p==$this->path)
						break;
					$type=mb_strtolower(pathinfo($p,PATHINFO_EXTENSION));
					if(in_array($type,$this->preview) and is_file($p))
					{
						$pq=preg_quote($this->prevsuff,'#');
						$preview=substr_replace($p,$this->prevsuff,strrpos($p,'.'),0);
						if(is_file($preview))
							Files::Delete($preview);
						elseif(preg_match('#'.$pq.'\.[a-z0-9_]+$#',$p)>0)
							Files::Delete(preg_replace('#'.$pq.'(\.[a-z0-9_]+)$#','\1',$p));
					}
					if(Files::Delete($p))
						return Result('ok');
				}while(false);
				Error($lang['error_del']);
			break;
			case'edit':
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';
				$type=strpos($what,'.')===false ? '' : strtolower(pathinfo($what,PATHINFO_EXTENSION));
				if(!in_array($type,$this->editable))
					return Error($lang['error_edit']);
				$p=$this->GetPath($path,$what);
				if($p==$this->path or !is_file($p))
					return Error();
				$E=new Editor;
				$E->type='codemirror';
				$E->ownbb=$E->smiles=false;
				Result(Eleanor::$Template->UplEditFile($what,$E->Area('text',file_get_contents($p),array('codemirror'=>array('type'=>$type))),Files::ShortPath($this->path,$p),false));
			break;
			case'save':
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';
				$c=isset($_POST['content']) ? (string)$_POST['content'] : '';
				if($this->max_size!==true)
				{
					list($cursize)=$this->FilesSize($path);
					$cursize+=strlen($c);
					if($cursize>$this->max_size)
						return Error();
				}
				if(self::EF(basename($what)))
					return Error($what);
				$path=$this->GetPath($path).Files::Windows($what);
				$type=strpos($what,'.')===false ? '' : strtolower(pathinfo($what,PATHINFO_EXTENSION));
				if(is_file($path) and in_array($type,$this->editable) and file_put_contents($path,$c)!==false)
					return Result('ok');
				Error();
			break;
			case'new-file':
				$f=isset($_POST['file']) ? (string)$_POST['file'] : '';
				if($f=='')
					return Error();
				if(self::EF($f))
					return Error($lang['error_name']);
				$type=strpos($f,'.')===false ? '' : strtolower(pathinfo($f,PATHINFO_EXTENSION));
				if(!in_array($type,$this->editable))
					return Error($lang['error_edit']);
				if($this->max_files>0)
				{
					list(,$cnt)=$this->FilesSize($path);
					if($this->max_files<$cnt+1)
						return Error();
				}
				$path=$this->GetPath($path).Files::Windows($f);
				if(is_file($path))
					return Error($lang['file_exists']);
				if(false===file_put_contents($path,''))
					return Error();
				$E=new Editor;
				$E->type='codemirror';
				$E->ownbb=$E->smiles=false;
				Result(Eleanor::$Template->UplEditFile($f,$E->Area('text','',array('codemirror'=>array('type'=>$type))),Files::ShortPath($this->path,$path),true));
			break;
		}
	}

	/**
	 * Проверка корректности имени файла или каталога
	 * @param string $f Имя файла или каталога для проверки
	 */
	protected static function EF($f)
	{
		return preg_match('~[\s#"\'\\\\/:*\?<>|%]+~',$f)>0;
	}
}