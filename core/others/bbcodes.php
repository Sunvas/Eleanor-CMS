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
class BBCodes extends BaseClass
{
	public static
		#Проверять корректность ссылок, мыл и т.п. Это полезно отключать, когда Вы хотите сохранить, допустим, формат письма. А потом просто заменять переменными текст.
		$checkout=true,

		#BB теги, подлежащие замене.
		$tags=array('b','p','i','s','a','q','li','ul','ol','em','tt','big','sub','sup','var','abbr','cite','code','spansmall','strong','noindex','legend','blockquote','span','small','address','option','optgroup','select','table','tr','td','th','thead','tfoot','tbody','caption','col','colgroup','legend','fieldset','object','param','article','aside','details','details','figcaption','figure','footer','header','hgroup','mark','nav','wbr','source','video','time','summary','section','ruby','rp','rt','progress','output');

	/**
	 * Преобразование текста, размеченного BB кодами в HTML разметку
	 *
	 * @param string $text Текст с BB разметкой
	 */
	public static function Save($text)
	{
		$text=static::ParseContainer($text,'[ul','[/ul]',array(__class__,'DoList'),true);
		$text=static::ParseContainer($text,'[ol','[/ol]',array(__class__,'DoList'),true);

		foreach(array('DoImage'=>'img','DoUrl'=>'url') as $k=>$v)
		{
			$ocp=-1;
			$cp=0;
			while(false!==$cp=stripos($text,'['.$v,$cp))
			{
				if($cp==$ocp)
				{
					++$cp;
					continue;
				}

				$tl=strlen($v);
				if(trim($text{$cp+$tl+1},'=] ')!='')
				{
					++$cp;
					continue;
				}
				$l=false;
				do
				{
					$l=strpos($text,']',$l ? $l+1 : $cp);
					if($l===false)
					{
						++$cp;
						continue 2;
					}
				}while($text{$l-1}=='\\');
				$ps=substr($text,$cp+$tl+1,$l-$cp-3-1);
				$ps=str_replace('\\]',']',$ps);
				if(false===$clpos=stripos($text,'[/'.$v.']',$l+1))
				{
					++$cp;
					continue;
				}

				$ct=substr($text,$l+1,$clpos-$l-1);
				$l=$clpos-$cp+$tl+3;#[/]

				$r=static::$k($ct,$ps);
				$text=substr_replace($text,$r,$cp,$l);
				$ocp=$cp++;
			}
		}

		$rk=array(
			'[c]',
			'[tm]',
			'[r]',
			'[s]',
			'[/s]',
			'[u]',
			'[/u]',
			"\t",
		);
		$r=array(
			'&copy;',
			'&#153;',
			'&reg;',
			'<span style="text-decoration:line-through;">',
			'</span>',
			'<span style="text-decoration:underline;">',
			'</span>',
			'&nbsp;&nbsp;&nbsp;&nbsp;',
		);

		$text=str_replace($rk,$r,$text);

		$rk=$r=array();

		$rk[]='#\[email([^\]]*?)\](.+?)\[/email\]#ie';
		$r[]='static::DoEmail(\'\2\',\'\1\')';

		$rk[]='#\[(left|right|center|justify)\](.+?)\[/\1\]#is';
		$r[]='<div style="text-align:\1"\>\2</div>';

		$rk[]='#\[size=(\d{1,}|xx-small|x-small|small|medium|large|x-large|xx-large)(px|pt|em)?;?\](.+?)\[/size\]#ies';
		$r[]='static::FontAttr(\'size\',\'\1\2\',\'\3\')';

		$rk[]='#\[background=([^\]]+)\](.+?)\[/background\]#ies';
		$r[]='static::FontAttr(\'background\',\'\1\',\'\2\')';

		$rk[]='#\[color=([^\]]+)\](.+?)\[/color\]#ies';
		$r[]='static::FontAttr(\'color\',\'\1\',\'\2\')';

		$rk[]='#\[font=([^\]]+)\](.+?)\[/font\]#ies';
		$r[]='static::FontAttr(\'font\',\'\1\',\'\2\')';

		$rk[]='/&amp;#(\d+?);/i';
		$r[]='&#\1;';

		$rk[]='/&#(\d+?)([^\d;])/';
		$r[]='&#\1;\2';

		$rk[]='#\[(hr|input|option)([^\]]*?)\]#is';
		$r[]='<\1\2 />';

		$text=preg_replace($rk,$r,$text);
		$bb=join('|',static::$tags);
		while(preg_match('#\[('.$bb.'|h1|h2|h3|h4|h5|h6)(\s+[^\]]+)?\].*?\[/\1\]#is',$text))
			$text=preg_replace('#\[('.$bb.'|h1|h2|h3|h4|h5|h6)(\s+[^\]]+)?\](.*?)\[/\1\]#is','<\1\2>\3</\1>',$text);
		return nl2br($text);
	}

