<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Classes\E,
	Eleanor\Classes\Files,

	CMS\Classes\Paginator;

/** Admin of unit "static pages"
 * @var Classes\Uri4AdminPanel $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

const
	/** @const name of config file */
	CONFIG='config',

	/** @const name for file uploading */
	ATTACH='attach';

/** Obtaining rights of site team member
 * @return array */
function Rights():array
{
	$config=Config(CONFIG,__DIR__.DIRECTORY_SEPARATOR);
	$P=new Permissions(CMS::$P->groups,$config);

	return[$P->create,$P->delete];
}

/** Settings page
 * @param Classes\Uri4AdminPanel $Uri
 * @return array|string */
function Settings(Classes\Uri4AdminPanel$Uri):array|string
{
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		$storage=[];

		#Arrays of int
		foreach(['create','delete'] as $f)
			if(\is_array($_POST[$f] ?? 0) && \array_all($_POST[$f],fn($t)=>\is_int($t)))
				$storage[$f]=$_POST[$f];

		$dir=__DIR__.DIRECTORY_SEPARATOR;
		$ok=$storage && \file_put_contents($dir.CONFIG.'.json',\json_encode($storage + Config(CONFIG,$dir),JSON),\LOCK_EX);

		if($ok)
			return[
				'ok'=>true
			];

		return[
			'ok'=>false,
			'error'=>$storage ? 'WRITE_ERROR' : 'NOTHING_TO_STORE'
		];
	}

	$config=Config(CONFIG,__DIR__.DIRECTORY_SEPARATOR);

	# Groups with access to admin panel
	$groups=(function(){
		$multi=L10NS!==null;
		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `title` FROM `groups` WHERE FIND_IN_SET('team', `roles`)>0
SQL);

		foreach($R as $a)
		{
			$a['id']=(int)$a['id'];

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			yield $a;
		}

		$R->free();
	})();

	return(CMS::$T)('settings',\compact('config','groups'));
}

/** List of static pages */
function ListOfItems(Classes\Uri4AdminPanel$Uri,bool$is_root):array|string
{
	#L10n related
	$multi=L10NS!==null;
	$l10n=$multi && \in_array($_GET['lang'] ?? 0,L10NS) ? $_GET['lang'] : L10N;

	#Query related
	$page=$pp=null;
	$where=$params=[];

	#Filter by id, slug, title and lang
	$id=(int)($_GET['id'] ?? 0);
	$slug=\is_string($_GET['slug'] ?? 0) ? trim($_GET['slug'],'%') : '';
	$title=\is_string($_GET['title'] ?? 0) ? trim($_GET['title'],'%') : '';

	if($id>0)
		$where[]='`id`='.$id;

	if($slug!='')
	{
		$where[]=$multi ? "`slug_{$l10n}` LIKE ?" : '`slug` LIKE ?';
		$params[]="%{$slug}%";
	}

	if($title!='')
	{
		$where[]=$multi ? "`title_{$l10n}` LIKE ?" : '`title` LIKE ?';
		$params[]="%{$title}%";
	}

	if($multi)
		$where[]="FIND_IN_SET('{$l10n}',`l10ns`)>0";

	$where=$where ? 'WHERE '.join(' AND ',$where) : '';

	if(isset($_GET['total']))
		$total=(int)$_GET['total'];
	else
	{
		if($params)
			$R=CMS::$Db->Execute(<<<SQL
SELECT COUNT(`id`) FROM `static` {$where}
SQL, $params);
		else
			$R=CMS::$Db->Query(<<<SQL
SELECT COUNT(`id`) FROM `static`
{$where}
SQL);

		$total=(int)$R->fetch_column();
	}

	try{
		[$sort,$order,$limit]=Paginator::SortOrderLimit($total,['id','slug','title','modified'],false,$page,$pp);

		if($multi and $order==='slug')
			$order='slug_'.$l10n;
	}catch(\OutOfBoundsException){
		$Uri->amp=false;
		Redirect($Uri);
	}

	$fields=$multi ? "`slug_{$l10n}` `slug`, `title_{$l10n}` `title`, `modified_{$l10n}` `modified`" : '`slug`, `title`, `modified`';

	if($params)
		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `status`, {$fields}
FROM `static`
{$where}
ORDER BY `{$sort}`{$order}
{$limit}
SQL, $params);
	else
		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `status`, {$fields}
