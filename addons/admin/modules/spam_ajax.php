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
$event=isset($_POST['event']) ? $_POST['event'] : '';
Eleanor::$Template->queue[]='Spam';
switch($event)
{
	case'search':
		Eleanor::$Language->Load('addons/admin/langs/spam-*.php','spam');
		$page=isset($_POST['page']) ? (int)$_POST['page'] : 1;
		$pp=isset($_POST['pp']) ? (int)$_POST['pp'] : 1;
		if($pp<=10)
			$pp=10;
		$where=$grs=$groups=$items=array();
		if(!empty($_POST['finame']) and !empty($_POST['finamet']))
		{
			$name=Eleanor::$Db->Escape($_POST['finame'],false);
			switch($_POST['finamet'])
			{
				case'b':
					$name=' LIKE \''.$name.'%\'';
				break;
				case'm':
					$name=' LIKE \'%'.$name.'%\'';
				break;
				case'e':
					$name=' LIKE \'%'.$name.'\'';
				break;
				default:
					$name='=\''.$name.'\'';
			}
			$where[]='`u`.`name`'.$name;
		}
		if(!empty($_POST['fiids']))
			$where[]='`id`'.Eleanor::$Db->In(explode(',',Tasks::FillInt($_POST['fiids'])));

		if(!empty($_POST['firegisterb']) and 0<$t=strtotime($_POST['firegisterb']))
			$where[]='`u`.`register`>=\''.date('Y-m-d H:i:s',$t).'\'';
		if(!empty($_POST['firegistera']) and 0<$t=strtotime($_POST['firegistera']))
			$where[]='`u`.`register`<=\''.date('Y-m-d H:i:s',$t).'\'';

		if(!empty($_POST['fiip']))
			$where[]='`ip` LIKE \''.str_replace('*','%',Eleanor::$Db->Escape($_POST['fiip'],false)).'\'';
		if(!empty($_POST['fiemail']))
			$where[]='`email` LIKE \''.str_replace('*','%',Eleanor::$Db->Escape($_POST['fiemail'],false)).'\'';
		if(isset($_POST['figender']) and $_POST['figender']>-2)
			$where[]='`gender`='.(int)$_POST['figender'];
		if(!empty($_POST['figroup']) and is_array($_POST['figroup']))
			if(isset($_POST['figroupt']) and $_POST['figroupt']=='and')
			{
				$g='%,';
				foreach($_POST['figroup'] as &$v)
					$g.=(int)$v.',%';
				$where[]='`groups` LIKE \''.str_replace('*','%',$g).'\'';
			}
			else
			{
				foreach($_POST['figroup'] as &$v);
					$v=(int)$v;
				$where[]='`groups` REGEXP \',('.join('|',$_POST['figroup']).'),\'';
			}
		$where=$where ? 'WHERE '.join(' AND ',$where) : '';
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$table=USERS_TABLE;
			$where=' INNER JOIN `'.P.'users_site` USING(`id`)'.$where;
		}
		else
			$table=P.'users_site';
		$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.$table.'` `u` INNER JOIN `'.P.'users_extra` `e` USING(`id`)'.$where);
		list($cnt)=$R->fetch_row();
		if($page<=0)
			$page=1;
		$offset=($page-1)*$pp;
		if($cnt and $offset>=$cnt)
			$offset=max(0,$cnt-$pp);

		if($cnt>0)
		{
			$myuid=Eleanor::$Login->GetUserValue('id');
			$Eleanor->Url->SetPrefix(array('section'=>'management','module'=>'users'));
			$R2=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`email`,`groups`,`ip`,`u`.`last_visit` FROM `'.$table.'` `u` INNER JOIN `'.P.'users_extra` `e` USING(`id`)'.$where.' LIMIT '.$offset.', '.$pp);
			while($a=$R2->fetch_assoc())
			{
				$a['groups']=$a['groups'] ? explode(',',trim($a['groups'],',')) : array();
				$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
				$a['_adel']=$myuid==$a['id'] ? false : $Eleanor->Url->Construct(array('delete'=>$a['id']));

				if($a['groups'])
					$groups=array_merge($groups,$a['groups']);
				$items[$a['id']]=array_slice($a,1);
			}
		}

		if($groups)
		{
			$R3=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
			$tosort=$groups=array();
			$Eleanor->Url->SetPrefix(array('section'=>'management','module'=>'groups'));
			while($a=$R3->fetch_assoc())
			{
				$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
				$tosort[$a['id']]=$a['title'];

				$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
				$groups[$a['id']]=array_slice($a,1);
			}
			natsort($tosort);
			foreach($tosort as $k=>&$v)
				$grs[$k]=$groups[$k];
		}

		Result(Eleanor::$Template->UsersList($items,$grs,$pp,$page,$cnt));
	break;
	case'progress':
		$ids=isset($_POST['ids']) ? (array)$_POST['ids'] : array();
		if(!$ids)
			return Error();
		$res=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`sent`,`total`,`status` FROM `'.P.'spam` WHERE `id`'.Eleanor::$Db->In($ids));
		while($a=$R->fetch_assoc())
			$res[$a['id']]=array(
				'done'=>$a['status']!='runned',
				'percent'=>$a['total']>0 ? round($a['sent']/$a['total']*100) : 0,
				'val'=>$a['sent'],
				'total'=>$a['total'],
			);
		Result($res ? $res : false);
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}