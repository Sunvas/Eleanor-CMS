<?php
namespace CMS;

/** Dashboard of the blog demo unit
 * @var Classes\UriDashboard $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

function Blogs():array|string
{
	if(CMS::$json)
		return[
			'ok'=>false
		];

	return(CMS::$T)('blogs');
}

function Star():array|string
{
	if(CMS::$json)
		return[
			'ok'=>false
		];

	return(CMS::$T)('star',
		demo_date:date('Y-m-d H:i:s'),
		demo_time:time()
	);
}

if(!CMS::$json)
	CMS::$T->queue[]=ROOT.'dashboard/'.$this->name;

$is_admin=\in_array('admin',CMS::$P->roles);

return match($_GET['zone'] ?? ''){
	'star'=>$is_admin ? Star() : Halt(),
	''=>Blogs(),
	default=>Halt()
};