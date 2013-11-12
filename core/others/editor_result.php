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
class Editor_Result extends BaseClass
{
	public
		$type='bb',#Тип редактора, выбор - см конструктор класса.

		$smiles=true,#Разрещить использование смайлов
		$ownbb=true,#Разрешить использование своих ББ кодов? Полезно отключать, когда при помощи ББ редактора формируется письмо на отправку

		$editors=array(),#Перечень редакторов. Смотри в конструктор
		$visual=array(),#Перечень визуальных редакторов

		$imgalt='',#Автоматическое прописывание альтов к картинкам

		$checkout=true,#Проверять корректность ссылок, мыл и т.п. Это полезно отключать, когда Вы хотите сохранить, допустим, формат письма. А потом просто заменять переменными текст.
		$antilink,#(go|nofollow)Защита от прямых ссылок

		#Запрещенные теги. Они будут вырезаны после парсинга!
		$disabled=array('applet','meta','link','html','body','style','head','script','iframe','frame','frameset','base','!doctype'),#
		$enabled=array();#Разрешенные теги! Все остальные будут вырезаны!

	/**
	 * Конструктор. Здесь определяются поддерживаемые редакторы и флаги визуальных редакторов
	 */
	public function __construct()#ToDo! To trait & editor
	{
		$this->editors=array(
			'no'=>'textarea',
			'bb'=>'Eleanor BB Editor',
			'ckeditor'=>'CKEditor',
			'tinymce'=>'TinyMCE',
			'codemirror'=>'CodeMirror',
		);
		#Перечень визуальных редакторов
		$this->visual=array('ckeditor','tinymce');
		Eleanor::LoadOptions('editor');
		if($type=Eleanor::$Login->GetUserValue('editor'))
			$this->type=$type;
		else
			$this->type=Eleanor::$vars['editor_type'];

		$this->antilink=Eleanor::$vars['antidirectlink'];
	}

	/**
	 * Получение HTMLа после правки редактором
	 *
	 * @param string $name Имя редактора
	 * @param bool $isv Флаг получения указывающий, что $name не имя редактора, а полученный из него контент для обработки
	 * @param bool $save Флаг генерации результата для сохранения в БД, а не для вывода на экран. Разница в обработке ownbb кодов
	 */
	public function GetHTML($name,$isv=false,$save=true)
	{
		if(!$isv and !isset($_POST[$name]))
			return;
		$text=$isv ? (string)$name : (string)$_POST[$name];
		$text=str_replace("\r",'',$text);
		/*if(preg_match('#\[_[a-z_0-9\-]+_\]#',$text)>0)
		{
			preg_match_all('#\[_[a-z_0-9\-]+_\]#',$text,$m);
			$m=$m[0];
			$m[]='';
			$text=preg_split('#\[_[a-z_0-9\-]+_\]#',$text);
			foreach($text as $k=>&$v)
				$v=$this->GetHTML($v,true,$save).$m[$k];
			return join($text);
		}*/

		$text=static::CensorFilter($text);
		if($this->ownbb)
		{
			OwnBB::$opts['visual']=in_array($this->type,$this->visual);
			$text=OwnBB::StoreNotParsed($text,$save ? OwnBB::SAVE : OwnBB::SHOW);
		}
		switch($this->type)
		{
			case'bb':#ББ редактор
				$text=htmlspecialchars($text,ENT_NOQUOTES,CHARSET);
				#Fix для noparse своих ББ кодов
				$text=preg_replace('#&lt;!\-\- NP (\d+) \-\-&gt;#','<!-- NP \1 -->',$text);
				$text=BBcodes::Save($text);
			break;
			case'tinymce':
			case'ckeditor':
			break;
			case'codemirror':
			default:#Без ББ редактора
				$text=htmlspecialchars($text,ENT_NOQUOTES);
				#Fix для noparse своих ББ кодов
				$text=preg_replace('#&lt;!\-\- NP (\d+) \-\-&gt;#','<!-- NP \1 -->',$text);
		}
		$text=$this->SafeHtml($text);
		if($this->ownbb)
		{
			$au=Eleanor::$vars['autoparse_urls'];#Заплатка для запрета обработки автопарсинга урлов в тегах кода и т.п.
			Eleanor::$vars['autoparse_urls']=false;
			$text=OwnBB::ParseBBCodes($text,$save ? OwnBB::SAVE : OwnBB::SHOW);
			$text=OwnBB::ParseNotParsed($text,$save ? OwnBB::SAVE : OwnBB::SHOW);
			Eleanor::$vars['autoparse_urls']=$au;
		}
		return$text;
	}

