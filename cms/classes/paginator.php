<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

/** Pagination utilities */
class Paginator extends \Eleanor\Basic
{
	/** Get the number of items per page
	 * @param int $def Default number of items per page
	 * @param int $min Minimum number of items per page
	 * @param int $max Maximum number of items per page
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

	/** Get sorting and pagination values for forward pagination: [sort field, SQL ORDER direction (DESC or empty), SQL LIMIT, offset]
	 * @param int $total Total number of items
	 * @param string[] $sorting Non-empty list of allowed sort fields; the first value is used by default
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
			$desc=false;# When a user clicks a column, ascending order is expected first
		}
		else
			$sort=\array_first($sorting);

		$order=match($_GET['order'] ?? 0){
			'asc'=>'',
			'desc'=>' DESC',
			default=>$desc ? ' DESC' : ''
		};

		return[$sort,$order,' LIMIT '.($offset==0 ? '' : $offset.',').$pp,$offset];
	}
}

# Not required here because class name matches filename
return Paginator::class;