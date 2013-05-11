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
$lang=Eleanor::$Language->Load('addons/admin/langs/comments-*.php','lc');
Eleanor::$Template->queue[]='Comments';

if(isset($ong))
	return CommentsList(true);

$R=Eleanor::$Db->Query('SELECT COUNT(`status`) FROM `'.P.'comments` WHERE `status`=-1');
list($cnt)=$R->fetch_row();

$Eleanor->module['links']=array(
	'list'=>$Eleanor->Url->Prefix(),
	'news'=>$cnt>0
		? array(
			'link'=>$Eleanor->Url->Construct(array(''=>array('fi'=>array('status'=>-1)))),
			'cnt'=>$cnt,
		)
		: false,
	'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
);


if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'options':
			$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
			$c=$Eleanor->Settings->GetInterface('group','comments');
			if($c)
			{
				$c=Eleanor::$Template->Options($c);
				Start();
				echo$c;
			}
		break;
		default:
			CommentsList();
	}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
	{
		$R=Eleanor::$Db->Query('SELECT `status` FROM `'.P.'comments` WHERE id='.$id.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway(true);
		$values=array(
			'text'=>$Eleanor->Editor_result->GetHtml('text'),
		);
		$status=isset($_POST['status']) ? (int)$_POST['status'] : 1;
		if($status<-1 or $status>1)
			$status=1;
		if($a['status']!=$status)
			ChangeStatus($id,$status);
		Eleanor::$Db->Update(P.'comments',$values,'`id`='.$id.' LIMIT 1');
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	else
		Edit($id);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `parents`,`text` FROM `'.P.'comments` WHERE `id`='.$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		ChangeStatus($id,0);
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'comments` WHERE `id`='.$id.' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			Eleanor::$Db->Delete(P.'comments','`parents` LIKE \''.$a['parents'].$id.',%\'');
			Eleanor::$Db->Delete(P.'comments','`id`='.$id.' LIMIT 1');
		}
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;

}
elseif(isset($_GET['swap']))
{
	$id=(int)$_GET['swap'];
	if(Eleanor::$our_query)
	{
		$R=Eleanor::$Db->Query('SELECT `status` FROM `'.P.'comments` WHERE `id`='.$id.' LIMIT 1');
		if($a=$R->fetch_assoc())
			ChangeStatus($id,$a['status']<1 ? 1 : 0);
	}
	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#comment'.$id : true);
}
else
	CommentsList();

function CommentsList($ong=false)
{global$Eleanor,$title;
	if(!$ong)
		$title=Eleanor::$Language['lc']['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$mc=$titles=$items=$where=$qs=array();
	list($modules,$mapi)=CommentsModules();

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;
		$qs['']['fi']=array();
		if(isset($_REQUEST['fi']['module']))
		{
			$m=(int)$_REQUEST['fi']['module'];
			if(isset($modules[$m]))
			{
				$qs['']['fi']['module']=$m;
				$where[]='`module`='.$m;
			}
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';
	if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']) and is_array($_POST['mass']))
		switch($_POST['op'])
		{
			case'a':
				ChangeStatus($_POST['mass'],1);
			break;
			case'd':
				ChangeStatus($_POST['mass'],-1);
			case'b':
				ChangeStatus($_POST['mass'],0);
			break;
			case'k':
				ChangeStatus($_POST['mass'],0);
				$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.P.'comments` WHERE `id`'.Eleanor::$Db->In($_POST['mass']));
				while($a=$R->fetch_assoc())
				{
					Eleanor::$Db->Delete(P.'comments','`parents` LIKE \''.$a['parents'].$a['id'].',%\'');
					Eleanor::$Db->Delete(P.'comments','`id`='.$a['id'].' LIMIT 1');
				}
		}

	$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'comments`'.$where);
	list($cnt)=$R->fetch_row();
	if($page<=0)
		$page=1;
	if(isset($_GET['new-pp']) and 4<$pp=(int)$_GET['new-pp'])
		Eleanor::SetCookie('per-page',$pp);
	else
		$pp=abs((int)Eleanor::GetCookie('per-page'));
	if($pp<5 or $pp>500)
		$pp=50;
	$offset=abs(($page-1)*$pp);
	if($cnt and $offset>=$cnt)
		$offset=max(0,$cnt-$pp);
	$sort=isset($_GET['sort']) ? (string)$_GET['sort'] : '';
	if(!in_array($sort,array('id','module','date','author','ip')))
		$sort='';
	$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? (string)$_GET['so'] : 'desc';
	if($so!='asc')
		$so='desc';
	if($sort and ($sort!='news' or $so!='desc'))
		$qs+=array('sort'=>$sort,'so'=>$so);
	else
		$sort='id';
	$qs+=array('sort'=>false,'so'=>false);

	if($cnt>0)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`module`,`contid`,`status`,`date`,`author`,`author_id`,`ip`,`text` FROM `'.P.'comments`'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
		while($a=$R->fetch_assoc())
		{
			$mc[$a['module']][$a['contid']][]=$a['id'];

			$a['_aswap']=$Eleanor->Url->Construct(array('swap'=>$a['id']));
			$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
			$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));

			$items[$a['id']]=array_slice($a,1);
		}
	}

	foreach($mc as $k=>&$v)
		if(isset($mapi[$k]))
		{
			$Api=new$mapi[$k];
			$titles+=(array)$Api->LinkToComment($v);
		}
	unset($Api,$v,$mc);
	$links=array(
		'sort_date'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'date','so'=>$qs['sort']=='date' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_author'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'author','so'=>$qs['sort']=='author' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_module'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'module','so'=>$qs['sort']=='module' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_ip'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'ip','so'=>$qs['sort']=='ip' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
		'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
		'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
		'first_page'=>$Eleanor->Url->Construct($qs),
		'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
	);
	$c=Eleanor::$Template->CommentsList($items,$modules,$titles,$cnt,$pp,$qs,$page,$links,$ong);
	if($ong)
		return$c;
	Start();
	echo$c;
}