FROM `static`
{$where}
ORDER BY `{$sort}`{$order}
{$limit}
SQL);

	$items=(function()use($R,$l10n){
		$USU=new Classes\Uri($l10n);# Userspace uri

		foreach($R as $a)
		{
			$a['id']=(int)$a['id'];
			$a['user-area']=$USU(\explode('/',$a['slug']));

			yield $a;
		}

		$R->free();
	})();

	[$can_create,$can_delete]=$is_root ? [true,true] : Rights();

	return (CMS::$T)('items',\compact('items','total','sort','pp','can_create','can_delete')+['desc'=>(bool)$order]);
}

/** Checking availability of desired slug
 * @param string $slug
 * @param int $id of static page
 * @param string $l10n code of language, should be already sanitized
 * @throws \Throwable
 * @return bool */
function CheckSlug(string$slug,int$id,string$l10n=''):bool
{
	$field=L10NS===null ? 'slug' : 'slug_'.$l10n;

	$R=CMS::$Db->Execute(<<<SQL
SELECT `{$field}` FROM `static` WHERE `{$field}`=? AND `id`!={$id} LIMIT 1
SQL ,[$slug]);

	return $R->num_rows>0;
}

/** Remove unused attached files from the folder of the static page
 * @param Abstracts\AdminPanel $Unit
 * @param int $id of edited static page
 * @param string $l10n
 * @return array */
function StoreItemFiles(Abstracts\AdminPanel$Unit,int$id,string$l10n=''):array
{
	#Files used in static page
	$key=$l10n ? 'files_'.$l10n : 'files';
	$in_use=\is_string($_POST[$key] ?? 0) && \json_validate($_POST[$key]) ? (array)\json_decode($_POST[$key],true) : [];

	#Folder of the static page and trash folder
	$dir=STATIC_PATH."uploads/{$Unit->name}/{$id}/";
	$trash=$GLOBALS['CMS']->{'daily-cleanup'}::UPLOADS_TRASH;
	$trash_exists=\is_dir($trash);

	#No files
	if(!\is_dir($dir))
		return[];

	$files=\array_diff(\scandir($dir),['.','..']);

	if($l10n)
		$files=\array_filter($files,fn($file)=>\str_contains($file,"-$l10n-"));

	$diff=\array_diff($files,$in_use);

	foreach($diff as $file)
	{
		$trash_file=$trash.$file;

		if($trash_exists and !\is_file($trash_file))
			\rename($dir.$file,$trash_file);
		else
			\unlink($dir.$file);
	}

	return \array_values(\array_intersect($files,$in_use));
}

/** Creating, Updating & Deleting static page
 * @param int $id of edited static page
 * @param bool $is_root
 * @param Abstracts\AdminPanel $Unit
 * @return array|string
 * @throws \Throwable */
