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
if(!defined('CMS'))die;
global$Eleanor,$title;
$title[]=$Eleanor->module['title'];
Eleanor::$Template->queue[]='AdminContacts';
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php','contacts');#'contacts' - для шаблона

$post=false;
$controls=array(
	'info'=>array(
		'title'=>$lang['info'],
		'descr'=>'',
		'type'=>'editor',
		'multilang'=>true,
		'bypost'=>&$post,
		'save'=>'SaveML',
		'load'=>'LoadML',
	),
	'whom'=>array(
		'title'=>$lang['res'],
		'type'=>'user',
		'descr'=>'',
		'bypost'=>&$post,
		'multilang'=>true,
		'default'=>Eleanor::$vars['multilang'] ? array(''=>array()) : array(),
		'save'=>'SaveML',
		'load'=>'LoadML',
		'options'=>array(
			'load'=>function($co,$O)
			{
				if($co['bypost'])
				{
					$t=$O->GetPostVal($co['name'],array('email'=>array(),'whom'=>array()));
					if(is_array($t) and isset($t['email'],$t['whom']) and is_array($t['email']) and is_array($t['whom']) and count($t['email'])==count($t['whom']) and $t['whom'])
						$co['value']=array_combine($t['email'],$t['whom']);
				}
				return Eleanor::$Template->LoadWhom($co['controlname'],empty($co['value']) || !is_array($co['value']) ? array() : $co['value']);
			},
			'save'=>function($co,$O) use ($lang)
			{
				$t=$O->GetPostVal($co['name'],array('email'=>array(),'whom'=>array()));
				if(is_array($t) and isset($t['email'],$t['whom']) and is_array($t['email']) and is_array($t['whom']) and count($t['email'])==count($t['whom']) and $t['whom'])
				{
					$t['email']=array_values($t['email']);
					$t['whom']=array_values($t['whom']);
					if(count($t['email'])==1 and !$t['email'][0])
						return array();
					foreach($t['email'] as $k=>&$v)
					{
						$t['whom'][$k]=htmlspecialchars((string)$t['whom'][$k],ELENT,CHARSET,false);
						if(!Strings::CheckEmail($v,false))
							$O->errors['EMAIL_ERROR']=sprintf($lang['erremail'],$t['whom'][$k],($co['multilang'] && $co['name']['lang'] ? '('.Eleanor::$langs[ $co['name']['lang'] ]['name'].') ' : ''));
					}
					return array_combine($t['email'],$t['whom']);
				}
				return array();
			},
		)
	),
	'subject'=>array(
		'title'=>$lang['lf'],
		'descr'=>$lang['lf_'],
		'type'=>'edit',
		'multilang'=>true,
		'bypost'=>&$post,
		'save'=>'SaveML',
		'load'=>'LoadML',
		'options'=>array(
			'htmlsafe'=>true,
		),
	),
);

$error=false;
$f=$Eleanor->module['path'].'config.php';
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$post=true;
	try
	{
		$conf=$Eleanor->Controls->SaveControls($controls);
		file_put_contents($f,'<?php return '.var_export($conf,true).';');
	}
	catch(EE$E)
	{
		$error=$E->getMessage();
	}
}
else
{
	$conf=file_exists($f) ? (array)include$f : array();
	foreach($conf as $k=>&$v)
		$controls[$k]['value']=$v;
}

$values=$Eleanor->Controls->DisplayControls($controls);
$s=Eleanor::$Template->Contacts($controls,$values,$error);
Start();
echo$s;


function SaveML($a)
{
	return Eleanor::$vars['multilang'] ? $a['value'] : array(''=>$a['value']);
}

function LoadML($a)
{	if($a['multilang'])
		return array('value'=>(array)$a['value']);
	return array('value'=>is_array($a['value']) ? Eleanor::FilterLangValues($a['value']) : $a['value']);
}
