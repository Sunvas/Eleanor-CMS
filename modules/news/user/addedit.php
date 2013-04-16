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
function AddEdit($id,$errors=array(),$gn=array())
{global$Eleanor,$title;
	$mc=$Eleanor->module['config'];
	SetData($mc['usercorrecttpl']);
	$lang=Eleanor::$Language[$mc['n']];
	$isu=Eleanor::$Login->IsUser();
	if($id)
	{
		$uid=(int)Eleanor::$Login->GetUserValue('id');
		$R=Eleanor::$Db->Query('SELECT `author`,`author_id`,`voting` FROM `'.$mc['t'].'` WHERE id='.$id.' LIMIT 1');
		if(!$values=$R->fetch_assoc() or !Eleanor::$Permissions->IsAdmin() and ($isu and $values['author_id']!=$uid or $uid==0 and !in_array($id,$gn)))
			return GoAway(true);
		if($isu)
			unset($values['author']);
		else
			unset($values['status']);
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`cats`,`enddate`,`show_detail`,`show_sokr`,`tags`,`status` FROM `'.$mc['t'].'` WHERE id='.$id.' LIMIT 1');
			$values+=$R->fetch_assoc();
			$values['cats']=$values['cats'] ? explode(',,',trim($values['cats'],',')) : array();
			$values['tags']=$values['tags'] ? explode(',,',trim($values['tags'],',')) : array();

			$values['_maincat']=reset($values['cats']);

			$values['uri']=$values['title']=$values['announcement']=$values['text']=array();
			$R=Eleanor::$Db->Query('SELECT `language`,`uri`,`title`,`announcement`,`text` FROM `'.$mc['tl'].'` WHERE `id`='.$id);
			while($temp=$R->fetch_assoc())
				if(!Eleanor::$vars['multilang'] and (!$temp['language'] or $temp['language']==Language::$main))
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk]=$tv;
					if(!$temp['language'])
						break;
				}
				elseif(!$temp['language'] and Eleanor::$vars['multilang'])
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk][Language::$main]=$tv;
					$values['_onelang']=true;
					break;
				}
				elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$temp['language']]))
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk][$temp['language']]=$tv;

			if(Eleanor::$vars['multilang'])
			{
				if(!isset($values['_onelang']))
					$values['_onelang']=false;
				$values['_langs']=array_keys($values['title']);
			}

			if($values['tags'])
			{
				$R=Eleanor::$Db->Query('SELECT `language`,`name` FROM `'.$mc['tt'].'` WHERE `id`'.Eleanor::$Db->In($values['tags']));
				$values['tags']=array();
				while($a=$R->fetch_assoc())
					$values['tags'][(Eleanor::$vars['multilang'] and !$values['_onelang']) ? $a['language'] : ''][]=$a['name'];
				foreach($values['tags'] as &$v)
				{
					#Удаление одинаковых тегов
					foreach($v as $kt=>&$vt)
						foreach($v as $kkt=>&$vvt)
						{
							if($kkt>=$kt)
								break;
							if(mb_strtolower($vt)==mb_strtolower($vvt))
								unset($v[$kt]);
						}
					$v=join(', ',$v);
				}
			}
			else
				$values['tags']=array(''=>'');
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$dv=Eleanor::$vars['multilang'] ? array(''=>'') : '';
		$values=array(
			'cats'=>isset($_GET['def'],$_GET['def']['category']) ? explode(',',(string)$_GET['def']['category']) : array(),
			'show_detail'=>true,
			'show_sokr'=>false,
			'tags'=>array(''=>''),
			'voting'=>false,
			'enddate'=>'',
			#Языковые
			'uri'=>$dv,
			'title'=>$dv,
			'announcement'=>$dv,
			'text'=>$dv,
			#Специальные
			'_maincat'=>0,
		);
		if(Eleanor::$vars['multilang'])
		{
			$values['_onelang']=true;
			$values['_langs']=array_keys(Eleanor::$langs);
		}
		if($isu)
			$values['status']=1;
		else
			$values['author']='';

		$title[]=$lang['adding'];
		if(!$errors)
		{
			if(!isset($Eleanor->TimeCheck))
				$Eleanor->TimeCheck=new TimeCheck($Eleanor->module['id']);
			$ch=$Eleanor->TimeCheck->Check('add',false);
			if($ch)
				$errors['FLOOD_LIMIT']=sprintf($lang['FLOOD_LIMIT'],Eleanor::$Permissions->FloodLimit(),$ch['_datets']-time());
		}
	}

	$hasdraft=false;
	if($isu and !$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\''.$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$id.'\' LIMIT 1');
		if($draft=$R->fetch_row() and $draft[0])
		{
			$hasdraft=true;
			$_POST+=(array)unserialize($draft[0]);
			$errors=true;
		}
	}

	if($errors)
	{
		$bypost=true;
		if($errors===true)
			$errors=array();
		$values['tags']=isset($_POST['tags']) ? (array)$_POST['tags'] : array();
		if(Eleanor::$vars['multilang'])
		{
			$values['title']=isset($_POST['title']) ? (array)$_POST['title'] : array();
			$values['announcement']=isset($_POST['announcement']) ? (array)$_POST['announcement'] : array();
			$values['text']=isset($_POST['text']) ? (array)$_POST['text'] : array();
			$values['uri']=isset($_POST['uri']) ? (array)$_POST['uri'] : array();
			$values['_onelang']=isset($_POST['_onelang']);
			$values['_langs']=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array(Language::$main);
		}
		else
		{
			$values['title']=isset($_POST['title']) ? (string)$_POST['title'] : '';
			$values['announcement']=isset($_POST['announcement']) ? (string)$_POST['announcement'] : '';
			$values['text']=isset($_POST['text']) ? (string)$_POST['text'] : '';
			$values['uri']=isset($_POST['uri']) ? (string)$_POST['uri'] : '';
		}
		$values['_maincat']=isset($_POST['_maincat']) ? (int)$_POST['_maincat'] : 0;
		$values['enddate']=isset($_POST['enddate']) ? (string)$_POST['enddate'] : '';
		$values['cats']=isset($_POST['cats']) ? (array)$_POST['cats'] : array();
		$values['show_detail']=isset($_POST['show_detail']);
		$values['show_sokr']=isset($_POST['show_sokr']);
		if($isu)
			$values['status']=isset($_POST['status']) ? (int)$_POST['status'] : 0;
		else
			$values['author']=isset($_POST['author']) ? (string)$_POST['author'] : '';
		$Eleanor->VotingManager->bypost=true;
	}
	else
		$bypost=false;
	$Eleanor->Uploader->allow_walk=false;
	$Eleanor->Uploader->watermark=true;
	$Eleanor->Uploader->buttons_top['create_folder']=false;

	$Eleanor->VotingManager->noans=!Eleanor::$Permissions->IsAdmin();

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('do'=>'delete','id'=>$id),true,'') : false,
		'nodraft'=>$isu ? $Eleanor->Url->Construct(array('do'=>$id ? 'edit' : 'add','id'=>$id,array('nodraft'=>1)),true,'') : false,
		'draft'=>$isu ? $Eleanor->Url->Construct(array('do'=>'draft'),true,'') : false,
	);

	$c=Eleanor::$Template->AddEdit($id,$values,$errors,$Eleanor->Uploader->Show($id ? $mc['n'].DIRECTORY_SEPARATOR.$id : false),$Eleanor->VotingManager->AddEdit($values['voting']),$bypost,$hasdraft,$back,$links,$Eleanor->Captcha->disabled ? false : $Eleanor->Captcha->GetCode());
	Start();
	echo$c;
}

