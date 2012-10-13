<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Файл частоиспользуемых элементов шаблона
*/
class TplIndex
{	/*
		Элемент шаблона: листалка страниц

		$a - массив параметров или число страниц. В случае передачи массива (что иногда крайне удобно) ключи:
			0 - количество элементов
			pp - количество элементов на страницу
			page - номер текущей страницы, где мы сейчас находимся
			href - массив для генератора ссылок Url, либо строка с участком {page} (этот участок может быть и в массиве)
			ajax - название функции, куда будет передан запрос
			adata - массив дополнительных данных для AJAX запроса
			all - флаг вывода всех страниц
			name - имя параметра страницы для динамических ссылок (...&page=1)
			gap - число видимых страниц при листании слева и справа от текущей (при выводе не всех страниц)
			hash - окончание ссылок для страниц после знака #
		Эти же параметры можно передать в функцию в качестве параметров
			$a,$pp=1,$page=1,$href=array(),$ajax=false,$adata=array(),$all=false,$name='page',$gap=4
	*/	public static function Pages($a,$pp=1,$page=1,$href=array(),$ajax=false,$adata=array(),$all=false,$name='page',$gap=4)
	{		$ltpl=Eleanor::$Language['tpl'];		if(!is_array($a))
			$a=array($a);
		$a+=array(
			'pp'=>$pp,
			'page'=>$page,
			'href'=>$href,
			'ajax'=>$ajax,
			'adata'=>$adata,
			'all'=>$all,
			'name'=>$name,
			'gap'=>$gap,
			'hash'=>false,
		);
		$h=$a['hash'] ? '#'.$a['hash'] : '';
		$pages=ceil($a[0]/$a['pp']);

		if($reverse=$a['page']<0)
			$a['page']=-$a['page'];

		if($pages>1)
		{			$js=static::PageUrl($a,'{page}');
			$js=str_replace('%7Bpage%7D','{page}',$js);
			$result[]='<a href="#"'.($a['ajax'] ? '' : ' onclick="CORE.JumpToPage(\''.$js.$h.'\','.$pages.');return !1;"').' title="'.$ltpl['goto_page'].'"><img src="'.Eleanor::$Template->default['theme'].'images/gotopage.png" alt="" /></a>';
			if(strpos($js,'{page}')!==false)
				if($reverse)
				{
					if($a['page']<$pages)
						$GLOBALS['head']['prev']='<link rel="prev" href="'.static::PageUrl($a,$a['page']+1).'" />';
					if($a['page']>1)
						$GLOBALS['head']['next']='<link rel="next" href="'.static::PageUrl($a,$a['page']-1).'" />';
				}
				else
				{
					if($a['page']>1)
						$GLOBALS['head']['prev']='<link rel="prev" href="'.static::PageUrl($a,$a['page']-1).'" />';
					if($a['page']<$pages)
						$GLOBALS['head']['next']='<link rel="next" href="'.static::PageUrl($a,$a['page']+1).'" />';
				}
			$i=$reverse ? $pages : 1;
			for(;;)
			{
				if($i<1 or $i>$pages)
					break;
				if($i==$a['page'])
					$result[]='<span>'.$i.'</span>';
				elseif($all or ($i<=$a['gap'] or $i>($pages-$a['gap']) or $i>=($a['page']-$a['gap']) and $i<=($a['page']+$a['gap'])))
					$result[]='<a href="'.static::PageUrl($a,$i).$h.'" data-page="'.$i.'">'.$i.'</a>';
				else
				{
					$result[]='<span class="numbersmore">...</span>';
					if($i>($a['page']+$a['gap']))
						$i=$reverse ? $a['page']+$a['gap'] : $pages-$a['gap']+1;
					else
						$i=$reverse ? $a['gap'] : $a['page']-$a['gap'];
					continue;
				}
				if($reverse)
					$i--;
				else
					$i++;
			}
			$u=uniqid('nu-');
			return'<div class="numbers"'.($a['ajax'] ? ' id="'.$u.'"' : '').'><b>'.$ltpl['pages'].' </b>'.implode(' ',$result).'</div>'.($a['ajax'] ? '<script type="text/javascript">//<![CDATA[
$(function(){	$("#'.$u.' a").click(function(){		var p=$(this).data("page"),
			d='.($a['adata'] ? Eleanor::JsVars($a['adata'],false,true) : 'false').';
		if(p)			'.$a['ajax'].'(p,d);
		else
			CORE.JumpToPage(function(s){'.$a['ajax'].'(s,d)},'.$pages.',d);
		return false;
	});
})//]]></script>' : '');
		}	}

	private static function PageUrl($a,$p)
	{		$h=isset($a[$p]) ? $a[$p] : $a['href'];		if(is_array($h))
			return$GLOBALS['Eleanor']->Url->Construct($h+array($a['name']=>array($a['name']=>$p)));
		return is_object($h) ? $h($p) : $h;	}}