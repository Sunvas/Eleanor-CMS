<?php
namespace CMS;

$Uri=new Classes\UriDashboard(u:'main');
$l10n=new \Eleanor\Classes\L10n('main',__DIR__.'/l10n/');
$is_admin=\in_array('admin',CMS::$P->roles);

if($is_admin){?>
<li class="nav-group">
	<a class="nav-link nav-group-toggle" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-house"></i> <?=$l10n['main']?></a>
	<ul class="nav-group-items compact">
		<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-chalkboard"></i> <?=$l10n['mainpage']?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?=$Uri(zone:'settings')?>"><i class="nav-icon fa-solid fa-gear"></i> <?=$l10n['settings']?></a></li>
	</ul>
</li>
<?php }else{?>
<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-house"></i> <?=$l10n['mainpage']?></a></li>
<?php }?>