	/**
	 * Преобразователь опасного HTML в безопасный (очищение HTML от XSS атак)
	 *
	 * @param string $s Строка опасного HTML
	 */
	public function SafeHtml($s)
	{
		$s=str_replace('<!-- NP2 ','<!-- ',$s);
		#Сначала идут комментарии, а только потом CDATA, потому что мы заменяем текст на комментарии!
		$sarr=array(
			array(
				'<!-- ',#И не вздумайте убирать пробелы! Вы же не хотите XSS типа <!--[if gte IE 4]>\br<SCRIPT>alert('XSS');</SCRIPT>\br<![endif]-->
				' -->'
			),
			array(
				'<![CDATA[',
				']]>'
			),
		);
		$n=0;
		$np=array();
		foreach($sarr as &$st)
		{
			$ocp=-1;
			$cp=0;
			while(false!==$cp=strpos($s,$st[0],$cp) and false!==$l=strpos($s,$st[1],$cp))
			{
				if($cp==$ocp)
				{
					++$cp;
					continue;
				}
				$el=strlen($st[1]);
				$l-=$cp-$el;
				$r='<!-- NP2 '.$n++.' -->';
				$tost=substr($s,$cp,$l);
				if(strpos($tost,'<!-- NP ')===0)#Это же комментарий из своих ББ кодов!
				{
					++$cp;
					continue;
				}
				$s=substr_replace($s,$r,$cp,$l);
				$np[]=array(
					'r'=>$r,
					't'=>$tost,
				);
				$ocp=$cp;
			}
		}

		#Убираем чередование тегов <<< и >>>
		$rf[]='#<+#';
		$rt[]='<';
		$rf[]='#>+#';
		$rt[]='>';
		$rf[]='#^([^<]*)>#m';#
		$rt[]='\1&gt;';
		$rf[]='#<([^>]*)$#m';#
		$rt[]='&lt;\1';
		$s=preg_replace($rf,$rt,$s);
		#Проверим теги

		#CheckTag Далее идет список тегов и характериистика их вложенности.

		#Список одиночных тегов
		$single=array('input','hr','br','img','image','param','area','embed','col','source','wbr');

		#Теги, закрытием которых обычно пренебрегают.
		$clforget=array('p','li','colgroup');

		#Строчные теги. Они могут находится друг в друге неограниченное число раз.
		$inline=array('a','abbr','address','span','small','i','b','s','em','strong','q','big','small','sup','sub','var','tt','cite','code','input','select','br','img');

		#Накладывает ограничения на вложенность тегов. Теги, не указанные в массиве справа, вложенные в тег слева, будут игнорироваться как неправильно вложенные.
		$children=array(
			'button'=>&$inline,
			'caption'=>&$inline,
			'th'=>&$inline,
			'h1'=>&$inline,
			'h2'=>&$inline,
			'h3'=>&$inline,
			'h4'=>&$inline,
			'h5'=>&$inline,
			'h6'=>&$inline,
			'nav'=>&$inline,
			'progress'=>&$inline,
			'rp'=>&$inline,
			'rt'=>&$inline,
			'meter'=>&$inline,
			'colgroup'=>array('col'),
			'table'=>array('caption','col','colgroup','tbody','thead','tfoot','tr'),
			'thead'=>array('tr'),
			'tfoot'=>array('tr'),
			'tbody'=>array('tr'),
			'tr'=>array('td','th'),
			'output'=>array(),
			'textarea'=>array(),
			'keygen'=>array(),
			'time'=>array(),
			'ul'=>array('li'),
			'ol'=>array('li'),
			'option'=>array(),
			'object'=>array('param'),
			'select'=>array('option','optgroup'),
			'optgroup'=>array('option'),
			'datalist'=>array('option'),
			'map'=>array('area'),
			'hgroup'=>array('h1','h2','h3','h4','h5','h6'),
			'video'=>array('sorce'),
			'audio'=>array('sorce'),
		);

		#Специальные теги, которые могу находится только внутри остальных тегов, причем ограниченное число раз. Значение каждого ключа и характеризует это. 0 - без ограничений.
		$parents=array(
			'th'=>array('tr'=>0),
			'td'=>array('tr'=>0),
			'tr'=>array('table'=>0,'thead'=>0,'tfoot'=>0,'tbody'=>0),
			'thead'=>array('table'=>1),
			'tfoot'=>array('table'=>1),
			'tbody'=>array('table'=>1),
			'caption'=>array('table'=>0),
			'colgroup'=>array('table'=>0),
			'col'=>array('table'=>0,'colgroup'=>0),
			'li'=>array('ul'=>0,'ol'=>0),
			'legend'=>array('fieldset'=>1),
			'param'=>array('object'=>0),
			'option'=>array('select'=>0,'optgroup'=>0,'datalist'=>0),
			'optgroup'=>array('select'=>0),
			'figcaption'=>array('figure'=>1),
			'rp'=>array('ruby'=>0),
			'rt'=>array('ruby'=>0),
			'summary'=>array('details'=>1),
			'source'=>array('video'=>0,'audio'=>0),
		);
		$n=0;
		$opened=$allowed=$used=array();
		#[E]CheckTag
		$offset=0;
		while(isset($s[$offset]) and false!==$ps=strpos($s,'<',$offset))
		{
			$to=$ps;#Tag offset=Position start
			$ft='';#Full tag
			while(false!==$pe=strpos($s,'>',$to))
			{
				$ft=substr($s,$ps+1,$pe-$ps-1);

				$qpo=0;
				$qp=$ft;
				while(true)
				{
					if(false===$qp=strpbrk(substr($qp,$qpo),'"\''))
						break 2;
					if(false===$qpo=strpos($qp,substr($qp,0,1),1))
					{
						$to=$pe+1;
						break;
					}
					$qpo++;
				}
			}
			#Наши комментарии мы не трогаем вообще
			if(strpos($ft,'!-- NP')!==0)
			{
				#CheckTag Эта функция не валидизирует код полностью! Т.е. она НЕ обезопасивает Вас от ошибок типа <table>ТЕКСТ</table>. Эта функция всего-лишь пытается убрать ошибочные теги
				do
				{
					$tag=ltrim($ft);
					$tag=rtrim($tag,' /	');
					if(!$tag)
						return'';
					if($closed=$tag[0]=='/')
						$tag=substr($tag,1);
					$tag=str_replace(array("\r","\n"),' ',$tag);
					if(preg_match('#^([a-z0-9]+)#i',$tag,$m)==0)
					{
						$rep='';
						break;
					}
					$opts=$m[1]==$tag || $closed ? '' : substr($tag,strlen($m[1])+1);
					$tag=strtolower($m[1]);

					if(in_array($tag,$this->disabled) or $this->enabled and !in_array($tag,$this->enabled) or $closed and !in_array($tag,$opened))
					{
						$rep='';
						break;
					}
					if($closed)
					{
						#Если передыдущий открытый тег оказался таким как этот - благополучно все закрываем.
						if($opened[$n]==$tag)
						{
							unset($opened[$n],$used[$n],$allowed[$n--]);
							$rep='</'.$tag.'>';
							break;
						}
						$rep='';
						#Если мы закрываем тег, который открыли ранее... - Закроем все предыдущеие теги.
						for($i=$n-1;$i>0;--$i)
							if($opened[$i]==$tag)
							{
								for($j=$n;$j>=$i;--$j)
								{
									$rep.='</'.$opened[$j].'>';
									unset($opened[$j],$allowed[$j]);
								}
								$n=$i-1;
								break;
							}
						break;
					}

					if(isset($parents[$tag]) and !isset($opened[$n],$parents[$tag][$opened[$n]]) or isset($opened[$n],$children[$opened[$n]]) and !in_array($tag,$children[$opened[$n]]) or isset($used[$n][$tag]) and !$used[$n][$tag] or isset($allowed[$n]) and !in_array($tag,$allowed[$n]))
					{
						$rep='';
						break;
					}

					if($opts)
					{
						$opts=Strings::ParseParams($opts);
						$opts=array_change_key_case($opts,CASE_LOWER);
						$opf='';
						switch($tag)
						{
							case'object':
								unset($opts['data']);
							break;
							case'a':
								if($this->antilink=='nofollow' and isset($opts['href']) and preg_match('#^[a-z]{3,7}://#i',$opts['href'])>0)
									$opts['rel']='nofollow';
							break;
							case'img':
							case'image':
								if(empty($opts['alt']))
									$opts['title']=$opts['alt']=$this->imgalt;
								if(!isset($opts['src']))
								{
									$rep='';
									break 2;
								}
						}
						foreach($opts as $k=>$v)
						{
							#Вырежем все события
							if(substr($k,0,2)=='on' or preg_match('#^[a-z0-9]+#',$k)==0)
								continue;
							if($k=='style')
							{
								while(preg_match('#/\*.*?\*/#s',$v)>0)
									$v=preg_replace('#/\*.*?\*/#s','',$v);
								/*@import, url и expression не разрешены в тегах style!
								Нельзя, чтобы контент из подписи отображался где-то вне своего места.*/
								$v=str_ireplace(array('expression','url','@import','position'),'na',$v);
							}
							if($this->checkout and ($k=='href' or $k=='src') and $v!='#')
								if(Strings::CheckUrl($v))
									$v=htmlspecialchars($v,ELENT,CHARSET,false);
								else
								{
									$rep='';
									break 2;
								}
							$v=str_replace(array('"','&39;','&lt;','&gt;'),array('&quot;','\''/*,'<','>'*/),$v);
							$v=str_ireplace('javascript:','jаvаsсript:',$v);
							$opf.=' '.$k.'="'.$v.'"';
						}
						$opts=$opf;
					}
					elseif(in_array($tag,array('a')))#Теги, невозможные без параметров
					{
						$rep='';
						break;
					}
					if(in_array($tag,$single))
					{
						$rep='<'.$tag.$opts.' />';
						break;
					}

					if(in_array($tag,$clforget) and isset($opened[$n]) and $opened[$n]==$tag)
					{
						$rep='</'.$tag.'><'.$tag.$opts.'>';
						break;
					}

					if($n>0)
					{
						#Тег caption может быть только сразу после тега <table>
						if($opened[$n]=='table' and $tag!='caption')
							$used[$n]['caption']=0;

						#Тег figcaption должен быть первым или последним в теге figure
						if($opened[$n]=='figure')
							if($tag=='figcaption' and isset($used[$n]['_figdone']))
								$allowed[$n]=array();
							else
								$used[$n]['_figdone']=true;

						#Тег summary должен идти первым внутри details.
						if($opened[$n]=='details' and $tag!='summary')
							$used[$n]['summary']=0;
					}
					$ch=isset($children[$tag]);
					if(in_array($tag,$inline) and !$ch)
					{
						$allowed[$n+1]=isset($allowed[$n]) ? array_intersect($inline,$allowed[$n]) : $inline;
					}
					elseif($ch)
						$allowed[$n+1]=$children[$tag];
					if(isset($used[$n][$tag]))
					{
						if($used[$n][$tag]>0)
							--$used[$n][$tag];
					}
					elseif(isset($parents[$tag]))
						foreach($parents[$tag] as $k=>&$v)
							if($k==$opened[$n] and $v>0)
								$used[$n][$tag]=$v-1;
					$opened[++$n]=$tag;
					$rep='<'.$tag.$opts.'>';
				}
				while(false);
				#[E]CheckTag

				$replen=strlen($rep);
				$ftlen=strlen($ft)+2;#Это < и >
				$s=substr_replace($s,$rep,$ps,$ftlen);
			}
			else
				$replen=$ftlen=0;
			$offset=($pe ? $pe : $to)-$ftlen+$replen;
		}

		#CheckTag
		for(;$n>0;--$n)
			$s.='</'.$opened[$n].'>';
		#[E] CheckTag

		/*
			Следующий участок кода призван убрать запрещенные текстовые вставки. Например <table>текст<tr> или </td>текст</tr>. Стоит отметить,
			что на этом этапе все теги, в которые обрамлен запрещенный текст должны быть "пришиблены" кодом выше.
		*/
		$s=preg_replace('#(<(?:table|tr|thead|tbody|tfoot|select|/tfoot|/thead|/tbody|/tr|/td|/option|ul|ol|/li|colgroup|/colgroup|video|source)[^>]*>)[^<]+#','\1',$s);

		#Тег figcaption должен быть первым или последним в теге figure
		$s=preg_replace('#<figure[^>]*>[^<]+<figcaption>#','<figure><figcaption>',$s);
		$s=preg_replace('#</figcaption>[^<]+</figure>#','</figcaption></figure>',$s);

		#Убираем любые пустые конструкции типа <b>   </b>
		$s=preg_replace('#<(b|i|u|s|a|q|li|ul|ol|em|tt|big|sub|sup|var|cite|code|span|small|spansmall|strong|noindex|legend|blockquote|select|table)[^>]*>\s*</\1>#i','',$s);

		$op=array();
		$w=strtok($s,'<');
		$s='';
		$u=CHARSET=='utf-8';
		$ab=constant(Language::$main.'::ALPHABET');
		#++$smcnt;
		while($w!==false)
		{
			if(false!==$we=strpos($w,'>'))
			{
				$s.='<';
				$ct=$w[0]=='/';
				if(preg_match('#^'.($ct ? '/' : '').'([a-z0-9]+)#',$w,$t)>0 and substr($w,$we-1,1)!='/')
				{
					$t=$t[1];
					if($ct and end($op)==$t)
						array_pop($op);
					elseif(!$ct and in_array($t,array('a','textarea','script')))
						$op[]=$t;
				}
				$s.=substr($w,0,$we+1);
				$w=substr($w,$we+1);
			}
			if(Eleanor::$vars['autoparse_urls'] and count(array_intersect(array('a','textarea','script'),$op))==0)
			{
				$w=preg_replace('#(^|\s|>|\](?<!\[url\]))([a-z]{3,10}://[\wa-z'.$ab.'0-9/\._\-:]+\.[\wa-z'.$ab.']{2,}(/[\w\./\-&=\?:_;\#]*)?)#i','\1[url]\2[/url]',$w);
				$w=preg_replace('#(\[url[^\]]*\])\s*(?:\[url[^\]]*\]+)#i','\1',$w);
				$w=preg_replace('#(\[/url\])\s*(?:\[/url\])+#i','\1',$w);
			}
			if($this->smiles)
			{
				if(!isset($smiles))
					$smiles=static::GetSmiles();
				$stop=$ab.'abcdefghijklmnopuqrstuvwxyz0123456789"\'';
				foreach($smiles as &$v)
				{
					$sp=0;
					foreach($v['emotion'] as &$emo)
						while(false!==$p=mb_strpos($w,$emo,$sp))
						{
							$emlen=mb_strlen($emo);
							$sp=$p;
							if(($p===0 or mb_stripos($stop,mb_substr($w,$p-1,1))===false) and (mb_substr($w,$p+$emlen,1)=='' or mb_stripos($stop,mb_substr($w,$p+$emlen,1))===false))
							{
								$em='<img class="smile" alt="'.($tmp=htmlspecialchars($emo,ELENT,CHARSET)).'" title="'.$tmp.'" src="'.$v['path'].'" />';
								$w=substr_replace($w,$em,$u ? strlen(mb_substr($w,0,$p)) : $p,$u ? strlen($emo) : $emlen);
								$sp+=mb_strlen($em)-$emlen;
								#++$smcnt;
							}
							else
								$sp+=$emlen;
						}
				}
				/*
					if($wcnt>100)
						thow new EE('Слишком много смайлов!',EE::USER);
				*/
			}

			$s.=$w;
			$w=strtok('<');
		}

		if($this->antilink=='go')
			$s=preg_replace('#(<[^>]+href=")([a-z]{3,7}://[^>]*>)#','\1go.php?\2',$s);

		if($np)
			foreach($np as &$v)
				$s=str_replace($v['r'],$v['t'],$s);

		return$s;
	}

