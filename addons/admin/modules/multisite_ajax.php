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
switch($event)
{
	case'checkdb':
		$data=isset($_POST['data']) ? (array)$_POST['data'] : array();
		$p=isset($data['prefix']) ? (string)$data['prefix'] : '';
		if(isset($data['host'],$data['user'],$data['pass'],$data['db']))
			try
			{
				$Db=new Db($data);
			}
			catch(EE$E)
			{
				return Result('connect');
			}
		else
		{
			if($p==P)
				return Result('prefix');
			$Db=Eleanor::$Db;
		}
		if(strpos($p,'`.`')!==false)
			list($db,$p)=explode('`.`',$p,2);
		else
			$db=false;
		$R=$Db->Query('SHOW TABLES'.($db ? ' FROM `'.$db.'`' : '').' LIKE \''.$Db->Escape($p,false).'multisite_jump\'');
		if($R->num_rows>0)
			Result(false);
		else
			Result('db');
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}