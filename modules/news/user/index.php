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

global$Eleanor,$title;
$Eleanor->module['config']=$mc=include($Eleanor->module['path'].'config.php');
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'user-*.php',$mc['n']);
$Eleanor->Categories->Init($mc['c']);
$Eleanor->module['etag']='';#Дополнение к ETAG
Eleanor::LoadOptions($mc['opts']);

$curls=array();
$puri=false;
if($Eleanor->Url->is_static)
{
	$str=$Eleanor->Url->GetEnding(array($Eleanor->Url->ending,$Eleanor->Url->delimiter),true);
	$_GET+=$Eleanor->Url->Parse($str ? array() : array('do'));
	if($str)
	{
		$curls=isset($_GET['']) ? (array)$_GET[''] : array();
		if($str==$Eleanor->Url->ending and !isset($_GET['page']))
			$puri=array_pop($curls);
	}
}
$cid=isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
$id=isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(isset($_GET['do']))
{	$d=(string)$_GET['do'];
	switch($d)
	{		case'tags':
			SetData();
			$title[]=$lang['tags'];
			$c=Eleanor::$Template->ShowAllTags();
			Start();
			echo$c;
		break;
		case'my':
			if(Eleanor::$vars['publ_add'])
			{
				$title[]=$lang['my'];
				if(Eleanor::$Login->IsUser())
				{
					$where='`author_id`='.Eleanor::$Login->GetUserValue('id');
					$cntf='author_id';
				}
				else
				{					$gn=GetGN();
					$where='`id`'.($gn ? Eleanor::$Db->In($gn) : '=0');					$cntf='id';
				}
				$R=Eleanor::$Db->Query('SELECT COUNT(`'.$cntf.'`) FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND '.$where);
					list($cnt)=$R->fetch_row();

				$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
				if($page<1)
					$page=1;
				$offset=($page-1)*$cnt;

				if($cnt and $offset>=$cnt)
					$offset=max(0,$cnt-Eleanor::$vars['publ_per_page']);
				$R=$cnt>0 ? Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_detail`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,IF(`text`=\'\',0,1) `_hastext`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND '.$where.' ORDER BY `ldate` DESC LIMIT '.$offset.', '.Eleanor::$vars['publ_per_page']) : false;
				$d=FormatList($R);
				if(!$d)
					return;
				$links=array(
					'first_page'=>$Eleanor->Url->Construct(array('do'=>'my'),true,''),
					'pages'=>function($n){ return$GLOBALS['Eleanor']->Url->Construct(array('do'=>'my',''=>array('page'=>$n)),true,''); },
				);
				$c=Eleanor::$Template->MyList($d,$cnt,$page,Eleanor::$vars['publ_per_page'],$links);
				Start();
				echo$c;
			}
			else
				Main();		break;
		case'add':
			if(Eleanor::$vars['publ_add'])
			{				include$Eleanor->module['path'].'user/addedit.php';
				if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
					Save(0);
				else
					AddEdit(0);
			}
			else
				Main();
		break;
		case'edit':
		case'delete':
			include$Eleanor->module['path'].'user/addedit.php';
			if($Eleanor->Url->is_static)
				$id=(isset($_GET['']) and is_array($_GET[''])) ? (int)reset($_GET['']) : false;
			else
				$id=isset($_GET['id']) ? (int)$_GET['id'] : false;
			$gn=Eleanor::$Login->IsUser() ? array() : GetGN();
			switch($d)
			{
				case'edit':
					if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
						Save((int)$id,$gn);
					else
						AddEdit((int)$id,array(),$gn);
				break;
				case'delete':
					$uid=(int)Eleanor::$Login->GetUserValue('id');
					$R=Eleanor::$Db->Query('SELECT `id`,`title`,`author_id`,`tags`,`voting` FROM `'.$mc['t'].'` LEFT JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
					if(!$a=$R->fetch_assoc() or !Eleanor::$our_query or !Eleanor::$Permissions->IsAdmin() and ($uid>0 and $a['author_id']!=$uid or $uid==0 and !in_array($a['id'],$gn)))
						return GoAway(true);
					if(isset($_POST['ok']))
					{
						if($a['voting'])
							$Eleanor->VotingManager->Delete($a['voting']);
						Files::Delete(Eleanor::$root.Eleanor::$uploads.DIRECTORY_SEPARATOR.$mc['n'].DIRECTORY_SEPARATOR.$id);
						RemoveTags($a['id']);
						Eleanor::$Db->Delete(P.'comments','`module`='.$Eleanor->module['id'].' AND `contid`='.$a['id']);
						Eleanor::$Db->Delete($mc['t'],'`id`='.$id.' LIMIT 1');
						Eleanor::$Db->Delete($mc['tl'],'`id`='.$id);
						if($uid)
							Eleanor::$Db->Delete(P.'drafts','`key`=\''.$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$id.'\' LIMIT 1');
						Eleanor::$Cache->Lib->DeleteByTag($mc['n']);
						$title[]=$lang['deleted'];
						SetData($Eleanor->module['config']['usercorrecttpl']);
						$c=Eleanor::$Template->DelSuccess($a,empty($_POST['back']) ? false : $_POST['back']);
					}
					else
					{
						$title[]=$lang['delc'];
						if(isset($_GET['noback']))
							$back='';
						else
							$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
						SetData($Eleanor->module['config']['usercorrecttpl']);
						$c=Eleanor::$Template->Delete($a,$back);
					}
					Start();
					echo$c;
			}
		break;
		case'search':
			SetData();
			if($Eleanor->Url->is_static)
				$md=(isset($_GET['']) and is_array($_GET[''])) ? reset($_GET['']) : false;
			else
				$md=isset($_GET['md']) ? (string)$_GET['md'] : false;
			$values=array(
				'text'=>'',
				'where'=>'tat',
				'tags'=>array(),
				'categs'=>array(),
				'sort'=>'date',
				'c'=>'and',
				't'=>'and',
			);
			$error='';
			$data=$cnt=$page=false;
			if($_SERVER['REQUEST_METHOD']=='POST')
				do
				{
					$T=new TimeCheck($Eleanor->module['id']);
					$values=array(
						'text'=>isset($_POST['text']) ? (string)Eleanor::$POST['text'] : '',
						'where'=>isset($_POST['where']) ? (string)$_POST['where'] : '',
						'tags'=>isset($_POST['tags']) ? (array)$_POST['tags'] : array(),
						'categs'=>isset($_POST['categs']) ? (array)$_POST['categs'] : array(),
						'sort'=>isset($_POST['sort']) ? (string)$_POST['sort'] : 'date',
						'c'=>isset($_POST['c']) && $_POST['c']=='or' ? 'or' : 'and',
						't'=>isset($_POST['t']) && $_POST['t']=='or' ? 'or' : 'and',
					);
					if($ch=$T->Check('search',false))
					{
						$error=$lang['search_limit'](Eleanor::$Permissions->SearchLimit(),$ch['_datets']-time());
						break;
					}
					if($values['text'] and mb_strlen($values['text'])<3)
					{
						$error=$lang['sym_limit'](3);
						break;
					}
					$seladd=$order='';
					$query=array();
					if($values['text'])
					{
						$dbtext=Eleanor::$Db->Escape($values['text'],false);
						switch($values['where'])
						{
							case't':
								$query['match']='MATCH(`title`) AGAINST (\''.$dbtext.'\' IN BOOLEAN MODE)';
							break;
							case'ta':
								$query['match']='MATCH(`title`,`announcement`) AGAINST (\''.$dbtext.'\' IN BOOLEAN MODE)';
							break;
							default:
								$query['match']='MATCH(`title`,`announcement`,`text`) AGAINST (\''.$dbtext.'\' IN BOOLEAN MODE)';
						}
						switch($values['sort'])
						{
							case'relevance':
								/*
								$seladd=', '.$query['match'].' `_rev`'
								$order='ORDER BY `_rev` DESC';
								*/
								$order='';
							break;
							default:
								$order=' ORDER BY `ldate` DESC';
						}
					}
					else
						$order=' ORDER BY `ldate` DESC';

					if($values['categs'])
					{
						sort($values['categs'],SORT_NUMERIC);
						$query['categs']='`lcats` '.($values['c']=='or' ? 'REGEXP(\',('.join('|',Eleanor::$Db->Escape($values['categs'],false)).'),\')' : 'LIKE \'%,'.join(',%,',Eleanor::$Db->Escape($values['categs'],false)).',%\'');
					}

					if($values['tags'])
					{
						sort($values['tags'],SORT_NUMERIC);
						$query['tags']='`tags` '.($values['t']=='or' ? 'REGEXP(\',('.join('|',Eleanor::$Db->Escape($values['tags'],false)).'),\')' : 'LIKE \'%,'.join(',%,',Eleanor::$Db->Escape($values['tags'],false)).',%\'');
						/*
							Как вариант. Может пригодится
							$query['tags']=$values['t']=='or' ? ' AND `id` IN (SELECT `id` FROM `'.$mc['rt'].'` WHERE `tag`'.Eleanor::$Db->In($values['tags']).' GROUP BY `id`)' : ' AND `id` IN (SELECT `id` FROM `'.$mc['rt'].'` WHERE `tag`'.Eleanor::$Db->In($values['tags']).' GROUP BY `id` HAVING COUNT(DISTINCT `tag`)='.count($values['tags']).')';
						*/
					}

					if(!(isset($query['match']) or isset($query['categs']) or isset($query['tags'])))
					{
						$error=$lang['notofind'];
						break;
					}
					$query=join(' AND ',$query);
					$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `lstatus`=1 AND `language` IN (\'\',\''.Language::$main.'\') AND '.$query);
					list($cnt)=$R->fetch_row();
					$cnt=(int)$cnt;

					if($cnt==0)
						break;

					if($cnt>Eleanor::$vars['publ_per_page'])
					{
						$T->Add('search','',true,Eleanor::$Permissions->SearchLimit());
						$query.=$order;
						Eleanor::StartSession($md);
						$_SESSION[$mc['n']]=array(
							'cnt'=>$cnt,
							'query'=>$query,
							'values'=>$values,
							'seladd'=>$seladd,
						);
						$Eleanor->Url->ending='';
						return GoAway(array('do'=>'search','md'=>session_id()));
					}
				}while(false);
			elseif($md)
				do
				{
					Eleanor::StartSession($md);
					if(!isset($_SESSION[$mc['n']]))
						break;
					extract($_SESSION[$mc['n']],EXTR_OVERWRITE);
				}while(false);

			if($cnt>0)
			{				$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
				if($page<1)
					$page=1;
				$offset=abs(($page-1)*Eleanor::$vars['publ_per_page']);

				if($cnt and $offset>=$cnt)
					$offset=max(0,$cnt-Eleanor::$vars['publ_per_page']);

				$R=Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_detail`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,IF(`text`=\'\',0,1) `_hastext`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting`'.$seladd.' FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 AND '.$query.' LIMIT '.$offset.', '.Eleanor::$vars['publ_per_page']);
				$data=FormatList($R,false,$values['text'] ? array('hl'=>array('hl'=>$values['text'])) : array());			}

			$tags=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`name` FROM `'.$mc['tt'].'` WHERE `language` IN (\'\',\''.Language::$main.'\') AND `cnt`>0 ORDER BY `name` ASC LIMIT 150');
			while($a=$R->fetch_assoc())
				$tags[$a['id']]=$a['name'];

			$links=array(
				'first_page'=>$Eleanor->Url->Prefix(''),
				'pages'=>function($n) use ($md){ return$GLOBALS['Eleanor']->Url->Construct(array('do'=>'search','md'=>$md,''=>array('page'=>$n)),true,''); },
			);

			$c=Eleanor::$Template->Search($values,$error,$tags,$data,$cnt,$page,Eleanor::$vars['publ_per_page'],$links);
			Start();
			echo$c;
		break;
		case'categories':
			if(!$Eleanor->Categories->dump)
				return GoAway();
			global$title;
			$title[]=$lang['categs'];
			SetData();
			$c=Eleanor::$Template->ShowCategories();
			Start();
			echo$c;
		break;
		case'draft':
			if(Eleanor::$Login->IsUser())
			{
				$n=isset($_POST['_draft']) ? (int)$_POST['_draft'] : 0;
				unset($_POST['_draft'],$_POST['back']);
				Eleanor::$Db->Replace(P.'drafts',array('key'=>$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$n,'value'=>serialize($_POST)));
			}
			Eleanor::$content_type='text/plain';
			Start('');
			echo'ok';
		break;
		default:
			if(preg_match('#^(\d{4})\D(\d{1,2})(?:\D(\d{1,2}))?$#',$d,$ma)>0)
			{				if(strtotime($d)>time())
					return GoAway(true);
				$title[]=sprintf($lang['for'],Eleanor::$Language->Date($d,strlen($d)>7 ? 'fd' : 'my',array('lowercase'=>true)));
				$R=Eleanor::$Db->Query('SELECT COUNT(`lstatus`) FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 AND IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) LIKE \''.$d.'%\'');
				list($cnt)=$R->fetch_row();

				$np=$cnt % Eleanor::$vars['publ_per_page'];
				$pages=max(ceil($cnt/Eleanor::$vars['publ_per_page'])-($np>0 ? 1 : 0),1);
				$page=isset($_GET['page']) ? (int)$_GET['page'] : $pages;
				$intpage=$pages - $page + 1;
				$offset=max(0,$intpage-1)*Eleanor::$vars['publ_per_page'];

				$limit=Eleanor::$vars['publ_per_page'];
				if($offset==0)
					$limit+=$np;
				else
					$offset+=$np;

				if($cnt and $offset>=$cnt)
					$offset=max(0,$cnt-$limit);
				$R=Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_detail`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,IF(`text`=\'\',0,1) `_hastext`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 AND IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) LIKE \''.$d.'%\' ORDER BY `ldate` DESC LIMIT '.$offset.', '.$limit);
				$data=FormatList($R);
				if(!$data)
					return;
				$links=array(
					'first_page'=>$Eleanor->Url->Prefix(''),
					'pages'=>function($n) use ($d){ return$GLOBALS['Eleanor']->Url->Construct(array('do'=>$d,''=>array('page'=>$n)),true,''); },
				);
				$c=Eleanor::$Template->DateList($d,$data,$cnt,-$page,$pages,Eleanor::$vars['publ_per_page'],$links);
				$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct(array('do'=>$d),true,'');
				Start();
				echo$c;			}
			else
				ExitPage();	}
}
elseif(isset($_GET['tag']))
{	$tag=(string)$_GET['tag'];
	$tag=htmlspecialchars($tag,ELENT,CHARSET,true);
	$R=Eleanor::$Db->Query('SELECT `id`,`name`,`cnt` FROM `'.$mc['tt'].'` WHERE `name`=\''.Eleanor::$Db->Escape($tag,false).'\' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
	if(!$tag=$R->fetch_assoc())
		return ExitPage();
	$title[]=sprintf($lang['wt'],$tag['name']);

	/*
		$R=Eleanor::$Db->Query('SELECT COUNT(`tag`) FROM `'.$mc['rt'].'` WHERE `tag`='.$tag['id']);
		list($cnt)=$R->fetch_row();

		if($cnt!=$tag['cnt'])
			Eleanor::$Db->Update($mc['tt'],array('cnt'=>$cnt),'`id`='.$tag['id'].' LIMIT 1');
	*/

	$np=$tag['cnt'] % Eleanor::$vars['publ_per_page'];
	$pages=max(ceil($tag['cnt']/Eleanor::$vars['publ_per_page'])-($np>0 ? 1 : 0),1);
	$page=isset($_GET['page']) ? (int)$_GET['page'] : $pages;
	$intpage=$pages - $page + 1;
	$offset=max(0,$intpage-1)*Eleanor::$vars['publ_per_page'];

	$limit=Eleanor::$vars['publ_per_page'];
	if($offset==0)
		$limit+=$np;
	else
		$offset+=$np;

	if($tag['cnt'] and $offset>=$tag['cnt'])
		$offset=max(0,$cnt-$limit);

	$R=Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_detail`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,IF(`text`=\'\',0,1) `_hastext`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `id` IN (SELECT `id` FROM `'.$mc['rt'].'` WHERE `tag`='.$tag['id'].') AND `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 ORDER BY `ldate` DESC LIMIT '.$offset.', '.$limit);
	$d=FormatList($R);
	if(!$d)
		return;
	$tnd=htmlspecialchars_decode($tag['name'],ELENT);
	$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct(array(array('tag'=>$tnd)));
	$links=array(
		'first_page'=>$Eleanor->Url->Construct(array(array('tag'=>$tnd))),
		'pages'=>function($n) use ($tnd){ return$GLOBALS['Eleanor']->Url->Construct(array(array('tag'=>$tnd,array('page'=>$n)))); },
	);
	$c=Eleanor::$Template->TagsList($tag,$d,$tag['cnt'],$page,$pages,Eleanor::$vars['publ_per_page'],$links);
	Start();
	echo$c;}
elseif($id or $puri)
{	$where=$id ? '`id`='.(int)$id : '`uri`=\''.Eleanor::$Db->Escape($puri,false).'\'';
	$uid=(int)Eleanor::$Login->GetUserValue('id');
	$gn=$uid==0 ? GetGN() : array();

	$R=Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_sokr`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,`text`,`meta_title`,`meta_descr`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND '.$where.' LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return ExitPage();
	$ed=Eleanor::$Permissions->IsAdmin() || ($uid>0 and $a['author_id']==$uid or $uid==0 and in_array($a['id'],$gn));
	if($a['status']!=1 and !$ed)
		return ExitPage();
	if($ed)
	{		$a['_aedit']=$Eleanor->Url->Construct(array('do'=>'edit','id'=>$a['id']),true,'');		$a['_adel']=$Eleanor->Url->Construct(array('do'=>'delete','id'=>$a['id']),true,'');
	}
	else
		$a['_aedit']=$a['_adel']=false;

	$Eleanor->module['ptags']=$a['tags']=$a['tags'] ? explode(',,',trim($a['tags'],',')) : array();
	$Eleanor->module['pid']=$a['id'];
	$a['_cat']=$a['cats'] ? (int)ltrim($a['cats'],',') : false;
	$u=array('u'=>array($a['uri'],'id'=>$a['id']));
	if($a['_cat'] and $Eleanor->Url->furl)
	{
		$cu=$Eleanor->Categories->GetUri($a['_cat']);
		if($cu)
			$u=$cu+$u;
	}
	if($cid or $curls or $id and $a['uri'])
	{
		$category=$Eleanor->Categories->GetCategory($cid ? $cid : $curls);
		if($category)
		{			$category['_a']=$Eleanor->Url->Construct($Eleanor->Categories->GetUri($category['id']),true,false);
			if($category['id']!=$a['_cat'])
			{
				$commu=$Eleanor->Comments->GET();
				foreach($commu as $k=>$v)
					$u[]=array($Eleanor->Comments->upref.$k=>$v);
				return GoAway($u);
			}
		}
	}
	else
		$category=false;
	if(!Eleanor::$is_bot and $a['status']==1)
	{
		#Не будем увеличивать счетчик просмотров от переходов по страницам новости
		$ref=getenv('HTTP_REFERER');
		$uu=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($u,true,'');
		$uu=htmlspecialchars_decode($uu,ELENT);
		if(strpos($ref,$uu)===false)
			Eleanor::$Db->Update($mc['t'],array('!reads'=>'`reads`+1'),'`id`='.$a['id'].' LIMIT 1');
	}

	if(Eleanor::$caching)
	{
		Eleanor::$last_mod=$a['last_mod'];
		$etag=Eleanor::$etag;
		$uid=Eleanor::$Login->GetUserValue('id');
		Eleanor::$etag=md5($uid.'-'.$a['id'].'-'.$mc['n'].$Eleanor->module['etag']);
		if(Eleanor::$modified and Eleanor::$last_mod and Eleanor::$last_mod<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
			return Start();
		else
			Eleanor::$modified=false;
	}
	SetData();

	if(Eleanor::$vars['publ_rating'] and $a['status']==1)
	{		$TCH=new TimeCheck($Eleanor->module['id'],false,$uid);
		$a['_canrate']=(!Eleanor::$vars['publ_mark_users'] and Eleanor::$vars['publ_remark'] or $uid>0) && !$TCH->Check($a['id']);
	}
	else
		$a['_canrate']=false;

	if($a['meta_title'])
		$title=$a['meta_title'];
	else
		$title[]=$a['title'];

	if($a['meta_descr'])
		$Eleanor->module['description']=$a['meta_descr'];

	$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($u);

	OwnBB::$opts['alt']=$a['title'];

	$a['announcement']=$a['show_sokr'] && $a['announcement'] ? OwnBB::Parse($a['announcement']) : false;
	if($a['text'])
		$a['text']=OwnBB::Parse($a['text']);

	#Поддержка соцсетей:
	$Lst=Eleanor::LoadListTemplate('headfoot')
		->og('title',$a['title'])
		->og('uri',$Eleanor->origurl)
		->og('locale',Eleanor::$langs[Language::$main]['d'])
		->og('site_name',Eleanor::$vars['site_name']);
	if($a['meta_descr'])
		$Lst->og('description',$a['meta_descr']);
	if(preg_match('#<img.+?src="([^"]+)"[^>]*>#',$a['text'],$m)>0)
		$Lst->og('image',strpos($m[1],'://')===false ? PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$m[1] : $m[1]);
	$GLOBALS['head']['og']=(string)$Lst;

	$a['_tags']=array();
	if($a['tags'])
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`name`,`cnt` FROM `'.$mc['tt'].'` WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id`'.Eleanor::$Db->In($a['tags']));
		while($temp=$R->fetch_assoc())
		{			$temp['_a']=$Eleanor->Url->Construct(array(array('tag'=>htmlspecialchars_decode($temp['name'],ELENT))));
			$a['_tags'][$temp['id']]=array_slice($temp,1);
		}
	}
	if($a['voting'])
	{
		$Eleanor->Voting=new Voting($a['voting']);
		$Eleanor->Voting->mid=$Eleanor->module['id'];
		$voting=$Eleanor->Voting->Show(array('module'=>$Eleanor->module['name'],'event'=>'voting','id'=>$a['id']));
	}
	else
		$voting=false;
	if(!$Eleanor->Comments->off)
		$Eleanor->Comments->baseurl=array('module'=>$Eleanor->module['name'])+$u;

	$hl=isset($_GET['hl']) && is_string($_GET['hl']) && preg_match('#^[a-z0-9'.constant(Language::$main.'::ALPHABET').' ]+$#i',$_GET['hl'])>0 ? preg_split('/\s+/',$_GET['hl']) : false;
	$c=Eleanor::$Template->Show($a,$category,$voting,$Eleanor->Comments->off ? '' : $Eleanor->Comments->Show($a['id']),$hl);
	Start();
	echo$c;}
elseif($cid or $curls)
{
	$category=$Eleanor->Categories->GetCategory($cid ? $cid : $curls);
	if(!$category)
		return ExitPage();
	$Lst=Eleanor::LoadListTemplate('headfoot');
	$head['rss']=$Lst->link(array(
		'rel'=>'alternate',
		'type'=>'application/rss+xml',
		'href'=>Eleanor::$services['rss']['file'].'?'.Url::Query(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name'],'c'=>$category['id']) : array('module'=>$Eleanor->module['name'],'c'=>$category['id'])),
		'title'=>sprintf($lang['from'],$category['title']),
	));
	if($category['meta_title'])
		$title=$category['meta_title'];
	else
		$title[]=$category['title'];

	if($category['meta_descr'])
		$Eleanor->module['description']=$category['meta_descr'];

	if(Eleanor::$vars['publ_catsubcat'])
	{
		$carr=array($category['id']);
		$p=$category['parents'].$category['id'].',';
		foreach($Eleanor->Categories->dump as $k=>&$v)
			if(strpos($v['parents'],$p)===0)
				$carr[]=$k;
		$cwhere=count($carr)>1 ? 'REGEXP (\',('.join('|',$carr).'),\')' : 'LIKE \'%,'.$category['id'].',%\'';
	}
	else
		$cwhere='LIKE \'%,'.$category['id'].',%\'';
	$R=Eleanor::$Db->Query('SELECT COUNT(`lstatus`) FROM `'.$mc['tl'].'` WHERE `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 AND `lcats` '.$cwhere.'');
	list($cnt)=$R->fetch_row();

	$np=$cnt % Eleanor::$vars['publ_per_page'];
	$pages=max(ceil($cnt/Eleanor::$vars['publ_per_page'])-($np>0 ? 1 : 0),1);
	$page=isset($_GET['page']) ? (int)$_GET['page'] : $pages;
	$intpage=$pages - $page + 1;
	$offset=max(0,$intpage-1)*Eleanor::$vars['publ_per_page'];

	$limit=Eleanor::$vars['publ_per_page'];
	if($offset==0)
		$limit+=$np;
	else
		$offset+=$np;

	if($cnt and $offset>=$cnt)
		$offset=max(0,$cnt-$limit);
	$R=Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_detail`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,IF(`text`=\'\',0,1) `_hastext`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 AND `lcats` '.$cwhere.' ORDER BY `ldate` DESC LIMIT '.$offset.', '.$limit);
	$d=FormatList($R);
	if(!$d)
		return;
	$cu=$Eleanor->Categories->GetUri($category['id']);
	$links=array(
		'first_page'=>$Eleanor->Url->Construct($cu,true,false),
		'pages'=>function($n) use ($cu){ return$GLOBALS['Eleanor']->Url->Construct($cu+array('page'=>array('page'=>$n)),true,''); },
	);

	$c=Eleanor::$Template->CategoryList($category,$d,$cnt,-$page,$pages,Eleanor::$vars['publ_per_page'],$links);
	$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($cu+array('page'=>array('page'=>$page==$pages ? false : $page)),true,false);

	#Поддержка соцсетей:
	$Lst=Eleanor::LoadListTemplate('headfoot')
		->og('title',$category['title'])
		->og('uri',$Eleanor->origurl)
		->og('locale',Eleanor::$langs[Language::$main]['d'])
		->og('site_name',Eleanor::$vars['site_name']);
	if($category['meta_descr'])
		$Lst->og('description',$category['meta_descr']);
	if($category['image'])
		$Lst->og('image',PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Categories->imgfolder.$category['image']);
	$GLOBALS['head']['og']=(string)$Lst;

	Start();
	echo$c;
}
else
	Main();

function Main()
{global$Eleanor,$title;
	$title[]=Eleanor::$Language[$Eleanor->module['config']['n']]['n'];

	$R=Eleanor::$Db->Query('SELECT COUNT(`lstatus`) FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `lstatus`=1 AND `language` IN (\'\',\''.Language::$main.'\')');
	list($cnt)=$R->fetch_row();

	$np=$cnt % Eleanor::$vars['publ_per_page'];
	$pages=max(ceil($cnt/Eleanor::$vars['publ_per_page'])-($np>0 ? 1 : 0),1);
	$page=isset($_GET['page']) ? (int)$_GET['page'] : $pages;
	$intpage=$pages - $page + 1;
	$offset=max(0,$intpage-1)*Eleanor::$vars['publ_per_page'];

	$limit=Eleanor::$vars['publ_per_page'];
	if($offset==0)
		$limit+=$np;
	else
		$offset+=$np;

	if($cnt and $offset>=$cnt)
		$offset=max(0,$cnt-$limit);

	$R=Eleanor::$Db->Query('SELECT `id`,`cats`,IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`) `date`,`author`,`author_id`,`show_detail`,`r_average`,`r_total`,`r_sum`,`status`,`reads`,`comments`,`tags`,`uri`,`title`,`announcement`,IF(`text`=\'\',0,1) `_hastext`,UNIX_TIMESTAMP(`last_mod`) `last_mod`,`voting` FROM `'.$Eleanor->module['config']['t'].'` INNER JOIN `'.$Eleanor->module['config']['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1 ORDER BY `ldate` DESC LIMIT '.$offset.', '.$limit);
	$d=FormatList($R,empty($Eleanor->module['general']));
	if(!$d)
		return;
	$links=array(
		'first_page'=>$Eleanor->Url->Prefix(false),
		'pages'=>function($n){ return$GLOBALS['Eleanor']->Url->Construct(array(array('page'=>$n))); },
	);
	$c=Eleanor::$Template->ShowList($d,$cnt,-$page,$pages,Eleanor::$vars['publ_per_page'],$links);
	$Eleanor->origurl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct(array('page'=>array('page'=>$page==$pages ? false : $page)));
	Start();
	echo$c;
}

function FormatList($R,$caching=true,$anurl=array())
{global$Eleanor;	$items=$cats=$tags=$cus=array();
	$lmod=0;
	$ids=',';
	$uid=(int)Eleanor::$Login->GetUserValue('id');
	if($R and $R->num_rows>0)
	{
		$isa=Eleanor::$Permissions->IsAdmin();
		$gn=$uid==0 ? GetGN() : array();

		while($a=$R->fetch_assoc())
		{			if($isa || ($uid>0 and $a['author_id']==$uid or $uid==0 and in_array($a['id'],$gn)))
			{
				$a['_aedit']=$Eleanor->Url->Construct(array('do'=>'edit','id'=>$a['id']),true,'');
				$a['_adel']=$Eleanor->Url->Construct(array('do'=>'delete','id'=>$a['id']),true,'');
			}
			else
				$a['_aedit']=$a['_adel']=false;

			$ids.=$a['id'].',';			if($a['last_mod']>$lmod)				$lmod=$a['last_mod'];

			$a['_cat']=$a['cats'] ? (int)ltrim($a['cats'],',') : false;
			if($a['_cat'] and !isset($cats[$a['_cat']]) and isset($Eleanor->Categories->dump[$a['_cat']]))
			{				$cus[$a['_cat']]=$Eleanor->Categories->GetUri($a['_cat']);
				$cats[$a['_cat']]=array(
					'_a'=>$Eleanor->Url->Construct($cus[$a['_cat']],true,false),
					't'=>$Eleanor->Categories->dump[$a['_cat']]['title'],
				);
			}

			$a['tags']=$a['tags'] ? explode(',,',trim($a['tags'],',')) : array();
			$tags=array_merge($tags,$a['tags']);

			if(Eleanor::$vars['publ_rating'])
				$v['_canrate']=false;
			$a['_readmore']=$a['show_detail'] || $a['_hastext'];

			OwnBB::$opts['alt']=$a['title'];
			if($a['announcement'])
				$a['announcement']=OwnBB::Parse($a['announcement']);

			$u=array('u'=>array($a['uri'],'id'=>$a['id']))+$anurl;
			$a['_url']=$Eleanor->Url->Construct($a['_cat'] && $Eleanor->Url->furl && isset($cus[$a['_cat']]) ? $cus[$a['_cat']]+$u : $u);
			$items[$a['id']]=array_slice($a,1);
		}
	}

	if($caching and Eleanor::$caching)
	{
		Eleanor::$last_mod=$lmod;
		$etag=Eleanor::$etag;
		Eleanor::$etag=md5($uid.$ids.$Eleanor->module['config']['n'].$Eleanor->module['etag']);
		if(Eleanor::$modified and Eleanor::$last_mod and Eleanor::$last_mod<=Eleanor::$modified and $etag and $etag==Eleanor::$etag)
			return Start();
		else
			Eleanor::$modified=false;
	}
	SetData();

	if(Eleanor::$vars['publ_rating'] and !Eleanor::$vars['publ_mark_details'])
	{		$TCH=new TimeCheck($Eleanor->module['id'],false,$uid);		$ch=$TCH->Check(array_keys($items));
		$guests=!Eleanor::$vars['publ_mark_users'];
		foreach($items as $k=>&$v)
			$v['_canrate']=($guests and Eleanor::$vars['publ_remark'] or $uid>0) && !isset($ch[$k]);
	}

	if($tags)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`name`,`cnt` FROM `'.$Eleanor->module['config']['tt'].'` WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id`'.Eleanor::$Db->In($tags));
		$tags=array();
		while($a=$R->fetch_assoc())
		{			$a['_url']=$Eleanor->Url->Construct(array(array('tag'=>htmlspecialchars_decode($a['name'],ELENT))));
			$tags[$a['id']]=array_slice($a,1);
		}
	}
	return compact('items','cats','tags');}

function GetGN()
{global$Eleanor;	$gn=Eleanor::GetCookie($Eleanor->module['config']['n'].'-gn');
	$gns=Eleanor::GetCookie($Eleanor->module['config']['n'].'-gns');

	if($gn and $gns and $gns===md5($gn.$Eleanor->module['config']['secret']))
		return explode(',',$gn);
	return array();}

function SetData($tpl=false)
{global$Eleanor,$head;
	$lang=Eleanor::$Language[$Eleanor->module['config']['n']];	$Lst=Eleanor::LoadListTemplate('headfoot');
	$u='?'.Url::Query(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['uri'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']));
	$Eleanor->module['rss']=Eleanor::$services['rss']['file'].$u;
	$head['rss']=$Lst('link',array(
		'rel'=>'alternate',
		'type'=>'application/rss+xml',
		'href'=>$Eleanor->module['rss'],
		'title'=>$lang['n'],
	));
	$head['search']=$Lst('link',array(
		'rel'=>'search',
		'type'=>'application/opensearchdescription+xml',
		'title'=>$lang['n'],
		'href'=>Eleanor::$services['xml']['file'].$u,
	));

	Eleanor::$Template->queue[]=$tpl ? $tpl : $Eleanor->module['config']['usertpl'];

	$tags=Eleanor::$Cache->Get($Eleanor->module['config']['n'].'_tags_'.Language::$main);
	if($tags===false)
	{
		$tags=array();
		$R=Eleanor::$Db->Query('SELECT `name`,`cnt` FROM `'.$Eleanor->module['config']['tt'].'` WHERE `language` IN (\'\',\''.Language::$main.'\') AND `cnt`>0 ORDER BY `cnt` DESC LIMIT 50');
		while($a=$R->fetch_assoc())
		{			$a['_a']=$Eleanor->Url->Construct(array(array('tag'=>htmlspecialchars_decode($a['name'],ELENT))));
			$tags[]=$a;
		}
		Eleanor::$Cache->Put($Eleanor->module['config']['n'].'_tags_'.Language::$main,$tags,3600);
	}

	#Cron
	if(isset(Eleanor::$services['cron']))
	{
		$cron=Eleanor::$Cache->Get($Eleanor->module['config']['n'].'_nextrun');
		$t=time();
		$cron=$cron===false && $cron<=$t ? Eleanor::$services['cron']['file'].'?'.Url::Query(array('module'=>$Eleanor->module['name'],'language'=>Language::$main==LANGUAGE ? false : Language::$main,'rand'=>$t)) : '';
	}
	else
		$cron=false;

	$Eleanor->module+=array(
		'tags'=>$tags ? $tags : null,
		'cron'=>$cron,
		'links'=>array(
			'base'=>$Eleanor->Url->Prefix(false),
			'categories'=>$Eleanor->Categories->dump ? $Eleanor->Url->Construct(array('do'=>'categories'),true,'') : false,
			'tags'=>$tags ? $Eleanor->Url->Construct(array('do'=>'tags'),true,'') : false,
			'search'=>$Eleanor->Url->Construct(array('do'=>'search'),true,''),
			'add'=>Eleanor::$vars['publ_add'] ? $Eleanor->Url->Construct(array('do'=>'add'),true,'') : false,
			'my'=>Eleanor::$vars['publ_add'] ? $Eleanor->Url->Construct(array('do'=>'my'),true,'') : false,
		)
	);}