	/**
	 * Замена запрещенных слов в текст
	 *
	 * @param string $s Текст с запрещенными словами
	 */
	public static function CensorFilter($s)
	{
		if(Eleanor::$vars['bad_words'])
		{
			$repl=array();
			foreach(explode(',',trim(Eleanor::$vars['bad_words'],',')) as $v)
			{
				$v=trim($v);
				if(!$v)
					continue;
				$repl[]='#(?<![\w<])'.preg_quote($v,'#').'(?!\w)#i';
			}
			$s=preg_replace($repl,Eleanor::$vars['bad_words_replace'],$s);
		}
		return$s;
	}

	/**
	 * Генератор дампа смайлов
	 */
	public static function GetSmiles()#ToDo! To trait & editor
	{
		$sm=Eleanor::$Cache->Get('smiles',false);
		if($sm===false)
		{
			$sm=array();
			$R=Eleanor::$Db->Query('SELECT `path`,`emotion`,`show` FROM `'.P.'smiles` WHERE `status`=1 ORDER BY `pos` ASC');
			while($a=$R->fetch_assoc())
			{
				$a['emotion']=explode(',,',trim($a['emotion'],','));
				$sm[]=$a;
			}
			Eleanor::$Cache->Put('smiles',$sm,0,false);
		}
		return$sm;
	}
}