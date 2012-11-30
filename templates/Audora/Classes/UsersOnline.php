<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Ўаблоны дл€ отображени€ информации о пользовател€х онлайн
*/
class TplUsersOnline
{	public static
		$lang;	/*
		—одержимое блока " то онлайн"
		$sess - массив формата сервис=>users|bots|guests=>array(), внутренний массив - данные о сессии пользовател€, ключи:
			user_id - ID пользовател€
			enter - врем€ входа
			ip_guest - IP гост€ и бота
			ip_user - IP пользовател€
			service - сервис
			botname - им€ бота
			groups - массив групп пользовател€
			name - им€ пользоват€ (не безопасный HTML)
			_gpref - HTML префикс группы пользовател€
			_gend - HTML окончание группы пользовател€
		$scnt - если в sess переданы не все сессии (передаетс€ 30 самых свежих), в этом массиве в формате сервис=>число сессий будет содержатьс€ число
			сессий дл€ каждого сервиса
		$sscnt - если в sess переданы не все сессии (передаетс€ 30 самых свежих), в этом массиве в формате сервис=>(user|bot|guest)=>число сессий будет
			содержатьс€ число сессий сгруппированных по пользовател€м, ботам и гост€м
	*/	public static function BlockOnline($sess,$scnt,$sscnt)
	{		$t=time();
		$c='';
		foreach($sess as $k=>&$v)
		{			#„тобы избегать проверок if(isset($v['users'|'bots'|'guests']))
			$v+=array('users'=>array(),'guests'=>array(),'bots'=>array());

			foreach($v['users'] as &$vv)
				$vv='<a class="entry" href="'.$vv['_aedit'].'" data-uid="'.$vv['user_id'].'" data-s="'.$k.'" title="'.call_user_func(static::$lang['min_left'],floor(($t-strtotime($vv['enter']))/60)).'">'.$vv['_gpref'].htmlspecialchars($vv['name'],ELENT,CHARSET).$vv['_gend'].'</a>';
			$u=isset($sscnt[$k]['user']) ? $sscnt[$k]['user'] : count($v['users']);

			foreach($v['guests'] as &$vv)
				$vv='<span class="entry" data-gip="'.$vv['ip_guest'].'" data-s="'.$k.'" title="'.call_user_func(static::$lang['min_left'],floor(($t-strtotime($vv['enter']))/60)).'">'.$vv['ip_guest'].'</span>';
			$g=isset($sscnt[$k]['guest']) ? $sscnt[$k]['guest'] : count($v['guests']);

			foreach($v['bots'] as &$vv)
				$vv='<span class="entry" data-gip="'.$vv['ip_guest'].'" data-s="'.$k.'" title="'.call_user_func(static::$lang['min_left'],floor(($t-strtotime($vv['enter']))/60)).'">'.htmlspecialchars($vv['botname'],ELENT,CHARSET).'</span>';
			$b=isset($sscnt[$k]['bot']) ? $sscnt[$k]['bot'] : count($v['bots']);

			$c.='<div><h2>'.$k.' ('.(isset($scnt[$k]) ? $scnt[$k] : $u+$b+$g).')</h2>'
				.($u>0 ? '<div><h4>'.call_user_func(static::$lang['users'],$u).'</h4>'.join(', ',$v['users']).(isset($sscnt[$k]['user']) && $b<$sscnt[$k]['user'] ? ' ...' : '').'</div>' : '')
				.($g>0 ? '<div><h4>'.call_user_func(static::$lang['guests'],$g).'</h4>'.join(', ',$v['guests']).(isset($sscnt[$k]['guest']) && $b<$sscnt[$k]['guest'] ? ' ...' : '').'</div>' : '')
				.($b>0 ? '<div><h4>'.call_user_func(static::$lang['bots'],$b).'</h4>'.join(', ',$v['bots']).(isset($sscnt[$k]['bot']) && $b<$sscnt[$k]['bot'] ? ' ...' : '').'</div>' :'')
				.'</div>';
		}
		return$c;
	}

	/*
		¬сплывающее окно с подробной информацией об онлайн сессии
		$data - массив с ключами:
			type - тип сессии user, bot, guest
			enter - врем€ входа
			ip_guest - IP гост€
			ip_user - IP пользовател€
			info - массив с ключами
				r - ссылка на страницу, откуда пришел пользователь, либо false
				c - кодировки, поддерживаемые клиентским устройством
				e - форматы приема данных, поддерживаемые клиентским устройством
			service - название сервиса
			browser - USER_AGENT клиентского устройства
			location - местоположение пользовател€
			botname - им€ бота
			groups - группы пользовател€
			name - им€ пользовател€
			_gpref - HTML префикс группы пользовател€
			_gend - HTML окончание группы пользовател€
	*/
	public static function SessionDetail($data)
	{		$GLOBALS['title'][]=static::$lang['user_info'];
		$c='';
		$t=time();
		if($data)
		{			$ip=$data['ip_guest'] ? $data['ip_guest'] : $data['ip_user'];
			$loc=PROTOCOL.Eleanor::$domain.Eleanor::$site_path.htmlspecialchars($data['location'],ELENT,CHARSET,false);

			if($data['name'])
				$c.='<h1>'.$data['_gpref'].htmlspecialchars($data['name'],ELENT,CHARSET).$data['_gend'].'</h1><hr />';
			$c.='<ul style="list-style-type:none">
<li><b>IP</b> <a href="http://eleanor-cms.ru/whois/'.$ip.'" target="_blank">'.$ip.'</a></li>
<li><b>'.static::$lang['activity'].'</b> '.call_user_func(static::$lang['min_left'],floor(($t-strtotime($data['enter']))/60)).'</li>
<li><b>'.static::$lang['now_onp'].'</b> <a href="'.$loc.'" target="_blank" title="'.static::$lang['go'].'">'.$loc.'</a></li>
<li><b>'.static::$lang['browser'].'</b> '.htmlspecialchars($data['browser'],ELENT,CHARSET,false).'</li>
<li><b>'.static::$lang['service'].'</b> '.$data['service'].'</li>';

			foreach($data['info'] as $k=>&$v)
				if($v)
				{
					$v=htmlspecialchars($v,ELENT,CHARSET);
					$c.='<li><b>'.static::$lang[$k].'</b> ';
					switch($k)
					{
						case'ips':
							$ips='';
							foreach($v as $k_=>&$v_)
								$ips.=$k_.'='.$v_.', ';
							$ips=rtrim($ips,', ');
							$c.=$ips.'</li>';
						break;
						case'r':
							$c.='<a href="'.$v.'" target="_blank" title="'.static::$lang['go'].'">'.$v.'</a></li>';
						break;
						default:
							$c.=$v.'</li>';
					}
				}

			$c.='</ul>';
		}
		else
			$c.='<div style="text-align:center"><b>'.static::$lang['session_nf'].'</b></div>';
		return Eleanor::$Template->SimplePage($c);
	}}
TplUsersOnline::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/users-*.php',false);