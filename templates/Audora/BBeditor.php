<?php
/*
	Шаблон оформления. Системного BB редактора

	@var array(
		id - идентификатор редактора
		name - имя контрола редактора
		value - значение редактора
		addon - дополнительные параметры textarea
		smiles - флаг включения смайлов в предпросмотре
		ownbb - флаг включения "своих" BB кодов в предпросмотре
	)
*/
if(!defined('CMS'))die;
$GLOBALS['head']['bbeditor']='<link rel="stylesheet" type="text/css" href="templates/Audora/style/bbeditor.css" media="screen" />';
$l=Eleanor::$Language['bbeditor'];
?><!-- BB EDITOR TEXTAREA+PANEL -->
<div class="bb_editor" id="div_<?php echo$id?>">

<!-- BB PANEL -->
<div class="bb_panel">
<div class="bb_rpanel">
	<a href="#" title="<?php echo$l['preview']?>" class="bbe_preview"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['increase_field']?>" class="bbe_splus"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['decrease_field']?>" class="bbe_sminus"><img src="images/spacer.png" alt="" /></a>
</div>
	<a href="#" title="<?php echo$l['bold']?>" class="bbe_bold"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['italic']?>" class="bbe_italic"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['underline']?>" class="bbe_uline"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['strike']?>" class="bbe_strike"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['left']?>" class="bbe_left"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['center']?>" class="bbe_center"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['right']?>" class="bbe_right"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['justify']?>" class="bbe_justify"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['hr']?>" class="bbe_hr"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['link']?>" class="bbe_url"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['email']?>" class="bbe_mail"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['image']?>" class="bbe_img"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['ul']?>" class="bbe_ul"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['ol']?>" class="bbe_ol"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['li']?>" class="bbe_li"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['tm']?>" class="bbe_tm"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['copyright']?>" class="bbe_c"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['registered']?>" class="bbe_r"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['font']?>" class="bbe_font"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['tab']?>" class="bbe_tab"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$l['nobb']?>" class="bbe_nobb"><img src="images/spacer.png" alt="" /></a>
<div class="clr"></div>
</div>
<!--END BB PANEL -->
	<div class="dtarea"><?php echo Eleanor::Text($name,$value,$addon+array('style'=>'width:99.5%','rows'=>10))?></div>
	<div class="bb_fonts" style="position:absolute;display:none;">
	<table><tr>
	<td><?php echo$l['color']?>:</td>
	<td>
		<select class="bbe_color" size="1">
		<option value="0"><?php echo$l['select']?></option>
		<option style="background-color: black; color: #ffffff;">black</option>
		<option style="background-color: gray; color: #ffffff;">gray</option>
		<option style="background-color: white; color: #000000;">white</option>
		<option style="background-color: maroon; color: #ffffff;">maroon</option>
		<option style="background-color: orange; color: #ffffff;">orange</option>
		<option style="background-color: orangered; color: #ffffff;">orangered</option>
		<option style="background-color: red; color: #ffffff;">red</option>
		<option style="background-color: purple; color: #ffffff;">purple</option>
		<option style="background-color: fuchsia; color: #ffffff;">fuchsia</option>
		<option style="background-color: green; color: #ffffff;">green</option>
		<option style="background-color: lime; color: #ffffff;">lime</option>
		<option style="background-color: olive; color: #ffffff;">olive</option>
		<option style="background-color: yellow; color: #000000;">yellow</option>
		<option style="background-color: navy; color: #ffffff;">navy</option>
		<option style="background-color: blue; color: #ffffff;">blue</option>
		<option style="background-color: teal; color: #ffffff;">teal</option>
		<option style="background-color: aqua; color: #ffffff;">aqua</option>
		</select>
	</td>
		</tr><tr>
	<td><?php echo$l['background']?>:</td>
	<td>
		<select class="bbe_background" size="1">
		<option value="0"><?php echo$l['select']?></option>
		<option style="background-color: black; color: #ffffff;">black</option>
		<option style="background-color: gray; color: #ffffff;">gray</option>
		<option style="background-color: white; color: #000000;">white</option>
		<option style="background-color: maroon; color: #ffffff;">maroon</option>
		<option style="background-color: orange; color: #ffffff;">orange</option>
		<option style="background-color: orangered; color: #ffffff;">orangered</option>
		<option style="background-color: red; color: #ffffff;">red</option>
		<option style="background-color: purple; color: #ffffff;">purple</option>
		<option style="background-color: fuchsia; color: #ffffff;">fuchsia</option>
		<option style="background-color: green; color: #ffffff;">green</option>
		<option style="background-color: lime; color: #ffffff;">lime</option>
		<option style="background-color: olive; color: #ffffff;">olive</option>
		<option style="background-color: yellow; color: #000000;">yellow</option>
		<option style="background-color: navy; color: #ffffff;">navy</option>
		<option style="background-color: blue; color: #ffffff;">blue</option>
		<option style="background-color: teal; color: #ffffff;">teal</option>
		<option style="background-color: aqua; color: #ffffff;">aqua</option>
		</select>
	</td>
		</tr><tr>
	<td><?php echo$l['size']?>:</td>
	<td>
		<select class="bbe_size" size="1"><option value="0"><?php echo$l['select']?></option><option>8</option><option>10</option><option>12</option><option>14</option><option>16</option><option>18</option><option>20</option><option>22</option><option>24</option><option>26</option><option>28</option><option>30</option><option>32</option></select>
	</td>
	</tr><tr>
	<td><?php echo$l['font']?>:</td>
	<td>
		<select class="bbe_font" size="1">
		<option value="0"><?php echo$l['select']?></option>
		<option style="font-family: Arial, Helvetica, sans-serif;">Arial</option>
		<option style="font-family: 'Times New Roman', Times, serif;">Times New Roman</option>
		<option style="font-family: 'Courier New', Courier, monospace;">Courier New</option>
		<option style="font-family: Geneva, Arial, Helvetica, sans-serif;">Geneva</option>
		<option style="font-family: Verdana, Arial, Helvetica, sans-serif;">Verdana</option>
		<option style="font-family: Georgia, 'Times New Roman', Times, serif;">Georgia</option>
		<option style="font-family: 'Comic Sans MS', Georgia, Times, cursive;">Comic Sans MS</option>
		</select>
	</td>
	</tr></table>
</div>

</div>
<script type="text/javascript">/*<![CDATA[*/new CORE.BBEditor({id:"<?php echo$id,'"',$ownbb ? ',ownbb:true' : '',$smiles ? ',smiles:true' : '',',service:"',Eleanor::$service?>"});//]]></script>
<!-- END BB EDITOR TEXTAREA+PANEL -->