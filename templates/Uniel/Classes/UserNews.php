<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для пользователей модуля новости.

	Глобальные переменные:
		$GLOBALS['Eleanor']->Categories - объект категорий модуля, важные методы и свойства
			dump - массив всех категорий. Формат: id=>array(), ключи внутреннего массива:
				title - название категории
				description - описание категории
				image - имя картинки-логотипа категории, либо пусто
				parent - идентификатор категории-родителя
				parents - идентификаторы категорий-родителей, разделенных запятыми
			imgforlder - путь к каталогу с картинками-логотипами категории
			GetOptions() - метод возвращает <option>-ы (теги) для <select> структурированных категорий
			GetUri() - получение параметров массива параметров для Url->Construct()
		$GLOBALS['Eleanor']->module - массив параметров модуля
			tags - массив тегов модуля, каждый элемент которого - массив с ключами:
				_a - ссылка на материалы с этим тегом
				cnt - количество материалов с этим тегом
				name - название тега
			corn - ссылка на запуск крона новостей
			links - массив ссылок (меню модуля) с ключами:
				base - ссылка на главную модуля
				categories - ссылка на категории, либо false
				tags - ссылка на теги модуля, либо false
				search - ссылка на поиск материалов
				add - ссылка на добавление материалов, либо false
				my - ссылка на материалы пользователя (свои материалы), либо false
*/
class TplUserNews
{	/*
		Внутенний метод. Важный момент Cron
	*/	protected static function TopMenu($tit=false)
	{		$GLOBALS['jscripts'][]='js/module_publications.js';
		#Cron
		$cron=$GLOBALS['Eleanor']->module['cron'] ? '<img src="'.$GLOBALS['Eleanor']->module['cron'].'" style="width:1px;height1px;" />' : '';
		#[E] Cron

		if($_SERVER['QUERY_STRING']=='')
			return$cron;		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];		return Eleanor::$Template->Menu(array(
			'menu'=>array(
				array($links['base'],$lang['all']),
				$links['categories'] ? array($links['categories'],$lang['categs']) : false,
				$links['tags'] ? array($links['tags'],$lang['tags']) : false,
				array($links['search'],$lang['search'],'addon'=>array('rel'=>'search')),
				$links['add'] ? array($links['add'],$lang['add']) : false,
				$links['my'] ? array($links['my'],$lang['my']) : false,
			),
			'title'=>($tit ? $tit : $lang['n']).$cron,
		));
	}

	protected static function List_($data,$shst=false)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$T=clone Eleanor::$Template;
		foreach($data['items'] as $k=>&$v)
		{
			$ntags='';
			foreach($v['tags'] as &$tv)
				if(isset($data['tags'][$tv]))
					$ntags.='<a href="'.$data['tags'][$tv]['_url'].'">'.$data['tags'][$tv]['name'].'</a>, ';

			$status=false;
			if(isset($v['status']) and $shst)
				switch($v['status'])
				{					case-1:
						$status='<span style="font-weight:bold;color:darkyellow">'.$lang['waitmod'].'</span>';
					break;					case 1:
						$status='<span style="font-weight:bold;color:green">'.$lang['activated'].'</span>';
					break;
					default:
						$status='<span style="font-weight:bold;">'.$lang['deactivated'].'</span>';
				}

			$T->Base(array(
				'top'=>array(
					'published'=>sprintf($lang['published_'],Eleanor::$Language->Date($v['date'],'fdt')),
					'category'=>isset($data['cats'][$v['_cat']]) ? sprintf($lang['category_'],'<a href="'.$data['cats'][$v['_cat']]['_a'].'">'.$data['cats'][$v['_cat']]['t'].'</a>') : false,
					'comments'=>$lang['comments_']($v['comments'],'<a href="'.$v['_url'].'#comments">'.$v['comments'].'</a>'),
					'author'=>sprintf($lang['publisher_'],$v['author_id'] ? '<a href="'.Eleanor::$Login->UserLink($v['author'],$v['author_id']).'" rel="author">'.$v['author'].'</a>' : $v['author']),
				),
				'bottom'=>array(
					'readmore'=>$v['_readmore'] ? '<a href="'.$v['_url'].'#more">'.$lang['readmore'].'</a>' : false,
					'voting'=>$v['voting'] ? ' <a href="'.$v['_url'].'#voting">'.$lang['voting'].'</a>' : false,
					'status'=>$status,
					'rating'=>Eleanor::$vars['publ_rating'] && isset($data['rating'][$k]) ? $data['rating'][$k] : false,
					'edit'=>$v['_aedit'] ? Eleanor::$Template->EditDelete($v['_aedit'],$v['_adel']) : false,
				),
				'title'=>$v['_readmore'] ? '<a href="'.$v['_url'].'">'.$v['title'].'</a>'.($v['_hastext'] ? ' <a href="#" data-id="'.$k.'" data-more="#more-'.$k.'" class="getmore"></a>' : '') : $v['title'],
				'text'=>$v['announcement'].($v['_hastext'] ? '<div id="more-'.$k.'" style="display:none"></div>' : '').($ntags ? '<div class="tags">'.sprintf($lang['tags_'],rtrim($ntags,', ')).'</div>' : ''),
			));
		}
		return$T.'<script type="text/javascript">//<![CDATA[
