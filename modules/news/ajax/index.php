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
$mc=include$Eleanor->module['path'].'config.php';
Eleanor::LoadOptions($mc['opts']);
if(isset($_GET['do']))
{
	$d=(string)$_GET['do'];
	switch($d)
	{
		case'opensearch':#http://usabili.ru/news/2011/03/04/opensearch.html
			BeAs('user');
			$q=isset($_GET['q']) ? (string)$_GET['q'] : false;
			$items=$dates=$urls=$cats=array();
			if($q)
			{
				$Eleanor->Categories->Init($mc['c']);
				$q=htmlspecialchars(CHARSET=='utf-8' ? $q : mb_convert_encoding($q,CHARSET,'utf-8'),ELENT,CHARSET,false);
				$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`ldate`,`lcats`,`title` FROM `'.$mc['tl'].'` WHERE `title` LIKE \''.Eleanor::$Db->Escape($q,false).'%\' AND `lstatus`=1 AND `language`IN(\'\',\''.Language::$main.'\') LIMIT 5');
				while($a=$R->fetch_assoc())
				{
					$a['_cat']=$a['lcats'] && $Eleanor->Url->furl ? (int)ltrim($a['lcats'],',') : false;
					if($a['_cat'] and $Eleanor->Url->furl and !isset($cats[$a['_cat']]) and isset($Eleanor->Categories->dump[$a['_cat']]))
						$cats[$a['_cat']]=$Eleanor->Categories->GetUri($a['_cat']);

					$items[]=addcslashes($a['title'],"\n\r\t\"\\");
					$dates[]=addcslashes(Eleanor::$Language->Date($a['ldate'],'fdt'),"\n\r\t\"\\");

					$u=array('u'=>array($a['uri'],'id'=>$a['id']));
					$urls[]=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($a['_cat'] && isset($cats[$a['_cat']]) ? $cats[$a['_cat']]+$u : $u);
				}
			}
			Eleanor::$content_type='text/plain';
			Start();
			echo'["'.addcslashes($q,"\n\r\t\"\\").'",['.($items ? '"'.join('","',$items).'"' : '').'],['.($dates ? '"'.join('","',$dates).'"' : '').'],['.($urls ? '"'.join('","',$urls).'"' : '').']]';
		break;
		case'searchsuggesions':
			$q=isset($_GET['query']) ? htmlspecialchars((string)$_GET['query'],ELENT,CHARSET,false) : false;
			$items=array();
			if($q)
			{
				$R=Eleanor::$Db->Query('SELECT `uri`,`title`,`ldate` FROM `'.$mc['tl'].'` WHERE `title` LIKE \''.Eleanor::$Db->Escape($q,false).'%\' AND `lstatus`=1 AND `language`IN(\'\',\''.Language::$main.'\') LIMIT 5');
				while($a=$R->fetch_assoc())
					$items[]=addcslashes($a['title'],"\n\r\t\"\\");
			}
			Eleanor::$content_type='text/plain';
			Start();
			echo'{query:"'.addcslashes($q,"\n\r\t\"\\").'",suggestions:['.($items ? '"'.join('","',$items).'"' : '').']}';
	}
}
else
{
	$ev=isset($_POST['event']) ? (string)$_POST['event'] : '';
	switch($ev)
	{
		case'tags':
			$q=isset($_POST['query']) ? Url::Decode($_POST['query']) : '';
			$l=isset($_POST['lang']) ? Url::Decode($_POST['lang']) : '';
			$s=array();
			$R=Eleanor::$Db->Query('SELECT `name` FROM `'.$mc['tt'].'` WHERE'.($l && isset(Eleanor::$langs[$l]) ? '`language` IN (\'\',\''.$l.'\') AND' : '').' `name` LIKE \''.Eleanor::$Db->Escape($q,false).'%\' ORDER BY `name` ASC LIMIT 50');
			while($t=$R->fetch_row())
				$s[]=addcslashes($t[0],"\n\r\t\"\\");
			Eleanor::$content_type='application/json';
			Start();
			echo'{query:"'.$q.'",suggestions:['.($s ? '"'.join('","',$s).'"' : '').'],data:[]}';
		break;
		case'archive':
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
			Eleanor::SetCookie($mc['n'].'-archive',$y.'-'.$m);
			include$Eleanor->module['path'].'block_archive_funcs.php';
			$lang=Eleanor::$Language->Load($Eleanor->module['path'].'blocks-*.php',false);
			$days=ArchiveDays($y,$m,$mc,$Eleanor->module['name']);
			Result(array('month'=>$days['m'],'year'=>$days['y'],'archive'=>Eleanor::$Template->BlockArchive($days,$lang,$Eleanor->module['name'],true)));
		break;
		case'rating':
			if(!Eleanor::$vars['publ_rating'])
				return Error();
			BeAs('user');
			Eleanor::$Template->queue[]=$mc['usertpl'];
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			$R=Eleanor::$Db->Query('SELECT `r_total`,`r_average`,`r_sum` FROM `'.$mc['t'].'` WHERE `id`='.$id.(Eleanor::$Permissions->IsAdmin() ? '' : ' AND `status`=1').' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return Error();

			$uid=(int)Eleanor::$Login->GetUserValue('id');
			$can=!Eleanor::$vars['publ_mark_users'] && Eleanor::$vars['publ_remark'] || $uid;

			if($can)
			{
				$TCH=new TimeCheck($Eleanor->module['id'],false,$uid);
				$can=!$TCH->Check($id);
			}

			$mark=isset($_POST['mark']) ? (int)$_POST['mark'] : 0;
			$marks=range(Eleanor::$vars['publ_lowmark'],Eleanor::$vars['publ_highmark']);
			if(false!==$z=array_search(0,$marks))
				unset($marks[$z]);

			if($can and in_array($mark,$marks))
			{
				$a['r_average']=Rating::AddMark($a['r_total'],$a['r_average'],$mark);
				$a['r_total']++;
				$a['r_sum']+=$mark;

				Eleanor::$Db->Update($mc['t'],array('r_total'=>$a['r_total'],'r_average'=>$a['r_average'],'r_sum'=>$a['r_sum']),'`id`='.$id.' LIMIT 1');
				Eleanor::$Db->Update($mc['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
				$TCH->Add($id,$mark,Eleanor::$vars['publ_mark_users'],Eleanor::$vars['publ_remark'].'d');
				Result(Eleanor::$Template->Rating($id,false,$a['r_total'],$a['r_average'],$a['r_sum'],$marks,false));
			}
			else
				Error();
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

			$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`text`,`lcats` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id`='.$id.$where.' LIMIT 1');
			if(!$a=$R->fetch_assoc() or $a['text']=='')
				return Error();
			Result(OwnBB::Parse($a['text']));
		break;
		case'voting':
			BeAs('user');
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			$uid=(int)Eleanor::$Login->GetUserValue('id');
			$R=Eleanor::$Db->Query('SELECT `voting` FROM `'.$mc['t'].'` WHERE `id`='.$id.(Eleanor::$Permissions->IsAdmin() ? '' : ' AND (`status`=1'.($uid==0 ? '' : ' OR `author_id`='.$uid).')').' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return Error();
			$V=new Voting_Ajax($a['voting']);
			$V->mid=$Eleanor->module['id'];
			if($V->Process())
				Eleanor::$Db->Update($mc['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
		break;
		case'comments':
			$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
			$R=Eleanor::$Db->Query('SELECT `id`,`cats`,`uri` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id`='.$id.' AND `language`IN(\'\',\''.Language::$main.'\') AND `status`=1 LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return Error();
			BeAs('user');

			$Eleanor->Categories->Init($mc['c']);
			$cat=$a['cats'] && $Eleanor->Url->furl ? $Eleanor->Categories->GetUri((int)ltrim($a['cats'],',')) : false;
			$u=array('u'=>array($a['uri'],'id'=>$a['id']));
			$data=isset($_POST['comments']) ? (array)$_POST['comments'] : array();
			$Eleanor->Comments_ajax->baseurl=array('module'=>$Eleanor->module['name'])+($cat ? $cat+$u : $u);
			if($r=$Eleanor->Comments_ajax->Process($data,$id))
				switch($r['event'])
				{
					case'post':
						if($Eleanor->Comments->rights['post']==1 and !$r['merged'])
							Eleanor::$Db->Update($mc['t'],array('!comments'=>'`comments`+1'),'`id`='.$id.' LIMIT 1');
					case'save':
						Eleanor::$Db->Update($mc['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
					break;
					case'delete':
						if($r['deleted'])
						{
							Eleanor::$Db->Update($mc['t'],array('!comments'=>'IF(`comments`>'.$r['deleted'].',`comments`-'.$r['deleted'].',0)'),'`id`='.$id.' LIMIT 1');
							Eleanor::$Db->Update($mc['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
						}
					break;
					case'moderate':
						if($r['activated'])
						{
							Eleanor::$Db->Update($mc['t'],array('!comments'=>'GREATEST(0,`comments`'.($r['activated']>0 ? '+'.$r['activated'] : $r['activated']).')'),'`id`='.$id.' LIMIT 1');
							Eleanor::$Db->Update($mc['tl'],array('!last_mod'=>'NOW()'),'`id`='.$id.' LIMIT 1');
						}
				}
		break;
		default:
			Error(Eleanor::$Language['main']['unknown_event']);
	}
}