function Edit($id,$error='')
{global$Eleanor,$title;
	$title[]=Eleanor::$Language['lc']['editing'];
	$R=Eleanor::$Db->Query('SELECT `id`,`module`,`contid`,`status`,`date`,`author`,`author_id`,`text` FROM `'.P.'comments` WHERE id='.$id.' LIMIT 1');
	if(!$values=$R->fetch_assoc())
		return GoAway(true);
	if($error)
	{
		if($error===true)
			$error='';
		$values['text']=isset($_POST['text']) ? (string)$_POST['text'] : '';
		$values['status']=isset($_POST['status']) ? (int)$_POST['status'] : 0;
		$bypost=true;
	}
	else
		$bypost=false;
	list($modules,$mapi)=CommentsModules($values['module']);
	$mapi=reset($mapi);
	$Api=new$mapi;
	$mapi=$Api->LinkToComment(array($values['contid']=>array($values['id'])));

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$links=array(
		'delete'=>$Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)),
	);
	$c=Eleanor::$Template->Edit($id,reset($modules),reset($mapi),$values,$bypost,$error,$back,$links);
	Start();
	echo$c;
}

function ChangeStatus($ids,$newst)
{
	$act=$newst==1;
	$pars='';
	$R=Eleanor::$Db->Query('SELECT `id`,`module`,`contid`,`status`,`parent`,`parents` FROM `'.P.'comments` WHERE `id`'.Eleanor::$Db->In($ids));
	$ids=$sids=$pupd=$addids=$aff=$affids=array();
	while($a=$R->fetch_assoc())
	{
		$pars.=$a['parents'];
		if(strpos($pars,','.$a['id'].',')===false)
			$addids[]=$a['parents'].$a['id'].',';
		$ids[]=$a['id'];
		$sids[$a['status']][]=$a['id'];
		if($act and $a['status']!=1)
			$pupd[$a['parent']]=isset($pupd[$a['parent']]) ? $pupd[$a['parent']]+1 : 1;
		elseif(!$act and $a['status']==1)
			$pupd[$a['parent']]=0;
		$aff[$a['module']][$a['contid']]=0;
		$affids[$a['id']]=&$aff[$a['module']][$a['contid']];
	}

	foreach($addids as &$v)
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`module`,`contid`,`status`,`parent` FROM `'.P.'comments` WHERE `parents` LIKE \''.$v.'%\' AND `id`'.Eleanor::$Db->In($ids,true));
		while($a=$R->fetch_assoc())
		{
			$ids[]=$a['id'];
			$sids[$a['status']][]=$a['id'];
			if($act and $a['status']!=1)
				$pupd[$a['parent']]=isset($pupd[$a['parent']]) ? $pupd[$a['parent']]+1 : 1;
			elseif(!$act and $a['status']==1)
				$pupd[$a['parent']]=0;
			$aff[$a['module']][$a['contid']]=0;
			$affids[$a['id']]=&$aff[$a['module']][$a['contid']];
		}
	}

	Eleanor::$Db->Transaction();
	foreach($sids as $k=>&$v)
		if($k==-1)
		{
			if($act)
			{
				$in=Eleanor::$Db->In($v);
				$R=Eleanor::$Db->Query('SELECT `date` FROM `'.P.'comments` WHERE `id`'.$in.' ORDER BY `sortdate` ASC LIMIT 1');
				if($a=$R->fetch_assoc())
					foreach($v as &$vv)
						$affids[$vv]+=Eleanor::$Db->Update(P.'comments',array('!date'=>'FROM_UNIXTIME('.(time()-strtotime($a['date'])).'+UNIX_TIMESTAMP(`date`))','status'=>1),'`id`='.$vv.' LIMIT 1');
			}
			elseif($newst==0)
				foreach($v as &$vv)
					$affids[$vv]+=Eleanor::$Db->Update(P.'comments',array('status'=>0),'`id`='.$vv.' LIMIT 1');
		}
		else
			foreach($v as &$vv)
				$affids[$vv]+=Eleanor::$Db->Update(P.'comments',array('status'=>$newst),'`id`='.$vv.' LIMIT 1');
	foreach($pupd as $k=>&$v)
		Eleanor::$Db->Update(P.'comments',$v>0 ? array('!answers'=>$act ? '`answers`+'.$v : 'GREATEST(0,`answers`-'.$v.')') : array('answers'=>0),'`id`='.$k.' LIMIT 1');
	Eleanor::$Db->Commit();

	list(,$mapi)=CommentsModules(array_keys($aff));
	foreach($aff as $m=>&$cn)
		if(isset($mapi[$m]) and method_exists($mapi[$m],'UpdateNumComments'))
		{
			if(!$act)
				foreach($cn as &$v)
					$v=-$v;
			$Api=new$mapi[$m];
			try
			{
				$Api->UpdateNumComments($cn);
			}
			catch(EE$E)
			{
				$E->Log();
			}
		}
}

function CommentsModules($ids=false)
{static$r;
	if(!isset($r))
	{
		$modules=$mapi=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l`,`path`,`api` FROM `'.P.'modules` WHERE `api`!=\'\''.($ids ? ' AND `id`'.Eleanor::$Db->In($ids) : ''));
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
			if(!method_exists($class,'LinkToComment'))
				continue;
			$modules[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
			$mapi[$a['id']]=$class;
		}
		asort($modules,SORT_STRING);
		$r=array($modules,$mapi);
	}
	return$r;
}