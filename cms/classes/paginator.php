<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

/** Pagination helper */
class Paginator extends \Eleanor\Basic
{
	/** Get the number of items per page
	 * @param int $def Default number of items per page
	 * @param int $min Minimum amount of items per page
	 * @param int $max Maximum amountof items per page
	 * @return int */
	static function PerPage(int$def=25,int$min=5,int$max=500):int
	{
		$pp=(int)($_GET['pp'] ?? 0);
		$cpp=(int)($_COOKIE['per-page'] ?? 0);

		if($pp>=$min and $pp<=$max)
		{
			if($pp!=$cpp)
				\CMS\SetCookie('per-page',(string)$pp);

			return $pp;
		}

		return ($cpp>=$min && $cpp<=$max) ? $cpp : $def;
	}

	/** Get the [sort field, mysql ORDER direction (DESC or empty), mysql LIMIT, offset] for ascending (!) pagination
	 * @param int $total Total items
	 * @param array $sorting list of possible order fields (first value is the default)
	 * @param bool $desc Default order direction: true for descending, false for ascending (F goes before T)
	 * @param ?int $page Page number
	 * @param ?int $pp Items per page
	 * @return array
	 * @throws \OutOfBoundsException */
	static function SortOrderLimit(int$total,array$sorting,bool$desc=true,?int$page=null,?int&$pp=null):array
	{
		$pp??=static::PerPage();
		$page??=(int)($_GET['page'] ?? 1);
		$pages=$total>0 ? \ceil($total/$pp) : 1;

		if($page<1 or $page>$pages)
			throw new \OutOfBoundsException('Page number must be between 1 and '.$pages);

		$offset=($page-1)*$pp;

		if(\is_string($_GET['sort'] ?? 0))
		{
			if(!\in_array($_GET['sort'],$sorting,true))
				throw new \OutOfBoundsException('Sort values must be one of '.join(', ',$sorting));

			$sort=$_GET['sort'];
			$desc=false;//When user clicks on column, he expects that column to be sorted by asc
		}
		else
			$sort=\reset($sorting);

		$order=match($_GET['order'] ?? 0){
			'asc'=>'',
			'desc'=>' DESC',
			default=>$desc ? ' DESC' : ''
		};

		return[$sort,$order,' LIMIT '.($offset==0 ? '' : $offset.',').$pp,$offset];
	}
}

#Not necessary here, since class name equals filename
return Paginator::class;