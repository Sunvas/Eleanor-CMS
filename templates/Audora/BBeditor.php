<?php
/*
	Шаблон оформления. Системного BB редактора

	@var array(
		id - идентификатор редактора
		name - имя контрола редактора
		value - значение редактора
		extra - дополнительные параметры textarea
		smiles - флаг включения смайлов в предпросмотре
		ownbb - флаг включения "своих" BB кодов в предпросмотре
	)
*/
if(!defined('CMS'))die;
$GLOBALS['head']['bbeditor']='<link rel="stylesheet" type="text/css" href="templates/Audora/style/bbeditor.css" media="screen" />';
$lang=Eleanor::$Language->Load($theme.'langs/bbeditor-*.php',false);
?><!-- BB EDITOR TEXTAREA+PANEL -->
<div class="bb_editor" id="ed-<?php echo$id?>">

<!-- BB PANEL -->
<div class="bb_panel">
<div class="bb_rpanel">
	<a href="#" title="<?php echo$lang['preview']?>" class="bb_preview"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['increase_field']?>" class="bb_plus"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['decrease_field']?>" class="bb_minus"><img src="images/spacer.png" alt="" /></a>
</div>
	<a href="#" title="<?php echo$lang['bold']?>" class="bb_bold"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['italic']?>" class="bb_italic"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['underline']?>" class="bb_uline"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['strike']?>" class="bb_strike"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['left']?>" class="bb_left"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['center']?>" class="bb_center"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['right']?>" class="bb_right"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['justify']?>" class="bb_justify"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['hr']?>" class="bb_hr"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['link']?>" class="bb_url"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['email']?>" class="bb_mail"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['image']?>" class="bb_img"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['ul']?>" class="bb_ul"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['ol']?>" class="bb_ol"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['li']?>" class="bb_li"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['tm']?>" class="bb_tm"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['copyright']?>" class="bb_c"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['registered']?>" class="bb_r"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['font']?>" class="bb_font"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['tab']?>" class="bb_tab"><img src="images/spacer.png" alt="" /></a>
	<a href="#" title="<?php echo$lang['nobb']?>" class="bb_nobb"><img src="images/spacer.png" alt="" /></a>
<div class="clr"></div>
</div>
<!--END BB PANEL -->
	<div class="dtarea"><?php echo Eleanor::Text($name,$value,$extra+array('style'=>'width:99.5%','rows'=>10))?></div>
	<div class="bb_fonts" style="position:absolute;display:none;">
	<table><tr>
	<td><?php echo$lang['color']?>:</td>
	<td>
		<select class="bb_color" size="1">
		<option value="0"><?php echo$lang['select']?></option>
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
	<td><?php echo$lang['background']?>:</td>
	<td>
		<select class="bb_background" size="1">
		<option value="0"><?php echo$lang['select']?></option>
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
	<td><?php echo$lang['size']?>:</td>
	<td>
		<select class="bb_size" size="1"><option value="0"><?php echo$lang['select']?></option><option>8</option><option>10</option><option>12</option><option>14</option><option>16</option><option>18</option><option>20</option><option>22</option><option>24</option><option>26</option><option>28</option><option>30</option><option>32</option></select>
	</td>
	</tr><tr>
	<td><?php echo$lang['font']?>:</td>
	<td>
		<select class="bb_font" size="1">
		<option value="0"><?php echo$lang['select']?></option>
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
<script type="text/javascript">/*<![CDATA[*/new CORE.BBEditor({id:"<?php echo$id,'"',$ownbb ? ',ownbb:true' : '',$smiles ? ',smiles:true' : '',',service:"',Eleanor::$service?>",Preview:function(html){	{
		var pr=$("<div class=\"preview\">").width($("#ed-<?php echo$id?>").parent().width()).insertAfter($("#ed-<?php echo$id?>").parent().children("div.preview").remove().end().find("div.bb_yourpanel")),
			hide=$("<div style=\"text-align:center\"><input type=\"button\" class=\"button\" value=\""+CORE.Lang('hide')+"\" /></div>").find("input").click(function(){
				pr.remove();
			}).end();
		pr.html(html+"<br />").append(hide).show();
	}}});//]]></script>
<!-- END BB EDITOR TEXTAREA+PANEL -->