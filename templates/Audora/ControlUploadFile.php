<?php
/*
	Внешний вид контрола "загрузка файла" (uploadfile).

	@var string result|control определяет следующие параметры.
		Для: result
			@var string - путь к загруженному файлу, либо пусто
		Для: control
			@var bool признак того, что файл загружен
			@var bool признак того, что путь к файлу был прописан
			@var string путь к файлу
			@var string имя инпута для контрола
			@var array настройки, ключи массива:
				max_size - максимальный размер файла в байтах
				types - массив разрешенных к загрузке файлов
*/
if(!defined('CMS'))die;
if($v_0=='result')
	return$v_1 ? '<a href="'.$v_1.'" rel="nofollow">'.basename($v_1).'</a>' : '';

$uploaded=&$v_1;
$writed=&$v_2;
$value=&$v_3;
$name=&$v_4;
$options=&$v_4;
$lang=Eleanor::$Language->Load($theme.'langs/uploadfile-*.php',false);

$img=(($uploaded or $writed) and preg_match('#\.(png|jpe?g|bmp|gif)$#i',$value)>0);
$r='<ul style="list-style-type:none">';
$u=uniqid();
if($uploaded or $writed)
{
	$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
	$GLOBALS['head']['autocomplete|style']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
	echo'<script type="text/javascript">/*<![CDATA[*/';
	if($img)
	{
		$GLOBALS['head']['colorbox']='<link rel="stylesheet" media="screen" href="addons/colorbox/colorbox.css" />';
		$GLOBALS['jscripts'][]='addons/colorbox/jquery.colorbox-min.js';
		echo'$(function(){
	$("#a-',$u,'").colorbox({
		title: function(){
			var url=$(this).attr("href"),
				title=$(this).find("img").attr("title");
			return "<a href=\""+url+"\" target=\"_blank\">"+(title ? title : url)+"</a>";
		},
		maxWidth:Math.round(screen.width/1.5),
		maxHeight:Math.round(screen.height/1.5),
	});
});';
	}
	echo'$(function(){
		$("#text-',$u,'").autocomplete({
			serviceUrl:CORE.ajax_file,
			minChars:2,
			delimiter: null,
			params:{
				direct:"'.Eleanor::$service.'",
				file:"autocomplete"
			}
		});
	});//]]></script>';
	$r.='<li><span style="vertical-align:15%">'
		.sprintf($uploaded ? $lang['uploaded_file'] : $lang['writed_file'],'<a href="'.$value.'" target="_blank"'.($img ? ' id="a-'.$u.'"' : '').'>'.basename($value).'</span></a>');

	if($uploaded)
		$r.='<label style="margin:0px 15px">'.Eleanor::Check($name.'[delete]').'<span style="vertical-align:15%"> '.Eleanor::$Language['tpl']['delete'].'</span></label>';
	$r.='</li>';
}

echo$r,'<li class="upload"',$writed ? ' style="display:none"' : '','>',
	Eleanor::Control($name.'[file]','file',false,array('onchange'=>'$(this).closest(\'form\').attr(\'enctype\',\'multipart/form-data\')')),
	'<br /><a class="small" href="#" onclick="$(\'li.upload\').hide();$(\'li.write\').show();$(\'#type-',$u,'\').val(\'w\');return false">',
	$lang['write'].'</a></li><li class="write"',$writed ? '' : ' style="display:none"','>',
	Eleanor::Edit($name.'[text]','',array('id'=>'text-'.$u)),
	'<br /><a class="small" href="#" onclick="var f=$(this).closest(\'form\');f.find(\'li.upload\').show();f.find(\'li.write\').hide();$(\'#type-'.$u.'\').val(\'u\');return false">',
	$lang['upload'],'</a></li>',
	$options['max_size'] ? '<li class="upload"'.($writed ? ' style="display:none"' : '').'><span class="small" style="font-weight:bold">'.sprintf($lang['max_size'],Files::BytesToSize($options['max_size'])).'</span></li>' : '',
	$options['types'] ? '<li><span class="small" style="font-weight:bold">'.sprintf($lang['allowed_types'],join(', ',$options['types'])).'</span></li>' : '',
	'</ul>',Eleanor::Control($name.'[type]','hidden',$writed ? 'w' : 'u',array('id'=>'type-'.$u));