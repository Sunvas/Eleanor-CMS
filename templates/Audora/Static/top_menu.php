<?php
/*
	Элемент шаблона. Верхнее меню админки.
	Содержит в себе небольшое количество программного кода, поскольку используемый код имеет отношение исключительно к данному шаблону
*/
if(!defined('CMS'))die;?><div class="elhmenu">
<?php
if(Eleanor::$vars['multilang'])
{
	echo'<div class="hlang"><ul class="reset">';
	foreach(Eleanor::$langs as $k=>$lng)
	{		$img='<img src="images/lang_flags/'.$k.'.png" title="'.$lng['name'].'" alt="'.$lng['name'].'" />';
		echo'<li>'.($k==Language::$main ? '<span class="active">'.$img.'</span>' : '<a href="'.Eleanor::$filename.'?language='.$k.'">'.$img.'</a>').'</li>';
	}
	echo'</ul></div>';
}
$lm=Eleanor::$Language['main'];
?>
				<div class="hviewsite">
					<span><a href="<?php echo PROTOCOL.Eleanor::$domain.Eleanor::$site_path?>"><span><?php echo Eleanor::$Language['tpl']['view site']?></span></a></span>
				</div>
				<ul class="reset hmenu" id="crmenu">
						<li><a href="<?php echo Eleanor::$services['admin']['file']?>?section=general"><span><?php echo$lm['main page']?></span></a></li>
						<li><a href="<?php echo Eleanor::$services['admin']['file']?>?section=modules" class="ddrop" data-rel="#subm1"><span><?php echo$lm['modules']?></span></a></li>
						<li><a href="<?php echo Eleanor::$services['admin']['file']?>?section=management" class="ddrop" data-rel="#subm2"><span><?php echo$lm['management']?></span></a></li>
						<li><a href="<?php echo Eleanor::$services['admin']['file']?>?section=options"><span><?php echo$lm['options']?></span></a></li>
				</ul>
<?php
$di='images/modules/default-small.png';
$modules=Eleanor::$Cache->Get('adminhaeder_'.Language::$main,false);
if($modules===false)
{
	$modules=$titles=$premodules=array();
	$R=Eleanor::$Db->Query('SELECT `sections`,`title_l` `title`,`descr_l` `descr`,`protected`,`image` FROM `'.P.'modules` WHERE `services`=\'\' OR `services` LIKE \'%,'.Eleanor::$service.',%\' AND `active`=1');
	while($a=$R->fetch_assoc())
	{
		$img=$di;
		if($a['image'])
		{
			$a['image']='images/modules/'.str_replace('*','small',$a['image']);
			if(is_file(Eleanor::$root.$a['image']))
				$img=$a['image'];
		}

		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
		$a['descr']=$a['descr'] ? Eleanor::FilterLangValues((array)unserialize($a['descr'])) : '';

		$sections=$a['sections'] ? (array)unserialize($a['sections']) : false;
		if($sections)
		{
			foreach($sections as $k=>&$v)
				if(isset($v['']))
					$v=reset($v['']);
				elseif(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
					$v=reset($v[Language::$main]);
				elseif(isset($v[LANGUAGE]))
					$v=reset($v[LANGUAGE]);
				else
					continue;
			$titles[]=$a['title'];
			$premodules[]='<li><a href="'.Eleanor::$filename.'?'.$Eleanor->Url->Construct(array('section'=>'modules','module'=>reset($sections)),false).'" title="'.$a['descr'].'"><span><img src="'.$img.'" alt="" />'.$a['title'].'</span></a></li>';
		}
	}
	asort($titles,SORT_STRING);
	foreach($titles as $k=>&$v)
		$modules[]=$premodules[$k];

	Eleanor::$Cache->Put('adminhaeder_'.Language::$main,$modules,3600,false);
}
$modcnt=count($modules);
if($three=$modcnt>23)
	$modcnt_d=ceil($modcnt/3);
else
	$modcnt_d=$modcnt>10 ? ceil($modcnt/2) : $modcnt;
?>
				<div id="subm1" class="hmenusub<?php if($modcnt>10) echo $three ? ' threecol' : ' twocol'?>">
					<div class="colomn">
						<ul class="reset">
<?php
echo join(array_slice($modules,0,$modcnt_d))
?>
						</ul>
					</div>
<?php if($modcnt>10):?>
					<div class="colomn">
						<ul class="reset">
<?php
echo join(array_slice($modules,$modcnt_d,$modcnt_d))
?>
						</ul>
					</div>
<?php endif;if($three>10):?>
					<div class="colomn">
						<ul class="reset">
<?php
echo join(array_slice($modules,$modcnt_d*2))
?>
						</ul>
					</div>
<?php endif;unset($module,$modcnt,$modcnt_d)?>
					<div class="clr"></div>
				</div>
<?php
$manage=array();
require Eleanor::$root.'addons/admin/info.php';
foreach($info as $name=>&$a)
{
	if(!isset($a['services'][Eleanor::$service]) or !empty($a['hidden']))
		continue;
	$img=$di;
	if($a['image'])
	{
		$a['image']='images/modules/'.str_replace('*','small',$a['image']);
		if(is_file(Eleanor::$root.$a['image']))
			$img=$a['image'];
	}
	$manag[]='<li><a href="'.Eleanor::$filename.'?'.$Eleanor->Url->Construct(array('section'=>'management','module'=>$name),false).'" title="'.$a['descr'].'"><span><img src="'.$img.'" alt="" />'.$a['title'].'</span></a></li>';
}
?>
				<div id="subm2" class="hmenusub">
					<ul class="reset">
<?php echo join($manag);unset($manag)?>
					</ul>
				</div>
<script type="text/javascript">/*<![CDATA*/$(function(){$(".ddrop").MainMenu();})//]]></script>
		</div>