	/**
	 * Преобразование HTML разметки в текст, текст размеченный BB кодами
	 *
	 * @param string $text Текст с HTML разметкой
	 */
	public static function Load($text)
	{
		$text=self::ParseContainer($text,'<ul','</ul>',array(__class__,'UnDoList'),true);
		$text=self::ParseContainer($text,'<ol','</ol>',array(__class__,'UnDoList'),true);

		$rk[]='#<a([^>]+?)>(.+?)</a>#e';
		$r[]='static::UnDoUrl(\'\2\',\'\1\')';

		$rk[]='#<div align="(left|right|center|justify)">(.+?)</div>#si';
		$r[]='[\1]\2[/\1]';

		$rk[]='#<(p|div) style="text-align:\s*(left|right|center|justify);?">(.+?)</\1>#si';
		$r[]='[\2]\3[/\2]';

		$rk[]='#<span style="text\-decoration:\s*line-through;?">(.+?)</span>#si';
		$r[]='[s]\1[/s]';

		$rk[]='#<span style="text\-decoration:\s*underline;?">(.+?)</span>#si';
		$r[]='[u]\1[/u]';

		$rk[]="#(<br>|<br />)[\r\n]*#i";
		$r[]="\n";

		$rk[]='#<span style="font-size:\s*(\d{1,}|xx-small|x-small|small|medium|large|x-large|xx-large)(px|pt|em)?;?">(.+?)</span>#s';
		$r[]='[size=\1\2]\3[/size]';

		$rk[]='#<span style="font-family:\s*(.+?)">(.+?)</span>#s';
		$r[]='[font=\1]\2[/font]';

		$rk[]='#<span style="color:\s*([^"]+?)">(.+?)</span>#s';
		$r[]='[color=\1]\2[/color]';

		$rk[]='#<span style="background-color:\s*(.+?)">(.+?)</span>#s';
		$r[]='[background=\1]\2[/background]';

		$rk[]='#<img([^>]+?)>#e';
		$r[]='static::UnDoImage(\'\1\')';

		$rk[]='#<(hr|input|option)(\s+[^>]+?)?>#ise';
		$r[]='\'[\1\'.rtrim(\'\2\',\' /\').\']\'';

		$text=preg_replace($rk,$r,$text);

		$bb=join('|',static::$tags);
		while(preg_match('#<('.$bb.'|h1|h2|h3|h4|h5|h6)(\s+[^>]+)?>.*?</\1>#is',$text))
			$text=preg_replace('#<('.$bb.'|h1|h2|h3|h4|h5|h6)(\s+[^>]+)?>(.*?)</\1>#is','[\1\2]\3[/\1]',$text);

		$rk=array(
			'&copy;',
			'&#153;',
			'&reg;',
			'&nbsp;&nbsp;&nbsp;&nbsp;',
		);
		$r=array(
			'[c]',
			'[tm]',
			'[r]',
			"\t",
		);
		return str_replace($rk,$r,$text);
	}

	/**
	 * Обработка контейнера в тексте
	 *
	 * Простой пример. Есть текст: '[quote]Первая цитатая[quote]Цитата в цитате[/quote][/quote]';
	 * сли мы будем пытаться обработать цитату при помощи регулярки '#\[quote([^\]]*)\](.*)\[/quote\]#Use'=>'DoQuote(\'\2\',\'\1\')',
	 * то получим следующее:
	 *    |------------Первая цитатая------------------|
	 *    |                     |-----Вторая цитата----|-------|
	 * '[quote]Первая цитатая [quote]Цитата в цитате[/quote][/quote]';
	 * Текущий метод метод позволяет добиться корректной обработки цитаты:
	 *    |------------Вторая цитатая--------------------------|
	 *    |                     |-----Первая цитата----|       |
	 * '[quote]Первая цитатая [quote]Цитата в цитате[/quote][/quote]';
	 *
	 * @param string $s Входящий текст с контейнером
	 * @param string $beg Начало контейнера
	 * @param string $eb Конец контейнера
	 * @params callable $cb Функция которой будет передана строка для обработки, содержащая начало и содержимое контейнера, но не содержащая его конец
	 */
	public static function ParseContainer($s,$beg,$end,$cb)
	{
		$bl=strlen($beg);
		$el=strlen($end);
		while(false!==$bp=strpos($s,$beg) and false!==$ep=strpos($s,$end,$bp+1+$bl))
		{
			$brp=strrpos(substr($s,0,$ep-1),$beg);
			if($brp>$bp)
				$bp=$brp;
			$ns=substr($s,$bp,$ep-$bp);
			$ns=call_user_func($cb,$ns);
			$s=substr_replace($s,$ns,$bp,$ep-$bp+$el);
		}
		return$s;
	}

