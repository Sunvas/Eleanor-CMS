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
global$Eleanor,$title;
$post=false;
$controls=array(
	'id'=>array(
		'title'=>'Google search id',
		'descr'=>'<a href="http://google.com/cse/">Google custom search</a>',
		'type'=>'input',
		'bypost'=>&$post,
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
	'ads'=>array(
		'title'=>'Google AdSense id',
		'descr'=>'Please, do not edit this field!',
		'type'=>'input',
		'bypost'=>&$post,
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
);

$f=$Eleanor->module['path'].'config.php';
$error='';
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$post=true;
	try
	{
		$values=$Eleanor->Controls->SaveControls($controls);
		file_put_contents($f,'<?php return '.var_export($values,true).';');
	}
	catch(EE$E)
	{		$error=$E->getMessage();	}
}
else
{
	$values=file_exists($f) ? (array)include$f : array();
	$values+=array(
		'id'=>'',
		'ads'=>'',
	);
	foreach($values as $k=>&$v)
		$controls[$k]['value']=$v;
}
$title[]=$Eleanor->module['title'];
$values=$Eleanor->Controls->DisplayControls($controls);

$c=Eleanor::$Template->GoogleSearch($controls,$values,$error);
Start();
echo$c;