<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	====
	*Pseudonym
*/
if(!defined('CMS'))die;
global$Eleanor,$title;
$lang=Eleanor::$Language->Load('addons/admin/langs/multisite-*.php','ms');
Eleanor::$Template->queue[]='Multisite';

$Eleanor->module['links']=array(
	'main'=>$Eleanor->Url->Prefix(),
	'options'=>$Eleanor->Url->Construct(array('do'=>'options')),
);

$d=isset($_GET['do']) ? (string)$_GET['do'] : '';
switch($d)
{
	case'options':
		$Eleanor->Url->SetPrefix(array('do'=>'options'),true);
		$c=$Eleanor->Settings->GetInterface('group','multisite');
		if($c)
		{
			$c=Eleanor::$Template->Options($c);
			Start();
			echo$c;
		}
	break;
	default:
		$post=false;
		$controls=array(
			'site',
			'title'=>array(
				'title'=>$lang['sn'],
				'descr'=>'',
				'type'=>'input',
				'imp'=>true,
				'multilang'=>Eleanor::$vars['multilang'],
				'save'=>function($a) use ($lang)
				{
					if($a['multilang'])
					{
						foreach($a['value'] as &$v)
							if($v=='')
								throw new EE($lang['emp_t'],EE::USER);
						return$a['value'];
					}
					if($a['value']=='')
						throw new EE($lang['emp_t'],EE::USER);
					return array(''=>$a['value']);
				},
				'load'=>function($a)
				{
					if($a['multilang'])
						return array('value'=>(array)$a['value']);
					return array('value'=>is_array($a['value']) ? Eleanor::FilterLangValues($a['value']) : $a['value']);
				},
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'address'=>array(
				'title'=>$lang['sa'],
				'descr'=>$lang['sa_'],
				'default'=>PROTOCOL,
				'type'=>'input',
				'imp'=>true,
				'save'=>function($a) use ($lang)
				{
					if(!Strings::CheckUrl($a['value']))
						throw new EE($lang['err_adr'],EE::USER);
					if(substr($a['value'],-1)!='/')
						$a['value'].='/';
					return$a['value'];
				},
				'bypost'=>&$post,
				'options'=>array(
					'extra'=>array(
						'data-default'=>PROTOCOL,
					),
				),
			),
			'sync'=>array(
				'title'=>$lang['sync'],
				'descr'=>$lang['sync_'],
				'type'=>'check',
				'imp'=>false,
				'bypost'=>&$post,
				'default'=>false,
				'options'=>array(
					'extra'=>array(
						'data-default'=>0,
					),
				),
			),
			'secret'=>array(
				'title'=>$lang['secret'],
				'descr'=>'',
				'type'=>'input',
				'imp'=>true,
				'bypost'=>&$post,
			),
			'db',
			'prefix'=>array(
				'title'=>$lang['pref'],
				'descr'=>'',
				'type'=>'input',
				'imp'=>false,
				'bypost'=>&$post,
				'options'=>array(
					'extra'=>array(
						'class'=>'db',
					)
				)
			),
			'host'=>array(
				'title'=>$lang['dbhost'],
				'descr'=>$lang['dbhost_'],
				'type'=>'input',
				'imp'=>false,
				'default'=>'localhost',
				'bypost'=>&$post,
				'options'=>array(
					'extra'=>array(
						'class'=>'db',
						'data-default'=>'localhost',
					)
				)
			),
			'db'=>array(
				'title'=>$lang['dbn'],
				'descr'=>'',
				'imp'=>false,
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'extra'=>array(
						'class'=>'db',
					)
				)
			),
			'user'=>array(
				'title'=>$lang['dbu'],
				'descr'=>'',
				'type'=>'input',
				'imp'=>false,
				'bypost'=>&$post,
				'options'=>array(
					'extra'=>array(
						'class'=>'db',
					)
				)
			),
			'pass'=>array(
				'title'=>$lang['dbp'],
				'descr'=>'',
				'imp'=>false,
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'extra'=>array(
						'class'=>'db',
					)
				)
			),
		);

		$data=$sites=array();
		$multilang=Eleanor::$vars['multilang'] ? array_keys(Eleanor::$langs) : array(Language::$main);
		$f=Eleanor::$root.'addons/config_multisite.php';
		$error='';
		if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		{
			$post=true;
			$keys=isset($_POST['sites']) ? array_keys((array)$_POST['sites']) : array();
			if($keys)
				try
				{
					foreach($keys as $k=>&$v)
					{
						$Eleanor->Controls->arrname=array('sites',$v);
						$data[$k]=$Eleanor->Controls->SaveControls($controls);

						if(!$data[$k]['secret'])
						{
							$p=isset($data[$k]['prefix']) ? (string)$data[$k]['prefix'] : '';
							if(isset($data[$k]['host'],$data[$k]['user'],$data[$k]['pass'],$data[$k]['db']))
								$Db=new Db($data[$k]);
							else
							{
								if($p==P)
									throw new EE(true,EE::USER);
								$Db=Eleanor::$Db;
							}
							if(strpos($p,'`.`')!==false)
								list($db,$p)=explode('`.`',$p,2);
							else
								$db=false;
							$Db->Query('SHOW TABLES'.($db ? ' FROM `'.$db.'`' : '').' LIKE \''.$Db->Escape($p,false).'multisite_jump\'');
							if($Db->num_rows==0)
								throw new EE($lang['nom'],EE::UNIT);
						}
					}
					file_put_contents($f,'<?php return '.var_export($data,true).';');
				}
				catch(EE$E)
				{
					$error=$E->getMessage();
				}
			else
				file_put_contents($f,'<?php return array();');
		}
		else
		{
			$data=is_file($f) ? (array)include$f : array();
			$keys=array_keys($data);
		}

		if(!$keys)
			$keys=array(0);

		foreach($keys as $sn)
		{
			$values=array();
			if(isset($data[$sn]))
				foreach($data[$sn] as $k=>&$v)
					$values[$k]['value']=$v;
			$Eleanor->Controls->arrname=array('sites',$sn);
			$sites[$sn]=$Eleanor->Controls->DisplayControls($controls,$values)+$values;
		}

		$title[]=$lang['conf'];
		$s=Eleanor::$Template->Multisite($sites,$controls,$error);
		Start();
		echo$s;
}