function Save($id,$gn=array())
{global$Eleanor,$title;
	$mc=$Eleanor->module['config'];
	$lang=Eleanor::$Language[$mc['n']];
	$errors=array();
	if(!$id)
	{
		$Eleanor->TimeCheck=new TimeCheck($Eleanor->module['id']);
		$ch=$Eleanor->TimeCheck->Check('add',false);
		if($ch)
			$errors['FLOOD_LIMIT']=sprintf($lang['FLOOD_LIMIT'],Eleanor::$Permissions->FloodLimit(),$ch['_datets']-time());
	}

	$isu=Eleanor::$Login->IsUser();
	$uid=$isu ? Eleanor::$Login->GetUserValue('id') : 0;
	$values=array();

	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `author_id`,`date`,`voting` FROM `'.$mc['t'].'` WHERE id='.$id.' LIMIT 1');
		if(!$old=$R->fetch_assoc() or !Eleanor::$Permissions->IsAdmin() and ($uid>0 and $old['author_id']!=$uid or $uid==0 and !in_array($id,$gn)))
			return GoAway(true);
	}

	$mod=Eleanor::$Permissions->Moderate();
	#Перечень переменных, которые дублируются для языковой таблицы ниже
	$maincat=isset($_POST['_maincat']) ? (int)$_POST['_maincat'] : false;
	$cats=isset($_POST['cats']) ? array_unique((array)$_POST['cats']) : array();
	if($cats and count($cats)>1)
	{
		sort($cats,SORT_NUMERIC);
		if($maincat!=$cats[0])
			array_unshift($cats,$maincat ? $maincat : reset($cats));
	}
	$cats=$cats ? ','.join(',,',$cats).',' : '';

	if($isu)
	{
		$status=isset($_POST['status']) ? (int)$_POST['status'] : 0;
		$cangn=false;
		$values['author']=Eleanor::$Login->GetUserValue('name');
	}
	else
	{
		$status=1;
		$cangn=!$id;
		$values['author']=isset($_POST['author']) ? (string)Eleanor::$POST['author'] : '';
		if($values['author']=='')
			$errors['ERROR_FILL_AUTHOR']=$lang['FILL_AUTHOR'];
	}
	$status=$status>0 ? ($mod ? -1 : 1) : 0;

	$enddate=isset($_POST['enddate']) ? (string)$_POST['enddate'] : '';
	if($enddate)
	{
		$enddate=strtotime($enddate);
		if(!$enddate)
			$errors['ERROR_END_DATE']=$lang['ERROR_END_DATE'];
		elseif($status and $enddate<=time())
			$errors['ERROR_END_DATE_IN_PAST']=$lang['ERROR_END_DATE_IN_PAST'];
		$enddate=date('Y-m-d H:i:s',$enddate);
	}

	if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
	{
		$langs=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array();
		$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
		if(!$langs)
			$langs=array(Language::$main);
	}
	else
		$langs=array('');

	$Eleanor->VotingManager->noans=!Eleanor::$Permissions->IsAdmin();

	$Eleanor->VotingManager->langs=Eleanor::$vars['multilang'] ? $langs : array();
	$voting=$Eleanor->VotingManager->Save($id ? $old['voting'] : false);
	if(is_array($voting))
		$errors+=$voting;

	$values+=array(
		'cats'=>$cats,
		'enddate'=>$enddate,
		'status'=>$status,
		'show_detail'=>isset($_POST['show_detail']),
		'show_sokr'=>isset($_POST['show_sokr']),
		'tags'=>'',
		'voting'=>$voting,
	);

	if(Eleanor::$vars['multilang'])
	{
		$lvalues=array(
			'title'=>array(),
			'announcement'=>array(),
			'text'=>array(),
			'uri'=>array(),
			'tags'=>array(),
		);
		foreach($langs as $l)
		{
			$lng=$l ? $l : Language::$main;
			$lvalues['tags'][$l]=isset($_POST['tags'],$_POST['tags'][$lng]) && is_array($_POST['tags']) ? (string)Eleanor::$POST['tags'][$lng] : '';
			$lvalues['title'][$l]=$Eleanor->Editor_result->imgalt=isset($_POST['title'],$_POST['title'][$lng]) && is_array($_POST['title']) ? (string)Eleanor::$POST['title'][$lng] : '';
			$lvalues['announcement'][$l]=isset($_POST['announcement'],$_POST['announcement'][$lng]) && is_array($_POST['announcement']) ? $Eleanor->Editor_result->GetHtml((string)$_POST['announcement'][$lng],true) : '';
			$lvalues['text'][$l]=isset($_POST['text'],$_POST['text'][$lng]) && is_array($_POST['text']) ? $Eleanor->Editor_result->GetHtml((string)$_POST['text'][$lng],true) : '';
			$lvalues['uri'][$l]=isset($_POST['uri'],$_POST['uri'][$lng]) && is_array($_POST['uri']) ? (string)$_POST['uri'][$lng] : '';
		}
	}
	else
	{
		$Eleanor->Editor_result->imgalt=isset($_POST['title']) ? (string)Eleanor::$POST['title'] : '';
		$lvalues=array(
			'title'=>array(''=>$Eleanor->Editor_result->imgalt),
			'announcement'=>array(''=>$Eleanor->Editor_result->GetHtml('announcement')),
			'text'=>array(''=>$Eleanor->Editor_result->GetHtml('text')),
			'tags'=>array(''=>isset($_POST['tags']) ? (string)Eleanor::$POST['tags'] : ''),
			'uri'=>array(''=>isset($_POST['uri']) ? (string)$_POST['uri'] : ''),
		);
	}

	$ml=in_array('',$langs) ? Language::$main : '';
	$emp=array('title'=>array(),'announcement'=>array(),'text'=>array());
	foreach($emp as $kf=>&$field)
		foreach($lvalues[$kf] as $k=>&$v)
			$field[$k]=$v=='' && (in_array($k,$langs) or $ml==$k);

	foreach($emp['title'] as $k=>&$v)
		if($v)
		{
			$er='EMPTY_TITLE'.strtoupper($k ? '_'.$k : '');
			$errors[$er]=$lang['EMPTY_TITLE']($k);
		}

	foreach($emp['announcement'] as $k=>&$v)
		if($v and $emp['text'][$k])
		{
			$er='EMPTY_TEXT'.strtoupper($k ? '_'.$k : '');
			$errors[$er]=$lang['EMPTY_TEXT']($k);
		}

	$cach=$Eleanor->Captcha->Check(isset($_POST['check']) ? (string)$_POST['check'] : '');
	$Eleanor->Captcha->Destroy();
	if(!$cach)
		$errors[]='WRONG_CAPTCHA';

	if($errors)
		return AddEdit($id,$errors,$gn);

	foreach($lvalues['uri'] as $k=>&$v)
	{
		if($v=='')
			$v=htmlspecialchars_decode($lvalues['title'][$k],ELENT);
		$v=$Eleanor->Url->Filter($v,$k);
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$mc['t'].'` INNER JOIN `'.$mc['tl'].'` USING(`id`) WHERE `uri`='.Eleanor::$Db->Escape($v).' AND `language`'.($k ? 'IN(\'\',\''.$k.'\')' : '=\'\'').($id ? ' AND `id`!='.$id : '').' LIMIT 1');
		if($R->num_rows>0)
			$v='';
	}

	$addt=$values['tags']=array();
	foreach($lvalues['tags'] as $lng=>&$tags)
	{
		$tags=$tags ? explode(',',$tags) : array();
		foreach($tags as $kt=>&$vt)
		{
			$vt=trim($vt);
			if(!$vt)
			{
				unset($tags[$kt]);
				continue;
			}
			foreach($tags as $kkt=>$vvt)
			{
				if($kkt>=$kt)
					break;
				if(mb_strtolower($vt)==mb_strtolower($vvt))
					unset($tags[$kt]);
			}
		}
		unset($vt);#Не просто так
		if(!$tags)
			continue;
		$toins=$utags=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`name` FROM `'.$mc['tt'].'` WHERE'.(($lng and isset(Eleanor::$langs[$lng])) ? '`language` IN (\'\',\''.$lng.'\') AND' : '').' `name`'.Eleanor::$Db->In($tags));
		while($a=$R->fetch_assoc())
			$utags[$a['id']]=$a['name'];

		foreach($tags as &$tv)
		{
			$n=true;
			foreach($utags as &$utv)
				if(mb_strtolower($tv)==mb_strtolower($utv))
				{
					$n=false;
					break;
				}
			if($n)
				$toins[]=$tv;
		}

		if($toins)
		{
			$n=Eleanor::$Db->Insert($mc['tt'],array('language'=>array_fill(0,count($toins),$lng),'name'=>$toins));
			if($n>0)
				foreach($toins as &$v)
					$utags[$n++]=$v;
		}
		$tags=array_keys($utags);
		$values['tags']=array_merge($values['tags'],$tags);
		$values['tags']=array_unique($values['tags']);
		sort($values['tags'],SORT_NUMERIC);
	}
	unset($tags,$utags);

	if(Eleanor::$Login->IsUser())
		Eleanor::$Db->Delete(P.'drafts','`key`=\''.$mc['n'].'-'.Eleanor::$Login->GetUserValue('id').'-n'.$id.'\' LIMIT 1');
	$ping=false;
	if($id)
	{
		$t=array();
		if($status==1)
		{
			$ping=true;
			$R=Eleanor::$Db->Query('SELECT `tag` FROM `'.$mc['rt'].'` WHERE `id`='.$id);
			while($a=$R->fetch_assoc())
				$t[]=$a['tag'];
			$addt=array_diff($values['tags'],$t);
			$delt=array_diff($t,$values['tags']);

			if($delt)
			{
				$delt=Eleanor::$Db->In($delt);
				Eleanor::$Db->Delete($mc['rt'],'`id`='.$id.' AND `tag`'.$delt);
				Eleanor::$Db->Update($mc['tt'],array('!cnt'=>'`cnt`-1'),'`id`'.$delt.' AND `cnt`>0');
			}
		}
		else
			RemoveTags($id);
		$values['tags']=$values['tags'] ? ','.join(',,',$values['tags']).',' : '';

		Eleanor::$Db->Update($mc['t'],$values,'id='.$id.' LIMIT 1');
		Eleanor::$Db->Delete($mc['tl'],'`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		foreach($langs as &$v)
		{
			$values=array(
				'id'=>$id,
				'language'=>$v,
				'uri'=>$lvalues['uri'][$v],
				'lstatus'=>$status,
				'lcats'=>$cats,
				'title'=>$lvalues['title'][$v],
				'announcement'=>$lvalues['announcement'][$v],
				'text'=>$lvalues['text'][$v],
				'last_mod'=>date('Y-m-d H:i:s'),
			);
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$mc['tl'].'` WHERE `id`='.$id.' AND `language`=\''.$values['language'].'\' LIMIT 1');
			if($R->num_rows>0)
				Eleanor::$Db->Update($mc['tl'],$values,'`id`='.$id.' AND `language`=\''.$values['language'].'\' LIMIT 1');
			else
			{
				$values['ldate']=$old['date'];
				Eleanor::$Db->Replace($mc['tl'],$values);
			}
		}
		$title[]=$mod ? $lang['waitmod'] : $lang['nssedit'];
	}
	else
	{
		if($status==1)
		{
			$addt=$values['tags'];
			$ping=true;
		}
		$values['tags']=$values['tags'] ? ','.join(',,',$values['tags']).',' : '';
		$date=date('Y-m-d H:i:s');
		$values+=array(
			'date'=>$date,
			'author_id'=>$uid,
		);
		Eleanor::$Db->Transaction();#Все ради аплоадера
		$id=Eleanor::$Db->Insert($mc['t'],$values);
		try
		{
			$ft=$Eleanor->Uploader->MoveFiles($mc['n'].DIRECTORY_SEPARATOR.$id);
		}
		catch(EE$E)
		{
			Eleanor::$Db->Rollback();
			return AddEdit(false,$E->getMessage());
		}
		$values=array('id'=>array(),'language'=>array(),'uri'=>array(),'title'=>array(),'announcement'=>array(),'text'=>array());
		foreach($langs as &$v)
		{
			$values['id'][]=$id;
			$values['language'][]=$v;
			$values['lstatus'][]=$status;
			$values['ldate'][]=$date;
			$values['lcats'][]=$cats;
			$values['uri'][]=$lvalues['uri'][$v];
			$values['title'][]=str_replace($ft['from'],$ft['to'],$lvalues['title'][$v]);
			$values['announcement'][]=str_replace($ft['from'],$ft['to'],$lvalues['announcement'][$v]);
			$values['text'][]=str_replace($ft['from'],$ft['to'],$lvalues['text'][$v]);
			$values['last_mod'][]=date('Y-m-d H:i:s');
		}
		Eleanor::$Db->Insert($mc['tl'],$values);
		Eleanor::$Db->Commit();
		$title[]=$mod ? $lang['waitmod'] : $lang['nssadded'];
		if($flood=Eleanor::$Permissions->FloodLimit())
			$Eleanor->TimeCheck->Add('add','',true,$flood);
		if($cangn)
		{
			$gn=Eleanor::GetCookie($mc['n'].'-gn');
			$gns=Eleanor::GetCookie($mc['n'].'-gns');

			if($gn and $gns and $gns===md5($gn.$mc['secret']))
				$gn=explode(',',$gn);
			else
				$gn=array();

			$gn[]=$id;
			sort($gn,SORT_NUMERIC);
			$gn=join(',',$gn);
			Eleanor::SetCookie($mc['n'].'-gn',$gn);
			Eleanor::SetCookie($mc['n'].'-gns',md5($gn.$mc['secret']));
		}
	}
	if($addt)
	{
		Eleanor::$Db->Insert($mc['rt'],array('id'=>array_fill(0,count($addt),$id),'tag'=>array_values($addt)));
		Eleanor::$Db->Update($mc['tt'],array('!cnt'=>'`cnt`+1'),'`id`'.Eleanor::$Db->In($addt));
	}

	Eleanor::$Cache->Lib->DeleteByTag($mc['n']);
	SetData($mc['usercorrecttpl']);
	$u=array('u'=>array(Eleanor::FilterLangValues($lvalues['uri']),'id'=>$id));
	if($maincat and $Eleanor->Url->furl)
	{
		$cu=$Eleanor->Categories->GetUri($maincat);
		if($cu)
			$u=$cu+$u;
	}
	if($ping and Eleanor::$vars['publ_ping'])
	{
		$sd=PROTOCOL.Eleanor::$domain.Eleanor::$site_path;
		Ping::Add(array('id'=>$mc['n'],'changes'=>$sd.$Eleanor->Url->Prefix(),'rss'=>$sd.$Eleanor->module['links']['rss']));
	}
	Eleanor::$Cache->Obsolete($mc['n'].'_nextrun');
	$oldid=func_get_arg(0);
	$c=Eleanor::$Template->AddEditComplete(empty($_POST['back']) ? '' : $_POST['back'],$Eleanor->Url->Construct($u),$oldid,$status,Eleanor::FilterLangValues($lvalues['title']),$mod);
	Start();
	echo$c;
}

function RemoveTags($ids)
{global$Eleanor;
	$mc=$Eleanor->module['config'];
	$in=Eleanor::$Db->In($ids);
	$R=Eleanor::$Db->Query('SELECT `tag`,COUNT(`id`) `cnt` FROM `'.$mc['rt'].'` WHERE `id`'.$in.' GROUP BY `tag`');
	while($a=$R->fetch_assoc())
		Eleanor::$Db->Update($mc['tt'],array('!cnt'=>'GREATEST(0,`cnt`-'.$a['cnt'].')'),'`id`='.$a['tag'].' AND `cnt`>0');
	Eleanor::$Db->Delete($mc['rt'],'`id`'.$in);
}