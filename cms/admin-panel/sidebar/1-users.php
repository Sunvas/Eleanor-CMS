<?php
namespace CMS;

$Uri=new Classes\Uri4AdminPanel(u:'users');
$l10n=new L10n('users',__DIR__.'/l10n/');
$is_root=\in_array('root',CMS::$P->roles);

if($is_root){?>
<li class="nav-group">
	<a class="nav-link nav-group-toggle" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-users"></i> <?=$l10n['users']?></a>
	<ul class="nav-group-items compact">
		<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-users-line"></i> <?=$l10n['userlist']?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?=$Uri(zone:'groups')?>"><i class="nav-icon fa-solid fa-user-group"></i> <?=$l10n['groups']?></a></li>
	</ul>
</li>
<?php }else{?>
<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-users-line"></i> <?=$l10n['userlist']?></a></li>
<?php }?>