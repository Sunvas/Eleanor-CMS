<?php
namespace CMS;

use CMS\Classes\Paginator,
	Eleanor\Classes\L10n;

/** Dashboard of unit "static pages"
 * @var Classes\UriDashboard $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

/** @const name of config file */
const CONFIG='config';

/** Obtaining rights of site team member
 * @returns array [] */
function Rights():array
{
	$config=Config(CONFIG,__DIR__.DIRECTORY_SEPARATOR);
	$P=new Permissions(CMS::$P->groups,$config);

	return[$P->create,$P->delete];
}

/** Settings page
 * @param Classes\UriDashboard $Uri
 * @return array|string */
function Settings(Classes\UriDashboard$Uri):array|string
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
		$ok=$storage && \file_put_contents($dir.CONFIG.'.json',\json_encode($storage + Config(CONFIG,$dir),JSON));

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

	#Groups with access to dashboard
	$groups=(function(){
		$multi=L10NS!==null;

		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `title` FROM `groups` WHERE FIND_IN_SET('team', `roles`)>0
SQL);
		while($a=$R->fetch_assoc())
		{
			$a['id']=(int)$a['id'];

			if($multi)
			{
				$tmp=\json_decode($a['title'],true);
				$a['title']=L10n::Item($tmp,'#'.$a['id']);
			}

			yield $a;
		}
	})();

	return(CMS::$T)('settings',\compact('config','groups'));
}

/** List of static pages */
function ListOfItems(Classes\UriDashboard$Uri,bool$is_admin):array|string
{
	$multi=L10NS!==null;
	$page=$pp=null;
	$where=$params=[];

	#Filter by id, title and slug
	$id=(int)($_GET['id'] ?? 0);
	$slug=\is_string($_GET['slug'] ?? 0) ? trim($_GET['slug'],'%') : '';
	$title=\is_string($_GET['title'] ?? 0) ? trim($_GET['title'],'%') : '';

	if($id>0)
		$where[]='`id`='.$id;

	if($slug!='')
		if($multi)
		{
			//ToDo!
		}
		else
		{
			$where[]='`slug` LIKE ?';
			$params[]="%{$slug}%";
		}

	if($title!='')
		if($multi)
		{
			//ToDo!
		}
		else
		{
			$where[]='`title` LIKE ?';
			$params[]="%{$title}%";
		}

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
		[$sort,$order,$limit]=Paginator::SortOrderLimit($total,['id','slug','title'],false,$page,$pp);
	}catch(\OutOfBoundsException){
		$Uri->amp=false;
		Redirect($Uri);
	}

	if($params)
		$R=CMS::$Db->Execute(<<<SQL
SELECT `id`, `status`, `slug`, `title`, `modified`
FROM `static`
{$where}
ORDER BY `{$sort}`{$order}
{$limit}
SQL, $params);
	else
		$R=CMS::$Db->Query(<<<SQL
SELECT `id`, `status`, `slug`, `title`, `modified`
FROM `static`
{$where}
ORDER BY `{$sort}`{$order}
{$limit}
SQL);

	$items=(function()use($R,$Uri){
		while($a=$R->fetch_assoc())
		{
			$a['id']=(int)$a['id'];

			$a['userspace']='#';//ToDo! Link to the userspace

			yield $a;
		}
	})();

	[$can_create,$can_delete]=$is_admin ? [true,true] : Rights();

	return (CMS::$T)('items',\compact('items','total','sort','pp','can_create','can_delete')+['desc'=>(bool)$order]);
}

function Item(int$id,bool$is_admin):array|string
{
	//ToDo! Roadmap: static pages, upload files
	return 'item page';
}

#Assigning folder with templates
if(!CMS::$json)
	CMS::$T->queue[]=ROOT.'dashboard/'.$this->name;

$is_admin=\in_array('admin',CMS::$P->roles);

if(isset($_GET['item']))
	return Item((int)$_GET['item'],$is_admin);

return match($_GET['zone'] ?? ''){
	'settings'=>$is_admin ? Settings($Uri) : Halt(),
	''=>ListOfItems($Uri,$is_admin),
	default=>Halt()
};

