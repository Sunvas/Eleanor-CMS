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
global$Eleanor;
$event=isset($_POST['event']) ? (string)$_POST['event'] : '';
Eleanor::$Template->queue[]='Smiles';
switch($event)
{
	case'setemotion':
		$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
		$em=isset($_POST['emotion']) ? (string)$_POST['emotion'] : false;
		$em=$em ? explode(',',$em) : array();
		$exemo=array();
		foreach($em as &$v)
		{
			$v=trim($v,', ');
			if($v=='')
				unset($em[$k]);
			else
			{
				$R=Eleanor::$Db->Query('SELECT `emotion` FROM `'.P.'smiles` WHERE `emotion`LIKE\'%,'.Eleanor::$Db->Escape($v,false).',%\' AND `id`!='.$id.' LIMIT 1');
				if($a=$R->fetch_assoc())
				{
					$a['emotion']=explode(',,',trim($a['emotion'],','));
					$exemo=array_merge($exemo,array_intersect($a['emotion'],$v));
				}
			}
		}
		if($exemo)
		{
			$lang=Eleanor::$Language->Load('addons/admin/langs/smiles-*.php',false);
			return Error($lang['emoexists']($exemo));
		}
		if(!$id or !$em)
			return Error();
		$R=Eleanor::$Db->Query('SELECT `emotion` FROM `'.P.'smiles` WHERE `id`='.$id.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return Error();
		$a['emotion']=explode(',,',trim($a['emotion'],','));
		if(count($a['emotion'])!=count($em) or array_diff($em,$a['emotion']))
		{
			Eleanor::$Db->Update(P.'smiles',array('emotion'=>','.join(',,',$em).','),'`id`='.$id.' LIMIT 1');
			Eleanor::$Cache->Obsolete('smiles');
		}
		Result(true);
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}