<?php
namespace CMS;

$Uri=new Classes\UriDashboard(u:'blog');
$is_admin=\in_array('admin',CMS::$P->roles);

if($is_admin){?>
<li class="nav-group">
	<a class="nav-link nav-group-toggle" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-shapes"></i> Demo blog</a>
	<ul class="nav-group-items compact">
		<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-pentagon"></i> Visible for all</a></li>
		<li class="nav-item"><a class="nav-link" href="<?=$Uri(zone:'star')?>"><i class="nav-icon fa-solid fa-star"></i> Visible for admin only</a></li>
	</ul>
</li>
<?php }else{?>
<li class="nav-item"><a class="nav-link" href="<?=$Uri?>"><i class="nav-icon fa-solid fa-pentagon"></i> Visible for all</a></li>
<?php }?>