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
class TplComments
{	public static
		$lang=array();	/*
		Элемент шаблона: отображение комментариев
		$rights - массив прав пользователя в комментариях, ключи:
			edit - право редактировать свои комментарии. Если число - это количество секунд, по истечению которых после написания комментария право теряется.
			delete - право удалять свои комментарии. Если число - это количество секундпо истечению которых после написания комментария право теряется.
			post - право создавать новые комментарии, свойство определяет статус новых комментариев: -1 - для перемодерации, 0 - для блокировки, 1 - без премодерации, false - для запрета публикации
			medit - Право редактировать чужие комментарии
			mdelete - Право удалять чужие комментарии
			ip - Право просматривать IP с которых были отправлены комментарии
			status - право менять статусы постов

		$pagpq  - от posts+authors+groups+parent+quotes массив значений:
			posts - массив комментариев. Формат: id=>array(), ключи внутреннего массива:
				status - статус комментария
				parents - массив ID всех родителей комментария
				date - дата комментариея
				answers - число ответов на данный комментарий
				author_id - идентификатор автора комментария
				author - имя автора комментария
				ip (при наличии прав) - IP адрес, с которого был оставлен комментарий
				_n - порядковый номер комментария
				_afind - ссылка на комментарий
				_achilden - ссылка на ветку данного комментария
				_edit - флаг возможности редактирования комментария
				_delete - флаг возможности удаления комментария
			authors - массив авторов всех комментариев. Формат id=>array(), ключи внутреннего массива:
				_group - идентификатор группы автора
				name - имя автора (не безопасный HTML)
				signature - подпись автора
				avatar_location - местоположения аватара
				avatar_type - тип аватара (uploaded,url,local)
				_online - флаг наличия пользователя онлайн
			groups - массив групп авторов всех комментариев. Формат id=>array(), ключи внутреннего массива:
				title - название группы
				html_pref - HTML префикс группы
				html_end - HTML окончание группы
			parent - массив родительского комментария, ключи:
				id - идентификатор комментария
				описание остальных ключей (status, parents, date, answers, author_id, author, ip, text, _edit, _delete, _afind, _n) смотрите выше.
			quotes - массив цитат из родительских комментариев (комментариев, ответом на которые, является текущий комментарий). Формат id=>text.
				Цитаты отсортированы в родительском порядке (комментарии 1 и 2):
					Комментарий 1
						Комментарий 2: Ответ на комментарий 1:
								Комментарий 3 (текущий комментарий, не входит в список цитаты): Ответ на комментарий 2
				Каждая цитата содержит в себе строку <!-- SUBQUOTE --> для вставки подцитаты.
		$postquery - этот массив параметров должен быть передан в $_POST запросе при ajax запросе
		$dataquery - содержимое ajax-запроса должно быть передано методом POST в этих ключах
		$cnt - количество комментариев всего
		$pp - количество комментариев на страницу
		$page - номер текущей страницы на которой мы находимся
		$pages - количество страниц всего
		$statuses - массив количества комментариев каждого статуса. Ключи массива - числовые выражения статуса комментариев
		$gname - имя гостя, если зашли под пользователем, эта переменная равна false
		$captcha - капча при написании комментария
		$links
			first_page - ссылка на первую страницу комментариев
			pages - функция-генератор ссылок на остальные страницы
	*/	public static function ShowComments($rights,$pagpq,$postquery,$dataquery,$cnt,$pp,$page,$pages,$statuses,$gname,$captcha,$links)
	{		array_push($GLOBALS['jscripts'],'js/eleanor_comments.js','js/eleanor_comments-'.Language::$main.'.js');
		$editor='';
		if($rights['post']!==false)
		{			$Lst=Eleanor::LoadListTemplate('table-form');			$editor.=
				($rights['post']==-1 ? Eleanor::$Template->Message(static::$lang['needch'],'info') : '')
				.$Lst->form(array('id'=>'newcomment'))->begin()
				.'<tr class="infolabel first"><td colspan="2" class="answerto">'.static::$lang['addc'].'</td></tr>'
				.($gname===false ? '' : $Lst->item(static::$lang['yn'],Eleanor::Edit('name',$gname,array('tabindex'=>1))))
				.$Lst->item(static::$lang['yc'],$GLOBALS['Eleanor']->Editor->Area('text','',array('bb'=>array('tabindex'=>2))))
				.($captcha ? $Lst->item(array(static::$lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>3)),'descr'=>static::$lang['captcha_'])) : '')
				.$Lst->button(Eleanor::Control('parent','hidden',$pagpq[3] ? $pagpq[3]['id'] : 0).Eleanor::Button(static::$lang['addc']))
				->end()->endform();
		}
		$reverse=$page<0;
		$pager=Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$reverse ? $pages : 1=>$links['first_page']),'C.GoToPage','comments');
		if($pagpq[3])
			Eleanor::LoadOptions('user-profile');		return Eleanor::$Template->Title(static::$lang['vc']).'<div id="comments">'
			.($rights['status'] ? '<div class="moderate"'.($pagpq[0] ? '>'.static::CommentsModerate($rights) : ' style="display:none">').'</div>' : '')
			.($pagpq[3] ? '<div class="parent">'.static::CommentsPost($rights,$pagpq[3]['id'],$pagpq[3],true,$pagpq[1],$pagpq[2],$pagpq[4],static::$lang).'</div>' : '')
			.'<div class="nocomments"'.($pagpq[0] ? ' style="display:none">' : '>'.Eleanor::$Template->Message($pagpq[3] ? static::$lang['anc'] : static::$lang['nc'],'info')).'</div>'
			.'<div class="comments'.($pagpq[3] ? ' children' : '').'"'.($pagpq[0] ? '>'.static::CommentsPosts($rights,$pagpq,static::$lang) : ' style="display:none">').'</div>'
			.'<div class="paginator"'.($pager ? '>'.$pager : ' style="display:none">').'</div>
			<div class="status" id="commentsinfo"></div><div style="text-align:center;margin-bottom:15px"><a href="#" class="link-button cb-lnc" style="width:250px"><b>'.static::$lang['lnp'].'</b></a></div>'
			.$editor.'</div><script type="text/javascript">/*<![CDATA[*/var C;$(function(){C=new CORE.Comments('.Eleanor::JsVars(array(
				'lastpost'=>time(),
				'postquery'=>$postquery,
				'!dataquery'=>'["'.join('","',$dataquery).'"]',
				'nextn'=>$statuses[1]+$statuses[0],
				'reverse'=>$reverse,
				'page'=>$page,
				'pages'=>$pages,
				'baseurl'=>$links['first_page'],
				'parent'=>$pagpq[3] ? (int)$pagpq[3]['id'] : 0,
			),false,true,'').')})//]]></script>';	}

	/*
		Элемент шаблона: "прослойка" при склеивании комментариев
		$diff - массив разницы текущего времени и ранее опубликованного комментария, ключи:
			0 - годы
			1 - месяцы
			2 - дни
			3 - часы
			4 - минуты
			5 - секунды
	*/
	public static function CommentsAddedAfter($diff)
	{		return'<br /><br /><span class="small">'.call_user_func_array(static::$lang['added_after'],$diff).':</span><br />';	}

	/*
		Элемент массива. Оформления цитаты
		$q - массив цитаты, ключи:
			name - имя пользователя
			date - дата цитаты
			find - ссылка на оригинальный комментарий
			id - идентификатор поста, который цитируется
			text - текст цитаты
	*/
	public static function CommentsQuote($q)
	{		return'<blockquote class="extend"><div class="top">'
		.sprintf(
			static::$lang['cite'],
			($q['name'] || $q['date'] ? ' ('.$q['name'].' @ '.$q['date'].')' : '')
			.($q['id'] ? ' <a href="'.$q['find'].'" data-id="'.$q['id'].'" class="cb-gocomment" target="_blank"><img src="'.Eleanor::$Template->default['theme'].'images/findpost.gif" /></a>' : '')
		)
		.'</div><div class="text">'.$q['text'].'</div></blockquote>';
	}

	/*
		Элемент шаблона: загрузка новых комментариев
		Описание входящих параметров смотрите в методе ShowComments (выше).
	*/
	public static function CommentsLNC($rights,$pagpq)
	{
		if($pagpq[3])
			Eleanor::LoadOptions('user-profile');
		return array(
			'moderate'=>$rights['status'] && $pagpq[0] ? static::CommentsModerate($rights) : '',
			'comments'=>$pagpq[0] ? static::CommentsPosts($rights,$pagpq) : '',
		);
	}

	/*
		Элемент шаблона: Загрузка страницы на AJAX.
		Описание входящих параметров смотрите в методе ShowComments (выше).
	*/
	public static function CommentsLoadPage($rights,$pagpq,$cnt,$pp,$page,$pages,$parent,$links)
	{
		$r=array('paginator'=>Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$page<0 ? $pages : 1=>$links['first_page']),'C.GoToPage','comments'));
		if($pagpq)
		{
			if($pagpq[0])
				$r['comments']=static::CommentsPosts($rights,$pagpq);
			else
			{
				$r['moderate']=$r['comments']='';
				$r['nocomments']=Eleanor::$Template->Message($parent ? static::$lang['anc'] : static::$lang['nc'],'info');
			}
		}
		return$r;
	}

	/*
		Элемент шаблона: форма редактирования комментария
		$a - массив редактируемого комментария, ключи:
			id - идентификатор комментария
			status - статус комментария
			date - дата комментария
			author_id - идентификатор автора комментария
			author - имя автора комментария
			text - текст комментария
	*/
	public static function CommentsEdit($a)
	{
		return'<form>'.$GLOBALS['Eleanor']->Editor->Area('text'.$a['id'],$a['text']).'<div style="text-align:center">'.Eleanor::Button(static::$lang['save']).' '.Eleanor::Button(static::$lang['cancel'],'button',array('class'=>'cb-cancel')).'</div></form>';
	}

	/*
		Элемент шаблона: текст комментария, после его сохранения (редактирования)
			$text - текст текущего комментария
			$quotes - массив цитат из родительских комментариев (комментариев, ответом на которые является текущий комментарий). Формат id=>text.
				Цитаты отсортированы в родительском порядке (комментарии 1 и 2):
					Комментарий 1
						Комментарий 2: Ответ на комментарий 1:
							Комментарий 3 (текущий комментарий, не входит в список цитаты): Ответ на комментарий 2
			Каждая цитата содержит в себе строку <!-- SUBQUOTE --> для вставки подцитаты.
	*/
	public static function CommentsAfterEdit($text,$quotes)
	{
		$pq='';
		if(count($quotes)>2)
			array_splice($quotes,-2);
		foreach($quotes as &$v)
			$pq=str_replace('<!-- SUBQUOTE -->',$pq,$v);
		$pq=str_replace('<!-- SUBQUOTE -->','',$pq);
		return$pq.$text;
	}

	protected static function CommentsModerate($rights)
	{
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		return Eleanor::Select('',Eleanor::Option(static::$lang['withsel'],'').Eleanor::Option(static::$lang['doact'],1).Eleanor::Option(static::$lang['toblock'],0).Eleanor::Option(static::$lang['tomod'],-1).($rights['mdelete'] ? Eleanor::Option(Eleanor::$Language['tpl']['delete'],'delete') : ''),array('class'=>'modevent')).' '.Eleanor::Check('',false,array('id'=>'masscheck'));
	}

	protected static function CommentsPosts($rights,$pagpq)
	{		$c='';
		if($pagpq[0] and !$pagpq[3])
			Eleanor::LoadOptions('user-profile');
		$mass=$rights['status'] || $rights['mdelete'];
		foreach($pagpq[0] as $k=>&$v)
			$c.=static::CommentsPost($rights,$k,$v,$mass,$pagpq[1],$pagpq[2],$pagpq[4]);		return$c;
	}

	protected static function CommentsPost($rights,$id,$c,$mass,$authors,$groups,$quotes)
	{		$la=static::$lang['answers'];		$ltpl=Eleanor::$Language['tpl'];		$author=isset($authors[$c['author_id']]) ? $authors[$c['author_id']] : false;
		$group=$author && isset($groups[$author['_group']]) ? $groups[$author['_group']] : false;
		if(!$author)
			$avatar='images/avatars/guest.png';
		else
			switch($author['avatar_location'] ? $author['avatar_type'] : '')
			{
				case'local':
					$avatar='images/avatars/'.$author['avatar_location'];
				break;
				case'upload':
					$avatar=Eleanor::$uploads.'/avatars/'.$author['avatar_location'];
				break;
				case'url':
					$avatar=$author['avatar_location'];
				break;
				default:
					$avatar='images/avatars/user.png';
			}
		static$maw,$mah;
		if(!isset($maw,$mah))
			list($maw,$mah)=explode(' ',Eleanor::$vars['avatar_size']);

		switch($c['status'])
		{
			case -1:
				$status='<span style="color:orange;font-weight:bold">'.static::$lang['stmodwait'].'</span>';
				$data['postn']='?';
			break;
			case 0:
				$status='<span style="color:red;font-weight:bold">'.static::$lang['stblocked'].'</span>';
			break;
			default:
				$status='';
		}
		$ip=$rights['ip'] ? '<a href="http://eleanor-cms.ru/whois/'.$c['ip'].'" target="_blank">'.$c['ip'].'</a> ' : '';

		$pq='';
		if(count($c['parents'])>2)
			array_splice($c['parents'],0,-2);
		foreach($c['parents'] as &$pv)
			$pq=isset($quotes[$pv]) ? str_replace('<!-- SUBQUOTE -->',$pq,$quotes[$pv]) : '';
		$pq=str_replace('<!-- SUBQUOTE -->','',$pq);
		return'<div class="comment" id="comment'.$id.'">
<div class="binner">
	<div class="avatarcol">
		<div class="lcolomn">
			<div class="avatarbox"><img style="max-width:'.$maw.';max-height:'.$mah.';" src="'.$avatar.'" title="'.$c['author'].'" />'.($author && $author['_online'] ? '<br /><span style="color:green">Online</span>' : '').'</div>
		</div>
		<div class="rcolomn">
			<div class="heading">
				<span class="argr">'
				.($c['_n'] ? '<a href="'.$c['_afind'].'" class="cb-findcomment">#'.($c['status'] ? $c['_n'] : '?').'</a>' : '')
				.($mass && in_array($c['status'],array(-1,0,1)) ? ' '.Eleanor::Check('mass[]',false,array('value'=>$id)) : '')
				.'</span><h2>'
				.Eleanor::$Language->Date($c['date'],'fdt').', '.($group ? '<a href="'.Eleanor::$Login->UserLink($author['name'],$c['author_id']).'" title="'.$group['title'].'" class="cb-insertnick">'.$group['html_pref'].$c['author'].$group['html_end'].'</a>' : '<span class="cb-insertnick">'.$c['author'].'</span>').' </h2>'
				.($status || $ip ? '<div class="moreinfo">'.$ip.$status.'<div class="clr"></div></div>' : '')
				.'</div>
			<div class="maincont"><div class="text">'.$pq.$c['text'].'</div>'
			.($author && $author['signature'] ? '<div class="clr"></div><p class="signature">-----<br />'.$author['signature'].'</p>' : '')
			.'</div>
		</div>
		<div class="clr"></div>
	</div>
	<div class="commentinfo buttons">'
		.($c['_achilden'] ? '<a href="'.$c['_achilden'].'#comments" class="answers">'.$la($c['answers']).'</a>' : '')
		.($c['status']==1 && $rights['post']
			? '<span class="argr"><a href="#" class="cb-qquote" data-id="'.$id.'" data-date="'.$c['date'].'" data-name="'.$c['author'].'">'.static::$lang['qquote'].'</a></span>'
				.(isset($c['_n']) ? '<span class="argr"><a href="#" class="cb-answer" data-id="'.$id.'">'.static::$lang['answer'].'</a></span>' : '')
			: '')
		.($c['_edit'] ? '<span class="argr"><a href="#" class="cb-edit" data-id="'.$id.'">'.$ltpl['edit'].'</a></span>' : '')
		.($c['_delete'] ? '<span class="argr"><a href="#" class="cb-delete" data-id="'.$id.'"'.(isset($c['_n']) ? '' : ' data-recount="1"').'>'.$ltpl['delete'].'</a></span>' : '')
		.'<div class="clr"></div>
	</div>
</div></div>';	}
}
TplComments::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/comments-*.php',false);