<?php
namespace CMS;

$Uri=new Classes\Uri4AdminPanel(u:'static');
$l10n=new L10n('static',__DIR__.'/l10n/');
$is_root=\in_array('root',CMS::$P->roles);

if($is_root){?>
<li class="nav-group">
	<a class="nav-link nav-group-toggle" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-file"></i> <?=$l10n['title-br']?></a>
	<ul class="nav-group-items compact">
		<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-table-list"></i> <?=$l10n['list']?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?=$Uri(zone:'settings')?>"><i class="nav-icon fa-solid fa-screwdriver"></i> <?=$l10n['settings']?></a></li>
	</ul>
</li>
<?php }else{?>
<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-file"></i> <?=$l10n['title']?></a></li>
<?php }?>