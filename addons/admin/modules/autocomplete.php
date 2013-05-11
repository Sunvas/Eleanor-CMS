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
if(!defined('CMS'))die;

function DoFilter(&$files,$filter)
{
	switch($filter)
	{
		case'onlydir':
			foreach($files as $k=>&$f)
				if(substr($f,-1)!=DIRECTORY_SEPARATOR)
					unset($files[$k]);
		break;
		case'module-image':
			foreach($files as $k=>&$f)
				if(preg_match('#.(jpe?g|gif|png|bmp)$#',$f)>0 or substr($f,-1)==DIRECTORY_SEPARATOR)
					$f=preg_replace('#\-(big|small).(jpe?g|gif|png|bmp)$#','-*.\2',$f);
				else
					unset($files[$k]);
			$files=array_unique($files);
		break;
		case'blocks-files':
			foreach($files as $k=>&$f)
				if(strpos($f,'.config.')!==false)
					unset($files[$k]);
		break;
		case'types':
			$types=empty($_POST['types']) ? array() : explode(',',$_POST['types']);
			foreach($types as &$v)
				$v=preg_quote($v);
			foreach($files as $k=>&$f)
				if(preg_match('#.('.join('|',$types).')$#',$f)==0 and substr($f,-1)!=DIRECTORY_SEPARATOR)
					unset($files[$k]);
	}

}

$goal=isset($_GET['goal']) ? (string)$_GET['goal'] : '';
$query=isset($_GET['query']) ? trim($_GET['query'],'\//') : '';

switch($goal)
{
	case'users':
		$items=array();
		if($query)
		{
			$R=Eleanor::$UsersDb->Query('SELECT `id`,`name` FROM `'.USERS_TABLE.'` WHERE `name` LIKE \''.Eleanor::$UsersDb->Escape($query,false).'%\' LIMIT 100');
			while($a=$R->fetch_assoc())
				$items[$a['id']]=addcslashes($a['name'],"\n\r\t\"\\");
		}
		Start();
		echo'{query:"'.addcslashes($query,"\n\r\t\"\\").'",suggestions:['.($items ? '"'.join('","',$items).'"],data:["'.join('","',array_keys($items)).'"' : '').']}';
	break;
	default:
		$filter=isset($_GET['filter']) ? (string)$_GET['filter'] : false;
		$cut=Eleanor::$root;
		$path=isset($_GET['path']) ? trim((string)$_GET['path'],'\//') : '';

		if($path)
		{
			$cut.='/'.$path;
			if(strpos($query,$path)===0)
				$path='';
		}

		$path=str_replace('..','',$path);
		$path=preg_replace('#[^a-z0-9\-_\.\\\\/]+#i','',ltrim($path.DIRECTORY_SEPARATOR.$query,'/\\')).'*';

		if($items=glob(Eleanor::$root.$path,GLOB_MARK))
		{
			if($filter)
				DoFilter($items,$filter);
			foreach($items as $k=>$v)
			{
				$v=str_replace('\\','/',$v);
				$v=addslashes(substr($v,strlen($cut)));
				$items[$k]=$v;
			}
		}
		Start();
		echo'{query:"'.addcslashes($query,"\n\r\t\"\\").'",suggestions:['.($items ? '"'.join('","',$items).'"' : '').']}';
}