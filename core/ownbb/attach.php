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

class OwnBbCode_attach extends OwnBbCode
{
	const
		SINGLE=true;

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
		$p=$p ? Strings::ParseParams($p,'file') : array();
		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}
		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		#Если параметр пропущен - тег считаем сбойным и не показываем
		if($p['file']===true)
			return'';

		$is_our=strpos($p['file'],'://')===false;
		if($is_our)
			$p['file']=PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$p['file'];
		$type=isset($p['type']) ? $p['type'] : substr(strrchr($p['file'],'.'),1);
		$type=strtolower($type);
		switch($type)
		{
			case'mp4':
			case'webm';
			case'ogv':
				$GLOBALS['head'][__class__]='<link rel="stylesheet" type="text/css" href="addons/flowplayer/skin/minimalist.css" />';
				$GLOBALS['jscripts'][]='addons/flowplayer/flowplayer.min.js';
				return'<div class="flowplayer" data-swf="addons/flowplayer/flowplayer.swf">
					<video>
						<source type="video/'.$type.'" src="'.$p['file'].'" />
					</video>
				</div>'.(basename($_SERVER['SCRIPT_FILENAME'])==Eleanor::$services['ajax']['file'] ? '<script type="text/javascript">//<![CDATA[
				$(function(){
					$(".flowplayer").flowplayer();
				})//]]></script>' : '');
			case'flv':
				$p['height']=isset($p['height']) ? (int)$p['height'] : 300;
			case'mp3':
				$p['width']=isset($p['width']) ? (int)$p['width'] : 400;
				$p['height']=isset($p['height']) ? (int)$p['height'] : 30;

				$align=isset($p['align']) && in_array($p['align'],array('left','center','right')) ? 'float:'.$p['align'] : '';
				$GLOBALS['jscripts'][]='addons/flowplayer/flowplayer-3.2.13.min.js';
				$pl=uniqid('player_');
				return'<a href="'.$p['file'].'" style="display:block;width:'.$p['width'].'px;height:'.$p['height'].'px;'.$align.'" id="'.$pl.'"></a>
<script type="text/javascript">//<![CDATA[
flowplayer("'.$pl.'","addons/flowplayer/flowplayer-3.2.18.swf",{
	// pause on first frame of the video
	clip: {
		autoPlay: false,
		autoBuffering: false
	},
	plugins:{
		controls:{
			autoHide: '.($type=='mp3' ? 'false' : 'true').'
		}
	}
});
//]]></script>';
			break;
			case'swf':
				$id=uniqid('swf_');
				$p['width']=isset($p['width']) ? (int)$p['width'] : 520;
				$p['height']=isset($p['height']) ? (int)$p['height'] : 330;
				$GLOBALS['jscripts'][]='js/swfobject.js';
				return'<div id="'.$id.'"></div><script type="text/javascript">//<![CDATA[
swfobject.embedSWF("'.$p['file'].'", "'.$id.'", "'.$p['width'].'", "'.$p['height'].'", "9.0.0");
//]]></script>';
			break;
			case'jpeg':
			case'jpg':
			case'png':
			case'bmp':
			case'gif':
				$pi=isset($p['mw']) ? array('style'=>' style="max-width:'.(int)$p['mw'].'px"') : array();
				if(!isset($p['preview']))
					$p['preview']=$p['file'];
				if(!isset($p['alt']) and isset(OwnBB::$opts['alt']))
					$p['alt']=OwnBB::$opts['alt'];
				foreach($p as $k=>$v)
					switch($k)
					{
						case'border':
							$v=abs((int)$v);
							if($v>5)
								$v=5;
							$pi['border']=' border="'.$v.'"';
						break;
						case'alt':
						case'title':
							$pi['alt']=' alt="'.$v.'" title="'.$v.'"';
						break;
						break;
						case'style':
							$pi['style']=' style="'.str_ireplace(array('expression','url','@import'),'na',$v).'"';
						break;
						case'height':
						case'class':
						case'width':
							$pi[$k]=' '.$k.'="'.$v.'"';
					}
				if(!isset($GLOBALS['head']['colorbox']))
				{
					$GLOBALS['jscripts'][]='addons/colorbox/jquery.colorbox-min.js';
					$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';
					$GLOBALS['head'][]='<script type="text/javascript">//<![CDATA[
$(function(){
	var F=function(){
		$("a.gallery").colorbox({
			title: function(){
				var url=$(this).attr("href"),
					title=$(this).find("img").attr("title");
				return "<a href=\""+url+"\" target=\"_blank\">"+(title ? title : url)+"</a>";
			},
			maxWidth:Math.round(screen.width/1.5),
			maxHeight:Math.round(screen.height/1.5)
		});
	}
	if(CORE.in_ajax.length)
		CORE.after_ajax.push(F);
	else
		F();
});//]]></script>';
				}
				return'<a href="'.$p['file'].'" target="_blank" rel="gallery" class="gallery"><img src="'.$p['preview'].'"'.join($pi).' /></a>';
			break;
			case'mpg':
			case'mpeg':
			case'avi':
				$p['height']=isset($p['height']) ? (int)$p['height'] : 420;
			case'mid':
			case'kar':
				$p['width']=isset($p['width']) ? (int)$p['width'] : '100%';
				return'<embed type="application/x-mplayer2" pluginspage="http://www.microsoft.com/windows/mediaplayer/en/default.asp" src="'.$p['file'].'" width="'.$p['width'].'" height="'.$p['height'].'" autostart="0" showcontrols="true" showstatusbar="true" showdisplay="true" />';
			break;
			case'mov':
				$p['width']=isset($p['width']) ? (int)$p['width'] : 520;
				$p['height']=isset($p['height']) ? (int)$p['height'] : 330;
				return'<embed type="application/x-mplayer2" pluginspage="http://www.apple.com/quicktime/download/indext.html" src="'.$p['file'].'" width="'.$p['width'].'" height="'.$p['height'].'" autostart="0" showcontrols="true" showstatusbar="true" showdisplay="true" />';
			break;
			default:
				if(!isset(Eleanor::$vars['antidirectlink']))
					Eleanor::LoadOptions('editor');
				$rel=$pr='';
				if(Eleanor::$vars['antidirectlink'] and 0!==strpos($p['file'],PROTOCOL.Eleanor::$domain) and false!==$pos=strpos($p['file'],'://') and $pos<7)
					if(Eleanor::$vars['antidirectlink']=='nofollow')
						$rel=' rel="nofollow"';
					else
						$pr='go.php?';
				return'<a href="'.$pr.$p['file'].'" target="_blank"'.$rel.'>'.$p['file'].'</a>';
		}
	}
}