$(function(){
	CORE.Publications.Init("div.base","'.$GLOBALS['Eleanor']->module['name'].'");
})//]]></script>';	}

	/*
		Список новостей на главной сайта и главной модуля
		$data - массив данных. Ключи:
			items - массив новостей. Формат: id=>array()
				date - дата публикации новости
				author - имя автора новости
				author_id - идентификатор автора новости
				status - статус новости (1 - активна, 0 - заблокирована, -1 - ожидает модерации)
				reads - число просмотров
				comments - число комментариев
				tags - массив идентификаторов тегов новости
				title - заголовок новости
				announcement - анонс новости
				voting`- флаг наличия опроса в новости

				_aedit - ссылка на редактирование новости, либо false
				_adel - ссылка на удаление новости, либо false
				_cat - идентификатор категории новости
				_readmore - флаг наличия подробной новости
				_hastext - флаг наличия подробного текста новости
				_url - ссылка на новость
			cats - массив категорий. Формат: id=>array()
				_a - ссылка на категорию
				t - название категории
			tags - массив тегов. Формат: id=>array(), ключи внутреннего массива:
				_url - ссылка на новости с тегом
				name - имя тега
				cnt - количество новостей с данным тегом
			rating - массив результирующего кода рейтинга новостей. Формат: id=>код рейтинга
		$cnt - количество новостей всего
		$page - номер страницы, на которой мы сейчас находимся
		$pp - число новостей на страницу
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
	*/
	public static function ShowList($data,$cnt,$page,$pp,$links)
	{		return static::TopMenu().static::List_($data).Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page);
	}

	/*
		Страница вывода новостей за определенную дату
		$data - дата
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - формат ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function DateList($date,$data,$cnt,$page,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title'])).self::List_($data).Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page,$links['pages']);
	}

	/*
		Страница вывода новостей пользователя (своих)
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - формат ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function MyList($data,$cnt,$page,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title'])).self::List_($data,false).Eleanor::$Template->Pages(array($cnt,1=>$links['first_page']),$pp,$page,$links['pages']);
	}

	/*
		Страница вывода новостей определенной категории
		$category - данные категории, массив с ключами:
			id - идентификатор категории
			title - название категории
			description - описание категории
		Описание остальных переменных доступно в методе List
	*/
	public static function CategoryList($category,$data,$cnt,$page,$pp,$links)
	{		return self::ShowCategories($category['id']).self::List_($data)
			.Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page,$links['pages']);
	}

	/*
		Страница вывода всех категорий
	*/
	public static function ShowCategories($cat=0)
	{		$dump=&$GLOBALS['Eleanor']->Categories->dump;		if(isset($dump[$cat]))
		{			$way=$dump[$cat]['parents'] ? explode(',',rtrim($dump[$cat]['parents'],',')) : array();
			foreach($way as $k=>&$v)
				if(isset($dump[$v]))
					$v=array(
						$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($v),true,false),
						$dump[$v]['title'],
					);				else
					unset($way[$k]);
			if($way)
			{				$w='<span class="cat1">';				foreach($way as $v)
					$w='<a href="'.$v[0].'">'.$v[1].'</a> &raquo; ';
				$w.='</span><hr />';			}
			else
				$w='';			$c=$w.'<table style="width:100%"><tr>'
				.($dump[$cat]['image'] ? '<td><img src="'.$GLOBALS['Eleanor']->Categories->imgfolder.$dump[$cat]['image'].'" alt="'.$dump[$cat]['title'].'" title="'.$dump[$cat]['title'].'" /></td>' : '')
				.'<td><td><h2 class="title">'.$dump[$cat]['title'].'</h2>'.$dump[$cat]['description'].'</td></tr></table>';		}
		else
			$c='';
		$cols=3;#Количество колонок категорий

		$w=round(100/$cols);
		$num=$cols;
		$iscats=true;
		$subcat=-1;
		$subcatsb=true;
		foreach($dump as $k=>&$v)
			switch($v['parent'])
			{				case$cat:
					if($iscats)
					{
						$c.=($cat ? '<hr />' : '').'<table class="categories">';
						$iscats=false;
					}

					if($subcat>0)
					{						if(!$subcatsb)
							$c.='</ul>';
						$c.='</td>';					}
					if($num==0)
					{
						$c.='</tr>';
						$num=$cols;
					}
					if($num==$cols)
						$c.='<tr>';

					$c.='<td style="width:'.$w.'%"><table><tr>'
						.($v['image'] ? '<td><img src="'.$GLOBALS['Eleanor']->Categories->imgfolder.$v['image'].'" alt="'.$v['title'].'" title="'.$v['title'].'" /></td>' : '')
						.'<td><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($k),true,false).'"><strong>'.$v['title'].'</strong></a></td></tr></table>';

					$subcat=$k;
					$subcatsb=true;
					$num--;
				break;
				case$subcat:
					if($subcatsb)
					{						$c.='<ul>';
						$subcatsb=false;					}
					$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($k),true,false).'">'.$v['title'].'</a></li>';
				break;
			}
		if(!$iscats)
		{			if($subcat>0)
			{
				if(!$subcatsb)
					$c.='</ul>';
				$c.='</td>';
			}
			for(;$num>0;$num--)
				$c.='<td></td>';
			if($num==0)
				$c.='</tr>';
			$c.='</table>';		}
		return static::TopMenu().Eleanor::$Template->OpenTable().$c.Eleanor::$Template->CloseTable();	}

	/*
		Страница вывода всех тегов
	*/
	public static function ShowAllTags()
	{		$tags=clone Eleanor::$Template;
		foreach($GLOBALS['Eleanor']->module['tags'] as &$v)
			$tags->Tag($v);
		return static::TopMenu(Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['tags_list']).Eleanor::$Template->OpenTable().'<span class="alltags">'.$tags.'</span>'.Eleanor::$Template->CloseTable();
	}

	/*
		Страница вывода новостей за определенную дату
		$data - дата
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - формат ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function TagsList($tag,$data,$cnt,$page,$pp,$links)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::TopMenu(sprintf($lang['wt'],$tag['name']))
			.($data['items'] ? self::List_($data).Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page,$links['pages']) : Eleanor::$Template->Message(sprintf($lang['notag'],$tag['name']),'info'));	}

	/*
		Страница поиска новостей
		$values - значение полей поиска формы, массив с ключами:
			text - поисковый запрос
			where - где искать: в заголовке, в заголовке и анонсе, в заголовке, анонсе и тексте (t,ta,tat)
			tags - массив тегов
			categs - массив категорий
			sort - порядок сортировки (date,relevance)
			c - поиск в массиве категорий И или ИЛИ (and,or)
			t - поиск в массиве тегов И или ИЛИ (and,or)
		$error - ошибка, если пустая, значит ошибки нет
		$md - идентификатор поиска
		$tags - массив тегов, формат id=>имя тега
		Описание остальных переменных доступно в методе List
	*/
	public static function Search($values,$error,$md,$tags,$data,$cnt,$page,$pp)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];		$tagopts='';
		foreach($tags as $k=>&$v)
			$tagopts.=Eleanor::Option($v,$k,in_array($k,$values['tags']));
		$Lst=Eleanor::LoadListTemplate('table-form');

		if($data and $data['items'])
		{			if($values['text'])
			{				$mw=preg_split('/\s+/',$values['text']);
				foreach($data['items'] as &$v)
				{
					$v['title']=Strings::MarkWords($mw,$v['title']);
					$v['text']=Strings::MarkWords($mw,$v['text']);
				}
			}
			$results='<br /><br />'.self::List_($data).Eleanor::$Template->Pages($cnt,$pp,$page,$GLOBALS['Eleanor']->Url->Construct(array('do'=>'search','md'=>$md,array('page'=>'{page}')),true,''));
		}
		else
			$results='';
		return static::TopMenu($lang['lookfor'])
			.($error ? Eleanor::$Template->Message($error,'error') : '')
			.($cnt===0 ? Eleanor::$Template->Message($lang['notfound'],'info') : '')
			.'<form method="post">'
			.$Lst->begin()
				->item($lang['text'],Eleanor::Edit('text',$values['text']))
				->item($lang['swhere'],Eleanor::Select('where',Eleanor::Option($lang['title'],'title',$values['where']=='t').Eleanor::Option($lang['ta'],'ta',$values['where']=='ta').Eleanor::Option($lang['tat'],'tat',$values['where']=='tat')))
				->item($lang['categs'],Eleanor::Items('categs',$GLOBALS['Eleanor']->Categories->GetOptions($values['categs'])).'<br /><label>'.Eleanor::Radio('c','and',$values['c']=='and').$lang['and'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>'.Eleanor::Radio('c','or',$values['c']=='or').$lang['or'].'</label>')
				->item($lang['tags'],Eleanor::Items('tags',$tagopts).'<br /><label>'.Eleanor::Radio('t','and',$values['t']=='and').$lang['and'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>'.Eleanor::Radio('t','or',$values['t']=='or').$lang['or'].'</label>')
				->item($lang['sortby'],Eleanor::Select('sort',Eleanor::Option($lang['sdate'],'date',$values['sort']=='date').Eleanor::Option($lang['srel'],'relevance',$values['sort']=='relevance')).'</label>')
				->button(Eleanor::Button($lang['find']))
				->end()
			.'</form>'
			.$results;
	}

	/*
		Страница подробного просмотра новости
		$a - массив новости, ключи:
			id - идентификатор новости в БД
			date - дата новости
			author - имя автора новости
			author_id - идентификатор автора новости
			status - статус новости (1 - активна, 0 - заблокирована, -1 - ожидает модерации)
			reads - число просмотров
			comments - число комментариев
			title - заголовок новости
			announcement - анонс новости
			text - подробный текст новости
			_aedit - ссылка на редактирование новости, либо false
			_adel - ссылка на удаление новости, либо false
			_cat - идентификатор основной категории новости
			_sokr - анонс новости
			_tags - массив всех тегов новосоти. Формат: id=>array(), ключи внутреннего массива:
				_a - ссылка на новости данного тега
				tag'=>$temp['name']),true,''),'name'=>$temp['name']);
		$category
			id - идентификатор категории
			title - название категории
			description - описание категории
			_a - ссылка на новости из данной категории
		$rating - HTML рейтинга новости
		$voting - HTML опроса новости, либо false
		$comments - HTML комментариев
		$hl - массив слов, которые необходимо подсветить в новости
	*/
	public static function Show($a,$category,$rating,$voting,$comments,$hl)
	{		if($hl)
		{			$a['title']=Strings::MarkWords($mw,$a['title']);
			$a['text']=Strings::MarkWords($mw,$a['text']);
			if($a['announcement'])
				$a['announcement']=Strings::MarkWords($mw,$a['announcement']);
		}
		$tags='';
		foreach($a['_tags'] as &$v)
			$tags.='<a href="'.$v['_a'].'">'.$v['name'].'</a>, ';

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		switch($a['status'])
		{
			case-1:
				$status='<span style="font-weight:bold;color:darkyellow">'.$lang['waitmod'].'</span>';
			break;
			case 0:
				$status='<span style="font-weight:bold;">'.$lang['deactivated'].'</span>';
			break;
			default:
				$status=false;
		}

		return static::TopMenu()
			.Eleanor::$Template->Base(array(
				'top'=>array(
					'published'=>sprintf($lang['published_'],Eleanor::$Language->Date($a['date'],'fdt')),
					'category'=>$category ? sprintf($lang['category_'],'<a href="'.$category['_a'].'">'.$category['title'].'</a>') : false,
					'author'=>sprintf($lang['publisher_'],$a['author_id'] ? '<a href="'.Eleanor::$Login->UserLink($a['author'],$a['author_id']).'">'.$a['author'].'</a>' : $a['author']),
					'reads'=>sprintf($lang['reads_'],$a['reads']),
				),
				'bottom'=>array(
					'status'=>$status,
					'rating'=>$rating ? $rating : false,
					'edit'=>$a['_aedit'] ? Eleanor::$Template->EditDelete($a['_aedit'],$a['_adel']) : false,
				),
				'title'=>$a['title'],
				'text'=>($a['announcement'] ? $a['announcement'].'<a id="more"></a>' : '').$a['text'].($tags ? '<div class="tags">'.sprintf($lang['tags_'],rtrim($tags,', ')).'</div>' : '').($voting ? '<a id="voting"></a>'.$voting : ''),
			))
			.$comments;	}
}