	/**
	 * Внутренний метод создания картинки
	 *
	 * @param string $url Адрес картинки
	 * @param string $params Необработанная строка параметров картинки
	 */
	protected static function DoImage($url,$params)
	{
		$url=stripslashes($url);
		$params=Strings::ParseParams(stripslashes($params),'url');
		$tparams=array();
		foreach($params as $k=>$v)
		{
			$v=str_replace('"','&quot;',$v);
			switch(strtolower($k))
			{
				case'border':
					$v=abs((int)$v);
					if($v>5)
						$v=5;
					$tparams['border']=' border="'.$v.'"';
				break;
				case'alt':
					$tparams['alt']=' alt="'.$v.'" title="'.$v.'"';
				break;
				case'id':
				case'class':
				case'style':
				case'width':
				case'height':
					$tparams[$k]=' '.$k.'="'.$v.'"';
				break;
				case'url':
					$url=str_replace('"','&quot;',$url);
					$tparams['alt']=' alt="'.$url.'" title="'.$url.'"';
					$url=$v;
			}
		}
		return'<img src="'.$url.'"'.join($tparams).' />';
	}

	/**
	 * Внутренний метод создания списка
	 *
	 * @param string $text Предварительно размеченный bb кодами списоков текст
	 */
	protected static function DoList($text)
	{
		if(preg_match('#^\[(ul|ol)([^\]]*)\](.+)$#is',$text,$m)==0)
			return '';
		$type=strtolower($m[1]);
		$params=$m[2];
		$text=trim($m[3]);
		$tparams=array();
		$params=Strings::ParseParams($params);
		foreach($params as $k=>&$v)
			switch(strtolower($k))
			{
				case'id':
				case'class';
				case'style';
				case'title';
					$tparams[$k]=' '.$k.'="'.str_replace('"','&quot;',$v).'"';
			}
		if(strpos($text,'[*]')==0)
		{
			$text=str_replace('[*]','</li><li>',$text);
			$text=preg_replace('#^</li>#','',$text);
			$text=preg_replace("#(\r)?(\n)?</li>#",'</li>',$text.'</li>');
		}
		else
			$text='<li>'.$text.'</li>';
		return'<'.$type.join($tparams).'>'.$text.'</'.$type.'>';
	}

	/**
	 * Внутренний метод создания ссылок
	 *
	 * @param string $text Текст ссылки
	 * @param string $params Необработанная строка параметров ссылки
	 */
	protected static function DoUrl($text,$params='')
	{
		if(is_string($params))#На случай, если мы обратимся из функции DoEmail
			$params=Strings::ParseParams($params,'href');
		if(isset($params['name']))
		{
			unset($params['href'],$params['target']);
			$tparams=array();
		}
		else
		{
			if(!isset($params['href']))
			{
				$params['href']=$text;
				if(strlen($text)>55)
					$text=substr($text,0,35).'...'.substr($text,-15);
			}
			if(static::$checkout and stripos($params['href'],PROTOCOL.Eleanor::$domain.Eleanor::$site_path)===0)
				$params['href']=substr($params['href'],strlen(PROTOCOL.Eleanor::$domain.Eleanor::$site_path));
			$tparams=array('target'=>' target="_blank"');
		}
		foreach($params as $k=>$v)
			switch(strtolower($k))
			{
				case'id':
				case'name':
				case'class':
				case'style':
				case'title':
				case'target':
				case'href':
				case'rel':
					$tparams[$k]=' '.$k.'="'.htmlspecialchars($v,ELENT,CHARSET,false).'"';
				break;
				case'self':
					unset($tparams['target']);
			}
		return'<a'.join($tparams).' />'.$text.'</a>';
	}

	/**
	 * Внутренний метод создания ссылок на e-mail
	 *
	 * @param string $text Текст ссылки
	 * @param string $params Необработанная строка параметров ссылки
	 */
	protected static function DoEmail($text,$params='')
	{
		$text=stripslashes($text);
		$params=Strings::ParseParams(stripslashes($params),'href');
		if(!isset($params['href']))
			$params['href']=$text;
		$params['href']='mailto:'.preg_replace('#^mailto:#','',$params['href']);
		return static::DoUrl($text,$params);
	}

