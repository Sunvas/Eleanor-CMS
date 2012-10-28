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
global$Eleanor;
$Eleanor->module['config']=include($Eleanor->module['path'].'config.php');
Eleanor::LoadOptions($Eleanor->module['config']['opts']);
if(isset($_GET['do']))
{	$d=(string)$_GET['do'];
	switch($d)
	{		case'suggestions':
			$q=isset($_GET['query']) ? htmlspecialchars((string)$_GET['query'],ELENT,CHARSET,false) : false;
			$items=array();
			if($q)
			{				$R=Eleanor::$Db->Query('SELECT `title` FROM `'.$Eleanor->module['config']['tl'].'` WHERE MATCH(`title`,`text`) AGAINST ('.Eleanor::$Db->Escape($q).' IN BOOLEAN MODE) AND `lstatus`=1 LIMIT 5');
				while($a=$R->fetch_assoc())
					$items[]=addcslashes($a['title'],"\n\r\t\"\\");
			}
			Eleanor::$content_type='text/plain';
			Start();
			echo'{query:"'.addcslashes($q,"\n\r\t\"\\").'",suggestions:['.($items ? '"'.join('","',$items).'"' : '').']}';	}}
else
{
	$ev=isset($_POST['event']) ? (string)$_POST['event'] : '';
	switch($ev)
	{		case'tags':
			$q=isset($_POST['query']) ? Url::Decode($_POST['query']) : '';
			$l=isset($_POST['lang']) ? Url::Decode($_POST['lang']) : '';
			$s=array();
			$R=Eleanor::$Db->Query('SELECT `name` FROM `'.$Eleanor->module['config']['tt'].'` WHERE'.($l && isset(Eleanor::$langs[$l]) ? '`language` IN (\'\',\''.$l.'\') AND' : '').' `name` LIKE \''.Eleanor::$Db->Escape($q,false).'%\' ORDER BY `name` ASC LIMIT 50');
			while($t=$R->fetch_row())
				$s[]=addcslashes($t[0],"\n\r\t\"\\");
			Eleanor::$content_type='application/json';
			Start();
			echo'{query:"'.$q.'",suggestions:['.($s ? '"'.join('","',$s).'"' : '').'],data:[]}';
		break;		case'archive':
			BeAs('user');
			$m=isset($_POST['month']) ? (int)$_POST['month'] : idate('n');
			$y=isset($_POST['year']) ? (int)$_POST['year'] : idate('Y');
			if($y<1991 or $y>idate('Y')+1)
				$y=idate('Y');
			if($m<1)
			{
				$y--;
				$m=12;
			}
			if($m>12)
			{
				$y++;
				$m=1;
			}
			Eleanor::SetCookie($Eleanor->module['config']['n'].'-archive',$y.'-'.$m);
			include$Eleanor->module['path'].'block_archive_funcs.php';
			$lang=Eleanor::$Language->Load($Eleanor->module['path'].'lang_blocks-*.php',false);
			$days=ArchiveDays($y,$m,$Eleanor->module['config'],$Eleanor->module['name']);
			Result(array('month'=>$m,'year'=>$y,'archive'=>Eleanor::$Template->BlockArchive($days,$lang,$Eleanor->module['name'],true)));
		break;
		case'rating':
			if(!Eleanor::$vars['publ_rating'])
				return Error();
			BeAs('user');
			Eleanor::$Template->queue[]=$Eleanor->module['config']['usertpl'];
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			$R=Eleanor::$Db->Query('SELECT `id`,`r_total`,`r_average`,`r_sum` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.(Eleanor::$Permissions->IsAdmin() ? '' : ' AND `status`=1').' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return Error();
			$R=new Rating_Ajax;
			$R->table=$Eleanor->module['config']['t'];
			$R->mid=$Eleanor->module['id'];
			$R->tremark=Eleanor::$vars['publ_remark'].'d';
			$R->once=Eleanor::$vars['publ_mark_users'];
			$R->marks=range(Eleanor::$vars['publ_lowmark'],Eleanor::$vars['publ_highmark']);
			if(false!==$z=array_search(0,$R->marks))
				unset($R->marks[$z]);
			if($R->Process($id,$a))
				Eleanor::$Db->Update($Eleanor->module['config']['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
		break;
		case'getmore':
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			BeAs('user');

			if(Eleanor::$Permissions->IsAdmin())
				$where='';
			elseif(Eleanor::$Login->IsUser())
				$where=' AND (`lstatus`=1 OR `author_id`='.Eleanor::$Login->GetUserValue('id').')';
			else
				$where=' AND `lstatus`=1';

			$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`text`,`lcats` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id`='.$id.$where.' LIMIT 1');
			if(!$a=$R->fetch_assoc() or $a['text']=='')
				return Error();
			Result(OwnBB::Parse($a['text']));
		break;
		case'voting':
			BeAs('user');
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			$uid=(int)Eleanor::$Login->GetUserValue('id');
			$R=Eleanor::$Db->Query('SELECT `voting` FROM `'.$Eleanor->module['config']['t'].'` WHERE `id`='.$id.(Eleanor::$Permissions->IsAdmin() ? '' : ' AND (`status`=1'.($uid==0 ? '' : ' OR `author_id`='.$uid).')').' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return Error();
			$V=new Voting_Ajax($a['voting']);
			$V->mid=$Eleanor->module['id'];
			if($V->Process())
				Eleanor::$Db->Update($Eleanor->module['config']['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
		break;
		case'comments':
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			$R=Eleanor::$Db->Query('SELECT `id`,`cats`,`uri` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `id`='.$id.' AND `language`IN(\'\',\''.Language::$main.'\') AND `status`=1 LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return Error();
			BeAs('user');

			$Eleanor->Categories->Init($Eleanor->module['config']['c']);
			$cat=$a['cats'] && $Eleanor->Url->furl ? $Eleanor->Categories->GetUri((int)ltrim($a['cats'],',')) : false;
			$u=array('u'=>array($a['uri'],'nid'=>$a['id']));
			$data=isset($_POST['comments']) ? (array)$_POST['comments'] : array();
			$Eleanor->Comments_ajax->baseurl=array('module'=>$Eleanor->module['name'])+($cat ? $cat+$u : $u);
			if($r=$Eleanor->Comments_ajax->Process($data,$id))
				switch($r['event'])
				{
					case'post':
						if($Eleanor->Comments->rights['post']==1 and !$r['merged'])
							Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!comments'=>'`comments`+1'),'`id`='.$id.' LIMIT 1');
					case'save':
						Eleanor::$Db->Update($Eleanor->module['config']['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
					break;
					case'delete':
						if($r['deleted'])
						{
							Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!comments'=>'IF(`comments`>'.$r['deleted'].',`comments`-'.$r['deleted'].',0)'),'`id`='.$id.' LIMIT 1');
							Eleanor::$Db->Update($Eleanor->module['config']['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
						}
					break;
					case'moderate':
						if($r['activated'])
						{
							Eleanor::$Db->Update($Eleanor->module['config']['t'],array('!comments'=>'GREATEST(0,`comments`'.($r['activated']>0 ? '+'.$r['activated'] : $r['activated']).')'),'`id`='.$id.' LIMIT 1');
							Eleanor::$Db->Update($Eleanor->module['config']['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
						}
				}
		break;
		default:
			Error(Eleanor::$Language['main']['unknown_event']);
	}
}