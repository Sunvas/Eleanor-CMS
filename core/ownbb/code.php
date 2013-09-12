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

class OwnBbCode_code extends OwnBbCode
{
	/**
	 * Обработка информации перед показом на странице
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,$t) : array();
		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);
		$GLOBALS['jscripts'][]='addons/highlight/highlight.pack.js';
		$GLOBALS['head'][]='<script type="text/javascript">//<![CDATA[
hljs.tabReplace="    ";
$(function(){
	CORE.AddHead("highlight",$("<link>").attr({rel:"stylesheet",type:"text/css",href:"addons/highlight/default.css",media:"screen"}));
	$("pre code").each(function(){
		if(!$(this).data("hlled"))
		{
			hljs.highlightBlock(this);
			$(this).data("hlled",true);
		}
	});
});//]]></script>';
		return'<div class="code"><pre><code'.(isset($p['auto']) ? '' : ' class="'.(isset($p[$t]) ? 'language-'.$p[$t] : 'no-highlight').'"').'><!-- NOBR -->'.$c.'</code></pre></div><!-- NOBR -->';
	}

	/**
	 * Обработка информации перед её правкой
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreEdit($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,$t) : array();
		if(isset($p[$t]) and $p[$t]=='no-highlight')
			unset($p[$t]);
		if(!empty(OwnBB::$opts['visual']))
		{
			$c=str_replace("\t",'    ',$c);
			$c=str_replace(' ','&nbsp;',$c);
			$c=nl2br($c);
		}
		else
			$c=htmlspecialchars_decode($c,ELENT);
		return parent::PreSave($t,$p,$c,true);
	}

	/**
	 * Обработка информации перед её сохранением
	 *
	 * @param string $t Тег, который обрабатывается
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега [tag...] Вот это [/tag]
	 * @param bool $cu Флаг возможности использования тега
	 */
	public static function PreSave($t,$p,$c,$cu)
	{
		if(!empty(OwnBB::$opts['visual']))
		{
			$c=preg_replace('#<br ?/?>#i',"\n",$c);
			$c=strip_tags($c,'<span><a><img><input><b><i><u><s><em><strong>');
		}
		else
			$c=htmlspecialchars($c,ELENT,CHARSET);
		$Ed=new Editor_Result;
		$c=$Ed->SafeHtml($c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}