	/**
	 * Внутренний метод создания выделения текста определенным цветом
	 *
	 * @param string $param Название параметра, который будет настроен в тексте: size - размер, background - фон, color - цвет, font - шрифт
	 * @param string $value Значение параметра настройки
	 * @param string $text Текст для настройки
	 */
	protected static function FontAttr($param,$value,$text)
	{
		$text=stripslashes($text);
		if(static::$checkout)
			$value=preg_replace('/[^#a-z0-9\-;,\)\( ]/i','',$value);
		if($param=='size')
		{
			$pt='pt';
			if(preg_match('#(pt|px|em);?$#i',$value,$m))
				$pt=strtolower($m[1]);
			$value=(int)$value;
			if($value>32)
				$value=32;
			return'<span style="font-size:'.$value.$pt.'">'.$text.'</span>';
		}
		if($param=='background')
			return'<span style="background-color:'.$value.'">'.$text.'</span>';
		if($param=='color')
			return'<span style="color:'.$value.'">'.$text.'</span>';
		if($param=='font')
			return'<span style="font-family:'.$value.'">'.$text.'</span>';
	}

	/**
	 * Внутренний метод преобразования списка, размеченного на HTML в список, размеченный BB кодами
	 *
	 * @param string $text HTML размеченного списка
	 */
	protected static function UnDoList($text)
	{
		if(preg_match('#^<(ul|ol)([^>]*)>(.+)$#is',$text,$m)==0)
			return'';
		$type=strtolower($m[1]);
		$params=$m[2];
		$text=trim($m[3]);
		$params=Strings::ParseParams($params);
		$tparams='';
		foreach($params as $k=>&$v)
		{
			$q='';
			if(strpos($v,'"')!==false)
				$q='\'';
			elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
				$q='"';
			$tparams.=' '.$k.'='.$q.$v.$q;
		}
		$text=str_replace(array('<li>','</li>'),array("\n[*]",''),stripslashes($text));
		return'['.$type.ltrim($tparams).']'.$text."\n[/".$type.']';
	}

	/**
	 * Внутренний метод преобразования HTML ссылок в ссылки на BB кодах
	 *
	 * @param string $text Текст ссылки
	 * @param string $params Необработанная строка параметров ссылки
	 */
	protected static function UnDoUrl($text,$params)
	{
		$text=stripslashes($text);
		$params=Strings::ParseParams(stripslashes($params));
		$tag='url';
		$params_a=isset($params['name']) ? array() : array('self'=>' self');
		if(isset($params['href']) and stripos($text,PROTOCOL.Eleanor::$domain.Eleanor::$site_path)===0 and $params['href']==substr($text,strlen(PROTOCOL.Eleanor::$domain.Eleanor::$site_path)))
			unset($params['href']);
		$ta='';
		foreach($params as $k=>$v)
		{
			$q='';
			if(strpos($v,'"')!==false)
				$q='\'';
			elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
				$q='"';
			switch(strtolower($k))
			{
				case'target':
					if($v=='_blank')
						unset($params_a['self']);
					else
						$params_a[$k]=' target='.$v;
				break;
				case'href':
					if(strpos($v,'mailto:')===0)
					{
						$tag='email';
						$v=preg_replace('#^mailto:#','',$v);
					}
					if($v==$text)
						continue;
					$ta.='='.$q.$v.$q;
				break;
				default:
					$params_a[$k]=' '.$k.'='.$q.$v.$q;
			}
		}
		#Для того, чтобы параметр self был всегда последним
		if(isset($params_a['self']))
		{
			unset($params_a['self']);
			$params_a['self']=' self';
		}
		return'['.$tag.$ta.join($params_a).']'.$text.'[/'.$tag.']';
	}

	/**
	 * Внутренний метод преобразования HTML картинок в картинки на BB кодах
	 *
	 * @param string $params Необработанная строка параметров картинки
	 */
	protected static function UnDoImage($params)
	{
		$params=Strings::ParseParams(stripslashes($params));
		$iparams=array();
		foreach($params as $k=>$v)
		{
			$q='';
			if(strpos($v,'"')!==false)
				$q='\'';
			elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
				$q='"';
			switch(strtolower($k))
			{
				case'border':
					$v=(int)$v;
					if($v>5)
						$v=5;
					if($v>0)
						$iparams['border']=' border='.$v;
				break;
				case'alt':
				case'title':
					if($v!='')
						$iparams['alt']=' alt='.$q.$v.$q;
				break;
				case'src':
				break;
				default:
					$iparams[$k]=' '.$k.'='.$q.$v.$q;
			}
		}
		return'[img'.join($iparams).']'.$params['src'].'[/img]';
	}
}