function Item(int$id,bool$is_root,Abstracts\AdminPanel$Unit):array|string
{
	[$can_create,$can_delete]=$is_root ? [true,true] : Rights();

	if(CMS::$json)
	{
		#Static page removal
		if(CMS::$delete)
		{
			if(!$can_delete)
				return[
					'ok'=>false,
					'error'=>'INSUFFICIENT',
				];

			Files::Delete(STATIC_PATH."uploads/static/{$Unit->name}/{$id}/");
			$num=CMS::$Db->Delete('static','`id`='.$id);

			return[
				'ok'=>$num>0,
			];
		}

		if(CMS::$post)
		{
			if(!$can_create and $id<1)
				return[
					'ok'=>false,
					'error'=>'INSUFFICIENT',
				];

			#Uploading files
			if($id>0 and isset($_FILES[ATTACH]) and \is_uploaded_file($_FILES[ATTACH]['tmp_name']))
			{
				$dir=STATIC_PATH."uploads/{$Unit->name}/{$id}/";
				$ext=\strrchr($_FILES[ATTACH]['name'],'.');
				$hash=\hash_file('sha3-256',$_FILES[ATTACH]['tmp_name']);

				#New file name consists of file hash, l10n code (if used) and user id separated by -
				if(L10NS===null)
					$name=$hash.'-'.CMS::$A->current.$ext;
				else
				{
					$l10n=\in_array($_POST['l10n'] ?? 0,[L10N,...L10NS]) ? $_POST['l10n'] : L10N;
					$name=$hash."-$l10n-".CMS::$A->current.$ext;
				}

				$full=$dir.$name;

				#File exists
				if(\is_file($full))
				{
					\unlink($_FILES[ATTACH]['tmp_name']);

					return[
						'ok'=>true,
						'path'=>"static/uploads/{$Unit->name}/{$id}/",
						'filename'=>$name,
					];
				}

				#Creating folder
				if(!\is_dir($dir) and !\mkdir($dir,0755,true))
				{
					new E('Unable to create directory',E::SYSTEM,input:$dir)->Log();

					return[
						'ok'=>false,
						'error'=>'NO_DIR'
					];
				}

				#Moving file to destination
				if(\move_uploaded_file($_FILES[ATTACH]['tmp_name'],$full))
				{
					if(\preg_match('#\.(jpe?g|png|svg|gif)$#i',$ext)===1)
						$name=Classes\Image::Optimize($dir,$name);

					return[
						'ok'=>true,
						'path'=>"static/uploads/{$Unit->name}/{$id}/",
						'filename'=>$name,
					];
				}

				return[
					'ok'=>false,
					'error'=>'UNABLE_TO_MOVE'
				];
			}

			//Uploading image by URL
			if(count($_POST)===1 and isset($_POST[ATTACH]))
			{
				//ToDo!
				return[
					'ok'=>false,
					'error'=>'CURRENTLY_NOT_SUPPORTED'
				];
			}

			$data=[];

			if(\is_string($_POST['status'] ?? 0))
				$data['status']=$_POST['status'];

			if(L10NS===null)
			{
				foreach(['slug','title','description','content_source'] as $f)
					if(\is_string($_POST[$f] ?? 0))
						$data[$f]=$_POST[$f];

				//Check availability of slug
				if(isset($data['slug']))
					if($data['slug']==='')
						$data['slug']=null;
					elseif(CheckSlug($data['slug'],$id))
						return[
							'ok'=>false,
							'error'=>'SLUG_EXISTS'
						];

				//Check content source
				if(isset($data['content_source']) and !\json_validate($data['content_source']))
					return[
						'ok'=>false,
						'error'=>'CONTENT_SOURCE_FORMAT'
					];

				$files=StoreItemFiles($Unit,$id);
				$data['files']=\json_encode($files,JSON);
			}
			elseif(\is_string($_POST['l10ns'] ?? 0))
			{
				$mono=$_POST['l10ns']==='';
				$l10ns=$mono ? [] : \explode(',',$_POST['l10ns']);

				//Stack of system languages
				$stack=$mono ? [L10N] : [L10N,...L10NS];

				if(!$mono and !array_intersect($l10ns,$stack))
					return[
						'ok'=>false,
						'error'=>'WEIRD_L10NS'
					];

				foreach(['slug','title','description','content_source'] as $f)
				{
					foreach($stack as $l10n)
					{
						$k=$f.'_'.$l10n;
						$p=$mono ? $f : $k;

						if(\is_string($_POST[$p] ?? 0))
							$data[$k]=$_POST[$p];
						elseif(!\in_array($l10n,$l10ns))
							$data[$k]=null;
					}

					#If static page in monolingual - other fields should be null (except slug)
					if($mono)
					{
						$nullify=$f!=='slug';

						if($nullify or isset($data[$f.'_'.L10N]))
							foreach(L10NS as $l10n)
								$data[$f.'_'.$l10n]=$nullify ? null : $data[$f.'_'.L10N];
					}
				}

				//Validation of content
				foreach($stack as $l10n)
				{
					$k='slug_'.$l10n;

					if(isset($data[$k]) and CheckSlug($data[$k],$id,$l10n))
						return[
							'ok'=>false,
							'error'=>'SLUG_EXISTS',
							'l10n'=>$l10n
						];

					$k='content_source_'.$l10n;

					if(isset($data[$k]))
					{
						if(!\json_validate($data[$k]))
							return[
								'ok'=>false,
								'error'=>'CONTENT_SOURCE_FORMAT',
								'l10n'=>$l10n
							];

						$files=StoreItemFiles($Unit,$id,$l10n);
						$data['files_'.$l10n]=\json_encode($files,JSON);
					}
				}

				//Set l10ns value
				if($data)
					$data['l10ns']=join(',',$l10ns);
			}

			if(!$data)
				return[
					'ok'=>false,
					'error'=>'EMPTY',
				];

			if($id>0)
			{
				$num=CMS::$Db->Update('static',$data,'`id`='.$id);

				return[
					'ok'=>true,
					'changed'=>$num>0
				];
			}

			$id=CMS::$Db->Insert('static',$data);

			return $id>0 ? ['ok'=>true,'id'=>$id] : ['ok'=>false,'error'=>'DB'];
		}

		if(\is_string($_GET['check_slug'] ?? 0))
		{
			if(CheckSlug($_GET['check_slug'],$id,\in_array($_GET['l10n'] ?? 0,L10NS ?? []) ? $_GET['l10n'] : L10N))
				return[
					'ok'=>true
				];

			return[
				'ok'=>false,
				'error'=>'SLUG_EXISTS'
			];
		}

		//Loading L10N version of content
		if($id and \is_string($_GET['lang'] ?? 0) and L10NS!==null)
		{
			$l10n=\in_array($_GET['lang'],L10NS) ? $_GET['lang'] : L10N;

			$R=CMS::$Db->Query(<<<SQL
SELECT `slug_{$l10n}` `slug`, `title_{$l10n}` `title`, `description_{$l10n}` `description`, `content_source_{$l10n}` `content_source`
FROM `static`
WHERE `id`={$id}
LIMIT 1
SQL );

			if($item=SingleFetch($R))
			{
				$item['content_source']=\json_validate($item['content_source'] ?? '') ? \json_decode($item['content_source'],true) : [];

				return[
					'ok'=>true,
					'item'=>$item,
				];
			}

			return[
				'ok'=>false,
				'error'=>'NOT_FOUND',
			];
		}

		return[
			'ok'=>true,
		];
	}

	if(L10NS===null)
		$fields='`slug`, `title`, `content_source`, `description`';
	else
	{
		$R=CMS::$Db->Query(<<<SQL
SELECT `l10ns` FROM `static` WHERE `id`={$id} LIMIT 1
SQL );
		if($R->num_rows<1)
			Halt();

		$l10ns=$R->fetch_column();

		if($l10ns)
		{
			//Page is multilingual
			$l10ns=\explode(',',$l10ns);
			$stack=\array_intersect([L10N,...L10NS],$l10ns);
			$l10n=\in_array($_GET['lang'] ?? 0,$stack) ? $_GET['lang'] : \array_first($l10ns);
		}
		else
			$l10n=L10N;

		$fields="`l10ns`, `slug_{$l10n}` `slug`, `title_{$l10n}` `title`, `content_source_{$l10n}` `content_source`, `description_{$l10n}` `description`";
	}

	$R=CMS::$Db->Query(<<<SQL
SELECT `status`, {$fields}
FROM `static`
WHERE `id`={$id}
LIMIT 1
SQL );

	if(!$item=SingleFetch($R))
		Halt();

	$item['content_source']=\json_validate($item['content_source'] ?? '') ? \json_decode($item['content_source'],true) : [];

	return (CMS::$T)('item',\compact('item','can_delete'));
}

#Assigning folder with templates
if(!CMS::$json)
	CMS::$T[]=CMS.'admin-panel/'.$this->name;

$is_root=\in_array('root',CMS::$P->roles);

if(isset($_GET['item']))
	return Item((int)$_GET['item'],$is_root,$this);

return match($_GET['zone'] ?? ''){
	'settings'=>$is_root ? Settings($Uri) : Halt(),
	''=>ListOfItems($Uri,$is_root),
	default=>Halt()
};

