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
Eleanor::$Template->queue[]='Sitemap';
switch($event)
{
	case'loadmsetts':
		$ids=isset($_POST['mids']) ? (array)$_POST['mids'] : array();
		$r=array();
		if($ids)
		{
			$C=new Controls;
			$R=Eleanor::$Db->Query('SELECT `id`,`title_l`,`descr_l`,`path`,`api` FROM `'.P.'modules` WHERE `id`'.Eleanor::$Db->In($ids));
			while($a=$R->fetch_assoc())
			{
				$api=Eleanor::FormatPath($a['api'],$a['path']);
				$class='Api'.basename(dirname($api));
				do
				{
					if(class_exists($class,false))
						break;
					if(is_file($api))
					{
						include$api;
						if(class_exists($class,false))
							break;
					}
					continue 2;
				}while(false);
				if(!method_exists($class,'SitemapGenerate'))
					continue;
				$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';

				if(method_exists($class,'SitemapConfigure'))
				{
					$a['descr_l']=$a['descr_l'] ? Eleanor::FilterLangValues((array)unserialize($a['descr_l'])) : '';

					$Api=new$class;
					$conf=$Api->SitemapConfigure($p=false);
					$C->arrname=array('module'.$a['id']);
					$sett=array();
					$error=false;
					try
					{
						$sett=$C->DisplayControls($conf);
					}
					catch(EE$E)
					{
						$error=$E->getMessage();
					}

					$r[$a['id']]=Eleanor::$Template->GetSettings(array(
						'id'=>$a['id'],
						't'=>$a['title_l'],
						'd'=>$a['descr_l'],
						'c'=>$conf,
						's'=>$sett,
						'e'=>$error,
					));
				}
			}
		}
		Result($r);
	break;
	case'progress':
		$ids=isset($_POST['ids']) ? (array)$_POST['ids'] : array();
		if(!$ids)
			return Error();
		$res=array();
		$R=Eleanor::$Db->Query('SELECT `s`.`id`,`s`.`taskid`,`s`.`already`,`s`.`total`,`s`.`status`,`t`.`free` FROM `'.P.'sitemaps` `s` INNER JOIN `'.P.'tasks` `t` ON `t`.`id`=`s`.`taskid` WHERE `s`.`id`'.Eleanor::$Db->In($ids));
		while($a=$R->fetch_assoc())
			$res[$a['id']]=array(
				'done'=>(bool)$a['free'],
				'percent'=>$a['total']>0 ? round($a['already']/$a['total']*100) : 0,
				'val'=>$a['already'],
				'total'=>$a['total'],
			);
		Result($res ? $res : false);
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}